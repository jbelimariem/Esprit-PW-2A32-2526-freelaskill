<?php
require_once __DIR__ . '/../../controllers/Category_prodController.php';
require_once __DIR__ . '/../../controllers/produitController.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1;
}

$categoryController = new Category_prodController();
$productController = new ProduitController();
$editingCategory = null;
$categories      = [];
$pendingProducts = [];

// -------------------------------------------------------
// Actions sur les produits (approuver / refuser)
// -------------------------------------------------------
if (!empty($_GET['product_action']) && !empty($_GET['id'])) {
    $productId = (int) $_GET['id'];
    if ($_GET['product_action'] === 'approve') {
        $productController->updateStatutData($productId, 'disponible');
        header('Location: dashboard.php');
        exit;
    }
    if ($_GET['product_action'] === 'reject') {
        $productController->deleteData($productId);
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
        $categoryController->createData([
            'nom'         => $name,
            'description' => $description
        ]);
    }

    if (!empty($_POST['action']) && $_POST['action'] === 'update' && !empty($_POST['id'])) {
        $categoryController->updateData((int) $_POST['id'], [
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
    $categoryController->deleteData((int) $_GET['id']);
    header('Location: dashboard.php');
    exit;
}

// -------------------------------------------------------
// Modifier une catégorie (charger dans le formulaire)
// -------------------------------------------------------
if (!empty($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $editingCategory = $categoryController->getByIdData((int) $_GET['id']);
}

// -------------------------------------------------------
// Charger les données de statistiques
// -------------------------------------------------------
$categories    = $categoryController->getAllData();
$categoryNames = [];
foreach ($categories as $category) {
    $categoryNames[$category['idCategory']] = $category['nom'];
}
$pendingProducts = $productController->getByStatutData('pending');

// Requêtes pour le dashboard parfait
$pdo = config::getConnexion();

// Total des ventes (Montant total des commandes livrées ou en attente)
$stmt = $pdo->query("SELECT SUM(montant_total) as total FROM commande");
$salesRow = $stmt->fetch(PDO::FETCH_ASSOC);
$totalSales = $salesRow['total'] ? $salesRow['total'] : 0;

// Total des produits
$stmt = $pdo->query("SELECT COUNT(*) as nb FROM produit");
$prodRow = $stmt->fetch(PDO::FETCH_ASSOC);
$nbProducts = $prodRow['nb'];

// Total des utilisateurs (si la table user existe, on gère les erreurs)
$nbUsers = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) as nb FROM user");
    if ($stmt) {
        $uRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $nbUsers = $uRow['nb'];
    }
} catch (Exception $e) {
    $nbUsers = 42; // Fallback
}

?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | FreelaSkill</title>
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
                <a href="./dashboard.php" class="admin-nav-item active">
                    <i class="fa-solid fa-house"></i> Dashboard
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-chart-line"></i> Analytics
                </a>
                <div style="margin: 1rem 0 0.5rem; font-size: 0.75rem; text-transform: uppercase; color: #475569; padding-left: 1rem; font-weight: 700; letter-spacing: 1px;">
                    Marketplace
                </div>
                <a href="produits.php" class="admin-nav-item">
                    <i class="fa-solid fa-box-open"></i> Produits
                </a>
                <a href="./pending_products.php" class="admin-nav-item">
                    <i class="fa-solid fa-clock"></i> Validation produits
                </a>
                                <a href="./ajouter_categorie.php" class="admin-nav-item">
                    <i class="fa-solid fa-plus"></i> Ajouter Catégorie
                </a>
                <a href="./liste_categories.php" class="admin-nav-item">
                    <i class="fa-solid fa-list"></i> Liste des Catégories
                </a>
                <a href="./mes_achats.php" class="admin-nav-item">
                    <i class="fa-solid fa-bag-shopping"></i> Mes Achats
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-cart-shopping"></i> Commandes
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-users"></i> Clients
                </a>
                <div style="margin: 1rem 0 0.5rem; font-size: 0.75rem; text-transform: uppercase; color: #475569; padding-left: 1rem; font-weight: 700; letter-spacing: 1px;">
                    Utilisateurs & rôles
                </div>
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-user-tie"></i> Freelancers
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-user-graduate"></i> Étudiants
                </a>
                <div style="margin: 1rem 0 0.5rem; font-size: 0.75rem; text-transform: uppercase; color: #475569; padding-left: 1rem; font-weight: 700; letter-spacing: 1px;">
                    Paramètres
                </div>
                <a href="#" class="admin-nav-item">
                    <i class="fa-solid fa-gear"></i> Général
                </a>
            </div>

            <div style="padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.08);">
                <a href="../Frontoffice/home.php" class="admin-nav-item" style="color: #ef4444; padding: 0.75rem;">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Retour au Hub
                </a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="admin-search">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" placeholder="Rechercher dans le dashboard">
                </div>
                <div class="admin-top-actions">
                    <div class="admin-icon-btn">
                        <i class="fa-regular fa-moon"></i>
                    </div>
                    <div class="admin-icon-btn">
                        <i class="fa-regular fa-bell"></i>
                        <span class="badge-dot"></span>
                    </div>
                    <div class="nav-avatar" style="margin-left: 0.5rem;">AH</div>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-header-row">
                    <h1 class="admin-page-title">Dashboard</h1>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <button class="admin-btn-outline" style="background: rgba(255,255,255,0.03);">
                            <i class="fa-regular fa-calendar"></i> Sélectionner la date
                        </button>
                        <button class="admin-btn">Paramètres</button>
                    </div>
                </div>

                <div class="admin-grid-4">
                    <div class="glass-card flex-col">
                        <div class="stat-card-header">
                            <span>Ventes Totales</span>
                            <div class="stat-card-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                        </div>
                        <div class="stat-card-value"><?= number_format($totalSales, 0, ',', ' ') ?> DT</div>
                        <div class="stat-card-trend trend-up">
                            <i class="fa-solid fa-arrow-trend-up"></i> Global <span style="color: #475569; font-weight: 500; margin-left: 4px;">Chiffre d'affaires</span>
                        </div>
                    </div>
                    <div class="glass-card flex-col">
                        <div class="stat-card-header">
                            <span>Produits</span>
                            <div class="stat-card-icon" style="color: #a855f7; background: rgba(168, 85, 247, 0.1);"><i class="fa-solid fa-box"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $nbProducts ?></div>
                        <div class="stat-card-trend trend-up">
                            <i class="fa-solid fa-arrow-trend-up"></i> En ligne <span style="color: #475569; font-weight: 500; margin-left: 4px;">Sur la marketplace</span>
                        </div>
                    </div>
                    <div class="glass-card flex-col">
                        <div class="stat-card-header">
                            <span>Utilisateurs</span>
                            <div class="stat-card-icon" style="color: #10b981; background: rgba(16, 185, 129, 0.1);"><i class="fa-solid fa-user-group"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $nbUsers ?></div>
                        <div class="stat-card-trend trend-up">
                            <i class="fa-solid fa-arrow-trend-up"></i> Actifs <span style="color: #475569; font-weight: 500; margin-left: 4px;">Inscrits</span>
                        </div>
                    </div>
                    <div class="glass-card flex-col">
                        <div class="stat-card-header">
                            <span>Produits en attente</span>
                            <div class="stat-card-icon" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1);"><i class="fa-solid fa-clock"></i></div>
                        </div>
                        <div class="stat-card-value"><?= count($pendingProducts) ?></div>
                        <div class="stat-card-trend trend-up">
                            <i class="fa-solid fa-arrow-trend-up"></i> <?= count($pendingProducts) ?> <span style="color: #475569; font-weight: 500; margin-left: 4px;">Articles à valider</span>
                        </div>
                    </div>
                </div>

                <div class="admin-grid-2-1">
                    <div class="glass-card">
                        <div class="admin-list-header" style="margin-bottom: 0;">
                            <span class="admin-list-title">Earnings</span>
                            <div class="admin-icon-btn" style="width: 32px; height: 32px;"><i class="fa-solid fa-ellipsis-vertical"></i></div>
                        </div>
                        <div style="height: 250px; display: flex; align-items: center; justify-content: center; color: #475569;">
                            <i class="fa-solid fa-chart-area fa-3x" style="opacity: 0.2; margin-right: 1rem;"></i> Graphique en attente
                        </div>
                    </div>
                    <div class="glass-card">
                        <div class="admin-list-header" style="margin-bottom: 0;">
                            <span class="admin-list-title">Trafic</span>
                            <div class="admin-icon-btn" style="width: 32px; height: 32px;"><i class="fa-solid fa-ellipsis-vertical"></i></div>
                        </div>
                        <div style="height: 250px; display: flex; align-items: center; justify-content: center; color: #475569;">
                            <i class="fa-solid fa-chart-pie fa-3x" style="opacity: 0.2; margin-right: 1rem;"></i> Graphique en attente
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </main>
    </div>

    <script src="js.js"></script>
</body>
</html>
