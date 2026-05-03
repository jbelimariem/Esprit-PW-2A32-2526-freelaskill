<?php

$config = [
    // Supported providers: brevo, resend, generic.
    'provider'   => getenv('MAIL_API_PROVIDER') ?: 'brevo',
    'api_url'    => getenv('MAIL_API_URL') ?: 'https://api.brevo.com/v3/smtp/email',
    'api_key'    => getenv('MAIL_API_KEY') ?: '',
    'from_email' => getenv('MAIL_FROM_EMAIL') ?: 'tounsimariam034@gmail.com',
    'from_name'  => getenv('MAIL_FROM_NAME') ?: 'FreelaSkill',
];

$localConfigPath = __DIR__ . '/email_config.local.php';

if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;

    if (is_array($localConfig)) {
        $config = array_merge($config, $localConfig);
    }
}

return $config;
