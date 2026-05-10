<?php
require_once __DIR__ . '/../../controllers/NotificationController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$notifController = new NotificationController();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['mark_read'])) {
    $notifController->markAsRead((int)$_GET['mark_read'], $user_id);
    header('Location: notifications.php');
    exit();
}

if (isset($_GET['mark_all_read'])) {
    $notifController->markAllAsRead($user_id);
    header('Location: notifications.php');
    exit();
}

$unreadCount = $notifController->getUnreadCount($user_id);

// Pagination logic
$itemsPerPage = 6;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

$totalItems = $notifController->getTotalCount($user_id);
$totalPages = ceil($totalItems / $itemsPerPage);
if ($currentPage > $totalPages && $totalPages > 0) $currentPage = $totalPages;

$offset = ($currentPage - 1) * $itemsPerPage;
$notifications = $notifController->getByUserPaginated($user_id, $itemsPerPage, $offset);
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=6">
    <style>
        .notif-container { max-width: 700px; margin: 2rem auto; padding: 0 1rem; }
        .notif-item {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 1rem;
            padding: 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            gap: 1.25rem;
            transition: 0.3s;
            position: relative;
        }
        .notif-item.unread {
            background: rgba(59,130,246,0.05);
            border-color: rgba(59,130,246,0.2);
        }
        .notif-item.unread::before {
            content: '';
            position: absolute;
            left: 0; top: 1.25rem; bottom: 1.25rem;
            width: 4px; background: #3b82f6;
            border-radius: 0 4px 4px 0;
        }
        .notif-icon {
            width: 48px; height: 48px; border-radius: 50%;
            background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0;
        }
        .unread .notif-icon { background: rgba(59,130,246,0.1); color: #3b82f6; }
        .notif-content { flex: 1; }
        .notif-msg { color: #e2e8f0; font-size: 0.95rem; line-height: 1.5; margin-bottom: 0.25rem; }
        .notif-date { color: #64748b; font-size: 0.8rem; }
        .notif-actions { display: flex; align-items: center; }
        .mark-read-btn {
            color: #3b82f6; font-size: 0.85rem; font-weight: 600;
            text-decoration: none; opacity: 0.8; transition: 0.2s;
        }
        .mark-read-btn:hover { opacity: 1; }
    </style>
</head>
<body class="page-anim home-page">

<nav>
    <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
    <div class="nav-right">
        <a href="home.php" class="cart-btn"><i class="fa-solid fa-arrow-left"></i> Retour</a>
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
                <div class="mkt-avatar"><i class="fa-solid fa-bell"></i></div>
                <div class="mkt-profile-name">Notifications</div>
                <div class="mkt-profile-sub">Toutes vos alertes</div>
            </div>
            <div class="mkt-profile-stats">
                <div class="mkt-stat">
                    <div class="mkt-stat-val"><?= $unreadCount ?></div>
                    <div class="mkt-stat-label">Non lues</div>
                </div>
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
                <a href="mes_commandes.php" class="nav-item">
                    <i class="fa-solid fa-receipt"></i> Mes commandes
                </a>
                <a href="notifications.php" class="nav-item active">
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

    <div class="mkt-main">
        <div class="notif-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.75rem;">Notifications</h1>
                <?php if($unreadCount > 0): ?>
                    <a href="?mark_all_read=1" class="mark-read-btn">Tout marquer comme lu</a>
                <?php endif; ?>
            </div>

            <?php if (empty($notifications)): ?>
                <div class="glass-card" style="text-align: center; padding: 5rem 2rem; border: 1px dashed rgba(255,255,255,0.1); background: rgba(255,255,255,0.02);">
                    <div style="width: 80px; height: 80px; background: rgba(59,130,246,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                        <i class="fa-regular fa-bell" style="font-size: 2.5rem; color: var(--tech-blue);"></i>
                    </div>
                    <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: white;">Aucune notification</h2>
                    <p style="color: #94a3b8; max-width: 400px; margin: 0 auto; line-height: 1.6;">Vous êtes à jour. Tous les événements récents ont été traités.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                        <div class="notif-icon">
                            <i class="fa-solid <?= $n['type'] === 'order_update' ? 'fa-box-open' : 'fa-bell' ?>"></i>
                        </div>
                        <div class="notif-content">
                            <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
                            <div class="notif-date"><?= date('d/m/Y à H:i', strtotime($n['date_notif'])) ?></div>
                        </div>
                        <div class="notif-actions">
                            <?php if (!$n['is_read']): ?>
                                <a href="?mark_read=<?= $n['idNotification'] ?>" class="mark-read-btn" title="Marquer comme lu">
                                    <i class="fa-solid fa-check"></i> Marquer comme lu
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination" style="margin-top: 2rem; display: flex; justify-content: center; gap: 0.5rem;">
                    <a href="?page=<?= max(1, $currentPage - 1) ?>" class="pag-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>" style="text-decoration:none; padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border-radius: 8px; color: white;">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="pag-btn <?= $i === $currentPage ? 'active' : '' ?>" style="text-decoration:none; padding: 0.5rem 1rem; border-radius: 8px; <?= $i === $currentPage ? 'background: #3b82f6; color: white;' : 'background: rgba(255,255,255,0.05); color: #94a3b8;' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <a href="?page=<?= min($totalPages, $currentPage + 1) ?>" class="pag-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" style="text-decoration:none; padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border-radius: 8px; color: white;">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../assets/js.js?v=6"></script>
</body>
</html>
