<?php
// models/produit.php
<<<<<<< HEAD
require_once __DIR__ . '/../controllers/config.php';

=======
>>>>>>> e50c4cf (Mise a jour locale avant synchronisation)

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
<<<<<<< HEAD
    private $pdo;

    public function __construct($category_id = null, $nom = '', $description = '', $prix = 0, $stock = 0, $image = '', $statut = '', $user_id = null) {
        $this->pdo = config::getConnexion();

=======

    public function __construct($category_id = null, $nom = '', $description = '', $prix = 0, $stock = 0, $image = '', $statut = '', $user_id = null) {
>>>>>>> e50c4cf (Mise a jour locale avant synchronisation)
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
<<<<<<< HEAD

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

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM produit");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM produit WHERE idProduit = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCategory($category_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM produit WHERE category_id = ?");
        $stmt->execute([$category_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByStatut($statut) {
        $stmt = $this->pdo->prepare("SELECT * FROM produit WHERE LOWER(TRIM(statut)) = LOWER(TRIM(?))");
        $stmt->execute([trim($statut)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByStatus($status) {
        return $this->getByStatut($status);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE produit
             SET nom = ?, description = ?, prix = ?, stock = ?, image = ?, category_id = ?, statut = ?
             WHERE idProduit = ?"
        );

        return $stmt->execute([
            $data['nom'],
            $data['description'],
            $data['prix'],
            $data['stock'],
            $data['image'],
            $data['category_id'],
            isset($data['statut']) ? trim($data['statut']) : null,
            $id
        ]);
    }

    public function updateStatut($id, $statut) {
        $stmt = $this->pdo->prepare("UPDATE produit SET statut = ? WHERE idProduit = ?");
        return $stmt->execute([trim($statut), $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM produit WHERE idProduit = ?");
        return $stmt->execute([$id]);
    }
=======
>>>>>>> e50c4cf (Mise a jour locale avant synchronisation)

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
