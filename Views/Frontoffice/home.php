<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';

$produitController  = new ProduitController();
$categoryController = new Category_prodController();
$produits           = $produitController->getByStatus('disponible');
$categories         = $categoryController->getAllData();

$categoryCounts = [];
foreach ($produits as $p) {
    $categoryCounts[$p['category_id']] = ($categoryCounts[$p['category_id']] ?? 0) + 1;
}
$categoryNames = [];
foreach ($categories as $cat) {
    $categoryNames[$cat['idCategory']] = $cat['nom'];
}
$totalProduitCount   = count($produits);
$totalCategoryCount  = count($categories);

// Pagination
$itemsPerPage = 12;
$currentPage  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalPages   = ceil($totalProduitCount / $itemsPerPage);
$currentPage  = min($currentPage, $totalPages > 0 ? $totalPages : 1);
$startIndex   = ($currentPage - 1) * $itemsPerPage;
$produitsPagines = array_slice($produits, $startIndex, $itemsPerPage);
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace — FreelaSkill</title>
    <meta name="description" content="Parcourez notre marketplace FreelaSkill — équipements tech, licences logiciels, accessoires créatifs.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=4">
</head>
<body class="page-anim home-page">

<!-- NAVBAR -->
<nav>
    <div class="logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </div>
    <ul class="nav-links">
        <li><a href="#">Accueil</a></li>
        <li><a href="#">Missions</a></li>
        <li><a href="#" class="active">Marketplace</a></li>
        <li><a href="#">Freelancers</a></li>
    </ul>
    <div class="nav-right">
        
        <div class="theme-toggle-btn" style="cursor: pointer; margin-right: 15px; font-size: 1.2rem; color: var(--text-muted); display: flex; align-items: center;" title="Basculer le thème">
            <i class="fa-regular fa-moon"></i>
        </div>
        <a href="panier.php" class="cart-btn" style="position: relative;">
            <i class="fa-solid fa-bag-shopping"></i> Panier
            <span class="cart-count" style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);">0</span>
        </a>
        <div class="nav-avatar">AH</div>
    </div>
</nav>

<!-- HERO -->
<section class="hero-banner" style="padding: 3rem 4rem 2rem;">
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>
    <div class="hero-content" style="max-width:750px;">
        <div class="hero-tag"><i class="fa-solid fa-bolt"></i> Marketplace Tunisia</div>
        <h1 class="hero-title">Trouvez les outils<br>qu'il vous <span>faut</span></h1>
        <p class="hero-sub">Équipements tech, licences logiciels, accessoires créatifs — livrés partout en Tunisie.</p>
        <div class="search-container">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="main-search-input" placeholder="Rechercher un produit, une marque…">
            </div>
            <button class="btn-search" id="main-search-btn"><i class="fa-solid fa-search"></i> Rechercher</button>
        </div>
        <div class="action-row" style="display:flex; align-items:center; gap:.75rem; margin-top:1.25rem; max-width:700px;">
            <span style="color:#475569; font-size:.82rem;">
                Vous vendez ? <strong style="color:#94A3B8; font-weight:500;">Déposez votre annonce gratuitement</strong>
            </span>
            <a href="vendreproduit.php" style="display:inline-flex; align-items:center; gap:6px; background:transparent; color:#94A3B8; border:1px solid rgba(255,255,255,0.1); padding:8px 16px; border-radius:10px; font-size:.82rem; font-weight:500; white-space:nowrap; text-decoration:none; transition:background .2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'">
                + Vendre un produit
            </a>
        </div>
    </div>
</section>

