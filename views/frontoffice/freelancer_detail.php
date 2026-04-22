<?php
// views/frontoffice/freelancer_detail.php
require_once __DIR__ . '/../../controllers/FreelancerDetailController.php';

$id = $_GET['id'] ?? 0;
$controller = new FreelancerDetailController();
$controller->execute($id);
