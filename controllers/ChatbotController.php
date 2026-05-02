<?php
// controllers/ChatbotController.php

require_once __DIR__ . '/../services/GroqService.php';

class ChatbotController {
    private $maxMessageLength = 1200;
    private $maxHistoryItems = 10;
    private $rateLimitWindow = 600;
    private $rateLimitMax = 30;

    public function handle() {
        $this->startSessionIfNeeded();

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJson([
                'success' => false,
                'message' => 'Method not allowed.'
            ], 405);
        }

        if ($this->isRateLimited()) {
            $this->sendJson([
                'success' => false,
                'message' => 'Trop de messages. Reessayez dans quelques minutes.'
            ], 429);
        }

        $payload = $this->readJsonPayload();

        if ($payload === null) {
            $this->sendJson([
                'success' => false,
                'message' => 'JSON invalide.'
            ], 400);
        }

        $message = $this->limitText($payload['message'] ?? '', $this->maxMessageLength);

        if ($message === '') {
            $this->sendJson([
                'success' => false,
                'message' => 'Ecrivez un message avant d envoyer.'
            ], 422);
        }

        $history = $this->sanitizeHistory($payload['history'] ?? []);
        $replyData = $this->generateReply($message, $history);

        $this->sendJson([
            'success' => true,
            'reply' => $replyData['reply'],
            'source' => $replyData['source']
        ]);
    }

    private function startSessionIfNeeded() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function readJsonPayload() {
        $raw = file_get_contents('php://input');

        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $payload = json_decode($raw, true);

        return is_array($payload) ? $payload : null;
    }

    private function isRateLimited() {
        $now = time();
        $key = 'freelaskill_chatbot_rate';
        $bucket = $_SESSION[$key] ?? [
            'started_at' => $now,
            'count' => 0
        ];

        if (($now - (int) $bucket['started_at']) > $this->rateLimitWindow) {
            $bucket = [
                'started_at' => $now,
                'count' => 0
            ];
        }

        if ((int) $bucket['count'] >= $this->rateLimitMax) {
            $_SESSION[$key] = $bucket;
            return true;
        }

        $bucket['count'] = (int) $bucket['count'] + 1;
        $_SESSION[$key] = $bucket;

        return false;
    }

    private function sanitizeHistory($history) {
        if (!is_array($history)) {
            return [];
        }

        $history = array_slice($history, -$this->maxHistoryItems);
        $clean = [];

        foreach ($history as $item) {
            if (!is_array($item)) {
                continue;
            }

            $role = $item['role'] ?? '';
            $content = $this->limitText($item['content'] ?? '', 900);

            if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }

            $clean[] = [
                'role' => $role,
                'content' => $content
            ];
        }

        return $clean;
    }

    private function generateReply($message, $history) {
        $groq = new GroqService();

        if ($groq->isConfigured()) {
            try {
                $apiReply = $groq->chat($this->buildApiMessages($message, $history), [
                    'temperature' => 0.5,
                    'max_completion_tokens' => 350
                ]);

                return [
                    'reply' => $apiReply,
                    'source' => 'api'
                ];
            } catch (RuntimeException $e) {
                // Fallback to the local helper copy if the external API is unavailable.
            }
        }

        return [
            'reply' => $this->localFreelaSkillReply($message),
            'source' => 'local'
        ];
    }

    private function buildApiMessages($message, $history) {
        return array_merge(
            [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt()
                ]
            ],
            $history,
            [
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ]
        );
    }

    private function systemPrompt() {
        return implode("\n", [
            'You are FreelaSkill Assistant inside a freelance and advanced-jobs platform.',
            'Answer in French by default unless the user clearly uses another language.',
            'Keep answers short, practical, friendly, and action-oriented.',
            'Help users with registration, login, profile, CV, portfolio, missions, hiring talent, and advanced careers.',
            'For advanced careers, suggest skills, learning steps, portfolio ideas, and realistic next actions.',
            'Never ask for passwords, secret API keys, or sensitive personal data.',
            'If the question needs private account data, tell the user to check the profile page or contact an admin.'
        ]);
    }

    private function localFreelaSkillReply($message) {
        $text = $this->normalizeText($message);

        if ($this->containsAny($text, ['metier', 'career', 'carriere', 'orientation', 'job', 'ia', 'ai', 'data', 'cyber', 'cloud', 'robotique'])) {
            return implode("\n", [
                'Voici des metiers avances interessants pour FreelaSkill :',
                '- IA / Machine Learning : Python, statistiques, projets avec donnees.',
                '- Cybersecurite : reseaux, Linux, tests de securite, bonnes pratiques.',
                '- Data analyst : SQL, Excel/Power BI, Python, storytelling avec les donnees.',
                '- Cloud engineer : Linux, Docker, CI/CD, AWS/Azure/GCP.',
                '- Developpeur web avance : PHP, JavaScript, API, securite, architecture.',
                '',
                'Si tu me dis ton niveau et ce que tu aimes, je peux te proposer un chemin plus precis.'
            ]);
        }

        if ($this->containsAny($text, ['inscription', 'register', 'creer compte', 'signup', 'sign up'])) {
            return implode("\n", [
                'Pour creer un compte :',
                '1. Clique sur Sign up.',
                '2. Choisis Freelancer si tu veux proposer tes services, ou Client si tu veux recruter.',
                '3. Complete ton profil avec une bio claire, tes competences, ton CV et ton portfolio.',
                '',
                'Un profil complet donne beaucoup plus de confiance.'
            ]);
        }

        if ($this->containsAny($text, ['connexion', 'login', 'mot de passe', 'password', 'oublie'])) {
            return implode("\n", [
                'Pour la connexion :',
                '- Utilise ton email et ton mot de passe.',
                '- Si tu as oublie ton mot de passe, passe par la page de recuperation.',
                '- Evite trop de tentatives rapides, le site peut bloquer temporairement pour securite.'
            ]);
        }

        if ($this->containsAny($text, ['profil', 'profile', 'cv', 'portfolio', 'github', 'linkedin', 'bio'])) {
            return implode("\n", [
                'Pour ameliorer ton profil freelance :',
                '- Ajoute une bio courte avec ton domaine et ta valeur.',
                '- Mets un CV propre et un portfolio avec 2 ou 3 projets concrets.',
                '- Ajoute GitHub/LinkedIn si tu es dans la tech.',
                '- Utilise des mots cles precis : PHP, API, UI, Data, Cybersecurite, etc.'
            ]);
        }

        if ($this->containsAny($text, ['client', 'recruter', 'mission', 'freelance', 'talent', 'projet'])) {
            return implode("\n", [
                'Pour recruter un bon talent :',
                '- Decris clairement la mission, le delai et le budget.',
                '- Liste les competences obligatoires et optionnelles.',
                '- Demande un exemple de projet ou portfolio.',
                '- Commence par une petite tache test si le projet est grand.'
            ]);
        }

        return implode("\n", [
            'Je peux t aider avec FreelaSkill.',
            '',
            'Tu peux me demander par exemple :',
            '- Quels metiers avances apprendre ?',
            '- Comment ameliorer mon profil freelance ?',
            '- Comment recruter un talent ?',
            '- Comment creer un compte ou me connecter ?'
        ]);
    }

    private function normalizeText($text) {
        $text = (string) $text;

        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

            if ($converted !== false) {
                $text = $converted;
            }
        }

        return strtolower($text);
    }

    private function containsAny($text, $needles) {
        foreach ($needles as $needle) {
            if (strpos($text, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function limitText($value, $maxLength) {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maxLength, 'UTF-8');
        }

        return substr($value, 0, $maxLength);
    }

    private function sendJson($data, $status = 200) {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
