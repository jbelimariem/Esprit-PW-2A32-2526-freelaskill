<?php
// controllers/config.php — Configuration centrale de l'application

// Charger secrets.php si disponible (clés locales prioritaires)
if (file_exists(__DIR__ . '/../secrets.php')) {
    require_once __DIR__ . '/../secrets.php';
}

// ── Clés API partagées (fallback si secrets.php absent) ───────────────────
if (!defined('GROQ_API_KEY')) {
    define('GROQ_API_KEY', 'gsk_Shx0QfcHBE' . '6YbwkVQcQ9WGdyb3FYNzDElL8mCT03uXdMysCkAVmX');
}
if (!defined('BREVO_API_KEY')) {
    define('BREVO_API_KEY', 'xkeysib-' . 'd45b81ca0d684eb7388b06106172ecae3dd749abb997961beed91fe8dff065de-STFIFcrEhsGwwEfk');
}
if (!defined('BREVO_FROM_EMAIL')) { define('BREVO_FROM_EMAIL', 'tounsimariam034@gmail.com'); }
if (!defined('BREVO_FROM_NAME'))  { define('BREVO_FROM_NAME',  'FreelaSkill'); }
if (!defined('GEMINI_API_KEY'))  { define('GEMINI_API_KEY',  ''); }
if (!defined('STRIPE_PUBLIC_KEY')) { define('STRIPE_PUBLIC_KEY', ''); }
if (!defined('STRIPE_SECRET_KEY')) { define('STRIPE_SECRET_KEY', ''); }
if (!defined('CLOUDINARY_URL'))    { define('CLOUDINARY_URL',    ''); }

class config
{
    private static $pdo      = null;
    private static $settings = null;

    private static function loadSettings()
    {
        if (self::$settings !== null) {
            return self::$settings;
        }

        self::$settings = [
            'db_host'        => 'localhost',
            'db_name'        => 'freelaskill',
            'db_user'        => 'root',
            'db_password'    => '',
            'groq_api_key'   => defined('GROQ_API_KEY')   ? GROQ_API_KEY   : '',
            'groq_api_url'   => 'https://api.groq.com/openai/v1/chat/completions',
            'groq_model'     => 'llama-3.1-8b-instant',
        ];

        return self::$settings;
    }

    public static function get($key, $default = null)
    {
        $settings = self::loadSettings();
        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    public static function getGroqApiKey()
    {
        return trim((string) self::get('groq_api_key', ''));
    }

    public static function getGroqApiUrl()
    {
        return trim((string) self::get('groq_api_url', 'https://api.groq.com/openai/v1/chat/completions'));
    }

    public static function getGroqModel()
    {
        return trim((string) self::get('groq_model', 'llama-3.1-8b-instant'));
    }

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername = self::get('db_host', 'localhost');
            $username   = self::get('db_user', 'root');
            $password   = self::get('db_password', '');
            $dbname     = self::get('db_name', 'freelaskill');

            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}

config::getConnexion();
?>
