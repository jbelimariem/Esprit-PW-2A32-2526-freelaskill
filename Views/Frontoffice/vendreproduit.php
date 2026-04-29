<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';

$controller = new ProduitController();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imagePath = '';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $imagePath = $controller->uploadImageToCloudinary($_FILES['image']);
        } catch (Exception $e) {
            $errors[] = 'Erreur lors de l\'upload sur Cloudinary : ' . $e->getMessage();
        }
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    $price = max(0, (int) ($_POST['price'] ?? 0));

    if ($title === '') {
        $errors[] = 'Le titre du produit est obligatoire.';
    }
    if ($description === '') {
        $errors[] = 'La description est obligatoire.';
    }
    if ($category === '') {
        $errors[] = 'La catégorie est obligatoire.';
    }
    if ($price <= 0) {
        $errors[] = 'Le prix doit être supérieur à 0.';
    }

    if (empty($errors)) {
        $data = [
            'nom' => $title,
            'description' => $description,
            'prix' => $price,
            'category_id' => $category,
            'statut' => 'pending',
            'stock' => 1,
            'image' => $imagePath,
            'user_id' => $_SESSION['admin_id'] ?? 1
        ];

        $controller->createData($data);
        header('Location: home.php');
        exit;
    }
}

$categoryController = new Category_prodController();
$categories = $categoryController->getAllData();
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendre un produit — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=6">
</head>
<body class="page-anim">

<nav>
    <div class="logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </div>
    <div class="nav-right">
        <button class="theme-toggle-btn" style="background: none; border: none; color: #e2e8f0; cursor: pointer; font-size: 1.2rem; padding: 0.5rem; display: flex; align-items: center; justify-content: center; transition: color 0.3s ease;" title="Toggle dark/light mode">
            <i class="fa-regular fa-moon"></i>
        </button>
        <a href="home.php" class="cart-btn" style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.15); color: white;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>
</nav>

