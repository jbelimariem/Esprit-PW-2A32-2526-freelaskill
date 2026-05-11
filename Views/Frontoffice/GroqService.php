<?php

require_once __DIR__ . '/../../controllers/config.php';
require_once __DIR__ . '/../../Models/User.php';

class GroqService
{
    public function isConfigured()
    {
        return config::getGroqApiKey() !== '' && function_exists('curl_init');
    }

    public function chat(array $messages, array $options = [])
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Groq n est pas configure sur ce projet.');
        }

        $payload = [
            'model' => config::getGroqModel(),
            'messages' => array_values($messages),
            'temperature' => $options['temperature'] ?? 0.6,
            'max_completion_tokens' => $options['max_completion_tokens'] ?? 300,
        ];

        $ch = curl_init(config::getGroqApiUrl());

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . config::getGroqApiKey(),
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 25,
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Impossible de contacter Groq: ' . $curlError);
        }

        $data = json_decode($response, true);

        if ($status < 200 || $status >= 300) {
            $message = $data['error']['message'] ?? 'Erreur API Groq.';
            throw new RuntimeException($this->sanitizeApiError($message, $status));
        }

        $reply = trim((string) ($data['choices'][0]['message']['content'] ?? ''));

        if ($reply === '') {
            throw new RuntimeException('Groq a renvoye une reponse vide.');
        }

        return $reply;
    }

    private function sanitizeApiError($message, $status)
    {
        $message = (string) $message;

        if (preg_match('/rate|limit|tokens per minute|try again/i', $message)) {
            return 'Limite temporaire Groq atteinte. Reessayez dans quelques secondes.';
        }

        if ($status === 401 || $status === 403) {
            return 'Cle API Groq invalide ou non autorisee.';
        }

        return 'Erreur API Groq.';
    }

    public function generateProfileBio(User $user, $existingBio = '')
    {
        $fullName = trim($user->getPrenom() . ' ' . $user->getNom());
        $role = $user->getRole() === 'freelancer' ? 'freelancer' : 'client';
        $links = [];

        if (trim((string) $user->getGithubUrl()) !== '') {
            $links[] = 'GitHub';
        }

        if (trim((string) $user->getLinkedinUrl()) !== '') {
            $links[] = 'LinkedIn';
        }

        $messages = [
            [
                'role' => 'system',
                'content' => implode("\n", [
                    'You write concise professional bios in French for a freelance platform.',
                    'Return only the bio text.',
                    'Use 2 or 3 sentences, no title, no bullet points, no quotation marks.',
                    'Sound credible, specific, and modern.',
                ]),
            ],
            [
                'role' => 'user',
                'content' => implode("\n", [
                    'Generate a French profile bio for this user.',
                    'Name: ' . ($fullName !== '' ? $fullName : 'Utilisateur'),
                    'Role: ' . $role,
                    'Existing bio: ' . (trim((string) $existingBio) !== '' ? trim((string) $existingBio) : 'None'),
                    'Professional links: ' . (!empty($links) ? implode(', ', $links) : 'None'),
                    'If the user is a freelancer, highlight expertise, reliability, and value for clients.',
                    'If the user is a client, highlight project vision, collaboration style, and seriousness.',
                    'Length: 40 to 70 words.',
                ]),
            ],
        ];

        return $this->chat($messages, [
            'temperature' => 0.7,
            'max_completion_tokens' => 180,
        ]);
    }

    /**
     * Centralized content moderation using Groq.
     * Returns ['clean' => bool, 'reason' => ?string, 'severity' => ?string]
     */
    public function checkContentModeration($text, $field = 'texte')
    {
        $text = trim((string) $text);
        if ($text === '' || mb_strlen($text) < 2) {
            return ['clean' => true];
        }

        if (!$this->isConfigured()) {
            return ['clean' => true]; // Fail-open
        }

        $sample = mb_substr($text, 0, 1000);
        $messages = [
            [
                'role' => 'system',
                'content' => implode("\n", [
                    'You are a content moderation assistant for a professional freelance platform.',
                    'Your task is to analyze user-submitted text and detect inappropriate content.',
                    'Categories: Insults, profanity, hate speech, sexual content, threats, violent language, spam.',
                    'IMPORTANT: Normal professional text is CLEAN.',
                    'OUTPUT FORMAT: Return ONLY a valid JSON object.',
                    'If CLEAN: {"clean":true}',
                    'If NOT CLEAN: {"clean":false,"reason":"Short explanation in French (max 10 words)","severity":"low|medium|high"}',
                ]),
            ],
            [
                'role' => 'user',
                'content' => 'Analyze this ' . $field . " for inappropriate content:\n\n" . $sample,
            ],
        ];

        try {
            $raw = $this->chat($messages, [
                'temperature' => 0.0,
                'max_completion_tokens' => 100,
            ]);

            // Extract JSON
            if (preg_match('/\{.*?\}/s', $raw, $m)) {
                $result = json_decode($m[0], true);
            } else {
                $result = json_decode($raw, true);
            }

            if (!is_array($result) || !array_key_exists('clean', $result)) {
                return ['clean' => true];
            }

            return $result;
        } catch (Throwable $e) {
            return ['clean' => true];
        }
    }
}
