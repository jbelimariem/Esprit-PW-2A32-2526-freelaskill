<?php
// controllers/ClientFreelancersController.php
require_once __DIR__ . '/../Models/ClientFreelancers.php';

class ClientFreelancersController {
    public function execute() {
        $model = new ClientFreelancers();
        $freelancers = $model->getAllFreelancers();
        
        include __DIR__ . '/../views/frontoffice/client_freelancers.view.php';
    }
}
