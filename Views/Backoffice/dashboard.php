<?php
require_once __DIR__ . '/../../Models/Category_prod.php';
require_once __DIR__ . '/../../Models/Produit.php';

$categoryModel  = new Category_prod();
$productModel   = new Produit();
$editingCategory = null;
$categories      = [];
$pendingProducts = [];

// -------------------------------------------------------
// Actions sur les produits (approuver / refuser)
// -------------------------------------------------------
if (!empty($_GET['product_action']) && !empty($_GET['id'])) {
    $productId = (int) $_GET['id'];
    if ($_GET['product_action'] === 'approve') {
        $productModel->updateStatut($productId, 'disponible');
        header('Location: dashboard.php');
        exit;
    }
    if ($_GET['product_action'] === 'reject') {
        $productModel->delete($productId);
        header('Location: dashboard.php');
        exit;
    }
}

// -------------------------------------------------------
// Actions sur les catégories (créer / modifier)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!empty($_POST['action']) && $_POST['action'] === 'create' && $name !== '') {
        $categoryModel->create([
            'nom'         => $name,
            'description' => $description
        ]);
    }

    if (!empty($_POST['action']) && $_POST['action'] === 'update' && !empty($_POST['id'])) {
        $categoryModel->update((int) $_POST['id'], [
            'nom'         => $name,
            'description' => $description
        ]);
    }

    header('Location: dashboard.php');
    exit;
}

