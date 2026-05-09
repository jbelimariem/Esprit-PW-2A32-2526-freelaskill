<?php
// controllers/CommandeProduitController.php

require_once __DIR__ . '/../Models/CommandeProduit.php';
require_once __DIR__ . '/config.php';

class CommandeProduitController {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // -------------------------------------------------------
    // Base de données : CRUD
    // -------------------------------------------------------
    public function getByCommandeData($idCommande) {
        $sql = "SELECT cp.*, p.nom, p.image, p.prix, p.statut 
                FROM commande_produit cp
                JOIN produit p ON cp.idProduit = p.idProduit
                WHERE cp.idCommande = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCommande]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createData($data) {
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

    public function updateQuantiteData($idCommande, $idProduit, $quantite) {
        $sql = "UPDATE commande_produit 
                SET quantite=? 
                WHERE idCommande=? AND idProduit=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$quantite, $idCommande, $idProduit]);
    }

    public function deleteData($idCommande, $idProduit) {
        $sql = "DELETE FROM commande_produit 
                WHERE idCommande=? AND idProduit=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCommande, $idProduit]);
    }

    public function deleteByCommandeData($idCommande) {
        $sql = "DELETE FROM commande_produit WHERE idCommande=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCommande]);
    }
}
