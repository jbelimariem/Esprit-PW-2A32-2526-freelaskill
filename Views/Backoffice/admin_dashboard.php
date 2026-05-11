<?php
$activePage = 'dashboard';
require_once __DIR__ . '/../../controllers/contratController.php';
require_once __DIR__ . '/../../Models/NotificationRepository.php';
require_once __DIR__ . '/../../Models/ContratVersionRepository.php';

$pdo   = config::getConnexion();
$stats = getContratStatistics();

// Derniers contrats
$recentContrats = getAllContrats('', 'date_creation', 'DESC');
$recentContrats = array_slice($recentContrats, 0, 5);

// Notifications non lues
$notifRepo  = new NotificationRepository($pdo);
$unreadCount = $notifRepo->countUnread();
$recentNotifs = $notifRepo->findUnread();
$recentNotifs = array_slice($recentNotifs, 0, 4);

// Règles
$pdo2 = config::getConnexion();
$totalRules  = (int)$pdo2->query('SELECT COUNT(*) FROM rules')->fetchColumn();
$activeRules = (int)$pdo2->query("SELECT COUNT(*) FROM rules WHERE statut='actif'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Dashboard · FreelaSkill</title>
    <link rel="stylesheet" href="css/admin.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="css/admin.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="glow-orb" style="width:600px;height:600px;background:#2563EB;top:-200px;right:-200px;"></div>
<div class="glow-orb" style="width:400px;height:400px;background:#EF4444;bottom:-100px;left:50px;"></div>

<main class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar animate-in">
        <div>
            <div class="admin-breadcrumb">
                <i class="fa-solid fa-house"></i>
                <span class="sep">/</span>
                <span class="current">Dashboard</span>
            </div>
            <h1 class="admin-page-title">Tableau de <span>Bord</span></h1>
        </div>
        <div class="topbar-actions">
            <?php if ($unreadCount > 0): ?>
                <div class="admin-badge" style="background:rgba(239,68,68,0.1);border-color:rgba(239,68,68,0.2);color:#F87171;">
                    <i class="fa-solid fa-bell"></i>
                    <?php echo $unreadCount; ?> notification<?php echo $unreadCount > 1 ? 's' : ''; ?>
                </div>
            <?php endif; ?>
            <div class="admin-badge"><i class="fa-solid fa-user-shield"></i> SuperAdmin</div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="stats-grid animate-in delay-1" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
        <div class="stat-card" style="--accent-color:#2563EB;">
            <div class="stat-info">
                <div class="stat-label">Total Contrats</div>
                <div class="stat-value"><?php echo intval($stats['total']); ?></div>
                <div class="stat-sub">tous statuts</div>
            </div>
            <div class="stat-icon" style="background:rgba(37,99,235,0.12);color:#2563EB;border-radius:50%;width:48px;height:48px;">
                <i class="fa-solid fa-file-contract"></i>
            </div>
        </div>
        <div class="stat-card" style="--accent-color:#60A5FA;">
            <div class="stat-info">
                <div class="stat-label">Actifs</div>
                <div class="stat-value"><?php echo intval($stats['by_status']['actif']); ?></div>
                <div class="stat-sub">en cours</div>
            </div>
            <div class="stat-icon" style="background:rgba(96,165,250,0.12);color:#60A5FA;border-radius:50%;width:48px;height:48px;">
                <i class="fa-solid fa-spinner"></i>
            </div>
        </div>
        <div class="stat-card" style="--accent-color:#34D399;">
            <div class="stat-info">
                <div class="stat-label">Terminés</div>
                <div class="stat-value"><?php echo intval($stats['by_status']['termine']); ?></div>
                <div class="stat-sub">complétés</div>
            </div>
            <div class="stat-icon" style="background:rgba(52,211,153,0.12);color:#34D399;border-radius:50%;width:48px;height:48px;">
                <i class="fa-solid fa-check-double"></i>
            </div>
        </div>
        <div class="stat-card" style="--accent-color:#10B981;">
            <div class="stat-info">
                <div class="stat-label">Budget Total</div>
                <div class="stat-value" style="font-size:1.4rem;"><?php echo number_format($stats['total_budget'], 0, ',', ' '); ?></div>
                <div class="stat-sub">DT générés</div>
            </div>
            <div class="stat-icon" style="background:rgba(16,185,129,0.12);color:#10B981;border-radius:50%;width:48px;height:48px;">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>
        <div class="stat-card" style="--accent-color:#A855F7;">
            <div class="stat-info">
                <div class="stat-label">Règles Actives</div>
                <div class="stat-value"><?php echo $activeRules; ?></div>
                <div class="stat-sub">sur <?php echo $totalRules; ?> total</div>
            </div>
            <div class="stat-icon" style="background:rgba(168,85,247,0.12);color:#A855F7;border-radius:50%;width:48px;height:48px;">
                <i class="fa-solid fa-gavel"></i>
            </div>
        </div>
        <div class="stat-card" style="--accent-color:#F59E0B;">
            <div class="stat-info">
                <div class="stat-label">Budget Moyen</div>
                <div class="stat-value" style="font-size:1.4rem;"><?php echo number_format($stats['avg_budget'], 0, ',', ' '); ?></div>
                <div class="stat-sub">DT / contrat</div>
            </div>
            <div class="stat-icon" style="background:rgba(245,158,11,0.12);color:#F59E0B;border-radius:50%;width:48px;height:48px;">
                <i class="fa-solid fa-scale-unbalanced"></i>
            </div>
        </div>
    </div>

    <!-- Taux de complétion -->
    <?php
    $total    = intval($stats['total']);
    $termine  = intval($stats['by_status']['termine']);
    $taux     = $total > 0 ? round(($termine / $total) * 100) : 0;
    ?>
    <div class="admin-card animate-in delay-2" style="margin-bottom:1.5rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
            <div class="admin-card-title"><i class="fa-solid fa-chart-line"></i> Taux de complétion</div>
            <span style="font-family:'Space Grotesk',sans-serif;font-size:1.5rem;font-weight:700;color:#34D399;"><?php echo $taux; ?>%</span>
        </div>
        <div style="background:rgba(255,255,255,0.05);border-radius:999px;height:10px;overflow:hidden;">
            <div style="height:100%;width:<?php echo $taux; ?>%;background:linear-gradient(90deg,#2563EB,#34D399);border-radius:999px;transition:width 1s ease;"></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:0.5rem;font-size:0.75rem;color:var(--text-muted);">
            <span><?php echo $termine; ?> terminé<?php echo $termine > 1 ? 's' : ''; ?></span>
            <span><?php echo $total - $termine; ?> en cours</span>
        </div>
    </div>

    <!-- Graphique + Notifications récentes -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;" class="animate-in delay-2">

        <!-- Graphique barres -->
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title"><i class="fa-solid fa-chart-bar"></i> Répartition par statut</div>
            </div>
            <div style="height:220px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Notifications récentes -->
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title"><i class="fa-solid fa-bell"></i> Activité récente</div>
                <?php if ($unreadCount > 0): ?>
                    <span class="badge badge-actif" style="font-size:0.72rem;"><?php echo $unreadCount; ?> non lue<?php echo $unreadCount > 1 ? 's' : ''; ?></span>
                <?php endif; ?>
            </div>
            <?php if (empty($recentNotifs)): ?>
                <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                    <i class="fa-solid fa-check-circle" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;color:#34D399;opacity:0.6;"></i>
                    Aucune activité récente
                </div>
            <?php else: ?>
                <?php foreach ($recentNotifs as $n): ?>
                    <div style="display:flex;gap:0.75rem;align-items:flex-start;padding:0.75rem 0;border-bottom:1px solid var(--border);">
                        <div style="width:32px;height:32px;border-radius:50%;background:<?php echo $n->getColorNouveauStatut(); ?>22;color:<?php echo $n->getColorNouveauStatut(); ?>;display:flex;align-items:center;justify-content:center;font-size:0.82rem;flex-shrink:0;">
                            <i class="fa-solid <?php echo $n->getIconNouveauStatut(); ?>"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.82rem;font-weight:600;color:var(--text-light);margin-bottom:0.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php echo htmlspecialchars($n->getTitreContrat(), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?php echo htmlspecialchars($n->getMessage(), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div style="font-size:0.68rem;color:#475569;flex-shrink:0;"><?php echo date('d/m H:i', strtotime($n->getDateCreation())); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Derniers contrats -->
    <div class="admin-card animate-in delay-3">
        <div class="admin-card-header">
            <div class="admin-card-title"><i class="fa-solid fa-clock-rotate-left"></i> Derniers contrats</div>
            <a href="admin_contrat_list.php" class="btn btn-secondary" style="font-size:0.8rem;padding:0.4rem 0.9rem;">
                Voir tout <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Budget</th>
                    <th>Statut</th>
                    <th>Créé le</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentContrats)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:2rem;">
                        <i class="fa-solid fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;opacity:0.3;"></i>
                        Aucun contrat
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($recentContrats as $c): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo htmlspecialchars($c['titre'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="color:var(--tech-blue);font-weight:600;"><?php echo number_format($c['budget'], 2, ',', ' '); ?> DT</td>
                            <td>
                                <span class="badge badge-<?php echo htmlspecialchars($c['statut'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $c['statut'])); ?>
                                </span>
                            </td>
                            <td style="color:var(--text-muted);font-size:0.85rem;"><?php echo date('d/m/Y', strtotime($c['date_creation'])); ?></td>
                            <td>
                                <div style="display:flex;gap:0.4rem;justify-content:flex-end;">
                                    <a href="admin_contrat_form.php?action=edit&id=<?php echo intval($c['id_contrat']); ?>" class="btn btn-secondary btn-icon" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                                    <a href="admin_contrat_versions.php?action=history&id_contrat=<?php echo intval($c['id_contrat']); ?>" class="btn btn-secondary btn-icon" title="Historique" style="background:rgba(99,102,241,0.12);color:#818CF8;border-color:rgba(99,102,241,0.2);"><i class="fa-solid fa-clock-rotate-left"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Raccourcis rapides -->
    <div class="menu-grid animate-in delay-3" style="margin-top:0;">
        <a href="admin_contrat_form.php" class="menu-card" style="--card-glow:rgba(37,99,235,0.12);">
            <div class="menu-card-icon" style="background:rgba(37,99,235,0.12);color:var(--tech-blue);"><i class="fa-solid fa-plus"></i></div>
            <div><div class="menu-card-title">Nouveau Contrat</div><div class="menu-card-desc">Créer un contrat rapidement</div></div>
            <div class="menu-card-arrow" style="color:var(--tech-blue);">Créer <i class="fa-solid fa-arrow-right"></i></div>
        </a>
        <a href="admin_rules_form.php" class="menu-card" style="--card-glow:rgba(168,85,247,0.12);">
            <div class="menu-card-icon" style="background:rgba(168,85,247,0.12);color:#A855F7;"><i class="fa-solid fa-gavel"></i></div>
            <div><div class="menu-card-title">Nouvelle Règle</div><div class="menu-card-desc">Ajouter une règle de contrat</div></div>
            <div class="menu-card-arrow" style="color:#A855F7;">Créer <i class="fa-solid fa-arrow-right"></i></div>
        </a>
        <a href="admin_export_pdf.php?action=export_all" class="menu-card" target="_blank" style="--card-glow:rgba(245,158,11,0.12);">
            <div class="menu-card-icon" style="background:rgba(245,158,11,0.12);color:#F59E0B;"><i class="fa-solid fa-file-pdf"></i></div>
            <div><div class="menu-card-title">Export PDF</div><div class="menu-card-desc">Exporter tous les contrats</div></div>
            <div class="menu-card-arrow" style="color:#F59E0B;">Exporter <i class="fa-solid fa-arrow-right"></i></div>
        </a>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('statusChart');
    if (!ctx) return;

    const statusData = <?php echo json_encode($stats['by_status']); ?>;
    const colorMap = {
        brouillon:'#94A3B8', en_attente:'#FBBF24', actif:'#60A5FA',
        termine:'#34D399', annule:'#F87171', archive:'#6B7280'
    };

    const labels = [], data = [], colors = [], borders = [];
    for (const [k, v] of Object.entries(statusData)) {
        labels.push(k.charAt(0).toUpperCase() + k.slice(1).replace('_', ' '));
        data.push(v);
        colors.push((colorMap[k] || '#fff') + '33');
        borders.push(colorMap[k] || '#fff');
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: colors,
                borderColor: borders,
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
                }
            },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94A3B8', font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94A3B8', font: { size: 11 }, stepSize: 1, precision: 0 } }
            }
        }
    });
});
</script>
</body>
</html>
