<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';

$notifController = new NotificationController();
$unreadCount = $notifController->getUnreadCount(1);

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
        'statut'      => $produit['statut'],
        'disponibilite' => $_POST['disponibilite'] ?? 'Disponible maintenant',
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
    <title>Modifier l'annonce — FreelaSkill</title>
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
        <a href="notifications.php" class="cart-btn" style="position: relative; margin-right: 10px;">
            <i class="fa-solid fa-bell"></i>
            <?php if($unreadCount > 0): ?>
                <span class="cart-count" style="position:absolute;top:-6px;right:-6px;background:#3b82f6;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a href="home.php" class="cart-btn" style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.15); color: white;">
            <i class="fa-solid fa-arrow-left"></i> Boutique
        </a>
    </div>
</nav>

<div class="marketplace-layout">
    <aside class="mkt-sidebar">
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-pen"></i></div>
                <div class="mkt-profile-name">Modifier l'annonce</div>
                <div class="mkt-profile-sub">Marketplace FreelaSkill</div>
            </div>
        </div>

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
                <a href="mes_commandes.php" class="nav-item">
                    <i class="fa-solid fa-receipt"></i> Mes commandes
                </a>
                <a href="notifications.php" class="nav-item">
                    <i class="fa-solid fa-bell"></i> Notifications
                    <?php if($unreadCount > 0): ?>
                        <span style="background:#ef4444; color:white; border-radius:50%; width:18px; height:18px; font-size:10px; display:flex; align-items:center; justify-content:center; margin-left:auto;"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="vendreproduit.php" class="nav-item">
                    <i class="fa-solid fa-plus-circle"></i> Vendre un produit
                </a>
            </div>
        </div>
    </aside>

    <div class="mkt-main">
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
                    
                    <div style="grid-column: 1 / -1; margin-bottom: 0.5rem;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.5rem;">
                            <label for="description" style="margin-bottom:0; color:#94A3B8; font-size:.9rem; font-weight: 500;">Description détaillée</label>
                            <button type="button" id="toggle-ai-prompt" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); color: var(--tech-blue); padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Générer avec l'IA
                            </button>
                        </div>
                        
                        <!-- AI Prompt Input (Hidden by default) -->
                        <div id="ai-prompt-container" style="display: none; margin-bottom: 1rem; animation: slideDown 0.3s ease-out;">
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="text" id="ai-prompt-input" placeholder="Décrivez votre produit en quelques mots (ex: PC Gamer RTX 4090...)" style="flex: 1; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 0.75rem; border-radius: 8px; font-size: 0.9rem;">
                                <button type="button" id="generate-ai-btn" style="background: var(--tech-blue); border: none; color: white; padding: 0 1.25rem; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                    <i class="fa-solid fa-paper-plane" id="ai-btn-icon"></i> <span id="ai-btn-text">Générer</span>
                                </button>
                            </div>
                            <p id="ai-error" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.5rem; display: none;"></p>
                        </div>

                        <textarea id="description" name="description" rows="5" class="price-input" style="width: 100%; resize: vertical; min-height: 120px;"><?= htmlspecialchars($produit['description']) ?></textarea>
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
                    
                    <div style="grid-column: 1 / -1;">
                        <label style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Disponibilité</label>
                        <div class="mkt-sidebar-section" style="display:flex; flex-wrap:wrap; gap: 0.5rem;">
                            <input type="radio" id="dispo_immediate" name="disponibilite" value="Disponible maintenant" style="display:none;" <?= ($produit['disponibilite'] ?? 'Disponible maintenant') === 'Disponible maintenant' ? 'checked' : '' ?> />
                            <label for="dispo_immediate" class="filter-option <?= ($produit['disponibilite'] ?? 'Disponible maintenant') === 'Disponible maintenant' ? 'active' : '' ?>" style="cursor: pointer; margin-bottom: 0;">
                                <span><i class="fa-solid fa-circle-check" style="color:#10b981;margin-right:.4rem;"></i>Disponible</span>
                            </label>
                            
                            <input type="radio" id="dispo_deux_semaines" name="disponibilite" value="Dans 2 semaines" style="display:none;" <?= ($produit['disponibilite'] ?? '') === 'Dans 2 semaines' ? 'checked' : '' ?> />
                            <label for="dispo_deux_semaines" class="filter-option <?= ($produit['disponibilite'] ?? '') === 'Dans 2 semaines' ? 'active' : '' ?>" style="cursor: pointer; margin-bottom: 0;">
                                <span><i class="fa-solid fa-clock" style="color:#f59e0b;margin-right:.4rem;"></i>2 semaines</span>
                            </label>
                            
                            <input type="radio" id="dispo_un_mois" name="disponibilite" value="Dans 1 mois" style="display:none;" <?= ($produit['disponibilite'] ?? '') === 'Dans 1 mois' ? 'checked' : '' ?> />
                            <label for="dispo_un_mois" class="filter-option <?= ($produit['disponibilite'] ?? '') === 'Dans 1 mois' ? 'active' : '' ?>" style="cursor: pointer; margin-bottom: 0;">
                                <span><i class="fa-solid fa-clock" style="color:#f59e0b;margin-right:.4rem;"></i>1 mois</span>
                            </label>
                            
                            <input type="radio" id="dispo_non_disponible" name="disponibilite" value="Non disponible" style="display:none;" <?= ($produit['disponibilite'] ?? '') === 'Non disponible' ? 'checked' : '' ?> />
                            <label for="dispo_non_disponible" class="filter-option <?= ($produit['disponibilite'] ?? '') === 'Non disponible' ? 'active' : '' ?>" style="cursor: pointer; margin-bottom: 0;">
                                <span><i class="fa-solid fa-circle-xmark" style="color:#ef4444;margin-right:.4rem;"></i>Indisponible</span>
                            </label>
                        </div>
                    </div>
                    
                    <div style="grid-column: 1 / -1; display:flex; gap:1.5rem; flex-wrap:wrap; align-items:center; margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 2rem;">
                        <button type="submit" class="btn-submit" style="width:auto; padding: 0.9rem 2.5rem; font-size: 1.05rem; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); color: var(--tech-blue); border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);"><i class="fa-solid fa-save"></i> Enregistrer</button>
                        <a href="mes_ventes.php" style="color: var(--text-muted); font-size: 0.9rem; text-decoration: none; font-weight: 500;">Annuler</a>
                    </div>
                </form>
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
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
            imagePrompt.textContent = file.name;
        };
        reader.readAsDataURL(file);
    }
});

