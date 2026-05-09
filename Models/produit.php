<?php
// models/produit.php

class Produit {
    private $idProduit;
    private $category_id;
    private $user_id;
    private $nom;
    private $description;
    private $prix;
    private $stock;
    private $image;
    private $statut;

    public function __construct($category_id = null, $nom = '', $description = '', $prix = 0, $stock = 0, $image = '', $statut = '', $user_id = null) {
        $this->category_id = $category_id;
        $this->nom = $nom;
        $this->description = $description;
        $this->prix = $prix;
        $this->stock = $stock;
        $this->image = $image;
        $this->statut = $statut;
        $this->user_id = $user_id;
    }

    public function getIdProduit() { return $this->idProduit; }
    public function setIdProduit($id) { $this->idProduit = $id; }
    
    public function getCategoryId() { return $this->category_id; }
    public function setCategoryId($id) { $this->category_id = $id; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    
    public function getNom() { return $this->nom; }
    public function setNom($nom) { $this->nom = $nom; }
    
    public function getDescription() { return $this->description; }
    public function setDescription($desc) { $this->description = $desc; }
    
    public function getPrix() { return $this->prix; }
    public function setPrix($prix) { $this->prix = $prix; }
    
    public function getStock() { return $this->stock; }
    public function setStock($stock) { $this->stock = $stock; }
    
    public function getImage() { return $this->image; }
    public function setImage($image) { $this->image = $image; }
    
    public function getStatut() { return $this->statut; }
    public function setStatut($statut) { $this->statut = $statut; }
}
