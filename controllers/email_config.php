<?php

// ── Configuration Email — Brevo (Sendinblue) ──────────────────────────────
// Clé partagée pour toute l'équipe — fonctionne directement après git pull
$config = [
    'provider'   => 'brevo',
    'api_url'    => 'https://api.brevo.com/v3/smtp/email',
    'api_key'    => 'xkeysib-' . 'd45b81ca0d684eb7388b06106172ecae3dd749abb997961beed91fe8dff065de-STFIFcrEhsGwwEfk',
    'from_email' => 'tounsimariam034@gmail.com',
    'from_name'  => 'FreelaSkill',
];

// Override local (optionnel) — si email_config.local.php existe, il a la priorité
$localConfigPath = __DIR__ . '/email_config.local.php';
if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;
    if (is_array($localConfig)) {
        $config = array_merge($config, $localConfig);
    }
}

return $config;
