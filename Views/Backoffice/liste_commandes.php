<?php
require_once __DIR__ . '/../../controllers/commandeController.php';
require_once __DIR__ . '/../../controllers/CommandeProduitController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$commandeController = new CommandeController();

// Action de suppression
if (!empty($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $commandeController->deleteData((int) $_GET['id']);
    header('Location: liste_commandes.php');
    exit;
}

// Action de mise à jour du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = (int) $_POST['id'];
    $statut = $_POST['statut'] ?? 'en_attente';
    $commandeController->updateStatutData($id, $statut);
    header('Location: liste_commandes.php');
    exit;
}

// Récupérer toutes les commandes
$commandes = $commandeController->getAll();

// Pagination
$itemsPerPage = 15;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalPages = ceil(count($commandes) / $itemsPerPage);
$currentPage = min($currentPage, $totalPages > 0 ? $totalPages : 1);
$startIndex = ($currentPage - 1) * $itemsPerPage;
$commandesPaginees = array_slice($commandes, $startIndex, $itemsPerPage);

// Fonction pour obtenir le style du statut
function getStatusBadge($statut) {
    switch ($statut) {
        case 'en_attente':
            return '<span class="badge badge-warning"><i class="fa-solid fa-clock"></i> En attente</span>';
        case 'confirmée':
            return '<span class="badge badge-info"><i class="fa-solid fa-check-circle"></i> Confirmée</span>';
        case 'livrée':
            return '<span class="badge badge-success"><i class="fa-solid fa-box-open"></i> Livrée</span>';
        case 'annulée':
            return '<span class="badge badge-danger"><i class="fa-solid fa-times-circle"></i> Annulée</span>';
        default:
            return '<span class="badge">' . ucfirst($statut) . '</span>';
    }
}

// Fonction pour obtenir le nom de l'utilisateur (si disponible)
function getUserName($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT nom FROM user WHERE idUser = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['nom'] : 'Utilisateur #' . $user_id;
    } catch (Exception $e) {
        return 'Utilisateur #' . $user_id;
    }
}

?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Commandes | FreelaSkill</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 0.375rem;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .badge-warning {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }
        .badge-info {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
        }
        .badge-success {
            background: rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
        }
        .badge-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table thead {
            background: rgba(255, 255, 255, 0.03);
        }
        .data-table th {
            padding: 1rem;
            text-align: left;
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .data-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.375rem;
            background: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .btn-danger {
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.3);
        }
        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.5);
        }
        .btn-outline {
            background: transparent;
        }
        .pagination {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 2rem;
        }
        .pagination a, .pagination span {
            padding: 0.5rem 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.375rem;
            background: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .pagination a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .pagination .active {
            background: #3b82f6;
            border-color: #3b82f6;
        }
    </style>
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
                <a href="./ajouter_categorie.php" class="admin-nav-item">
                    <i class="fa-solid fa-plus"></i> Ajouter Catégorie
                </a>
                <a href="./liste_categories.php" class="admin-nav-item">
                    <i class="fa-solid fa-list"></i> Liste des Catégories
                </a>
                <a href="./mes_achats.php" class="admin-nav-item">
                    <i class="fa-solid fa-bag-shopping"></i> Mes Achats
                </a>
                <a href="./liste_commandes.php" class="admin-nav-item active">
                    <i class="fa-solid fa-cart-shopping"></i> Commandes
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
                    <input type="text" placeholder="Rechercher une commande">
                </div>
                <div class="admin-top-actions">
                    <div class="admin-icon-btn theme-toggle-btn" style="cursor:pointer;" title="Basculer thème">
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
                    <h1 class="admin-page-title">Gestion des Commandes</h1>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="color: #94a3b8; font-size: 0.9rem; display: flex; align-items: center;">
                            <i class="fa-solid fa-chart-bar" style="margin-right: 0.5rem;"></i>
                            Total : <?= count($commandes) ?> commande(s)
                        </span>
                        <button onclick="exportToPDF()" class="admin-btn" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none;">
                            <i class="fa-solid fa-file-pdf"></i> Exporter en PDF
                        </button>
                    </div>
                </div>

                <div class="admin-grid" style="display: block;">
                    <div class="glass-card admin-section">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID Commande</th>
                                    <th>Date</th>
                                    <th>Utilisateur</th>
                                    <th>Produits</th>
                                    <th>Montant</th>
                                    <th>Adresse Livraison</th>
                                    <th>Statut</th>
                                    <th style="min-width: 250px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($commandes)): ?>
                                    <tr><td colspan="8" style="color: var(--text-muted); text-align: center; padding: 2rem;">Aucune commande enregistrée.</td></tr>
                                <?php else: ?>
                                    <?php 
                                        $commandeProduitController = new CommandeProduitController();
                                        foreach ($commandesPaginees as $commande): 
                                        $produitsCmd = $commandeProduitController->getByCommandeData($commande['idCommande']);
                                    ?>
                                        <tr>
                                            <td><strong style="color: #3b82f6;">#<?= $commande['idCommande'] ?></strong></td>
                                            <td><span style="color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></span></td>
                                            <td><?= htmlspecialchars('Client #' . $commande['user_id']) ?></td>
                                            <td>
                                                <ul style="margin:0; padding-left: 1.2rem; font-size: 0.85rem; color: #cbd5e1;">
                                                    <?php if(empty($produitsCmd)): ?>
                                                        <li style="color: #ef4444;">Aucun produit</li>
                                                    <?php else: ?>
                                                        <?php foreach($produitsCmd as $p): ?>
                                                            <li><?= htmlspecialchars($p['nom']) ?> (x<?= $p['quantite'] ?>)</li>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </ul>
                                            </td>
                                            <td><strong style="color: #10b981;"><?= number_format($commande['montant_total'], 2, '.', ' ') ?> DT</strong></td>
                                            <td><span style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars(substr($commande['adresse_livraison'], 0, 30)) ?></span></td>
                                            <td><?= getStatusBadge($commande['statut']) ?></td>
                                            <td>
                                                <form method="POST" style="display: inline-block; margin-right: 0.5rem;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="id" value="<?= $commande['idCommande'] ?>">
                                                    <select name="statut" onchange="this.form.submit()" style="padding: 0.4rem 0.6rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 0.375rem; color: #e2e8f0; cursor: pointer; font-size: 0.8rem;">
                                                        <option value="en_attente" <?= $commande['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                                        <option value="confirmée" <?= $commande['statut'] === 'confirmée' ? 'selected' : '' ?>>Confirmée</option>
                                                        <option value="livrée" <?= $commande['statut'] === 'livrée' ? 'selected' : '' ?>>Livrée</option>
                                                        <option value="annulée" <?= $commande['statut'] === 'annulée' ? 'selected' : '' ?>>Annulée</option>
                                                    </select>
                                                </form>
                                                <a href="liste_commandes.php?action=delete&id=<?= $commande['idCommande'] ?>" class="btn btn-danger" onclick="return confirm('Vraiment supprimer cette commande ?');"><i class="fa-solid fa-trash"></i> Supprimer</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="liste_commandes.php?page=<?= $i ?>" class="<?= $i === $currentPage ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="../assets/pdf_export.js"></script>
    <script src="../assets/js.js"></script>
</body>
</html>

