<?php
// Models/FreelancerList.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class FreelancerList {
    private $pdo;

    // Initialise la connexion PDO à la base de données
    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Retourne la liste des offres avec statut 'approved', filtrées par mot-clé (titre/description/compétences) et/ou fourchette de budget
    public function getApprovedJobs($query = '', $minPrice = 0, $maxPrice = 999999) {
        $sql = "SELECT * FROM offres_emploi WHERE statut = 'approved'";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (titre LIKE ? OR description LIKE ? OR competences LIKE ?)";
            $p = "%$query%";
            $params[] = $p; $params[] = $p; $params[] = $p;
        }

        if ($minPrice > 0) {
            $sql .= " AND budget >= ?";
            $params[] = $minPrice;
        }
        if ($maxPrice < 999999) {
            $sql .= " AND budget <= ?";
            $params[] = $maxPrice;
        }

        $sql .= " ORDER BY date_creation DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        $offres = [];
        foreach ($results as $row) {
            $offres[] = new JobOffer($row);
        }
        return $offres;
    }
}
