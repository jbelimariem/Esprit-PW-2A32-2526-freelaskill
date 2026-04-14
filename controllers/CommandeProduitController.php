<?php
// controllers/CommandeProduitController.php

require_once 'models/CommandeProduit.php';

class CommandeProduitController {

    private $commandeProduitModel;

    public function __construct() {
        $this->commandeProduitModel = new CommandeProduit();
    }

    // -------------------------------------------------------
    // Afficher les produits d'une commande
    // -------------------------------------------------------
    public function show($idCommande) {
        $produits = $this->commandeProduitModel->getByCommande($idCommande);
        include 'views/commandes.php';
    }

    // -------------------------------------------------------
    // Ajouter un produit à une commande
    // -------------------------------------------------------
    public function create($data) {
        $this->commandeProduitModel->create([
            'idCommande'    => $data['idCommande'],
            'idProduit'     => $data['idProduit'],
            'quantite'      => $data['quantite'],
            'prix_unitaire' => $data['prix_unitaire']
        ]);
    }

    // -------------------------------------------------------
    // Modifier la quantité d'un produit dans une commande
    // -------------------------------------------------------
    public function updateQuantite($idCommande, $idProduit, $quantite) {
        $this->commandeProduitModel->updateQuantite($idCommande, $idProduit, $quantite);
        header('Location: views/commandes.php');
        exit;
    }

    // -------------------------------------------------------
    // Supprimer un produit d'une commande
    // -------------------------------------------------------
    public function delete($idCommande, $idProduit) {
        $this->commandeProduitModel->delete($idCommande, $idProduit);
        header('Location: views/commandes.php');
        exit;
    }

}
