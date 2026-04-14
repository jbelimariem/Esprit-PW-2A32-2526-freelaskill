<?php
// controllers/JobOfferController.php

require_once __DIR__ . '/../Models/JobOffer.php';

class JobOfferController {

    private $model;

    public function __construct() {
        $this->model = new JobOffer();
    }

    // ================================================================
    // VALIDATION DES DONNÉES
    // ================================================================
    private function validate($data) {
        $errors = [];

        if (empty(trim($data['titre'] ?? ''))) {
            $errors[] = "Le titre est obligatoire.";
        } elseif (strlen(trim($data['titre'])) < 5) {
            $errors[] = "Le titre doit contenir au moins 5 caractères.";
        } elseif (strlen(trim($data['titre'])) > 255) {
            $errors[] = "Le titre ne doit pas dépasser 255 caractères.";
        }

        if (empty(trim($data['description'] ?? ''))) {
            $errors[] = "La description est obligatoire.";
        } elseif (strlen(trim($data['description'])) < 20) {
            $errors[] = "La description doit contenir au moins 20 caractères.";
        } elseif (strlen(trim($data['description'])) > 2000) {
            $errors[] = "La description ne doit pas dépasser 2000 caractères.";
        }

        if (empty(trim($data['competences'] ?? ''))) {
            $errors[] = "Les compétences requises sont obligatoires.";
        }

        if (empty($data['budget']) || !is_numeric($data['budget'])) {
            $errors[] = "Le budget doit être un nombre valide.";
        } elseif ((float)$data['budget'] <= 0) {
            $errors[] = "Le budget doit être supérieur à 0.";
        }

        if (empty(trim($data['delai'] ?? ''))) {
            $errors[] = "Le délai est obligatoire.";
        }

        return $errors;
    }

    // ================================================================
    // FRONTOFFICE — LISTE CLIENT
    // ================================================================
    public function index() {
        $titre  = $_GET['titre'] ?? '';
        $date   = $_GET['date']  ?? '';

        if (!empty($titre) || !empty($date)) {
            $offres = $this->model->search($titre, $date);
        } else {
            $offres = $this->model->getAll();
        }

        include __DIR__ . '/../views/frontoffice/home.php';
    }

    // ================================================================
    // FRONTOFFICE — AFFICHER FORMULAIRE AJOUT
    // ================================================================
    public function showAddForm() {
        $errors = [];
        include __DIR__ . '/../views/frontoffice/add_job.php';
    }

