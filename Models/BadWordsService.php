<?php
require_once __DIR__ . '/ApiService.php';

/**
 * Service de filtrage de contenu inapproprié.
 * Utilise PurgoMalum API (gratuit, sans clé).
 * Complété par une liste locale de mots interdits en FR/AR.
 *
 * @see https://www.purgomalum.com/
 */
class BadWordsService extends ApiService
{
    protected string $baseUrl = 'https://www.purgomalum.com/service/json';

    // Liste locale de mots interdits (FR + AR communs)
    private array $localBadWords = [
        // Français
        'merde', 'putain', 'connard', 'salaud', 'idiot', 'imbécile',
        'crétin', 'abruti', 'enculé', 'bâtard', 'con', 'conne',
        // Arabe (translittération)
        'kess', 'zebi', 'wled', 'hmar', 'kalb',
        // Anglais basique
        'fuck', 'shit', 'asshole', 'bitch', 'bastard', 'crap',
    ];

    /**
     * Vérifie si un texte contient des mots inappropriés.
     *
     * @param array $params ['text' => string]
     * @return array ['success' => bool, 'is_clean' => bool, 'flagged_words' => array, 'censored' => string]
     */
    public function call(array $params): array
    {
        $text = trim($params['text'] ?? '');

        if (empty($text)) {
            return ['success' => true, 'is_clean' => true, 'flagged_words' => [], 'censored' => ''];
        }

        // 1. Vérification locale d'abord (plus rapide)
        $localResult = $this->checkLocal($text);

        // 2. Vérification via PurgoMalum API (pour l'anglais principalement)
        $apiResult = $this->checkViaApi($text);

        $flaggedWords = array_unique(array_merge(
            $localResult['flagged_words'],
            $apiResult['flagged_words'] ?? []
        ));

        $isClean = empty($flaggedWords) && ($apiResult['is_clean'] ?? true);

        return [
            'success'       => true,
            'is_clean'      => $isClean,
            'flagged_words' => $flaggedWords,
            'censored'      => $apiResult['censored'] ?? $this->censorLocal($text),
            'message'       => $isClean
                ? 'Contenu approprié.'
                : 'Contenu inapproprié détecté : ' . implode(', ', $flaggedWords),
        ];
    }

    /**
     * Vérifie plusieurs champs d'un formulaire en une fois.
     *
     * @param array $fields ['titre' => '...', 'description' => '...']
     * @return array ['is_clean' => bool, 'errors' => array]
     */
    public function checkFields(array $fields): array
    {
        $errors   = [];
        $isClean  = true;

        foreach ($fields as $fieldName => $value) {
            if (empty($value)) continue;

            $result = $this->call(['text' => $value]);

            if ($result['success'] && !$result['is_clean']) {
                $isClean = false;
                $errors[$fieldName] = 'Ce champ contient du contenu inapproprié : '
                    . implode(', ', $result['flagged_words']);
            }
        }

        return [
            'is_clean' => $isClean,
            'errors'   => $errors,
        ];
    }

    // ── Méthodes privées ──────────────────────────────────────────────

    private function checkLocal(string $text): array
    {
        $textLower   = mb_strtolower($text);
        $flagged     = [];

        foreach ($this->localBadWords as $word) {
            if (mb_strpos($textLower, mb_strtolower($word)) !== false) {
                $flagged[] = $word;
            }
        }

        return ['is_clean' => empty($flagged), 'flagged_words' => $flagged];
    }

    private function checkViaApi(string $text): array
    {
        // PurgoMalum ne traite que l'anglais — on l'utilise en complément
        $result = $this->httpGet($this->baseUrl, ['text' => $text]);

        if (!$result['success']) {
            // Si l'API échoue, on ne bloque pas — on retourne propre
            return ['is_clean' => true, 'flagged_words' => [], 'censored' => $text];
        }

        $data    = $result['data'];
        $result_text = $data['result'] ?? $text;

        // PurgoMalum remplace les mots par des astérisques
        $hasBadWords = ($result_text !== $text) && (strpos($result_text, '*') !== false);

        return [
            'is_clean'      => !$hasBadWords,
            'flagged_words' => $hasBadWords ? ['contenu inapproprié (EN)'] : [],
            'censored'      => $result_text,
        ];
    }

    private function censorLocal(string $text): string
    {
        $textLower = mb_strtolower($text);
        foreach ($this->localBadWords as $word) {
            $pattern = '/' . preg_quote($word, '/') . '/iu';
            $stars   = str_repeat('*', mb_strlen($word));
            $text    = preg_replace($pattern, $stars, $text);
        }
        return $text;
    }
}
