<?php
// controllers/AiCandidateAssistantController.php

require_once __DIR__ . '/../config.php';

class AiCandidateAssistantController {
    // Clé chargée depuis secrets.php via config.php

    public function chat($message, $history, $jobInfo, $candidates) {
        $systemPrompt = "Tu es un consultant RH expert assistant pour la plateforme FreelaSkill.\n";
        $systemPrompt .= "Ton rôle est d'aider le client (recruteur) à analyser les candidatures reçues pour son offre et à choisir le meilleur profil.\n\n";
        
        $systemPrompt .= "--- INFORMATIONS SUR L'OFFRE ---\n";
        $systemPrompt .= "Titre: " . $jobInfo['titre'] . "\n";
        $systemPrompt .= "Description: " . $jobInfo['description'] . "\n";
        $systemPrompt .= "Compétences requises: " . $jobInfo['competences'] . "\n\n";

        $systemPrompt .= "--- LISTE DES CANDIDATS ---\n";
        foreach ($candidates as $index => $can) {
            $systemPrompt .= "Candidat #" . ($index + 1) . ":\n";
            $systemPrompt .= "- Nom: " . $can['name'] . "\n";
            $systemPrompt .= "- Titre: " . $can['job_title'] . "\n";
            $systemPrompt .= "- Expérience/Motivation: " . ($can['cover_letter'] ?: 'Non fournie') . "\n";
            $systemPrompt .= "---------------------------\n";
        }

        $systemPrompt .= "\nINSTRUCTIONS :\n";
        $systemPrompt .= "- Sois analytique, objectif et professionnel.\n";
        $systemPrompt .= "- Compare les compétences des candidats avec les besoins de l'offre.\n";
        $systemPrompt .= "- Si le client te demande 'Qui est le meilleur ?', propose un top 1 ou 2 avec des justifications claires.\n";
        $systemPrompt .= "- Réponds de manière concise et utilise du Markdown (gras, listes) pour la clarté.\n";
        $systemPrompt .= "- Ne mentionne pas de données que tu n'as pas.\n";

        $messages = [
            ["role" => "system", "content" => $systemPrompt]
        ];

        foreach ($history as $msg) {
            $role = ($msg['role'] === 'bot') ? 'assistant' : 'user';
            if (empty(trim($msg['text']))) continue;
            $messages[] = [
                "role" => $role,
                "content" => $msg['text']
            ];
        }

        // Ajouter le message actuel
        $messages[] = [
            "role" => "user",
            "content" => $message
        ];

        $url = 'https://api.groq.com/openai/v1/chat/completions';
        
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => $messages,
            "temperature" => 0.4,
            "max_tokens" => 2048
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            $errorBody = $response ? json_decode($response, true) : null;
            $errorMsg = isset($errorBody['error']['message']) ? $errorBody['error']['message'] : 'HTTP ' . $httpCode;
            error_log("Erreur API Groq (Candidate Assistant) [{$httpCode}]: " . $errorMsg);
            return "⚠️ Erreur IA Groq: " . $errorMsg;
        }

        $responseData = json_decode($response, true);
        if (isset($responseData['choices'][0]['message']['content'])) {
            return $responseData['choices'][0]['message']['content'];
        }

        return "Je n'ai pas pu générer de réponse.";
    }
}
