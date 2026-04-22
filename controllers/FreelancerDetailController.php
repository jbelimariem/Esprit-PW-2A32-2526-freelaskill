<?php
// controllers/FreelancerDetailController.php
require_once __DIR__ . '/../Models/FreelancerDetail.php';

class FreelancerDetailController {
    public function execute($id) {
        $model = new FreelancerDetail();
        $offre = $model->getJob($id);
        
        if (!$offre) {
            header('Location: freelancer_home.php');
            exit;
        }
        
        include __DIR__ . '/../views/frontoffice/freelancer_detail.view.php';
    }
}
