<?php
// controllers/DeleteController.php
require_once __DIR__ . '/../Models/JobOffer.php';

class DeleteController {
    public function execute($id) {
        (new JobOffer())->delete($id);
        header('Location: home.php?success=deleted'); exit;
    }
}
