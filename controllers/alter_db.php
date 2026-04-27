<?php
require_once __DIR__ . '/../config.php';
$pdo = config::getConnexion();
try {
    $pdo->exec('ALTER TABLE contrat ADD COLUMN freelance_info VARCHAR(255) DEFAULT NULL, ADD COLUMN signature_client VARCHAR(255) DEFAULT NULL, ADD COLUMN signature_freelance VARCHAR(255) DEFAULT NULL;');
    echo "Columns added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
