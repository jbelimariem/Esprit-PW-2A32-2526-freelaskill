<?php
// controllers/AdminDetailController.php
require_once __DIR__ . '/../Models/AdminDetail.php';

class AdminDetailController {
    public function execute($id) {
        $model = new AdminDetail();

        // Gérer les actions sur les candidatures
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_app'])) {
            $model->updateApplicationStatus((int)$_POST['app_id'], $_POST['action_app']);
            header("Location: detail_job_admin.php?id=$id&success=" . $_POST['action_app']);
            exit;
        }

        $offre = $model->getJobById($id);
        if (!$offre) { header('Location: dashboard.php'); exit; }
        
        $candidats = $model->getApplicationsByJobId($id);
        include __DIR__ . '/../views/backoffice/detail_job_admin.view.php';
    }
}
