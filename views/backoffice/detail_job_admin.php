<?php
// views/backoffice/detail_job_admin.php
require_once __DIR__ . '/../../controllers/AdminDetailController.php';
define('BASE_URL', '/projet22/');
(new AdminDetailController())->execute((int)($_GET['id'] ?? 0));
