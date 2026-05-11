<?php
// views/frontoffice/api_candidate_assistant.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../controllers/AiCandidateAssistantController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $message = $data['message'] ?? '';
    $history = $data['history'] ?? [];
    $jobInfo = $data['jobInfo'] ?? null;
    $candidates = $data['candidates'] ?? [];

    if (empty($message) || !$jobInfo) {
        echo json_encode(['status' => 'error', 'message' => 'Données manquantes']);
        exit;
    }

    $controller = new AiCandidateAssistantController();
    $reply = $controller->chat($message, $history, $jobInfo, $candidates);

    echo json_encode([
        'status' => 'success',
        'reply' => $reply
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Méthode invalide']);
exit;
