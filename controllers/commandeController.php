<?php
// controllers/commandeController.php

require_once __DIR__ . '/../Models/commande.php';
require_once __DIR__ . '/../config.php';

class CommandeController {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // -------------------------------------------------------
    // Base de données : CRUD
    // -------------------------------------------------------
    public function getAll() {
        $sql = "SELECT * FROM commande";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM commande WHERE idCommande = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUser($user_id) {
        $sql = "SELECT * FROM commande WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createData($data) {
        $sql = "INSERT INTO commande 
                (user_id, date_commande, statut, adresse_livraison, montant_total) 
                VALUES (?, CURDATE(), ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['user_id'],
            'en_attente',
            $data['adresse_livraison'],
            $data['montant_total']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateStatutData($id, $statut) {
        $sql = "UPDATE commande SET statut=? WHERE idCommande=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut, $id]);
    }

    public function deleteData($id) {
        $sql = "DELETE FROM commande WHERE idCommande = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }
}
