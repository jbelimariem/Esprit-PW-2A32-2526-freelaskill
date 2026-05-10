<?php
require_once 'controllers/config.php';
$pdo = config::getConnexion();
$stmt = $pdo->query("SELECT idProduit, nom, statut, user_id FROM produit WHERE nom LIKE '%Mercedes%'");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($res, JSON_PRETTY_PRINT);
