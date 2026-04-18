<?php
require_once __DIR__ . '/config.php';
$pdo = config::getConnexion();

try {
    $pdo->exec("ALTER TABLE produit ADD COLUMN user_id INT DEFAULT 1");
    echo "Column user_id added to produit.\n";
} catch (Exception $e) {
    echo "user_id already exists or error: " . $e->getMessage() . "\n";
}

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables:\n";
print_r($tables);

foreach ($tables as $t) {
    echo "\nStruct for $t:\n";
    print_r($pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC));
}
