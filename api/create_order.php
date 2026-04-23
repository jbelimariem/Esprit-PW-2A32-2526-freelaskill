<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Initialise les tables si nécessaire ─────────────────────────────────────
function ensureTables($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS commande (
        idCommande INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL DEFAULT 1,
        date_commande DATE NOT NULL,
        statut VARCHAR(50) NOT NULL DEFAULT 'en_attente',
        adresse_livraison TEXT,
        montant_total DECIMAL(10,2) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS commande_produit (
        id INT AUTO_INCREMENT PRIMARY KEY,
        idCommande INT NOT NULL,
        idProduit INT NOT NULL,
        quantite INT NOT NULL DEFAULT 1,
        prix_unitaire DECIMAL(10,2) NOT NULL DEFAULT 0,
        FOREIGN KEY (idCommande) REFERENCES commande(idCommande) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// ── Vérification méthode ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// ── Lire JSON ────────────────────────────────────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['cart']) || !isset($input['adresse'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données manquantes', 'received' => $input]);
    exit;
}

$cart    = $input['cart'];
$adresse = trim($input['adresse'] ?? '');
$user_id = $_SESSION['user_id'] ?? 1;

if (empty($cart) || !is_array($cart)) {
    http_response_code(400);
    echo json_encode(['error' => 'Panier vide']);
    exit;
}

// ── Calcul montant ───────────────────────────────────────────────────────────
$montant_total = 0;
foreach ($cart as $item) {
    $price    = floatval($item['price']    ?? 0);
    $quantity = intval($item['quantity'] ?? 1);
    $montant_total += $price * $quantity;
}

// ── Transaction ──────────────────────────────────────────────────────────────
try {
    $pdo = config::getConnexion();
    ensureTables($pdo);

    $pdo->beginTransaction();

    // 1. Créer la commande
    $stmt = $pdo->prepare("INSERT INTO commande (user_id, date_commande, statut, adresse_livraison, montant_total)
                           VALUES (?, CURDATE(), 'en_attente', ?, ?)");
    $stmt->execute([$user_id, $adresse, $montant_total]);
    $commande_id = $pdo->lastInsertId();

    // 2. Ajouter chaque produit
    $stmtCP = $pdo->prepare("INSERT INTO commande_produit (idCommande, idProduit, quantite, prix_unitaire)
                             VALUES (?, ?, ?, ?)");

    foreach ($cart as $item) {
        $produit_id = null;

        // Priorité à l'ID envoyé depuis le JS
        if (!empty($item['id']) && intval($item['id']) > 0) {
            $produit_id = intval($item['id']);
        }

        // Fallback : chercher par nom
        if (!$produit_id && !empty($item['title'])) {
            $find = $pdo->prepare("SELECT idProduit FROM produit WHERE nom = ? LIMIT 1");
            $find->execute([trim($item['title'])]);
            $row = $find->fetch(PDO::FETCH_ASSOC);
            if ($row) $produit_id = intval($row['idProduit']);
        }

        if ($produit_id) {
            $stmtCP->execute([
                $commande_id,
                $produit_id,
                intval($item['quantity'] ?? 1),
                floatval($item['price']    ?? 0)
            ]);
        }
        // Si le produit n'est pas trouvé en BD, on l'ignore (ne plante pas la commande)
    }

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        'success'  => true,
        'message'  => 'Commande créée avec succès',
        'order_id' => (int)$commande_id,
        'montant'  => $montant_total
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'error'   => 'Erreur lors de la création de la commande',
        'details' => $e->getMessage()
    ]);
}
?>
