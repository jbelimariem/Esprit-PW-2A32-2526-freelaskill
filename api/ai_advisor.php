<?php
/**
 * AI ADVISOR API — FreelaSkill
 * Reçoit une question utilisateur, interroge la BDD pour les produits disponibles,
 * injecte le catalogue comme contexte, et retourne une recommandation personnalisée.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Méthode non autorisée"]);
    exit;
}

require_once __DIR__ . '/../controllers/config.php';
require_once __DIR__ . '/../controllers/AIController.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$input = json_decode(file_get_contents('php://input'), true);
$question = trim($input['question'] ?? '');

if (empty($question)) {
    echo json_encode(["error" => "Question vide"]);
    exit;
}

// ── 1. RÉCUPÉRER TOUS LES PRODUITS DISPONIBLES DEPUIS LA BDD ──────────────
try {
    $pdo = config::getConnexion();
    $currentUserId = $_SESSION['user_id'] ?? null;

    // On récupère les produits avec leurs catégories
    $stmt = $pdo->prepare("
        SELECT 
            p.idProduit,
            p.nom,
            p.description,
            p.prix,
            p.stock,
            p.image,
            p.disponibilite,
            c.nom AS categorie
        FROM produit p
        LEFT JOIN category_prod c ON p.category_id = c.idCategory
        WHERE LOWER(TRIM(p.statut)) = 'disponible'
          AND (? IS NULL OR p.user_id IS NULL OR p.user_id <> ?)
        ORDER BY p.prix ASC
    ");
    $stmt->execute([$currentUserId, $currentUserId]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo json_encode(["error" => "Erreur BDD : " . $e->getMessage()]);
    exit;
}

if (empty($produits)) {
    echo json_encode([
        "reply" => "Désolé, il n'y a aucun produit disponible en ce moment dans notre marketplace.",
        "products" => []
    ]);
    exit;
}

// ── 2. CONSTRUIRE LE CATALOGUE POUR LE CONTEXTE IA ────────────────────────
$catalogueLines = [];
foreach ($produits as $p) {
    $dispo = $p['disponibilite'] ?? 'Disponible maintenant';
    $catalogueLines[] = sprintf(
        "[ID:%d] \"%s\" | Catégorie: %s | Prix: %s DT | Dispo: %s | Description: %s",
        $p['idProduit'],
        $p['nom'],
        $p['categorie'] ?? 'Autre',
        number_format($p['prix'], 2, '.', ' '),
        $dispo,
        mb_strimwidth($p['description'] ?? '', 0, 150, '…')
    );
}
$catalogueText = implode("\n", $catalogueLines);

// ── 3. APPEL GROQ AVEC CONTEXTE PRODUIT ───────────────────────────────────
$aiController = new AIController();
$result = $aiController->recommendProducts($question, $catalogueText);

// ── 4. RÉCUPÉRER LES PRODUITS RECOMMANDÉS (par IDs extraits de la réponse) ─
$recommendedProducts = [];
$rawReply = $result['reply'] ?? '';

// Extraire les IDs mentionnés dans la réponse IA (format [ID:X])
preg_match_all('/\[ID:(\d+)\]/', $rawReply, $matches);
$recommendedIds = array_unique($matches[1] ?? []);

if (!empty($recommendedIds)) {
    foreach ($produits as $p) {
        if (in_array((string)$p['idProduit'], $recommendedIds)) {
            $recommendedProducts[] = $p;
        }
    }
}

// Nettoyer la réponse — retirer les marqueurs [ID:X] du texte final
$cleanReply = preg_replace('/\[ID:\d+\]\s?/', '', $rawReply);
$cleanReply = trim($cleanReply);

echo json_encode([
    "reply"    => $cleanReply,
    "products" => $recommendedProducts,
    "total_catalog" => count($produits)
]);
