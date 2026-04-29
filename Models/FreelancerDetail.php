<?php
// Models/FreelancerDetail.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class FreelancerDetail {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function getJob($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ? AND statut = 'approved'");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new JobOffer($row) : null;
    }

    public function applyJob($job_id, $name, $email, $job_title) {
        $stmt = $this->pdo->prepare("INSERT INTO job_applications (job_id, name, email, job_title, created_at, status) VALUES (?, ?, ?, ?, NOW(), 'pending')");
        return $stmt->execute([$job_id, $name, $email, $job_title]);
    }

    public function hasApplied($job_id, $email) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM job_applications WHERE job_id = ? AND email = ?");
        $stmt->execute([$job_id, $email]);
        return $stmt->fetchColumn() > 0;
    }
}
