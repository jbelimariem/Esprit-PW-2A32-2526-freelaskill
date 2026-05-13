<?php
require_once __DIR__ . '/../../controllers/commandeController.php';
require_once __DIR__ . '/../../controllers/CommandeProduitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$user = null;
$hasAvatar = false;
$avatarUrl = '';
$initials = '';

if (!empty($_SESSION['user_id'])) {
    $userController = new UserController();
    $user = $userController->getById((int)$_SESSION['user_id']);
    if ($user) {
        $initials = strtoupper(mb_substr($user->getPrenom(), 0, 1) . mb_substr($user->getNom(), 0, 1));
        $avatar = trim((string)$user->getAvatar());
        if ($avatar !== '') {
            if (strpos($avatar, 'http') === 0) {
                $avatarUrl = $avatar;
            } else {
                $avatarUrl = '../../' . ltrim(str_replace('\\', '/', $avatar), '/');
            }
            $hasAvatar = true;
        }
    }
}

$notifController = new NotificationController();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php');
    exit;
}
$unreadCount = $notifController->getUnreadCount($user_id);

$commandeController = new CommandeController();
$cpController = new CommandeProduitController();
$categoryController = new Category_prodController();

// Traitement de la suppression
if (isset($_GET['delete_id'])) {
    $targetOrder = $commandeController->getByIdData((int)$_GET['delete_id']);
    if ($targetOrder && (int)($targetOrder['user_id'] ?? 0) === (int)$user_id) {
        $commandeController->deleteData((int)$_GET['delete_id']);
    }
    header('Location: mes_commandes.php');
    exit;
}

$totalCommandeCount = $commandeController->getTotalCountByUser($user_id);

// Pagination logic
$itemsPerPage = 4;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

$totalPages = ceil($totalCommandeCount / $itemsPerPage);
if ($currentPage > $totalPages && $totalPages > 0) $currentPage = $totalPages;

