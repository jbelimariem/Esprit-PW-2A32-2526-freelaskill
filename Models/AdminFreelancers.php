<?php
// Models/AdminFreelancers.php
require_once __DIR__ . '/../config.php';

class AdminFreelancers {
    private $pdo;

    // Initialise la connexion PDO à la base de données
    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Retourne la liste des freelancers avec leurs candidatures attachées, filtrés par nom/prénom/email si recherche active
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

    // Retourne le nombre total de freelancers et le nombre total de candidatures (pour les cartes statistiques)
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

    // Retourne la répartition des freelancers par statut : active, pending, suspended (pour le graphique camembert)
    public function getFreelancersStatusCounts() {
        $stmt = $this->pdo->query("SELECT status, COUNT(*) as count FROM users WHERE role = 'freelancer' GROUP BY status");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $counts = [
            'active' => 0,
            'pending' => 0,
            'suspended' => 0
        ];
        
        foreach ($results as $row) {
            $status = strtolower($row['status']);
            if (isset($counts[$status])) {
                $counts[$status] = (int)$row['count'];
            }
        }
        
        return $counts;
    }

    // Retourne le nombre de candidatures par jour sur les 14 derniers jours (pour la courbe d'évolution)
    public function getFreelancersGrowth() {
        $stmt = $this->pdo->query("
            SELECT DATE(created_at) as date_insc, COUNT(*) as count 
            FROM job_applications 
            GROUP BY DATE(created_at) 
            ORDER BY DATE(created_at) ASC 
            LIMIT 14
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
