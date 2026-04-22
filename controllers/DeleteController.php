<?php
// controllers/DeleteController.php
require_once __DIR__ . '/../Models/Delete.php';

class DeleteController {
    public function execute($id) {
<<<<<<< HEAD
        $model = new Delete();
        $model->deleteJob($id);
=======
        (new Delete())->delete($id);
>>>>>>> faca6fd (sss)
        header('Location: home.php?success=deleted'); exit;
    }
}
