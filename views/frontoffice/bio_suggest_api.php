<?php
/**
 * views/frontoffice/bio_suggest_api.php
 * AJAX endpoint — génère une bio professionnelle via l'IA Groq.
 * Méthode : POST
 * Paramètres : nom, prenom, role, bio (optionnel)
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée.']);
    exit;
}

require_once __DIR__ . '/../../controllers/config.php';
require_once __DIR__ . '/GroqService.php';

try {
    $groq = new GroqService();

    if (!$groq->isConfigured()) {
        throw new RuntimeException('Groq API non configurée.');
    }

    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $role = trim($_POST['role'] ?? 'freelancer');
    $existingBio = trim($_POST['bio'] ?? '');

    $fullName = trim($prenom . ' ' . $nom);
    if ($fullName === '') $fullName = 'Utilisateur';

    $messages = [
        [
            'role' => 'system',
            'content' => implode("\n", [
                'Tu es un expert en personal branding.',
                'Rédige une bio professionnelle, concise et percutante en français pour une plateforme de freelancing.',
                'Règles :',
                '- 2 ou 3 phrases maximum.',
                '- Pas de titres, pas de listes à puces, pas de guillemets.',
                '- Ton moderne, crédible et engageant.',
                '- Retourne UNIQUEMENT le texte de la bio, rien d\'autre.',
            ]),
        ],
        [
            'role' => 'user',
            'content' => implode("\n", [
                "Génère une bio pour cet utilisateur :",
                "Nom : $fullName",
                "Rôle : " . ($role === 'client' ? 'Client (recruteur)' : 'Freelancer (prestataire)'),
                "Bio actuelle : " . ($existingBio !== '' ? $existingBio : 'Aucune'),
                "Si c'est un freelancer, souligne l'expertise et la fiabilité.",
                "Si c'est un client, souligne la vision de projet et le sérieux.",
                "Longueur cible : 40 à 60 mots.",
            ]),
        ],
    ];

    $bio = $groq->chat($messages, [
        'temperature' => 0.8,
        'max_completion_tokens' => 200,
    ]);

    echo json_encode(['bio' => $bio]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
