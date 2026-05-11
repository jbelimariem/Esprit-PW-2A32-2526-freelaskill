<?php
// views/frontoffice/freelancer_home.php
require_once __DIR__ . '/../../controllers/FreelancerDashboardController.php';

$controller = new FreelancerDashboardController();
$controller->execute();
