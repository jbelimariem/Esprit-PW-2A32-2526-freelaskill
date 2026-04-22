<?php
<<<<<<< HEAD
require_once __DIR__ . '/../config.php';
=======
// Models/AdminDashboard.php — Modèle spécifique pour le dashboard admin
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';
>>>>>>> faca6fd (sss)

class AdminDashboard {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

<<<<<<< HEAD
    public function getAllJobs() {
        $stmt = $this->pdo->query("SELECT * FROM offres_emploi ORDER BY date_creation DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
=======
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM offres_emploi ORDER BY date_creation DESC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) { $offres[] = new JobOffer($row); }
        return $offres;
>>>>>>> faca6fd (sss)
    }

    public function search($q, $d) {
        $sql = "SELECT * FROM offres_emploi WHERE 1=1";
        $params = [];
<<<<<<< HEAD
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
=======
        if (!empty($q)) { $sql .= " AND titre LIKE ?"; $params[] = "%$q%"; }
        if (!empty($d)) { $sql .= " AND DATE(date_creation) = ?"; $params[] = $d; }
        $sql .= " ORDER BY date_creation DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) { $offres[] = new JobOffer($row); }
        return $offres;
>>>>>>> faca6fd (sss)
    }

    public function getByStatut($statut) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE statut = ? ORDER BY date_creation DESC");
        $stmt->execute([$statut]);
<<<<<<< HEAD
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
=======
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) { $offres[] = new JobOffer($row); }
        return $offres;
>>>>>>> faca6fd (sss)
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
