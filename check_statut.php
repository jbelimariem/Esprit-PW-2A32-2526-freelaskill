<?php
require_once 'controllers/config.php';
$pdo = config::getConnexion();
$stmt = $pdo->query("SELECT idProduit, nom, statut, user_id FROM produit");
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($produits, JSON_PRETTY_PRINT);
