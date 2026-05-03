<?php
// views/frontoffice/api_generate_job.php

require_once __DIR__ . '/../../controllers/AiJobGeneratorController.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['history']) || !is_array($input['history'])) {
    echo json_encode(['status' => 'error', 'message' => 'Historique manquant ou invalide.']);
    exit;
}

$controller = new AiJobGeneratorController();
$result = $controller->generateJob($input['history']);

echo json_encode($result);
