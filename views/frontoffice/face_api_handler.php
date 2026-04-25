<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

require_once __DIR__ . '/../../controllers/UserController.php';

// Endpoint 1: Register Face
if (isset($_GET['action']) && $_GET['action'] === 'register') {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Non autorisé']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['descriptor'])) {
        echo json_encode(['success' => false, 'message' => 'Aucune donnée faciale fournie.']);
        exit;
    }

    $descriptorJson = json_encode($data['descriptor']);
    $uc = new UserController();
    $uc->updateFaceDescriptor($_SESSION['user_id'], $descriptorJson);

    echo json_encode(['success' => true, 'message' => 'Visage enregistré avec succès !']);
    exit;
}

// Endpoint 2: Login via Face
if (isset($_GET['action']) && $_GET['action'] === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['descriptor'])) {
        echo json_encode(['success' => false, 'message' => 'Aucune donnée faciale.']);
        exit;
    }

    $loginDescriptor = $data['descriptor']; // Array of 128 floats

    $uc = new UserController();
    $users = $uc->getAll(); // Get all active users
    
    $bestMatchUser = null;
    $minDistance = 999;
    $threshold = 0.45; // Strict threshold for login

    foreach ($users as $user) {
        $savedDescriptorJson = $user->getFaceDescriptor();
        if (!$savedDescriptorJson) continue;

        $savedDescriptor = json_decode($savedDescriptorJson, true);
        if (!is_array($savedDescriptor) || count($savedDescriptor) !== 128) continue;

        // Calculate Euclidean distance
        $distance = 0;
        for ($i = 0; $i < 128; $i++) {
            $distance += pow($loginDescriptor[$i] - $savedDescriptor[$i], 2);
        }
        $distance = sqrt($distance);

        if ($distance < $minDistance) {
            $minDistance = $distance;
            $bestMatchUser = $user;
        }
    }

    if ($bestMatchUser && $minDistance <= $threshold) {
        // Success! Log them in
        $_SESSION['user_id'] = $bestMatchUser->getId();
        $_SESSION['user_nom'] = $bestMatchUser->getNom();
        $_SESSION['user_prenom'] = $bestMatchUser->getPrenom();
        $_SESSION['user_role'] = $bestMatchUser->getRole();
        $_SESSION['user_status'] = $bestMatchUser->getStatus();

        $nextPage = ($bestMatchUser->getRole() === 'freelancer' && $bestMatchUser->getGithubUrl() === '' && $bestMatchUser->getLinkedinUrl() === '') 
            ? 'onboarding_links.php' : 'profile.php';

        echo json_encode(['success' => true, 'message' => 'Visage reconnu ! Connexion en cours...', 'nextPage' => $nextPage]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Visage non reconnu ou non enregistré.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
