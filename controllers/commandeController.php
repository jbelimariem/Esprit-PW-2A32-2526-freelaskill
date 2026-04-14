<?php
// controllers/CommandeController.php

require_once 'models/Commande.php';
require_once 'models/CommandeProduit.php';
require_once 'models/Produit.php';

class CommandeController {

    private $commandeModel;
    private $commandeProduitModel;
    private $produitModel;

    public function __construct() {
        $this->commandeModel        = new Commande();
        $this->commandeProduitModel = new CommandeProduit();
        $this->produitModel         = new Produit();
    }

    // -------------------------------------------------------
    // panier.php — afficher le panier (session)
    // -------------------------------------------------------
    public function showPanier() {
        $panier = isset($_SESSION['panier']) ? $_SESSION['panier'] : [];
        $total  = 0;
        foreach ($panier as $item) {
            $total += $item['prix'] * $item['quantite'];
        }
        include __DIR__ . '/../Views/Frontoffice/panier.php';
    }

    // -------------------------------------------------------
    // detailproduit.php — ajouter au panier
    // -------------------------------------------------------
    public function ajouterAuPanier($idProduit, $quantite = 1) {
        $produit = $this->produitModel->getById($idProduit);
        if ($produit) {
            if (!isset($_SESSION['panier'])) {
                $_SESSION['panier'] = [];
            }
            if (isset($_SESSION['panier'][$idProduit])) {
                $_SESSION['panier'][$idProduit]['quantite'] += $quantite;
            } else {
                $_SESSION['panier'][$idProduit] = [
                    'idProduit' => $idProduit,
                    'nom'       => $produit['nom'],
                    'prix'      => $produit['prix'],
                    'image'     => $produit['image'],
                    'quantite'  => $quantite
                ];
            }
        }
        header('Location: /Views/Frontoffice/panier.php');
        exit;
    }

    // -------------------------------------------------------
    // panier.php — modifier quantité dans le panier
    // -------------------------------------------------------
    public function modifierQuantite($idProduit, $quantite) {
        if (isset($_SESSION['panier'][$idProduit])) {
            if ($quantite <= 0) {
                unset($_SESSION['panier'][$idProduit]);
            } else {
                $_SESSION['panier'][$idProduit]['quantite'] = $quantite;
            }
        }
        header('Location: /Views/Frontoffice/panier.php');
        exit;
    }

    // -------------------------------------------------------
    // panier.php — supprimer un produit du panier
    // -------------------------------------------------------
    public function supprimerDuPanier($idProduit) {
        if (isset($_SESSION['panier'][$idProduit])) {
            unset($_SESSION['panier'][$idProduit]);
        }
        header('Location: /Views/Frontoffice/panier.php');
        exit;
    }

    // -------------------------------------------------------
    // panier.php — valider la commande
    // -------------------------------------------------------
    public function validerCommande() {
        if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
            header('Location: /Views/Frontoffice/panier.php');
            exit;
        }

        $panier = $_SESSION['panier'];
        $total  = 0;
        foreach ($panier as $item) {
            $total += $item['prix'] * $item['quantite'];
        }

        // Créer la commande
        $data = [
            'user_id'           => $_SESSION['user_id'],
            'adresse_livraison' => $_POST['adresse_livraison'] ?? '',
            'montant_total'     => $total
        ];
        $idCommande = $this->commandeModel->create($data);

        // Ajouter les produits dans commande_produit
        foreach ($panier as $item) {
            $this->commandeProduitModel->create([
                'idCommande'    => $idCommande,
                'idProduit'     => $item['idProduit'],
                'quantite'      => $item['quantite'],
                'prix_unitaire' => $item['prix']
            ]);
            // Mettre à jour le stock
            $this->produitModel->updateStock($item['idProduit'], $item['quantite']);
        }

        // Vider le panier
        unset($_SESSION['panier']);

        header('Location: /Views/Frontoffice/confirmation.php');
        exit;
    }

    // -------------------------------------------------------
    // Afficher les commandes d'un user
    // -------------------------------------------------------
    public function mesCommandes() {
        $commandes = $this->commandeModel->getByUser($_SESSION['user_id']);
        include 'views/commandes.php';
    }

    // -------------------------------------------------------
    // Admin — afficher toutes les commandes
    // -------------------------------------------------------
    public function index() {
        $commandes = $this->commandeModel->getAll();
        include 'views/commandes.php';
    }

    // -------------------------------------------------------
    // Admin — changer le statut d'une commande
    // -------------------------------------------------------
    public function updateStatut($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->commandeModel->updateStatut($id, $_POST['statut']);
            header('Location: views/commandes.php');
            exit;
        }
    }

}
