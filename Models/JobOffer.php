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

    // --- DATABASE METHODS ---

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM offres_emploi ORDER BY date_creation DESC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) {
            $offres[] = new JobOffer($row);
        }
        return $offres;
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new JobOffer($row) : null;
    }

    public function save() {
        $sql = "INSERT INTO offres_emploi (titre, description, competences, budget, delai, statut, client_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $this->titre, 
            $this->description, 
            $this->competences, 
            $this->budget, 
            $this->delai, 
            $this->statut,
            $this->client_id
        ]);
    }

    public function update() {
        $sql = "UPDATE offres_emploi SET titre=?, description=?, competences=?, budget=?, delai=?, statut=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $this->titre, 
            $this->description, 
            $this->competences, 
            $this->budget, 
            $this->delai, 
            $this->statut, 
            $this->id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM offres_emploi WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function search($q, $d, $maxBudget = null) {
        $sql = "SELECT * FROM offres_emploi WHERE 1=1";
        $params = [];
        if (!empty($q)) {
            $sql .= " AND (titre LIKE ? OR description LIKE ?)";
            $params[] = "%$q%"; $params[] = "%$q%";
        }
        if (!empty($d)) {
            $sql .= " AND DATE(date_creation) = ?";
            $params[] = $d;
        }
        if (!empty($maxBudget)) {
            $sql .= " AND budget <= ?";
            $params[] = $maxBudget;
        }
        $sql .= " ORDER BY date_creation DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) {
            $offres[] = new JobOffer($row);
        }
        return $offres;
    }

    public function getByStatut($statut) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE statut = ? ORDER BY date_creation DESC");
        $stmt->execute([$statut]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offres = [];
        foreach ($results as $row) {
            $offres[] = new JobOffer($row);
        }
        return $offres;
    }

    public function updateStatut($id, $statut) {
        $stmt = $this->pdo->prepare("UPDATE offres_emploi SET statut = ? WHERE id = ?");
        return $stmt->execute([$statut, $id]);
    }

    public function countAll() {
        return $this->pdo->query("SELECT COUNT(*) FROM offres_emploi")->fetchColumn();
    }

    public function countByStatut($s) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM offres_emploi WHERE statut = ?");
        $stmt->execute([$s]);
        return $stmt->fetchColumn();
    }
}
