<?php
// Models/Detail.php — Modèle spécifique pour le détail
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';
require_once __DIR__ . '/JobApplication.php';

class Detail {
    private $pdo;

    // Initialise la connexion PDO à la base de données
    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Retourne un objet JobOffer par son ID (sans filtre de statut — côté client)
    public function getJobById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new JobOffer($row) : null;
    }

    // --- Applications ---
    // Retourne toutes les candidatures d'une offre via JOIN avec users (pour récupérer le freelancer_id), triées par date
    public function getApplicationsByJobId($job_id) {
        $stmt = $this->pdo->prepare("
            SELECT ja.*, u.id AS freelancer_id 
            FROM job_applications ja 
            LEFT JOIN users u ON ja.email = u.email 
            WHERE ja.job_id = ? 
            ORDER BY ja.created_at DESC
        ");
        $stmt->execute([$job_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $apps = [];
        foreach ($results as $row) {
            $apps[] = new JobApplication($row);
        }
        return $apps;
    }

    // Met à jour le statut d'une candidature dans job_applications ('approved' ou 'rejected')
    // Et crée automatiquement une conversation entre le freelancer et le client si 'approved'
    public function updateApplicationStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $id]);

        // ── Création automatique de conversation quand status = 'approved' ──
        if ($result && $status === 'approved') {
            // 1. Récupérer le freelancer_id et le client_id
            $stmt2 = $this->pdo->prepare("
                SELECT ja.user_id AS freelancer_id, jo.client_id
                FROM job_applications ja
                JOIN job_offer jo ON ja.job_id = jo.id
                WHERE ja.id = ?
            ");
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);

            if ($row && $row['freelancer_id'] && $row['client_id']) {
                $freelancer_id = (int)$row['freelancer_id'];
                $client_id     = (int)$row['client_id'];

                // 2. Vérifier si une conversation existe déjà entre ces deux utilisateurs
                $stmt3 = $this->pdo->prepare("
                    SELECT id_conversation FROM conversations
                    WHERE (id_user1 = ? AND id_user2 = ?)
                       OR (id_user1 = ? AND id_user2 = ?)
                ");
                $stmt3->execute([$freelancer_id, $client_id, $client_id, $freelancer_id]);

                if (!$stmt3->fetch()) {
                    // 3. Créer la conversation
                    $stmt4 = $this->pdo->prepare("
                        INSERT INTO conversations (id_user1, id_user2, statut, date_creation)
                        VALUES (?, ?, 'active', NOW())
                    ");
                    $stmt4->execute([$freelancer_id, $client_id]);
                }
            }
        }

        return $result;
    }
}
