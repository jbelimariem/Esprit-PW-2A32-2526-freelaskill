<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1;
}

require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/commandeController.php';
$productController  = new ProduitController();
$commandeController = new CommandeController();

// ── Charger les données ──────────────────────────────────────────────
$pendingProducts = $productController->getByStatutData('pending');
$allCommandes    = $commandeController->getAllData();

// Construire la liste unifiée des notifications (chacune a un ID unique)
$allNotifications = [];
foreach ($pendingProducts as $prod) {
    $allNotifications[] = [
        'id'          => 'prod_' . $prod['idProduit'],
        'type'        => 'produit',
        'icon'        => 'fa-solid fa-clock',
        'color'       => '#f59e0b',
        'bg'          => 'rgba(245,158,11,0.1)',
        'title'       => 'Nouveau produit en attente',
        'description' => 'Le produit <strong style="color:white;">' . htmlspecialchars($prod['nom']) . '</strong> a été ajouté par un vendeur et nécessite votre approbation.',
        'action_link' => 'pending_products.php',
        'action_text' => 'Voir et approuver',
    ];
}
foreach ($allCommandes as $cmd) {
    $allNotifications[] = [
        'id'          => 'cmd_' . $cmd['idCommande'],
        'type'        => 'commande',
        'icon'        => 'fa-solid fa-cart-shopping',
        'color'       => '#10b981',
        'bg'          => 'rgba(16,185,129,0.1)',
        'title'       => 'Nouvelle commande',
        'description' => 'La commande <strong style="color:white;">#CMD-' . str_pad($cmd['idCommande'], 3, '0', STR_PAD_LEFT) . '</strong> a été passée.',
        'action_link' => 'liste_commandes.php',
        'action_text' => 'Voir la commande',
    ];
}

// ── Gérer l'état "lus" via la session ───────────────────────────────
if (!isset($_SESSION['read_notifs'])) {
    $_SESSION['read_notifs'] = [];
}

// Action : marquer tout comme lu
if (isset($_GET['mark_all_read'])) {
    $_SESSION['read_notifs'] = array_column($allNotifications, 'id');
    header('Location: notification.php');
    exit;
}

// Action : marquer une seule notif comme lue
if (isset($_GET['mark_read'])) {
    $id = $_GET['mark_read'];
    if (!in_array($id, $_SESSION['read_notifs'])) {
        $_SESSION['read_notifs'][] = $id;
    }
    // Rediriger vers la page d'action si spécifiée
    $redirect = $_GET['redirect'] ?? 'notification.php';
    header('Location: ' . $redirect);
    exit;
}

