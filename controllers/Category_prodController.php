<?php
// controllers/Category_prodController.php

require_once __DIR__ . '/../Models/Category_prod.php';
require_once __DIR__ . '/../config.php';

class Category_prodController {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // -------------------------------------------------------
    // Base de données : CRUD
    // -------------------------------------------------------
    public function getAllData() {
        $sql = "SELECT * FROM category_prod";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByIdData($id) {
        $sql = "SELECT * FROM category_prod WHERE idCategory = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createData($data) {
        $sql = "INSERT INTO category_prod (nom, description) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['description']
        ]);
    }

    public function updateData($id, $data) {
        $sql = "UPDATE category_prod SET nom=?, description=? WHERE idCategory=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['description'],
            $id
        ]);
    }

    public function deleteData($id) {
        $sql = "DELETE FROM category_prod WHERE idCategory = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    // -------------------------------------------------------
    // Frontoffice
    // -------------------------------------------------------
    public function index() {
        $categories = $this->getAllData();
        include __DIR__ . '/../Views/Frontoffice/home.php';
    }

    public function show($id) {
        $category = $this->getByIdData($id);
        include __DIR__ . '/../Views/Frontoffice/home.php';
    }

    // -------------------------------------------------------
    // Admin Actions (Routes de l'ancien controlleur)
    // -------------------------------------------------------
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom'         => $_POST['nom'],
                'description' => $_POST['description']
            ];
            $this->createData($data);
            header('Location: home.php');
            exit;
        }
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom'         => $_POST['nom'],
                'description' => $_POST['description']
            ];
            $this->updateData($id, $data);
            header('Location: home.php');
            exit;
        }
    }

    public function delete($id) {
        $this->deleteData($id);
        header('Location: home.php');
        exit;
    }

}
