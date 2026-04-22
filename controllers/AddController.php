<?php
// controllers/AddController.php
<<<<<<< HEAD
=======
require_once __DIR__ . '/../Models/JobOffer.php';
>>>>>>> faca6fd (sss)
require_once __DIR__ . '/../Models/Add.php';

class AddController {
    private $model;
    public function __construct() { $this->model = new Add(); }

    private function validate($data) {
        $errors = [];
        if (empty($data['titre'])) $errors['titre'] = "Le titre est requis.";
        if (empty($data['budget'])) $errors['budget'] = "Le budget est requis.";
        elseif (!is_numeric($data['budget'])) $errors['budget'] = "Le budget doit être un nombre.";
        if (empty($data['description'])) $errors['description'] = "La description est requise.";
        if (empty($data['competences'])) $errors['competences'] = "Les compétences sont requises.";
        if (empty($data['delai'])) $errors['delai'] = "Le délai est requis.";
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
<<<<<<< HEAD
                $this->model->save($data);
=======
                $offre = new JobOffer($data);
                $this->model->save($offre);
>>>>>>> faca6fd (sss)
                header('Location: home.php?success=added'); exit;
            }
        }
        include __DIR__ . '/../views/frontoffice/add_job.view.php';
    }
}
