<?php
require_once __DIR__ . '/../../controllers/Category_prodController.php';

$categoryController = new Category_prodController();

if (!empty($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $categoryController->deleteData((int) $_GET['id']);
    header('Location: liste_categories.php');
    exit;
}

$categories = $categoryController->getAllData();

// Pagination
$itemsPerPage = 20;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalPages = ceil(count($categories) / $itemsPerPage);
$currentPage = min($currentPage, $totalPages > 0 ? $totalPages : 1);
$startIndex = ($currentPage - 1) * $itemsPerPage;
$categoriesPagines = array_slice($categories, $startIndex, $itemsPerPage);
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Catégories | FreelaSkill</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="css.css">
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
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-chart-line"></i> Analytics
                </a>
                <div style="margin: 1rem 0 0.5rem; font-size: 0.75rem; text-transform: uppercase; color: #475569; padding-left: 1rem; font-weight: 700; letter-spacing: 1px;">
                    Marketplace
                </div>
                <a href="produits.php" class="admin-nav-item">
                    <i class="fa-solid fa-box-open"></i> Produits
                </a>
                <a href="./pending_products.php" class="admin-nav-item">
                    <i class="fa-solid fa-clock"></i> Validation produits
                </a>
                <a href="./ajouter_categorie.php" class="admin-nav-item">
                    <i class="fa-solid fa-plus"></i> Ajouter Catégorie
                </a>
                <a href="./liste_categories.php" class="admin-nav-item active">
                    <i class="fa-solid fa-list"></i> Liste des Catégories
                </a>
                <a href="./mes_achats.php" class="admin-nav-item">
                    <i class="fa-solid fa-bag-shopping"></i> Mes Achats
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-cart-shopping"></i> Commandes
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-users"></i> Clients
                </a>
                <div style="margin: 1rem 0 0.5rem; font-size: 0.75rem; text-transform: uppercase; color: #475569; padding-left: 1rem; font-weight: 700; letter-spacing: 1px;">
                    Utilisateurs & rôles
                </div>
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-user-tie"></i> Freelancers
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-user-graduate"></i> Étudiants
                </a>
                <div style="margin: 1rem 0 0.5rem; font-size: 0.75rem; text-transform: uppercase; color: #475569; padding-left: 1rem; font-weight: 700; letter-spacing: 1px;">
                    Paramètres
                </div>
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-gear"></i> Général
                </a>
            </div>

            <div style="padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.08);">
                <a href="dashboard.php" class="admin-nav-item" style="color: #ef4444; padding: 0.75rem;">
                    <i class="fa-solid fa-arrow-left"></i> Retour au dashboard
                </a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="admin-search">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" placeholder="Rechercher une catégorie">
                </div>
                <div class="admin-top-actions">
                    <div class="admin-icon-btn">
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
                    <h1 class="admin-page-title">Liste des catégories</h1>
                    <div style="display: flex; gap: 1rem;">
                        <a href="ajouter_categorie.php" class="admin-btn">
                            <i class="fa-solid fa-plus"></i> Ajouter une catégorie
                        </a>
                    </div>
                </div>

                <div class="admin-grid" style="display: block;">
                    <div class="glass-card admin-section">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th style="min-width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr><td colspan="4" style="color: var(--text-muted); text-align: center; padding: 2rem;">Aucune catégorie enregistrée.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($categoriesPagines as $category): ?>
                                        <tr>
                                            <td><?= $category['idCategory'] ?></td>
                                            <td><strong style="color:white;"><?= htmlspecialchars($category['nom']) ?></strong></td>
                                            <td><span style="color: var(--text-muted);"><?= htmlspecialchars($category['description']) ?></span></td>
                                            <td>
                                                <a href="ajouter_categorie.php?action=edit&id=<?= $category['idCategory'] ?>" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;"><i class="fa-solid fa-pen"></i> Modifier</a>
                                                <a href="liste_categories.php?action=delete&id=<?= $category['idCategory'] ?>" class="btn btn-danger js-delete-link" style="padding: 0.4rem 1rem; font-size: 0.85rem;" onclick="return confirm('Vraiment supprimer cette catégorie ?');"><i class="fa-solid fa-trash"></i> Supprimer</a>
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
