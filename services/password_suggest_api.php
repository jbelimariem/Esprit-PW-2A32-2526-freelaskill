<?php
/**
 * services/password_suggest_api.php
 * AJAX endpoint — génère 3 mots de passe forts via l'IA Groq.
 * Méthode : POST  (aucun paramètre requis)
 * Réponse : { "passwords": ["Abc!1234", "Xy#8Zzqw", "P@ss9Mno"] }
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Bloc CORS minimal (même origine uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée.']);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/GroqService.php';

try {
    $groq = new GroqService();

    if (!$groq->isConfigured()) {
        throw new RuntimeException('Groq API non configurée.');
    }

    $messages = [
        [
            'role'    => 'system',
            'content' => implode("\n", [
                'You are a cybersecurity expert specializing in password generation.',
                'Your only task is to generate strong passwords.',
                'RULES:',
                '- Each password must be exactly 14 characters long.',
                '- Each password must contain: uppercase letters, lowercase letters, digits, and special characters (!@#$%^&*-_+=?).',
                '- Passwords must look random and NOT be based on dictionary words.',
                '- Passwords must be different from each other.',
                'OUTPUT FORMAT: Return ONLY a valid JSON array with exactly 3 strings. No explanation, no markdown, no extra text.',
                'Example: ["Xp7!kLm#QwR2Zt", "Bv#9nJd$HqE4Yw", "Tm3@rCu&PsX8Lf"]',
            ]),
        ],
        [
            'role'    => 'user',
            'content' => 'Generate 3 strong passwords now. Return only the JSON array.',
        ],
    ];

    $raw = $groq->chat($messages, [
        'temperature'          => 1.0,
        'max_completion_tokens' => 120,
    ]);

    // Nettoyage : extraire le tableau JSON même si l'IA ajoute du texte parasite
    if (preg_match('/\[.*?\]/s', $raw, $m)) {
        $passwords = json_decode($m[0], true);
    } else {
        $passwords = json_decode($raw, true);
    }

    if (!is_array($passwords) || count($passwords) < 1) {
        throw new RuntimeException('Réponse IA invalide : ' . $raw);
    }

    // Garder uniquement les chaînes, max 5
    $passwords = array_values(array_filter(
        array_slice($passwords, 0, 5),
        fn($p) => is_string($p) && trim($p) !== ''
    ));

    echo json_encode(['passwords' => $passwords]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
