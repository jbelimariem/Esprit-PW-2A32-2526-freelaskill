<?php
class config
{
    private static $pdo = null;
    private static $settings = null;

    private static function loadSettings()
    {
        if (self::$settings !== null) {
            return self::$settings;
        }

        self::$settings = [
            'db_host' => 'localhost',
            'db_name' => 'freelaskill',
            'db_user' => 'root',
            'db_password' => '',
            'groq_api_key' => getenv('GROQ_API_KEY') ?: '',
            'groq_api_url' => getenv('GROQ_API_URL') ?: 'https://api.groq.com/openai/v1/chat/completions',
            'groq_model' => getenv('GROQ_MODEL') ?: 'llama-3.1-8b-instant',
        ];

        $localConfigPath = __DIR__ . '/config.local.php';

        if (is_file($localConfigPath)) {
            $localSettings = require $localConfigPath;

            if (is_array($localSettings)) {
                self::$settings = array_merge(self::$settings, $localSettings);
            }
        }

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
            $username = self::get('db_user', 'root');
            $password = self::get('db_password', '');
            $dbname = self::get('db_name', 'freelaskill');

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
