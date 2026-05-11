<?php
// views/frontoffice/register.php
require_once __DIR__ . '/../../controllers/AuthController.php';

(new AuthController())->executeRegisterPage();
