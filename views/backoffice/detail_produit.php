<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';

$produitController = new ProduitController();
$categoryController = new Category_prodController();
$pendingProducts = $produitController->getByStatutData('pending');
$produit = null;
$categoryName = 'Autre';
$stockClass = 'out-stock';
$stockText = 'Indisponible';
$priceFormatted = '0';

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
    }
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produit['nom'] ?? 'Détail produit') ?> | Admin</title>
    <link rel="stylesheet" href="../assets/style.css?v=3">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="css.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-detail-card {
            background: rgba(255,255,255,0.01);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: var(--radius-lg);
            padding: 2rem;
            display: grid;
            grid-template-columns: 450px 1fr;
            gap: 2rem;
            align-items: start;
        }
        .detail-image {
            width: 100%;
            height: 500px;
            background: rgba(0,0,0,0.3);
            border-radius: 1rem;
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(255,255,255,0.05);
        }
        @media (max-width: 1100px) {
            .admin-detail-card { grid-template-columns: 1fr; }
            .detail-image { height: 400px; }
        }
    </style>
</head>
<body class="page-anim">
    <div class="hero-glow" style="z-index: 0; position: fixed;"></div>
    <div class="hero-glow-2" style="z-index: 0; position: fixed; left: 20%; bottom: -150px; top: auto;"></div>

    <div class="admin-layout" style="position: relative; z-index: 1;">
                                        <aside class="sidebar">
            <div style="padding: 0 0.5rem; margin-bottom: 2rem;">
                <div class="logo">
                    <i class="fa-solid fa-shapes" style="color: #3b82f6;"></i>
                    Freela<span>Skill</span>
                </div>
                <p style="font-size: 0.75rem; color: #475569; margin-top: 0.5rem; letter-spacing: 1px;">Admin Control v1.0</p>
            </div>
            
            <a href="users_dashboard.php" class="nav-item active" style="text-decoration:none;"><i class="fa-solid fa-users-viewfinder"></i> Gestion Users</a>
            <a href="admin_missions.php" class="nav-item" style="text-decoration:none;"><i class="fa-solid fa-network-wired"></i> Flux de Missions</a>
            
            <div class="nav-item-wrapper">
                <a href="dashboard.php" class="nav-item" style="text-decoration:none;">
                    <i class="fa-solid fa-store"></i> Marketplace
                    <i class="fa-solid fa-chevron-right" style="margin-left:auto; font-size:0.7rem; opacity:0.5;"></i>
                </a>
                <div class="submenu">
                    <div class="submenu-title">Marketplace Admin</div>
                    <a href="dashboard.php" class="submenu-item">
                        <i class="fa-solid fa-chart-line"></i> Dashboard
                    </a>
                    <a href="produits.php" class="submenu-item">
                        <i class="fa-solid fa-box"></i> Gestion Produits
                    </a>
                    <a href="mes_achats.php" class="submenu-item">
                        <i class="fa-solid fa-user-tag"></i> Mes produits admin
                    </a>
                    <a href="pending_products.php" class="submenu-item">
                        <i class="fa-solid fa-clock"></i> Validation Produits
                    </a>
                    <a href="ajouter_produit.php" class="submenu-item">
                        <i class="fa-solid fa-plus"></i> Ajouter Produit
                    </a>
                    <a href="liste_categories.php" class="submenu-item">
                        <i class="fa-solid fa-list"></i> Liste Catégories
                    </a>
                    <a href="ajouter_categorie.php" class="submenu-item">
                        <i class="fa-solid fa-folder-plus"></i> Ajouter Catégorie
                    </a>
                    <a href="liste_commandes.php" class="submenu-item">
                        <i class="fa-solid fa-cart-shopping"></i> Commandes
                    </a>
                </div>
            </div>

            <div class="nav-item"><i class="fa-solid fa-shield-halved"></i> Securite</div>
<<<<<<< HEAD
            <a href="/freelaskill/messagerie_index.php?page=admin" class="nav-item" style="text-decoration:none;"><i class="fa-solid fa-comments"></i> Messagerie</a>
=======
            <div class="nav-item"><i class="fa-solid fa-comments"></i> Messagerie</div>
