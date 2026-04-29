<?php
// controllers/FreelancerApplicationsController.php
require_once __DIR__ . '/../Models/FreelancerApplications.php';

class FreelancerApplicationsController {
    public function execute() {
        $model = new FreelancerApplications();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
            $model->cancelApplication((int)$_POST['app_id']);
            header('Location: freelancer_applications.php?success=cancelled');
            exit;
        }

        $search = $_GET['search'] ?? '';
        $applications = $model->getMyApplications($search);
        
        include __DIR__ . '/../views/frontoffice/freelancer_applications.view.php';
    }
}
