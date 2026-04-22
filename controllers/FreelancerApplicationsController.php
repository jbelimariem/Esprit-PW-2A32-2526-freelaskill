<?php
// controllers/FreelancerApplicationsController.php
require_once __DIR__ . '/../Models/FreelancerApplications.php';

class FreelancerApplicationsController {
    public function execute() {
        $model = new FreelancerApplications();
        $applications = $model->getMyApplications();
        
        include __DIR__ . '/../views/frontoffice/freelancer_applications.view.php';
    }
}
