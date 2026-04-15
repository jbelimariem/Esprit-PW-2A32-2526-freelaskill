<?php
// Models/JobOffer.php

require_once __DIR__ . '/../config.php';

class JobOffer {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // ----------------------------------------------------------------
    // Récupérer toutes les offres
    // ----------------------------------------------------------------
    public function getAll() {
        $sql  = "SELECT * FROM job_offer ORDER BY date_creation DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Récupérer une offre par ID
    // ----------------------------------------------------------------
    public function getById($id) {
        $sql  = "SELECT * FROM job_offer WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ----------------------------------------------------------------
    // Filtrer par statut (pending / approved / rejected)
    // ----------------------------------------------------------------
    public function getByStatut($statut) {
        $sql  = "SELECT * FROM job_offer WHERE statut = ? ORDER BY date_creation DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Récupérer les offres d'un client
    // ----------------------------------------------------------------
    public function getByClientId($clientId) {
        $sql  = "SELECT * FROM job_offer WHERE client_id = ? ORDER BY date_creation DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Recherche par titre et/ou date
    // ----------------------------------------------------------------
    public function search($titre = '', $date = '') {
        $conditions = [];
        $params     = [];

        if (!empty($titre)) {
            $conditions[] = "titre LIKE ?";
            $params[]     = '%' . $titre . '%';
        }
        if (!empty($date)) {
            $conditions[] = "DATE(date_creation) = ?";
            $params[]     = $date;
        }

        $where = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $sql   = "SELECT * FROM job_offer $where ORDER BY date_creation DESC";
        $stmt  = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Recherche unifiée (titre OU date)
    // ----------------------------------------------------------------
    public function searchUnified($q) {
        if (empty($q)) return $this->getAll();

        $sql = "SELECT * FROM job_offer 
                WHERE (titre LIKE ?) 
                OR (DATE(date_creation) = ?)
                ORDER BY date_creation DESC";
        $stmt = $this->pdo->prepare($sql);
        // On passe 'q' pour le titre (avec %) et pour la date (direct)
        $stmt->execute(['%'.$q.'%', $q]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Créer une offre (statut = pending par défaut)
    // ----------------------------------------------------------------
    public function create($data) {
        $sql = "INSERT INTO job_offer 
                (titre, description, competences, budget, delai, statut, client_id)
                VALUES (?, ?, ?, ?, ?, 'pending', ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['titre'],
            $data['description'],
            $data['competences'],
            $data['budget'],
            $data['delai'],
            $data['client_id'] ?? 1
        ]);
        return $this->pdo->lastInsertId();
    }

    // ----------------------------------------------------------------
    // Modifier une offre
    // ----------------------------------------------------------------
    public function update($id, $data) {
        $sql = "UPDATE job_offer 
                SET titre=?, description=?, competences=?, budget=?, delai=?, statut=?
                WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['titre'],
            $data['description'],
            $data['competences'],
            $data['budget'],
            $data['delai'],
            $data['statut'],
            $id
        ]);
    }

    // ----------------------------------------------------------------
    // Modifier uniquement le statut (admin: approve / reject)
    // ----------------------------------------------------------------
    public function updateStatut($id, $statut) {
        $sql  = "UPDATE job_offer SET statut = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut, $id]);
    }

    // ----------------------------------------------------------------
    // Supprimer une offre
    // ----------------------------------------------------------------
    public function delete($id) {
        $sql  = "DELETE FROM job_offer WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    // ----------------------------------------------------------------
    // Compter par statut (pour stats admin)
    // ----------------------------------------------------------------
    public function countByStatut($statut) {
        $sql  = "SELECT COUNT(*) as total FROM job_offer WHERE statut = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut]);
        $row  = $stmt->fetch();
        return (int) $row['total'];
    }

    // ----------------------------------------------------------------
    // Compter total
    // ----------------------------------------------------------------
    public function countAll() {
        $sql  = "SELECT COUNT(*) as total FROM job_offer";
        $stmt = $this->pdo->query($sql);
        $row  = $stmt->fetch();
        return (int) $row['total'];
    }
}
?>
