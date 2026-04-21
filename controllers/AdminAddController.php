<?php
// controllers/AdminAddController.php
require_once __DIR__ . '/../Models/JobOffer.php';

class AdminAddController {
    public function execute() {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => trim($_POST['titre'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competences' => trim($_POST['competences'] ?? ''),
                'budget' => $_POST['budget'] ?? 0,
                'delai' => trim($_POST['delai'] ?? 'Indéfini'),
                'statut' => 'approved', // Admin ajoute directement en approuvé
                'client_id' => 1
            ];
            (new JobOffer($data))->save();
            header('Location: dashboard.php?success=added'); exit;
        }
        include __DIR__ . '/../views/backoffice/add_job_admin.view.php';
    }
}
