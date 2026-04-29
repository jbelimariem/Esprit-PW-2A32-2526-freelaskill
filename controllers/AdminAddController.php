<?php
// controllers/AdminAddController.php
require_once __DIR__ . '/../Models/AdminAdd.php';

class AdminAddController {
    public function execute() {
        $errors = [];
        $data = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => trim($_POST['titre'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competences' => trim($_POST['competences'] ?? ''),
                'budget' => trim($_POST['budget'] ?? ''),
                'delai' => trim($_POST['delai'] ?? ''),
                'statut' => $_POST['statut'] ?? 'approved',
                'client_id' => 1
            ];

            if (empty($data['titre'])) $errors['titre'] = "Le titre est requis.";
            if (empty($data['description'])) $errors['description'] = "La description est requise.";
            if (empty($data['competences'])) $errors['competences'] = "Les compétences sont requises.";
            if (empty($data['budget']) || !is_numeric($data['budget']) || $data['budget'] <= 0) $errors['budget'] = "Le budget doit être un nombre valide supérieur à 0.";
            if (empty($data['delai'])) $errors['delai'] = "Le délai est requis.";

            if (empty($errors)) {
                $offre = new JobOffer($data);
                (new AdminAdd())->save($offre);
                header('Location: dashboard.php?success=added'); exit;
            }
        }
        include __DIR__ . '/../views/backoffice/add_job_admin.view.php';
    }
}
