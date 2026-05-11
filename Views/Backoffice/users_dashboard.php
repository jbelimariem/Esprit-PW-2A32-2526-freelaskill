<?php
// views/backoffice/users_dashboard.php
require_once __DIR__ . '/../../controllers/BackofficeController.php';

(new BackofficeController())->executeUsersDashboardPage();
