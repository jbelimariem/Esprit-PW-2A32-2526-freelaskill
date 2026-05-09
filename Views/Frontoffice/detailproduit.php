<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';

$notifController = new NotificationController();
$unreadCount = $notifController->getUnreadCount(1);


$produitController = new ProduitController();
$categoryController = new Category_prodController();
$produit = null;
$categoryName = 'Autre';
$stockClass = 'out-stock';
$stockText = 'Indisponible';
$priceFormatted = '0';
$similarProducts = [];
$canOrder = false;

if (!empty($_GET['id'])) {
    $produit = $produitController->getByIdData((int) $_GET['id']);
    if ($produit) {
        $category = $categoryController->getByIdData($produit['category_id']);
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
        $canOrder = ($produit['disponibilite'] ?? 'Disponible maintenant') !== 'Non disponible' && (int)$produit['stock'] > 0;
        $similarProducts = array_values(array_filter(
            $produitController->getByCategoryData($produit['category_id'], 'disponible'),
            fn($p) => (int)$p['idProduit'] !== (int)$produit['idProduit']
        ));
        $similarProducts = array_slice($similarProducts, 0, 3);
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
    <link rel="stylesheet" href="../assets/style.css?v=6">
    <style>
        .detail-actions { display:flex; flex-wrap:wrap; gap:.75rem; margin-top:1rem; }
        .detail-actions .btn-cart, .detail-actions .cart-btn { justify-content:center; width:auto; min-height:44px; }
        .trust-strip { display:grid; grid-template-columns:repeat(3,1fr); gap:0; margin-top:1rem; background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); border-radius:1.25rem; overflow:hidden; }
        .trust-item { padding:1rem; border-right:1px solid rgba(255,255,255,.06); }
        .trust-item:last-child { border-right:0; }
        .trust-item i { color:#3b82f6; margin-right:.45rem; }
        .similar-section { margin-top:1.5rem; padding:1.25rem; background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); border-radius:1.25rem; }
        .similar-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-top:1rem; }
        .similar-card { text-decoration:none; color:inherit; background:rgba(0,0,0,.18); border:1px solid rgba(255,255,255,.06); border-radius:1rem; overflow:hidden; transition:.2s; }
        .similar-card:hover { transform:translateY(-2px); border-color:rgba(59,130,246,.35); }
        .similar-card img { width:100%; height:130px; object-fit:cover; display:block; }
        .similar-card div { padding:.85rem; }
        @media (max-width:900px){ .trust-strip,.similar-grid{grid-template-columns:1fr;} .trust-item{border-right:0;border-bottom:1px solid rgba(255,255,255,.06);} }
    </style>
</head>
<body class="page-anim">

<nav>
    <div class="logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </div>
    <div class="nav-right">
        <button class="theme-toggle-btn" style="background: none; border: none; color: #e2e8f0; cursor: pointer; font-size: 1.2rem; padding: 0.5rem; display: flex; align-items: center; justify-content: center; transition: color 0.3s ease;" title="Toggle dark/light mode">
            <i class="fa-regular fa-moon"></i>
        </button>
        <a href="notifications.php" class="cart-btn" style="position: relative; margin-right: 10px;">
            <i class="fa-solid fa-bell"></i>
            <?php if($unreadCount > 0): ?>
                <span class="cart-count" style="position:absolute;top:-6px;right:-6px;background:#3b82f6;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a href="home.php" class="cart-btn" style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.15); color: white;">
            <i class="fa-solid fa-arrow-left"></i> Boutique
        </a>
    </div>
</nav>

