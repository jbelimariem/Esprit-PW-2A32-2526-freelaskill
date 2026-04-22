<?php
// controllers/EditController.php
<<<<<<< HEAD
=======
require_once __DIR__ . '/../Models/JobOffer.php';
>>>>>>> faca6fd (sss)
require_once __DIR__ . '/../Models/Edit.php';

class EditController {
    private $model;
    public function __construct() { $this->model = new Edit(); }

    public function execute($id) {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
<<<<<<< HEAD
            $data = [
                'id' => $id,
                'titre' => trim($_POST['titre'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competences' => trim($_POST['competences'] ?? ''),
                'budget' => $_POST['budget'] ?? '',
                'delai' => trim($_POST['delai'] ?? ''),
                'statut' => $_POST['statut'] ?? 'pending'
            ];
            if (empty($data['titre'])) $errors[] = "Le titre est requis.";
            if (empty($errors)) {
                $this->model->update($data);
                header('Location: home.php?success=updated'); exit;
            }
=======
            $offre->setTitre(trim($_POST['titre'] ?? ''));
            $offre->setDescription(trim($_POST['description'] ?? ''));
            $offre->setCompetences(trim($_POST['competences'] ?? ''));
            $offre->setBudget($_POST['budget'] ?? 0);
            $offre->setDelai(trim($_POST['delai'] ?? ''));
            $offre->setStatut('pending');
            $this->model->update($offre);
            header('Location: home.php?success=updated'); exit;
>>>>>>> faca6fd (sss)
        }
        $offre = $this->model->getById($id);
        include __DIR__ . '/../views/frontoffice/edit_job.view.php';
    }
}
