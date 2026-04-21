<?php
// controllers/DetailController.php
require_once __DIR__ . '/../Models/JobOffer.php';
require_once __DIR__ . '/../Models/JobApplication.php';

class DetailController {
    public function execute($id) {
        $model = new JobOffer();
        $appModel = new JobApplication();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_app'])) {
            $appModel->updateStatus((int)$_POST['app_id'], $_POST['action_app']);
            header("Location: detail_job.php?id=$id&success=" . $_POST['action_app']); exit;
        }
        $offre = $model->getById($id);
        if (!$offre) { header('Location: home.php'); exit; }
        $candidats = $appModel->getByJobId($id);
        include __DIR__ . '/../views/frontoffice/detail_job.view.php';
    }
}
