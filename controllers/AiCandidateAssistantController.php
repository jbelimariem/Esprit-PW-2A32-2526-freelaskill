<?php
// controllers/AiCandidateAssistantController.php

class AiCandidateAssistantController {
    private const GEMINI_API_KEY = 'AIzaSyDdQZ40BrdEH8cg5pWx44Zo3LYJ_sLbmwQ';

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

        $contents = [];
        foreach ($history as $msg) {
            $role = ($msg['role'] === 'bot') ? 'model' : 'user';
            if (empty(trim($msg['text']))) continue;
            $contents[] = [
                "role" => $role,
                "parts" => [["text" => $msg['text']]]
            ];
        }

        // Ajouter le message actuel s'il n'est pas dans l'historique
        $contents[] = [
            "role" => "user",
            "parts" => [["text" => $message]]
        ];

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . self::GEMINI_API_KEY;
        
        $data = [
            "systemInstruction" => [
                "parts" => [["text" => $systemPrompt]]
            ],
            "contents" => $contents,
            "generationConfig" => [
                "temperature" => 0.4
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return "Désolé, je rencontre une erreur de connexion.";
        }

        $responseData = json_decode($response, true);
        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            return $responseData['candidates'][0]['content']['parts'][0]['text'];
        }

        return "Je n'ai pas pu générer de réponse.";
    }
}
