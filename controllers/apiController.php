<?php
/**
 * Contrôleur API — Endpoints AJAX pour les services externes.
 * Respecte MVC : ce fichier ne fait que router les requêtes vers les services.
 *
 * Endpoints disponibles :
 *   POST /apiController.php?action=translate
 *   POST /apiController.php?action=generate_description
 *   POST /apiController.php?action=suggest_rules
 *   POST /apiController.php?action=check_content
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/TranslationService.php';
require_once __DIR__ . '/../Models/GeminiService.php';
require_once __DIR__ . '/../Models/BadWordsService.php';

// Headers JSON
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Sécurité : uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

// Lire le body JSON ou les données POST classiques
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$action = $_GET['action'] ?? $input['action'] ?? '';

// Clé Gemini — à définir dans config.php : define('GEMINI_API_KEY', 'votre_cle');
$geminiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

switch ($action) {

    // ── 1. TRADUCTION ─────────────────────────────────────────────────
    case 'translate':
        $text = trim($input['text'] ?? '');
        $from = trim($input['from'] ?? 'fr');
        $to   = trim($input['to']   ?? 'en');

        if (empty($text)) {
            echo json_encode(['success' => false, 'error' => 'Texte requis.']);
            exit;
        }

        $service = new TranslationService();
        $result  = $service->call(['text' => $text, 'from' => $from, 'to' => $to]);
        echo json_encode($result);
        break;

    // ── 2. TRADUCTION D'UN CONTRAT COMPLET ────────────────────────────
    case 'translate_contrat':
        $contrat = $input['contrat'] ?? [];
        $from    = trim($input['from'] ?? 'fr');
        $to      = trim($input['to']   ?? 'en');

        if (empty($contrat)) {
            echo json_encode(['success' => false, 'error' => 'Données contrat requises.']);
            exit;
        }

        $service = new TranslationService();
        $result  = $service->translateContrat($contrat, $from, $to);
        echo json_encode(['success' => true, 'contrat' => $result]);
        break;

    // ── 3. TRADUCTION D'UNE RÈGLE ─────────────────────────────────────
    case 'translate_rule':
        $rule = $input['rule'] ?? [];
        $from = trim($input['from'] ?? 'fr');
        $to   = trim($input['to']   ?? 'en');

        if (empty($rule)) {
            echo json_encode(['success' => false, 'error' => 'Données règle requises.']);
            exit;
        }

        $service = new TranslationService();
        $result  = $service->translateRule($rule, $from, $to);
        echo json_encode(['success' => true, 'rule' => $result]);
        break;

    // ── 4. GÉNÉRATION DE DESCRIPTION (IA Gemini) ──────────────────────
    case 'generate_description':
        $titre      = trim($input['titre']      ?? '');
        $freelancer = trim($input['freelancer'] ?? '');
        $budget     = floatval($input['budget'] ?? 0);
        $delai      = intval($input['delai']    ?? 0);
        $lang       = trim($input['lang']       ?? 'fr');

        if (empty($titre)) {
            echo json_encode(['success' => false, 'error' => 'Titre requis pour générer une description.']);
            exit;
        }

        if (empty($geminiKey)) {
            echo json_encode(['success' => false, 'error' => 'Clé API Gemini non configurée. Ajoutez GEMINI_API_KEY dans config.php']);
            exit;
        }

        $service = new GeminiService($geminiKey);
        $result  = $service->generateContratDescription($titre, $freelancer, $budget, $delai, $lang);
        echo json_encode($result);
        break;

    // ── 5. SUGGESTION DE RÈGLES (IA Gemini) ───────────────────────────
    case 'suggest_rules':
        $titreContrat = trim($input['titre_contrat'] ?? '');
        $typeContrat  = trim($input['type_contrat']  ?? '');
        $lang         = trim($input['lang']          ?? 'fr');

        if (empty($titreContrat)) {
            echo json_encode(['success' => false, 'error' => 'Titre du contrat requis.']);
            exit;
        }

        if (empty($geminiKey)) {
            echo json_encode(['success' => false, 'error' => 'Clé API Gemini non configurée. Ajoutez GEMINI_API_KEY dans config.php']);
            exit;
        }

        $service = new GeminiService($geminiKey);
        $result  = $service->suggestRules($titreContrat, $typeContrat, $lang);
        echo json_encode($result);
        break;

    // ── 6. VÉRIFICATION CONTENU (Bad Words) ───────────────────────────
    case 'check_content':
        $fields = $input['fields'] ?? [];

        if (empty($fields) || !is_array($fields)) {
            // Compatibilité : champ unique
            $text = trim($input['text'] ?? '');
            if (empty($text)) {
                echo json_encode(['success' => false, 'error' => 'Champs requis.']);
                exit;
            }
            $fields = ['text' => $text];
        }

        $service = new BadWordsService();
        $result  = $service->checkFields($fields);
        echo json_encode(['success' => true] + $result);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Action inconnue : '{$action}'."]);
        break;
}
