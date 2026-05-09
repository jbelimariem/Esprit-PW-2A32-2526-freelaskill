<?php
// models/Commande.php

class Commande {
    private $idCommande;
    private $user_id;
    private $date_commande;
    private $statut;
    private $adresse_livraison;
    private $mode_paiement;
    private $mode_livraison;
    private $montant_total;

    public function __construct($user_id = null, $date_commande = '', $statut = '', $adresse_livraison = '', $montant_total = 0, $mode_paiement = '', $mode_livraison = '') {
<<<<<<< HEAD

=======
>>>>>>> e50c4cf (Mise a jour locale avant synchronisation)
        $this->user_id = $user_id;
        $this->date_commande = $date_commande;
        $this->statut = $statut;
        $this->adresse_livraison = $adresse_livraison;
        $this->montant_total = $montant_total;
        $this->mode_paiement = $mode_paiement;
        $this->mode_livraison = $mode_livraison;
<<<<<<< HEAD
        require_once __DIR__ . '/../controllers/config.php';

=======
>>>>>>> e50c4cf (Mise a jour locale avant synchronisation)
    }

    public function getIdCommande() { return $this->idCommande; }
    public function setIdCommande($id) { $this->idCommande = $id; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }

    public function getDateCommande() { return $this->date_commande; }
    public function setDateCommande($date) { $this->date_commande = $date; }

    public function getStatut() { return $this->statut; }
    public function setStatut($statut) { $this->statut = $statut; }

    public function getAdresseLivraison() { return $this->adresse_livraison; }
    public function setAdresseLivraison($adresse) { $this->adresse_livraison = $adresse; }

    public function getModePaiement() { return $this->mode_paiement; }
    public function setModePaiement($mode) { $this->mode_paiement = $mode; }

    public function getModeLivraison() { return $this->mode_livraison; }
    public function setModeLivraison($mode) { $this->mode_livraison = $mode; }

<<<<<<< HEAD

=======
>>>>>>> e50c4cf (Mise a jour locale avant synchronisation)
    public function getMontantTotal() { return $this->montant_total; }
    public function setMontantTotal($montant) { $this->montant_total = $montant; }
}
