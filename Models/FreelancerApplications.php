<?php
// Models/FreelancerApplications.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';
require_once __DIR__ . '/JobApplication.php';

class FreelancerApplications {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Retourne toutes les candidatures avec les infos de l'offre jointes (JOIN)
    public function getMyApplications($search = '') {
        $sql = "SELECT a.*, o.titre as job_title, o.budget, o.delai, o.date_creation 
                FROM job_applications a 
                JOIN offres_emploi o ON a.job_id = o.id";

        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE o.titre LIKE ? OR DATE(a.created_at) = ?";
            $params[] = "%$search%";
            $params[] = $search;
        }

        $sql .= " ORDER BY a.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retourne une candidature par son ID (pour pré-remplir le formulaire de modification)
    public function getById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, o.titre as job_title 
             FROM job_applications a 
             JOIN offres_emploi o ON a.job_id = o.id
             WHERE a.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Met à jour une candidature (nom, email, téléphone, titre, lettre de motivation, chemin CV)
    public function updateApplication($id, $name, $email, $phone, $job_title, $cover_letter, $cv_path = null) {
        if ($cv_path !== null) {
            $sql  = "UPDATE job_applications SET name=?, email=?, phone=?, job_title=?, message=?, cover_letter=?, cv_link=?, cv_path=? WHERE id=?";
            $args = [$name, $email, $phone, $job_title, $cover_letter, $cover_letter, $cv_path, $cv_path, $id];
        } else {
            $sql  = "UPDATE job_applications SET name=?, email=?, phone=?, job_title=?, message=?, cover_letter=? WHERE id=?";
            $args = [$name, $email, $phone, $job_title, $cover_letter, $cover_letter, $id];
        }
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($args);
    }

    // Supprime une candidature (annulation)
    public function cancelApplication($id) {
        $stmt = $this->pdo->prepare("DELETE FROM job_applications WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
