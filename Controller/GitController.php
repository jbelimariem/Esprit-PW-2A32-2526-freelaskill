<?php
/**
 * Controller/GitController.php
 * Toute la logique métier Git ici (MVC strict)
 */

require_once __DIR__ . '/../Model/GitRepository.php';
require_once __DIR__ . '/../Model/GitCommit.php';

class GitController {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
        $this->initTables();
    }

    // ─────────────────────────────────────────────────────────────────────
    // INIT — Crée les tables si elles n'existent pas encore
    // ─────────────────────────────────────────────────────────────────────

    private function initTables(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS git_commits (
                id_commit        INT AUTO_INCREMENT PRIMARY KEY,
                id_conversation  INT          NOT NULL,
                branche          VARCHAR(100) NOT NULL DEFAULT 'main',
                message          VARCHAR(500) NOT NULL,
                auteur           VARCHAR(150) NOT NULL DEFAULT 'Utilisateur',
                id_user          INT          NOT NULL DEFAULT 0,
                date_commit      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                hash             CHAR(8)      NOT NULL,
                snapshot         LONGTEXT     NOT NULL DEFAULT '[]',
                file_path        VARCHAR(500) NULL,
                file_name        VARCHAR(255) NULL,
                INDEX idx_conv (id_conversation),
                INDEX idx_branch (branche)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        // Add file columns to existing tables (safe to run multiple times)
        try { $this->pdo->exec("ALTER TABLE git_commits ADD COLUMN file_path VARCHAR(500) NULL"); } catch (\Exception $e) {}
        try { $this->pdo->exec("ALTER TABLE git_commits ADD COLUMN file_name VARCHAR(255) NULL"); } catch (\Exception $e) {}

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS git_branches (
                id_branch        INT AUTO_INCREMENT PRIMARY KEY,
                id_conversation  INT          NOT NULL,
                nom              VARCHAR(100) NOT NULL,
                base_commit_hash CHAR(8)      NULL,
                date_creation    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_conv_branch (id_conversation, nom)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────

    private function jsonResponse(array $data): void {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function generateHash(): string {
        return substr(bin2hex(random_bytes(4)), 0, 8);
    }

    private function getCurrentUserId(): int {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    /** Charge les messages actuels de la conversation pour le snapshot */
    private function loadMessagesSnapshot(int $id_conversation): string {
        $stmt = $this->pdo->prepare("
            SELECT id_message, contenu, id_expediteur, date_envoi
            FROM messages
            WHERE id_conversation = :id AND statut != 'deleted'
            ORDER BY date_envoi ASC
        ");
        $stmt->execute([':id' => $id_conversation]);
        return json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }

    /** Vérifie que l'utilisateur a accès à la conversation */
    private function checkAccess(int $id_conversation): bool {
        $id_user = $this->getCurrentUserId();
        $stmt = $this->pdo->prepare("
            SELECT id_conversation FROM conversations
            WHERE id_conversation = :id
              AND (id_user1 = :u OR id_user2 = :u OR titre LIKE '%\"groupe\":true%')
        ");
        $stmt->execute([':id' => $id_conversation, ':u' => $id_user]);
        return (bool)$stmt->fetch();
    }

    /** Retourne les branches d'une conversation, crée 'main' si vide */
    private function getBranches(int $id_conversation): array {
        $stmt = $this->pdo->prepare("
            SELECT nom, date_creation FROM git_branches
            WHERE id_conversation = :id
            ORDER BY date_creation ASC
        ");
        $stmt->execute([':id' => $id_conversation]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($rows)) {
            // Créer branche 'main' par défaut
            $this->pdo->prepare("
                INSERT IGNORE INTO git_branches (id_conversation, nom)
                VALUES (:id, 'main')
            ")->execute([':id' => $id_conversation]);
            $rows = [['nom' => 'main', 'date_creation' => date('Y-m-d H:i:s')]];
        }
        return $rows;
    }

    // ─────────────────────────────────────────────────────────────────────
    // VUE PRINCIPALE — Affiche le panel Git
    // ─────────────────────────────────────────────────────────────────────

    public function panel(): void {
        $id_conversation = (int)($_GET['id_conversation'] ?? 0);
        if (!$id_conversation || !$this->checkAccess($id_conversation)) {
            // This is an HTML page (popup window), not an API call — redirect instead of JSON
            header('Location: index.php?page=conversations');
            exit;
        }

        $branches     = $this->getBranches($id_conversation);
        $brancheNoms  = array_column($branches, 'nom');
        $brancheActive = $_GET['branche'] ?? 'main';
        if (!in_array($brancheActive, $brancheNoms)) $brancheActive = 'main';

        // Commits de cette branche
        $stmt = $this->pdo->prepare("
            SELECT * FROM git_commits
            WHERE id_conversation = :id AND branche = :branche
            ORDER BY date_commit DESC
        ");
        $stmt->execute([':id' => $id_conversation, ':branche' => $brancheActive]);
        $commits = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalCommits = count($commits);

        // Dernier commit
        $dernierCommit = $commits[0] ?? null;

        include __DIR__ . '/../Views/Frontoffice/git_panel.php';
    }

    // ─────────────────────────────────────────────────────────────────────
    // COMMIT
    // ─────────────────────────────────────────────────────────────────────

    public function commit(): void {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $message         = trim($_POST['message'] ?? '');
        $branche         = trim($_POST['branche'] ?? 'main');
        $id_user         = $this->getCurrentUserId();

        if (!$id_conversation)          $this->jsonResponse(['error' => 'ID conversation requis']);
        if (empty($message))            $this->jsonResponse(['error' => 'Message de commit requis']);
        if (strlen($message) > 500)     $this->jsonResponse(['error' => 'Message trop long (max 500)']);
        if (!$this->checkAccess($id_conversation)) $this->jsonResponse(['error' => 'Accès non autorisé']);

        // Sanitize
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $branche = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $branche) ?: 'main';

        // Handle optional file attachment
        $file_path = null;
        $file_name = null;
        if (!empty($_FILES['commit_file']['name']) && $_FILES['commit_file']['error'] === UPLOAD_ERR_OK) {
            $maxSize = 10 * 1024 * 1024; // 10 MB
            if ($_FILES['commit_file']['size'] > $maxSize) {
                $this->jsonResponse(['error' => 'Fichier trop volumineux (max 10 MB)']);
            }
            $origName  = basename($_FILES['commit_file']['name']);
            $ext       = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $safeName  = 'git_' . uniqid() . '.' . $ext;
            $uploadDir = __DIR__ . '/../Views/Frontoffice/js/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($_FILES['commit_file']['tmp_name'], $uploadDir . $safeName)) {
                $file_path = 'Views/Frontoffice/js/uploads/' . $safeName;
                $file_name = $origName;
            }
        }

        // S'assurer que la branche existe
        $this->pdo->prepare("
            INSERT IGNORE INTO git_branches (id_conversation, nom)
            VALUES (:id, :nom)
        ")->execute([':id' => $id_conversation, ':nom' => $branche]);

        $hash     = $this->generateHash();
        $snapshot = $this->loadMessagesSnapshot($id_conversation);
        $auteur   = 'Utilisateur #' . $id_user;

        $stmt = $this->pdo->prepare("
            INSERT INTO git_commits (id_conversation, branche, message, auteur, id_user, hash, snapshot, file_path, file_name)
            VALUES (:conv, :branch, :msg, :auteur, :user, :hash, :snap, :fpath, :fname)
        ");
        $ok = $stmt->execute([
            ':conv'   => $id_conversation,
            ':branch' => $branche,
            ':msg'    => $message,
            ':auteur' => $auteur,
            ':user'   => $id_user,
            ':hash'   => $hash,
            ':snap'   => $snapshot,
            ':fpath'  => $file_path,
            ':fname'  => $file_name,
        ]);

        $this->jsonResponse([
            'success'   => $ok,
            'hash'      => $hash,
            'message'   => $message,
            'auteur'    => $auteur,
            'date'      => date('Y-m-d H:i:s'),
            'file_name' => $file_name,
            'file_path' => $file_path,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // CRÉER UNE BRANCHE
    // ─────────────────────────────────────────────────────────────────────

    public function createBranche(): void {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $nom             = trim($_POST['nom'] ?? '');
        $from_hash       = trim($_POST['from_hash'] ?? '');

        if (!$id_conversation)          $this->jsonResponse(['error' => 'ID conversation requis']);
        if (empty($nom))                $this->jsonResponse(['error' => 'Nom de branche requis']);
        if (strlen($nom) > 100)         $this->jsonResponse(['error' => 'Nom trop long']);
        if (!$this->checkAccess($id_conversation)) $this->jsonResponse(['error' => 'Accès non autorisé']);

        $nom = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $nom);
        if (empty($nom)) $this->jsonResponse(['error' => 'Nom de branche invalide (lettres, chiffres, - _)']);

        // Vérifier si elle existe déjà
        $stmt = $this->pdo->prepare("
            SELECT id_branch FROM git_branches
            WHERE id_conversation = :id AND nom = :nom
        ");
        $stmt->execute([':id' => $id_conversation, ':nom' => $nom]);
        if ($stmt->fetch()) $this->jsonResponse(['error' => 'Cette branche existe déjà']);

        $stmt = $this->pdo->prepare("
            INSERT INTO git_branches (id_conversation, nom, base_commit_hash)
            VALUES (:id, :nom, :hash)
        ");
        $ok = $stmt->execute([
            ':id'   => $id_conversation,
            ':nom'  => $nom,
            ':hash' => $from_hash ?: null,
        ]);

        $this->jsonResponse(['success' => $ok, 'nom' => $nom]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // SUPPRIMER UNE BRANCHE
    // ─────────────────────────────────────────────────────────────────────

    public function deleteBranche(): void {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $nom             = trim($_POST['nom'] ?? '');

        if ($nom === 'main') $this->jsonResponse(['error' => 'Impossible de supprimer la branche main']);
        if (!$this->checkAccess($id_conversation)) $this->jsonResponse(['error' => 'Accès non autorisé']);

        // Supprimer les commits de cette branche aussi
        $this->pdo->prepare("DELETE FROM git_commits WHERE id_conversation = :id AND branche = :nom")
                  ->execute([':id' => $id_conversation, ':nom' => $nom]);

        $stmt = $this->pdo->prepare("DELETE FROM git_branches WHERE id_conversation = :id AND nom = :nom");
        $ok = $stmt->execute([':id' => $id_conversation, ':nom' => $nom]);

        $this->jsonResponse(['success' => $ok]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // VOIR UN COMMIT (diff snapshot vs précédent)
    // ─────────────────────────────────────────────────────────────────────

    public function showCommit(): void {
        $id_conversation = (int)($_GET['id_conversation'] ?? 0);
        $hash            = trim($_GET['hash'] ?? '');

        if (!$this->checkAccess($id_conversation)) $this->jsonResponse(['error' => 'Accès non autorisé']);

        $stmt = $this->pdo->prepare("
            SELECT * FROM git_commits
            WHERE id_conversation = :id AND hash = :hash
        ");
        $stmt->execute([':id' => $id_conversation, ':hash' => $hash]);
        $commit = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$commit) $this->jsonResponse(['error' => 'Commit introuvable']);

        // Chercher le commit précédent sur la même branche
        $stmtPrev = $this->pdo->prepare("
            SELECT * FROM git_commits
            WHERE id_conversation = :id AND branche = :branch AND date_commit < :date
            ORDER BY date_commit DESC
            LIMIT 1
        ");
        $stmtPrev->execute([
            ':id'     => $id_conversation,
            ':branch' => $commit['branche'],
            ':date'   => $commit['date_commit'],
        ]);
        $prevCommit = $stmtPrev->fetch(\PDO::FETCH_ASSOC);

        $currentMessages = json_decode($commit['snapshot'] ?? '[]', true) ?: [];
        $prevMessages    = $prevCommit ? (json_decode($prevCommit['snapshot'] ?? '[]', true) ?: []) : [];

        $diff = $this->computeDiff($prevMessages, $currentMessages);

        $this->jsonResponse([
            'commit'      => $commit,
            'prev_commit' => $prevCommit,
            'diff'        => $diff,
            'added'       => $diff['added'],
            'removed'     => $diff['removed'],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // RESTAURER un commit (replace messages par le snapshot)
    // ─────────────────────────────────────────────────────────────────────

    public function restore(): void {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $hash            = trim($_POST['hash'] ?? '');
        $id_user         = $this->getCurrentUserId();

        if (!$this->checkAccess($id_conversation)) $this->jsonResponse(['error' => 'Accès non autorisé']);

        $stmt = $this->pdo->prepare("
            SELECT snapshot, message FROM git_commits
            WHERE id_conversation = :id AND hash = :hash
        ");
        $stmt->execute([':id' => $id_conversation, ':hash' => $hash]);
        $commit = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$commit) $this->jsonResponse(['error' => 'Commit introuvable']);

        $messages = json_decode($commit['snapshot'] ?? '[]', true) ?: [];

        // Supprimer les messages actuels et réinsérer depuis le snapshot
        $this->pdo->prepare("DELETE FROM messages WHERE id_conversation = :id")
                  ->execute([':id' => $id_conversation]);

        $stmtInsert = $this->pdo->prepare("
            INSERT INTO messages (id_message, contenu, id_expediteur, date_envoi, id_conversation, statut)
            VALUES (:id_msg, :contenu, :expediteur, :date, :conv, 'normal')
        ");

        foreach ($messages as $msg) {
            $stmtInsert->execute([
                ':id_msg'     => $msg['id_message'],
                ':contenu'    => $msg['contenu'],
                ':expediteur' => $msg['id_expediteur'],
                ':date'       => $msg['date_envoi'],
                ':conv'       => $id_conversation,
            ]);
        }

        // Auto-commit de la restauration
        $newHash = $this->generateHash();
        $restoreMsg = 'Restauration depuis commit ' . $hash . ' : ' . htmlspecialchars($commit['message'], ENT_QUOTES, 'UTF-8');
        $this->pdo->prepare("
            INSERT INTO git_commits (id_conversation, branche, message, auteur, id_user, hash, snapshot)
            VALUES (:conv, 'main', :msg, :auteur, :user, :hash, :snap)
        ")->execute([
            ':conv'   => $id_conversation,
            ':msg'    => $restoreMsg,
            ':auteur' => 'Utilisateur #' . $id_user,
            ':user'   => $id_user,
            ':hash'   => $newHash,
            ':snap'   => $commit['snapshot'],
        ]);

        $this->jsonResponse(['success' => true, 'new_hash' => $newHash]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // API JSON — Liste commits
    // ─────────────────────────────────────────────────────────────────────

    public function listCommits(): void {
        $id_conversation = (int)($_GET['id_conversation'] ?? 0);
        $branche         = trim($_GET['branche'] ?? 'main');

        if (!$this->checkAccess($id_conversation)) $this->jsonResponse(['error' => 'Accès non autorisé']);

        $stmt = $this->pdo->prepare("
            SELECT id_commit, branche, message, auteur, id_user, date_commit, hash, file_path, file_name
            FROM git_commits
            WHERE id_conversation = :id AND branche = :branch
            ORDER BY date_commit DESC
        ");
        $stmt->execute([':id' => $id_conversation, ':branch' => $branche]);
        $commits = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->jsonResponse(['commits' => $commits, 'total' => count($commits)]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // API JSON — Liste branches
    // ─────────────────────────────────────────────────────────────────────

    public function listBranches(): void {
        $id_conversation = (int)($_GET['id_conversation'] ?? 0);
        if (!$this->checkAccess($id_conversation)) $this->jsonResponse(['error' => 'Accès non autorisé']);

        $branches = $this->getBranches($id_conversation);

        // Compter les commits par branche
        $stmt = $this->pdo->prepare("
            SELECT branche, COUNT(*) as nb
            FROM git_commits
            WHERE id_conversation = :id
            GROUP BY branche
        ");
        $stmt->execute([':id' => $id_conversation]);
        $counts = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $counts[$row['branche']] = (int)$row['nb'];
        }

        foreach ($branches as &$b) {
            $b['nb_commits'] = $counts[$b['nom']] ?? 0;
        }

        $this->jsonResponse(['branches' => $branches]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // DIFF HELPER — Compare deux snapshots
    // ─────────────────────────────────────────────────────────────────────

    private function computeDiff(array $prev, array $current): array {
        $prevIds    = array_column($prev,    'id_message');
        $currentIds = array_column($current, 'id_message');

        $addedIds   = array_diff($currentIds, $prevIds);
        $removedIds = array_diff($prevIds, $currentIds);

        $added   = array_values(array_filter($current, fn($m) => in_array($m['id_message'], $addedIds)));
        $removed = array_values(array_filter($prev,    fn($m) => in_array($m['id_message'], $removedIds)));

        // Detect modified messages (same id, different content)
        $modified = [];
        foreach ($current as $cm) {
            foreach ($prev as $pm) {
                if ($cm['id_message'] === $pm['id_message'] && $cm['contenu'] !== $pm['contenu']) {
                    $modified[] = ['before' => $pm, 'after' => $cm];
                }
            }
        }

        return [
            'added'    => $added,
            'removed'  => $removed,
            'modified' => $modified,
        ];
    }
}
?>