<?php
// Models/FreelancerDetail.php — Gère une offre individuelle et la logique de candidature.
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class FreelancerDetail {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Retourne une offre approuvée par son ID
    public function getJob($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ? AND statut = 'approved'");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new JobOffer($row) : null;
    }

    // Enregistre une candidature complète (nom, email, téléphone, titre, lettre de motivation, chemin CV)
    public function applyJob($job_id, $name, $email, $phone, $job_title, $cover_letter, $cv_path) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO job_applications (job_id, name, email, job_title, message, cv_link, phone, cover_letter, cv_path, created_at, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')"
        );
        return $stmt->execute([
            $job_id,
            $name,
            $email,
            $job_title,
            $cover_letter,   // message (lettre de motivation)
            $cv_path,        // cv_link (chemin fichier ou lien)
            $phone,
            $cover_letter,
            $cv_path
        ]);
    }

    // Vérifie si un email a déjà postulé à cette offre
    public function hasApplied($job_id, $email) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM job_applications WHERE job_id = ? AND email = ?");
        $stmt->execute([$job_id, $email]);
        return $stmt->fetchColumn() > 0;
    }
}
