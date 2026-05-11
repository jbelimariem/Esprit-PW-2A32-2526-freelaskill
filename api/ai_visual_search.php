<?php
/**
 * Visual Search API - FreelaSkill
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../controllers/config.php';
require_once __DIR__ . '/../controllers/AIController.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "POST required"]); exit;
}

$file = $_FILES['image'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["error" => "No image"]); exit;
}

$imageData = file_get_contents($file['tmp_name']);
$ai = new AIController();
$description = $ai->analyzeImage(base64_encode($imageData));

if (empty($description) || strpos($description, 'Erreur') === 0) {
    echo json_encode(["error" => "AI Error: " . $description]); exit;
}

try {
    $pdo = config::getConnexion();
    $currentUserId = $_SESSION['user_id'] ?? null;
    
    // Nettoyage et extraction des mots-clés
    $cleanDesc = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $description), 'UTF-8');
    $allWords = array_values(array_filter(explode(' ', $cleanDesc), function($w) { 
        return mb_strlen($w) > 2 && !in_array($w, ['image', 'photo', 'objet', 'avec', 'dans']); 
    }));

    if (empty($allWords)) $allWords = explode(' ', $cleanDesc);

    // On sépare le mot principal (souvent le premier) des autres
    $mainWord = $allWords[0];
    
    $sql = "SELECT p.*, c.nom as categorie,
            (
                (CASE WHEN p.nom LIKE ? THEN 20 ELSE 0 END) +
                (CASE WHEN p.description LIKE ? THEN 5 ELSE 0 END) +
                (CASE WHEN c.nom LIKE ? THEN 10 ELSE 0 END)
            ) as relevance
            FROM produit p 
            LEFT JOIN category_prod c ON p.category_id = c.idCategory 
            WHERE p.statut != 'vendu'
              AND (? IS NULL OR p.user_id IS NULL OR p.user_id <> ?)";

    $params = ["%$mainWord%", "%$mainWord%", "%$mainWord%", $currentUserId, $currentUserId];
    
    $whereParts = [];
    foreach ($allWords as $w) {
        $whereParts[] = "p.nom LIKE ? OR p.description LIKE ? OR c.nom LIKE ?";
        $params[] = "%$w%"; $params[] = "%$w%"; $params[] = "%$w%";
    }

    if (!empty($whereParts)) {
        $sql .= " AND (" . implode(" OR ", $whereParts) . ")";
    }

    $sql .= " ORDER BY relevance DESC, p.idProduit DESC LIMIT 20";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "description" => $description,
        "products" => $products
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
