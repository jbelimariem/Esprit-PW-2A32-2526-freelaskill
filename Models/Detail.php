<?php
<<<<<<< HEAD
require_once __DIR__ . '/../config.php';
=======
// Models/Detail.php — Modèle spécifique pour le détail
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';
require_once __DIR__ . '/JobApplication.php';
>>>>>>> faca6fd (sss)

class Detail {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

<<<<<<< HEAD
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
=======
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new JobOffer($row) : null;
    }

    // --- Applications ---
    public function getApplicationsByJobId($job_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM applications WHERE job_id = ? ORDER BY created_at DESC");
        $stmt->execute([$job_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $apps = [];
        foreach ($results as $row) {
            $apps[] = new JobApplication($row);
        }
        return $apps;
    }

    public function updateApplicationStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
>>>>>>> faca6fd (sss)
    }
}
