<?php
// controllers/AdminEditController.php
require_once __DIR__ . '/../Models/AdminEdit.php';

class AdminEditController {
    public function execute($id) {
        $model = new AdminEdit();
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
            header('Location: dashboard.php?success=updated'); exit;
        }
        $offre = $model->getById($id);
        include __DIR__ . '/../views/backoffice/edit_job_admin.view.php';
    }
}
