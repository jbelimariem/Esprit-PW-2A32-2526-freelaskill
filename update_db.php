<?php
require 'config.php';
$pdo = config::getConnexion();

try {
    $pdo->exec("ALTER TABLE job_applications ADD COLUMN status VARCHAR(50) DEFAULT 'pending'");
    echo "Column 'status' added successfully to 'job_applications'.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
