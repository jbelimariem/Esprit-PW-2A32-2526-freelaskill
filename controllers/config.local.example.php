<?php
// ============================================================
//  CONFIGURATION LOCALE — NE PAS COMMITTER CE FICHIER !
//  Copiez ce fichier : cp config.local.example.php config.local.php
//  puis remplacez les valeurs ci-dessous.
// ============================================================

return [
    // -----------------------------------------------------------
    // GROQ API  (IA : chatbot, bio, modération, mots de passe)
    // Obtenez votre clé GRATUITE sur : https://console.groq.com
    // -----------------------------------------------------------
    'groq_api_key' => 'gsk_VOTRE_CLE_GROQ_ICI',
    'groq_api_url' => 'https://api.groq.com/openai/v1/chat/completions',
    'groq_model'   => 'llama-3.1-8b-instant',
];
