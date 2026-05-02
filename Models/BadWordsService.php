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
        // Français — mots complets uniquement
        'merde', 'putain', 'connard', 'salaud', 'idiot', 'imbécile',
        'crétin', 'abruti', 'enculé', 'bâtard',
        // Arabe (translittération)
        'kess', 'zebi', 'hmar', 'kalb',
        // Anglais basique
        'fuck', 'shit', 'asshole', 'bitch', 'bastard',
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
        $flagged = [];

        foreach ($this->localBadWords as $word) {
            // Chercher le mot entier uniquement (pas dans un mot plus long)
            // Ex: "con" ne doit pas matcher "conclu", "selon", "contrat"
            $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
            if (preg_match($pattern, $text)) {
                $flagged[] = $word;
            }
        }

        return ['is_clean' => empty($flagged), 'flagged_words' => $flagged];
    }

    private function checkViaApi(string $text): array
    {
        // PurgoMalum ne traite que l'anglais
        // On ne l'appelle que si le texte semble être en anglais (contient peu de mots français communs)
        $frenchIndicators = ['le', 'la', 'les', 'de', 'du', 'des', 'est', 'sont', 'dans', 'pour', 'avec', 'sur', 'par', 'une', 'un', 'ce', 'qui', 'que', 'pas', 'ne'];
        $textLower = mb_strtolower($text);
        $frenchWordCount = 0;
        foreach ($frenchIndicators as $word) {
            if (preg_match('/\b' . $word . '\b/', $textLower)) {
                $frenchWordCount++;
            }
        }

        // Si le texte contient 3+ mots français, on ne passe pas par l'API anglaise
        if ($frenchWordCount >= 3) {
            return ['is_clean' => true, 'flagged_words' => [], 'censored' => $text];
        }

        $result = $this->httpGet($this->baseUrl, ['text' => $text]);

        if (!$result['success']) {
            return ['is_clean' => true, 'flagged_words' => [], 'censored' => $text];
        }

        $data        = $result['data'];
        $result_text = $data['result'] ?? $text;

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
