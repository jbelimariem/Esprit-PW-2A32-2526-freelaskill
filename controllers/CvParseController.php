<?php
// controllers/CvParseController.php
require_once __DIR__ . '/AiAssistantController.php'; // Pour récupérer la clé API

class CvParseController {
    // Utiliser la même clé API
    private const GEMINI_API_KEY = 'AIzaSyDdQZ40BrdEH8cg5pWx44Zo3LYJ_sLbmwQ';

    public function parsePdfCv($tmpFilePath) {
        // 1. Lire le fichier et l'encoder en Base64
        $pdfData = base64_encode(file_get_contents($tmpFilePath));

        // 2. Préparer le prompt
        $prompt = "Voici un CV au format PDF. Analyse son contenu et extrais les informations suivantes EXACTEMENT au format JSON (pas de balises markdown, juste le JSON pur) :\n";
        $prompt .= "{\n";
        $prompt .= "  \"name\": \"Prénom et Nom\",\n";
        $prompt .= "  \"email\": \"adresse email\",\n";
        $prompt .= "  \"phone\": \"numéro de téléphone\",\n";
        $prompt .= "  \"skills\": \"Liste des compétences clés, séparées par des virgules\",\n";
        $prompt .= "  \"cover_letter\": \"Rédige un très court texte de présentation (2 à 3 phrases) très professionnel, écrit à la 1ère personne ('Je'), basé sur les expériences du CV pour postuler à une mission générique.\"\n";
        $prompt .= "}";

        // 3. Appel de l'API Gemini 2.5 Flash avec InlineData
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . self::GEMINI_API_KEY;
        
        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt],
                        [
                            "inlineData" => [
                                "mimeType" => "application/pdf",
                                "data" => $pdfData
                            ]
                        ]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.1,
                "responseMimeType" => "application/json"
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
            error_log("Erreur API CV Parsing: " . $response);
            return null;
        }

        $responseData = json_decode($response, true);
        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            $jsonText = $responseData['candidates'][0]['content']['parts'][0]['text'];
            return json_decode($jsonText, true);
        }

        return null;
    }
}
