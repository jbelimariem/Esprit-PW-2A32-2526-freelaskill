<?php
// models/Commande.php

require_once __DIR__ . '/../config.php';

class Commande {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Récupérer toutes les commandes
    public function getAll() {
        $sql = "SELECT * FROM commande";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // Récupérer une commande par id
    public function getById($id) {
        $sql = "SELECT * FROM commande WHERE idCommande = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Récupérer les commandes d'un user
    public function getByUser($user_id) {
        $sql = "SELECT * FROM commande WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    // Créer une commande
    public function create($data) {
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

    // Modifier le statut d'une commande
    public function updateStatut($id, $statut) {
        $sql = "UPDATE commande SET statut=? WHERE idCommande=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut, $id]);
    }

    // Supprimer une commande
    public function delete($id) {
        $sql = "DELETE FROM commande WHERE idCommande = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

}
