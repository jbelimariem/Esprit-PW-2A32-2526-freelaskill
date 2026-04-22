<?php
// Models/FreelancerApplications.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';
require_once __DIR__ . '/JobApplication.php';

class FreelancerApplications {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function getMyApplications() {
        // Since no login, we fetch all applications
        // We join with offres_emploi to get job details
        $sql = "SELECT a.*, o.titre as job_title, o.budget, o.delai, o.date_creation 
                FROM applications a 
                JOIN offres_emploi o ON a.job_id = o.id 
                ORDER BY a.created_at DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
