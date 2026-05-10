<?php
// config.php — Connexion à la base de données

// Charger secrets.php si disponible
if (file_exists(__DIR__ . '/secrets.php')) {
    require_once __DIR__ . '/secrets.php';
}

// Fallback clés API si secrets.php absent
if (!defined('GROQ_API_KEY')) {
    define('GROQ_API_KEY', 'gsk_Shx0QfcHBE' . '6YbwkVQcQ9WGdyb3FYNzDElL8mCT03uXdMysCkAVmX');
}
if (!defined('GEMINI_API_KEY'))    { define('GEMINI_API_KEY', ''); }
if (!defined('STRIPE_PUBLIC_KEY')) { define('STRIPE_PUBLIC_KEY', ''); }
if (!defined('STRIPE_SECRET_KEY')) { define('STRIPE_SECRET_KEY', ''); }
if (!defined('CLOUDINARY_URL'))    { define('CLOUDINARY_URL', ''); }


class config {
    private static $pdo = null;

    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username   = "root";
            $password   = "";
            $dbname     = "freelaskill";
            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password,
                    [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"]
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Erreur de connexion : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

config::getConnexion();
?>
