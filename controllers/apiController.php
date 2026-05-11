<?php
/**
 * Contrôleur API — Endpoints AJAX pour les services externes.
 * Respecte MVC : ce fichier ne fait que router les requêtes vers les services.
 *
 * Endpoints :
 *   POST ?action=translate
 *   POST ?action=translate_contrat
 *   POST ?action=translate_rule
 *   POST ?action=generate_description
 *   POST ?action=suggest_rules
 *   POST ?action=check_content
 *   POST ?action=ocr_smart
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/TranslationService.php';
require_once __DIR__ . '/../Models/GeminiService.php';
require_once __DIR__ . '/../Models/BadWordsService.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$action    = $_GET['action'] ?? $input['action'] ?? '';
$geminiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

switch ($action) {

    // ── 1. TRADUCTION ─────────────────────────────────────────────────
    case 'translate':
        $text = trim($input['text'] ?? '');
        $from = trim($input['from'] ?? 'fr');
        $to   = trim($input['to']   ?? 'en');
        if (empty($text)) { echo json_encode(['success' => false, 'error' => 'Texte requis.']); exit; }
        $service = new TranslationService();
        echo json_encode($service->call(['text' => $text, 'from' => $from, 'to' => $to]));
        break;

    // ── 2. TRADUCTION CONTRAT ─────────────────────────────────────────
    case 'translate_contrat':
        $contrat = $input['contrat'] ?? [];
        $from    = trim($input['from'] ?? 'fr');
        $to      = trim($input['to']   ?? 'en');
        if (empty($contrat)) { echo json_encode(['success' => false, 'error' => 'Données contrat requises.']); exit; }
        $service = new TranslationService();
        echo json_encode(['success' => true, 'contrat' => $service->translateContrat($contrat, $from, $to)]);
        break;

    // ── 3. TRADUCTION RÈGLE ───────────────────────────────────────────
    case 'translate_rule':
        $rule = $input['rule'] ?? [];
        $from = trim($input['from'] ?? 'fr');
        $to   = trim($input['to']   ?? 'en');
        if (empty($rule)) { echo json_encode(['success' => false, 'error' => 'Données règle requises.']); exit; }
        $service = new TranslationService();
        echo json_encode(['success' => true, 'rule' => $service->translateRule($rule, $from, $to)]);
        break;

    // ── 4. GÉNÉRATION DESCRIPTION (Gemini) ────────────────────────────
    case 'generate_description':
        $titre      = trim($input['titre']      ?? '');
        $freelancer = trim($input['freelancer'] ?? '');
        $budget     = floatval($input['budget'] ?? 0);
        $delai      = intval($input['delai']    ?? 0);
        $lang       = trim($input['lang']       ?? 'fr');

        if (empty($titre)) { echo json_encode(['success' => false, 'error' => 'Titre requis.']); exit; }

        if (empty($geminiKey) || $geminiKey === 'VOTRE_CLE_GEMINI_ICI') {
            echo json_encode(generateLocalDescription($titre, $freelancer, $budget, $delai, $lang));
            exit;
        }

        $service = new GeminiService($geminiKey);
        $result  = $service->generateContratDescription($titre, $freelancer, $budget, $delai, $lang);

        if (!$result['success'] && isQuotaError($result['error'] ?? '')) {
            $result = generateLocalDescription($titre, $freelancer, $budget, $delai, $lang);
        }
        echo json_encode($result);
        break;

    // ── 5. SUGGESTION RÈGLES (Gemini) ─────────────────────────────────
    case 'suggest_rules':
        $titreContrat = trim($input['titre_contrat'] ?? '');
        $typeContrat  = trim($input['type_contrat']  ?? '');
        $lang         = trim($input['lang']          ?? 'fr');

        if (empty($titreContrat)) { echo json_encode(['success' => false, 'error' => 'Titre du contrat requis.']); exit; }

        if (empty($geminiKey) || $geminiKey === 'VOTRE_CLE_GEMINI_ICI') {
            echo json_encode(generateLocalRules($titreContrat, $lang));
            exit;
        }

        $service = new GeminiService($geminiKey);
        $result  = $service->suggestRules($titreContrat, $typeContrat, $lang);

        if (!$result['success'] && isQuotaError($result['error'] ?? '')) {
            $result = generateLocalRules($titreContrat, $lang);
        }
        echo json_encode($result);
        break;

    // ── 6. VÉRIFICATION CONTENU (Bad Words) ───────────────────────────
    case 'check_content':
        $fields = $input['fields'] ?? [];
        if (empty($fields) || !is_array($fields)) {
            $text = trim($input['text'] ?? '');
            if (empty($text)) { echo json_encode(['success' => false, 'error' => 'Champs requis.']); exit; }
            $fields = ['text' => $text];
        }
        $service = new BadWordsService();
        $result  = $service->checkFields($fields);
        echo json_encode(['success' => true] + $result);
        break;

    // ── 7. OCR INTELLIGENT (Gemini Vision) ────────────────────────────
    case 'ocr_smart':
        $imageData = $input['image'] ?? '';
        $lang      = trim($input['lang'] ?? 'fr');

        if (empty($imageData)) { echo json_encode(['success' => false, 'error' => 'Image requise.']); exit; }

        if (empty($geminiKey) || $geminiKey === 'VOTRE_CLE_GEMINI_ICI') {
            echo json_encode(['success' => false, 'error' => 'Clé Gemini requise pour l\'OCR intelligent.']);
            exit;
        }

        if (!preg_match('/^data:(image\/[a-z]+);base64,(.+)$/i', $imageData, $matches)) {
            echo json_encode(['success' => false, 'error' => 'Format image invalide.']);
            exit;
        }

        $mimeType  = $matches[1];
        $b64Data   = $matches[2];
        $langMap   = ['fr' => 'français', 'en' => 'English', 'ar' => 'arabe'];
        $langLabel = $langMap[$lang] ?? 'français';

        $prompt = "Analyse cette image d'un document de contrat freelance écrit en {$langLabel}. "
                . "Extrais les informations suivantes et réponds UNIQUEMENT avec un JSON valide :\n"
                . "{\"titre\":\"\",\"description\":\"\",\"budget\":\"\",\"delai\":\"\",\"freelance_info\":\"\"}\n"
                . "budget = montant numérique seulement (ex: 220). delai = nombre de jours seulement (ex: 22). "
                . "Si une info est absente, mets une chaîne vide. Ne mets QUE le JSON.";

        $url     = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . urlencode($geminiKey);
        $payload = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['inline_data' => ['mime_type' => $mimeType, 'data' => $b64Data]]
                ]
            ]],
            'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 512]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($text)) {
            $errMsg = $data['error']['message'] ?? 'Réponse vide de Gemini Vision.';
            echo json_encode(['success' => false, 'error' => $errMsg]);
            exit;
        }

        $text = trim(preg_replace('/```(?:json)?\s*/i', '', $text));
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');
        if ($start !== false && $end !== false) {
            $text = substr($text, $start, $end - $start + 1);
        }

        $extracted = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'error' => 'JSON invalide dans la réponse.', 'raw' => $text]);
            exit;
        }

        echo json_encode(['success' => true, 'extracted' => $extracted]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Action inconnue : '{$action}'."]);
        break;
}

