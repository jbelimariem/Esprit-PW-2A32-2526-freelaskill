<?php
// views/frontoffice/client_freelancers.php
require_once __DIR__ . '/../../controllers/ClientFreelancersController.php';

$controller = new ClientFreelancersController();
$controller->execute();
