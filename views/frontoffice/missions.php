<?php
// views/frontoffice/missions.php — Point d'entrée pour les missions (Dashboard Client)
require_once __DIR__ . '/../../controllers/ListController.php';
(new ListController())->execute();
