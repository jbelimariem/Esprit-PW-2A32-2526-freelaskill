<?php
require_once __DIR__ . '/../config.php';

class Add {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function save($data) {
        $sql = "INSERT INTO offres_emploi (titre, description, competences, budget, delai, statut, client_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['titre'], 
            $data['description'], 
            $data['competences'], 
            $data['budget'], 
            $data['delai'], 
            $data['statut'],
            $data['client_id']
        ]);
    }
}
