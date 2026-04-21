<?php
// controllers/AdminStatusController.php
require_once __DIR__ . '/../Models/JobOffer.php';

class AdminStatusController {
    public function execute($id, $action) {
        $model = new JobOffer();
        if ($action === 'delete') { $model->delete($id); $s = 'deleted'; }
        else { $s = ($action === 'approve') ? 'approved' : 'rejected'; $model->updateStatut($id, $s); }
        header("Location: dashboard.php?success=$s"); exit;
    }
}