<!-- MARKETPLACE LAYOUT -->
<div class="marketplace-layout">

    <!-- ── SIDEBAR ── -->
    <aside class="mkt-sidebar">
        <!-- Card 1 : Fiche Produit -->
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-box-open"></i></div>
                <div class="mkt-profile-name">Fiche Produit</div>
                <div class="mkt-profile-sub">Marketplace FreelaSkill</div>
            </div>
        </div>

        <!-- Card 2 : Navigation + Infos produit -->
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
                <a href="mes_ventes.php" class="nav-item">
                    <i class="fa-solid fa-tag"></i> Mes ventes
                </a>
                <a href="mes_commandes.php" class="nav-item">
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

            <!-- Informations -->
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Informations</div>
                <?php if ($produit): ?>
                    <div class="filter-option active">
                        <span><i class="fa-solid fa-tag" style="color:#3b82f6;margin-right:.4rem;"></i><?= htmlspecialchars($categoryName) ?></span>
                    </div>
                    <div class="filter-option">
                        <span><i class="fa-solid fa-money-bill" style="color:#10b981;margin-right:.4rem;"></i>Prix : <?= $priceFormatted ?> DT</span>
                    </div>
                    <div class="filter-option">
                        <span><i class="fa-solid fa-box" style="color:#a855f7;margin-right:.4rem;"></i><?= htmlspecialchars($stockText) ?></span>
                    </div>
                    <?php 
                    $disponibilite = $produit['disponibilite'] ?? 'Disponible maintenant';
                    $stockCount = (int)($produit['stock'] ?? 0);
                    ?>
                    <div class="filter-option">
                        <span>
                            <?php if ($stockCount <= 0): ?>
                                <i class="fa-solid fa-circle-xmark" style="color:#ef4444;margin-right:.4rem;"></i>Rupture de stock
                            <?php elseif ($disponibilite === 'Disponible maintenant'): ?>
                                <i class="fa-solid fa-circle-check" style="color:#10b981;margin-right:.4rem;"></i>Disponible maintenant
                            <?php elseif ($disponibilite === 'Dans 2 semaines'): ?>
                                <i class="fa-solid fa-clock" style="color:#f59e0b;margin-right:.4rem;"></i>Dans 2 semaines
                            <?php elseif ($disponibilite === 'Dans 1 mois'): ?>
                                <i class="fa-solid fa-clock" style="color:#f59e0b;margin-right:.4rem;"></i>Dans 1 mois
                            <?php elseif ($disponibilite === 'Non disponible'): ?>
                                <i class="fa-solid fa-circle-xmark" style="color:#ef4444;margin-right:.4rem;"></i>Non disponible
                            <?php else: ?>
                                <span><?= htmlspecialchars($disponibilite) ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php else: ?>
                    <div class="filter-option active">
                        <span>Produit introuvable</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </aside>

    <!-- ── MAIN PANEL ── -->
    <div class="mkt-main">

        <!-- HERO -->
        <section class="hero-banner" style="padding: 2rem 2rem 3rem;">
            <div class="hero-glow"></div>
            <div class="hero-glow-2"></div>
            <div class="hero-content" style="margin: 0 auto; text-align: center; display: flex; flex-direction: column; align-items: center;">
                <div class="hero-tag"><i class="fa-solid fa-box-open"></i> Fiche produit</div>
                <h1 class="hero-title"><?= htmlspecialchars($produit['nom'] ?? 'Produit introuvable') ?></h1>
                <p class="hero-sub">Consultez les détails complets et caractéristiques techniques ci-dessous.</p>
            </div>
        </section>

        <div class="products-toolbar">
            <p class="result-count"><strong><?= $produit ? '1 article' : '0 article' ?></strong> sur cette fiche</p>
            <div class="toolbar-right">
                <button class="view-btn active" title="Fiche produit"><i class="fa-solid fa-info"></i></button>
            </div>
        </div>

        <?php if ($produit): ?>
            <div class="products-grid" style="grid-template-columns: 1fr;">
                <div class="product-card" data-id="<?= (int)$produit['idProduit'] ?>" style="animation-delay: 0.05s; opacity: 1;">
                    <div class="card-image" style="background: linear-gradient(135deg, #0d1117, #1e3a5f); position: relative; overflow: hidden;">
                        <?php if (!empty($produit['image'])): ?>
                            <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>" style="width:100%; height:100%; object-fit: cover; display:block;" />
                        <?php else: ?>
                            <span style="display:inline-block; font-size: 2.5rem;">🛍️</span>
                        <?php endif; ?>
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
                        </div>
                        <div class="detail-actions">
                            <?php if ($canOrder): ?>
                                <button class="btn-cart"><i class="fa-solid fa-cart-plus"></i> Ajouter au panier</button>
                            <?php else: ?>
                                <button class="btn-cart" disabled><i class="fa-solid fa-ban"></i> Indisponible</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="trust-strip">
                <div class="trust-item"><i class="fa-solid fa-truck"></i><strong>Livraison Tunisie</strong><br><span style="color:#94a3b8;font-size:.85rem;">Standard, suivi vendeur</span></div>
                <div class="trust-item"><i class="fa-solid fa-shield-halved"></i><strong>Paiement sécurisé</strong><br><span style="color:#94a3b8;font-size:.85rem;">Stripe ou paiement sur place</span></div>
                <div class="trust-item"><i class="fa-solid fa-rotate-left"></i><strong>Retour sous 14 jours</strong><br><span style="color:#94a3b8;font-size:.85rem;">Selon état du produit</span></div>
            </div>
            <?php if (!empty($similarProducts)): ?>
                <div class="similar-section">
                    <div class="mkt-nav-label">Produits similaires</div>
                    <div class="similar-grid">
                        <?php foreach ($similarProducts as $sp): ?>
                            <a class="similar-card" href="detailproduit.php?id=<?= (int)$sp['idProduit'] ?>">
                                <?php if (!empty($sp['image'])): ?><img src="<?= htmlspecialchars($sp['image']) ?>" alt="<?= htmlspecialchars($sp['nom']) ?>"><?php endif; ?>
                                <div>
                                    <strong><?= htmlspecialchars($sp['nom']) ?></strong><br>
                                    <span style="color:#10b981;font-weight:700;"><?= number_format((float)$sp['prix'], 0, ',', ' ') ?> DT</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
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
