<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../controllers/config.php';
require_once __DIR__ . '/../controllers/AIController.php';

$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];

if (count($ids) < 2) {
    echo json_encode(["error" => "Au moins deux produits sont nécessaires pour le combat !"]);
    exit;
}

try {
    $pdo = config::getConnexion();
    $products = [];
    
    foreach($ids as $id) {
        $stmt = $pdo->prepare("SELECT nom, prix, description FROM produit WHERE idProduit = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if ($p) {
            $products[] = $p;
        }
    }

    if (count($products) < 2) {
        echo json_encode(["error" => "Certains produits n'existent plus ou sont introuvables."]);
        exit;
    }

    $ai = new AIController();
    $result = $ai->compareBattle($products);

    echo json_encode(["battle_report" => $result]);

} catch (Exception $e) {
    echo json_encode(["error" => "Erreur BDD : " . $e->getMessage()]);
}
