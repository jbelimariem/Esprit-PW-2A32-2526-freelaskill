<?php
// controllers/FreelancerDetailController.php
require_once __DIR__ . '/../Models/FreelancerDetail.php';

class FreelancerDetailController {
    public function execute($id) {
        $model = new FreelancerDetail();
        
        // Handle job application
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply') {
            // Mocking freelancer data since there's no auth system
            $freelancer_name = "Alexandre Dupont";
            $freelancer_email = "alexandre.d@example.com";
            $freelancer_title = "Développeur Full Stack";
            
            $model->applyJob($id, $freelancer_name, $freelancer_email, $freelancer_title);
            header('Location: freelancer_applications.php?success=applied');
            exit;
        }

        $offre = $model->getJob($id);
        
        if (!$offre) {
            header('Location: freelancer_home.php');
            exit;
        }
        $freelancer_email = "alexandre.d@example.com"; // Mock user
        $has_applied = $model->hasApplied($id, $freelancer_email);
        
        include __DIR__ . '/../views/frontoffice/freelancer_detail.view.php';
    }
}
