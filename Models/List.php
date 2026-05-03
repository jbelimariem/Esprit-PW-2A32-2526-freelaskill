<?php
// Models/List.php — Modèle spécifique pour le listage
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class ListModel {
    private $pdo;

    // Initialise la connexion PDO à la base de données
    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Retourne toutes les offres (tous statuts) triées par date de création décroissante
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM offres_emploi ORDER BY date_creation DESC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) {
            $offres[] = new JobOffer($row);
        }
        return $offres;
    }

    // Retourne les offres filtrées dynamiquement par mot-clé (titre/description), date exacte et/ou budget maximum
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
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) {
            $offres[] = new JobOffer($row);
        }
        return $offres;
    }
}
