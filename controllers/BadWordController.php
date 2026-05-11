<?php
/**
 * Controller/BadWordController.php
 * Détection des mots interdits (sans base de données)
 */

class BadWordController {

    // Liste des mots interdits (hardcodée)
    private array $badWords = [
        // Français
        'merde', 'putain', 'connard', 'connasse', 'salope', 'enculé',
        'fdp', 'fils de pute', 'nique', 'niquer', 'batard', 'bâtard',
        'con', 'conne', 'abruti', 'imbecile', 'imbécile', 'cretin', 'crétin',
        // Anglais
        'fuck', 'shit', 'bitch', 'asshole', 'bastard', 'damn',
        'crap', 'dick', 'pussy', 'whore', 'nigga', 'nigger', 'wtf',
        // Arabe translittéré
        'kahba', 'charmota', 'zebi', 'hmaar', 'klab', 'kalb', 'naik', 'tfouh',
    ];

    public function __construct() {
    }

    private function jsonResponse(array $data): void {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Normaliser le texte (contournements : f*ck, sh!t, m3rde...)
    // ─────────────────────────────────────────────────────────────────────

    private function normalize(string $text): string {
        $replacements = [
            '@'=>'a','4'=>'a','3'=>'e','€'=>'e',
            '1'=>'i','!'=>'i','0'=>'o','5'=>'s',
            '$'=>'s','7'=>'t','*'=>'','.'=>'','-'=>'','_'=>'',
        ];
        // Convert to lowercase, replace special chars, and trim whitespace/newlines
        $text = trim(strtr(mb_strtolower($text, 'UTF-8'), $replacements));
        // Remove extra whitespace and newlines
        $text = preg_replace('/[\s\n\r\t]+/', ' ', $text);
        return $text;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Détecter les mots interdits dans un texte
    // Retourne la liste des mots trouvés
    // ─────────────────────────────────────────────────────────────────────

    public function detect(string $text): array {
        $normalized = $this->normalize($text);
        $found      = [];

        foreach ($this->badWords as $word) {
            $wordNorm = $this->normalize($word);
            // Use word boundaries to match the word
            $pattern = '/\b' . preg_quote($wordNorm, '/') . '\b/iu';
            if (preg_match($pattern, $normalized)) {
                $found[] = $word;
            }
        }

        return array_unique($found);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Censurer les mots interdits (remplacer par ***)
    // ─────────────────────────────────────────────────────────────────────

    public function censor(string $text): string {
        foreach ($this->badWords as $word) {
            $stars = str_repeat('*', mb_strlen($word, 'UTF-8'));
            $text  = preg_replace('/' . preg_quote($word, '/') . '/iu', $stars, $text);
        }
        return $text;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Vérifier un texte (API pour le front)
    // ─────────────────────────────────────────────────────────────────────

    public function check(): void {
        $text  = trim($_POST['text'] ?? '');
        $found = $this->detect($text);

        $this->jsonResponse([
            'clean'   => empty($found),
            'found'   => $found,
            'censored'=> $this->censor($text),
        ]);
    }
}
?>