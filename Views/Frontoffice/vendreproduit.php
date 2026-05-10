<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user = null;
$hasAvatar = false;
$avatarUrl = '';
$initials = '';

if (!empty($_SESSION['user_id'])) {
    $userController = new UserController();
    $user = $userController->getById((int) $_SESSION['user_id']);
    if ($user) {
        $initials = strtoupper(mb_substr($user->getPrenom(), 0, 1) . mb_substr($user->getNom(), 0, 1));
        $avatar = trim((string) $user->getAvatar());
        if ($avatar !== '') {
            if (strpos($avatar, 'http') === 0) {
                $avatarUrl = $avatar;
            } else {
                $avatarUrl = '../../' . ltrim(str_replace('\\', '/', $avatar), '/');
            }
            $hasAvatar = true;
        }
    }
}

$notifController = new NotificationController();
$currentUserId = (int) $_SESSION['user_id'];
$unreadCount = $notifController->getUnreadCount($currentUserId);

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
    $stock = (int) ($_POST['stock'] ?? -1);

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

    if ($stock < 0) {
        $errors[] = 'Le stock doit etre superieur ou egal a 0.';
    }

    if (empty($errors)) {
        $data = [
            'nom' => $title,
            'description' => $description,
            'prix' => $price,
            'category_id' => $category,
            'statut' => 'pending',
            'disponibilite' => $_POST['disponibilite'] ?? 'Disponible maintenant',
            'stock' => $stock,
            'image' => $imagePath,
            'user_id' => $currentUserId
        ];

        $newProductId = $controller->createData($data);
        $notifController->createData(
            $currentUserId,
            "Votre produit \"" . $title . "\" a ete envoye en validation.",
            'product_pending'
        );
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
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=6">
    <script src="../assets/theme.js" defer></script>
</head>

<body class="page-anim">

    <nav>
        <div class="logo">
            <i class="fa-solid fa-shapes"></i>
            Freela<span>Skill</span>
        </div>
        <ul class="nav-links">
            <li><a href="#">Accueil</a></li>
            <?php if (empty($_SESSION['role']) || $_SESSION['role'] !== 'freelancer'): ?>
        <li><a href="#">Missions</a></li>
        <?php endif; ?>
            <li><a href="home.php" class="active">Marketplace</a></li>
            <?php if (empty($_SESSION['role']) || $_SESSION['role'] !== 'client'): ?>
        <li><a href="#">Freelancers</a></li>
        <?php endif; ?>
            <li><a href="profile.php">Mon Profil</a></li>
        </ul>
        <div class="nav-right">
            <button type="button" class="theme-toggle" data-theme-toggle>
                <i class="fa-solid fa-sun" data-theme-icon></i>
                <span data-theme-label>Jour</span>
            </button>

            <a href="notifications.php" class="cart-btn"
                style="position: relative; margin-right: 10px; color: var(--text-muted);">
                <i class="fa-solid fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="cart-count"
                        style="position:absolute;top:-6px;right:-6px;background:#3b82f6;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>
            <a href="panier.php" class="cart-btn"
                style="position: relative; margin-right: 15px; color: var(--text-muted);">
                <i class="fa-solid fa-bag-shopping"></i>
                <span class="cart-count"
                    style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);">0</span>
            </a>

            <?php if ($user): ?>
                <div class="nav-avatar<?php echo $hasAvatar ? ' has-image' : ''; ?>"
                    title="<?php echo htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()); ?>">
                    <?php if ($hasAvatar): ?>
                        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Photo de profil" class="nav-avatar-image">
                    <?php else: ?>
                        <?php echo $initials; ?>
                    <?php endif; ?>
                </div>
                <a href="logout.php" class="btn btn-outline"
                    style="font-size:0.82rem; padding:0.45rem 1rem; margin-left: 10px;" title="Déconnexion">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary" style="font-size:0.82rem; padding:0.45rem 1rem;">
                    Connexion
                </a>
            <?php endif; ?>
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
                    <a href="mes_commandes.php" class="nav-item">
                        <i class="fa-solid fa-receipt"></i> Mes commandes
                    </a>
                    <a href="notifications.php" class="nav-item">
                        <i class="fa-solid fa-bell"></i> Notifications
                        <?php if ($unreadCount > 0): ?>
                            <span
                                style="background:#ef4444; color:white; border-radius:50%; width:18px; height:18px; font-size:10px; display:flex; align-items:center; justify-content:center; margin-left:auto;"><?= $unreadCount ?></span>
                        <?php endif; ?>
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
                <div class="hero-content"
                    style="margin: 0 auto; text-align: center; display: flex; flex-direction: column; align-items: center;">
                    <div class="hero-tag"><i class="fa-solid fa-store"></i> Vendre un produit</div>
                    <h1 class="hero-title">Publiez votre annonce en quelques minutes</h1>
                    <p class="hero-sub">Complétez le formulaire ci-dessous pour vendre votre produit sur FreelaSkill.
                    </p>
                </div>
            </section>

            <div class="products-toolbar"
                style="flex-direction: column; align-items: stretch; gap: 1rem; margin-bottom: 2rem; width:100%;">
                <p class="result-count"><strong>Formulaire de vente</strong></p>
                <div class="toolbar-right" style="flex-wrap: wrap; gap: 1rem;">
                    <button class="view-btn active" title="Vendre un produit"><i
                            class="fa-solid fa-upload"></i></button>
                </div>
            </div>

            <div class="product-card"
                style="opacity: 1; max-width: 850px; margin: 0 auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
                <div class="card-body" style="padding: 2.5rem;">
                    <?php if (!empty($errors)): ?>
                        <div
                            style="margin-bottom:1.5rem; padding:1rem; background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.25); border-radius: 0.85rem; color: #f87171;">
                            <strong>Erreur :</strong>
                            <ul style="margin:0.5rem 0 0 1rem; padding:0; list-style: disc;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form id="sell-form" action="vendreproduit.php" method="POST" enctype="multipart/form-data"
                        novalidate style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div style="grid-column: 1 / -1;">
                            <label for="title"
                                style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Titre
                                du produit</label>
                            <input id="title" name="title" type="text" placeholder="Ex. MacBook Pro 16 - Très bon état"
                                class="price-input" style="width: 100%;">
                        </div>

                        <div>
                            <label for="category"
                                style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Catégorie</label>
                            <select id="category" name="category" class="price-input" style="width: 100%;">
                                <option value="">Sélectionnez une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['idCategory'] ?>"><?= htmlspecialchars($category['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="price"
                                style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Prix
                                (DT)</label>
                            <input id="price" name="price" type="number" min="1" placeholder="Ex. 1450"
                                class="price-input" style="width: 100%;">
                        </div>

                        <div>
                            <label for="stock"
                                style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Stock</label>
                            <input id="stock" name="stock" type="number" min="0" step="1" placeholder="Ex. 3"
                                value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>"
                                class="price-input" style="width: 100%;">
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: .5rem;">
                                <label for="description"
                                    style="color:#94A3B8; font-size:.9rem; font-weight: 500;">Description
                                    détaillée</label>
                                <button type="button" id="toggle-ai-btn"
                                    style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); color: var(--tech-blue); padding: 0.3rem 0.8rem; border-radius: 0.5rem; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.3s;">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Générer avec IA
                                </button>
                            </div>

                            <!-- AI Prompt Input (Hidden by default) -->
                            <div id="ai-prompt-container"
                                style="display: none; margin-bottom: 1rem; padding: 1rem; background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.1); border-radius: 0.8rem; animation: slideDown 0.3s ease;">
                                <p style="font-size: 0.8rem; color: #cbd5e1; margin-bottom: 0.5rem;">Décrivez brièvement
                                    le produit (ex: iPhone 13 bleu, 128Go, batterie 90%)</p>
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="text" id="ai-prompt-input" placeholder="Quel produit vendez-vous ?"
                                        class="price-input" style="flex: 1; margin-bottom: 0;">
                                    <button type="button" id="generate-ai-btn"
                                        style="background: var(--tech-blue); color: white; border: none; padding: 0 1.2rem; border-radius: 0.5rem; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                        <span id="ai-btn-text">Générer</span>
                                        <i id="ai-btn-icon" class="fa-solid fa-paper-plane"></i>
                                    </button>
                                </div>
                                <div id="ai-error"
                                    style="color: #ef4444; font-size: 0.75rem; margin-top: 0.5rem; display: none;">
                                </div>
                            </div>

                            <textarea id="description" name="description" rows="5"
                                placeholder="Décrivez l'état, les caractéristiques techniques, les défauts éventuels..."
                                class="price-input"
                                style="width: 100%; resize: vertical; min-height: 120px;"></textarea>
                        </div>

                        <div style="grid-column: 1 / -1; margin-bottom: 0.5rem;">
                            <label for="product-image"
                                style="display:block; margin-bottom:.8rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Photos
                                du produit</label>
                            <input id="product-image" name="image" type="file" accept="image/png,image/jpeg,image/webp"
                                hidden>
                            <label id="image-dropzone" for="product-image"
                                style="border: 2px dashed rgba(59,130,246,0.3); border-radius: var(--radius-md); display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 2.5rem 1rem; background: rgba(59,130,246,0.02); color: #94A3B8; cursor: pointer; transition: all 0.3s;"
                                onmouseover="this.style.borderColor='rgba(59,130,246,0.8)'; this.style.background='rgba(59,130,246,0.06)'"
                                onmouseout="this.style.borderColor='rgba(59,130,246,0.3)'; this.style.background='rgba(59,130,246,0.02)'">
                                <i class="fa-solid fa-cloud-arrow-up"
                                    style="font-size: 2.5rem; color: var(--tech-blue); margin-bottom: 0.8rem;"></i>
                                <span id="image-prompt"
                                    style="font-size:0.95rem; margin-bottom: 0.4rem; color: white;">Glissez vos images
                                    ici ou <strong style="color:var(--tech-blue);">cliquez</strong> pour
                                    parcourir</span>
                                <span style="font-size:0.75rem; color: #64748b;">PNG, JPG, WEBP — Max 5Mo par
                                    fichier</span>
                                <img id="image-preview" alt="Aperçu du produit"
                                    style="display:none; width: 100%; max-width: 280px; margin-top: 1rem; border-radius: 1rem; object-fit: cover;" />
                            </label>
                            <div id="image-error-container"
                                style="min-height:1.25rem; margin-top:0.5rem; color:#f87171; font-size:0.93rem; line-height:1.3;">
                            </div>
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <label
                                style="display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.9rem; font-weight: 500;">Disponibilité</label>
                            <div class="mkt-sidebar-section" style="gap: 0.5rem;">
                                <input type="radio" id="dispo_immediate" name="disponibilite"
                                    value="Disponible maintenant" style="display:none;" />
                                <label for="dispo_immediate" class="filter-option"
                                    style="cursor: pointer; margin-bottom: 0;">
                                    <span><i class="fa-solid fa-circle-check"
                                            style="color:#10b981;margin-right:.4rem;"></i>Disponible maintenant</span>
                                </label>

                                <input type="radio" id="dispo_deux_semaines" name="disponibilite"
                                    value="Dans 2 semaines" style="display:none;" />
                                <label for="dispo_deux_semaines" class="filter-option"
                                    style="cursor: pointer; margin-bottom: 0;">
                                    <span><i class="fa-solid fa-clock"
                                            style="color:#f59e0b;margin-right:.4rem;"></i>Dans 2 semaines</span>
                                </label>

                                <input type="radio" id="dispo_un_mois" name="disponibilite" value="Dans 1 mois"
                                    style="display:none;" />
                                <label for="dispo_un_mois" class="filter-option"
                                    style="cursor: pointer; margin-bottom: 0;">
                                    <span><i class="fa-solid fa-clock"
                                            style="color:#f59e0b;margin-right:.4rem;"></i>Dans 1 mois</span>
                                </label>

                                <input type="radio" id="dispo_non_disponible" name="disponibilite"
                                    value="Non disponible" style="display:none;" />
                                <label for="dispo_non_disponible" class="filter-option"
                                    style="cursor: pointer; margin-bottom: 0;">
                                    <span><i class="fa-solid fa-circle-xmark"
                                            style="color:#ef4444;margin-right:.4rem;"></i>Non disponible</span>
                                </label>
                            </div>
                        </div>

                        <div
                            style="grid-column: 1 / -1; display:flex; gap:1.5rem; flex-wrap:wrap; align-items:center; margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 2rem;">
                            <button type="submit" class="btn-submit"
                                style="width:auto; padding: 0.9rem 2.5rem; font-size: 1.05rem; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); color: var(--tech-blue); border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);"><i
                                    class="fa-solid fa-paper-plane"></i> Publier l'annonce</button>
                            <span style="color: var(--text-muted); font-size: 0.85rem;"><i
                                    class="fa-solid fa-shield-halved"></i> Vos coordonnées ne seront partagées qu'en cas
                                d'accord.</span>
                        </div>
                    </form>
                    <div id="sell-confirmation"
                        style="display:none; margin-top:1.5rem; color: var(--tech-green); font-weight: 500; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 1rem; border-radius: var(--radius-sm); text-align: center;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle availability option selection
        document.querySelectorAll('input[name="disponibilite"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.querySelectorAll('label[for^="dispo_"]').forEach(label => {
                    label.classList.remove('active');
                });
                if (this.checked) {
                    document.querySelector('label[for="' + this.id + '"]').classList.add('active');
                }
            });
        });
        // Set active state on load if a radio is checked
        document.querySelectorAll('input[name="disponibilite"]:checked').forEach(radio => {
            const label = document.querySelector('label[for="' + radio.id + '"]');
            if (label) label.classList.add('active');
        });
    </script>
    <script src="../assets/js.js?v=6"></script>
    <script>
        // AI Generation Logic
        const toggleAiBtn = document.getElementById('toggle-ai-btn');
        const aiPromptContainer = document.getElementById('ai-prompt-container');
        const aiPromptInput = document.getElementById('ai-prompt-input');
        const generateAiBtn = document.getElementById('generate-ai-btn');
        const aiBtnText = document.getElementById('ai-btn-text');
        const aiBtnIcon = document.getElementById('ai-btn-icon');
        const descriptionField = document.getElementById('description');
        const aiError = document.getElementById('ai-error');

        toggleAiBtn.addEventListener('click', () => {
            const isHidden = aiPromptContainer.style.display === 'none';
            aiPromptContainer.style.display = isHidden ? 'block' : 'none';
            if (isHidden) aiPromptInput.focus();
        });

        generateAiBtn.addEventListener('click', async () => {
            const prompt = aiPromptInput.value.trim();
            if (!prompt) {
                aiError.textContent = "Veuillez entrer quelques mots sur votre produit.";
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

                if (data.error) {
                    aiError.textContent = data.error;
                    aiError.style.display = 'block';
                } else if (data.text) {
                    descriptionField.value = data.text;
                    aiPromptContainer.style.display = 'none';
                    aiPromptInput.value = '';
                    // Trigger animation on textarea
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
    </script>
    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>

</html>
