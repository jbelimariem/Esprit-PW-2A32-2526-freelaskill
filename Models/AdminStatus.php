<?php
// Models/AdminStatus.php — Modèle spécifique pour le changement de statut admin
require_once __DIR__ . '/../config.php';

class AdminStatus {
    private $pdo;

    // Initialise la connexion PDO à la base de données
    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Supprime définitivement une offre d'emploi de la BDD par son ID
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM offres_emploi WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Met à jour le statut d'une offre : 'approved', 'rejected' ou 'pending'
    public function updateStatut($id, $statut) {
        $stmt = $this->pdo->prepare("UPDATE offres_emploi SET statut = ? WHERE id = ?");
        return $stmt->execute([$statut, $id]);
    }
}