>>>>>>> 82705c67f6dd52e299a9ffa6fb62a7b16335bcf5

            <div style="margin-top: auto; padding-top: 2rem;">
                <a href="../frontoffice/home.php" class="btn btn-outline"
                   style="width:100%;font-size:.85rem;padding:.75rem;border-radius:999px;display:flex;align-items:center;justify-content:center;gap:.5rem; color: #ef4444; border-color: rgba(239,68,68,0.2);">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Retour au Hub
                </a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="admin-search" style="opacity: 0; pointer-events:none;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="...">
                </div>
                <div class="admin-top-actions">
                    <div class="admin-icon-btn theme-toggle-btn" style="cursor:pointer;" title="Basculer thème">
                        <i class="fa-regular fa-moon"></i>
                    </div>
                    <a href="notification.php" class="admin-icon-btn" style="text-decoration:none; position:relative;">
                        <i class="fa-regular fa-bell"></i>
                        <span class="badge-dot" style="display:flex; align-items:center; justify-content:center; width:16px; height:16px; border-radius:50%; font-size:10px; font-weight:bold; top:-4px; right:-4px;"><?= count($pendingProducts) + 2 ?></span>
                    </a>
                    <div class="nav-avatar" style="margin-left: 0.5rem;">AH</div>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-header-row" style="margin-bottom: 2rem;">
                    <h1 class="admin-page-title">Fiche produit - Backoffice</h1>
                    <div style="display: flex; gap: 1rem;">
                        <a href="produits.php" class="admin-btn-outline" style="background: rgba(255,255,255,0.03);">
                            <i class="fa-solid fa-arrow-left"></i> Retour aux produits
                        </a>
                    </div>
                </div>

                <?php if ($produit): ?>
                    <div class="admin-detail-card">
                        <div class="detail-image">
                            <?php if (!empty($produit['image'])): ?>
                                <img src="<?= htmlspecialchars($produit['image']) ?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; display:block;" />
                            <?php else: ?>
                                <div style="height: 400px; display:flex; align-items:center; justify-content:center;">
                                    <i class="fa-solid fa-image" style="font-size:4rem; color: rgba(255,255,255,0.2);"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($produit['statut'] === 'pending'): ?>
                                <div style="position:absolute; top:1rem; left:1rem; background:#f59e0b; color:white; padding:0.4rem 1rem; border-radius:2rem; font-size:0.85rem; font-weight:700;">EN ATTENTE DE VALIDATION</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="detail-info" style="display:flex; flex-direction:column; gap: 1.5rem;">
                            <div>
                                <div style="color:var(--tech-blue); font-weight:700; text-transform:uppercase; letter-spacing:1px; font-size:0.85rem; margin-bottom:0.5rem;">
                                    <?= htmlspecialchars($categoryName) ?>
                                </div>
                                <h2 style="font-size:2rem; color: white; margin-bottom:0.5rem; line-height:1.2;"><?= htmlspecialchars($produit['nom']) ?></h2>
                                <div style="color:var(--text-muted); font-size:0.9rem;">ID Produit: #<?= $produit['idProduit'] ?></div>
                            </div>

                            <div style="display:flex; gap: 2rem; align-items:center;">
                                <div style="font-size:2rem; font-weight:700; color:white;"><?= $priceFormatted ?> <span style="font-size:1.2rem; color:var(--text-muted);">DT</span></div>
                                <div style="padding:0.5rem 1rem; border-radius:0.5rem; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); display:flex; gap:0.5rem; align-items:center;">
                                    <div style="width:10px; height:10px; border-radius:50%; background:<?= $produit['stock'] > 2 ? '#10b981' : ($produit['stock'] > 0 ? '#f59e0b' : '#ef4444') ?>; box-shadow:0 0 10px <?= $produit['stock'] > 2 ? '#10b981' : ($produit['stock'] > 0 ? '#f59e0b' : '#ef4444') ?>;"></div>
                                    <span style="font-weight:600; color:white;"><?= htmlspecialchars($stockText) ?> (<?= $produit['stock'] ?>)</span>
                                </div>
                            </div>
                            
                            <div style="width:100%; height:1px; background:rgba(255,255,255,0.08);"></div>
                            
                            <div>
                                <h3 style="font-size:1.1rem; color:white; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;"><i class="fa-solid fa-align-left" style="color:var(--text-muted);"></i> Description</h3>
                                <p style="color:var(--text-muted); line-height:1.7; white-space:pre-wrap;"><?= htmlspecialchars($produit['description']) ?></p>
                            </div>
                            
                            <div style="width:100%; height:1px; background:rgba(255,255,255,0.08);"></div>
                            
                            <div>
                                <h3 style="font-size:1.1rem; color:white; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;"><i class="fa-solid fa-shield-halved" style="color:var(--text-muted);"></i> Statut de publication</h3>
                                <?php if ($produit['statut'] === 'disponible'): ?>
                                    <div style="display:inline-flex; align-items:center; gap:0.5rem; color:#10b981; font-weight:600; background:rgba(16,185,129,0.1); padding:0.5rem 1rem; border-radius:0.5rem;"><i class="fa-solid fa-check-circle"></i> Approuvé et en ligne</div>
                                <?php else: ?>
                                    <div style="display:inline-flex; align-items:center; gap:0.5rem; color:#f59e0b; font-weight:600; background:rgba(245,158,11,0.1); padding:0.5rem 1rem; border-radius:0.5rem;"><i class="fa-solid fa-clock"></i> En attente de modération</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="padding: 4rem; text-align: center; background: rgba(255,255,255,0.02); border-radius: 1rem; border: 1px dashed rgba(255,255,255,0.1);">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                        <h2 style="color:white; margin-bottom:1rem;">Produit introuvable</h2>
                        <p style="color: var(--text-muted); margin-bottom: 2rem;">Ce produit n'existe pas ou a été supprimé.</p>
                        <a href="produits.php" class="admin-btn">Retour aux produits</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
