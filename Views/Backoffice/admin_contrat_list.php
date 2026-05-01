<?php
$activePage = 'contrats';
require_once __DIR__ . '/../../controllers/contratController.php';
$stats = getContratStatistics();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Liste des Contrats · FreelaSkill</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="css/admin.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="glow-orb" style="width:500px;height:500px;background:#2563EB;top:-150px;right:-150px;"></div>

<main class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar animate-in">
        <div>
            <div class="admin-breadcrumb">
                <i class="fa-solid fa-house"></i>
                <span class="sep">/</span>
                <a href="admin_contrat.php">Contrats</a>
                <span class="sep">/</span>
                <span class="current">Liste</span>
            </div>
            <h1 class="admin-page-title">Liste des <span>Contrats</span></h1>
        </div>
        <div class="topbar-actions">
            <a href="admin_contrat.php" class="btn btn-secondary btn-back" style="margin-bottom:0;">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
            <button id="statsToggleBtn" onclick="toggleStats()" class="btn btn-secondary">
                <i class="fa-solid fa-chart-bar"></i> Statistiques
            </button>
            <a href="admin_export_pdf.php?action=export_all" class="btn btn-purple" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> Exporter tout
            </a>
            <a href="admin_contrat_form.php" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouveau contrat
            </a>
            <div class="admin-badge">
                <i class="fa-solid fa-user-shield"></i> SuperAdmin
            </div>
        </div>
    </div>

    <!-- Alertes -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error animate-in">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div><?php foreach ($errors as $e) echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '<br>'; ?></div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) || !empty($successMessage)): ?>
        <div class="alert alert-success animate-in">
            <i class="fa-solid fa-circle-check"></i>
            <?php
            if (isset($_GET['success'])) {
                $msgs = ['delete'=>'Contrat supprimé.','archive'=>'Contrat archivé.','verify_ok'=>'Contrat conforme.','create'=>'Contrat créé.','update'=>'Contrat mis à jour.'];
                echo $msgs[$_GET['success']] ?? 'Action réalisée.';
            } else echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8');
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error animate-in">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?php echo $_GET['error'] === 'verify_fail' ? 'Contrat non conforme (budget, délai ou description insuffisants).' : 'Une erreur est survenue.'; ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques (masquées par défaut) -->
    <div id="stats-container" style="display:none;margin-bottom:2rem;" class="animate-in">
        <div class="stats-grid">
            <div class="stat-card" style="--accent-color:#2563EB;">
                <div class="stat-info">
                    <div class="stat-label">Total Contrats</div>
                    <div class="stat-value"><?php echo intval($stats['total']); ?></div>
                </div>
                <div class="stat-icon" style="background:rgba(37,99,235,0.15);color:#2563EB;border-radius:50%;width:48px;height:48px;">
                    <i class="fa-solid fa-file-contract"></i>
                </div>
            </div>
            <div class="stat-card" style="--accent-color:#60A5FA;">
                <div class="stat-info">
                    <div class="stat-label">Actifs</div>
                    <div class="stat-value"><?php echo intval($stats['by_status']['actif']); ?></div>
                </div>
                <div class="stat-icon" style="background:rgba(96,165,250,0.15);color:#60A5FA;border-radius:50%;width:48px;height:48px;">
                    <i class="fa-solid fa-spinner"></i>
                </div>
            </div>
            <div class="stat-card" style="--accent-color:#34D399;">
                <div class="stat-info">
                    <div class="stat-label">Terminés</div>
                    <div class="stat-value"><?php echo intval($stats['by_status']['termine']); ?></div>
                </div>
                <div class="stat-icon" style="background:rgba(52,211,153,0.15);color:#34D399;border-radius:50%;width:48px;height:48px;">
                    <i class="fa-solid fa-check-double"></i>
                </div>
            </div>
            <div class="stat-card" style="--accent-color:#F87171;">
                <div class="stat-info">
                    <div class="stat-label">Annulés</div>
                    <div class="stat-value"><?php echo intval($stats['by_status']['annule']); ?></div>
                </div>
                <div class="stat-icon" style="background:rgba(248,113,113,0.15);color:#F87171;border-radius:50%;width:48px;height:48px;">
                    <i class="fa-solid fa-xmark"></i>
                </div>
            </div>
            <div class="stat-card" style="--accent-color:#10B981;">
                <div class="stat-info">
                    <div class="stat-label">Budget Total</div>
                    <div class="stat-value" style="font-size:1.4rem;"><?php echo number_format($stats['total_budget'], 0, ',', ' '); ?></div>
                    <div class="stat-sub">DT</div>
                </div>
                <div class="stat-icon" style="background:rgba(16,185,129,0.15);color:#10B981;border-radius:50%;width:48px;height:48px;">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <div class="stat-card" style="--accent-color:#F59E0B;">
                <div class="stat-info">
                    <div class="stat-label">Budget Moyen</div>
                    <div class="stat-value" style="font-size:1.4rem;"><?php echo number_format($stats['avg_budget'], 0, ',', ' '); ?></div>
                    <div class="stat-sub">DT / contrat</div>
                </div>
                <div class="stat-icon" style="background:rgba(245,158,11,0.15);color:#F59E0B;border-radius:50%;width:48px;height:48px;">
                    <i class="fa-solid fa-scale-unbalanced"></i>
                </div>
            </div>
        </div>

        <!-- Graphique barres verticales -->
        <div class="admin-card" style="margin-top:1.25rem;">
            <div class="admin-card-header">
                <div class="admin-card-title"><i class="fa-solid fa-chart-bar"></i> Répartition par statut</div>
            </div>
            <div style="padding:1rem;height:260px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Barre de recherche -->
    <form method="GET" action="admin_contrat_list.php" class="search-bar animate-in delay-1">
        <div class="search-field" style="flex:2;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Rechercher par titre ou description...">
        </div>
        <div>
            <select name="sort" class="search-select">
                <?php
                $sortOptions = ['date_creation'=>'Date','titre'=>'Titre','budget'=>'Budget','delai'=>'Délai','statut'=>'Statut'];
                $currentSort = $_GET['sort'] ?? 'date_creation';
                foreach ($sortOptions as $val => $label) {
                    echo "<option value=\"$val\"" . ($val === $currentSort ? ' selected' : '') . ">$label</option>";
                }
                ?>
            </select>
        </div>
        <div>
            <select name="order" class="search-select">
                <option value="DESC" <?php echo ($_GET['order'] ?? 'DESC') === 'DESC' ? 'selected' : ''; ?>>Décroissant</option>
                <option value="ASC"  <?php echo ($_GET['order'] ?? '') === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="admin_contrat_list.php" class="btn btn-secondary">Réinitialiser</a>
    </form>

    <!-- Tableau -->
    <div class="admin-card animate-in delay-2">
        <div class="admin-card-header">
            <div class="admin-card-title">
                <i class="fa-solid fa-table-list"></i>
                Tous les contrats
                <span style="font-size:0.78rem;font-weight:400;color:var(--text-muted);margin-left:0.5rem;">(<?php echo count($contrats); ?> résultat<?php echo count($contrats) > 1 ? 's' : ''; ?>)</span>
            </div>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Budget</th>
                    <th>Délai</th>
                    <th>Statut</th>
                    <th>Créé le</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contrats)): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:3rem;">
                        <i class="fa-solid fa-inbox" style="font-size:2rem;display:block;margin-bottom:0.75rem;opacity:0.4;"></i>
                        Aucun contrat trouvé.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($contrats as $c): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo htmlspecialchars($c['titre'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="font-family:'Space Grotesk',sans-serif;font-weight:600;color:var(--tech-blue);">
                                <?php echo number_format($c['budget'], 2, ',', ' '); ?> DT
                            </td>
                            <td><?php echo intval($c['delai']); ?> j</td>
                            <td>
                                <span class="badge badge-<?php echo htmlspecialchars($c['statut'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $c['statut'])); ?>
                                </span>
                            </td>
                            <td style="color:var(--text-muted);font-size:0.85rem;">
                                <?php echo date('d/m/Y', strtotime($c['date_creation'])); ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:0.4rem;justify-content:flex-end;flex-wrap:wrap;">
                                    <a href="admin_contrat_form.php?action=edit&id=<?php echo intval($c['id_contrat']); ?>"
                                       class="btn btn-secondary btn-icon" title="Modifier">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="admin_contrat_list.php?action=verify&id=<?php echo intval($c['id_contrat']); ?>"
                                       class="btn btn-success btn-icon" title="Vérifier conformité">
                                        <i class="fa-solid fa-clipboard-check"></i>
                                    </a>
                                    <a href="admin_export_pdf.php?id=<?php echo intval($c['id_contrat']); ?>"
                                       class="btn btn-purple btn-icon" title="Exporter PDF" target="_blank">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="margin:0;">
                                        <input type="hidden" name="action" value="archive">
                                        <input type="hidden" name="id" value="<?php echo intval($c['id_contrat']); ?>">
                                        <button type="submit" class="btn btn-warning btn-icon"
                                                onclick="return confirm('Archiver ce contrat ?');" title="Archiver">
                                            <i class="fa-solid fa-box-archive"></i>
                                        </button>
                                    </form>
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="margin:0;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo intval($c['id_contrat']); ?>">
                                        <button type="submit" class="btn btn-danger btn-icon"
                                                onclick="return confirm('Supprimer définitivement ce contrat ?');" title="Supprimer">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<script>
function toggleStats() {
    const el  = document.getElementById('stats-container');
    const btn = document.getElementById('statsToggleBtn');
    const isOpen = el.style.display !== 'none';
    if (isOpen) {
        el.style.display = 'none';
        if (btn) { btn.classList.remove('btn-primary'); btn.classList.add('btn-secondary'); }
    } else {
        el.style.display = 'block';
        if (btn) { btn.classList.remove('btn-secondary'); btn.classList.add('btn-primary'); }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('statusChart');
    if (!ctx) return;
    const statusData = <?php echo json_encode($stats['by_status']); ?>;
    const colorMap = {
        brouillon:'#94A3B8', en_attente:'#FBBF24', actif:'#60A5FA',
        termine:'#34D399', annule:'#F87171', archive:'#6B7280'
    };
    const labels = [], data = [], colors = [], borderColors = [];
    for (const [k, v] of Object.entries(statusData)) {
        labels.push(k.charAt(0).toUpperCase() + k.slice(1).replace('_', ' '));
        data.push(v);
        colors.push((colorMap[k] || '#fff') + '33'); // 20% opacity fill
        borderColors.push(colorMap[k] || '#fff');
    }
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Contrats',
                data,
                backgroundColor: colors,
                borderColor: borderColors,
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.95)',
                    titleColor: '#94A3B8',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} contrat${ctx.parsed.y > 1 ? 's' : ''}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false },
                    ticks: { color: '#94A3B8', font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false },
                    ticks: {
                        color: '#94A3B8',
                        font: { size: 11 },
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
</body>
</html>
