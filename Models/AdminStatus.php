<?php
// Models/AdminStatus.php — Modèle spécifique pour le changement de statut admin
require_once __DIR__ . '/../config.php';

class AdminStatus {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM offres_emploi WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateStatut($id, $statut) {
        $stmt = $this->pdo->prepare("UPDATE offres_emploi SET statut = ? WHERE id = ?");
        return $stmt->execute([$statut, $id]);
    }
}
