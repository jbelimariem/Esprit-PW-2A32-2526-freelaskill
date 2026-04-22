<?php
require_once __DIR__ . '/../config.php';

class Detail {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function getJobById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getApplicationsByJobId($job_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM applications WHERE job_id = ? ORDER BY created_at DESC");
        $stmt->execute([job_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateApplicationStatus($app_id, $status) {
        $stmt = $this->pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $app_id]);
    }
}
