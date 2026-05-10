<?php

$config = [
    // Supported providers: resend, generic.
    'provider'   => getenv('MAIL_API_PROVIDER') ?: 'resend',
    'api_url'    => getenv('MAIL_API_URL') ?: 'https://api.resend.com/emails',
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
