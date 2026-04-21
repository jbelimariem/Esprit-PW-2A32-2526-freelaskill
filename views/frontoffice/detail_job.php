<?php
// views/frontoffice/detail_job.php
require_once __DIR__ . '/../../controllers/DetailController.php';
define('BASE_URL', '/projet22/');
(new DetailController())->execute((int)($_GET['id'] ?? 0));
