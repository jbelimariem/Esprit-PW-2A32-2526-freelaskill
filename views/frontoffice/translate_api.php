<?php
// views/frontoffice/translate_api.php
// Groq-powered batch translation endpoint — supports EN, FR, AR targets.

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Rate-limit guard (basic: 60 req/min per session)
session_start();
$now = time();
if (!isset($_SESSION['translate_bucket'])) {
    $_SESSION['translate_bucket'] = ['count' => 0, 'reset' => $now + 60];
}
if ($now > $_SESSION['translate_bucket']['reset']) {
    $_SESSION['translate_bucket'] = ['count' => 0, 'reset' => $now + 60];
}
$_SESSION['translate_bucket']['count']++;
if ($_SESSION['translate_bucket']['count'] > 60) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many translation requests. Please wait a minute.']);
    exit;
}

// Load config / Groq service
require_once __DIR__ . '/../../controllers/config.php';
require_once __DIR__ . '/GroqService.php';

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!is_array($data) || empty($data['texts']) || !is_array($data['texts'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload. Expected {"texts": [...], "target_lang": "en|fr|ar"}']);
    exit;
}

// Resolve target language (default: English)
$allowedLangs = ['en', 'fr', 'ar'];
$targetLang   = isset($data['target_lang']) && in_array($data['target_lang'], $allowedLangs, true)
    ? $data['target_lang']
    : 'en';

$langInstructions = [
    'en' => [
        'instruction' => 'Translate each numbered item into natural, fluent English.',
        'example'     => '["Hello", "Welcome to the platform", "Sign up"]',
        'note'        => 'If an item is already in English, return it unchanged.',
    ],
    'fr' => [
        'instruction' => 'Traduisez chaque élément numéroté en français naturel et courant.',
        'example'     => '["Bonjour", "Bienvenue sur la plateforme", "S\'inscrire"]',
        'note'        => 'Si un élément est déjà en français, retournez-le tel quel.',
    ],
    'ar' => [
        'instruction' => 'ترجم كل عنصر مرقم إلى اللغة العربية الفصحى الطبيعية والسلسة.',
        'example'     => '["مرحبا", "مرحبا بك في المنصة", "التسجيل"]',
        'note'        => 'إذا كان العنصر بالعربية بالفعل، أعده كما هو.',
    ],
];

$lang = $langInstructions[$targetLang];

$texts = array_values($data['texts']);
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
            $lang['instruction'],
            'Return ONLY a JSON array of translated strings in the same order.',
            'Do NOT include the numbers. Do NOT add extra text or explanation.',
            'Preserve HTML entities, special characters, and emoji as-is.',
            $lang['note'],
            'Example output: ' . $lang['example'],
        ]),
    ],
    [
        'role'    => 'user',
        'content' => 'Translate these ' . count($texts) . " items:\n\n" . $numbered,
    ],
];

try {
    $groq  = new GroqService();
    $reply = $groq->chat($messages, [
        'temperature'           => 0.2,
        'max_completion_tokens' => min(1200, 120 + (count($texts) * 35)),
    ]);

    // Extract JSON array from reply
    if (preg_match('/\[[\s\S]*\]/u', $reply, $m)) {
        $translations = json_decode($m[0], true);
    } else {
        $translations = null;
    }

    if (!is_array($translations) || count($translations) < 1) {
        $translations = $texts; // fallback: return originals
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
        echo json_encode(['error' => 'Translation is temporarily limited. Please retry in a few seconds.']);
        exit;
    }

    http_response_code(500);
    echo json_encode(['error' => 'Translation is currently unavailable.']);
}