// AI Generation Logic
const toggleAiBtn = document.getElementById('toggle-ai-prompt');
const aiPromptContainer = document.getElementById('ai-prompt-container');
const aiPromptInput = document.getElementById('ai-prompt-input');
const generateAiBtn = document.getElementById('generate-ai-btn');
const aiError = document.getElementById('ai-error');
const aiBtnText = document.getElementById('ai-btn-text');
const aiBtnIcon = document.getElementById('ai-btn-icon');
const descriptionField = document.getElementById('description');

toggleAiBtn.addEventListener('click', () => {
    const isVisible = aiPromptContainer.style.display === 'block';
    aiPromptContainer.style.display = isVisible ? 'none' : 'block';
    if (!isVisible) aiPromptInput.focus();
});

generateAiBtn.addEventListener('click', async () => {
    const prompt = aiPromptInput.value.trim();
    if (!prompt) {
        aiError.textContent = "Veuillez entrer quelques mots-clés.";
        aiError.style.display = 'block';
        return;
    }

    aiError.style.display = 'none';
    aiBtnText.textContent = "Génération...";
    aiBtnIcon.className = "fa-solid fa-circle-notch fa-spin";
    generateAiBtn.disabled = true;

    try {
        const response = await fetch('api_generate_ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt: prompt })
        });

        const data = await response.json();

        if(data.error) {
            aiError.textContent = data.error;
            aiError.style.display = 'block';
        } else if(data.text) {
            descriptionField.value = data.text;
            aiPromptContainer.style.display = 'none';
            aiPromptInput.value = '';
            descriptionField.style.borderColor = 'var(--tech-blue)';
            setTimeout(() => descriptionField.style.borderColor = '', 1000);
        }
    } catch (error) {
        aiError.textContent = "Une erreur est survenue lors de la connexion à l'IA.";
        aiError.style.display = 'block';
    } finally {
        aiBtnText.textContent = "Générer";
        aiBtnIcon.className = "fa-solid fa-paper-plane";
        generateAiBtn.disabled = false;
    }
});

// Sync radio active state
document.querySelectorAll('input[name="disponibilite"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('label[for^="dispo_"]').forEach(l => l.classList.remove('active'));
        if(this.checked) document.querySelector('label[for="'+this.id+'"]').classList.add('active');
    });
});
</script>
<style>
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
</body>
</html>