// -------------------------------------------------------
// Supprimer une catégorie
// -------------------------------------------------------
if (!empty($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $categoryModel->delete((int) $_GET['id']);
    header('Location: dashboard.php');
    exit;
}

// -------------------------------------------------------
// Modifier une catégorie (charger dans le formulaire)
// -------------------------------------------------------
if (!empty($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $editingCategory = $categoryModel->getById((int) $_GET['id']);
}

// -------------------------------------------------------
// Charger les données
// -------------------------------------------------------
$categories    = $categoryModel->getAll();
$categoryNames = [];
foreach ($categories as $category) {
    $categoryNames[$category['idCategory']] = $category['nom'];
}
$pendingProducts = $productModel->getByStatut('pending');
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin System - FreelaSkill</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar animate-fade-up">
    <div style="padding: 0 2rem; margin-bottom: 3rem;">
        <div class="logo">
            <i class="fa-solid fa-shapes" style="color: var(--tunisian-red);"></i>
            Core<span>Panel</span>
        </div>
        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; letter-spacing: 1px;">Admin Control v3.0</p>
    </div>
    <div class="nav-item active"><i class="fa-solid fa-cube"></i> Métriques Globales</div>
    <div class="nav-item"><i class="fa-solid fa-network-wired"></i> Flux de Missions</div>
    <div class="nav-item"><i class="fa-solid fa-users-viewfinder"></i> Entités (Users)</div>
    <div class="nav-item"><i class="fa-solid fa-shield-halved"></i> Sécurité</div>
    <div style="margin-top: auto; padding: 0 2rem;">
        <a href="../Frontoffice/home.php" class="btn btn-outline" style="width: 100%; font-size: 0.85rem; padding: 0.75rem;">
            <i class="fa-solid fa-globe"></i> Retour au Hub
        </a>
    </div>
</aside>

<main class="main-panel">
    <div class="hero-glow-bg-2" style="top: 0; right: 0; opacity: 0.5;"></div>

    <!-- HEADER -->
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;" class="animate-fade-up delay-1">
        <h1 style="font-family: 'Space Grotesk'; font-size: 2rem; color: white;">
            Monitorage <span style="color: var(--tech-blue)">En Temps Réel</span>
        </h1>
        <div style="display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.05); padding: 0.5rem 1rem; border-radius: var(--radius-full); border: 1px solid rgba(255,255,255,0.05);">
            <i class="fa-solid fa-satellite" style="color: var(--tech-green);"></i>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Latence Réseau : 12ms</span>
        </div>
    </header>

    <!-- METRICS -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 3rem;" class="animate-fade-up delay-2">
        <div class="metric-card">
            <p style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Volume Transigé (24h)</p>
            <h2 style="font-family: 'Space Grotesk'; font-size: 2.5rem; color: white; margin: 0.5rem 0;">
                45,200 <span style="font-size: 1rem; color: var(--tech-blue);">DT</span>
            </h2>
            <p style="color: var(--tech-green); font-size: 0.85rem;"><i class="fa-solid fa-arrow-trend-up"></i> +12% vs Hier</p>
        </div>
        <div class="metric-card">
            <p style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Flux de Talents Actifs</p>
            <h2 style="font-family: 'Space Grotesk'; font-size: 2.5rem; color: white; margin: 0.5rem 0;">1,420</h2>
            <p style="color: var(--tech-blue); font-size: 0.85rem;"><i class="fa-solid fa-users"></i> Connexions stables</p>
        </div>
        <div class="metric-card" style="border-color: rgba(231,0,19,0.2);">
            <p style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Anomalies / À valider</p>
            <h2 style="font-family: 'Space Grotesk'; font-size: 2.5rem; color: var(--tunisian-red); margin: 0.5rem 0;">
                <?= count($pendingProducts) ?>
            </h2>
            <p style="color: var(--tunisian-red); font-size: 0.85rem;"><i class="fa-solid fa-triangle-exclamation"></i> Requiert attention humaine</p>
        </div>
    </div>

    <!-- SECTION PRODUITS + CATEGORIES -->
    <section class="metric-card admin-card animate-fade-up delay-4">
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <h3 style="color: white; font-family: 'Space Grotesk'; font-size: 1.2rem; margin: 0;">Produits en attente</h3>
            <p style="color: var(--text-muted); margin-top: 0.75rem;">Voir les nouveaux produits soumis par les vendeurs et les accepter ou refuser.</p>
        </div>

        <div style="padding: 2rem;">
            <div class="admin-grid">

                <!-- Produits à modérer -->
                <div class="admin-section">
                    <div class="section-title">Produits à modérer</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingProducts)): ?>
                                <tr><td colspan="6" style="color: var(--text-muted);">Aucun produit en attente.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pendingProducts as $pending): ?>
                                    <tr>
                                        <td><?= $pending['idProduit'] ?></td>
                                        <td><?= htmlspecialchars($pending['nom']) ?></td>
                                        <td><?= htmlspecialchars($categoryNames[$pending['category_id']] ?? 'Autre') ?></td>
                                        <td><?= number_format($pending['prix'], 0, ',', ' ') ?> DT</td>
                                        <td><?= htmlspecialchars($pending['stock']) ?></td>
                                        <td>
                                            <a href="?product_action=approve&id=<?= $pending['idProduit'] ?>" class="btn btn-cart">Accepter</a>
                                            <a href="?product_action=reject&id=<?= $pending['idProduit'] ?>" class="btn btn-danger js-delete-link">Refuser</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Gestion des catégories -->
                <div class="admin-section">
                    <div class="section-title">Gestion des catégories</div>
                    <form action="dashboard.php" method="POST" class="category-form">
                        <input type="hidden" name="action" value="<?= $editingCategory ? 'update' : 'create' ?>">
                        <?php if ($editingCategory): ?>
                            <input type="hidden" name="id" value="<?= $editingCategory['idCategory'] ?>">
                        <?php endif; ?>
                        <label for="name">Nom de la catégorie</label>
                        <input id="name" name="name" type="text"
                               value="<?= htmlspecialchars($editingCategory['nom'] ?? '') ?>"
                               placeholder="Ex : Informatique" required>
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  placeholder="Ex : Tous les produits électroniques et gadgets"><?= htmlspecialchars($editingCategory['description'] ?? '') ?></textarea>
                        <div style="display:flex; gap:1rem; align-items:center; margin-top: 1rem;">
                            <button type="submit" class="btn-cart">
                                <?= $editingCategory ? 'Mettre à jour' : 'Ajouter la catégorie' ?>
                            </button>
                            <?php if ($editingCategory): ?>
                                <a href="dashboard.php" class="btn btn-outline">Annuler</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Liste des catégories -->
                    <div style="margin-top: 2rem;">
                        <div class="section-title">Liste des catégories</div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr><td colspan="4" style="color: var(--text-muted);">Aucune catégorie enregistrée.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?= $category['idCategory'] ?></td>
                                            <td><?= htmlspecialchars($category['nom']) ?></td>
                                            <td><?= htmlspecialchars($category['description']) ?></td>
                                            <td>
                                                <a href="?action=edit&id=<?= $category['idCategory'] ?>" class="btn btn-outline">Modifier</a>
                                                <a href="?action=delete&id=<?= $category['idCategory'] ?>" class="btn btn-danger js-delete-link">Supprimer</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- fin admin-grid -->
        </div><!-- fin padding -->
    </section><!-- fin admin-card -->

</main>

<script src="js.js"></script>
</body>
</html>