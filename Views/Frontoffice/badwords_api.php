<?php
/**
 * views/frontoffice/badwords_api.php
 * AJAX POST endpoint — détecteur de contenu inapproprié via Groq.
 *
 * Body JSON attendu : { "text": "texte à analyser", "field": "bio" }
 * Réponse JSON :
 *   { "clean": true }                                         — texte propre
 *   { "clean": false, "reason": "...", "severity": "low|medium|high" }  — contenu problématique
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { http_response_code(405); echo json_encode(['error' => 'Méthode non autorisée.']); exit; }

require_once __DIR__ . '/../../controllers/config.php';
require_once __DIR__ . '/GroqService.php';

// --- Lire le body ---
$body = json_decode(file_get_contents('php://input'), true);
$text  = trim((string) ($body['text']  ?? ''));
$field = trim((string) ($body['field'] ?? 'texte'));

if ($text === '') {
    echo json_encode(['clean' => true]);
    exit;
}

// Trop court pour être problématique
if (mb_strlen($text) < 3) {
    echo json_encode(['clean' => true]);
    exit;
}

// Limite : analyser seulement les 1000 premiers caractères
$sample = mb_substr($text, 0, 1000);

try {
    $groq = new GroqService();
    $result = $groq->checkContentModeration($text, $field);
    echo json_encode($result);
} catch (Throwable $e) {
    echo json_encode(['clean' => true, '_warning' => 'Moderation unavailable.']);
}
