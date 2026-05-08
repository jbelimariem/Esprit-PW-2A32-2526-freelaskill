<?php
// controllers/AiAssistantController.php

require_once __DIR__ . '/../config.php';

class AiAssistantController {
    private $pdo;
    
    // ⚠️ IMPORTANT: Remplacez par votre vraie clé API Gemini (Google AI Studio)
    // Clé chargée depuis secrets.php via config.php

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function chat($message, $history = []) {
        // 1. Récupérer toutes les offres disponibles
        $stmt = $this->pdo->query("SELECT id, titre, description, competences, budget, delai FROM offres_emploi WHERE statut = 'approved'");
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $offersJson = json_encode($offers, JSON_UNESCAPED_UNICODE);
        
        // 2. Préparer le contexte système (System Prompt)
        $systemPrompt = "Tu es l'assistant IA de FreelaSkill, une plateforme pour freelances tunisiens.\n";
        $systemPrompt .= "Ton rôle est de discuter avec le freelancer, répondre à ses questions de manière très naturelle, et l'aider à trouver la meilleure mission parmi celles disponibles dans la base de données.\n";
        $systemPrompt .= "Sois amical, professionnel, clair et concis. N'invente jamais d'offres qui ne sont pas dans la liste fournie.\n";
        $systemPrompt .= "Si l'utilisateur te demande des offres, analyse son besoin (compétences, budget) et sélectionne les meilleures offres.\n";
        $systemPrompt .= "Pour chaque offre recommandée, donne son Titre, son Budget, et ajoute TOUJOURS un lien au format Markdown de cette façon : [Voir l'offre](freelancer_detail.php?id=ID_DE_L_OFFRE).\n";
        $systemPrompt .= "Tu peux utiliser du gras (**texte**) et des listes à puces pour structurer ta réponse.\n\n";
        $systemPrompt .= "--- OFFRES DISPONIBLES EN BASE DE DONNÉES ---\n";
        $systemPrompt .= $offersJson;

        // 3. Préparer l'historique de la conversation
        $contents = [];
        foreach ($history as $msg) {
            $role = ($msg['role'] === 'bot') ? 'model' : 'user';
            // Ignorer les messages vides pour éviter les erreurs d'API
            if (empty(trim($msg['text']))) continue;
            
            $contents[] = [
                "role" => $role,
                "parts" => [["text" => $msg['text']]]
            ];
        }
        
        // Ajouter le message actuel de l'utilisateur
        $contents[] = [
            "role" => "user",
            "parts" => [["text" => $message]]
        ];

        // 4. Appel de l'API Gemini
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key=' . GEMINI_API_KEY;
        
        $data = [
            "systemInstruction" => [
                "parts" => [["text" => $systemPrompt]]
            ],
            "contents" => $contents,
            "generationConfig" => [
                "temperature" => 0.4 // Équilibre entre créativité et précision
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        // Désactiver la vérification SSL en local (facultatif mais utile sous XAMPP/Windows)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            $errorBody = $response ? json_decode($response, true) : null;
            $errorMsg = isset($errorBody['error']['message']) ? $errorBody['error']['message'] : 'HTTP ' . $httpCode;
            error_log("Erreur API Gemini (Freelancer Assistant) [{$httpCode}]: " . $errorMsg);
            return "⚠️ Je suis désolé, je n'arrive pas à me connecter au serveur d'Intelligence Artificielle. Erreur: " . $errorMsg;
        }

        $responseData = json_decode($response, true);
        
        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            return $responseData['candidates'][0]['content']['parts'][0]['text'];
        }

        return "Désolé, je n'ai pas pu comprendre votre demande.";
    }
}
