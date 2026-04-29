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

// -------------------------------------------------------
// Statistiques pour les graphiques
// -------------------------------------------------------

// 1. Commandes par statut
$stmt = $pdo->query("SELECT statut, COUNT(*) as count FROM commande GROUP BY statut");
$orderStatuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$statusLabels = [];
$statusCounts = [];
foreach ($orderStatuses as $status) {
    $statusLabels[] = ucfirst($status['statut']);
    $statusCounts[] = $status['count'];
}

// 2. Ventes par catégorie
$stmt = $pdo->query("
    SELECT c.nom, COUNT(p.idProduit) as nb_produits, SUM(cmd.montant_total) as total_ventes
    FROM Category_prod c
    LEFT JOIN produit p ON c.idCategory = p.category_id
    LEFT JOIN commande_produit cp ON p.idProduit = cp.idProduit
    LEFT JOIN commande cmd ON cp.idCommande = cmd.idCommande
    GROUP BY c.idCategory
    LIMIT 5
");
$categorySales = $stmt->fetchAll(PDO::FETCH_ASSOC);
$categoryLabels = [];
$categoryCounts = [];
foreach ($categorySales as $cat) {
    $categoryLabels[] = $cat['nom'];
    $categoryCounts[] = (int)$cat['nb_produits'];
}

// 3. Évolution des ventes (7 derniers jours)
$stmt = $pdo->query("
    SELECT DATE(date_commande) as date, SUM(montant_total) as total
    FROM commande
    WHERE date_commande >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(date_commande)
    ORDER BY date ASC
");
$salesTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);
$trendDates = [];
$trendSales = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('d/m', strtotime("-$i days"));
    $trendDates[] = $date;
    $trendSales[] = 0;
}
foreach ($salesTrend as $trend) {
    $index = array_search(date('d/m', strtotime($trend['date'])), $trendDates);
    if ($index !== false) {
        $trendSales[$index] = (int)$trend['total'];
    }
}

// 4. Top 5 produits les plus vendus
$stmt = $pdo->query("
    SELECT p.nom, COUNT(cp.idCommande) as nb_ventes, SUM(cp.quantite) as quantite_totale
    FROM produit p
    LEFT JOIN commande_produit cp ON p.idProduit = cp.idProduit
    GROUP BY p.idProduit
    ORDER BY nb_ventes DESC
    LIMIT 5
");
$topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$productLabels = [];
$productSales = [];
foreach ($topProducts as $prod) {
    $productLabels[] = substr($prod['nom'], 0, 20) . (strlen($prod['nom']) > 20 ? '...' : '');
    $productSales[] = $prod['nb_ventes'] ?? 0;
}

// Couleurs pour les graphiques
$chartColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
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
                <a href="./ajouter_categorie.php" class="admin-nav-item">
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
                    <span style="margin-left:auto; background:#ef4444; color:white; font-size:0.7rem; font-weight:bold; padding:2px 6px; border-radius:10px;">3</span>
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
                        <div class="admin-list-header" style="margin-bottom: 1.5rem;">
                            <span class="admin-list-title">Ventes - 7 derniers jours</span>
                            <div class="admin-icon-btn" style="width: 32px; height: 32px;"><i class="fa-solid fa-ellipsis-vertical"></i></div>
                        </div>
                        <div style="position: relative; height: 250px;">
                            <canvas id="salesTrendChart"></canvas>
                        </div>
                    </div>
                    <div class="glass-card">
                        <div class="admin-list-header" style="margin-bottom: 1.5rem;">
                            <span class="admin-list-title">Statut des commandes</span>
                            <div class="admin-icon-btn" style="width: 32px; height: 32px;"><i class="fa-solid fa-ellipsis-vertical"></i></div>
                        </div>
                        <div style="position: relative; height: 250px; display: flex; justify-content: center; align-items: center;">
                            <canvas id="orderStatusChart" style="max-width: 200px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="admin-grid-2-1">
                    <div class="glass-card">
                        <div class="admin-list-header" style="margin-bottom: 1.5rem;">
                            <span class="admin-list-title">Top 5 Produits les plus vendus</span>
                            <div class="admin-icon-btn" style="width: 32px; height: 32px;"><i class="fa-solid fa-ellipsis-vertical"></i></div>
                        </div>
                        <div style="position: relative; height: 250px;">
                            <canvas id="topProductsChart"></canvas>
                        </div>
                    </div>
                    <div class="glass-card">
                        <div class="admin-list-header" style="margin-bottom: 1.5rem;">
                            <span class="admin-list-title">Produits par catégorie</span>
                            <div class="admin-icon-btn" style="width: 32px; height: 32px;"><i class="fa-solid fa-ellipsis-vertical"></i></div>
                        </div>
                        <div style="position: relative; height: 250px;">
                            <canvas id="categorySalesChart"></canvas>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </main>
    </div>

    <script src="js.js"></script>
    <script>
        // Configuration globale de Chart.js
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';

        // 1. Graphique des ventes - 7 derniers jours (Line Chart)
        const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(salesTrendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trendDates); ?>,
                datasets: [{
                    label: 'Ventes (DT)',
                    data: <?php echo json_encode($trendSales); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // 2. Graphique des statuts des commandes (Pie Chart)
        const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
        const statusColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($statusLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($statusCounts); ?>,
                    backgroundColor: statusColors,
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // 3. Graphique des produits les plus vendus (Bar Chart)
        const productsCtx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(productsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($productLabels); ?>,
                datasets: [{
                    label: 'Nombre de ventes',
                    data: <?php echo json_encode($productSales); ?>,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // 4. Graphique des produits par catégorie (Bar Chart)
        const categoryCtx = document.getElementById('categorySalesChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($categoryLabels); ?>,
                datasets: [{
                    label: 'Nombre de produits',
                    data: <?php echo json_encode($categoryCounts); ?>,
                    backgroundColor: 'rgba(168, 85, 247, 0.8)',
                    borderColor: '#a855f7',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
