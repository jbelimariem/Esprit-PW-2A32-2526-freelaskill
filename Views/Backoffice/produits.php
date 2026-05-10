<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1;
}

$produitController = new ProduitController();
$categoryController = new Category_prodController();
$pendingProducts = $produitController->getByStatutData('pending');

if (isset($_GET['delete_id'])) {
    $produitController->deleteData($_GET['delete_id'], $_SESSION['admin_id']);
    header('Location: produits.php');
    exit();
}

$categories = $categoryController->getAllData();
$categoryNames = [];
foreach ($categories as $category) {
    $categoryNames[$category['idCategory']] = $category['nom'];
}

// L'admin ne voit/gère que ses propres produits (les achats qu'il souhaite modifier s'il l'a créé)
$produits = $produitController->getAllData();


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
    <title>Tous les produits | FreelaSkill Admin</title>
    <link rel="stylesheet" href="../assets/style.css?v=2">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="css.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-option {
            cursor: pointer;
            transition: 0.3s;
        }

        .filter-option:hover {
            background: rgba(255, 255, 255, 0.08) !important;
            color: white;
        }

        .filter-option.active {
            background: rgba(59, 130, 246, 0.2) !important;
            color: var(--tech-blue);
        }

        .filter-option.active .filter-dot {
            background: var(--tech-blue);
            box-shadow: 0 0 6px var(--tech-blue);
        }

        .product-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.01) 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
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
                <p style="font-size: 0.75rem; color: #475569; margin-top: 0.5rem; letter-spacing: 1px;">Admin Control
                    v1.0</p>
            </div>

            <a href="users_dashboard.php" class="nav-item active" style="text-decoration:none;"><i
                    class="fa-solid fa-users-viewfinder"></i> Gestion Users</a>
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
                <div class="admin-search" style="flex: 1; max-width: 500px;">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" id="main-search-input" placeholder="Rechercher par nom de produit...">
                </div>
                <div class="admin-top-actions">
                    <div class="admin-icon-btn theme-toggle-btn">
                        <i class="fa-regular fa-moon"></i>
                    </div>
                    <a href="notification.php" class="admin-icon-btn" style="text-decoration:none; position:relative;">
                        <i class="fa-regular fa-bell"></i>
                        <span class="badge-dot"
                            style="display:flex; align-items:center; justify-content:center; width:16px; height:16px; border-radius:50%; font-size:10px; font-weight:bold; top:-4px; right:-4px;"><?= isset($pendingProducts) ? count($pendingProducts) + 2 : 3 ?></span>
                    </a>
                    <div class="nav-avatar" style="margin-left: 0.5rem;">AH</div>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-header-row" style="margin-bottom: 2rem; flex-wrap: wrap;">
                    <h1 class="admin-page-title" style="margin-bottom:0;">Gestion complète des produits</h1>

                    <div style="display: flex; gap: 1rem; align-items:center; flex-wrap: wrap;">
                        <span class="result-count"
                            style="color: var(--text-muted); margin-right:1rem;"><strong><?= $totalProduitCount ?>
                                produits</strong> total</span>

                        <!-- Wrapper indispensable pour que js.js lance la fonction avec '.filter-section' -->
                        <div class="filter-section"
                            style="margin-bottom:0; display:flex; align-items:center; flex-wrap: wrap; gap:0.5rem;">
                            <div class="filter-title" style="display:none;">Catégorie</div>
                            <div class="filter-option active" data-filter="all"
                                style="display:inline-flex; align-items:center; border-radius:2rem; background:rgba(255,255,255,0.03); padding:0.4rem 1rem; font-size:0.85rem; border:1px solid rgba(255,255,255,0.1);">
                                <span>Tous les produits</span>
                                <span class="filter-dot" style="margin-left:8px;"></span>
                            </div>
                            <?php foreach ($categories as $category): ?>
                                <div class="filter-option" data-filter="<?= htmlspecialchars($category['nom']) ?>"
                                    style="display:inline-flex; align-items:center; border-radius:2rem; background:rgba(255,255,255,0.03); padding:0.4rem 1rem; font-size:0.85rem; border:1px solid rgba(255,255,255,0.1);">
                                    <span><?= htmlspecialchars($category['nom']) ?></span>
                                    <span class="filter-dot" style="margin-left:8px;"></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <select class="sort-select admin-btn-outline"
                            style="background: rgba(255,255,255,0.03); color:white; border: 1px solid var(--border); font-size:0.85rem;">
                            <option>Trier : Pertinence</option>
                            <option>Prix croissant</option>
                            <option>Prix décroissant</option>
                        </select>

                        <button onclick="exportToPDF()" class="admin-btn"
                            style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none; white-space: nowrap; font-size:0.85rem;">
                            <i class="fa-solid fa-file-pdf"></i> Exporter
                        </button>
                    </div>
                </div>

                <table class="data-table" style="display: none;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produits)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Aucun produit</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($produits as $produit): ?>
                                <tr>
                                    <td><?= $produit['idProduit'] ?></td>
                                    <td><?= htmlspecialchars($produit['nom']) ?></td>
                                    <td><?= htmlspecialchars($categoryNames[$produit['category_id']] ?? 'Autre') ?></td>
                                    <td><?= number_format($produit['prix'], 0, ',', ' ') ?> DT</td>
                                    <td><?= $produit['stock'] ?></td>
                                    <td><?= $produit['statut'] ?></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="products-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    <?php if (empty($produits)): ?>
                        <div class="product-card"
                            style="opacity: 0.9; width: 100%; text-align: center; padding: 4rem 2rem; grid-column: 1 / -1;">
                            <div class="card-body" style="padding:0;">
                                <div class="card-title" style="font-size:1.2rem; color:white;">Aucun produit dans la base de
                                    données</div>
                                <p style="color: var(--text-muted); margin-top: 1rem; margin-bottom:2rem;">Commencez par
                                    ajouter des produits à votre marketplace.</p>
                                <a href="ajouter_produit.php" class="admin-btn"><i class="fa-solid fa-plus"></i> Ajouter mon
                                    premier produit</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($produitsPagines as $produit):
                            $categoryName = $categoryNames[$produit['category_id']] ?? 'Autre';
                            
                            // Récupération de la disponibilité choisie par l'utilisateur
                            $dispoValue = $produit['disponibilite'] ?? 'Disponible maintenant';
                            $stockQty = (int)($produit['stock'] ?? 0);

                            // Logique de classe et texte (comme dans mes_ventes.php)
                            if ($stockQty <= 0) {
                                $stockClass = 'out-stock';
                                $stockText = 'Rupture';
                                $badgeColor = '#475569'; // Gris
                                $badgeText = 'RUPTURE';
                                $opacityStyle = 'opacity:0.6;';
                            } elseif ($dispoValue === 'Disponible maintenant') {
                                $stockClass = 'in-stock';
                                $stockText = 'Dispo. maintenant';
                                $badgeColor = '#10b981'; // Vert
                                $badgeText = 'EN STOCK';
                                $opacityStyle = '';
                            } elseif ($dispoValue === 'Non disponible') {
                                $stockClass = 'out-stock';
                                $stockText = 'Non disponible';
                                $badgeColor = '#475569';
                                $badgeText = 'INDISPONIBLE';
                                $opacityStyle = 'opacity:0.6;';
                            } else {
                                // Cas "Dans 2 semaines" ou "Dans 1 mois"
                                $stockClass = 'low-stock';
                                $stockText = $dispoValue;
                                $badgeColor = '#f59e0b'; // Orange
                                $badgeText = 'COMMANDE';
                                $opacityStyle = '';
                            }

                            // Priorité au badge "En attente" si non validé
                            if ($produit['statut'] === 'pending') {
                                $badgeColor = '#3b82f6'; // Bleu
                                $badgeText = 'EN ATTENTE';
                            }
                            $priceFormatted = number_format($produit['prix'], 0, ',', ' ');
                            $descriptionPreview = htmlspecialchars(mb_strimwidth($produit['description'], 0, 70, '...'));
                            ?>
                            <div class="product-card" data-id="<?= $produit['idProduit'] ?>" style="<?= $opacityStyle ?>">
                                <div class="card-image" style="cursor: pointer; height:180px;"
                                    onclick="window.location.href='detail_produit.php?id=<?= $produit['idProduit'] ?>'">
                                    <?php if (!empty($produit['image'])): ?>
                                        <img src="<?= htmlspecialchars($produit['image']) ?>"
                                            alt="<?= htmlspecialchars($produit['nom']) ?>"
                                            style="width:100%; height:100%; object-fit: cover; border-radius: 0;" />
                                    <?php else: ?>
                                        <i class="fa-solid fa-box" style="font-size: 3rem; color:var(--text-muted);"></i>
                                    <?php endif; ?>

                                    <span class="card-badge"
                                        style="background:<?= $badgeColor ?>; color:white; top:10px; right:auto; left:10px;"><?= $badgeText ?></span>
                                </div>
                                <div class="card-body" style="padding:1rem;">
                                    <div class="card-category" style="margin-bottom:0.25rem;">
                                        <?= htmlspecialchars($categoryName) ?>
                                    </div>
                                    <div class="card-title" style="margin-bottom:0.25rem; font-size:1.05rem;">
                                        <?= htmlspecialchars($produit['nom']) ?>
                                    </div>
                                    <div class="card-rating" style="margin-bottom: 0.8rem; display:none;">
                                        <!-- Keep for JS compat -->
                                    </div>

                                    <p
                                        style="color: var(--text-muted); font-size: 0.8rem; line-height: 1.5; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height:2.4rem;">
                                        <?= $descriptionPreview ?>
                                    </p>

                                    <div class="card-footer" style="padding-top:0.5rem;">
                                        <div class="price-block">
                                            <span class="price-main" style="font-size:1.1rem;"><?= $priceFormatted ?></span>
                                            <span class="price-currency">DT</span>
                                        </div>
                                        <div class="stock-info <?= $stockClass ?>"><span class="stock-dot"></span>
                                            <?= htmlspecialchars($stockText) ?></div>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                        <a href="detail_produit.php?id=<?= $produit['idProduit'] ?>" class="btn-action"
                                            title="Voir l'annonce"
                                            style="display:flex; align-items: center; justify-content: center; width:36px; height:36px; background: rgba(59,130,246,0.15); color: var(--tech-blue); border-radius:0.5rem; font-weight:600; text-decoration:none;"><i
                                                class="fa-solid fa-eye"></i></a>
                                        <button type="button" class="btn-action" title="Supprimer"
                                            style="display:flex; align-items: center; justify-content: center; width:36px; height:36px; background: rgba(239,68,68,0.1); color: #ef4444; border:none; border-radius:0.5rem; font-weight:600; cursor:pointer;"
                                            onclick="openDeleteModal(<?= $produit['idProduit'] ?>)"><i
                                                class="fa-solid fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <div class="pagination" style="margin-top: 3rem;">
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
        </main>
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
            <h3 style="font-size:1.3rem; font-weight:700; margin-bottom:0.75rem; color:white;">Supprimer le produit ?
            </h3>
            <p style="color:var(--text-muted); font-size:0.95rem; margin-bottom:2rem; line-height:1.5;">Êtes-vous sûr de
                vouloir supprimer définitivement ce produit de la base de données ? Cette action est irréversible.</p>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="../assets/pdf_export.js"></script>
    <script src="../assets/js.js?v=2"></script>
</body>

</html>