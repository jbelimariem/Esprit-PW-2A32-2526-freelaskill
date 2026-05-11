<?php
// controllers/FreelancerDashboardController.php
require_once __DIR__ . '/../Models/FreelancerList.php';

class FreelancerDashboardController {
    public function execute() {
        $model = new FreelancerList();
        
        $q = $_GET['q'] ?? '';
        $min = $_GET['min_price'] ?? 0;
        $max = $_GET['max_price'] ?? 999999;
        
        $offres = $model->getApprovedJobs($q, $min, $max);
        
        include __DIR__ . '/../views/frontoffice/freelancer_home.view.php';
    }
}
