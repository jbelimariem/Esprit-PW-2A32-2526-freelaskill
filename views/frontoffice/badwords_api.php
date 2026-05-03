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

require_once __DIR__ . '/../../config.php';
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

    if (!$groq->isConfigured()) {
        // Si l'API n'est pas configurée, on laisse passer (fail-open)
        echo json_encode(['clean' => true]);
        exit;
    }

    $messages = [
        [
            'role'    => 'system',
            'content' => implode("\n", [
                'You are a content moderation assistant for a professional freelance platform.',
                'Your task is to analyze user-submitted text and detect inappropriate content.',
                'Categories to detect:',
                '  - Insults, profanity, hate speech, slurs (in any language: French, English, Arabic, etc.)',
                '  - Sexual or explicit content',
                '  - Threats or violent language',
                '  - Spam, promotional/advertising content unrelated to professional services',
                '  - Personal attacks or harassment',
                '  - Gibberish or meaningless sequences of characters used to bypass filters',
                'IMPORTANT: Normal professional text, even if critical or opinionated, is CLEAN.',
                'OUTPUT FORMAT: Return ONLY a valid JSON object. No markdown, no explanation.',
                'If CLEAN: {"clean":true}',
                'If NOT CLEAN: {"clean":false,"reason":"Short explanation in French (max 15 words)","severity":"low|medium|high"}',
                'severity=low: mild profanity; medium: insults/hate; high: threats/explicit.',
            ]),
        ],
        [
            'role'    => 'user',
            'content' => 'Analyze this text for inappropriate content. Field: ' . $field . "\n\nText:\n" . $sample,
        ],
    ];

    $raw = $groq->chat($messages, [
        'temperature'           => 0.0,
        'max_completion_tokens' => 80,
    ]);

    // Extraire le JSON même si l'IA ajoute du texte autour
    if (preg_match('/\{.*?\}/s', $raw, $m)) {
        $result = json_decode($m[0], true);
    } else {
        $result = json_decode($raw, true);
    }

    if (!is_array($result) || !array_key_exists('clean', $result)) {
        // Réponse inattendue → fail-open
        echo json_encode(['clean' => true]);
        exit;
    }

    echo json_encode($result);

} catch (Throwable $e) {
    // En cas d'erreur API → fail-open (ne pas bloquer l'utilisateur)
    echo json_encode(['clean' => true, '_warning' => 'Moderation unavailable.']);
}
