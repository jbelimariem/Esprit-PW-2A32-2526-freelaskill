<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';

$produitController = new ProduitController();
$categoryController = new Category_prodController();

if (!isset($_GET['id'])) {
    header('Location: mes_ventes.php');
    exit;
}

$idProduit = (int)$_GET['id'];
$produit = $produitController->getByIdData($idProduit);

if (!$produit) {
    header('Location: mes_ventes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imagePath = $produit['image'];
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $imagePath = $produitController->uploadImageToCloudinary($_FILES['image']);
        } catch (Exception $e) {
            // Garder l'ancienne image si Cloudinary échoue.
        }
    }

    $price = max(1, (int) $_POST['price']);
    $data = [
        'nom'         => $_POST['title'],
        'description' => $_POST['description'],
        'prix'        => $price,
        'category_id' => $_POST['category'],
        'statut'      => $_POST['availability'],
        'stock'       => $produit['stock'],
        'image'       => $imagePath
    ];
    
    $produitController->updateData($idProduit, $data);
    header('Location: mes_ventes.php');
    exit;
}

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
        <!-- Card 1 : Modifier -->
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-pen"></i></div>
                <div class="mkt-profile-name">Modifier l'annonce</div>
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
                <a href="vendreproduit.php" class="nav-item">
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
                <div class="hero-tag"><i class="fa-solid fa-pen"></i> Modifier l'annonce</div>
                <h1 class="hero-title">Mettez à jour votre annonce</h1>
                <p class="hero-sub">Ajustez les informations de votre produit (prix, description, photos).</p>
            </div>
        </section>

        <div class="products-toolbar" style="flex-direction: column; align-items: stretch; gap: 1rem; margin-bottom: 2rem; width:100%;">
            <p class="result-count"><strong>Formulaire de modification</strong></p>
            <div class="toolbar-right" style="flex-wrap: wrap; gap: 1rem;">
                <button class="view-btn active" title="Modifier le produit"><i class="fa-solid fa-pen"></i></button>
            </div>
        </div>

        <div class="product-card" style="opacity: 1; max-width: 850px; margin: 0 auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <div class="card-body" style="padding: 2.5rem;">
                <form id="sell-form" action="" method="POST" enctype="multipart/form-data" novalidate style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    
                    <div style="grid-column: 1 / -1;">
                        <label for="title" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Titre du produit</label>
                        <input id="title" name="title" type="text" value="<?= htmlspecialchars($produit['nom']) ?>" class="price-input" style="width: 100%;">
                    </div>
                    
                    <div>
                        <label for="category" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Catégorie</label>
                        <select id="category" name="category" class="price-input" style="width: 100%;">
                            <option value="">Sélectionnez une catégorie</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['idCategory'] ?>" <?= $category['idCategory'] == $produit['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="price" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Prix (DT)</label>
                        <input id="price" name="price" type="number" min="1" value="<?= htmlspecialchars($produit['prix']) ?>" class="price-input" style="width: 100%;">
                    </div>
                    
                    <div style="grid-column: 1 / -1;">
                        <label for="description" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Description détaillée</label>
                        <textarea id="description" name="description" rows="5" class="price-input" style="width: 100%; resize: vertical; min-height: 120px;"> <?= htmlspecialchars($produit['description']) ?></textarea>
                    </div>
                    
                    <div style="grid-column: 1 / -1; margin-bottom: 0.5rem;">
                        <label for="product-image" style="display:block; margin-bottom:.8rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Photos du produit (laisser vide pour conserver l'actuelle)</label>
                        <input id="product-image" name="image" type="file" accept="image/png,image/jpeg,image/webp" hidden>
                        <label id="image-dropzone" for="product-image" style="border: 2px dashed rgba(59,130,246,0.3); border-radius: var(--radius-md); display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 2.5rem 1rem; background: rgba(59,130,246,0.02); color: #94A3B8; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(59,130,246,0.8)'; this.style.background='rgba(59,130,246,0.06)'" onmouseout="this.style.borderColor='rgba(59,130,246,0.3)'; this.style.background='rgba(59,130,246,0.02)'">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.5rem; color: var(--tech-blue); margin-bottom: 0.8rem;"></i>
                            <span id="image-prompt" style="font-size:0.95rem; margin-bottom: 0.4rem; color: white;">Glissez vos images ici ou <strong style="color:var(--tech-blue);">cliquez</strong> pour parcourir</span>
                            <span style="font-size:0.75rem; color: #64748b;">PNG, JPG, WEBP — Max 5Mo par fichier</span>
                            <img id="image-preview" src="<?= !empty($produit['image']) ? htmlspecialchars($produit['image']) : '' ?>" alt="Aperçu du produit" style="<?= !empty($produit['image']) ? 'display:block;' : 'display:none;' ?> width: 100%; max-width: 280px; margin-top: 1rem; border-radius: 1rem; object-fit: cover;" />
                        </label>
                    </div>
                    
                    <div>
                        <label for="availability" style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Disponibilité actuelle</label>
                        <select id="availability" name="availability" class="price-input" style="width: 100%;">
                            <option value="">Sélectionnez</option>
                            <option <?= $produit['statut'] === 'Immédiate' || $produit['statut'] === 'disponible' ? 'selected' : '' ?> value="disponible">Immédiate / Disponible</option>
                            <option <?= $produit['statut'] === 'Sous quelques jours' ? 'selected' : '' ?> value="Sous quelques jours">Sous quelques jours</option>
                        </select>
                    </div>
                    <div></div> <!-- Empty cell for alignment if needed, but we keep flex below -->
                    
                    <div style="grid-column: 1 / -1; display:flex; gap:1.5rem; flex-wrap:wrap; align-items:center; margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 2rem;">
                        <button type="submit" class="btn-submit" style="width:auto; padding: 0.9rem 2.5rem; font-size: 1.05rem; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); color: var(--tech-blue); border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);"><i class="fa-solid fa-save"></i> Enregistrer les modifications</button>
                        <a href="mes_ventes.php" style="color: var(--text-muted); font-size: 0.9rem; text-decoration: none; font-weight: 500;">Annuler</a>
                    </div>
                </form>
                <div id="sell-confirmation" style="display:none; margin-top:1.5rem; color: var(--tech-green); font-weight: 500; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 1rem; border-radius: var(--radius-sm); text-align: center;">
                    <i class="fa-solid fa-circle-check"></i> 
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const imageInput = document.getElementById('product-image');
const imageDropzone = document.getElementById('image-dropzone');
const imagePreview = document.getElementById('image-preview');
const imagePrompt = document.getElementById('image-prompt');

