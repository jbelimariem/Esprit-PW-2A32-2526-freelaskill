<?php
require_once __DIR__ . '/../../controllers/AIController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Méthode non autorisée"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';

if (empty($prompt)) {
    echo json_encode(["error" => "Le prompt est vide"]);
    exit;
}

$aiController = new AIController();
$result = $aiController->generateDescription($prompt);

echo json_encode($result);
