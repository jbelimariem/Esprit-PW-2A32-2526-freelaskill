<?php
// ============================================================
//  EMAIL CONFIG LOCALE — NE PAS COMMITTER CE FICHIER !
//  Copiez ce fichier : cp email_config.local.example.php email_config.local.php
//  puis remplacez les valeurs ci-dessous.
// ============================================================

return [
    // -----------------------------------------------------------
    // FOURNISSEUR EMAIL : on utilise BREVO (ex-Sendinblue)
    // Créez un compte GRATUIT sur : https://app.brevo.com
    //   1. Allez dans SMTP & API > API Keys
    //   2. Créez une clé API et collez-la dans 'api_key'
    //   3. Vérifiez votre adresse email dans Brevo (Senders)
    //      et mettez-la dans 'from_email'
    // -----------------------------------------------------------
    'provider'   => 'brevo',
    'api_url'    => 'https://api.brevo.com/v3/smtp/email',
    'api_key'    => 'xkeysib-VOTRE_CLE_BREVO_ICI',
    'from_email' => 'votre@email.com',
    'from_name'  => 'FreelaSkill',
];
