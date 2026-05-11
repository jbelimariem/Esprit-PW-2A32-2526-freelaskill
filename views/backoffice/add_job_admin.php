<?php
// views/backoffice/add_job_admin.php
require_once __DIR__ . '/../../controllers/AdminAddController.php';
define('BASE_URL', '/projet22/');
(new AdminAddController())->execute();
