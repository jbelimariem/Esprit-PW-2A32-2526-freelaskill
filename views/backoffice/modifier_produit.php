<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';

$produitController = new ProduitController();
$categoryController = new Category_prodController();
$pendingProducts = $produitController->getByStatutData('pending');
if (!isset($_GET['id'])) {
    header('Location: produits.php');
    exit;
}

$idProduit = (int)$_GET['id'];
$produit = $produitController->getByIdData($idProduit);

if (!$produit) {
    header('Location: produits.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imagePath = $produit['image'];
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $imagePath = $produitController->uploadImageToCloudinary($_FILES['image']);
        } catch (Exception $e) {
            // Garder l'ancienne image si Cloudinary échoue.
        }
    }

    $price = (int) $_POST['price'];
    $stock = (int) ($_POST['stock'] ?? -1);
    if ($price <= 0) {
        $errors[] = 'Le prix doit être supérieur à 0.';
    }

    if ($stock < 0) {
        $errors[] = 'Le stock doit etre superieur ou egal a 0.';
    }

    if (empty($errors)) {
        $data = [
            'nom'         => $_POST['title'],
            'description' => $_POST['description'],
            'prix'        => $price,
            'category_id' => $_POST['category'],
            'statut'      => $_POST['statut'] ?? $produit['statut'],
            'disponibilite' => $_POST['disponibilite'] ?? 'Disponible maintenant',
            'stock'       => $stock,
            'image'       => $imagePath
        ];

        $produitController->updateData($idProduit, $data);
        header('Location: produits.php');
        exit;
    }
}

$categories = $categoryController->getAllData();
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un produit | Admin</title>
    <link rel="stylesheet" href="../assets/style.css?v=3">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="css.css?v=3">
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
                    <h1 class="admin-page-title">Modifier un produit</h1>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="produits.php" class="admin-btn-outline" style="background: rgba(255,255,255,0.03);">
                            <i class="fa-solid fa-arrow-left"></i> Retour aux produits
                        </a>
                    </div>
                </div>

                <div class="admin-card" style="padding: 2rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 1rem;">
                    <?php if (!empty($errors)): ?>
                        <div style="margin-bottom:1.5rem; padding:1rem; background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.25); border-radius: 0.85rem; color: #f87171;">
                            <strong>Erreur :</strong>
                            <ul style="margin:0.5rem 0 0 1rem; padding:0; list-style: disc;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form id="admin-product-form" action="" method="POST" enctype="multipart/form-data" novalidate style="display:grid; gap:1.5rem;">
                        <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:1.5rem;">
                            <div>
                                <label for="title" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Titre du produit</label>
                                <input id="title" name="title" type="text" value="<?= htmlspecialchars($produit['nom']) ?>" class="price-input" style="width:100%;">
                            </div>
                            <div>
                                <label for="category" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Catégorie</label>
                                <select id="category" name="category" class="price-input" style="width:100%;">
                                    <option value="">Sélectionnez une catégorie</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['idCategory'] ?>" <?= $category['idCategory'] == $produit['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="price" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Prix (DT)</label>
                                <input id="price" name="price" type="number" min="1" value="<?= htmlspecialchars($produit['prix']) ?>" class="price-input" style="width:100%;">
                            </div>
                            <div>
                                <label for="stock" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Stock</label>
                                <input id="stock" name="stock" type="number" min="0" step="1" value="<?= htmlspecialchars($produit['stock']) ?>" class="price-input" style="width:100%;">
                            </div>
                            <div>
                                <label for="disponibilite" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Disponibilité actuelle</label>
                                <select id="disponibilite" name="disponibilite" class="price-input" style="width:100%;">
                                    <option value="Disponible maintenant" <?= ($produit['disponibilite'] ?? '') === 'Disponible maintenant' ? 'selected' : '' ?>>Disponible maintenant</option>
                                    <option value="Dans 2 semaines" <?= ($produit['disponibilite'] ?? '') === 'Dans 2 semaines' ? 'selected' : '' ?>>Dans 2 semaines</option>
                                    <option value="Dans 1 mois" <?= ($produit['disponibilite'] ?? '') === 'Dans 1 mois' ? 'selected' : '' ?>>Dans 1 mois</option>
                                    <option value="Non disponible" <?= ($produit['disponibilite'] ?? '') === 'Non disponible' ? 'selected' : '' ?>>Non disponible</option>
                                </select>
                                <input type="hidden" name="statut" value="<?= htmlspecialchars($produit['statut']) ?>">
                            </div>
                        </div>

                        <div>
                            <label for="description" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Description</label>
                            <textarea id="description" name="description" rows="6" class="price-input" style="width:100%; min-height:180px;"> <?= htmlspecialchars($produit['description']) ?></textarea>
                        </div>

                        <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:1.5rem; align-items:start;">
                            <div>
                                <label for="image" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Photo du produit</label>
                                <input id="image" name="image" type="file" accept="image/*" class="price-input" style="width:100%;" />
                                <?php if (!empty($produit['image'])): ?>
                                    <div style="margin-top:1rem; border:1px solid rgba(255,255,255,0.1); border-radius:0.75rem; overflow:hidden; max-width:320px;">
                                        <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>" style="width:100%; height:auto; display:block;" />
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="display:flex; flex-direction:column; gap:1rem;">
                                <div style="background: rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius: 1rem; padding: 1rem;">
                                    <p style="color:#94A3B8; margin:0 0 .75rem 0;">Stock enregistre</p>
                                    <div style="font-weight:700; font-size:1.5rem; color:white;"><?= htmlspecialchars($produit['stock']) ?></div>
                                    <p style="color:var(--text-muted); margin-top:.5rem;">Modifiez la quantite avec le champ Stock du formulaire.</p>
                                </div>
                                <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                                    <button type="submit" class="admin-btn" style="flex:1; min-width:160px;">Enregistrer</button>
                                    <a href="produits.php" class="admin-btn-outline" style="flex:1; min-width:160px; background: rgba(255,255,255,0.03);">Annuler</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js.js?v=2"></script>
</body>
</html>
