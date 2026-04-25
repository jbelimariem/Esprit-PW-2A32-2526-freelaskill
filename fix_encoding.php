<?php
$files = [
    'c:/xampp/htdocs/projet2222/views/frontoffice/profile.php',
    'c:/xampp/htdocs/projet2222/views/frontoffice/login.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Decode double UTF-8 encoding (ISO-8859-1 back to correct UTF-8)
    $fixed = utf8_decode($content);
    file_put_contents($file, $fixed);
    echo basename($file) . " fixed.\n";
}
