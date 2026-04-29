<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';

$produitController = new ProduitController();
$categoryController = new Category_prodController();

if (!empty($_GET['product_action']) && !empty($_GET['id'])) {
    $productId = (int) $_GET['id'];
    if ($_GET['product_action'] === 'approve') {
        $produitController->updateStatutData($productId, 'disponible');
        header('Location: pending_products.php');
        exit;
    }
    if ($_GET['product_action'] === 'reject') {
        $produitController->deleteData($productId);
        header('Location: pending_products.php');
        exit;
    }
}

$categories    = $categoryController->getAllData();
$categoryNames = [];
foreach ($categories as $category) {
    $categoryNames[$category['idCategory']] = $category['nom'];
}
$pendingProducts = $produitController->getByStatutData('pending');

// Pagination
$itemsPerPage = 15;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalPages = ceil(count($pendingProducts) / $itemsPerPage);
$currentPage = min($currentPage, $totalPages > 0 ? $totalPages : 1);
$startIndex = ($currentPage - 1) * $itemsPerPage;
$pendingProductsPagines = array_slice($pendingProducts, $startIndex, $itemsPerPage);
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation produits | FreelaSkill</title>
    <link rel="stylesheet" href="../assets/style.css?v=2">
    <link rel="stylesheet" href="css.css?v=2">
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
                <a href="./pending_products.php" class="admin-nav-item active">
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
                    <input type="text" placeholder="Rechercher dans la validation produits">
                </div>
                <div class="admin-top-actions">
                    <div class="admin-icon-btn theme-toggle-btn" style="cursor:pointer;" title="Basculer thème">
                        <i class="fa-regular fa-moon"></i>
                    </div>
                    <div class="admin-icon-btn">
                        <i class="fa-regular fa-bell"></i>
                        <span class="badge-dot"></span>
                    </div>
                    <div class="nav-avatar" style="margin-left: 0.5rem;">AH</div>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-header-row">
                    <h1 class="admin-page-title">Validation des produits</h1>
                    <div style="display: flex; gap: 1rem;">
                        <a href="dashboard.php" class="admin-btn-outline" style="background: rgba(255,255,255,0.03);">
                            <i class="fa-solid fa-arrow-left"></i> Retour au dashboard
                        </a>
                        <button onclick="exportToPDF()" class="admin-btn" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none;">
                            <i class="fa-solid fa-file-pdf"></i> Exporter en PDF
                        </button>
                    </div>
                </div>

                <div class="glass-card admin-section" style="margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <div class="section-title" style="margin-bottom: 0.25rem;">En attente</div>
                            <p style="color: var(--text-muted);">Il y a <strong><?= count($pendingProducts) ?></strong> produit(s) en attente d’approbation.</p>
                        </div>
                    </div>
                    
                    <?php if (empty($pendingProducts)): ?>
                        <div style="padding: 3rem; text-align: center; color: var(--text-muted); background: rgba(255,255,255,0.02); border-radius: 1rem; border: 1px dashed rgba(255,255,255,0.1);">
                            <i class="fa-solid fa-check-circle" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem; opacity: 0.8;"></i>
                            <div style="font-size: 1.2rem; font-weight: 600;">Tout est à jour</div>
                            <p style="margin-top: 0.5rem;">Aucun produit n'est en attente d'approbation pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                            <?php foreach ($pendingProductsPagines as $pending): ?>
                                <div class="glass-card" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; border: 1px solid rgba(255,255,255,0.08); background: linear-gradient(145deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%); transition: transform 0.3s ease, box-shadow 0.3s ease; border-radius: 1.25rem;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.5)'" onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                                    
                                    <div style="align-self: flex-end; position: absolute; right: 1rem; top: 1rem; background: rgba(245, 158, 11, 0.2); color: #f59e0b; padding: 0.25rem 0.75rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600; z-index: 2;">
                                        En attente
                                    </div>

                                    <div style="width: 100%; height: 160px; border-radius: 1rem; overflow: hidden; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; position: relative;">
                                        <?php if (!empty($pending['image'])): ?>
                                            <img src="<?= htmlspecialchars($pending['image']) ?>" alt="<?= htmlspecialchars($pending['nom']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onerror="this.src='../assets/images/default-product.png'; this.onerror=null;" />
                                        <?php else: ?>
                                            <i class="fa-solid fa-box-open" style="font-size: 4rem; color: rgba(255,255,255,0.2);"></i>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div style="flex-grow: 1;">
                                        <div style="font-size: 0.75rem; color: #8b5cf6; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">
                                            <?= htmlspecialchars($categoryNames[$pending['category_id']] ?? 'Autre') ?>
                                        </div>
                                        <h3 style="font-size: 1.25rem; font-weight: 600; margin: 0 0 0.5rem 0; color: white;">
                                            <?= htmlspecialchars($pending['nom']) ?>
                                        </h3>
                                        <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?= htmlspecialchars(mb_strimwidth($pending['description'] ?? 'Pas de description.', 0, 100, '...')) ?>
                                        </p>
                                        
                                        <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                                            <div style="background: rgba(255,255,255,0.03); padding: 0.5rem 0.75rem; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                                <i class="fa-solid fa-tag" style="color: #94a3b8; font-size: 0.85rem;"></i>
                                                <span style="font-weight: 600; font-size: 0.95rem;"><?= number_format($pending['prix'], 0, ',', ' ') ?> DT</span>
                                            </div>
                                            <div style="background: rgba(255,255,255,0.03); padding: 0.5rem 0.75rem; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                                <i class="fa-solid fa-cubes" style="color: #94a3b8; font-size: 0.85rem;"></i>
                                                <span style="font-size: 0.9rem; color: var(--text-muted);">Stock: <span style="color: white; font-weight: 600;"><?= htmlspecialchars($pending['stock']) ?></span></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; gap: 0.75rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1.25rem; margin-top: auto;">
                                        <a href="./pending_products.php?product_action=approve&id=<?= $pending['idProduit'] ?>" class="btn" style="flex: 1; text-align: center; background: linear-gradient(135deg, #10b981 0%, #059669 100%); justify-content: center; gap: 0.5rem;">
                                            <i class="fa-solid fa-check"></i> Accepter
                                        </a>
                                        <a href="./pending_products.php?product_action=reject&id=<?= $pending['idProduit'] ?>" class="btn btn-danger js-delete-link" style="flex: 1; text-align: center; justify-content: center; gap: 0.5rem;" onclick="return confirm('Êtes-vous sûr de vouloir refuser ce produit ? Il sera supprimé.');">
                                            <i class="fa-solid fa-xmark"></i> Refuser
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <div class="pagination" style="margin-top: 3rem;">
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
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="../assets/pdf_export.js"></script>
    <script src="../assets/js.js?v=2"></script>
</body>
</html>

