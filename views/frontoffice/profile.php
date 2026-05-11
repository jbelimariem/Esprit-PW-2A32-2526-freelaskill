<?php
// views/frontoffice/profile.php
require_once __DIR__ . '/../../controllers/ProfileController.php';

(new ProfileController())->executeProfilePage();
