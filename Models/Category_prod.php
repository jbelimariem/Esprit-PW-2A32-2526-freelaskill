<?php
// models/Category_prod.php

class Category_prod {
    private $idCategory;
    private $nom;
    private $description;

    public function __construct($nom = '', $description = '') {
        $this->nom = $nom;
        $this->description = $description;
    }

    public function getIdCategory() { return $this->idCategory; }
    public function setIdCategory($id) { $this->idCategory = $id; }

    public function getNom() { return $this->nom; }
    public function setNom($nom) { $this->nom = $nom; }

    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }
}
