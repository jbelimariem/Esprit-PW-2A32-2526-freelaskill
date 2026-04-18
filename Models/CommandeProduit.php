<?php
// models/CommandeProduit.php

class CommandeProduit {
    private $idCommande;
    private $idProduit;
    private $quantite;
    private $prix_unitaire;

    public function __construct($idCommande = null, $idProduit = null, $quantite = 0, $prix_unitaire = 0) {
        $this->idCommande = $idCommande;
        $this->idProduit = $idProduit;
        $this->quantite = $quantite;
        $this->prix_unitaire = $prix_unitaire;
    }

    public function getIdCommande() { return $this->idCommande; }
    public function setIdCommande($id) { $this->idCommande = $id; }

    public function getIdProduit() { return $this->idProduit; }
    public function setIdProduit($id) { $this->idProduit = $id; }

    public function getQuantite() { return $this->quantite; }
    public function setQuantite($q) { $this->quantite = $q; }

    public function getPrixUnitaire() { return $this->prix_unitaire; }
    public function setPrixUnitaire($prix) { $this->prix_unitaire = $prix; }
}