// Calculer le nombre de notifications non lues
$unreadCount = 0;
foreach ($allNotifications as $n) {
    if (!in_array($n['id'], $_SESSION['read_notifs'])) {
        $unreadCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="page-anim">
    <div class="hero-glow" style="z-index: 0; position: fixed;"></div>
    <div class="hero-glow-2" style="z-index: 0; position: fixed; left: 20%; bottom: -150px; top: auto;"></div>

    <div class="admin-layout" style="position: relative; z-index: 1;">
        <aside class="admin-sidebar">
            <div class="logo">
                <i class="fa-solid fa-shapes"></i>
                Freela<span>Skill</span>
            </div>

            <div class="admin-nav">
                <a href="./dashboard.php" class="admin-nav-item">
                    <i class="fa-solid fa-house"></i> Dashboard
                </a>
                <div style="margin: 1rem 0 0.5rem; font-size: 0.75rem; text-transform: uppercase; color: #475569; padding-left: 1rem; font-weight: 700; letter-spacing: 1px;">
                    Marketplace
                </div>
                <a href="ajouter_produit.php" class="admin-nav-item">
                    <i class="fa-solid fa-plus"></i> Ajouter Produit
                </a>
                <a href="produits.php" class="admin-nav-item">
                    <i class="fa-solid fa-list"></i> Liste des Produits
                </a>
                <a href="./pending_products.php" class="admin-nav-item">
                    <i class="fa-solid fa-clock"></i> Validation produits
                </a>
                <a href="./ajouter_categorie.php" class="admin-nav-item">
                    <i class="fa-solid fa-plus"></i> Ajouter Catégorie
                </a>
                <a href="./liste_categories.php" class="admin-nav-item">
                    <i class="fa-solid fa-list"></i> Liste des Catégories
                </a>
                <a href="./mes_achats.php" class="admin-nav-item">
                    <i class="fa-solid fa-bag-shopping"></i> Mes Achats
                </a>
                <a href="./liste_commandes.php" class="admin-nav-item">
                    <i class="fa-solid fa-cart-shopping"></i> Commandes
                </a>
                <a href="./notification.php" class="admin-nav-item active">
                    <i class="fa-solid fa-bell"></i> Notifications
                    <?php if ($unreadCount > 0): ?>
                        <span id="sidebar-badge" style="margin-left:auto; background:#ef4444; color:white; font-size:0.7rem; font-weight:bold; padding:2px 7px; border-radius:10px;"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div style="padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.08);">
                <a href="../Frontoffice/home.php" class="admin-nav-item" style="color: #ef4444; padding: 0.75rem;">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Retour au Hub
                </a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="admin-search">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" placeholder="Rechercher dans les notifications">
                </div>
                <div class="admin-top-actions">
                    <div class="admin-icon-btn theme-toggle-btn" style="cursor:pointer;" title="Basculer thème">
                        <i class="fa-regular fa-moon"></i>
                    </div>
                    <a href="notification.php" class="admin-icon-btn" style="text-decoration:none; position:relative; background: rgba(59,130,246,0.1); border-color: rgba(59,130,246,0.3); color: #3b82f6;">
                        <i class="fa-regular fa-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                        <span id="header-badge" class="badge-dot" style="display:flex; align-items:center; justify-content:center; width:16px; height:16px; border-radius:50%; font-size:10px; font-weight:bold; top:-4px; right:-4px;"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="nav-avatar" style="margin-left: 0.5rem;">AH</div>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-header-row">
                    <h1 class="admin-page-title">
                        Notifications système
                        <?php if ($unreadCount > 0): ?>
                            <span style="font-size:1rem; font-weight:600; color:#ef4444; margin-left:0.75rem;"><?= $unreadCount ?> non lue<?= $unreadCount > 1 ? 's' : '' ?></span>
                        <?php else: ?>
                            <span style="font-size:1rem; font-weight:500; color:#10b981; margin-left:0.75rem;">✓ Tout lu</span>
                        <?php endif; ?>
                    </h1>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <?php if ($unreadCount > 0): ?>
                        <a href="notification.php?mark_all_read=1" class="admin-btn-outline" style="background: rgba(255,255,255,0.03); text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem; padding:0.6rem 1.2rem; border-radius:0.5rem; color:white; border:1px solid rgba(255,255,255,0.1); font-size:0.9rem; cursor:pointer;">
                            <i class="fa-solid fa-check-double"></i> Tout marquer comme lu
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="glass-card" style="padding: 2rem;">
                    <div style="display: flex; flex-direction: column; gap: 1rem;">

                        <?php if (empty($allNotifications)): ?>
                        <div style="text-align: center; padding: 4rem 2rem; opacity: 0.7;">
                            <i class="fa-regular fa-bell-slash" style="font-size: 3rem; color: #475569; margin-bottom: 1rem; display:block;"></i>
                            <h3 style="color: white; font-size: 1.2rem; margin-bottom: 0.5rem;">Aucune notification</h3>
                            <p style="color: #94a3b8;">Vous êtes à jour. Tous les événements récents ont été traités.</p>
                        </div>
                        <?php else: ?>

                        <?php foreach ($allNotifications as $notif):
                            $isRead = in_array($notif['id'], $_SESSION['read_notifs']);
                            $rowStyle = $isRead
                                ? 'opacity:0.45; background:rgba(255,255,255,0.01);'
                                : 'background:rgba(255,255,255,0.03); border-color:rgba(255,255,255,0.09);';
                        ?>
                        <div style="display:flex; gap:1.5rem; padding:1.5rem; <?= $rowStyle ?> border:1px solid rgba(255,255,255,0.05); border-radius:1rem; align-items:flex-start; transition:background 0.2s, opacity 0.2s; position:relative;">

                            <?php if (!$isRead): ?>
                            <span style="position:absolute; top:1rem; right:1rem; width:8px; height:8px; border-radius:50%; background:#ef4444; box-shadow:0 0 6px #ef4444;"></span>
                            <?php endif; ?>

                            <div style="width:48px; height:48px; border-radius:50%; background:<?= $notif['bg'] ?>; color:<?= $notif['color'] ?>; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
                                <i class="<?= $notif['icon'] ?>"></i>
                            </div>
                            <div style="flex:1;">
                                <h3 style="font-size:1.1rem; color:white; margin-bottom:0.4rem;"><?= $notif['title'] ?></h3>
                                <p style="color:#94a3b8; font-size:0.95rem; margin-bottom:1rem; line-height:1.5;"><?= $notif['description'] ?></p>
                                <div style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
                                    <?php if (!$isRead): ?>
                                    <a href="notification.php?mark_read=<?= urlencode($notif['id']) ?>&redirect=<?= urlencode($notif['action_link']) ?>"
                                       style="background:<?= $notif['type'] === 'produit' ? '#3b82f6' : 'rgba(255,255,255,0.1)' ?>; color:white; padding:0.5rem 1rem; border-radius:0.5rem; text-decoration:none; font-size:0.85rem; font-weight:600; border:1px solid rgba(255,255,255,0.1);">
                                        <i class="fa-solid fa-<?= $notif['type'] === 'produit' ? 'eye' : 'arrow-right' ?>" style="margin-right:0.4rem;"></i>
                                        <?= $notif['action_text'] ?>
                                    </a>
                                    <a href="notification.php?mark_read=<?= urlencode($notif['id']) ?>"
                                       style="color:#64748b; font-size:0.82rem; text-decoration:none; padding:0.4rem 0.8rem; border-radius:0.4rem; border:1px solid rgba(255,255,255,0.06);">
                                        <i class="fa-solid fa-check"></i> Marquer comme lu
                                    </a>
                                    <?php else: ?>
                                    <span style="color:#475569; font-size:0.82rem;"><i class="fa-solid fa-check-double"></i> Lu</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js.js"></script>
</body>
</html>
