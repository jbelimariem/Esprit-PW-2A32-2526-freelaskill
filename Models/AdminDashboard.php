<?php
// Models/AdminDashboard.php — Modèle spécifique pour le dashboard admin
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class AdminDashboard {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM offres_emploi ORDER BY date_creation DESC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) { $offres[] = new JobOffer($row); }
        return $offres;
    }

    public function search($q, $d) {
        $sql = "SELECT * FROM offres_emploi WHERE 1=1";
        $params = [];
        if (!empty($q)) { $sql .= " AND titre LIKE ?"; $params[] = "%$q%"; }
        if (!empty($d)) { $sql .= " AND DATE(date_creation) = ?"; $params[] = $d; }
        $sql .= " ORDER BY date_creation DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) { $offres[] = new JobOffer($row); }
        return $offres;
    }

    public function getByStatut($statut) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE statut = ? ORDER BY date_creation DESC");
        $stmt->execute([$statut]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) { $offres[] = new JobOffer($row); }
        return $offres;
    }

    public function countAll() {
        return $this->pdo->query("SELECT COUNT(*) FROM offres_emploi")->fetchColumn();
    }

    public function countByStatut($s) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM offres_emploi WHERE statut = ?");
        $stmt->execute([$s]);
        return $stmt->fetchColumn();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM offres_emploi WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
