<?php
require_once 'controllers/config.php';
$pdo = config::getConnexion();
$stmt = $pdo->query("SELECT * FROM produit WHERE idProduit = 5");
$res = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($res, JSON_PRETTY_PRINT);
