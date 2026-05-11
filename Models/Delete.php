<?php
// Models/Delete.php — Modèle spécifique pour la suppression
require_once __DIR__ . '/../config.php';

class Delete {
    private $pdo;

    // Initialise la connexion PDO à la base de données
    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Supprime définitivement une offre d'emploi de la BDD par son ID (utilisé côté client)
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM offres_emploi WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
