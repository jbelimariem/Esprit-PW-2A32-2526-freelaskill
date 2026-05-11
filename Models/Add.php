<?php
// Models/Add.php — Modèle spécifique pour l'ajout
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class Add {
    private $pdo;

    // Initialise la connexion PDO à la base de données
    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Insère une nouvelle offre d'emploi dans la BDD à partir d'un objet JobOffer, retourne true si succès
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
}
