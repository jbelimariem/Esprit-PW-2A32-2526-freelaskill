<?php

class Contrat
{
    private $id_contrat;
    private $titre;
    private $description;
    private $budget;
    private $delai;
    private $statut;
    private $date_creation;
    private $freelance_info;
    private $signature_client;
    private $signature_freelance;

    public function __construct(
        $id_contrat = null,
        $titre = null,
        $description = null,
        $budget = null,
        $delai = null,
        $statut = 'brouillon',
        $date_creation = null,
        $freelance_info = null,
        $signature_client = null,
        $signature_freelance = null
    ) {
        $this->id_contrat = $id_contrat;
        $this->titre = $titre;
        $this->description = $description;
        $this->budget = $budget;
        $this->delai = $delai;
        $this->statut = $statut;
        $this->date_creation = $date_creation;
        $this->freelance_info = $freelance_info;
        $this->signature_client = $signature_client;
        $this->signature_freelance = $signature_freelance;
    }

    // Getters
    public function getIdContrat() { return $this->id_contrat; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getBudget() { return $this->budget; }
    public function getDelai() { return $this->delai; }
    public function getStatut() { return $this->statut; }
    public function getDateCreation() { return $this->date_creation; }
    public function getFreelanceInfo() { return $this->freelance_info; }
    public function getSignatureClient() { return $this->signature_client; }
    public function getSignatureFreelance() { return $this->signature_freelance; }

    // Setters
    public function setIdContrat($id_contrat) { $this->id_contrat = $id_contrat; }
    public function setTitre($titre) { $this->titre = $titre; }
    public function setDescription($description) { $this->description = $description; }
    public function setBudget($budget) { $this->budget = $budget; }
    public function setDelai($delai) { $this->delai = $delai; }
    public function setStatut($statut) { $this->statut = $statut; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }
    public function setFreelanceInfo($freelance_info) { $this->freelance_info = $freelance_info; }
    public function setSignatureClient($signature_client) { $this->signature_client = $signature_client; }
    public function setSignatureFreelance($signature_freelance) { $this->signature_freelance = $signature_freelance; }
}