$offset = ($currentPage - 1) * $itemsPerPage;
$commandes = $commandeController->getByUserPaginated($user_id, $itemsPerPage, $offset);
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes — FreelaSkill</title>
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=6">
    <script src="../assets/theme.js" defer></script>
    <style>
        .order-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 1.25rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: 0.3s;
        }
        .order-card:hover {
            border-color: rgba(59,130,246,0.3);
            background: rgba(255,255,255,0.05);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 1rem;
        }
        .order-id { font-weight: 700; font-size: 1.1rem; color: #fff; }
        .order-date { color: #94a3b8; font-size: 0.85rem; }
        .order-status {
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-en_attente { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .status-livre { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .order-body { display: block; margin-bottom: 1rem; }
        .order-info { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 1.5rem; 
            width: 100%;
        }
        .info-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
        .info-val { color: #e2e8f0; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .mini-product {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
            background: rgba(0,0,0,0.2);
            padding: 0.5rem;
            border-radius: 0.75rem;
        }
        .mini-img { width: 40px; height: 40px; border-radius: 0.5rem; object-fit: cover; }
        .mini-name { font-size: 0.85rem; color: #f1f5f9; flex: 1; }
        .mini-qty { font-size: 0.85rem; color: #94a3b8; }
        .order-actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .btn-action {
            flex: 1;
            padding: 0.6rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
            border: none;
        }
        .btn-edit { background: rgba(59,130,246,0.1); color: #3b82f6; }
        .btn-edit:hover { background: rgba(59,130,246,0.2); }
        .btn-delete { background: rgba(239,68,68,0.1); color: #ef4444; }
        .btn-delete:hover { background: rgba(239,68,68,0.2); }
        .btn-view { background: rgba(16,185,129,0.1); color: #10b981; }
        .btn-view:hover { background: rgba(16,185,129,0.18); }
        .order-detail-panel { display:none; margin-top:1rem; padding:1rem; border-radius:1rem; background:rgba(0,0,0,.18); border:1px solid rgba(255,255,255,.06); }
        .order-detail-panel.open { display:block; }
        .detail-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
        @media (max-width: 900px) { .order-body, .detail-grid { flex-direction:column; grid-template-columns:1fr; } }
    </style>
</head>
<body class="page-anim home-page">

<!-- NAVBAR -->
<nav>
    <div class="logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </div>
    <ul class="nav-links">
        <li><a href="missions.php">Client</a></li>
        <li><a href="freelancer_home.php">Freelancer</a></li>
        <li><a href="home.php" class="active">Marketplace</a></li>
        <li><a href="profile.php">Mon Profil</a></li>
    </ul>
    <div class="nav-right">
        <button type="button" class="theme-toggle" data-theme-toggle>
            <i class="fa-solid fa-sun" data-theme-icon></i>
            <span data-theme-label>Jour</span>
        </button>
        
        <a href="notifications.php" class="cart-btn" style="position: relative; margin-right: 10px; color: var(--text-muted);">
            <i class="fa-solid fa-bell"></i>
            <?php if($unreadCount > 0): ?>
                <span class="cart-count" style="position:absolute;top:-6px;right:-6px;background:#3b82f6;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a href="panier.php" class="cart-btn" style="position: relative; margin-right: 15px; color: var(--text-muted);">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="cart-count" style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);">0</span>
        </a>

        <?php if ($user): ?>
            <div class="nav-avatar<?php echo $hasAvatar ? ' has-image' : ''; ?>" title="<?php echo htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()); ?>">
                <?php if ($hasAvatar): ?>
                    <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Photo de profil" class="nav-avatar-image">
                <?php else: ?>
                    <?php echo $initials; ?>
                <?php endif; ?>
            </div>
            <a href="logout.php" class="btn btn-outline" style="font-size:0.82rem; padding:0.45rem 1rem; margin-left: 10px;" title="Déconnexion">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary" style="font-size:0.82rem; padding:0.45rem 1rem;">
                Connexion
            </a>
        <?php endif; ?>
    </div>
</nav>

<!-- MARKETPLACE LAYOUT -->
<div class="marketplace-layout">

    <!-- ── SIDEBAR ── -->
    <aside class="mkt-sidebar">
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-store"></i></div>
                <div class="mkt-profile-name">Marketplace</div>
                <div class="mkt-profile-sub">Mes Commandes</div>
            </div>
            <div class="mkt-profile-stats">
                <div class="mkt-stat">
                    <div class="mkt-stat-val"><?= $totalCommandeCount ?></div>
                    <div class="mkt-stat-label">Commandes</div>
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

    <!-- MAIN -->
    <div class="mkt-main">
        <section class="hero-banner" style="padding: 2rem;">
            <div class="hero-glow"></div>
            <div class="hero-content" style="text-align: center;">
                <div class="hero-tag"><i class="fa-solid fa-receipt"></i> Historique</div>
                <h1 class="hero-title">Mes <span>Commandes</span></h1>
                <p class="hero-sub">Suivez vos achats et modifiez vos commandes en attente.</p>
            </div>
        </section>

        <div class="orders-list" style="margin-top: 2rem;">
            <?php if (empty($commandes)): ?>
                <div class="order-card" style="text-align: center; padding: 4rem;">
                    <i class="fa-solid fa-box-open" style="font-size: 3rem; color: #475569; margin-bottom: 1.5rem;"></i>
                    <h3>Vous n'avez pas encore passé de commande</h3>
                    <a href="home.php" class="btn-cart" style="margin-top: 1.5rem; display: inline-flex;">Parcourir la boutique</a>
                </div>
            <?php else: ?>
                <?php foreach ($commandes as $cmd): 
                    $products = $cpController->getByCommandeData($cmd['idCommande']);
                ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Commande #<?= $cmd['idCommande'] ?></div>
                                <div class="order-date">Passée le <?= date('d/m/Y', strtotime($cmd['date_commande'])) ?></div>
                            </div>
                            <div class="order-status status-<?= $cmd['statut'] ?>">
                                <?= str_replace('_', ' ', $cmd['statut']) ?>
                            </div>
                        </div>
                        <div class="order-body">
                            <div class="order-info">
                                <div>
                                    <div class="info-label">Adresse de livraison</div>
                                    <div class="info-val"><?= htmlspecialchars($cmd['adresse_livraison']) ?></div>
                                </div>
                                
                                <div>
                                    <div class="info-label">Mode de paiement</div>
                                    <div class="info-val"><?= htmlspecialchars($cmd['mode_paiement'] ?? 'Non spécifié') ?></div>
                                </div>
                                
                                <div>
                                    <div class="info-label">Total de la commande</div>
                                    <div class="info-val" style="font-weight: 700; color: #10b981; font-size: 1.1rem;"><?= number_format($cmd['montant_total'], 0, ',', ' ') ?> DT</div>
                                </div>
                            </div>
                        </div>
                        <div class="order-actions">
                            <button type="button" class="btn-action btn-view" onclick="toggleOrderDetail(<?= $cmd['idCommande'] ?>)">
                                <i class="fa-solid fa-eye"></i> Voir détail
                            </button>
                            <a href="modifier_commande.php?id=<?= $cmd['idCommande'] ?>" class="btn-action btn-edit">
                                <i class="fa-solid fa-pen"></i> Modifier
                            </a>
                            <button type="button" class="btn-action btn-delete" onclick="openDeleteModal(<?= $cmd['idCommande'] ?>)">
                                <i class="fa-solid fa-trash"></i> Supprimer
                            </button>
                        </div>
                        <div class="order-detail-panel" id="order-detail-<?= $cmd['idCommande'] ?>">
                            <div class="detail-grid">
                                <div>
                                    <div class="info-label">Statut</div>
                                    <div class="info-val"><?= htmlspecialchars(str_replace('_', ' ', $cmd['statut'])) ?></div>
                                </div>
                                <div>
                                    <div class="info-label">Mode paiement</div>
                                    <div class="info-val"><?= htmlspecialchars($cmd['mode_paiement'] ?? 'Non spécifié') ?></div>
                                </div>
                                <div>
                                    <div class="info-label">Mode livraison</div>
                                    <div class="info-val"><?= htmlspecialchars($cmd['mode_livraison'] ?? 'Standard') ?></div>
                                </div>
                            </div>
                            <div class="info-label" style="margin-top:1rem;">Produits commandés</div>
                            <?php foreach ($products as $p): ?>
                                <div class="mini-product">
                                    <img src="<?= htmlspecialchars($p['image']) ?>" class="mini-img" onerror="this.src='../assets/uploads/placeholder.png'">
                                    <div class="mini-name"><?= htmlspecialchars($p['nom']) ?></div>
                                    <div class="mini-qty">x<?= (int)$p['quantite'] ?> · <?= number_format((float)$p['prix_unitaire'], 0, ',', ' ') ?> DT</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination" style="margin-top: 3rem; display: flex; justify-content: center; gap: 0.5rem;">
                    <a href="?page=<?= max(1, $currentPage - 1) ?>" class="pag-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>" style="text-decoration:none; padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.05); border-radius: 10px; color: white; transition: 0.2s;">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="pag-btn <?= $i === $currentPage ? 'active' : '' ?>" style="text-decoration:none; padding: 0.6rem 1.2rem; border-radius: 10px; transition: 0.2s; <?= $i === $currentPage ? 'background: var(--tech-blue); color: white; box-shadow: 0 4px 15px rgba(59,130,246,0.3);' : 'background: rgba(255,255,255,0.05); color: #94a3b8;' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <a href="?page=<?= min($totalPages, $currentPage + 1) ?>" class="pag-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" style="text-decoration:none; padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.05); border-radius: 10px; color: white; transition: 0.2s;">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="deleteModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
   <div style="background:var(--bg-dark); padding:2rem; border-radius:1.5rem; border:1px solid var(--border); width:90%; max-width:380px; text-align:center;">
       <h3 style="margin-bottom:1rem;">Annuler la commande ?</h3>
       <p style="color:var(--text-muted); margin-bottom:2rem;">Cette action est irréversible.</p>
       <div style="display:flex; gap:1rem;">
           <button onclick="closeDeleteModal()" style="flex:1; padding:0.8rem; border-radius:0.5rem; border:none; cursor:pointer;">Annuler</button>
           <a id="confirmDeleteBtn" href="#" style="flex:1; padding:0.8rem; background:#ef4444; color:white; border-radius:0.5rem; text-decoration:none;">Confirmer</a>
       </div>
   </div>
</div>

<script>
function openDeleteModal(id) {
    document.getElementById('deleteModal').style.display = 'flex';
    document.getElementById('confirmDeleteBtn').href = '?delete_id=' + id;
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}
function toggleOrderDetail(id) {
    var panel = document.getElementById('order-detail-' + id);
    if (panel) panel.classList.toggle('open');
}
</script>
<script src="../assets/js.js?v=6"></script>
</body>
</html>
