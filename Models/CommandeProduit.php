<?php
// models/CommandeProduit.php

require_once __DIR__ . '/../config.php';

class CommandeProduit {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Récupérer les produits d'une commande
    public function getByCommande($idCommande) {
        $sql = "SELECT cp.*, p.nom, p.image, p.prix 
                FROM commande_produit cp
                JOIN produit p ON cp.idProduit = p.idProduit
                WHERE cp.idCommande = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCommande]);
        return $stmt->fetchAll();
    }

    // Ajouter un produit à une commande
    public function create($data) {
        $sql = "INSERT INTO commande_produit 
                (idCommande, idProduit, quantite, prix_unitaire) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['idCommande'],
            $data['idProduit'],
            $data['quantite'],
            $data['prix_unitaire']
        ]);
    }

    // Modifier la quantité
    public function updateQuantite($idCommande, $idProduit, $quantite) {
        $sql = "UPDATE commande_produit 
                SET quantite=? 
                WHERE idCommande=? AND idProduit=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$quantite, $idCommande, $idProduit]);
    }

    // Supprimer un produit d'une commande
    public function delete($idCommande, $idProduit) {
        $sql = "DELETE FROM commande_produit 
                WHERE idCommande=? AND idProduit=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCommande, $idProduit]);
    }

    // Supprimer tous les produits d'une commande
    public function deleteByCommande($idCommande) {
        $sql = "DELETE FROM commande_produit WHERE idCommande=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCommande]);
    }

}
