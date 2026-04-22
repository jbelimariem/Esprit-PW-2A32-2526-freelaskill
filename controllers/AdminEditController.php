<?php
// controllers/AdminEditController.php
require_once __DIR__ . '/../Models/AdminEdit.php';

class AdminEditController {
    public function execute($id) {
        $model = new AdminEdit();
<<<<<<< HEAD
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $id,
                'titre' => trim($_POST['titre'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competences' => trim($_POST['competences'] ?? ''), // Keep old if missing
                'budget' => $_POST['budget'] ?? 0,
                'delai' => trim($_POST['delai'] ?? ''),
                'statut' => $_POST['statut'] ?? 'pending'
            ];
            $model->update($data);
=======
        $offre = $model->getById($id);
        if (!$offre) { header('Location: dashboard.php'); exit; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $offre->setTitre(trim($_POST['titre'] ?? ''));
            $offre->setDescription(trim($_POST['description'] ?? ''));
            $offre->setBudget($_POST['budget'] ?? 0);
            $offre->setDelai(trim($_POST['delai'] ?? ''));
            $offre->setStatut($_POST['statut'] ?? 'pending');
            $model->update($offre);
>>>>>>> faca6fd (sss)
            header('Location: dashboard.php?success=updated'); exit;
        }
        $offre = $model->getById($id);
        include __DIR__ . '/../views/backoffice/edit_job_admin.view.php';
    }
}
