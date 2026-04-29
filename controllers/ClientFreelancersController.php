<?php
// controllers/ClientFreelancersController.php
require_once __DIR__ . '/../Models/ClientFreelancers.php';

class ClientFreelancersController {
    public function execute() {
        $q = $_GET['q'] ?? '';
        
        $model = new ClientFreelancers();
        $freelancers = $model->getAllFreelancers($q);
        
        include __DIR__ . '/../views/frontoffice/client_freelancers.view.php';
    }
}
