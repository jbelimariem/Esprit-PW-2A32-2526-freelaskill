<?php
// Models/AdminDashboard.php — Modèle spécifique pour le dashboard admin
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class AdminDashboard {
    private $pdo;

    // Initialise la connexion PDO à la base de données
    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Retourne toutes les offres de la BDD triées par date de création (plus récentes en premier)
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM offres_emploi ORDER BY date_creation DESC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) { $offres[] = new JobOffer($row); }
        return $offres;
    }

    // Retourne les offres filtrées par mot-clé sur le titre et/ou par date de création exacte
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

    // Retourne les offres selon un statut précis : 'pending', 'approved' ou 'rejected'
    public function getByStatut($statut) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE statut = ? ORDER BY date_creation DESC");
        $stmt->execute([$statut]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) { $offres[] = new JobOffer($row); }
        return $offres;
    }

    // Retourne le nombre total d'offres dans la BDD
    public function countAll() {
        return $this->pdo->query("SELECT COUNT(*) FROM offres_emploi")->fetchColumn();
    }

    // Retourne le nombre d'offres pour un statut donné (utilisé pour les cartes statistiques)
    public function countByStatut($s) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM offres_emploi WHERE statut = ?");
        $stmt->execute([$s]);
        return $stmt->fetchColumn();
    }

    // Met à jour le statut d'une offre (approved / rejected / pending)
    public function updateStatut($id, $statut) {
        $stmt = $this->pdo->prepare("UPDATE offres_emploi SET statut = ? WHERE id = ?");
        return $stmt->execute([$statut, $id]);
    }

    // Supprime définitivement une offre de la BDD par son ID
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM offres_emploi WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
