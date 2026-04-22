<?php
<<<<<<< HEAD
=======
// Models/Delete.php — Modèle spécifique pour la suppression
>>>>>>> faca6fd (sss)
require_once __DIR__ . '/../config.php';

class Delete {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

<<<<<<< HEAD
    public function deleteJob($id) {
=======
    public function delete($id) {
>>>>>>> faca6fd (sss)
        $stmt = $this->pdo->prepare("DELETE FROM offres_emploi WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
