<?php
require_once __DIR__ . '/../../controllers/AIController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Méthode non autorisée"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$text = $input['text'] ?? '';

if (empty($text)) {
    echo json_encode(["error" => "Le texte est vide"]);
    exit;
}

$aiController = new AIController();
$result = $aiController->moderateContent($text);

echo json_encode($result);
