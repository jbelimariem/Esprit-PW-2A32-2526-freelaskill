<?php
require_once 'controllers/config.php';
$pdo = config::getConnexion();
$stmt = $pdo->query("SELECT email, nom, prenom FROM users");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($res, JSON_PRETTY_PRINT);
