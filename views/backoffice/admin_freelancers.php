<?php
// views/backoffice/admin_freelancers.php
require_once __DIR__ . '/../../controllers/AdminFreelancersController.php';

$controller = new AdminFreelancersController();
$controller->execute();
