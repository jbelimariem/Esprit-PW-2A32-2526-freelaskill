<?php
require_once __DIR__ . '/ApiService.php';

/**
 * Service IA utilisant Google Gemini API (gemini-1.5-flash, gratuit avec clé).
 * Utilisé pour :
 *  - Générer une description de contrat
 *  - Proposer des règles automatiquement
 *  - Analyser un contrat
 *
 * Clé API gratuite : https://aistudio.google.com/app/apikey
 */
class GeminiService extends ApiService
{
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    private string   $apiKey;

    public function __construct(string $apiKey = '')
    {
        // Clé API Gemini — à configurer dans config.php ou .env
        $this->apiKey = $apiKey ?: (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '');
    }

    /**
     * Appel générique à Gemini.
     *
     * @param array $params ['prompt' => string, 'lang' => string]
     * @return array ['success' => bool, 'text' => string]
     */
    public function call(array $params): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Clé API Gemini non configurée.'];
        }

        $prompt = $params['prompt'] ?? '';
        if (empty($prompt)) {
            return ['success' => false, 'error' => 'Prompt vide.'];
        }

        $url     = $this->baseUrl . '?key=' . urlencode($this->apiKey);
        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature'     => 0.7,
                'maxOutputTokens' => 1024,
            ]
        ];

        $result = $this->httpPost($url, $payload);

        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error'] ?? 'Erreur API Gemini.'];
        }

        $data = $result['data'];

        // Extraire le texte généré
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($text)) {
            return ['success' => false, 'error' => 'Réponse vide de Gemini.'];
        }

        return ['success' => true, 'text' => trim($text)];
    }

    /**
     * Génère une description professionnelle pour un contrat.
     *
     * @param string $titre       Titre du contrat
     * @param string $freelancer  Nom/poste du freelancer
     * @param float  $budget      Budget en DT
     * @param int    $delai       Délai en jours
     * @param string $lang        Langue de sortie (fr/en/ar)
     */
    public function generateContratDescription(
        string $titre,
        string $freelancer,
        float  $budget,
        int    $delai,
        string $lang = 'fr'
    ): array {
        $langMap = ['fr' => 'français', 'en' => 'English', 'ar' => 'arabe'];
        $langLabel = $langMap[$lang] ?? 'français';

        $prompt = "Tu es un expert juridique en contrats freelance. "
            . "Génère une description professionnelle et détaillée en {$langLabel} pour un contrat intitulé \"{$titre}\". "
            . "Le freelancer est : {$freelancer}. Budget : {$budget} DT. Délai : {$delai} jours. "
            . "La description doit inclure : l'objet du contrat, les livrables attendus, les conditions de paiement et les responsabilités. "
            . "Réponds uniquement avec la description, sans titre ni introduction. Maximum 300 mots.";

        return $this->call(['prompt' => $prompt]);
    }

    /**
     * Propose automatiquement des règles pour un contrat.
     *
     * @param string $titreContrat  Titre du contrat
     * @param string $typeContrat   Type (développement, design, marketing, etc.)
     * @param string $lang          Langue de sortie
     * @return array ['success' => bool, 'rules' => array]
     */
    public function suggestRules(string $titreContrat, string $typeContrat = '', string $lang = 'fr'): array
    {
        $langMap = ['fr' => 'français', 'en' => 'English', 'ar' => 'arabe'];
        $langLabel = $langMap[$lang] ?? 'français';

        $context = $typeContrat ? " de type \"{$typeContrat}\"" : '';

        $prompt = "Tu es un expert en contrats freelance. "
            . "Pour un contrat intitulé \"{$titreContrat}\"{$context}, propose exactement 4 règles professionnelles en {$langLabel}. "
            . "Réponds UNIQUEMENT avec un JSON valide dans ce format exact, sans texte avant ou après :\n"
            . "[\n"
            . "  {\"titre\": \"...\", \"type\": \"...\", \"description\": \"...\", \"valeur\": \"...\"},\n"
            . "  {\"titre\": \"...\", \"type\": \"...\", \"description\": \"...\", \"valeur\": \"...\"}\n"
            . "]\n"
            . "Les types possibles : Confidentialité, Pénalité, Délai, Paiement, Propriété intellectuelle, Révision.\n"
            . "La valeur est un nombre (ex: 10 pour 10%, 500 pour 500 DT, 3 pour 3 jours).";

        $result = $this->call(['prompt' => $prompt]);

        if (!$result['success']) {
            return $result;
        }

        // Nettoyer la réponse et extraire le JSON
        $text = $result['text'];
        // Supprimer les balises markdown si présentes
        $text = preg_replace('/```json\s*/i', '', $text);
        $text = preg_replace('/```\s*/i', '', $text);
        $text = trim($text);

        // Trouver le tableau JSON
        $start = strpos($text, '[');
        $end   = strrpos($text, ']');
        if ($start !== false && $end !== false) {
            $text = substr($text, $start, $end - $start + 1);
        }

        $rules = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($rules)) {
            return ['success' => false, 'error' => 'Format JSON invalide dans la réponse Gemini.', 'raw' => $result['text']];
        }

        return ['success' => true, 'rules' => $rules];
    }
}
