<?php
// controllers/FaceApiController.php

require_once __DIR__ . '/UserController.php';

class FaceApiController extends UserController {

    public function execute() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
        }

        $action = $_GET['action'] ?? '';

        if ($action === 'register') {
            $this->registerFace();
        }

        if ($action === 'login') {
            $this->loginWithFace();
        }

        $this->jsonResponse(['success' => false, 'message' => 'Action inconnue.']);
    }

    private function registerFace() {
        if (empty($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Non autorise']);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['descriptor'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Aucune donnee faciale fournie.']);
        }

        $this->updateFaceDescriptor($_SESSION['user_id'], json_encode($data['descriptor']));
        $this->jsonResponse(['success' => true, 'message' => 'Visage enregistre avec succes !']);
    }

    private function loginWithFace() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['descriptor'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Aucune donnee faciale.']);
        }

        $loginDescriptor = $data['descriptor'];
        if (!is_array($loginDescriptor) || count($loginDescriptor) !== 128) {
            $this->jsonResponse(['success' => false, 'message' => 'Donnees faciales invalides.']);
        }

        $bestMatchUser = null;
        $minDistance = 999;
        $threshold = 0.45;

        foreach ($this->getAll() as $user) {
            $savedDescriptorJson = $user->getFaceDescriptor();
            if (!$savedDescriptorJson) {
                continue;
            }

            $savedDescriptor = json_decode($savedDescriptorJson, true);
            if (!is_array($savedDescriptor) || count($savedDescriptor) !== 128) {
                continue;
            }

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

        if (!$bestMatchUser || $minDistance > $threshold) {
            $this->jsonResponse(['success' => false, 'message' => 'Visage non reconnu ou non enregistre.']);
        }

        $_SESSION['user_id'] = $bestMatchUser->getId();
        $_SESSION['user_nom'] = $bestMatchUser->getNom();
        $_SESSION['user_prenom'] = $bestMatchUser->getPrenom();
        $_SESSION['user_role'] = $bestMatchUser->getRole();
        $_SESSION['user_status'] = $bestMatchUser->getStatus();

        $nextPage = ($bestMatchUser->getRole() === 'freelancer' && $bestMatchUser->getGithubUrl() === '' && $bestMatchUser->getLinkedinUrl() === '')
            ? 'onboarding_links.php'
            : 'profile.php';

        $this->jsonResponse([
            'success' => true,
            'message' => 'Visage reconnu ! Connexion en cours...',
            'nextPage' => $nextPage,
        ]);
    }

    private function jsonResponse(array $payload) {
        echo json_encode($payload);
        exit;
    }
}
