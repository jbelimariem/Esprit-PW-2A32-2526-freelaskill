<?php
// views/backoffice/admin_missions.php — Interface admin : Flux de Missions
require_once __DIR__ . '/../../controllers/AdminDashboardController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

(new AdminDashboardController())->execute();
