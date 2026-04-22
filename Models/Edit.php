<?php
require_once __DIR__ . '/../config.php';

class Edit {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data) {
        $sql = "UPDATE offres_emploi SET titre=?, description=?, competences=?, budget=?, delai=?, statut=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['titre'], 
            $data['description'], 
            $data['competences'], 
            $data['budget'], 
            $data['delai'], 
            $data['statut'], 
            $data['id']
        ]);
    }
}
