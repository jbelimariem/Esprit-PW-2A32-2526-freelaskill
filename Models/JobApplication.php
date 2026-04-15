<?php
// Models/JobApplication.php

require_once __DIR__ . '/../config.php';

class JobApplication {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // ----------------------------------------------------------------
    // Ajouter une candidature
    // ----------------------------------------------------------------
    public function create($data) {
        $sql = "INSERT INTO job_applications (job_id, name, email, job_title, message, cv_link) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['job_id'],
            $data['name'],
            $data['email'],
            $data['job_title'],
            $data['message'],
            $data['cv_link']
        ]);
    }

    // ----------------------------------------------------------------
    // Liste des candidats pour une offre spécifique
    // ----------------------------------------------------------------
    public function getByJobId($jobId) {
        $sql = "SELECT * FROM job_applications WHERE job_id = ? ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$jobId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ----------------------------------------------------------------
    // Compter le nombre de candidats pour une offre
    // ----------------------------------------------------------------
    public function countByJobId($jobId) {
        $sql = "SELECT COUNT(*) as total FROM job_applications WHERE job_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$jobId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    // ----------------------------------------------------------------
    // Mettre à jour le statut d'une candidature
    // ----------------------------------------------------------------
    public function updateStatus($id, $status) {
        $sql = "UPDATE job_applications SET status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$status, $id]);
    }
}
?>
