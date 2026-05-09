<?php

class AIController
{
    private $groqToken = "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";

    public function generateDescription($prompt)
    {
        $system = "Tu es un expert marketing pour la marketplace FreelaSkill. Genere une description de produit captivante et professionnelle en francais.";
        $text = $this->callGroq($prompt, 0.7, $system);

        if (strpos($text, 'Erreur') === 0) {
            return ["error" => $text];
        }

        return ["text" => $text];
    }

    public function moderateContent($text)
    {
        $model = "llama-3.3-70b-versatile";
        $system = "TU ES UN MODERATEUR ET ANALYSTE.
        1. Si le texte contient des insultes ou vulgarites, reponds 'REFUS'.
        2. Sinon, reponds 'OK: ' suivi d'un resume ultra-court (max 6 mots) de ce que l'utilisateur vend.
        Exemple : 'OK: Voiture Ferrari de luxe'";

        $answer = $this->callGroq("Texte a analyser : '$text'", 0.2, $system, $model);
        $cleanAnswer = trim($answer);

        if (strpos(strtoupper($cleanAnswer), 'OK:') === 0) {
            $recap = trim(substr($cleanAnswer, 3));
            return ["status" => "APPROVED", "reason" => $recap, "score" => 100];
        }

        return ["status" => "REJECTED", "reason" => "Contenu inapproprie detecte.", "score" => 0];
    }

    public function recommendProducts($userQuestion, $catalogueContext)
    {
        $model = "llama-3.3-70b-versatile";
        $system = "Tu es ARIA, l'assistante IA experte en shopping de la marketplace FreelaSkill Tunisia.

REGLES D'OR DE LOGIQUE :
1. ANALYSE DU BUDGET : Si l'utilisateur donne un budget B, un produit est 'dans le budget' si son PRIX <= B.
2. FILTRAGE STRICT : Il est INTERDIT de suggerer ou de mentionner un produit dont le prix est SUPERIEUR au budget (PRIX > B). Ignore-les totalement.
3. DISPONIBILITE : Ne propose que des produits presents dans le catalogue.
4. CATEGORIES : Si l'utilisateur cherche une 'voiture', regarde les produits dans la categorie 'Vehicules' ou dont le nom/description mentionne une voiture.

REGLES DE REPONSE :
1. Avant chaque produit recommande, ecris son identifiant exact sous la forme [ID:X] (exemple : [ID:5]).
2. Reponds toujours en francais, de facon chaleureuse et professionnelle.
3. Structure ta reponse ainsi :
   - Phrase d'accroche
   - Liste des produits conformes au budget (ID, nom, prix, justification)
   - Conseil final
4. Si AUCUN produit ne correspond au budget, dis-le poliment et ne propose rien d'autre.
5. Maximum 3 recommandations.

CATALOGUE DES PRODUITS DISPONIBLES :
$catalogueContext";

        $reply = $this->callGroq($userQuestion, 0.1, $system, $model);

        if (strpos($reply, 'Erreur') === 0) {
            return ["error" => $reply];
        }

        return ["reply" => $reply];
    }

    public function chatAboutProduct($userQuestion, $productName, $productDescription)
    {
        $system = "Tu es l'assistant de FreelaSkill. Reponds a la question de l'utilisateur en utilisant UNIQUEMENT les infos du produit suivant. Produit : $productName. Description : $productDescription.";
        return $this->callGroq($userQuestion, 0.4, $system);
    }

    private function callGroq($userInput, $temperature = 0.7, $systemPrompt = "Tu es un assistant utile.", $model = "llama-3.1-8b-instant")
    {
        $url = "https://api.groq.com/openai/v1/chat/completions";

        $data = [
            "model" => $model,
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => $userInput]
            ],
            "temperature" => $temperature
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->groqToken,
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            return trim($result['choices'][0]['message']['content']);
        }

        if ($curlError)
            return "Erreur de connexion (CURL) : " . $curlError;
        if (isset($result['error']['message']))
            return "Erreur API Groq : " . $result['error']['message'];

        return "Erreur lors de la generation. (Code inconnu)";
    }

    public function compareBattle(array $products)
    {
        $model = "llama-3.3-70b-versatile";
        $productsList = "";

        foreach ($products as $i => $p) {
            $num = $i + 1;
            $productsList .= "PRODUIT #$num : Name: {$p['nom']}, Price: {$p['prix']} DT, Description: {$p['description']}\n";
        }

        $system = "Tu es un EXPERT TECHNIQUE et TESTEUR de produits. Ton but est de departager plusieurs articles de facon objective.

FORMAT DE REPONSE OBLIGATOIRE (en HTML leger) :
1. Un tableau HTML <table> comparant les caracteristiques cles de TOUS les produits cote a cote.
2. Une section <div class='verdict-box'> avec un titre 'LE VERDICT ARIA' expliquant lequel est le meilleur achat selon le budget et l'usage.

Style : Utilise des classes CSS comme 'battle-table' deja stylees. Sois precis sur les specs techniques.";

        $prompt = "Fais un match comparatif entre ces produits :\n\n" . $productsList;
        return $this->callGroq($prompt, 0.7, $system, $model);
    }

    public function analyzeImage($base64Image, $mimeType = 'image/jpeg')
    {
        $model = "meta-llama/llama-4-scout-17b-16e-instruct";
        $url = "https://api.groq.com/openai/v1/chat/completions";

        $prompt = "Identify the main product in this image. Return 3 to 5 simple search keywords (French). Example: 'Ordinateur portable Dell'. No sentences.";

        $data = [
            "model" => $model,
            "messages" => [
                [
                    "role" => "user",
                    "content" => [
                        ["type" => "text", "text" => $prompt],
                        [
                            "type" => "image_url",
                            "image_url" => ["url" => "data:" . $mimeType . ";base64," . $base64Image]
                        ]
                    ]
                ]
            ],
            "temperature" => 0.1,
            "max_completion_tokens" => 80
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->groqToken,
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            return trim($result['choices'][0]['message']['content']);
        }

        if ($curlError)
            return "Erreur de connexion (CURL) : " . $curlError;
        if (isset($result['error']['message']))
            return "Erreur API Groq : " . $result['error']['message'];

        return "Erreur lors de l'analyse de l'image.";
    }
}
