<?php
// Models/JobOffer.php — Modèle Entité avec Getters/Setters et Logique PDO

require_once __DIR__ . '/../config.php';

class JobOffer {
    private $id;
    private $titre;
    private $description;
    private $competences;
    private $budget;
    private $delai;
    private $statut; // pending, approved, rejected
    private $date_creation;
    private $client_id;

    private $pdo;

    public function __construct($data = []) {
        $this->pdo = config::getConnexion();
        if (!empty($data)) {
            $this->id            = $data['id']            ?? null;
            $this->titre         = $data['titre']         ?? '';
            $this->description   = $data['description']   ?? '';
            $this->competences   = $data['competences']   ?? '';
            $this->budget        = $data['budget']        ?? 0;
            $this->delai         = $data['delai']         ?? '';
            $this->statut        = $data['statut']        ?? 'pending';
            $this->date_creation = $data['date_creation'] ?? null;
            $this->client_id     = $data['client_id']     ?? 1;
        }
    }

    // --- GETTERS ---
    public function getId()            { return $this->id; }
    public function getTitre()         { return $this->titre; }
    public function getDescription()   { return $this->description; }
    public function getCompetences()   { return $this->competences; }
    public function getBudget()        { return $this->budget; }
    public function getDelai()         { return $this->delai; }
    public function getStatut()        { return $this->statut; }
    public function getDateCreation()  { return $this->date_creation; }
    public function getClientId()      { return $this->client_id; }

    // --- SETTERS ---
    public function setTitre($t)       { $this->titre = $t; }
    public function setDescription($d) { $this->description = $d; }
    public function setCompetences($c) { $this->competences = $c; }
    public function setBudget($b)      { $this->budget = $b; }
    public function setDelai($dl)      { $this->delai = $dl; }
    public function setStatut($s)      { $this->statut = $s; }
}
