<?php
// views/frontoffice/login.php
require_once __DIR__ . '/../../controllers/AuthController.php';

(new AuthController())->executeLoginPage();
