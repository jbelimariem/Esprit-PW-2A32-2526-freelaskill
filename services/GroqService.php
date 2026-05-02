<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/User.php';

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
            throw new RuntimeException($message);
        }

        $reply = trim((string) ($data['choices'][0]['message']['content'] ?? ''));

        if ($reply === '') {
            throw new RuntimeException('Groq a renvoye une reponse vide.');
        }

        return $reply;
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
}
