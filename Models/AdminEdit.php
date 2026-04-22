<?php
<<<<<<< HEAD
require_once __DIR__ . '/../Models/Edit.php';

class AdminEdit extends Edit {
    // Uses the same logic as Edit for now
=======
// Models/AdminEdit.php — Modèle spécifique pour l'édition admin
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class AdminEdit {
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
        $sql = "UPDATE offres_emploi SET titre=?, description=?, budget=?, delai=?, statut=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $offre->getTitre(), 
            $offre->getDescription(), 
            $offre->getBudget(), 
            $offre->getDelai(),
            $offre->getStatut(), 
            $offre->getId()
        ]);
    }
>>>>>>> faca6fd (sss)
}
