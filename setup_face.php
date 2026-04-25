<?php
require_once 'c:/xampp/htdocs/projet2222/config.php';
$db = config::getConnexion();
try {
    $db->exec("ALTER TABLE users ADD COLUMN face_descriptor TEXT DEFAULT NULL");
    echo "Column face_descriptor added.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
