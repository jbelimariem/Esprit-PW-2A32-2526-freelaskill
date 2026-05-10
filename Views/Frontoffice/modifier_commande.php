<?php
require_once __DIR__ . '/../../controllers/commandeController.php';
require_once __DIR__ . '/../../controllers/CommandeProduitController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$currentUserId = $_SESSION['user_id'] ?? null;
if (!$currentUserId) {
    header('Location: login.php');
    exit;
}

$notifController = new NotificationController();
$unreadCount = $notifController->getUnreadCount($currentUserId);

$commandeController = new CommandeController();
$cpController = new CommandeProduitController();

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = $commandeController->getByIdData($orderId);

if (!$order || (int)($order['user_id'] ?? 0) !== (int)$currentUserId) {
    header('Location: mes_commandes.php');
    exit();
}

$products = $cpController->getByCommandeData($orderId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_item_id']) && !empty($_POST['delete_item_id'])) {
        $cpController->deleteData($orderId, intval($_POST['delete_item_id']));
        
        // Recalculate total
        $remaining = $cpController->getByCommandeData($orderId);
        if (empty($remaining)) {
            $commandeController->deleteData($orderId);
            header("Location: mes_commandes.php?deleted=1");
            exit();
        }
        
        $newTotal = 0;
        foreach ($remaining as $r) { $newTotal += $r['quantite'] * $r['prix_unitaire']; }
        $commandeController->updateDetailsData($orderId, $order['adresse_livraison'], $order['mode_paiement'], $newTotal);
        header("Location: modifier_commande.php?id=$orderId");
        exit();
    }

    $newAdresse = $_POST['adresse_livraison'] ?? $order['adresse_livraison'];
    $newModePaiement = $_POST['mode_paiement'] ?? $order['mode_paiement'];
    $quantities = $_POST['qty'] ?? [];

    $newTotal = 0;
    foreach ($products as $p) {
        $idP = $p['idProduit'];
        $qty = isset($quantities[$idP]) ? max(1, intval($quantities[$idP])) : $p['quantite'];
        $cpController->updateQuantiteData($orderId, $idP, $qty);
        $newTotal += $qty * $p['prix_unitaire'];
    }

    $commandeController->updateDetailsData($orderId, $newAdresse, $newModePaiement, $newTotal);
    header('Location: mes_commandes.php?updated=1');
    exit();
}

$unreadCount = $notifController->getUnreadCount($currentUserId);
$totalProduitCount = count($commandeController->getAllData()); // Or just a placeholder