    // ================================================================
    // FRONTOFFICE — CRÉER UNE OFFRE (POST)
    // ================================================================
    public function create() {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre'       => trim($_POST['titre']       ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competences' => trim($_POST['competences'] ?? ''),
                'budget'      => $_POST['budget']           ?? '',
                'delai'       => trim($_POST['delai']       ?? ''),
                'client_id'   => 1  // statique pour l'instant
            ];
            $errors = $this->validate($data);
            if (empty($errors)) {
                $this->model->create($data);
                header('Location: ' . BASE_URL . 'views/frontoffice/home.php?success=added');
                exit;
            }
        }
        include __DIR__ . '/../views/frontoffice/add_job.php';
    }

    // ================================================================
    // FRONTOFFICE — AFFICHER FORMULAIRE MODIFICATION
    // ================================================================
    public function showEditForm($id) {
        $offre  = $this->model->getById($id);
        $errors = [];
        if (!$offre) {
            header('Location: ' . BASE_URL . 'views/frontoffice/home.php');
            exit;
        }
        include __DIR__ . '/../views/frontoffice/edit_job.php';
    }

    // ================================================================
    // FRONTOFFICE — MODIFIER UNE OFFRE (POST)
    // ================================================================
    public function update($id) {
        $offre  = $this->model->getById($id);
        $errors = [];
        if (!$offre) {
            header('Location: ' . BASE_URL . 'views/frontoffice/home.php');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre'       => trim($_POST['titre']       ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competences' => trim($_POST['competences'] ?? ''),
                'budget'      => $_POST['budget']           ?? '',
                'delai'       => trim($_POST['delai']       ?? ''),
                'statut'      => $offre['statut']  // conserve le statut actuel
            ];
            $errors = $this->validate($data);
            if (empty($errors)) {
                $this->model->update($id, $data);
                header('Location: ' . BASE_URL . 'views/frontoffice/home.php?success=updated');
                exit;
            }
        }
        include __DIR__ . '/../views/frontoffice/edit_job.php';
    }

    // ================================================================
    // FRONTOFFICE — SUPPRIMER UNE OFFRE
    // ================================================================
    public function delete($id) {
        $this->model->delete($id);
        header('Location: ' . BASE_URL . 'views/frontoffice/home.php?success=deleted');
        exit;
    }

    // ================================================================
    // FRONTOFFICE — DÉTAIL UNE OFFRE
    // ================================================================
    public function show($id) {
        $offre = $this->model->getById($id);
        if (!$offre) {
            header('Location: ' . BASE_URL . 'views/frontoffice/home.php');
            exit;
        }
        include __DIR__ . '/../views/frontoffice/detail_job.php';
    }

    // ================================================================
    // FRONTOFFICE — EXPORT CSV
    // ================================================================
    public function exportCsv() {
        $offres = $this->model->getAll();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="offres_emploi_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['ID', 'Titre', 'Compétences', 'Budget (DT)', 'Délai', 'Statut', 'Date de création'], ';');
        foreach ($offres as $offre) {
            fputcsv($output, [
                $offre['id'],
                $offre['titre'],
                $offre['competences'],
                $offre['budget'],
                $offre['delai'],
                $offre['statut'],
                $offre['date_creation']
            ], ';');
        }
        fclose($output);
        exit;
    }

    // ================================================================
    // BACKOFFICE — LISTE ADMIN
    // ================================================================
    public function adminIndex() {
        $filtre       = $_GET['filtre'] ?? 'all';
        $searchTitre  = $_GET['titre']  ?? '';
        $searchDate   = $_GET['date']   ?? '';

        if (!empty($searchTitre) || !empty($searchDate)) {
            $offres = $this->model->search($searchTitre, $searchDate);
        } elseif ($filtre !== 'all') {
            $offres = $this->model->getByStatut($filtre);
        } else {
            $offres = $this->model->getAll();
        }

        $totalAll      = $this->model->countAll();
        $totalPending  = $this->model->countByStatut('pending');
        $totalApproved = $this->model->countByStatut('approved');
        $totalRejected = $this->model->countByStatut('rejected');

        include __DIR__ . '/../views/backoffice/dashboard.php';
    }

    // ================================================================
    // BACKOFFICE — APPROUVER / REJETER
    // ================================================================
    public function adminUpdateStatut($id, $statut) {
        $this->model->updateStatut($id, $statut);
        header('Location: ' . BASE_URL . 'views/backoffice/dashboard.php?success=' . $statut);
        exit;
    }

    // ================================================================
    // BACKOFFICE — AJOUTER (form + POST)
    // ================================================================
    public function adminCreate() {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre'       => trim($_POST['titre']       ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competences' => trim($_POST['competences'] ?? ''),
                'budget'      => $_POST['budget']           ?? '',
                'delai'       => trim($_POST['delai']       ?? ''),
                'client_id'   => 1
            ];
            $errors = $this->validate($data);
            if (empty($errors)) {
                $newId = $this->model->create($data);
                // Admin peut aussi définir le statut
                $statut = trim($_POST['statut'] ?? 'pending');
                if (in_array($statut, ['pending','approved','rejected'])) {
                    $this->model->updateStatut($newId, $statut);
                }
                header('Location: ' . BASE_URL . 'views/backoffice/dashboard.php?success=added');
                exit;
            }
        }
        include __DIR__ . '/../views/backoffice/add_job_admin.php';
    }

    // ================================================================
    // BACKOFFICE — MODIFIER (form + POST)
    // ================================================================
    public function adminEdit($id) {
        $offre  = $this->model->getById($id);
        $errors = [];
        if (!$offre) {
            header('Location: ' . BASE_URL . 'views/backoffice/dashboard.php');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $statut = trim($_POST['statut'] ?? 'pending');
            if (!in_array($statut, ['pending','approved','rejected'])) {
                $statut = 'pending';
            }
            $data = [
                'titre'       => trim($_POST['titre']       ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competences' => trim($_POST['competences'] ?? ''),
                'budget'      => $_POST['budget']           ?? '',
                'delai'       => trim($_POST['delai']       ?? ''),
                'statut'      => $statut
            ];
            $errors = $this->validate($data);
            if (empty($errors)) {
                $this->model->update($id, $data);
                header('Location: ' . BASE_URL . 'views/backoffice/dashboard.php?success=updated');
                exit;
            }
        }
        include __DIR__ . '/../views/backoffice/edit_job_admin.php';
    }

    // ================================================================
    // BACKOFFICE — SUPPRIMER
    // ================================================================
    public function adminDelete($id) {
        $this->model->delete($id);
        header('Location: ' . BASE_URL . 'views/backoffice/dashboard.php?success=deleted');
        exit;
    }

    // ================================================================
    // BACKOFFICE — DÉTAIL
    // ================================================================
    public function adminShow($id) {
        $offre = $this->model->getById($id);
        if (!$offre) {
            header('Location: ' . BASE_URL . 'views/backoffice/dashboard.php');
            exit;
        }
        include __DIR__ . '/../views/backoffice/detail_job_admin.php';
    }

    // ================================================================
    // BACKOFFICE — EXPORT CSV
    // ================================================================
    public function adminExportCsv() {
        $offres = $this->model->getAll();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="admin_offres_emploi_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['ID', 'Titre', 'Description', 'Compétences', 'Budget (DT)', 'Délai', 'Statut', 'Client ID', 'Date de création'], ';');
        foreach ($offres as $offre) {
            fputcsv($output, [
                $offre['id'],
                $offre['titre'],
                $offre['description'],
                $offre['competences'],
                $offre['budget'],
                $offre['delai'],
                $offre['statut'],
                $offre['client_id'],
                $offre['date_creation']
            ], ';');
        }
        fclose($output);
        exit;
    }
}
?>
