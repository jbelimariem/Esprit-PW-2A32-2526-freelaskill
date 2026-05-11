<?php
// views/frontoffice/ai_assistant.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../controllers/AiAssistantController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer le corps de la requête JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $message = $data['message'] ?? '';
    $history = $data['history'] ?? [];

    if (empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Message vide']);
        exit;
    }

    $controller = new AiAssistantController();
    $reply = $controller->chat($message, $history);

    echo json_encode([
        'status' => 'success',
        'reply' => $reply
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Méthode invalide']);
exit;
