<?php
// controllers/ListController.php — Action: Liste et Recherche Front
<<<<<<< HEAD
=======
require_once __DIR__ . '/../Models/JobOffer.php';
>>>>>>> faca6fd (sss)
require_once __DIR__ . '/../Models/List.php';

class ListController {
    private $model;
    public function __construct() { $this->model = new ListModel(); }

    public function execute() {
        $q = $_GET['q'] ?? '';
        $d = $_GET['date'] ?? '';
        $budget = $_GET['budget'] ?? '';

        if (!empty($q) || !empty($d) || !empty($budget)) {
            $offres = $this->model->search($q, $d, $budget);
        } else {
            $offres = $this->model->getAll();
        }

        include __DIR__ . '/../views/frontoffice/home.view.php';
    }
}
