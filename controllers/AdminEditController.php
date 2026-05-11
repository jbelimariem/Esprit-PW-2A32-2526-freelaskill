<?php
// controllers/AdminEditController.php
require_once __DIR__ . '/../Models/AdminEdit.php';

class AdminEditController {
    public function execute($id) {
        $model = new AdminEdit();
        $offre = $model->getById($id);
        if (!$offre) { header('Location: dashboard.php'); exit; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $offre->setTitre(trim($_POST['titre'] ?? ''));
            $offre->setDescription(trim($_POST['description'] ?? ''));
            $offre->setBudget($_POST['budget'] ?? 0);
            $offre->setDelai(trim($_POST['delai'] ?? ''));
            $offre->setStatut($_POST['statut'] ?? 'pending');
            $model->update($offre);
            header('Location: dashboard.php?success=updated'); exit;
        }
        include __DIR__ . '/../views/backoffice/edit_job_admin.view.php';
    }
}
