<?php
require_once __DIR__ . '/../../Models/Produit.php';
require_once __DIR__ . '/../../Models/Category_prod.php';

$produitModel = new Produit();
$categoryModel = new Category_prod();
$produits = $produitModel->getByStatus('disponible');
$categories = $categoryModel->getAll();
$categoryCounts = [];
foreach ($produits as $produit) {
    $categoryCounts[$produit['category_id']] = ($categoryCounts[$produit['category_id']] ?? 0) + 1;
}
$categoryNames = [];
foreach ($categories as $category) {
    $categoryNames[$category['idCategory']] = $category['nom'];
}
$totalProduitCount = count($produits);
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
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
        <li><a href="#">Blog</a></li>
    </ul>
    <div class="nav-right">
        <a href="panier.php" class="cart-btn">
            <i class="fa-solid fa-cart-shopping"></i> Panier
            <span class="cart-count">0</span>
        </a>
        <div class="nav-avatar">AH</div>
    </div>
</nav>

<!-- HERO BANNER -->
<section class="hero-banner">
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>
    <div class="hero-content">
        <div class="hero-tag"><i class="fa-solid fa-bolt"></i> Marketplace Tunisia</div>
        <h1 class="hero-title">Trouvez les outils<br>qu'il vous <span>faut</span></h1>
        <p class="hero-sub">Équipements tech, licences logiciels, accessoires créatifs — livrés partout en Tunisie.</p>
        <div class="search-container">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Rechercher un produit, une marque…">
            </div>
            <button class="btn-search"><i class="fa-solid fa-search"></i> Rechercher</button>
        </div>
        <div class="action-row" style="display:flex; align-items:center; gap:.75rem; margin-top:1.25rem; max-width:700px;">
            <span style="color:#475569; font-size:.82rem;">
                Vous vendez ? <strong style="color:#94A3B8; font-weight:500;">Déposez votre annonce gratuitement</strong>
            </span>
            <a href="vendreproduit.php" style="display:inline-flex; align-items:center; gap:6px; background:transparent; color:#94A3B8; border:1px solid rgba(255,255,255,0.1); padding:8px 16px; border-radius:10px; font-size:.82rem; font-weight:500; white-space:nowrap;">
                + Vendre un produit
            </a>
        </div>
    </div>
</section>

<!-- PAGE BODY -->
<div class="page-body">

    <!-- FILTERS -->
    <aside class="filters">

        <div class="filter-section">
            <div class="filter-title">Catégorie</div>
            <div class="filter-option active">
                <span>Tous les produits</span>
                <span class="filter-count"><?= $totalProduitCount ?></span>
                <span class="filter-dot"></span>
            </div>
            <?php foreach ($categories as $category): ?>
                <div class="filter-option">
                    <span><?= htmlspecialchars($category['nom']) ?></span>
                    <span class="filter-count"><?= $categoryCounts[$category['idCategory']] ?? 0 ?></span>
                    <span class="filter-dot"></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="divider"></div>

        <div class="filter-section">
            <div class="filter-title">Prix (DT)</div>
            <div class="price-range">
                <div class="price-inputs">
                    <input class="price-input" type="number" placeholder="Min" value="0">
                    <span class="price-sep">—</span>
                    <input class="price-input" type="number" placeholder="Max" value="3000">
                </div>
                <input type="range" min="0" max="3000" value="3000">
            </div>
        </div>

        <div class="divider"></div>

        <div class="filter-section">
            <div class="filter-title">Disponibilité</div>
            <div class="filter-option active">
                <span>En stock</span>
                <span class="filter-dot"></span>
            </div>
            <div class="filter-option">
                <span>Stock faible</span>
                <span class="filter-dot"></span>
            </div>
            <div class="filter-option">
                <span>Tous</span>
                <span class="filter-dot"></span>
            </div>
        </div>

    </aside>

    <!-- PRODUCTS -->
    <div class="products-area">

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

        <!-- Active filter chips -->
        <div class="active-filters">
            <div class="chip">Tous les produits <button>✕</button></div>
            <div class="chip">En stock <button>✕</button></div>
        </div>

        <!-- Grid -->
        <div class="products-grid">
            <?php if (empty($produits)): ?>
                <div class="product-card" style="opacity: 0.9; width: 100%; text-align: center; padding: 3rem 2rem;">
                    <div class="card-body">
                        <div class="card-title">Aucun produit pour le moment</div>
                        <p style="color: var(--text-muted); margin-top: 1rem;">Ajoutez un produit depuis la page «Vendre un produit» pour le voir apparaître ici.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($produits as $produit):
                    $categoryName = $categoryNames[$produit['category_id']] ?? 'Autre';
                    $stockClass = $produit['stock'] <= 0 ? 'out-stock' : ($produit['stock'] <= 2 ? 'low-stock' : 'in-stock');
                    $stockText = $produit['stock'] <= 0 ? 'Rupture de stock' : ($produit['stock'] <= 2 ? 'Stock faible' : 'En stock');
                    $opacityStyle = $produit['stock'] <= 0 ? 'opacity:0.6;' : '';
                    $priceFormatted = number_format($produit['prix'], 0, ',', ' ');
                    $descriptionPreview = htmlspecialchars(mb_strimwidth($produit['description'], 0, 70, '...'));
                ?>
                    <div class="product-card" data-id="<?= $produit['idProduit'] ?>" style="<?= $opacityStyle ?>">
                        <div class="card-image">
                            <span style="font-size: 3rem;">🛍️</span>
                            <?php if ($produit['stock'] <= 2 && $produit['stock'] > 0): ?>
                                <span class="card-badge badge-popular">STOCK FAIBLE</span>
                            <?php elseif ($produit['stock'] <= 0): ?>
                                <span class="card-badge badge-out">RUPTURE</span>
                            <?php endif; ?>
                            <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
                        </div>
                        <div class="card-body">
                            <div class="card-category"><?= htmlspecialchars($categoryName) ?></div>
                            <div class="card-title"><?= htmlspecialchars($produit['nom']) ?></div>
                            <div class="card-rating" style="margin-bottom: 0.8rem;">
                                <span class="stars">★★★★★</span>
                                <span class="rating-text" style="opacity: .8; font-size: .9rem;"><?= $descriptionPreview ?></span>
                            </div>
                            <div class="card-footer">
                                <div class="price-block">
                                    <span class="price-main"><?= $priceFormatted ?></span>
                                    <span class="price-currency">DT</span>
                                </div>
                                <div class="stock-info <?= $stockClass ?>"><span class="stock-dot"></span> <?= htmlspecialchars($stockText) ?></div>
                            </div>
                            <button class="btn-cart"><i class="fa-solid fa-cart-plus"></i> Ajouter au panier</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <button class="pag-btn" disabled><i class="fa-solid fa-chevron-left"></i></button>
            <button class="pag-btn active">1</button>
            <button class="pag-btn">2</button>
            <button class="pag-btn">3</button>
            <button class="pag-btn">…</button>
            <button class="pag-btn">6</button>
            <button class="pag-btn"><i class="fa-solid fa-chevron-right"></i></button>
        </div>

    </div>
</div>

<script src="../assets/js.js"></script>

</body>
</html>