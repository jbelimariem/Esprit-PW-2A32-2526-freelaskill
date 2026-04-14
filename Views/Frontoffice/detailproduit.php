<?php
require_once __DIR__ . '/../../Models/Produit.php';
require_once __DIR__ . '/../../Models/Category_prod.php';

$produitModel = new Produit();
$categoryModel = new Category_prod();
$produit = null;
$categoryName = 'Autre';
$stockClass = 'out-stock';
$stockText = 'Indisponible';
$priceFormatted = '0';

if (!empty($_GET['id'])) {
    $produit = $produitModel->getById((int) $_GET['id']);
    if ($produit) {
        $category = $categoryModel->getById($produit['category_id']);
        $categoryName = $category['nom'] ?? 'Autre';
        $priceFormatted = number_format((float) $produit['prix'], 0, ',', ' ');
        if ($produit['stock'] <= 0) {
            $stockText = 'Rupture de stock';
            $stockClass = 'out-stock';
        } elseif ($produit['stock'] <= 2) {
            $stockText = 'Stock faible';
            $stockClass = 'low-stock';
        } else {
            $stockText = 'En stock';
            $stockClass = 'in-stock';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produit['nom'] ?? 'Détail produit') ?> — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="page-anim">

<nav>
    <div class="logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </div>
    <div class="nav-right">
        <a href="home.php" class="cart-btn" style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.15); color: white;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>
</nav>

<section class="hero-banner">
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>
    <div class="hero-content">
        <div class="hero-tag"><i class="fa-solid fa-box-open"></i> Fiche produit</div>
        <h1 class="hero-title"><?= htmlspecialchars($produit['nom'] ?? 'Produit introuvable') ?></h1>
        <p class="hero-sub"><?= htmlspecialchars($produit['description'] ?? 'Sélectionnez un produit depuis la page d’accueil pour voir ses détails.') ?></p>
    </div>
</section>

<div class="page-body">
    <aside class="filters">
        <div class="filter-section">
            <div class="filter-title">Informations</div>
            <?php if ($produit): ?>
                <div class="filter-option active">
                    <span><?= htmlspecialchars($categoryName) ?></span>
                    <span class="filter-dot"></span>
                </div>
                <div class="filter-option">
                    <span>Prix : <?= $priceFormatted ?> DT</span>
                </div>
                <div class="filter-option">
                    <span><?= htmlspecialchars($stockText) ?></span>
                </div>
                <div class="filter-option">
                    <span>Statut : <?= htmlspecialchars($produit['statut'] ?? 'N/A') ?></span>
                </div>
            <?php else: ?>
                <div class="filter-option active">
                    <span>Produit introuvable</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="divider"></div>

        <div class="filter-section">
            <div class="filter-title">À propos</div>
            <div class="filter-option active">
                <span><?= htmlspecialchars($produit['description'] ?? 'Aucune description disponible.') ?></span>
            </div>
        </div>
    </aside>

    <div class="products-area">
        <div class="products-toolbar">
            <p class="result-count"><strong><?= $produit ? '1 article' : '0 article' ?></strong> sur cette fiche</p>
            <div class="toolbar-right">
                <button class="view-btn active" title="Fiche produit"><i class="fa-solid fa-info"></i></button>
            </div>
        </div>

        <?php if ($produit): ?>
            <div class="products-grid" style="grid-template-columns: 1fr;">
                <div class="product-card" style="animation-delay: 0.05s; opacity: 1;">
                    <div class="card-image" style="background: linear-gradient(135deg, #0d1117, #1e3a5f); font-size: 5rem;">
                        <span style="display:inline-block; font-size: 2.5rem;">🛍️</span>
                        <span class="card-badge <?= $stockClass === 'in-stock' ? 'badge-new' : ($stockClass === 'low-stock' ? 'badge-popular' : 'badge-out') ?>">
                            <?= htmlspecialchars($stockText) ?>
                        </span>
                        <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
                    </div>
                    <div class="card-body">
                        <div class="card-category"><?= htmlspecialchars($categoryName) ?></div>
                        <div class="card-title"><?= htmlspecialchars($produit['nom']) ?></div>
                        <div class="card-rating">
                            <span class="stars">★★★★★</span>
                            <span class="rating-text">#<?= htmlspecialchars($produit['idProduit']) ?></span>
                        </div>
                        <p style="color: var(--text-muted); line-height: 1.75; margin-bottom: 1rem;">
                            <?= nl2br(htmlspecialchars($produit['description'])) ?>
                        </p>
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
            </div>
        <?php else: ?>
            <div class="products-grid" style="grid-template-columns: 1fr;">
                <div class="product-card" style="animation-delay: 0.05s; opacity: 1; width: 100%; text-align: center; padding: 2rem;">
                    <div class="card-body">
                        <div class="card-title">Produit introuvable</div>
                        <p style="color: var(--text-muted); margin-top: 1rem;">
                            Aucun produit n’a été trouvé pour cet identifiant. Retournez à l’accueil et sélectionnez un produit valide.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js.js"></script>
</body>
</html>