?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Commande — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=6">
    <style>
        .edit-container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .edit-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1.5rem;
            padding: 2rem;
        }
        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 600; }
        .form-input {
            width: 100%; padding: 0.8rem 1rem;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 0.75rem; color: #fff; outline: none;
        }
        .form-input:focus { border-color: #3b82f6; }
        .prod-row {
            display: flex; align-items: center; gap: 1rem;
            background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 1rem; margin-bottom: 1rem;
        }
        .prod-img { width: 60px; height: 60px; border-radius: 0.5rem; object-fit: cover; }
        .prod-info { flex: 1; }
        .qty-input { width: 80px; text-align: center; }
        .btn-del-item {
            background: rgba(239,68,68,0.1); color: #ef4444; border: none;
            width: 32px; height: 32px; border-radius: 50%; cursor: pointer; transition: 0.2s;
            display: flex; align-items: center; justify-content: center;
        }
        .btn-del-item:hover { background: rgba(239,68,68,0.2); }
        
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px); z-index: 9999;
            display: none; align-items: center; justify-content: center;
        }
        .modal-content {
            background: #0f172a; border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1.5rem; padding: 2rem; width: 90%; max-width: 400px;
            text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .modal-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; }
        .modal-text { color: #94a3b8; margin-bottom: 2rem; font-size: 0.95rem; }
        .modal-btns { display: flex; gap: 1rem; }
        .modal-btn { flex: 1; padding: 0.75rem; border-radius: 0.75rem; font-weight: 600; cursor: pointer; border: none; transition: 0.2s; }
        .btn-cancel { background: rgba(255,255,255,0.05); color: #fff; }
        .btn-confirm { background: #ef4444; color: #fff; }
    </style>
</head>
<body class="page-anim">

<nav>
    <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
    <div class="nav-right">
        <a href="notifications.php" class="cart-btn" style="position: relative; margin-right: 10px;">
            <i class="fa-solid fa-bell"></i>
            <?php if($unreadCount > 0): ?>
                <span class="cart-count" style="position:absolute;top:-6px;right:-6px;background:#3b82f6;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a href="mes_commandes.php" class="cart-btn"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        <div class="nav-avatar">AH</div>
        <a href="logout.php" class="btn btn-outline" style="font-size:0.82rem; padding:0.45rem 1rem;">
            <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
        </a>
    </div>
</nav>

<div class="marketplace-layout">
    <aside class="mkt-sidebar">
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-pen"></i></div>
                <div class="mkt-profile-name">Commande</div>
                <div class="mkt-profile-sub">Modification #<?= $orderId ?></div>
            </div>
        </div>

        <div class="mkt-sidebar-card">
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="home.php" class="nav-item">
                    <i class="fa-solid fa-store"></i> Tout parcourir
                </a>
                <a href="panier.php" class="nav-item">
                    <i class="fa-solid fa-cart-shopping"></i> Mon panier
                </a>
                <a href="mes_ventes.php" class="nav-item">
                    <i class="fa-solid fa-tag"></i> Mes ventes
                </a>
                <a href="mes_commandes.php" class="nav-item active">
                    <i class="fa-solid fa-receipt"></i> Mes commandes
                </a>
                <a href="notifications.php" class="nav-item">
                    <i class="fa-solid fa-bell"></i> Notifications
                    <?php if($unreadCount > 0): ?>
                        <span style="background:#ef4444; color:white; border-radius:50%; width:18px; height:18px; font-size:10px; display:flex; align-items:center; justify-content:center; margin-left:auto;"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="vendreproduit.php" class="nav-item">
                    <i class="fa-solid fa-plus-circle"></i> Vendre un produit
                </a>
            </div>
        </div>
    </aside>

    <div class="mkt-main" style="padding: 0;">

<div class="edit-container">
    <div class="edit-card">
        <h2 style="margin-bottom: 2rem;">Modifier la commande #<?= $orderId ?></h2>
        
        <form id="editOrderForm" method="POST">
            <div class="form-group">
                <label class="form-label">Adresse de livraison</label>
                <textarea name="adresse_livraison" class="form-input" rows="3"><?= htmlspecialchars($order['adresse_livraison']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Mode de paiement</label>
                <select name="mode_paiement" class="form-input">
                    <option value="Sur place" <?= ($order['mode_paiement'] ?? '') === 'Sur place' ? 'selected' : '' ?>>Sur place</option>
                    <option value="En ligne" <?= ($order['mode_paiement'] ?? '') === 'En ligne' ? 'selected' : '' ?>>En ligne</option>
                </select>
            </div>

            <h3 style="margin: 2rem 0 1rem; font-size: 1.1rem; color: #94a3b8;">Articles commandés</h3>
            <?php if (empty($products)): ?>
                <p style="color: #64748b; text-align: center; padding: 1rem;">Aucun article dans cette commande.</p>
            <?php endif; ?>
            
            <?php foreach ($products as $p): ?>
                <div class="prod-row">
                    <img src="<?= htmlspecialchars($p['image']) ?>" class="prod-img">
                    <div class="prod-info">
                        <div style="font-weight: 600;"><?= htmlspecialchars($p['nom']) ?></div>
                        <div style="font-size: 0.85rem; color: #10b981;"><?= number_format($p['prix_unitaire'], 0, ',', ' ') ?> DT</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div>
                            <label class="form-label" style="text-align: center;">Qté</label>
                            <input type="number" name="qty[<?= $p['idProduit'] ?>]" class="form-input qty-input" value="<?= $p['quantite'] ?>" min="1">
                        </div>
                        <button type="button" class="btn-del-item" title="Supprimer cet article" onclick="confirmDelete(<?= $p['idProduit'] ?>)">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="margin-top: 2.5rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn-cart" style="flex: 2; justify-content: center;">
                    <i class="fa-solid fa-save"></i> Enregistrer les modifications
                </button>
                <a href="mes_commandes.php" class="btn-cart" style="flex: 1; background: transparent; border: 1px solid rgba(255,255,255,0.1); justify-content: center;">Annuler</a>
            </div>
            
            <input type="hidden" name="delete_item_id" id="delete_item_id">
        </form>
    </div>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal-content">
        <div class="modal-title">Supprimer l'article ?</div>
        <div class="modal-text">Êtes-vous sûr de vouloir retirer cet article de votre commande ?</div>
        <div class="modal-btns">
            <button class="modal-btn btn-cancel" onclick="closeModal()">Annuler</button>
            <button class="modal-btn btn-confirm" onclick="executeDelete()">Supprimer</button>
        </div>
    </div>
</div>

<script>
let itemIdToDelete = null;
function confirmDelete(id) { itemIdToDelete = id; document.getElementById('deleteModal').style.display = 'flex'; }
function closeModal() { document.getElementById('deleteModal').style.display = 'none'; }
function executeDelete() { if (itemIdToDelete) { document.getElementById('delete_item_id').value = itemIdToDelete; document.getElementById('editOrderForm').submit(); } }
</script>
</body>
</html>
