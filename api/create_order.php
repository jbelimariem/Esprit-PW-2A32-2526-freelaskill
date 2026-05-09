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
require_once __DIR__ . '/../controllers/MailController.php';

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
        mode_paiement VARCHAR(50),
        mode_livraison VARCHAR(50),
        montant_total DECIMAL(10,2) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Mise à jour de la table si colonnes manquantes
    try {
        $pdo->exec("ALTER TABLE commande ADD COLUMN IF NOT EXISTS mode_paiement VARCHAR(50)");
        $pdo->exec("ALTER TABLE commande ADD COLUMN IF NOT EXISTS mode_livraison VARCHAR(50)");
    } catch (Exception $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS commande_produit (
        id INT AUTO_INCREMENT PRIMARY KEY,
        idCommande INT NOT NULL,
        idProduit INT NOT NULL,
        quantite INT NOT NULL DEFAULT 1,
        prix_unitaire DECIMAL(10,2) NOT NULL DEFAULT 0,
        FOREIGN KEY (idCommande) REFERENCES commande(idCommande) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function getUserEmail($pdo, $user_id) {
    foreach (['idUser', 'id'] as $column) {
        try {
            $stmt = $pdo->prepare("SELECT email FROM user WHERE $column = ? LIMIT 1");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && !empty($user['email']) && filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                return $user['email'];
            }
        } catch (Exception $e) {}
    }

    return null;
}

function buildMailItems($cart) {
    $items = [];

    foreach ($resolvedCart as $item) {
        $items[] = [
            'nom' => $item['title'] ?? $item['nom'] ?? 'Produit',
            'prix' => $item['price'] ?? $item['prix'] ?? 0,
            'quantite' => $item['quantity'] ?? $item['quantite'] ?? 1,
        ];
    }

    return $items;
}

function resolveCartProducts($pdo, $cart) {
    $resolved = [];

    foreach ($cart as $item) {
        $produit_id = null;
        $quantity = max(1, intval($item['quantity'] ?? $item['quantite'] ?? 1));

        if (!empty($item['id']) && intval($item['id']) > 0) {
            $produit_id = intval($item['id']);
        } elseif (!empty($item['idProduit']) && intval($item['idProduit']) > 0) {
            $produit_id = intval($item['idProduit']);
        }

        if (!$produit_id && !empty($item['title'])) {
            $find = $pdo->prepare("SELECT idProduit FROM produit WHERE nom = ? LIMIT 1");
            $find->execute([trim($item['title'])]);
            $row = $find->fetch(PDO::FETCH_ASSOC);
            if ($row) $produit_id = intval($row['idProduit']);
        }

        if (!$produit_id) {
            throw new Exception("Produit introuvable dans le panier.");
        }

        $stmt = $pdo->prepare("SELECT idProduit, nom, prix, stock, disponibilite FROM produit WHERE idProduit = ? FOR UPDATE");
        $stmt->execute([$produit_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Produit #$produit_id introuvable.");
        }
        if (($product['disponibilite'] ?? '') === 'Non disponible') {
            throw new Exception($product['nom'] . " est non disponible.");
        }
        if ((int)$product['stock'] < $quantity) {
            throw new Exception("Stock insuffisant pour " . $product['nom'] . ".");
        }

        $resolved[] = [
            'idProduit' => (int)$product['idProduit'],
            'id' => (int)$product['idProduit'],
            'nom' => $product['nom'],
            'title' => $product['nom'],
            'prix' => (float)$product['prix'],
            'price' => (float)$product['prix'],
            'quantite' => $quantity,
            'quantity' => $quantity,
        ];
    }

    return $resolved;
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

$cart           = $input['cart'];
$adresse        = trim($input['adresse'] ?? '');
$mode_paiement  = trim($input['mode_paiement'] ?? 'Sur place');
$mode_livraison = trim($input['mode_livraison'] ?? 'Standard');
$user_id        = $_SESSION['user_id'] ?? 1;

if (empty($cart) || !is_array($cart)) {
    http_response_code(400);
    echo json_encode(['error' => 'Panier vide']);
    exit;
}

// ── Calcul montant ───────────────────────────────────────────────────────────
// ── Transaction ──────────────────────────────────────────────────────────────
try {
    $pdo = config::getConnexion();
    ensureTables($pdo);

    $pdo->beginTransaction();

    $resolvedCart = resolveCartProducts($pdo, $cart);
    $montant_total = 0;
    foreach ($resolvedCart as $item) {
        $montant_total += $item['prix'] * $item['quantite'];
    }

    // 1. Créer la commande
    $stmt = $pdo->prepare("INSERT INTO commande (user_id, date_commande, statut, adresse_livraison, mode_paiement, mode_livraison, montant_total)
                           VALUES (?, CURDATE(), 'en_attente', ?, ?, ?, ?)");
    $stmt->execute([$user_id, $adresse, $mode_paiement, $mode_livraison, $montant_total]);
    $commande_id = $pdo->lastInsertId();

    // 2. Ajouter chaque produit
    $stmtCP = $pdo->prepare("INSERT INTO commande_produit (idCommande, idProduit, quantite, prix_unitaire)
                             VALUES (?, ?, ?, ?)");
    $stmtStock = $pdo->prepare("UPDATE produit SET stock = GREATEST(stock - ?, 0), disponibilite = CASE WHEN GREATEST(stock - ?, 0) <= 0 THEN 'Non disponible' ELSE disponibilite END WHERE idProduit = ?");

    foreach ($resolvedCart as $item) {
        $stmtCP->execute([
            $commande_id,
            $item['idProduit'],
            $item['quantite'],
            $item['prix']
        ]);
        
        $qty = $item['quantite'];
        $stmtStock->execute([$qty, $qty, $item['idProduit']]);
    }

    $pdo->commit();

    $mailSent = false;
    $mailError = null;
    $userEmail = getUserEmail($pdo, $user_id);

    if ($userEmail) {
        $mailController = new MailController();
        $mailSent = $mailController->sendOrderConfirmation($userEmail, $commande_id, $montant_total, $resolvedCart);
        if (!$mailSent) {
            $mailError = $mailController->getLastError();
        }
    } else {
        $mailError = "Aucun email valide trouve pour l'utilisateur #$user_id.";
        error_log('[TalentBridge SMTP] ' . $mailError);
    }

    http_response_code(201);
    echo json_encode([
        'success'  => true,
        'message'  => 'Commande créée avec succès',
        'order_id' => (int)$commande_id,
        'montant'  => $montant_total,
        'mail_sent' => $mailSent,
        'mail_error' => $mailError
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
