<?php
// Models/AdminDetail.php — Modèle spécifique pour le détail admin
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';
require_once __DIR__ . '/JobApplication.php';

class AdminDetail {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new JobOffer($row) : null;
    }

    public function getApplicationsByJobId($job_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM applications WHERE job_id = ? ORDER BY created_at DESC");
        $stmt->execute([$job_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $apps = [];
        foreach ($results as $row) { $apps[] = new JobApplication($row); }
        return $apps;
    }

    public function updateApplicationStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
