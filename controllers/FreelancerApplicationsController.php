<?php
// controllers/FreelancerApplicationsController.php
require_once __DIR__ . '/../Models/FreelancerApplications.php';

class FreelancerApplicationsController {
    public function execute() {
        $model = new FreelancerApplications();

        // ── Annuler une candidature ──
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
            $model->cancelApplication((int)$_POST['app_id']);
            header('Location: freelancer_applications.php?success=cancelled');
            exit;
        }

        // ── Modifier une candidature ──
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
            $app_id       = (int)$_POST['app_id'];
            $name         = trim($_POST['name']         ?? '');
            $email        = trim($_POST['email']        ?? '');
            $phone        = trim($_POST['phone']        ?? '');
            $job_title    = trim($_POST['job_title']    ?? '');
            $cover_letter = trim($_POST['cover_letter'] ?? '');
            $cv_path      = null; // null = pas de changement de fichier

            $errors = [];
            if (empty($name))         $errors[] = 'Le nom est requis.';
            if (empty($email))        $errors[] = "L'email est requis.";
            if (empty($cover_letter)) $errors[] = 'La lettre de motivation est requise.';

            // Upload nouveau CV si fourni
            if (!empty($_FILES['cv_file']['name'])) {
                $allowed = ['pdf', 'doc', 'docx'];
                $ext = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $errors[] = 'Format de CV non autorisé (PDF, DOC, DOCX uniquement).';
                } elseif ($_FILES['cv_file']['size'] > 5 * 1024 * 1024) {
                    $errors[] = 'Le CV ne doit pas dépasser 5 Mo.';
                } else {
                    $upload_dir = __DIR__ . '/../uploads/cv/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    $filename = 'cv_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $upload_dir . $filename)) {
                        $cv_path = 'uploads/cv/' . $filename;
                    } else {
                        $errors[] = "Erreur lors de l'upload du CV.";
                    }
                }
            }

            if (empty($errors)) {
                $model->updateApplication($app_id, $name, $email, $phone, $job_title, $cover_letter, $cv_path);
                header('Location: freelancer_applications.php?success=updated');
                exit;
            }

            // Retour avec erreurs
            $edit_errors  = $errors;
            $edit_app_id  = $app_id;
            $search       = $_GET['search'] ?? '';
            $applications = $model->getMyApplications($search);
            $success      = null;
            include __DIR__ . '/../views/frontoffice/freelancer_applications.view.php';
            exit;
        }

        // ── Affichage (GET) ──
        $search       = $_GET['search'] ?? '';
        $applications = $model->getMyApplications($search);
        $edit_errors  = [];
        $edit_app_id  = null;
        $success      = $_GET['success'] ?? null;

        include __DIR__ . '/../views/frontoffice/freelancer_applications.view.php';
    }
}
