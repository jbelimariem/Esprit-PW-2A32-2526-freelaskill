<?php
// controllers/AdminDetailController.php
require_once __DIR__ . '/../Models/JobOffer.php';
require_once __DIR__ . '/../Models/JobApplication.php';

class AdminDetailController {
    public function execute($id) {
        $model = new JobOffer();
        $appModel = new JobApplication();

        // Gérer les actions sur les candidatures
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_app'])) {
            $appModel->updateStatus((int)$_POST['app_id'], $_POST['action_app']);
            header("Location: detail_job_admin.php?id=$id&success=" . $_POST['action_app']);
            exit;
        }

        $offre = $model->getById($id);
        if (!$offre) { header('Location: dashboard.php'); exit; }
        
        $candidats = $appModel->getByJobId($id);
        include __DIR__ . '/../views/backoffice/detail_job_admin.view.php';
    }
}
