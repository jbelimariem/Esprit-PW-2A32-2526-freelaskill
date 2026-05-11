<?php
require 'config.php';
$pdo = config::getConnexion();
$stmt = $pdo->query('SELECT id, nom, prenom, email, role FROM users LIMIT 10');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt2 = $pdo->query('SELECT id, name, status FROM job_applications');
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
