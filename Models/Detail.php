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
    public function updateApplicationStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
