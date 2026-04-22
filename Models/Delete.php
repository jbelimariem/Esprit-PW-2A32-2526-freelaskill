<?php
require_once __DIR__ . '/../config.php';

class Delete {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function deleteJob($id) {
        $stmt = $this->pdo->prepare("DELETE FROM offres_emploi WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
