<?php
<<<<<<< HEAD
require_once __DIR__ . '/../config.php';
=======
// Models/Add.php — Modèle spécifique pour l'ajout
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';
>>>>>>> faca6fd (sss)

class Add {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

<<<<<<< HEAD
    public function save($data) {
=======
    public function save($offre) {
>>>>>>> faca6fd (sss)
        $sql = "INSERT INTO offres_emploi (titre, description, competences, budget, delai, statut, client_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
<<<<<<< HEAD
            $data['titre'], 
            $data['description'], 
            $data['competences'], 
            $data['budget'], 
            $data['delai'], 
            $data['statut'],
            $data['client_id']
=======
            $offre->getTitre(), 
            $offre->getDescription(), 
            $offre->getCompetences(), 
            $offre->getBudget(), 
            $offre->getDelai(), 
            $offre->getStatut(),
            $offre->getClientId()
>>>>>>> faca6fd (sss)
        ]);
    }
}