// ── Helpers ───────────────────────────────────────────────────────────

function isQuotaError(string $msg): bool {
    return stripos($msg, 'quota') !== false
        || stripos($msg, 'RESOURCE_EXHAUSTED') !== false
        || stripos($msg, 'rate limit') !== false;
}

// ── Fallbacks locaux ──────────────────────────────────────────────────

function generateLocalDescription(string $titre, string $freelancer, float $budget, int $delai, string $lang): array
{
    $templates = [
        'fr' => "Le présent contrat, intitulé \"{titre}\", est conclu entre le client et le freelancer {freelancer}.\n\n"
              . "**Objet du contrat :** Réalisation des prestations définies dans ce document selon les termes convenus.\n\n"
              . "**Livrables attendus :** Le freelancer s'engage à fournir tous les livrables convenus dans les délais impartis, "
              . "conformément aux spécifications techniques et fonctionnelles définies conjointement.\n\n"
              . "**Conditions financières :** Le budget total alloué est de {budget} DT, payable selon les modalités convenues entre les parties.\n\n"
              . "**Délai de réalisation :** Les travaux devront être achevés dans un délai de {delai} jours à compter de la signature du présent contrat.\n\n"
              . "**Responsabilités :** Chaque partie s'engage à respecter ses obligations contractuelles et à communiquer de manière transparente "
              . "tout obstacle pouvant affecter le bon déroulement du projet.",

        'en' => "This contract, titled \"{titre}\", is entered into between the client and freelancer {freelancer}.\n\n"
              . "**Scope of Work:** The freelancer agrees to deliver all agreed services within the specified timeframe.\n\n"
              . "**Financial Terms:** The total budget allocated is {budget} DT, payable according to the terms agreed upon.\n\n"
              . "**Timeline:** All work must be completed within {delai} days from the signing of this contract.\n\n"
              . "**Responsibilities:** Both parties commit to fulfilling their contractual obligations.",

        'ar' => "هذا العقد بعنوان \"{titre}\" مبرم بين العميل والمستقل {freelancer}.\n\n"
              . "**موضوع العقد:** يلتزم المستقل بتقديم جميع الخدمات المتفق عليها في الوقت المحدد.\n\n"
              . "**الشروط المالية:** الميزانية الإجمالية هي {budget} دينار.\n\n"
              . "**مدة التنفيذ:** يجب إنجاز الأعمال في غضون {delai} يوماً.",
    ];

    $template = $templates[$lang] ?? $templates['fr'];
    $text = str_replace(
        ['{titre}', '{freelancer}', '{budget}', '{delai}'],
        [$titre, $freelancer ?: 'le freelancer', number_format($budget, 2, ',', ' '), $delai],
        $template
    );

    return ['success' => true, 'text' => $text, 'source' => 'local'];
}

