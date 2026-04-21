<?php
// views/frontoffice/home.php
require_once __DIR__ . '/../../controllers/ListController.php';
require_once __DIR__ . '/../../controllers/DeleteController.php';
define('BASE_URL', '/projet22/');
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    (new DeleteController())->execute((int)$_GET['id']);
} else {
    (new ListController())->execute();
}
