<?php
// views/frontoffice/cv.php
require_once __DIR__ . '/../../controllers/CVController.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$controller = new CVController();
$controller->execute($id);
