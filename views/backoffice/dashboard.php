<?php
// views/backoffice/dashboard.php
require_once __DIR__ . '/../../controllers/AdminDashboardController.php';
require_once __DIR__ . '/../../controllers/AdminStatusController.php';
define('BASE_URL', '/projet22/');
if (!empty($_GET['action']) && !empty($_GET['id'])) {
    (new AdminStatusController())->execute((int)$_GET['id'], $_GET['action']);
} else {
    (new AdminDashboardController())->execute();
}
