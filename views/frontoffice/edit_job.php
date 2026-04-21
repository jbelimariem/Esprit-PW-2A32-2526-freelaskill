<?php
// views/frontoffice/edit_job.php
require_once __DIR__ . '/../../controllers/EditController.php';
define('BASE_URL', '/projet22/');
(new EditController())->execute((int)($_GET['id'] ?? 0));
