<?php
// controllers/Category_prodController.php

require_once 'models/Category_prod.php';

class Category_prodController {

    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new Category_prod();
    }

    // -------------------------------------------------------
    // Afficher toutes les catégories (sidebar home.php)
    // -------------------------------------------------------
    public function index() {
        $categories = $this->categoryModel->getAll();
        include __DIR__ . '/../Views/Frontoffice/home.php';
    }

    // -------------------------------------------------------
    // Afficher une catégorie par id
    // -------------------------------------------------------
    public function show($id) {
        $category = $this->categoryModel->getById($id);
        include __DIR__ . '/../Views/Frontoffice/home.php';
    }

    // -------------------------------------------------------
    // Admin — ajouter une catégorie (POST)
    // -------------------------------------------------------
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom'         => $_POST['nom'],
                'description' => $_POST['description']
            ];
            $this->categoryModel->create($data);
            header('Location: /Views/Frontoffice/home.php');
            exit;
        }
    }

    // -------------------------------------------------------
    // Admin — modifier une catégorie (POST)
    // -------------------------------------------------------
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom'         => $_POST['nom'],
                'description' => $_POST['description']
            ];
            $this->categoryModel->update($id, $data);
            header('Location: /Views/Frontoffice/home.php');
            exit;
        }
    }

    // -------------------------------------------------------
    // Admin — supprimer une catégorie
    // -------------------------------------------------------
    public function delete($id) {
        $this->categoryModel->delete($id);
        header('Location: /Views/Frontoffice/home.php');
        exit;
    }

}
