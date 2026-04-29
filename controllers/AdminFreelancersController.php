<?php
// controllers/AdminFreelancersController.php
require_once __DIR__ . '/../Models/AdminFreelancers.php';

class AdminFreelancersController {
    public function execute() {
        $model = new AdminFreelancers();
        
        $q = trim($_GET['q'] ?? '');

        $stats = $model->getGlobalStats();
        $freelancers = $model->getFreelancersWithApplications($q);

        include __DIR__ . '/../views/backoffice/admin_freelancers.view.php';
    }
}
