<?php
// controllers/AdminDashboardController.php
require_once __DIR__ . '/../Models/AdminDashboard.php';

class AdminDashboardController {
    public function execute() {
        $model = new AdminDashboard();
        $filtre = $_GET['filtre'] ?? 'all';
        $q = $_GET['titre'] ?? '';
        $d = $_GET['date'] ?? '';
        
        if (isset($_GET['action']) && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            switch ($_GET['action']) {
                case 'approve':
                    $model->updateStatut($id, 'approved');
                    header('Location: admin_missions.php?success=approved'); exit;
                case 'reject':
                    $model->updateStatut($id, 'rejected');
                    header('Location: admin_missions.php?success=rejected'); exit;
                case 'delete':
                    $model->delete($id);
                    header('Location: admin_missions.php?success=deleted'); exit;
            }
        }

        if (!empty($q) || !empty($d)) { 
            $offres = $model->search($q, $d); 
        } elseif ($filtre !== 'all') { 
            $offres = $model->getByStatut($filtre); 
        } else { 
            $offres = $model->getAll(); 
        }

        $totalAll = $model->countAll();
        $totalPending = $model->countByStatut('pending');
        $totalApproved = $model->countByStatut('approved');
        $totalRejected = $model->countByStatut('rejected');

        include __DIR__ . '/../views/backoffice/dashboard.view.php';
    }
}
