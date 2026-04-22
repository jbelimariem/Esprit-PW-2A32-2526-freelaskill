<?php
// controllers/AdminAddController.php
require_once __DIR__ . '/../Models/AdminAdd.php';

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
                'statut' => $_POST['statut'] ?? 'approved',
                'client_id' => 1
            ];
            $offre = new JobOffer($data);
            (new AdminAdd())->save($offre);
            header('Location: dashboard.php?success=added'); exit;
        }
        include __DIR__ . '/../views/backoffice/add_job_admin.view.php';
    }
}
