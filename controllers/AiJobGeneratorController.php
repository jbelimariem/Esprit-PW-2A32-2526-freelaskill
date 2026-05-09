<?php
// controllers/AiJobGeneratorController.php

require_once __DIR__ . '/../config.php';

class AiJobGeneratorController {
    // Clé chargée depuis secrets.php via config.php

    public function generateJob($history) {
        $systemPrompt = "Tu es un assistant IA conversationnel expert en recrutement pour la plateforme FreelaSkill en Tunisie.\n";
        $systemPrompt .= "Ton rôle est de discuter avec le client pour l'aider à créer une offre d'emploi parfaite.\n";
        $systemPrompt .= "Pour créer une offre, tu as besoin des 5 éléments suivants :\n";
        $systemPrompt .= "1. Titre de l'offre (précis)\n";
        $systemPrompt .= "2. Description détaillée du besoin\n";
        $systemPrompt .= "3. Compétences requises (ex: PHP, React, Design)\n";
        $systemPrompt .= "4. Budget estimé (en DT)\n";
        $systemPrompt .= "5. Délai estimé (ex: 15 jours, 1 mois)\n\n";
        
        $systemPrompt .= "RÈGLES IMPORTANTES :\n";
        $systemPrompt .= "- Pose des questions naturelles et une par une (ou deux maximum) pour obtenir les informations manquantes.\n";
        $systemPrompt .= "- Sois courtois, professionnel et concis.\n";
        $systemPrompt .= "- Si le client donne une idée vague, propose-lui un budget ou des compétences standards pour le guider, et demande confirmation.\n";
        $systemPrompt .= "- Tu DOIS TOUJOURS répondre au format JSON stricte sans aucun texte autour (pas de balises Markdown ```json).\n";
        
        $systemPrompt .= "\nSTRUCTURE DU JSON DE RÉPONSE :\n";
        $systemPrompt .= "{\n";
        $systemPrompt .= "  \"message\": \"Ta réponse conversationnelle à afficher au client.\",\n";
        $systemPrompt .= "  \"is_complete\": false, // Mets à true UNIQUEMENT quand tu as toutes les infos (Titre, Desc, Compétences, Budget, Délai) ET que le client est d'accord.\n";
        $systemPrompt .= "  \"job_data\": null // Si is_complete est true, remplis cet objet avec {\"titre\": \"...\", \"description\": \"...\", \"competences\": \"...\", \"budget\": \"...\", \"delai\": \"...\"}\n";
        $systemPrompt .= "}\n";

        // 3. Préparer l'historique de la conversation (Format OpenAI/Groq)
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

        $url = 'https://api.groq.com/openai/v1/chat/completions';
        
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => $messages,
            "temperature" => 0.5,
            "response_format" => ["type" => "json_object"]
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
            error_log("Erreur API Groq (Job Generator) [{$httpCode}]: " . $errorMsg);
            return ['status' => 'error', 'message' => 'Erreur IA Groq: ' . $errorMsg];
        }

        $responseData = json_decode($response, true);
        
        if (isset($responseData['choices'][0]['message']['content'])) {
            $jsonText = $responseData['choices'][0]['message']['content'];
            $aiData = json_decode($jsonText, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($aiData['message'])) {
                return ['status' => 'success', 'data' => $aiData];
            } else {
                return ['status' => 'error', 'message' => 'L\'IA Groq a généré un format invalide.'];
            }
        }

        return ['status' => 'error', 'message' => 'Impossible de générer une réponse.'];
    }
}
