<?php
require_once __DIR__ . '/../config.php';

class ListModel { // Renamed to ListModel because 'List' is a reserved keyword in PHP
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM offres_emploi ORDER BY date_creation DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($q, $d, $maxBudget = null) {
        $sql = "SELECT * FROM offres_emploi WHERE 1=1";
        $params = [];
        if (!empty($q)) {
            $sql .= " AND (titre LIKE ? OR description LIKE ?)";
            $params[] = "%$q%"; $params[] = "%$q%";
        }
        if (!empty($d)) {
            $sql .= " AND DATE(date_creation) = ?";
            $params[] = $d;
        }
        if (!empty($maxBudget)) {
            $sql .= " AND budget <= ?";
            $params[] = $maxBudget;
        }
        $sql .= " ORDER BY date_creation DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
