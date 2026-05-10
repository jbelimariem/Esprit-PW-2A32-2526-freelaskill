<?php
require_once __DIR__ . '/../../controllers/produitController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1;
}

$produitController = new ProduitController();
$pendingProducts = $produitController->getByStatutData('pending');

// Gestion de la suppression
if (isset($_GET['delete_id'])) {
    $produitController->deleteData($_GET['delete_id'], $_SESSION['admin_id']);
    header('Location: mes_achats.php');
    exit();
}

// On récupère les PRODUITS de l'admin (et non les commandes, comme souhaité par l'utilisateur)
$mesProduits = $produitController->getAllAdminData($_SESSION['admin_id']);

// Pagination
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalPages = ceil(count($mesProduits) / $itemsPerPage);
$currentPage = min($currentPage, $totalPages > 0 ? $totalPages : 1);
$startIndex = ($currentPage - 1) * $itemsPerPage;
$mesProduitsPagines = array_slice($mesProduits, $startIndex, $itemsPerPage);

?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Achats (Mes Produits) | FreelaSkill</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <div class="nav-item"><i class="fa-solid fa-network-wired"></i> Flux de Missions</div>
            
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
            <div class="nav-item"><i class="fa-solid fa-comments"></i> Messagerie</div>

            <div style="margin-top: auto; padding-top: 2rem;">
                <a href="../frontoffice/home.php" class="btn btn-outline"
                   style="width:100%;font-size:.85rem;padding:.75rem;border-radius:999px;display:flex;align-items:center;justify-content:center;gap:.5rem; color: #ef4444; border-color: rgba(239,68,68,0.2);">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Retour au Hub
                </a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="admin-search">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
                <div class="admin-top-actions">
                    <div class="admin-icon-btn theme-toggle-btn" style="cursor:pointer;" title="Basculer thème">
                        <i class="fa-regular fa-moon"></i>
                    </div>
                    <a href="notification.php" class="admin-icon-btn" style="text-decoration:none; position:relative;">
                        <i class="fa-regular fa-bell"></i>
                        <span class="badge-dot" style="display:flex; align-items:center; justify-content:center; width:16px; height:16px; border-radius:50%; font-size:10px; font-weight:bold; top:-4px; right:-4px;"><?= isset($pendingProducts) ? count($pendingProducts) + 2 : 3 ?></span>
                    </a>
                    <div class="nav-avatar" style="margin-left: 0.5rem;">AH</div>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-header-row">
                    <h1 class="admin-page-title">Mes Achats (Mes Produits)</h1>
                </div>

                <div class="admin-grid" style="display: block;">
                    <div class="glass-card admin-section">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID Produit</th>
                                    <th>Image</th>
                                    <th>Titre</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($mesProduits)): ?>
                                    <tr><td colspan="7" style="color: var(--text-muted); text-align: center; padding: 2rem;">Vous n'avez aucun produit enregistré.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($mesProduitsPagines as $prod): ?>
                                        <tr>
                                            <td>#<?= $prod['idProduit'] ?></td>
                                            <td>
                                                <?php if(!empty($prod['image'])): ?>
                                                    <img src="<?= htmlspecialchars($prod['image']) ?>" alt="" style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($prod['nom']) ?></td>
                                            <td><strong style="color: white;"><?= htmlspecialchars($prod['prix']) ?> DT</strong></td>
                                            <td>
                                                <span class="badge <?= $prod['statut'] === 'disponible' ? 'badge-success' : 'badge-warning' ?>">
                                                    <?= htmlspecialchars($prod['statut']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($prod['stock']) ?>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <a href="./modifier_produit.php?id=<?= $prod['idProduit'] ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; white-space: nowrap;" title="Modifier">
                                                        <i class="fa-solid fa-pen"></i> Modifier
                                                    </a>
                                                    <a href="?delete_id=<?= $prod['idProduit'] ?>&page=<?= $currentPage ?>" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; white-space: nowrap;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');" title="Supprimer">
                                                        <i class="fa-solid fa-trash"></i> Supprimer
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination" style="margin-top: 2rem;">
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
        </main>
    </div>

    <script src="../assets/js.js"></script>
</body>
</html>
