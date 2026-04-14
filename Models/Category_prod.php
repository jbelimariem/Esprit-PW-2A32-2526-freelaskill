<?php
// models/Category_prod.php

require_once __DIR__ . '/../config.php';

class Category_prod {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Récupérer toutes les catégories
    public function getAll() {
        $sql = "SELECT * FROM category_prod";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // Récupérer une catégorie par id
    public function getById($id) {
        $sql = "SELECT * FROM category_prod WHERE idCategory = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Créer une catégorie
    public function create($data) {
        $sql = "INSERT INTO category_prod (nom, description) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['description']
        ]);
    }

    // Modifier une catégorie
    public function update($id, $data) {
        $sql = "UPDATE category_prod SET nom=?, description=? WHERE idCategory=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['description'],
            $id
        ]);
    }

    // Supprimer une catégorie
    public function delete($id) {
        $sql = "DELETE FROM category_prod WHERE idCategory = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

}
