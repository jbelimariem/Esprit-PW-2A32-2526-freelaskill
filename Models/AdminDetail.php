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
    public function updateApplicationStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
