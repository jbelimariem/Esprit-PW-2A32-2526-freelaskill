<?php
require 'config.php';
$pdo = config::getConnexion();

echo "--- TABLES IN DB ---\n";
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $tables) . "\n\n";

if (in_array('job_applications', $tables)) {
    echo "--- TABLE job_applications ---\n";
    $stmt = $pdo->query("DESCRIBE job_applications");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
} else {
    echo "TABLE job_applications DOES NOT EXIST!\n";
}
