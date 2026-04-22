<?php
<<<<<<< HEAD
require_once __DIR__ . '/../Models/Add.php';

class AdminAdd extends Add {
    // Uses the same logic as Add for now
=======
// Models/AdminAdd.php — Modèle spécifique pour l'ajout admin
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class AdminAdd {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function save($offre) {
        $sql = "INSERT INTO offres_emploi (titre, description, competences, budget, delai, statut, client_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $offre->getTitre(), 
            $offre->getDescription(), 
            $offre->getCompetences(), 
            $offre->getBudget(), 
            $offre->getDelai(), 
            $offre->getStatut(),
            $offre->getClientId()
        ]);
    }
>>>>>>> faca6fd (sss)
}
