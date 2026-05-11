<?php
/**
 * Script d'installation de PHPMailer (sans Composer)
 * Télécharge les 3 fichiers nécessaires depuis GitHub
 * Exécuter UNE SEULE FOIS : localhost/.../controllers/install_phpmailer.php
 */

$targetDir = __DIR__ . '/../Models/PHPMailer/';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$files = [
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php',
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php'      => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
];

echo "<h2>Installation PHPMailer</h2>";
$allOk = true;

foreach ($files as $filename => $url) {
    $dest = $targetDir . $filename;
    if (file_exists($dest)) {
        echo "✅ $filename — déjà installé<br>";
        continue;
    }
    $content = @file_get_contents($url);
    if ($content === false) {
        echo "❌ $filename — échec du téléchargement (vérifiez allow_url_fopen)<br>";
        $allOk = false;
    } else {
        file_put_contents($dest, $content);
        echo "✅ $filename — installé (" . strlen($content) . " bytes)<br>";
    }
}

echo "<br>";
if ($allOk) {
    echo "<strong style='color:green'>✅ PHPMailer installé avec succès !</strong><br>";
    echo "<a href='../Views/Backoffice/admin_email_config.php'>→ Configurer les emails</a>";
} else {
    echo "<strong style='color:red'>❌ Certains fichiers n'ont pas pu être téléchargés.</strong><br>";
    echo "Activez <code>allow_url_fopen = On</code> dans php.ini ou téléchargez manuellement depuis ";
    echo "<a href='https://github.com/PHPMailer/PHPMailer/tree/master/src'>GitHub PHPMailer</a>";
}
?>
