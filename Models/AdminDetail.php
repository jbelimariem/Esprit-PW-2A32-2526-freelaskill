<?php
// Models/AdminDetail.php — Modèle spécifique pour le détail admin
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';
require_once __DIR__ . '/JobApplication.php';

class AdminDetail {
    private $pdo;

    // Initialise la connexion PDO à la base de données
    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Retourne un objet JobOffer par son ID (sans filtre de statut — l'admin voit toutes les offres)
    public function getJobById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new JobOffer($row) : null;
    }

    // Retourne toutes les candidatures liées à une offre (table 'job_applications'), triées par date décroissante
    public function getApplicationsByJobId($job_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM job_applications WHERE job_id = ? ORDER BY created_at DESC");
        $stmt->execute([$job_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $apps = [];
        foreach ($results as $row) { $apps[] = new JobApplication($row); }
        return $apps;
    }

    // Met à jour le statut d'une candidature ('approved' ou 'rejected') dans la table 'job_applications'
    // Et crée automatiquement une conversation entre le freelancer et le client si 'approved'
    public function updateApplicationStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $id]);

        // ── Création automatique de conversation quand status = 'approved' ──
        if ($result && $status === 'approved') {
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

                $stmt3 = $this->pdo->prepare("
                    SELECT id_conversation FROM conversations
                    WHERE (id_user1 = ? AND id_user2 = ?)
                       OR (id_user1 = ? AND id_user2 = ?)
                ");
                $stmt3->execute([$freelancer_id, $client_id, $client_id, $freelancer_id]);

                if (!$stmt3->fetch()) {
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
