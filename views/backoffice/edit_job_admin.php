<?php
// views/backoffice/edit_job_admin.php
require_once __DIR__ . '/../../controllers/AdminEditController.php';
define('BASE_URL', '/projet22/');
(new AdminEditController())->execute((int)($_GET['id'] ?? 0));
