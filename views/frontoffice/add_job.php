<?php
// views/frontoffice/add_job.php
require_once __DIR__ . '/../../controllers/AddController.php';
define('BASE_URL', '/projet22/');
(new AddController())->execute();
