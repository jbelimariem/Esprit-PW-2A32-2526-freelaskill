<?php
// views/frontoffice/google_callback.php
session_start();
require_once __DIR__ . '/../../controllers/UserController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = '';
    $name = '';
    $lastname = '';

    // If Google sends the raw JWT token (Implicit Flow or GSI)
    $jwt = $_POST['credential'] ?? $_POST['id_token'] ?? '';
    
    if (!empty($jwt)) {
        $parts = explode('.', $jwt);
        if (count($parts) === 3) {
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            if ($payload && isset($payload['email'])) {
                $email = $payload['email'];
                $name = $payload['given_name'] ?? '';
                $lastname = $payload['family_name'] ?? '';
            }
        }
    } else if (isset($_POST['email'])) {
        // Fallback for custom JS form
        $email = $_POST['email'];
        $name = $_POST['name'] ?? '';
        $lastname = $_POST['lastname'] ?? '';
    }

    if (empty($email)) {
        header('Location: login.php?error=invalid_google_token');
        exit;
    }

    $userController = new UserController();
    $existingUser = $userController->getByEmail($email);

    if ($existingUser) {
        $status = $existingUser->getStatus();

        // Compte rejeté → page de refus
        if ($status === 'rejected') {
            $_SESSION['google_blocked'] = 'rejected';
            $_SESSION['google_email']   = $email;
            header('Location: google_blocked.php');
            exit;
        }

        // Compte banni → page de refus
        if ($status === 'banned') {
            $_SESSION['google_blocked'] = 'banned';
            $_SESSION['google_email']   = $email;
            header('Location: google_blocked.php');
            exit;
        }

        // Compte en attente → page d'attente
        if ($status === 'pending') {
            $_SESSION['google_blocked'] = 'pending';
            $_SESSION['google_email']   = $email;
            header('Location: google_blocked.php');
            exit;
        }

        // Compte actif → connexion automatique
        $_SESSION['user_id']     = $existingUser->getId();
        $_SESSION['user_nom']    = $existingUser->getNom();
        $_SESSION['user_prenom'] = $existingUser->getPrenom();
        $_SESSION['user_role']   = $existingUser->getRole();
        header('Location: profile.php');
        exit;

    } else {
        // Nouvel utilisateur : Rediriger vers google_complete.php avec ses infos préremplies
        $url = 'google_complete.php?email=' . urlencode($email)
             . '&nom='    . urlencode($lastname)
             . '&prenom=' . urlencode($name);
        header('Location: ' . $url);
        exit;
    }
} else {
    header('Location: login.php');
}
