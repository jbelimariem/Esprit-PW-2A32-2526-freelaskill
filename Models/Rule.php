<?php

class Rule
{
    private $id_rule;
    private $titre;
    private $description;
    private $type;
    private $valeur;
    private $date_creation;
    private $statut;
    private $titre_contrat;

    public function __construct($titre = '', $description = '', $type = '', $valeur = '', $statut = '', $titre_contrat = null)
    {
        $this->titre = $titre;
        $this->description = $description;
        $this->type = $type;
        $this->valeur = $valeur;
        $this->statut = $statut;
        $this->titre_contrat = $titre_contrat;
    }

    public function getIdRule() { return $this->id_rule; }
    public function setIdRule($id_rule) { $this->id_rule = $id_rule; }

    public function getTitre() { return $this->titre; }
    public function setTitre($titre) { $this->titre = $titre; }

    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }

    public function getType() { return $this->type; }
    public function setType($type) { $this->type = $type; }

    public function getValeur() { return $this->valeur; }
    public function setValeur($valeur) { $this->valeur = $valeur; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }

    public function getStatut() { return $this->statut; }
    public function setStatut($statut) { $this->statut = $statut; }

    public function getTitreContrat() { return $this->titre_contrat; }
    public function setTitreContrat($titre_contrat) { $this->titre_contrat = $titre_contrat; }
}
