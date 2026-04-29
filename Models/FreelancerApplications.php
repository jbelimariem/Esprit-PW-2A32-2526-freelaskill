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

    public function getMyApplications($search = '') {
        // Since no login, we fetch all applications
        // We join with offres_emploi to get job details
        $sql = "SELECT a.*, o.titre as job_title, o.budget, o.delai, o.date_creation 
                FROM job_applications a 
                JOIN offres_emploi o ON a.job_id = o.id";
        
        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE o.titre LIKE ? OR DATE(a.created_at) = ?";
            $params[] = "%$search%";
            $params[] = $search;
        }

        $sql .= " ORDER BY a.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelApplication($id) {
        $stmt = $this->pdo->prepare("DELETE FROM job_applications WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