imageInput.addEventListener('change', () => {
    const file = imageInput.files[0];
    if (!file) {
        imagePreview.style.display = 'none';
        imagePrompt.textContent = 'Glissez vos images ici ou cliquez pour parcourir';
        return;
    }
    if (!file.type.startsWith('image/')) {
        imagePrompt.textContent = 'Fichier non valide. Choisissez une image PNG, JPG ou WEBP.';
        return;
    }
    const reader = new FileReader();
    reader.onload = (event) => {
        imagePreview.src = event.target.result;
        imagePreview.style.display = 'block';
        imagePrompt.textContent = file.name;
    };
    reader.readAsDataURL(file);
});

imageDropzone.addEventListener('dragover', (event) => {
    event.preventDefault();
    imageDropzone.style.borderColor = 'rgba(59,130,246,0.8)';
    imageDropzone.style.background = 'rgba(59,130,246,0.06)';
});

imageDropzone.addEventListener('dragleave', () => {
    imageDropzone.style.borderColor = 'rgba(59,130,246,0.3)';
    imageDropzone.style.background = 'rgba(59,130,246,0.02)';
});

imageDropzone.addEventListener('drop', (event) => {
    event.preventDefault();
    imageDropzone.style.borderColor = 'rgba(59,130,246,0.3)';
    imageDropzone.style.background = 'rgba(59,130,246,0.02)';
    const file = event.dataTransfer.files[0];
    if (file) {
        imageInput.files = event.dataTransfer.files;
        imageInput.dispatchEvent(new Event('change'));
    }
});
</script>
<script src="../assets/js.js?v=2"></script>
</body>
</html>