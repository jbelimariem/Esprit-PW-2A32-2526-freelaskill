<?php
require 'config.php';
$pdo = config::getConnexion();
try {
    $pdo->exec("ALTER TABLE conversations DROP FOREIGN KEY conv_fk_user2");
    echo "FK dropped successfully\n";
} catch (Exception $e) {
    echo "Error dropping FK: " . $e->getMessage() . "\n";
}
