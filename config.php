<?php
class config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username   = "root";
            $password   = "";
            $dbname     = getenv('DB_NAME') ?: 'bd_rules';
            try {
                self::$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
config::getConnexion();

// ── API Keys ──────────────────────────────────────────────────────────
// Gemini API (gratuit) : https://aistudio.google.com/app/apikey
// Remplacez la valeur ci-dessous par votre vraie clé
define('GEMINI_API_KEY', 'AIzaSyCKjGMxx1ba9n1l8T7WskYi0f9wQy1MaUo');
