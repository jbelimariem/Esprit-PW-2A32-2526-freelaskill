<?php
// models/Produit.php

require_once __DIR__ . '/../config.php';

class Produit {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Récupérer tous les produits
    public function getAll() {
        $sql = "SELECT * FROM produit";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // Récupérer un produit par id
    public function getById($id) {
        $sql = "SELECT * FROM produit WHERE idProduit = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Récupérer les produits par catégorie
    public function getByCategory($category_id) {
        $sql = "SELECT * FROM produit WHERE category_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$category_id]);
        return $stmt->fetchAll();
    }

    // Récupérer les produits par prix
    public function getByPrice($min, $max) {
        $sql = "SELECT * FROM produit WHERE prix BETWEEN ? AND ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$min, $max]);
        return $stmt->fetchAll();
    }

    // Récupérer les produits par statut (ex: pending, disponible)
    public function getByStatut($statut) {
        $sql = "SELECT * FROM produit WHERE statut = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut]);
        return $stmt->fetchAll();
    }

    // Alias anglais pour compatibilité
    public function getByStatus($status) {
        return $this->getByStatut($status);
    }

    // Créer un produit
    public function create($data) {
        $sql = "INSERT INTO produit 
                (category_id, nom, description, prix, stock, image, statut) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['category_id'],
            $data['nom'],
            $data['description'],
            $data['prix'],
            $data['stock'],
            $data['image'],
            'pending'
        ]);
    }

    // Modifier un produit
    public function update($id, $data) {
        $sql = "UPDATE produit 
                SET nom=?, description=?, prix=?, stock=?, image=?, category_id=?, statut=? 
                WHERE idProduit=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['description'],
            $data['prix'],
            $data['stock'],
            $data['image'],
            $data['category_id'],
            $data['statut'],
            $id
        ]);
    }

    // Modifier le statut d'un produit (admin: pending → disponible)
    public function updateStatut($id, $statut) {
        $sql = "UPDATE produit SET statut = ? WHERE idProduit = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut, $id]);
    }

    // Supprimer un produit
    public function delete($id) {
        $sql = "DELETE FROM produit WHERE idProduit = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    // Modifier le stock après commande
    public function updateStock($id, $quantite) {
        $sql = "UPDATE produit SET stock = stock - ? WHERE idProduit = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$quantite, $id]);
    }

}
