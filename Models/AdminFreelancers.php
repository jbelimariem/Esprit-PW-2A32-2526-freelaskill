<?php
// Models/AdminFreelancers.php
require_once __DIR__ . '/../config.php';

class AdminFreelancers {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function getFreelancersWithApplications($searchQuery = '') {
        // Fetch all freelancers
        $sql = "SELECT id, nom, prenom, email, role FROM users WHERE role = 'freelancer'";
        $params = [];
        
        if (!empty($searchQuery)) {
            $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
            $searchTerm = "%" . $searchQuery . "%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $sql .= " ORDER BY nom ASC";
        
        $stmtUsers = $this->pdo->prepare($sql);
        $stmtUsers->execute($params);
        $freelancers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        // Fetch applications with job details
        $stmtApps = $this->pdo->prepare("
            SELECT a.email, a.status, o.titre as job_title 
            FROM job_applications a
            JOIN offres_emploi o ON a.job_id = o.id
        ");
        $stmtApps->execute();
        $allApps = $stmtApps->fetchAll(PDO::FETCH_ASSOC);

        // Map applications to freelancers
        $appsByEmail = [];
        foreach ($allApps as $app) {
            $email = $app['email'];
            if (!isset($appsByEmail[$email])) {
                $appsByEmail[$email] = [];
            }
            $appsByEmail[$email][] = $app;
        }

        foreach ($freelancers as &$free) {
            $email = $free['email'];
            $free['applications'] = $appsByEmail[$email] ?? [];
            $free['app_count'] = count($free['applications']);
        }

        return $freelancers;
    }

    public function getGlobalStats() {
        $stmtFree = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'freelancer'");
        $totalFreelancers = $stmtFree->fetchColumn();

        $stmtApps = $this->pdo->query("SELECT COUNT(*) FROM job_applications");
        $totalApps = $stmtApps->fetchColumn();

        return [
            'total_freelancers' => $totalFreelancers,
            'total_applications' => $totalApps
        ];
    }
}
