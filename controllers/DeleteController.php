<?php
// controllers/DeleteController.php
require_once __DIR__ . '/../Models/Delete.php';

class DeleteController {
    public function execute($id) {
        $model = new Delete();
        $model->deleteJob($id);
        header('Location: home.php?success=deleted'); exit;
    }
}
