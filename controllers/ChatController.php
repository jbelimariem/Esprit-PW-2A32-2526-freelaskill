<?php

putenv('CLAUDE_API_KEY=sk-ant-api03-XXXXXXXX'); 
// Controller/ChatController.php

require_once __DIR__ . '/../Models/conversation.php';
require_once __DIR__ . '/../Models/message.php';
require_once __DIR__ . '/BadWordController.php';

class ChatController {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    private function jsonResponse(array $data): void {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers groupes — le titre stocke un JSON quand c'est un groupe
    // Format: {"groupe":true,"nom":"Mon groupe","membres":[1,2,3]}
    // ─────────────────────────────────────────────────────────────────────

    private function isGroupe(array $conv): bool {
        if (empty($conv['titre'])) return false;
        $data = json_decode($conv['titre'], true);
        return is_array($data) && !empty($data['groupe']);
    }

    private function getGroupeData(array $conv): ?array {
        if (empty($conv['titre'])) return null;
        $data = json_decode($conv['titre'], true);
        return (is_array($data) && !empty($data['groupe'])) ? $data : null;
    }

    private function userInGroupe(array $conv, int $id_user): bool {
        $data = $this->getGroupeData($conv);
        if (!$data) return false;
        return in_array($id_user, $data['membres'] ?? []);
    }

    private function getConvDisplayName(array $conv, int $currentUserId): string {
        $data = $this->getGroupeData($conv);
        if ($data) return $data['nom'] ?? 'Groupe';
        $otherUser = ($conv['id_user1'] == $currentUserId) ? $conv['id_user2'] : $conv['id_user1'];
        return 'Utilisateur #' . $otherUser;
    }

    // ─────────────────────────────────────────────────────────────────────
    // VIEWS
    // ─────────────────────────────────────────────────────────────────────

    public function conversations() {
        $id_user = (int)$_SESSION['user_id'];

        // Récupérer toutes les conversations de l'utilisateur
        $stmt = $this->pdo->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM messages m
                    WHERE m.id_conversation = c.id_conversation) AS total_messages,
                   (SELECT contenu FROM messages m
                    WHERE m.id_conversation = c.id_conversation
                    ORDER BY m.date_envoi DESC LIMIT 1) AS dernier_message
            FROM conversations c
            WHERE (c.id_user1 = :id_user OR c.id_user2 = :id_user)
            ORDER BY c.date_creation DESC
        ");
        $stmt->execute([':id_user' => $id_user]);
        $rawConvs = $stmt->fetchAll();

        // Inclure aussi les groupes où l'utilisateur est membre (stocké dans titre JSON)
        $stmt2 = $this->pdo->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM messages m
                    WHERE m.id_conversation = c.id_conversation) AS total_messages,
                   (SELECT contenu FROM messages m
                    WHERE m.id_conversation = c.id_conversation
                    ORDER BY m.date_envoi DESC LIMIT 1) AS dernier_message
            FROM conversations c
            WHERE c.titre LIKE '%\"groupe\":true%'
        ");
        $stmt2->execute();
        $groupeConvs = $stmt2->fetchAll();

        // Filtrer les groupes où l'utilisateur est membre
        foreach ($groupeConvs as $gc) {
            if ($this->userInGroupe($gc, $id_user)) {
                // Eviter les doublons
                $ids = array_column($rawConvs, 'id_conversation');
                if (!in_array($gc['id_conversation'], $ids)) {
                    $rawConvs[] = $gc;
                }
            }
        }

        $conversations = $rawConvs;

        // RECHERCHE
        $search = trim($_GET['search'] ?? '');
        if ($search !== '') {
            $mot = strtolower($search);
            $conversations = array_values(array_filter($conversations, function($conv) use ($mot) {
                $titre   = strtolower($conv['titre'] ?? '');
                $dernier = strtolower($conv['dernier_message'] ?? '');
                // Pour les groupes, chercher dans le nom JSON
                $data = json_decode($conv['titre'] ?? '', true);
                $nomGroupe = strtolower($data['nom'] ?? '');
                return str_contains($titre, $mot)
                    || str_contains($dernier, $mot)
                    || str_contains($nomGroupe, $mot);
            }));
        }

