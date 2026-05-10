<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';

$produitController = new ProduitController();
$categoryController = new Category_prodController();
$notifController = new NotificationController();

if (!empty($_GET['product_action']) && !empty($_GET['id'])) {
    $productId = (int) $_GET['id'];
    $targetProduct = $produitController->getByIdData($productId);
    if ($_GET['product_action'] === 'approve') {
        $produitController->updateStatutData($productId, 'disponible');
        if ($targetProduct && !empty($targetProduct['user_id'])) {
            $notifController->createData(
                (int)$targetProduct['user_id'],
                "Votre produit \"" . $targetProduct['nom'] . "\" est maintenant disponible sur le marketplace.",
                'product_approved'
            );
        }
        header('Location: pending_products.php');
        exit;
    }
    if ($_GET['product_action'] === 'reject') {
        if ($targetProduct && !empty($targetProduct['user_id'])) {
            $notifController->createData(
                (int)$targetProduct['user_id'],
                "Votre produit \"" . $targetProduct['nom'] . "\" a ete refuse.",
                'product_rejected'
            );
        }
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
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="css.css?v=2">
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
                    <input type="text" placeholder="Rechercher dans la validation produits">
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
                                    
                                    <!-- Smart Moderator AI Section -->
                                    <div id="ai-mod-<?= $pending['idProduit'] ?>" style="background: rgba(59, 130, 246, 0.03); border: 1px solid rgba(59, 130, 246, 0.1); border-radius: 0.75rem; padding: 0.75rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Smart Moderator</span>
                                            <button onclick="moderateProduct(<?= $pending['idProduit'] ?>, '<?= base64_encode($pending['nom'] . ' ' . $pending['description']) ?>', true)" class="admin-btn" style="padding: 0.2rem 0.6rem; font-size: 0.7rem; background: rgba(59, 130, 246, 0.15); border-color: rgba(59, 130, 246, 0.2); color: #60a5fa;">
                                                <i class="fa-solid fa-wand-magic-sparkles"></i> Analyser
                                            </button>
                                        </div>
                                        <div id="ai-res-<?= $pending['idProduit'] ?>" style="font-size: 0.8rem; color: #cbd5e1; display: none; line-height: 1.4;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                                <span class="ai-status-badge"></span>
                                                <span class="ai-score-label"></span>
                                            </div>
                                            <p class="ai-reason-text" style="margin: 0; color: #94a3b8; font-style: italic;"></p>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; gap: 0.75rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1.25rem; margin-top: auto;">
                                        <a href="./pending_products.php?product_action=approve&id=<?= $pending['idProduit'] ?>" class="btn" style="flex: 1; text-align: center; background: linear-gradient(135deg, #10b981 0%, #059669 100%); justify-content: center; gap: 0.5rem;">
                                            <i class="fa-solid fa-check"></i> Accepter
                                        </a>
                                         <a href="#" class="btn btn-danger js-delete-link" style="flex: 1; text-align: center; justify-content: center; gap: 0.5rem;" onclick="openRejectModal(<?= $pending['idProduit'] ?>); return false;">
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
    <script>
    let rejectId = null;
    function openRejectModal(id) {
        rejectId = id;
        document.getElementById('rejectModal').style.display = 'flex';
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }
    function confirmReject() {
        if (rejectId) {
            window.location.href = `./pending_products.php?product_action=reject&id=${rejectId}`;
        }
    }

    async function moderateProduct(id, text, isBase64 = false) {
        if (isBase64) {
            try {
                // Handle UTF-8 correctly
                const binaryString = atob(text);
                const bytes = new Uint8Array(binaryString.length);
                for (let i = 0; i < binaryString.length; i++) {
                    bytes[i] = binaryString.charCodeAt(i);
                }
                text = new TextDecoder().decode(bytes);
            } catch(e) {
                text = atob(text);
            }
        }
        const resDiv = document.getElementById('ai-res-' + id);
        const modDiv = document.getElementById('ai-mod-' + id);
        const btn = modDiv.querySelector('button');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Analyse...';
        
        try {
            const response = await fetch('api_moderate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: text })
            });
            
            const data = await response.json();
            
            resDiv.style.display = 'block';
            const badge = resDiv.querySelector('.ai-status-badge');
            const scoreLabel = resDiv.querySelector('.ai-score-label');
            const reasonText = resDiv.querySelector('.ai-reason-text');
            
            if (data.error) {
                badge.style.display = 'none';
                scoreLabel.textContent = "Erreur: " + data.error;
                reasonText.textContent = "";
            } else {
                badge.style.display = 'inline-block';
                badge.textContent = data.status === 'APPROVED' ? 'VALIDE' : 'RISQUE';
                badge.style.background = data.status === 'APPROVED' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)';
                badge.style.color = data.status === 'APPROVED' ? '#10b981' : '#ef4444';
                badge.style.padding = '0.1rem 0.4rem';
                badge.style.borderRadius = '0.25rem';
                badge.style.fontSize = '0.65rem';
                badge.style.fontWeight = '700';
                
                scoreLabel.textContent = 'Confiance: ' + data.score + '%';
                reasonText.textContent = '« ' + data.reason + ' »';
            }
        } catch (e) {
            alert('Erreur lors de la modération IA');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Analyser';
        }
    }
    </script>

    <!-- Custom Reject Modal -->
    <div id="rejectModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem;">
        <div class="glass-card" style="max-width: 450px; width: 100%; padding: 2rem; border: 1px solid rgba(239, 68, 68, 0.2); text-align: center;">
            <div style="width: 64px; height: 64px; background: rgba(239, 68, 68, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.75rem; color: #ef4444;"></i>
            </div>
            <h2 style="font-size: 1.5rem; margin-bottom: 0.75rem; color: white;">Confirmer le refus ?</h2>
            <p style="color: #94a3b8; margin-bottom: 2rem; line-height: 1.6;">Êtes-vous sûr de vouloir refuser ce produit ? Cette action est irréversible et le produit sera supprimé.</p>
            <div style="display: flex; gap: 1rem;">
                <button onclick="closeRejectModal()" class="admin-btn-outline" style="flex: 1; justify-content: center;">Annuler</button>
                <button onclick="confirmReject()" class="admin-btn" style="flex: 1; background: #ef4444; border: none; justify-content: center;">Confirmer le refus</button>
            </div>
        </div>
    </div>
</body>
</html>

