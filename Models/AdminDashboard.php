<?php
require_once __DIR__ . '/../config.php';

class AdminDashboard {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function getAllJobs() {
        $stmt = $this->pdo->query("SELECT * FROM offres_emploi ORDER BY date_creation DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($q, $d) {
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
        $sql .= " ORDER BY date_creation DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByStatut($statut) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE statut = ? ORDER BY date_creation DESC");
        $stmt->execute([$statut]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll() {
        return $this->pdo->query("SELECT COUNT(*) FROM offres_emploi")->fetchColumn();
    }

    public function countByStatut($s) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM offres_emploi WHERE statut = ?");
        $stmt->execute([$s]);
        return $stmt->fetchColumn();
    }
}
