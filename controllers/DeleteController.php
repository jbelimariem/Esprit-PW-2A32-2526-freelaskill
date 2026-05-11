<?php
// controllers/DeleteController.php
require_once __DIR__ . '/../Models/Delete.php';

class DeleteController {
    public function execute($id) {
        (new Delete())->delete($id);
        header('Location: missions.php?success=deleted'); exit;
    }
}
