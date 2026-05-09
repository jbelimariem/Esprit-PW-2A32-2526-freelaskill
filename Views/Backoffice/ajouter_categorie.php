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
                <div style="margin: 1rem 0 0.5rem; font-size: 0.75rem; text-transform: uppercase; color: #475569; padding-left: 1rem; font-weight: 700; letter-spacing: 1px;">
                    Marketplace
                </div>
                <a href="ajouter_produit.php" class="admin-nav-item">
                    <i class="fa-solid fa-plus"></i> Ajouter Produit
                </a>
                <a href="produits.php" class="admin-nav-item">
                    <i class="fa-solid fa-list"></i> Liste des Produits
                </a>
                <a href="./pending_products.php" class="admin-nav-item">
                    <i class="fa-solid fa-clock"></i> Validation produits
                </a>
                <a href="./ajouter_categorie.php" class="admin-nav-item active">
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
                <a href="./notification.php" class="admin-nav-item">
                    <i class="fa-solid fa-bell"></i> Notifications
                    <span style="margin-left:auto; background:#ef4444; color:white; font-size:0.7rem; font-weight:bold; padding:2px 6px; border-radius:10px;"><?= isset($pendingProducts) ? count($pendingProducts) + 2 : 3 ?></span>
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
