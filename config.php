<?php
if (!defined('CLOUDINARY_URL')) {
    define('CLOUDINARY_URL', 'cloudinary://962455564499621:6KCUgBqDWEQCJtuolijK69-jlck@dsimea1nb');
}

// STRIPE CONFIGURATION
define('STRIPE_PUBLIC_KEY', 'pk_test_51TSfkU90r15ENUzNsAFBq4RQqWKfpiWd0bsa7W2Ss9DWq7cutvBba7wlCFuA6kqjYYJvlsLoqTrj6JW5SozN9Adf009vjh6sph'); // Remplace par ta vraie clé publique
define('STRIPE_SECRET_KEY', 'sk_test_51TSfkU90r15ENUzNGkNPM6UqFMEKkse26iuYEcAnGfxH3klenuCuRF1fxxmkM2rtKc5RZ8NymQZs0MrFebXIFTZ900m0eRxoP6');

// GMAIL SMTP CONFIGURATION
// Utilise un vrai compte Gmail + un mot de passe d'application Google.
// Tu peux aussi definir ces valeurs dans l'environnement: GMAIL_USER et GMAIL_PASS.
define('GMAIL_USER', getenv('GMAIL_USER') ?: 'acuityacuity23@gmail.com'); // ex: toncompte@gmail.com
define('GMAIL_PASS', getenv('GMAIL_PASS') ?: 'xrds olbu erwg irzn');

class config
{
    private static $pdo = null;
    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "freelaskill";
            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname",
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