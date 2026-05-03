<?php
// views/frontoffice/translate_api.php
// Groq-powered batch translation endpoint

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Rate-limit guard (basic: 30 req/min per session)
session_start();
$now = time();
if (!isset($_SESSION['translate_bucket'])) {
    $_SESSION['translate_bucket'] = ['count' => 0, 'reset' => $now + 60];
}
if ($now > $_SESSION['translate_bucket']['reset']) {
    $_SESSION['translate_bucket'] = ['count' => 0, 'reset' => $now + 60];
}
$_SESSION['translate_bucket']['count']++;
if ($_SESSION['translate_bucket']['count'] > 30) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please wait a minute.']);
    exit;
}

// Load config / Groq service
require_once __DIR__ . '/../../controllers/config.php';
require_once __DIR__ . '/GroqService.php';

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!is_array($data) || empty($data['texts']) || !is_array($data['texts'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload. Expected {"texts": [...]}']);
    exit;
}

$texts = array_values($data['texts']);

// Sanitize: skip empty strings, limit each request to a small batch.
$texts = array_filter($texts, fn($t) => is_string($t) && trim($t) !== '');
$texts = array_values(array_slice($texts, 0, 30));
$texts = array_map(function ($text) {
    $text = trim(preg_replace('/\s+/', ' ', (string) $text));
    return mb_substr($text, 0, 300);
}, $texts);

if (empty($texts)) {
    echo json_encode(['translations' => []]);
    exit;
}

// Build numbered list for the prompt
$numbered = '';
foreach ($texts as $i => $text) {
    $numbered .= ($i + 1) . '. ' . str_replace("\n", ' ', $text) . "\n";
}

$messages = [
    [
        'role'    => 'system',
        'content' => implode("\n", [
            'You are a professional website translator.',
            'Translate each numbered item from French (or any language) into natural, fluent English.',
            'Return ONLY a JSON array of translated strings in the same order.',
            'Do NOT include the numbers. Do NOT add extra text or explanation.',
            'Preserve HTML entities, special characters, and emoji as-is.',
            'If an item is already in English, return it unchanged.',
            'Example output: ["Hello", "Welcome to the platform", "Sign up"]',
        ]),
    ],
    [
        'role'    => 'user',
        'content' => "Translate these " . count($texts) . " items:\n\n" . $numbered,
    ],
];

try {
    $groq   = new GroqService();
    $reply  = $groq->chat($messages, [
        'temperature'          => 0.2,
        'max_completion_tokens' => min(1200, 120 + (count($texts) * 35)),
    ]);

    // Extract JSON array from reply
    if (preg_match('/\[[\s\S]*\]/u', $reply, $m)) {
        $translations = json_decode($m[0], true);
    } else {
        $translations = null;
    }

    if (!is_array($translations) || count($translations) < 1) {
        // Fallback: return originals
        $translations = $texts;
    }

    // Align counts
    while (count($translations) < count($texts)) {
        $translations[] = $texts[count($translations)];
    }
    $translations = array_slice($translations, 0, count($texts));

    echo json_encode(['translations' => array_values($translations)], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $message = $e->getMessage();

    if (preg_match('/rate|limit|tokens per minute|try again/i', $message)) {
        http_response_code(429);
        echo json_encode(['error' => 'La traduction est temporairement limitee. Reessayez dans quelques secondes.']);
        exit;
    }

    http_response_code(500);
    echo json_encode(['error' => 'La traduction est indisponible pour le moment.']);
}
