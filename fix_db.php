<?php
require_once __DIR__ . '/config.php';
$pdo = config::getConnexion();
try {
    $pdo->exec("INSERT IGNORE INTO user (id, nom, email, mot_de_passe) VALUES (1, 'Admin', 'admin@freelaskill.com', '123')");
    echo "User 1 inserted.";
} catch (Exception $e) {
    echo "Error inserting user: " . $e->getMessage();
}
