<?php
// controllers/ProduitController.php

require_once 'models/Produit.php';
require_once 'models/Category_prod.php';

class ProduitController {

    private $produitModel;
    private $categoryModel;

    public function __construct() {
        $this->produitModel  = new Produit();
        $this->categoryModel = new Category_prod();
    }

    // -------------------------------------------------------
    // home.php — afficher tous les produits + catégories
    // -------------------------------------------------------
    public function index() {
        $produits    = $this->produitModel->getAll();
        $categories  = $this->categoryModel->getAll();
        include __DIR__ . '/../Views/Frontoffice/home.php';
    }

    // -------------------------------------------------------
    // home.php — filtrer par catégorie (sidebar)
    // -------------------------------------------------------
    public function filterByCategory($category_id) {
        $produits   = $this->produitModel->getByCategory($category_id);
        $categories = $this->categoryModel->getAll();
        include __DIR__ . '/../Views/Frontoffice/home.php';
    }

    // -------------------------------------------------------
    // home.php — filtrer par prix (slider)
    // -------------------------------------------------------
    public function filterByPrice($min, $max) {
        $produits   = $this->produitModel->getByPrice($min, $max);
        $categories = $this->categoryModel->getAll();
        include __DIR__ . '/../Views/Frontoffice/home.php';
    }

    // -------------------------------------------------------
    // detailproduit.php — afficher un produit
    // -------------------------------------------------------
    public function show($id) {
        $produit = $this->produitModel->getById($id);
        if (!$produit) {
            header('Location: /Views/Frontoffice/home.php');
            exit;
        }
        include __DIR__ . '/../Views/Frontoffice/detailproduit.php';
    }

    // -------------------------------------------------------
    // vendreproduit.php — afficher le formulaire
    // -------------------------------------------------------
    public function showForm() {
        $categories = $this->categoryModel->getAll();
        include __DIR__ . '/../Views/Frontoffice/vendreproduit.php';
    }

    // -------------------------------------------------------
    // vendreproduit.php — publier un produit (POST)
    // -------------------------------------------------------
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom'         => $_POST['title'],
                'description' => $_POST['description'],
                'prix'        => $_POST['price'],
                'category_id' => $_POST['category'],
                'statut'      => $_POST['availability'],
                'stock'       => 1,
                'image'       => ''
            ];
            $this->produitModel->create($data);
            header('Location: /Views/Frontoffice/home.php');
            exit;
        }
    }

    // -------------------------------------------------------
    // Admin — supprimer un produit
    // -------------------------------------------------------
    public function delete($id) {
        $this->produitModel->delete($id);
        header('Location: /Views/Frontoffice/home.php');
        exit;
    }

}
