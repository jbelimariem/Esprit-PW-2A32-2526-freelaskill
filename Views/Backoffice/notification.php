<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1;
}

require_once __DIR__ . '/../../controllers/produitController.php';
$productController = new ProduitController();

// Simulate reading notifications from database for the example.
// We'll show pending products as notifications since the user mentioned "un produit a ete ajoute il faut l'approuve"
$pendingProducts = $productController->getByStatutData('pending');
$unreadCount = count($pendingProducts) + 2; // For demonstration: pending products + 2 fake order notifications

// Dummy notifications for orders
$dummyNotifications = [
    [
        'type' => 'commande',
        'icon' => 'fa-solid fa-cart-shopping',
        'color' => '#10b981',
        'bg' => 'rgba(16, 185, 129, 0.1)',
        'title' => 'Nouvelle commande ajoutée',
        'description' => 'La commande #CMD-409 a été passée par utilisateur.',
        'time' => 'Il y a 2 heures',
        'action_link' => 'liste_commandes.php',
        'action_text' => 'Voir la commande'
    ],
    [
        'type' => 'commande',
        'icon' => 'fa-solid fa-cart-shopping',
        'color' => '#10b981',
        'bg' => 'rgba(16, 185, 129, 0.1)',
        'title' => 'Nouvelle commande ajoutée',
        'description' => 'La commande #CMD-408 a été passée par utilisateur.',
        'time' => 'Il y a 5 heures',
        'action_link' => 'liste_commandes.php',
        'action_text' => 'Voir la commande'
    ]
];
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
                    <span style="margin-left:auto; background:#ef4444; color:white; font-size:0.7rem; font-weight:bold; padding:2px 6px; border-radius:10px;"><?= $unreadCount ?></span>
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
                        <span class="badge-dot" style="display:flex; align-items:center; justify-content:center; width:16px; height:16px; border-radius:50%; font-size:10px; font-weight:bold; top:-4px; right:-4px;"><?= $unreadCount ?></span>
                    </a>
                    <div class="nav-avatar" style="margin-left: 0.5rem;">AH</div>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-header-row">
                    <h1 class="admin-page-title">Notifications système</h1>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <button class="admin-btn-outline" style="background: rgba(255,255,255,0.03);">
                            <i class="fa-solid fa-check-double"></i> Tout marquer comme lu
                        </button>
                    </div>
                </div>

                <div class="glass-card" style="padding: 2rem;">
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        
                        <!-- Notifications for pending products -->
                        <?php foreach($pendingProducts as $prod): ?>
                        <div style="display: flex; gap: 1.5rem; padding: 1.5rem; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 1rem; align-items: flex-start; transition: background 0.2s;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-size: 1.1rem; color: white; margin-bottom: 0.4rem;">Nouveau produit en attente</h3>
                                <p style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 1rem; line-height: 1.5;">Le produit <strong style="color: white;"><?= htmlspecialchars($prod['nom']) ?></strong> a été ajouté par un vendeur et nécessite votre approbation pour apparaître dans le catalogue.</p>
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <a href="pending_products.php" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 600;"><i class="fa-solid fa-eye" style="margin-right: 0.4rem;"></i> Voir et approuver</a>
                                    <span style="color: #64748b; font-size: 0.8rem;"><i class="fa-regular fa-clock"></i> Récemment</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Dummy notifications for orders -->
                        <?php foreach($dummyNotifications as $notif): ?>
                        <div style="display: flex; gap: 1.5rem; padding: 1.5rem; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 1rem; align-items: flex-start; transition: background 0.2s;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: <?= $notif['bg'] ?>; color: <?= $notif['color'] ?>; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                                <i class="<?= $notif['icon'] ?>"></i>
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-size: 1.1rem; color: white; margin-bottom: 0.4rem;"><?= $notif['title'] ?></h3>
                                <p style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 1rem; line-height: 1.5;"><?= $notif['description'] ?></p>
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <a href="<?= $notif['action_link'] ?>" style="background: rgba(255,255,255,0.1); color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 600; border: 1px solid rgba(255,255,255,0.1);"><i class="fa-solid fa-arrow-right" style="margin-right: 0.4rem;"></i> <?= $notif['action_text'] ?></a>
                                    <span style="color: #64748b; font-size: 0.8rem;"><i class="fa-regular fa-clock"></i> <?= $notif['time'] ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if(empty($pendingProducts) && empty($dummyNotifications)): ?>
                        <div style="text-align: center; padding: 4rem 2rem; opacity: 0.7;">
                            <i class="fa-regular fa-bell-slash" style="font-size: 3rem; color: #475569; margin-bottom: 1rem;"></i>
                            <h3 style="color: white; font-size: 1.2rem; margin-bottom: 0.5rem;">Aucune notification</h3>
                            <p style="color: #94a3b8;">Vous êtes à jour. Tous les événements récents ont été traités.</p>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js.js"></script>
</body>
</html>
