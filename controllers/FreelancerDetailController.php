<?php
// controllers/FreelancerDetailController.php
require_once __DIR__ . '/../Models/FreelancerDetail.php';

class FreelancerDetailController {
    public function execute($id) {
        $model = new FreelancerDetail();

        // ──────────────────────────────────────────
        // Handle job application (POST)
        // ──────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply') {

            $name         = trim($_POST['name']         ?? '');
            $email        = trim($_POST['email']        ?? '');
            $phone        = trim($_POST['phone']        ?? '');
            $job_title    = trim($_POST['job_title']    ?? '');
            $cover_letter = trim($_POST['cover_letter'] ?? '');
            $cv_path      = '';

            // ── Validation basique ──
            $errors = [];
            if (empty($name))         $errors[] = 'Le nom est requis.';
            if (empty($email))        $errors[] = "L'email est requis.";
            if (empty($cover_letter)) $errors[] = 'La lettre de motivation est requise.';

            // ── Upload du CV ──
            if (!empty($_FILES['cv_file']['name'])) {
                $allowed = ['pdf', 'doc', 'docx'];
                $ext = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed)) {
                    $errors[] = 'Format de CV non autorisé (PDF, DOC, DOCX uniquement).';
                } elseif ($_FILES['cv_file']['size'] > 5 * 1024 * 1024) {
                    $errors[] = 'Le CV ne doit pas dépasser 5 Mo.';
                } else {
                    $upload_dir = __DIR__ . '/../uploads/cv/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    $filename = 'cv_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $upload_dir . $filename)) {
                        $cv_path = 'uploads/cv/' . $filename;
                    } else {
                        $errors[] = "Erreur lors de l'upload du CV.";
                    }
                }
            }

            // ── Si pas d'erreurs, on insère ──
            if (empty($errors)) {
                $model->applyJob($id, $name, $email, $phone, $job_title, $cover_letter, $cv_path);
                header('Location: freelancer_applications.php?success=applied');
                exit;
            }

            // En cas d'erreur, on repasse à la vue avec les erreurs
            $offre = $model->getJob($id);
            $has_applied = false;
            $apply_errors = $errors;
            $form_data = compact('name', 'email', 'phone', 'job_title', 'cover_letter');
            include __DIR__ . '/../views/frontoffice/freelancer_detail.view.php';
            exit;
        }

        // ──────────────────────────────────────────
        // Affichage de la page (GET)
        // ──────────────────────────────────────────
        $offre = $model->getJob($id);

        if (!$offre) {
            header('Location: freelancer_home.php');
            exit;
        }

        // Pas de système d'auth : on utilise la session ou email par défaut
        $freelancer_email = $_SESSION['email'] ?? 'alexandre.d@example.com';
        $has_applied      = $model->hasApplied($id, $freelancer_email);
        $apply_errors     = [];
        $form_data        = [];

        include __DIR__ . '/../views/frontoffice/freelancer_detail.view.php';
    }
}
