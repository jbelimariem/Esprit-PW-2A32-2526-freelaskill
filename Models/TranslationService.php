<?php
require_once __DIR__ . '/ApiService.php';

/**
 * Service de traduction utilisant MyMemory API (gratuit, sans clé requise).
 * Supporte : FR, EN, AR, ES, DE, IT, PT, etc.
 * Limite : 5000 caractères / requête, 1000 requêtes/jour.
 *
 * @see https://mymemory.translated.net/doc/spec.php
 */
class TranslationService extends ApiService
{
    protected string $baseUrl = 'https://api.mymemory.translated.net/get';

    // Codes de langue supportés
    public const LANG_FR = 'fr';
    public const LANG_EN = 'en';
    public const LANG_AR = 'ar';

    public const SUPPORTED_LANGS = [
        'fr' => 'Français',
        'en' => 'English',
        'ar' => 'العربية',
        'es' => 'Español',
        'de' => 'Deutsch',
    ];

    /**
     * Traduit un texte d'une langue source vers une langue cible.
     *
     * @param array $params ['text' => string, 'from' => string, 'to' => string]
     * @return array ['success' => bool, 'translated' => string, 'error' => string]
     */
    public function call(array $params): array
    {
        $text = trim($params['text'] ?? '');
        $from = $params['from'] ?? self::LANG_FR;
        $to   = $params['to']   ?? self::LANG_EN;

        if (empty($text)) {
            return ['success' => false, 'error' => 'Texte vide.'];
        }

        if (strlen($text) > 5000) {
            return ['success' => false, 'error' => 'Texte trop long (max 5000 caractères).'];
        }

        if ($from === $to) {
            return ['success' => true, 'translated' => $text];
        }

        $result = $this->httpGet($this->baseUrl, [
            'q'        => $text,
            'langpair' => $from . '|' . $to,
        ]);

        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error'] ?? 'Erreur API traduction.'];
        }

        $data = $result['data'];

        // MyMemory retourne responseStatus 200 si OK
        if (($data['responseStatus'] ?? 0) !== 200) {
            return ['success' => false, 'error' => $data['responseDetails'] ?? 'Traduction échouée.'];
        }

        $translated = $data['responseData']['translatedText'] ?? '';

        return [
            'success'    => true,
            'translated' => $translated,
            'from'       => $from,
            'to'         => $to,
            'original'   => $text,
        ];
    }

    /**
     * Traduit plusieurs champs d'un contrat en une seule passe.
     *
     * @param array  $contrat Tableau avec 'titre', 'description'
     * @param string $from    Langue source
     * @param string $to      Langue cible
     * @return array Contrat avec champs traduits
     */
    public function translateContrat(array $contrat, string $from, string $to): array
    {
        $result = $contrat;

        foreach (['titre', 'description'] as $field) {
            if (!empty($contrat[$field])) {
                $res = $this->call(['text' => $contrat[$field], 'from' => $from, 'to' => $to]);
                if ($res['success']) {
                    $result[$field . '_translated'] = $res['translated'];
                }
            }
        }

        $result['translated_to'] = $to;
        return $result;
    }

    /**
     * Traduit une règle (titre + description).
     */
    public function translateRule(array $rule, string $from, string $to): array
    {
        $result = $rule;

        foreach (['titre', 'description'] as $field) {
            if (!empty($rule[$field])) {
                $res = $this->call(['text' => $rule[$field], 'from' => $from, 'to' => $to]);
                if ($res['success']) {
                    $result[$field . '_translated'] = $res['translated'];
                }
            }
        }

        $result['translated_to'] = $to;
        return $result;
    }
}
