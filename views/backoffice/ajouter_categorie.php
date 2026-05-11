<?php
require_once __DIR__ . '/../../controllers/Category_prodController.php';
require_once __DIR__ . '/../../controllers/produitController.php';

$categoryController = new Category_prodController();
$productController = new ProduitController();
$pendingProducts = $productController->getByStatutData('pending');
$editingCategory = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!empty($_POST['action']) && $_POST['action'] === 'create' && $name !== '') {
        $categoryController->createData([
            'nom'         => $name,
            'description' => $description
        ]);
        header('Location: liste_categories.php');
        exit;
    }

    if (!empty($_POST['action']) && $_POST['action'] === 'update' && !empty($_POST['id'])) {
        $categoryController->updateData((int) $_POST['id'], [
            'nom'         => $name,
            'description' => $description
        ]);
        header('Location: liste_categories.php');
        exit;
    }
}

if (!empty($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $editingCategory = $categoryController->getByIdData((int) $_GET['id']);
}

?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editingCategory ? 'Modifier' : 'Ajouter' ?> Catégorie | FreelaSkill</title>
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
                <div class="admin-search">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" placeholder="Rechercher une catégorie">
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
                    <h1 class="admin-page-title"><?= $editingCategory ? 'Modifier la catégorie' : 'Ajouter une catégorie' ?></h1>
                    <div style="display: flex; gap: 1rem;">
                        <a href="liste_categories.php" class="admin-btn-outline" style="background: rgba(255,255,255,0.03);">
                            <i class="fa-solid fa-list"></i> Voir toutes les catégories
                        </a>
                    </div>
                </div>

                <div class="admin-grid" style="display: block; max-width: 600px; margin: 0 auto;">
                    <div class="glass-card admin-section">
                        <div class="section-title"><?= $editingCategory ? 'Modifier la catégorie' : 'Créer une nouvelle catégorie' ?></div>
                        
                        <!-- L'attribut required HTML5 a été supprimé. Le contrôle sera fait en JS -->
                        <form id="categoryForm" action="ajouter_categorie.php" method="POST" class="category-form">
                            <input type="hidden" name="action" value="<?= $editingCategory ? 'update' : 'create' ?>">
                            <?php if ($editingCategory): ?>
                                <input type="hidden" name="id" value="<?= $editingCategory['idCategory'] ?>">
                            <?php endif; ?>
                            
                            <label for="name">Nom de la catégorie</label>
                            <input id="name" name="name" type="text"
                                   value="<?= htmlspecialchars($editingCategory['nom'] ?? '') ?>"
                                   placeholder="Ex : Informatique">
                            <div id="nameError" style="color: #ef4444; font-size: 0.85rem; margin-top: -0.5rem; margin-bottom: 1rem; display: none;">Le nom de la catégorie est obligatoire.</div>
                            
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4"
                                      placeholder="Ex : Tous les produits électroniques et gadgets"><?= htmlspecialchars($editingCategory['description'] ?? '') ?></textarea>
                            
                            <div style="display:flex; gap:1rem; align-items:center; margin-top: 1rem;">
                                <button type="submit" class="btn btn-cart">
                                    <?= $editingCategory ? 'Mettre à jour' : 'Ajouter la catégorie' ?>
                                </button>
                                <?php if ($editingCategory): ?>
                                    <a href="liste_categories.php" class="btn btn-outline">Annuler</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Controle de saisie en JS -->
    <script>
        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            const nameInput = document.getElementById('name');
            const nameError = document.getElementById('nameError');
            
            if (nameInput.value.trim() === '') {
                e.preventDefault(); // Empêche l'envoi du formulaire
                nameInput.style.borderColor = '#ef4444';
                nameError.style.display = 'block';
                // Petite animation de secousse
                nameInput.style.animation = 'shake 0.4s';
                setTimeout(() => nameInput.style.animation = '', 400);
            } else {
                nameInput.style.borderColor = '';
                nameError.style.display = 'none';
            }
        });
        
        document.getElementById('name').addEventListener('input', function(e) {
            if(this.value.trim() !== '') {
                this.style.borderColor = '';
                document.getElementById('nameError').style.display = 'none';
            }
        });
    </script>
    <style>
        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
            100% { transform: translateX(0); }
        }
    </style>

    <script src="../assets/js.js"></script>
</body>
</html>
