<?php
// controllers/ListController.php — Action: Liste et Recherche Front
require_once __DIR__ . '/../Models/JobOffer.php';
require_once __DIR__ . '/../Models/List.php';

class ListController {
    private $model;
    public function __construct() { $this->model = new ListModel(); }

    public function execute() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $clientId = $_SESSION['user_id'] ?? null;

        $action = $_GET['action'] ?? '';
        $q = $_GET['q'] ?? '';
        $d = $_GET['d'] ?? '';
        $budget = $_GET['budget'] ?? '';
        if ($action === 'delete' && isset($_GET['id'])) {
            require_once __DIR__ . '/DeleteController.php';
            (new DeleteController())->execute($_GET['id']);
        }

        if (!empty($q) || !empty($d) || !empty($budget)) {
            $offres = $this->model->search($q, $d, $budget, $clientId);
        } else {
            $offres = $this->model->getAll($clientId);
        }

        include __DIR__ . '/../views/frontoffice/home.view.php';
    }
}
