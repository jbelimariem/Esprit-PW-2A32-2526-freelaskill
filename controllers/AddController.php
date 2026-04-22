<?php
// controllers/AddController.php
require_once __DIR__ . '/../Models/Add.php';

class AddController {
    private $model;
    public function __construct() { $this->model = new Add(); }

    private function validate($data) {
        $errors = [];
        if (empty($data['titre'])) $errors[] = "Le titre est requis.";
        if (empty($data['budget']) || !is_numeric($data['budget'])) $errors[] = "Le budget doit être un nombre.";
        return $errors;
    }

    public function execute() {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => trim($_POST['titre'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competences' => trim($_POST['competences'] ?? ''),
                'budget' => $_POST['budget'] ?? '',
                'delai' => trim($_POST['delai'] ?? ''),
                'statut' => 'pending',
                'client_id' => 1
            ];
            $errors = $this->validate($data);
            if (empty($errors)) {
                $this->model->save($data);
                header('Location: home.php?success=added'); exit;
            }
        }
        include __DIR__ . '/../views/frontoffice/add_job.view.php';
    }
}