<!-- MARKETPLACE LAYOUT -->
<div class="marketplace-layout">

    <!-- ── SIDEBAR ── -->
    <aside class="mkt-sidebar">

        <!-- Card 1 : Profil marketplace -->
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-shop"></i></div>
                <div class="mkt-profile-name">Marketplace</div>
                <div class="mkt-profile-sub">FreelaSkill Tunisia</div>
            </div>
            <div class="mkt-profile-stats">
                <div class="mkt-stat">
                    <div class="mkt-stat-val"><?= $totalProduitCount ?></div>
                    <div class="mkt-stat-label">Produits</div>
                </div>
                <div class="mkt-stat">
                    <div class="mkt-stat-val"><?= $totalCategoryCount ?></div>
                    <div class="mkt-stat-label">Catégories</div>
                </div>
            </div>
        </div>

        <!-- Card 2 : Navigation + Filtres -->
        <div class="mkt-sidebar-card">

            <!-- Navigation -->
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="home.php" class="nav-item active">
                    <i class="fa-solid fa-shop"></i> Tout parcourir
                </a>
                <a href="panier.php" class="nav-item">
                    <i class="fa-solid fa-cart-shopping"></i> Mon panier
                </a>
                <a href="mes_ventes.php" class="nav-item">
                    <i class="fa-solid fa-tag"></i> Mes ventes
                </a>
               
            </div>

            <!-- Catégories -->
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Catégorie</div>
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
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Disponibilité</div>
                <div class="filter-option active"><span><i class="fa-solid fa-circle-check" style="color:#10b981;margin-right:.4rem;"></i>En stock</span></div>
                <div class="filter-option"><span>Stock faible</span></div>
                <div class="filter-option"><span>Tous</span></div>
            </div>

        </div><!-- /mkt-sidebar-card -->

    </aside>


    <!-- ── MAIN PANEL ── -->
    <div class="mkt-main">

        <!-- Toolbar -->
        <div class="products-toolbar">
            <p class="result-count"><strong><?= $totalProduitCount ?> produits</strong> trouvés</p>
            <div class="toolbar-right">
                <select class="sort-select">
                    <option>Trier : Pertinence</option>
                    <option>Prix croissant</option>
                    <option>Prix décroissant</option>
                    <option>Nouveautés</option>
                </select>
                <div class="view-toggle">
                    <button class="view-btn active" title="Grille"><i class="fa-solid fa-grip"></i></button>
                    <button class="view-btn" title="Liste"><i class="fa-solid fa-list"></i></button>
                </div>
            </div>
        </div>

        <!-- Filter chips -->
        <div class="active-filters">
            <div class="chip">Tous les produits <button>✕</button></div>
            <div class="chip">En stock <button>✕</button></div>
        </div>

        <!-- Products grid -->
        <div class="products-grid">
            <?php if (empty($produits)): ?>
                <div class="product-card" style="opacity:.9;width:100%;text-align:center;padding:3rem 2rem;grid-column:1/-1;">
                    <div class="card-body">
                        <div class="card-title">Aucun produit pour le moment</div>
                        <p style="color:var(--text-muted);margin-top:1rem;">Ajoutez un produit depuis «Vendre un produit».</p>
                        <a href="vendreproduit.php" class="btn btn-primary" style="margin-top:1.5rem;"><i class="fa-solid fa-plus"></i> Vendre un produit</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($produitsPagines as $produit):
                    $catName   = $categoryNames[$produit['category_id']] ?? 'Autre';
                    $stockClass = $produit['stock'] <= 0 ? 'out-stock' : ($produit['stock'] <= 2 ? 'low-stock' : 'in-stock');
                    $stockText  = $produit['stock'] <= 0 ? 'Rupture' : ($produit['stock'] <= 2 ? 'Stock faible' : 'En stock');
                    $opStyle    = $produit['stock'] <= 0 ? 'opacity:0.6;' : '';
                    $priceStr   = number_format($produit['prix'], 0, ',', ' ');
                    $desc       = htmlspecialchars(mb_strimwidth($produit['description'], 0, 70, '…'));
                ?>
                    <div class="product-card" data-id="<?= $produit['idProduit'] ?>" style="<?= $opStyle ?>">
                        <div class="card-image">
                            <?php if (!empty($produit['image'])): ?>
                                <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:1rem 1rem 0 0;">
                            <?php else: ?>
                                <span style="font-size:3rem;">🛍️</span>
                            <?php endif; ?>
                            <?php if ($produit['stock'] <= 2 && $produit['stock'] > 0): ?>
                                <span class="card-badge badge-popular">STOCK FAIBLE</span>
                            <?php elseif ($produit['stock'] <= 0): ?>
                                <span class="card-badge badge-out" style="background:rgba(239,68,68,0.9);">RUPTURE</span>
                            <?php endif; ?>
                            <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
                        </div>
                        <div class="card-body">
                            <div class="card-category"><?= htmlspecialchars($catName) ?></div>
                            <div class="card-title"><?= htmlspecialchars($produit['nom']) ?></div>
                            <div class="card-rating" style="margin-bottom:.8rem;">
                                <span class="stars">★★★★★</span>
                                <span class="rating-text" style="opacity:.8;font-size:.9rem;"><?= $desc ?></span>
                            </div>
                            <div class="card-footer">
                                <div class="price-block">
                                    <span class="price-main"><?= $priceStr ?></span>
                                    <span class="price-currency">DT</span>
                                </div>
                                <div class="stock-info <?= $stockClass ?>"><span class="stock-dot"></span><?= $stockText ?></div>
                            </div>
                            <button class="btn-cart"><i class="fa-solid fa-cart-plus"></i> Ajouter au panier</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-top:3rem;">
            <a href="?page=<?= max(1,$currentPage-1) ?>" class="pag-btn <?= $currentPage<=1?'disabled':'' ?>" <?= $currentPage<=1?'onclick="return false;"':'' ?>>
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <?php
            $start = max(1, $currentPage-2);
            $end   = min($totalPages, $start+4);
            if ($start > 1): ?>
                <a href="?page=1" class="pag-btn">1</a>
                <?php if ($start > 2): ?><span class="pag-btn" style="pointer-events:none;">…</span><?php endif; ?>
            <?php endif; ?>
            <?php for ($i=$start;$i<=$end;$i++): ?>
                <a href="?page=<?= $i ?>" class="pag-btn <?= $i===$currentPage?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages-1): ?><span class="pag-btn" style="pointer-events:none;">…</span><?php endif; ?>
                <a href="?page=<?= $totalPages ?>" class="pag-btn"><?= $totalPages ?></a>
            <?php endif; ?>
            <a href="?page=<?= min($totalPages,$currentPage+1) ?>" class="pag-btn <?= $currentPage>=$totalPages?'disabled':'' ?>" <?= $currentPage>=$totalPages?'onclick="return false;"':'' ?>>
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
        <?php endif; ?>

    </div><!-- /mkt-main -->
</div><!-- /marketplace-layout -->

<script src="../assets/js.js?v=4"></script>
</body>
</html>