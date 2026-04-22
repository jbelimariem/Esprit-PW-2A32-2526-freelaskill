<?php
// Models/Edit.php — Modèle spécifique pour la modification
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class Edit {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new JobOffer($row) : null;
    }

    public function update($offre) {
        $sql = "UPDATE offres_emploi SET titre=?, description=?, competences=?, budget=?, delai=?, statut=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $offre->getTitre(), 
            $offre->getDescription(), 
            $offre->getCompetences(), 
            $offre->getBudget(), 
            $offre->getDelai(), 
            $offre->getStatut(), 
            $offre->getId()
        ]);
    }
}
