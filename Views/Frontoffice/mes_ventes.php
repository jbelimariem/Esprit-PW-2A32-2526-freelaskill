<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = null;
$hasAvatar = false;
$avatarUrl = '';
$initials = '';

if (!empty($_SESSION['user_id'])) {
    $userController = new UserController();
    $user = $userController->getById((int) $_SESSION['user_id']);
    if ($user) {
        $initials = strtoupper(mb_substr($user->getPrenom(), 0, 1) . mb_substr($user->getNom(), 0, 1));
        $avatar = trim((string) $user->getAvatar());
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
$sellerId = $_SESSION['user_id'] ?? null;
if (!$sellerId) {
    header('Location: login.php');
    exit;
}
$unreadCount = $notifController->getUnreadCount($sellerId);

$produitController = new ProduitController();
$categoryController = new Category_prodController();
$produits = $produitController->getAllAdminData($sellerId);
$categories = $categoryController->getAllData();
$categoryCounts = [];
foreach ($produits as $produit) {
    $categoryCounts[$produit['category_id']] = ($categoryCounts[$produit['category_id']] ?? 0) + 1;
}
$categoryNames = [];
foreach ($categories as $category) {
    $categoryNames[$category['idCategory']] = $category['nom'];
}

if (isset($_GET['delete_id'])) {
    $productToDelete = $produitController->getByIdData((int)$_GET['delete_id']);
    if ($productToDelete && (int)($productToDelete['user_id'] ?? 0) === (int)$sellerId) {
        $produitController->deleteData((int)$_GET['delete_id']);
    }
    header('Location: mes_ventes.php');
    exit();
}
$totalProduitCount = count($produits);

// Pagination
$itemsPerPage = 12;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalPages = ceil($totalProduitCount / $itemsPerPage);
$currentPage = min($currentPage, $totalPages > 0 ? $totalPages : 1);
$startIndex = ($currentPage - 1) * $itemsPerPage;
$produitsPagines = array_slice($produits, $startIndex, $itemsPerPage);

$pdo = config::getConnexion();
$sellerStats = [
    'revenue' => 0,
    'pending_orders' => 0,
    'sold_units' => 0,
    'top_products' => []
];
try {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(cp.quantite * cp.prix_unitaire), 0) AS revenue, COALESCE(SUM(cp.quantite), 0) AS sold_units
                           FROM commande_produit cp
                           JOIN produit p ON p.idProduit = cp.idProduit
                           WHERE p.user_id = ?");
    $stmt->execute([$sellerId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sellerStats['revenue'] = (float) ($row['revenue'] ?? 0);
    $sellerStats['sold_units'] = (int) ($row['sold_units'] ?? 0);

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT c.idCommande)
                           FROM commande c
                           JOIN commande_produit cp ON cp.idCommande = c.idCommande
                           JOIN produit p ON p.idProduit = cp.idProduit
                           WHERE p.user_id = ? AND c.statut = 'en_attente'");
    $stmt->execute([$sellerId]);
    $sellerStats['pending_orders'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT p.nom, COALESCE(SUM(cp.quantite), 0) AS qty, COALESCE(SUM(cp.quantite * cp.prix_unitaire), 0) AS total
                           FROM produit p
                           LEFT JOIN commande_produit cp ON cp.idProduit = p.idProduit
                           WHERE p.user_id = ?
                           GROUP BY p.idProduit, p.nom
                           ORDER BY qty DESC, total DESC
                           LIMIT 3");
    $stmt->execute([$sellerId]);
    $sellerStats['top_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace — FreelaSkill</title>
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=6">
    <script src="../assets/theme.js" defer></script>
    <style>
        .seller-dashboard {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .seller-kpi {
            background: rgba(255, 255, 255, .03);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 1rem;
            padding: 1rem;
        }

        .seller-kpi span {
            color: #94a3b8;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .seller-kpi strong {
            display: block;
            color: white;
            font-size: 1.35rem;
            margin-top: .35rem;
        }

        .top-list {
            display: flex;
            flex-direction: column;
            gap: .35rem;
            margin-top: .5rem;
        }

        .top-list div {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            color: #cbd5e1;
            font-size: .86rem;
        }

        @media(max-width:900px) {
            .seller-dashboard {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media(max-width:560px) {
            .seller-dashboard {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="page-anim home-page" data-page="mes-ventes">

    <!-- NAVBAR -->
    <nav>
        <div class="logo">
            <i class="fa-solid fa-shapes"></i>
            Freela<span>Skill</span>
        </div>
        <ul class="nav-links">
            <li><a href="#">Accueil</a></li>
            <li><a href="#">Missions</a></li>
            <li><a href="home.php" class="active">Marketplace</a></li>
            <li><a href="#">Freelancers</a></li>
            <li><a href="profile.php">Mon Profil</a></li>
        </ul>
        <div class="nav-right">
            <button type="button" class="theme-toggle" data-theme-toggle>
                <i class="fa-solid fa-sun" data-theme-icon></i>
                <span data-theme-label>Jour</span>
            </button>

            <a href="notifications.php" class="cart-btn"
                style="position: relative; margin-right: 10px; color: var(--text-muted);">
                <i class="fa-solid fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="cart-count"
                        style="position:absolute;top:-6px;right:-6px;background:#3b82f6;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>
            <a href="panier.php" class="cart-btn"
                style="position: relative; margin-right: 15px; color: var(--text-muted);">
                <i class="fa-solid fa-bag-shopping"></i>
                <span class="cart-count"
                    style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);">0</span>
            </a>

            <?php if ($user): ?>
                <div class="nav-avatar<?php echo $hasAvatar ? ' has-image' : ''; ?>"
                    title="<?php echo htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()); ?>">
                    <?php if ($hasAvatar): ?>
                        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Photo de profil" class="nav-avatar-image">
                    <?php else: ?>
                        <?php echo $initials; ?>
                    <?php endif; ?>
                </div>
                <a href="logout.php" class="btn btn-outline"
                    style="font-size:0.82rem; padding:0.45rem 1rem; margin-left: 10px;" title="Déconnexion">
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

            <!-- Card 1 : Profil marketplace -->
            <div class="mkt-profile-card">
                <div class="mkt-profile-header">
                    <div class="mkt-avatar"><i class="fa-solid fa-store"></i></div>
                    <div class="mkt-profile-name">Marketplace</div>
                    <div class="mkt-profile-sub">FreelaSkill Tunisia</div>
                </div>
                <div class="mkt-profile-stats">
                    <div class="mkt-stat">
                        <div class="mkt-stat-val"><?= $totalProduitCount ?></div>
                        <div class="mkt-stat-label">Produits</div>
                    </div>
                    <div class="mkt-stat">
                        <div class="mkt-stat-val"><?= count($categories) ?></div>
                        <div class="mkt-stat-label">Catégories</div>
                    </div>
                </div>
            </div>

            <!-- Card 2 : Navigation + Filtres -->
            <div class="mkt-sidebar-card">

                <!-- Navigation -->
                <div class="mkt-sidebar-section">
                    <div class="mkt-nav-label">Navigation</div>
                    <a href="home.php" class="nav-item">
                        <i class="fa-solid fa-store"></i> Tout parcourir
                    </a>
                    <a href="panier.php" class="nav-item">
                        <i class="fa-solid fa-cart-shopping"></i> Mon panier
                    </a>
                    <a href="mes_ventes.php" class="nav-item active">
                        <i class="fa-solid fa-tag"></i> Mes ventes
                    </a>
                    <a href="mes_commandes.php" class="nav-item">
                        <i class="fa-solid fa-receipt"></i> Mes commandes
                    </a>
                    <a href="notifications.php" class="nav-item">
                        <i class="fa-solid fa-bell"></i> Notifications
                        <?php if ($unreadCount > 0): ?>
                            <span
                                style="background:#ef4444; color:white; border-radius:50%; width:18px; height:18px; font-size:10px; display:flex; align-items:center; justify-content:center; margin-left:auto;"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="vendreproduit.php" class="nav-item">
                        <i class="fa-solid fa-plus-circle"></i> Vendre un produit
                    </a>
                </div>

                <!-- Catégories -->
                <div class="mkt-sidebar-section filter-section">
                    <div class="mkt-nav-label">Catégorie</div>
                    <div class="filter-title" style="display:none;">Catégorie</div>
                    <div class="filter-option active" data-filter="all">
                        <span>Tous les produits</span>
                        <span class="filter-count"><?= $totalProduitCount ?></span>
                    </div>
                    <?php foreach ($categories as $category): ?>
                        <div class="filter-option" data-filter="<?= htmlspecialchars($category['nom']) ?>">
                            <span><?= htmlspecialchars($category['nom']) ?></span>
                            <span class="filter-count"><?= $categoryCounts[$category['idCategory']] ?? 0 ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Prix -->
                <div class="mkt-sidebar-section">
                    <div class="mkt-nav-label">Prix (DT)</div>
                    <div class="price-range">
                        <div class="price-inputs">
                            <input class="price-input" type="number" placeholder="Min" value="0">
                            <span class="price-sep">—</span>
                            <input class="price-input" type="number" placeholder="Max" value="3000">
                        </div>
                        <input type="range" min="0" max="3000" value="3000">
                    </div>
                </div>

                <!-- Disponibilité -->
                <div class="mkt-sidebar-section filter-section">
                    <div class="mkt-nav-label">Disponibilité</div>
                    <div class="filter-title" style="display:none;">Disponibilité</div>
                    <div class="filter-option" data-filter="disponible maintenant"><span><i class="fa-solid fa-circle-check"
                                style="color:#10b981;margin-right:.4rem;"></i>En stock</span></div>
                    <div class="filter-option" data-filter="non disponible"><span>Non disponible</span></div>
                    <div class="filter-option active" data-filter="all"><span>Tous</span></div>
                </div>

            </div><!-- /mkt-sidebar-card -->

        </aside>

        <!-- PRODUCTS -->
        <div class="mkt-main">

            <!-- HERO BANNER -->
            <section class="hero-banner" style="padding: 2rem 2rem 3rem;">
                <div class="hero-glow"></div>
                <div class="hero-glow-2"></div>
                <div class="hero-content"
                    style="margin: 0 auto; text-align: center; display: flex; flex-direction: column; align-items: center;">
                    <div class="hero-tag"><i class="fa-solid fa-tags"></i> Mes ventes</div>
                    <h1 class="hero-title">Gérez vos <span>annonces</span></h1>
                    <p class="hero-sub">Retrouvez toutes vos offres publiées. Vous pouvez les modifier ou les retirer à
                        tout moment.</p>
                    <div class="search-container"
                        style="display:flex; align-items:center; justify-content: center; width: 100%; gap:0.5rem;">
                        <div class="search-wrap" style="flex:1; max-width: 500px;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="main-search-input" placeholder="Rechercher un produit, une marque…">
                        </div>
                        <button class="btn-search" id="main-search-btn" style="white-space:nowrap;"><i
                                class="fa-solid fa-search"></i> Rechercher</button>
                    </div>
                    <div class="action-row"
                        style="display:flex; align-items:center; justify-content: center; gap:.75rem; margin-top:1.25rem; width: 100%;">
                        <span style="color:#475569; font-size:.82rem;">
                            Vous vendez ? <strong style="color:#94A3B8; font-weight:500;">Déposez votre annonce
                                gratuitement</strong>
                        </span>
                        <a href="vendreproduit.php"
                            style="display:inline-flex; align-items:center; gap:6px; background:transparent; color:#94A3B8; border:1px solid rgba(255,255,255,0.1); padding:8px 16px; border-radius:10px; font-size:.82rem; font-weight:500; white-space:nowrap;">
                            + Vendre un produit
                        </a>
                    </div>
                </div>
            </section>

            <!-- Toolbar -->
            <div class="products-toolbar">
                <p class="result-count"><strong><?= $totalProduitCount ?> produits</strong> trouvés</p>
                <div class="toolbar-right">
                    <select class="sort-select">
                        <option>Trier : Pertinence</option>
                        <option>Prix croissant</option>
                        <option>Prix décroissant</option>
                        <option>Meilleures notes</option>
                        <option>Nouveautés</option>
                    </select>
                    <div class="view-toggle">
                        <button class="view-btn active" title="Grille"><i class="fa-solid fa-grip"></i></button>
                        <button class="view-btn" title="Liste"><i class="fa-solid fa-list"></i></button>
                    </div>
                </div>
            </div>

            <div class="seller-dashboard">
                <div class="seller-kpi">
                    <span>Total ventes</span>
                    <strong><?= (int) $sellerStats['sold_units'] ?></strong>
                </div>
                <div class="seller-kpi">
                    <span>Commandes en attente</span>
                    <strong><?= (int) $sellerStats['pending_orders'] ?></strong>
                </div>
                <div class="seller-kpi">
                    <span>Revenu estimé</span>
                    <strong><?= number_format($sellerStats['revenue'], 0, ',', ' ') ?> DT</strong>
                </div>
                <div class="seller-kpi">
                    <span>Produits les plus vendus</span>
                    <div class="top-list">
                        <?php if (empty($sellerStats['top_products'])): ?>
                            <div><em>Aucune vente</em><span>0</span></div>
                        <?php else: ?>
                            <?php foreach ($sellerStats['top_products'] as $top): ?>
                                <div><em><?= htmlspecialchars($top['nom']) ?></em><span><?= (int) $top['qty'] ?></span></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Active filter chips -->
            <div class="active-filters">
                <div class="chip">Tous les produits <button>✕</button></div>
                <div class="chip">Tous statuts <button>✕</button></div>
            </div>

            <!-- Grid -->
            <div class="products-grid">
                <?php if (empty($produits)): ?>
                    <div class="product-card" style="opacity: 0.9; width: 100%; text-align: center; padding: 3rem 2rem;">
                        <div class="card-body">
                            <div class="card-title">Aucun produit pour le moment</div>
                            <p style="color: var(--text-muted); margin-top: 1rem;">Ajoutez un produit depuis la page «Vendre
                                un produit» pour le voir apparaître ici.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($produitsPagines as $produit):
                        $categoryName = $categoryNames[$produit['category_id']] ?? 'Autre';

                        // Statut logic
                        $isPending = (strtolower(trim($produit['statut'] ?? '')) === 'pending');

                        // Disponibilité logic (Align with home.php)
                        $dispoValue = $produit['disponibilite'] ?? 'Disponible maintenant';
                        $stockQty = (int) ($produit['stock'] ?? 0);

                        if ($stockQty <= 0) {
                            $stockClass = 'out-stock';
                            $stockText = 'Rupture de stock';
                            $opacityStyle = 'opacity:0.6;';
                        } elseif ($dispoValue === 'Disponible maintenant') {
                            $stockClass = 'in-stock';
                            $stockText = 'Stock: ' . $stockQty;
                            $opacityStyle = '';
                        } elseif ($dispoValue === 'Non disponible') {
                            $stockClass = 'out-stock';
                            $stockText = 'Non disponible';
                            $opacityStyle = 'opacity:0.6;';
                        } else {
                            $stockClass = 'low-stock';
                            $stockText = $dispoValue . ' · Stock: ' . $stockQty;
                            $opacityStyle = '';
                        }

                        $priceFormatted = number_format($produit['prix'], 0, ',', ' ');
                        $descriptionPreview = htmlspecialchars(mb_strimwidth($produit['description'], 0, 70, '...'));
                        ?>
                        <div class="product-card" data-id="<?= $produit['idProduit'] ?>" data-dispo="<?= htmlspecialchars($dispoValue) ?>" style="<?= $opacityStyle ?>">
                            <div class="card-image">
                                <?php if (!empty($produit['image'])): ?>
                                    <img src="<?= htmlspecialchars($produit['image']) ?>"
                                        alt="<?= htmlspecialchars($produit['nom']) ?>"
                                        style="width:100%; height:100%; object-fit: cover; border-radius: 1rem;" />
                                <?php else: ?>
                                    <span style="font-size: 3rem;">🛍️</span>
                                <?php endif; ?>
                                <?php if ($isPending): ?>
                                    <span class="card-badge badge-popular" style="background: rgba(245, 158, 11, 0.9);"><i
                                            class="fa-solid fa-clock"></i> EN ATTENTE</span>
                                <?php endif; ?>
                                <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
                            </div>
                            <div class="card-body">
                                <div class="card-category"><?= htmlspecialchars($categoryName) ?></div>
                                <div class="card-title"><?= htmlspecialchars($produit['nom']) ?></div>
                                <div class="card-rating" style="margin-bottom: 0.8rem;">
                                    <span class="stars">★★★★★</span>
                                    <span class="rating-text"
                                        style="opacity: .8; font-size: .9rem;"><?= $descriptionPreview ?></span>
                                </div>
                                <div class="card-footer">
                                    <div class="price-block">
                                        <span class="price-main"><?= $priceFormatted ?></span>
                                        <span class="price-currency">DT</span>
                                    </div>
                                    <div class="stock-info <?= $stockClass ?>"><span class="stock-dot"></span>
                                        <?= htmlspecialchars($stockText) ?></div>
                                </div>
                                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                    <a href="modifier_produit.php?id=<?= $produit['idProduit'] ?>" class="btn-action"
                                        style="flex: 1; display:flex; align-items: center; justify-content: center; gap:0.5rem; background: rgba(255,255,255,0.1); color: white; padding: 0.5rem; border-radius:0.5rem; font-weight:600; text-decoration:none;"><i
                                            class="fa-solid fa-pen"></i> Modifier</a>
                                    <button type="button" class="btn-action"
                                        style="flex: 1; display:flex; align-items: center; justify-content: center; gap:0.5rem; background: rgba(239,68,68,0.15); color: #ef4444; border:none; border-radius:0.5rem; font-weight:600; cursor:pointer;"
                                        onclick="openDeleteModal(<?= $produit['idProduit'] ?>)"><i
                                            class="fa-solid fa-trash"></i> Supprimer</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <!-- Bouton Précédent -->
                <a href="?page=<?= max(1, $currentPage - 1) ?>"
                    class="pag-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>" <?= $currentPage <= 1 ? 'onclick="return false;"' : '' ?>>
                    <i class="fa-solid fa-chevron-left"></i>
                </a>

                <?php
                // Générateur de boutons de pagination
                $maxPagesToShow = 5;
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);

                if ($endPage - $startPage + 1 < $maxPagesToShow) {
                    $startPage = max(1, $endPage - $maxPagesToShow + 1);
                }

                // Afficher le bouton "1" si nécessaire
                if ($startPage > 1): ?>
                    <a href="?page=1" class="pag-btn">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="pag-btn" style="pointer-events:none; cursor:default;">…</span>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Boutons de pages -->
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?page=<?= $i ?>" class="pag-btn <?= ($i === $currentPage) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <!-- Afficher les derniers boutons si nécessaire -->
                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="pag-btn" style="pointer-events:none; cursor:default;">…</span>
                    <?php endif; ?>
                    <a href="?page=<?= $totalPages ?>" class="pag-btn">
                        <?= $totalPages ?>
                    </a>
                <?php endif; ?>

                <!-- Bouton Suivant -->
                <a href="?page=<?= min($totalPages, $currentPage + 1) ?>"
                    class="pag-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" <?= $currentPage >= $totalPages ? 'onclick="return false;"' : '' ?>>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>

        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
        <div
            style="background:var(--bg-dark); padding:2rem; border-radius:1.5rem; border:1px solid var(--border); box-shadow:0 10px 40px rgba(0,0,0,0.5); width:90%; max-width:380px; text-align:center; animation: fadeUp 0.3s ease forwards;">
            <div
                style="width:64px; height:64px; border-radius:50%; background:rgba(239,68,68,0.1); color:#ef4444; font-size:1.8rem; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="font-size:1.3rem; font-weight:700; margin-bottom:0.75rem; color:white;">Supprimer l'annonce ?
            </h3>
            <p style="color:var(--text-muted); font-size:0.95rem; margin-bottom:2rem; line-height:1.5;">Êtes-vous sûr de
                vouloir supprimer définitivement cette annonce ? Cette action est irréversible.</p>
            <div style="display:flex; gap:1rem;">
                <button type="button" onclick="closeDeleteModal()"
                    style="flex:1; padding:0.8rem; background:rgba(255,255,255,0.05); color:white; border:none; border-radius:0.5rem; cursor:pointer; font-weight:600; font-family:'Space Grotesk', sans-serif; transition:0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.05)'">Annuler</button>
                <a id="confirmDeleteBtn" href="#"
                    style="flex:1; display:flex; align-items:center; justify-content:center; padding:0.8rem; background:#ef4444; color:white; border:none; border-radius:0.5rem; text-decoration:none; font-weight:600; box-shadow:0 0 20px rgba(239,68,68,0.3); transition:0.2s;"
                    onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">Oui,
                    supprimer</a>
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
    </script>

    <script src="../assets/js.js?v=6"></script>

</body>

</html>
