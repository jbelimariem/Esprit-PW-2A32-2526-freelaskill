<?php
require 'config.php';
$pdo = config::getConnexion();
$stmt = $pdo->query('DESCRIBE conversations');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
