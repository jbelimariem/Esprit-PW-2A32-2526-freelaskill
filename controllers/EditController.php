<?php
// controllers/EditController.php
require_once __DIR__ . '/../Models/JobOffer.php';

class EditController {
    private $model;
    public function __construct() { $this->model = new JobOffer(); }

    public function execute($id) {
        $offre = $this->model->getById($id);
        if (!$offre) { header('Location: home.php'); exit; }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $offre->setTitre(trim($_POST['titre'] ?? ''));
            $offre->setDescription(trim($_POST['description'] ?? ''));
            $offre->setCompetences(trim($_POST['competences'] ?? ''));
            $offre->setBudget($_POST['budget'] ?? 0);
            $offre->setDelai(trim($_POST['delai'] ?? ''));
            $offre->setStatut('pending');
            $offre->update();
            header('Location: home.php?success=updated'); exit;
        }
        include __DIR__ . '/../views/frontoffice/edit_job.view.php';
    }
}
