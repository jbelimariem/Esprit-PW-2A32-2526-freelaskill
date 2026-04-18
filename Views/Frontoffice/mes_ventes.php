<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';

$produitController = new ProduitController();
$categoryController = new Category_prodController();
$produits = $produitController->getByStatus('disponible');
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
    $produitController->deleteData($_GET['delete_id']);
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
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=2">
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
        <a href="panier.php" class="cart-btn" style="position: relative;">
            <i class="fa-solid fa-bag-shopping"></i> Mes achats
            <span class="cart-count" style="position: absolute; top: -6px; right: -6px; background: #ef4444; color: white; border-radius: 50%; font-size: 0.7rem; font-weight: bold; display: flex; align-items: center; justify-content: center; width: 18px; height: 18px; border: 2px solid var(--bg-dark);">0</span>
        </a>
        <div class="nav-avatar">AH</div>
    </div>
</nav>

<!-- HERO BANNER -->
<section class="hero-banner">
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>
    <div class="hero-content">
        <div class="hero-tag"><i class="fa-solid fa-tags"></i> Mes ventes</div>
        <h1 class="hero-title">Gérez vos <span>annonces</span></h1>
        <p class="hero-sub">Retrouvez toutes vos offres publiées. Vous pouvez les modifier ou les retirer à tout moment.</p>
        <div class="search-container" style="display:flex; align-items:center; gap:0.5rem;">
            <div class="search-wrap" style="flex:1;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="main-search-input" placeholder="Rechercher un produit, une marque…">
            </div>
            <button class="btn-search" id="main-search-btn" style="white-space:nowrap;"><i class="fa-solid fa-search"></i> Rechercher</button>
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

    <!-- SIDEBAR NAVIGATION & FILTERS -->
    <aside class="filters">
        
        <!-- Marketplace Nav -->
        <div class="marketplace-sidebar-nav">
            <div class="sidebar-header">
                <h2>Marketplace</h2>
                <div class="sidebar-settings" title="Paramètres"><i class="fa-solid fa-gear"></i></div>
            </div>
            
            <a href="home.php" class="sidebar-nav-item">
                <div class="item-left">
                    <div class="icon-box"><i class="fa-solid fa-shop"></i></div>
                    <span class="nav-label">Tout parcourir</span>
                </div>
            </a>
            
            <a href="#" class="sidebar-nav-item">
                <div class="item-left">
                    <div class="icon-box"><i class="fa-solid fa-bell"></i></div>
                    <span class="nav-label">Notifications</span>
                </div>
            </a>
            
            <a href="panier.php" class="sidebar-nav-item">
                <div class="item-left">
                    <div class="icon-box"><i class="fa-solid fa-cart-shopping"></i></div>
                    <span class="nav-label">Panier</span>
                </div>
                <i class="fa-solid fa-chevron-right chevron-icon"></i>
            </a>
            
            <a href="mes_ventes.php" class="sidebar-nav-item active">
                <div class="item-left">
                    <div class="icon-box icon-blue"><i class="fa-solid fa-tag"></i></div>
                    <span class="nav-label">Mes ventes</span>
                </div>
                <i class="fa-solid fa-chevron-right chevron-icon"></i>
            </a>
        </div>

        <div class="filter-section">
            <div class="filter-title">Catégorie</div>
            <div class="filter-option active" data-filter="all">
                <span>Tous les produits</span>
                <span class="filter-count"><?= $totalProduitCount ?></span>
                <span class="filter-dot"></span>
            </div>
            <?php foreach ($categories as $category): ?>
                <div class="filter-option" data-filter="<?= htmlspecialchars($category['nom']) ?>">
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
                <?php foreach ($produitsPagines as $produit):
                    $categoryName = $categoryNames[$produit['category_id']] ?? 'Autre';
                    $stockClass = $produit['stock'] <= 0 ? 'out-stock' : ($produit['stock'] <= 2 ? 'low-stock' : 'in-stock');
                    $stockText = $produit['stock'] <= 0 ? 'Rupture de stock' : ($produit['stock'] <= 2 ? 'Stock faible' : 'En stock');
                    $opacityStyle = $produit['stock'] <= 0 ? 'opacity:0.6;' : '';
                    $priceFormatted = number_format($produit['prix'], 0, ',', ' ');
                    $descriptionPreview = htmlspecialchars(mb_strimwidth($produit['description'], 0, 70, '...'));
                ?>
                    <div class="product-card" data-id="<?= $produit['idProduit'] ?>" style="<?= $opacityStyle ?>">
                        <div class="card-image">
                            <?php if (!empty($produit['image'])): ?>
                                <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>" style="width:100%; height:100%; object-fit: cover; border-radius: 1rem;" />
                            <?php else: ?>
                                <span style="font-size: 3rem;">🛍️</span>
                            <?php endif; ?>
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
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <a href="modifier_produit.php?id=<?= $produit['idProduit'] ?>" class="btn-action" style="flex: 1; display:flex; align-items: center; justify-content: center; gap:0.5rem; background: rgba(255,255,255,0.1); color: white; padding: 0.5rem; border-radius:0.5rem; font-weight:600; text-decoration:none;"><i class="fa-solid fa-pen"></i> Modifier</a>
                                <button type="button" class="btn-action" style="flex: 1; display:flex; align-items: center; justify-content: center; gap:0.5rem; background: rgba(239,68,68,0.15); color: #ef4444; border:none; border-radius:0.5rem; font-weight:600; cursor:pointer;" onclick="openDeleteModal(<?= $produit['idProduit'] ?>)"><i class="fa-solid fa-trash"></i> Supprimer</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <!-- Bouton Précédent -->
            <a href="?page=<?= max(1, $currentPage - 1) ?>" class="pag-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>" <?= $currentPage <= 1 ? 'onclick="return false;"' : '' ?>>
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
            <a href="?page=<?= min($totalPages, $currentPage + 1) ?>" class="pag-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" <?= $currentPage >= $totalPages ? 'onclick="return false;"' : '' ?>>
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>

    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
   <div style="background:var(--bg-dark); padding:2rem; border-radius:1.5rem; border:1px solid var(--border); box-shadow:0 10px 40px rgba(0,0,0,0.5); width:90%; max-width:380px; text-align:center; animation: fadeUp 0.3s ease forwards;">
       <div style="width:64px; height:64px; border-radius:50%; background:rgba(239,68,68,0.1); color:#ef4444; font-size:1.8rem; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
           <i class="fa-solid fa-triangle-exclamation"></i>
       </div>
       <h3 style="font-size:1.3rem; font-weight:700; margin-bottom:0.75rem; color:white;">Supprimer l'annonce ?</h3>
       <p style="color:var(--text-muted); font-size:0.95rem; margin-bottom:2rem; line-height:1.5;">Êtes-vous sûr de vouloir supprimer définitivement cette annonce ? Cette action est irréversible.</p>
       <div style="display:flex; gap:1rem;">
           <button type="button" onclick="closeDeleteModal()" style="flex:1; padding:0.8rem; background:rgba(255,255,255,0.05); color:white; border:none; border-radius:0.5rem; cursor:pointer; font-weight:600; font-family:'Space Grotesk', sans-serif; transition:0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">Annuler</button>
           <a id="confirmDeleteBtn" href="#" style="flex:1; display:flex; align-items:center; justify-content:center; padding:0.8rem; background:#ef4444; color:white; border:none; border-radius:0.5rem; text-decoration:none; font-weight:600; box-shadow:0 0 20px rgba(239,68,68,0.3); transition:0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">Oui, supprimer</a>
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

<script src="../assets/js.js?v=2"></script>

</body>
</html>