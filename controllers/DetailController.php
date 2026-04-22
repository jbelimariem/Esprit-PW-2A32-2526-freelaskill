<?php
// controllers/DetailController.php
require_once __DIR__ . '/../Models/Detail.php';

class DetailController {
    public function execute($id) {
        $model = new Detail();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_app'])) {
            $model->updateApplicationStatus((int)$_POST['app_id'], $_POST['action_app']);
            header("Location: detail_job.php?id=$id&success=" . $_POST['action_app']); exit;
        }
        $offre = $model->getJobById($id);
        if (!$offre) { header('Location: home.php'); exit; }
        $candidats = $model->getApplicationsByJobId($id);
        include __DIR__ . '/../views/frontoffice/detail_job.view.php';
    }
}