<!-- MARKETPLACE LAYOUT -->
<div class="marketplace-layout">

    <!-- ── SIDEBAR ── -->
    <aside class="mkt-sidebar">
        <!-- Card 1 : Vendre -->
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-store"></i></div>
                <div class="mkt-profile-name">Vendre un produit</div>
                <div class="mkt-profile-sub">Marketplace FreelaSkill</div>
            </div>
        </div>

        <!-- Card 2 : Navigation -->
        <div class="mkt-sidebar-card">
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="home.php" class="nav-item">
                    <i class="fa-solid fa-store"></i> Tout parcourir
                </a>
                <a href="panier.php" class="nav-item">
                    <i class="fa-solid fa-cart-shopping"></i> Mon panier
                </a>
                <a href="mes_ventes.php" class="nav-item">
                    <i class="fa-solid fa-tag"></i> Mes ventes
                </a>
                <a href="vendreproduit.php" class="nav-item active">
                    <i class="fa-solid fa-plus-circle"></i> Vendre un produit
                </a>
            </div>
        </div>
    </aside>

    <!-- MAIN PANEL -->
    <div class="mkt-main">

        <!-- HERO -->
        <section class="hero-banner" style="padding: 2rem 2rem 3rem;">
            <div class="hero-glow"></div>
            <div class="hero-glow-2"></div>
            <div class="hero-content" style="margin: 0 auto; text-align: center; display: flex; flex-direction: column; align-items: center;">
                <div class="hero-tag"><i class="fa-solid fa-store"></i> Vendre un produit</div>
                <h1 class="hero-title">Publiez votre annonce en quelques minutes</h1>
                <p class="hero-sub">Complétez le formulaire ci-dessous pour vendre votre produit sur FreelaSkill.</p>
            </div>
        </section>

        <div class="products-toolbar" style="flex-direction: column; align-items: stretch; gap: 1rem; margin-bottom: 2rem; width:100%;">
            <p class="result-count"><strong>Formulaire de vente</strong></p>
            <div class="toolbar-right" style="flex-wrap: wrap; gap: 1rem;">
                <button class="view-btn active" title="Vendre un produit"><i class="fa-solid fa-upload"></i></button>
            </div>
        </div>

        <div class="product-card" style="opacity: 1; max-width: 850px; margin: 0 auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <div class="card-body" style="padding: 2.5rem;">
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
                    <form id="sell-form" action="vendreproduit.php" method="POST" enctype="multipart/form-data" novalidate style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div style="grid-column: 1 / -1;">
                        <label for="title" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Titre du produit</label>
                        <input id="title" name="title" type="text" placeholder="Ex. MacBook Pro 16 - Très bon état" class="price-input" style="width: 100%;">
                    </div>
                    
                    <div>
                        <label for="category" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Catégorie</label>
                        <select id="category" name="category" class="price-input" style="width: 100%;">
                            <option value="">Sélectionnez une catégorie</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['idCategory'] ?>"><?= htmlspecialchars($category['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="price" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Prix (DT)</label>
                        <input id="price" name="price" type="number" min="1" placeholder="Ex. 1450" class="price-input" style="width: 100%;">
                    </div>
                    
                    <div style="grid-column: 1 / -1;">
                        <label for="description" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Description détaillée</label>
                        <textarea id="description" name="description" rows="5" placeholder="Décrivez l'état, les caractéristiques techniques, les défauts éventuels et les informations concernant la remise ou livraison..." class="price-input" style="width: 100%; resize: vertical; min-height: 120px;"></textarea>
                    </div>
                    
                    <div style="grid-column: 1 / -1; margin-bottom: 0.5rem;">
                        <label for="product-image" style="display:block; margin-bottom:.8rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Photos du produit</label>
                        <input id="product-image" name="image" type="file" accept="image/png,image/jpeg,image/webp" hidden>
                        <label id="image-dropzone" for="product-image" style="border: 2px dashed rgba(59,130,246,0.3); border-radius: var(--radius-md); display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 2.5rem 1rem; background: rgba(59,130,246,0.02); color: #94A3B8; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(59,130,246,0.8)'; this.style.background='rgba(59,130,246,0.06)'" onmouseout="this.style.borderColor='rgba(59,130,246,0.3)'; this.style.background='rgba(59,130,246,0.02)'">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.5rem; color: var(--tech-blue); margin-bottom: 0.8rem;"></i>
                            <span id="image-prompt" style="font-size:0.95rem; margin-bottom: 0.4rem; color: white;">Glissez vos images ici ou <strong style="color:var(--tech-blue);">cliquez</strong> pour parcourir</span>
                            <span style="font-size:0.75rem; color: #64748b;">PNG, JPG, WEBP — Max 5Mo par fichier</span>
                            <img id="image-preview" alt="Aperçu du produit" style="display:none; width: 100%; max-width: 280px; margin-top: 1rem; border-radius: 1rem; object-fit: cover;" />
                        </label>
                        <div id="image-error-container" style="min-height:1.25rem; margin-top:0.5rem; color:#f87171; font-size:0.93rem; line-height:1.3;"></div>
                    </div>
                    
                    <div>
                        <label for="availability" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Disponibilité actuelle</label>
                        <select id="availability" name="availability" class="price-input" style="width: 100%;">
                            <option value="">Sélectionnez</option>
                            <option>Immédiate</option>
                            <option>Sous quelques jours</option>
                        </select>
                    </div>
                    <div></div> <!-- Empty cell for alignment if needed, but we keep flex below -->
                    
                    <div style="grid-column: 1 / -1; display:flex; gap:1.5rem; flex-wrap:wrap; align-items:center; margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 2rem;">
                        <button type="submit" class="btn-submit" style="width:auto; padding: 0.9rem 2.5rem; font-size: 1.05rem; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); color: var(--tech-blue); border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);"><i class="fa-solid fa-paper-plane"></i> Publier l'annonce</button>
                        <span style="color: var(--text-muted); font-size: 0.85rem;"><i class="fa-solid fa-shield-halved"></i> Vos coordonnées ne seront partagées qu'en cas d'accord.</span>
                    </div>
                </form>
                <div id="sell-confirmation" style="display:none; margin-top:1.5rem; color: var(--tech-green); font-weight: 500; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 1rem; border-radius: var(--radius-sm); text-align: center;">
                    <i class="fa-solid fa-circle-check"></i> 
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js.js?v=2"></script>
</body>
</html>