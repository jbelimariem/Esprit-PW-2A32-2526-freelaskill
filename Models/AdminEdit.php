<?php
// Models/AdminEdit.php — Modèle spécifique pour l'édition admin
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class AdminEdit {
    private $pdo;

    // Initialise la connexion PDO à la base de données
    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Retourne un objet JobOffer par son ID pour pré-remplir le formulaire de modification (version admin)
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new JobOffer($row) : null;
    }

    // Met à jour une offre en BDD (titre, description, budget, délai, statut) — sans toucher aux compétences
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
}