        // TRI
        $tri = $_GET['tri'] ?? 'date_desc';
        usort($conversations, function($a, $b) use ($tri) {
            if ($tri === 'messages') {
                return (int)$b['total_messages'] - (int)$a['total_messages'];
            }
            $diff = strtotime($a['date_creation']) - strtotime($b['date_creation']);
            return $tri === 'date_asc' ? $diff : -$diff;
        });

        $unread_count = 0;
        include __DIR__ . '/../Views/Frontoffice/Messagerie/conversations.php';
    }

    public function show(int $id_conversation) {
        $id_user = (int)$_SESSION['user_id'];

        $stmt = $this->pdo->prepare("SELECT * FROM conversations WHERE id_conversation = :id");
        $stmt->execute([':id' => $id_conversation]);
        $row = $stmt->fetch();

        if (!$row) {
            header('Location: messagerie_index.php?page=conversations');
            exit;
        }

        // Vérifier accès : membre direct ou membre du groupe
        $hasAccess = ($row['id_user1'] == $id_user || $row['id_user2'] == $id_user)
                   || $this->userInGroupe($row, $id_user);

        if (!$hasAccess) {
            header('Location: messagerie_index.php?page=conversations');
            exit;
        }

        $conversation = new Conversation(
            (int) $row['id_conversation'],
                  $row['date_creation'] ?? '',
                  $row['statut']        ?? 'active',
                  $row['titre']         ?? null,
            (int) $row['id_user1'],
            (int) $row['id_user2']
        );

        $stmt = $this->pdo->prepare("
            SELECT m.*,
                   GREATEST(0, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(m.date_envoi)) AS seconds_ago
            FROM messages m
            WHERE m.id_conversation = :id AND m.statut != 'deleted'
            ORDER BY m.date_envoi ASC
        ");
        $stmt->execute([':id' => $id_conversation]);
        $messages = $stmt->fetchAll();

        // Données du groupe si c'est un groupe
        $groupeData    = $this->getGroupeData($row);
        $isGroupe      = ($groupeData !== null);
        $convName      = $this->getConvDisplayName($row, $id_user);
        $groupeMembres = $groupeData['membres'] ?? [];

        include __DIR__ . '/../Views/Frontoffice/Messagerie/chat.php';
    }

    // ─────────────────────────────────────────────────────────────────────
    // MESSAGES
    // ─────────────────────────────────────────────────────────────────────

    public function sendMessage() {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $contenu         = trim($_POST['contenu'] ?? '');
        $id_user         = (int)$_SESSION['user_id'];

        
        if (strlen($contenu) > 5000) $this->jsonResponse(['error' => 'Message trop long']);

        $contenu = htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8');

        // Check for bad words and return a warning without saving
        $badWordChecker = new BadWordController();
        $foundWords = $badWordChecker->detect($contenu);

        if (!empty($foundWords)) {
            $label = count($foundWords) === 1 ? 'mot interdit détecté' : 'mots interdits détectés';
            $this->jsonResponse([
                'success'     => false,
                'warning'     => 'Attention : ' . $label . ' — ' . implode(', ', $foundWords),
                'found_words' => $foundWords,
            ]);
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO messages (contenu, date_envoi, statut, id_conversation, id_expediteur)
            VALUES (:contenu, NOW(), 'normal', :id_conversation, :id_expediteur)
        ");
        $ok = $stmt->execute([
            ':contenu'         => $contenu,
            ':id_conversation' => $id_conversation,
            ':id_expediteur'   => $id_user,
        ]);

        $this->jsonResponse(['success' => $ok]);
    }

    public function deleteMessage() {
        $id_message = (int)($_POST['id_message'] ?? 0);
        $id_user    = (int)$_SESSION['user_id'];

        $stmt = $this->pdo->prepare("
            UPDATE messages SET statut = 'deleted'
            WHERE id_message = :id AND id_expediteur = :user
        ");
        $this->jsonResponse(['success' => $stmt->execute([':id' => $id_message, ':user' => $id_user])]);
    }

    public function editMessage() {
        $id_message = (int)($_POST['id_message'] ?? 0);
        $contenu    = trim($_POST['contenu'] ?? '');
        $id_user    = (int)$_SESSION['user_id'];

        if (empty($contenu))        $this->jsonResponse(['error' => 'Message vide']);
        if (strlen($contenu) > 150) $this->jsonResponse(['error' => 'Message trop long']);

        $contenu = htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8');

        $stmt = $this->pdo->prepare("
            UPDATE messages SET contenu = :contenu
            WHERE id_message = :id
              AND id_expediteur = :user
              AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(date_envoi) <= 60
        ");
        $this->jsonResponse(['success' => $stmt->execute([
            ':contenu' => $contenu,
            ':id'      => $id_message,
            ':user'    => $id_user,
        ])]);
    }

    public function reportMessage() {
        $id_message = (int)($_POST['id_message'] ?? 0);
        if ($id_message <= 0) $this->jsonResponse(['error' => 'ID message invalide']);

        $stmt = $this->pdo->prepare("UPDATE messages SET statut = 'flagged' WHERE id_message = :id");
        $this->jsonResponse(['success' => $stmt->execute([':id' => $id_message])]);
    }

    public function translateMessage() {
        $id_message = (int)($_POST['id_message'] ?? 0);
        $target_lang = trim($_POST['target_lang'] ?? 'fr');
        
        if ($id_message <= 0) $this->jsonResponse(['error' => 'ID message invalide']);
        
        // Fetch message
        $stmt = $this->pdo->prepare("SELECT contenu FROM messages WHERE id_message = :id");
        $stmt->execute([':id' => $id_message]);
        $message = $stmt->fetch();
        
        if (!$message) $this->jsonResponse(['error' => 'Message non trouvé']);
        
        $text = $message['contenu'];
        
        // Detect source language using simple heuristics or external API
        $source_lang = $this->detectLanguage($text);
        
        // If source and target are the same, return original
        if ($source_lang === $target_lang) {
            $this->jsonResponse(['translated_text' => $text, 'source_lang' => $source_lang]);
        }
        
        // Translate using external service (DeepL, Claude API, etc.)
        $translated_text = $this->callTranslationService($text, $source_lang, $target_lang);
        
        if ($translated_text) {
            $this->jsonResponse(['translated_text' => $translated_text, 'source_lang' => $source_lang]);
        } else {
            $this->jsonResponse(['error' => 'Erreur lors de la traduction']);
        }
    }

    private function detectLanguage(string $text): string {
        // Simple language detection based on common words
        // In production, use a proper library or API
        
        $text_lower = strtolower($text);
        
        // Common French words
        $fr_words = ['le', 'la', 'de', 'et', 'est', 'que', 'pour', 'qui', 'dans', 'avec'];
        $fr_score = 0;
        foreach ($fr_words as $word) {
            if (str_contains($text_lower, $word)) $fr_score++;
        }
        
        // Common English words
        $en_words = ['the', 'and', 'is', 'for', 'with', 'that', 'have', 'this', 'but', 'not'];
        $en_score = 0;
        foreach ($en_words as $word) {
            if (str_contains($text_lower, $word)) $en_score++;
        }
        
        // Common Arabic words
        $ar_words = ['في', 'من', 'هو', 'أن', 'كان', 'على', 'إلى', 'هذا', 'التي', 'هذه'];
        $ar_score = 0;
        foreach ($ar_words as $word) {
            if (str_contains($text, $word)) $ar_score++;
        }
        
        if ($ar_score > $en_score && $ar_score > $fr_score) return 'ar';
        if ($en_score > $fr_score) return 'en';
        return 'fr';
    }

    private function callTranslationService(string $text, string $source_lang, string $target_lang): ?string {
        // Use Claude if API key is configured
        $claudeKey = getenv('CLAUDE_API_KEY') ?: false;
        if ($claudeKey) {
            $langMap = ['fr' => 'French', 'en' => 'English', 'ar' => 'Arabic'];
            $targetLangName = $langMap[$target_lang] ?? 'French';
            $sourceLangName = $langMap[$source_lang] ?? 'English';
            $result = $this->translateWithClaude($text, $sourceLangName, $targetLangName, $claudeKey);
            if ($result) {
                return $result;
            }
        }
        
        // Fallback: use Google Translate public endpoint
        return $this->translateWithGoogle($text, $source_lang, $target_lang);
    }

    private function translateWithClaude(string $text, string $sourceLang, string $targetLang, string $apiKey): ?string {
        $payload = [
            'model' => 'claude-3.5-sonic',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => "Translate this $sourceLang text to $targetLang. Return only the translation, no explanation:\n\n$text"
            ]]
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.anthropic.com/v1/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'Anthropic-Version: 2024-01-01'
            ],
            CURLOPT_TIMEOUT => 20,
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['completion'])) {
                return trim($data['completion']);
            }
            if (isset($data['choices'][0]['message']['content'])) {
                return trim($data['choices'][0]['message']['content']);
            }
        }
        return null;
    }

    private function translateWithGoogle(string $text, string $source_lang, string $target_lang): ?string {
        $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=' . rawurlencode($source_lang) . '&tl=' . rawurlencode($target_lang) . '&dt=t&q=' . rawurlencode($text);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        
        if (!$response) {
            return null;
        }
        $data = json_decode($response, true);
        if (!is_array($data) || !isset($data[0])) {
            return null;
        }
        $translated = '';
        foreach ($data[0] as $segment) {
            $translated .= $segment[0] ?? '';
        }
        return trim($translated);
    }

    public function getUnreadCounts() {
        $id_user = (int)$_SESSION['user_id'];
        
        $stmt = $this->pdo->prepare("
            SELECT c.id_conversation,
                   c.titre,
                   c.id_user1,
                   c.id_user2,
                   COUNT(m.id_message) AS unread_count,
                   (SELECT contenu FROM messages m2
                    WHERE m2.id_conversation = c.id_conversation
                      AND m2.id_expediteur != :user
                      AND m2.statut NOT IN ('deleted')
                    ORDER BY m2.date_envoi DESC LIMIT 1) AS last_message
            FROM conversations c
            LEFT JOIN messages m ON c.id_conversation = m.id_conversation
                   AND m.id_expediteur != :user
                   AND m.statut NOT IN ('deleted')
            WHERE (c.id_user1 = :user OR c.id_user2 = :user)
            GROUP BY c.id_conversation
        ");
        $stmt->execute([':user' => $id_user]);
        $rows = $stmt->fetchAll();
        
        $result = [];
        foreach ($rows as $row) {
            $name = $this->getConvDisplayName($row, $id_user);
            $raw_preview = is_string($row['last_message']) ? $row['last_message'] : '';
            $decoded_preview = json_decode($raw_preview, true);
            if (is_array($decoded_preview)) {
                if (!empty($decoded_preview['ephemeral'])) {
                    $preview = '🔥 Message éphémère';
                } elseif (isset($decoded_preview['type']) && $decoded_preview['type'] === 'file') {
                    $preview = '📎 ' . ($decoded_preview['name'] ?? 'Fichier');
                } else {
                    $preview = substr($raw_preview, 0, 80);
                }
            } else {
                $preview = substr($raw_preview, 0, 80);
            }
            $result[(int)$row['id_conversation']] = [
                'count' => (int)$row['unread_count'],
                'name' => $name,
                'preview' => $preview
            ];
        }
        
        $this->jsonResponse(['unread_counts' => $result]);
    }

    public function uploadFile() {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $id_user = (int)$_SESSION['user_id'];
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['error' => 'Fichier non valide']);
        }
        
        $file = $_FILES['file'];
        $maxSize = 10 * 1024 * 1024; // 10 MB
        
        if ($file['size'] > $maxSize) {
            $this->jsonResponse(['error' => 'Fichier trop volumineux (max 10 MB)']);
        }
        
        // Verify conversation access
        $stmt = $this->pdo->prepare("SELECT * FROM conversations WHERE id_conversation = :id");
        $stmt->execute([':id' => $id_conversation]);
        $conv = $stmt->fetch();
        
        if (!$conv) {
    $this->jsonResponse(['error' => 'Conversation introuvable']);
}
$directAccess = ($conv['id_user1'] == $id_user || $conv['id_user2'] == $id_user);
$groupeAccess = false;
if (!$directAccess && !empty($conv['titre'])) {
    $groupeData = json_decode($conv['titre'], true);
    if (is_array($groupeData) && !empty($groupeData['groupe'])) {
        $groupeAccess = in_array($id_user, $groupeData['membres'] ?? []);
    }
}
if (!$directAccess && !$groupeAccess) {
    $this->jsonResponse(['error' => 'Accès non autorisé']);
}
        
        // Create uploads directory
        $uploadDir = __DIR__ . '/../uploads/messagerie/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        // Generate unique filename
        $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('file_') . '.' . $fileExt;
        $filePath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $this->jsonResponse(['error' => 'Erreur lors du téléchargement']);
        }
        
        // Insert file reference as message
        $fileUrl = 'uploads/messagerie/' . $fileName;
        $fileInfo = json_encode([
            'type' => 'file',
            'name' => $file['name'],
            'size' => $file['size'],
            'url' => $fileUrl
        ]);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO messages (contenu, date_envoi, statut, id_conversation, id_expediteur)
            VALUES (:contenu, NOW(), 'normal', :id_conversation, :id_expediteur)
        ");
        
        $ok = $stmt->execute([
            ':contenu' => $fileInfo,
            ':id_conversation' => $id_conversation,
            ':id_expediteur' => $id_user,
        ]);
        
        $this->jsonResponse([
            'success' => $ok,
            'file_url' => $fileUrl,
            'file_name' => $file['name'],
            'file_size' => $file['size']
        ]);
    }

    public function sendEphemeralMessage() {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? '');
        $id_user = (int)$_SESSION['user_id'];
        $expiration = trim($_POST['expiration'] ?? '24h'); // 1h, 24h, or 'read'
        
        if (empty($contenu)) $this->jsonResponse(['error' => 'Message vide']);
        if (strlen($contenu) > 150) $this->jsonResponse(['error' => 'Message trop long']);
        
        $contenu = htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8');
        
        // Encode ephemeral info in contenu with a marker
        $ephemeralData = json_encode([
            'ephemeral' => true,
            'expiration' => $expiration,
            'created_at' => time(),
            'text' => $contenu
        ]);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO messages (contenu, date_envoi, statut, id_conversation, id_expediteur)
            VALUES (:contenu, NOW(), 'ephemeral', :id_conversation, :id_expediteur)
        ");
        
        $ok = $stmt->execute([
            ':contenu' => $ephemeralData,
            ':id_conversation' => $id_conversation,
            ':id_expediteur' => $id_user,
        ]);
        
        $this->jsonResponse(['success' => $ok]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONVERSATIONS NORMALES
    // ─────────────────────────────────────────────────────────────────────

    public function createConversation() {
        $id_user1 = (int)$_SESSION['user_id'];
        $id_user2 = (int)($_POST['user_id'] ?? 0);
        $titre    = trim($_POST['titre'] ?? '');

        // id_user2 = 0 is allowed when conversation is identified by titre (name-based)
        if ($id_user2 < 0)           $this->jsonResponse(['error' => 'ID utilisateur invalide']);
        if ($id_user2 !== 0 && $id_user1 === $id_user2)
            $this->jsonResponse(['error' => 'Vous ne pouvez pas vous écrire à vous-même']);

        $titre = $titre !== '' ? htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') : null;

        try {
            // If titre given, check a conversation with same titre doesn't already exist for this user
            if ($titre !== null) {
                $stmt = $this->pdo->prepare("
                    SELECT id_conversation FROM conversations
                    WHERE id_user1 = :u1 AND titre = :titre
                ");
                $stmt->execute([':u1' => $id_user1, ':titre' => $titre]);
                $existing = $stmt->fetch();
                if ($existing) {
                    $this->jsonResponse(['success' => true, 'id_conversation' => (int)$existing['id_conversation']]);
                }
            }

            // If id_user2 is 0, use id_user1 to satisfy FK constraint
            $u2_to_insert = ($id_user2 === 0) ? $id_user1 : $id_user2;

            $stmt = $this->pdo->prepare("
                INSERT INTO conversations (id_user1, id_user2, statut, titre, date_creation)
                VALUES (:u1, :u2, 'active', :titre, NOW())
            ");
            $ok = $stmt->execute([':u1' => $id_user1, ':u2' => $u2_to_insert, ':titre' => $titre]);
            if ($ok) {
                $this->jsonResponse(['success' => true, 'id_conversation' => (int)$this->pdo->lastInsertId()]);
            } else {
                $this->jsonResponse(['error' => 'Echec création']);
            }
        } catch (PDOException $e) {
            $this->jsonResponse(['error' => 'PDO: ' . $e->getMessage()]);
        }
    }

    public function renameConversation() {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $titre           = trim($_POST['titre'] ?? '');
        $id_user         = (int)$_SESSION['user_id'];

        if (empty($titre)) $this->jsonResponse(['error' => 'Titre requis']);

        $stmt = $this->pdo->prepare("
            SELECT * FROM conversations
            WHERE id_conversation = :id AND (id_user1 = :user OR id_user2 = :user)
        ");
        $stmt->execute([':id' => $id_conversation, ':user' => $id_user]);
        $conv = $stmt->fetch();

        // Pour les groupes, vérifier aussi si membre
        if (!$conv) {
            $stmt = $this->pdo->prepare("SELECT * FROM conversations WHERE id_conversation = :id");
            $stmt->execute([':id' => $id_conversation]);
            $conv = $stmt->fetch();
            if (!$conv || !$this->userInGroupe($conv, $id_user)) {
                $this->jsonResponse(['error' => 'Non autorisé']);
            }
        }

        // Si c'est un groupe, mettre à jour le nom dans le JSON
        if ($conv && $this->isGroupe($conv)) {
            $data = $this->getGroupeData($conv);
            $data['nom'] = $titre;
            $newTitre = json_encode($data, JSON_UNESCAPED_UNICODE);
            $stmt = $this->pdo->prepare("UPDATE conversations SET titre = :titre WHERE id_conversation = :id");
            $this->jsonResponse(['success' => $stmt->execute([':titre' => $newTitre, ':id' => $id_conversation])]);
        }

        $stmt = $this->pdo->prepare("UPDATE conversations SET titre = :titre WHERE id_conversation = :id");
        $this->jsonResponse(['success' => $stmt->execute([':titre' => $titre, ':id' => $id_conversation])]);
    }

    public function deleteConversationUser() {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $id_user         = (int)$_SESSION['user_id'];

        $stmt = $this->pdo->prepare("
            SELECT id_conversation FROM conversations
            WHERE id_conversation = :id AND (id_user1 = :user OR id_user2 = :user)
        ");
        $stmt->execute([':id' => $id_conversation, ':user' => $id_user]);
        if (!$stmt->fetch()) $this->jsonResponse(['error' => 'Non autorisé']);

        $stmt = $this->pdo->prepare("DELETE FROM conversations WHERE id_conversation = :id");
        $this->jsonResponse(['success' => $stmt->execute([':id' => $id_conversation])]);
    }

    public function searchUser() {
        $search = trim($_POST['search'] ?? '');
        if (empty($search)) $this->jsonResponse(['users' => []]);

        $me    = (int)$_SESSION['user_id'];
        $users = [];

        try {
            // Try to search in users table by name/prenom/email
            $stmt = $this->pdo->prepare("
                SELECT id,
                       CONCAT(COALESCE(prenom,''), ' ', COALESCE(nom,'')) AS full_name,
                       email
                FROM users
                WHERE id != :me
                  AND (
                        nom     LIKE :q
                     OR prenom  LIKE :q
                     OR email   LIKE :q
                     OR CONCAT(prenom,' ',nom) LIKE :q
                     OR CONCAT(nom,' ',prenom) LIKE :q
                  )
                LIMIT 8
            ");
            $stmt->execute([':me' => $me, ':q' => '%' . $search . '%']);
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                $displayName = trim($row['full_name']);
                if ($displayName === '') $displayName = $row['email'] ?? ('Utilisateur #' . $row['id_user']);
                $users[] = [
                    'id_user' => (int)$row['id'],
                    'nom'     => $displayName,
                    'email'   => $row['email'] ?? '',
                ];
            }
        } catch (\Exception $e) {
            // Fallback: if table/columns don't exist, return empty
            $users = [];
        }

        $this->jsonResponse(['users' => $users]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GROUPES
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Créer un groupe
     * POST: nom_groupe, membres[] (liste d'IDs)
     */
    public function createGroupe() {
    $id_user   = (int)$_SESSION['user_id'];
    $nomGroupe = trim($_POST['nom'] ?? '');  // Changed from 'nom_groupe' to 'nom'
    $membres   = $_POST['membres'] ?? [];

    if (empty($nomGroupe)) $this->jsonResponse(['error' => 'Nom du groupe requis']);

    // Nettoyer et valider les membres
    $membres = array_map('intval', (array)$membres);
    $membres = array_filter($membres, fn($m) => $m > 0 && $m !== $id_user);
    $membres = array_values(array_unique($membres));

    if (empty($membres)) $this->jsonResponse(['error' => 'Ajoutez au moins un membre']);

    // Le créateur est toujours dans le groupe
    $membres[] = $id_user;
    $membres   = array_unique($membres);

    // Stocker les données du groupe dans le champ titre en JSON
    $titreJson = json_encode([
        'groupe'  => true,
        'nom'     => $nomGroupe,
        'membres' => array_values($membres),
        'createur' => $id_user,
    ], JSON_UNESCAPED_UNICODE);

    try {
        // id_user2 set to creator ID to satisfy FK constraint
        $stmt = $this->pdo->prepare("
            INSERT INTO conversations (id_user1, id_user2, statut, titre, date_creation)
            VALUES (:u1, :u1, 'active', :titre, NOW())
        ");
        $ok = $stmt->execute([':u1' => $id_user, ':titre' => $titreJson]);

        if ($ok) {
            $this->jsonResponse([
                'success'         => true,
                'id_conversation' => (int)$this->pdo->lastInsertId(),
            ]);
        } else {
            $this->jsonResponse(['error' => 'Echec création groupe']);
        }
    } catch (PDOException $e) {
        $this->jsonResponse(['error' => 'PDO: ' . $e->getMessage()]);
    }
}

    /**
     * Inviter un membre dans un groupe existant
     * POST: id_conversation, user_id
     */
    public function inviterMembre() {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $newMembre       = (int)($_POST['user_id'] ?? 0);
        $id_user         = (int)$_SESSION['user_id'];

        if ($newMembre <= 0) $this->jsonResponse(['error' => 'ID utilisateur invalide']);

        $stmt = $this->pdo->prepare("SELECT * FROM conversations WHERE id_conversation = :id");
        $stmt->execute([':id' => $id_conversation]);
        $conv = $stmt->fetch();

        if (!$conv) $this->jsonResponse(['error' => 'Conversation introuvable']);
        if (!$this->isGroupe($conv)) $this->jsonResponse(['error' => 'Ce n\'est pas un groupe']);
        if (!$this->userInGroupe($conv, $id_user) && $conv['id_user1'] != $id_user)
            $this->jsonResponse(['error' => 'Non autorisé']);

        $data = $this->getGroupeData($conv);

        if (in_array($newMembre, $data['membres'])) {
            $this->jsonResponse(['error' => 'Cet utilisateur est déjà dans le groupe']);
        }

        $data['membres'][] = $newMembre;
        $newTitre = json_encode($data, JSON_UNESCAPED_UNICODE);

        $stmt = $this->pdo->prepare("UPDATE conversations SET titre = :titre WHERE id_conversation = :id");
        $ok   = $stmt->execute([':titre' => $newTitre, ':id' => $id_conversation]);

        $this->jsonResponse(['success' => $ok]);
    }

    /**
     * Quitter un groupe
     * POST: id_conversation
     */
    public function quitterGroupe() {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $id_user         = (int)$_SESSION['user_id'];

        $stmt = $this->pdo->prepare("SELECT * FROM conversations WHERE id_conversation = :id");
        $stmt->execute([':id' => $id_conversation]);
        $conv = $stmt->fetch();

        if (!$conv || !$this->isGroupe($conv)) $this->jsonResponse(['error' => 'Groupe introuvable']);

        $data = $this->getGroupeData($conv);
        $data['membres'] = array_values(array_filter($data['membres'], fn($m) => $m !== $id_user));

        // Si plus personne, supprimer le groupe
        if (empty($data['membres'])) {
            $stmt = $this->pdo->prepare("DELETE FROM conversations WHERE id_conversation = :id");
            $stmt->execute([':id' => $id_conversation]);
            $this->jsonResponse(['success' => true, 'deleted' => true]);
        }

        $newTitre = json_encode($data, JSON_UNESCAPED_UNICODE);
        $stmt = $this->pdo->prepare("UPDATE conversations SET titre = :titre WHERE id_conversation = :id");
        $ok   = $stmt->execute([':titre' => $newTitre, ':id' => $id_conversation]);

        $this->jsonResponse(['success' => $ok]);
    }

    /**
     * GIT / HISTORY PANEL
     * Shows full audit trail of a conversation
     */
    /**
     * GIT / HISTORY PANEL
     * Shows full audit trail of a conversation
     */
    public function git(int $id_conversation) {
        $id_user = (int)$_SESSION['user_id'];

        $stmt = $this->pdo->prepare("SELECT * FROM conversations WHERE id_conversation = :id");
        $stmt->execute([':id' => $id_conversation]);
        $row = $stmt->fetch();

        if (!$row) {
            header('Location: messagerie_index.php?page=conversations');
            exit;
        }

        // Auth check
        $hasAccess = ($row['id_user1'] == $id_user || $row['id_user2'] == $id_user)
                   || $this->userInGroupe($row, $id_user);

        if (!$hasAccess) {
            header('Location: messagerie_index.php?page=conversations');
            exit;
        }

        // Fetch ALL messages including deleted ones
        $stmt = $this->pdo->prepare("
            SELECT m.*, u.nom, u.prenom, u.avatar
            FROM messages m
            LEFT JOIN users u ON m.id_expediteur = u.id
            WHERE m.id_conversation = :id
            ORDER BY m.date_envoi ASC
        ");
        $stmt->execute([':id' => $id_conversation]);
        $allMessages = $stmt->fetchAll();

        // Separate commits from normal messages (optional, or just format all as commits)
        $commits = [];
        foreach ($allMessages as $m) {
            if ($m['statut'] === 'commit') {
                $commits[] = $m;
            }
        }

        $convName = $this->getConvDisplayName($row, $id_user);

        // Include the view
        include __DIR__ . '/../Views/Frontoffice/Messagerie/git.php';
    }

    /**
     * CREATE A NEW COMMIT
     */
    public function commitMessage(int $id_conversation) {
        $id_user = (int)$_SESSION['user_id'];
        $message = trim($_POST['message'] ?? '');
        $branch  = trim($_POST['branch']  ?? 'main');

        if (empty($message)) {
            $this->jsonResponse(['error' => 'Message de commit requis']);
        }

        $fileUrl = null;
        $fileName = null;
        $fileSize = 0;

        // Handle file upload if present
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $uploadDir = __DIR__ . '/../uploads/messagerie/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileNameGenerated = uniqid('commit_') . '.' . $fileExt;
            $filePath = $uploadDir . $fileNameGenerated;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $fileUrl = 'uploads/messagerie/' . $fileNameGenerated;
                $fileName = $file['name'];
                $fileSize = $file['size'];
            }
        }

        // Prepare commit content as JSON
        $commitData = json_encode([
            'type'    => 'commit',
            'message' => $message,
            'branch'  => $branch,
            'file'    => $fileUrl ? [
                'name' => $fileName,
                'url'  => $fileUrl,
                'size' => $fileSize
            ] : null
        ], JSON_UNESCAPED_UNICODE);

        $stmt = $this->pdo->prepare("
            INSERT INTO messages (contenu, date_envoi, statut, id_conversation, id_expediteur)
            VALUES (:contenu, NOW(), 'commit', :id_conversation, :id_expediteur)
        ");
        
        $ok = $stmt->execute([
            ':contenu'         => $commitData,
            ':id_conversation' => $id_conversation,
            ':id_expediteur'   => $id_user,
        ]);

        $this->jsonResponse(['success' => $ok]);
    }
}
?>