function generateLocalRules(string $titreContrat, string $lang): array
{
    $rules = [
        'fr' => [
            ['titre' => 'Clause de confidentialité',         'type' => 'Confidentialité',         'description' => 'Toutes les informations échangées dans le cadre de ce contrat sont strictement confidentielles et ne peuvent être divulguées à des tiers sans accord écrit préalable.',                                                    'valeur' => ''],
            ['titre' => 'Pénalité de retard',                'type' => 'Pénalité',                'description' => 'En cas de retard dans la livraison des travaux, une pénalité de 5% du montant total sera appliquée par semaine de retard, dans la limite de 20% du budget total.',                                                        'valeur' => '5'],
            ['titre' => 'Droit de propriété intellectuelle', 'type' => 'Propriété intellectuelle', 'description' => 'À la réception du paiement intégral, tous les droits de propriété intellectuelle sur les livrables sont transférés au client.',                                                                                      'valeur' => ''],
            ['titre' => 'Révisions incluses',                'type' => 'Révision',                'description' => 'Le contrat inclut jusqu\'à 3 cycles de révisions. Toute révision supplémentaire sera facturée selon le tarif horaire convenu.',                                                                                           'valeur' => '3'],
        ],
        'en' => [
            ['titre' => 'Confidentiality Clause',       'type' => 'Confidentiality',       'description' => 'All information exchanged under this contract is strictly confidential and may not be disclosed to third parties without prior written consent.',                                                                          'valeur' => ''],
            ['titre' => 'Late Delivery Penalty',        'type' => 'Penalty',               'description' => 'In case of late delivery, a penalty of 5% of the total amount will be applied per week of delay, up to a maximum of 20% of the total budget.',                                                                           'valeur' => '5'],
            ['titre' => 'Intellectual Property Rights', 'type' => 'Intellectual Property', 'description' => 'Upon receipt of full payment, all intellectual property rights over the deliverables are transferred to the client.',                                                                                                     'valeur' => ''],
            ['titre' => 'Included Revisions',           'type' => 'Revision',              'description' => 'The contract includes up to 3 revision cycles. Any additional revision will be billed at the agreed hourly rate.',                                                                                                        'valeur' => '3'],
        ],
        'ar' => [
            ['titre' => 'بند السرية',                   'type' => 'سرية',          'description' => 'جميع المعلومات المتبادلة في إطار هذا العقد سرية تامة ولا يجوز الإفصاح عنها لأطراف ثالثة دون موافقة كتابية مسبقة.',                                                                                                    'valeur' => ''],
            ['titre' => 'غرامة التأخير',                'type' => 'غرامة',         'description' => 'في حالة التأخر في التسليم، تُطبق غرامة بنسبة 5% من المبلغ الإجمالي عن كل أسبوع تأخير، بحد أقصى 20% من الميزانية الكلية.',                                                                                            'valeur' => '5'],
            ['titre' => 'حقوق الملكية الفكرية',         'type' => 'ملكية فكرية',   'description' => 'عند استلام الدفع الكامل، تنتقل جميع حقوق الملكية الفكرية على المنجزات إلى العميل.',                                                                                                                                    'valeur' => ''],
            ['titre' => 'المراجعات المشمولة',           'type' => 'مراجعة',        'description' => 'يشمل العقد ما يصل إلى 3 دورات مراجعة. أي مراجعة إضافية ستُفوتر وفق الأجر الساعي المتفق عليه.',                                                                                                                       'valeur' => '3'],
        ],
    ];

    return ['success' => true, 'rules' => $rules[$lang] ?? $rules['fr'], 'source' => 'local'];
}
