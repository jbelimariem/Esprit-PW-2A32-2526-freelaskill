<?php
// views/backoffice/admin_freelancers.view.php
function statutBadgeApp($s) {
    $map = [
        'pending'  => ['label'=>'En attente', 'color'=>'#f59e0b', 'bg'=>'rgba(245,158,11,0.15)', 'border'=>'rgba(245,158,11,0.3)', 'icon'=>'fa-clock'],
        'approved' => ['label'=>'Acceptée',   'color'=>'#10b981', 'bg'=>'rgba(16,185,129,0.15)',  'border'=>'rgba(16,185,129,0.3)',  'icon'=>'fa-check-circle'],
        'rejected' => ['label'=>'Refusée',    'color'=>'#ef4444', 'bg'=>'rgba(239,68,68,0.15)',   'border'=>'rgba(239,68,68,0.3)',   'icon'=>'fa-times-circle']
    ];
    return $map[$s] ?? $map['pending'];
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme:dark;">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Freelancers | Admin FrelaSkill</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="admin.css?v=<?= time() ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Adjustments for freelancers specific elements */
.freelancer-avatar {
    width: 40px; height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--tech-blue), #8b5cf6);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.9rem; color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}
.freelancer-name { font-weight: 600; color: white; font-size: 0.95rem; }
.freelancer-id { font-size: 0.75rem; color: var(--text-muted); font-family: 'JetBrains Mono', monospace; }

.count-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: var(--radius-full);
    font-size: 0.75rem; font-weight: 600;
    background: rgba(59, 130, 246, 0.1); color: var(--tech-blue);
    border: 1px solid rgba(59, 130, 246, 0.2);
}
.count-badge.zero { background: rgba(255,255,255,0.03); color: var(--text-muted); border-color: var(--border); }

.btn-details {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; border-radius: var(--radius-sm);
    font-size: 0.75rem; font-weight: 600; cursor: pointer;
    background: rgba(255,255,255,0.05); color: white;
    border: 1px solid var(--border); transition: var(--transition);
}
.btn-details:hover { background: rgba(255,255,255,0.1); border-color: var(--tech-blue); }
.btn-details.active { background: rgba(239,68,68,0.1); color: var(--tunisian-red); border-color: rgba(239,68,68,0.2); }

.details-panel { background: rgba(255,255,255,0.01); }
.details-inner { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; padding: 1.5rem; }
.app-card {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem; border-radius: var(--radius-md);
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
}
.app-job-name { font-weight: 600; font-size: 0.85rem; color: white; display: flex; align-items: center; gap: 8px; }
.app-status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 10px; border-radius: var(--radius-full);
    font-size: 0.7rem; font-weight: 600; border: 1px solid;
}
</style>
</head>
<body class="page-anim">

    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>

    <div class="admin-layout">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <div class="logo-container">
                <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
                <div class="admin-version">Admin Control v1.0</div>
            </div>
            <nav class="admin-nav">
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-users-viewfinder"></i> Gestion Users</a>
                <a href="dashboard.php" class="admin-nav-item"><i class="fa-solid fa-sitemap"></i> Flux de Missions</a>
                <a href="admin_freelancers.php" class="admin-nav-item active"><i class="fa-solid fa-user-tie"></i> Freelancers</a>
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-store"></i> Marketplace <i class="fa-solid fa-chevron-right arrow"></i></a>
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-shield-halved"></i> Securite</a>
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-comment-dots"></i> Messagerie</a>
            </nav>
            <div class="admin-sidebar-user">
                <div class="avatar">A</div>
                <div class="info">
                    <div class="name">Admin</div>
                    <div class="role">Superviseur</div>
                </div>
                <a href="../frontoffice/home.php" class="logout-btn" title="Quitter"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </aside>

        <!-- MAIN -->
        <main class="admin-main">
            <!-- TOPBAR -->
            <header class="admin-topbar">
                <form class="admin-search" method="GET" novalidate>
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="q" placeholder="Rechercher un freelancer..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" style="display:none;"></button>
                </form>
                <div class="admin-top-actions">
                    <button class="theme-toggle-btn admin-icon-btn" title="Mode clair"><i class="fa-solid fa-sun"></i></button>
                </div>
            </header>

            <!-- CONTENT -->
            <div class="admin-content">
                <h1 class="admin-page-title">Gestion des <span>Freelancers</span></h1>

                <!-- STAT CARDS -->
                <div class="admin-grid-4">
                    <div class="glass-card">
                        <div class="stat-card-header">
                            <span>Total Freelancers</span>
                            <div class="stat-card-icon"><i class="fa-solid fa-users"></i></div>
                        </div>
                        <div class="stat-card-value" id="count-freelancers">0</div>
                        <div class="stat-card-trend"><span class="trend-up"><i class="fa-solid fa-arrow-up"></i> 8%</span> inscrits</div>
                    </div>
                    <div class="glass-card">
                        <div class="stat-card-header">
                            <span>Candidatures</span>
                            <div class="stat-card-icon" style="background:rgba(6,185,129,0.1); color:var(--tech-green);"><i class="fa-solid fa-paper-plane"></i></div>
                        </div>
                        <div class="stat-card-value" id="count-apps">0</div>
                        <div class="stat-card-trend">Offres postulées</div>
                    </div>
                    <div class="glass-card" style="grid-column: span 2; display: flex; flex-direction: column;">
                        <div class="stat-card-header">
                            <span>Croissance</span>
                            <div class="stat-card-icon" style="background:rgba(139,92,246,0.1); color:#8b5cf6;"><i class="fa-solid fa-chart-line"></i></div>
                        </div>
                        <div style="flex: 1; min-height: 80px; position: relative;">
                            <canvas id="growthChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="glass-card" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Freelancer</th>
                                <th>Email</th>
                                <th style="text-align:center;">Missions</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php if (empty($freelancers)): ?>
                        <tr><td colspan="5">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-users-slash"></i></div>
                                <p class="empty-text">Aucun freelancer trouvé.</p>
                            </div>
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($freelancers as $i => $free): ?>
                        <tr>
                            <td><span style="color:var(--text-muted); font-family:'JetBrains Mono'; font-size:0.8rem;"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></span></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="freelancer-avatar"><?= strtoupper(substr($free['prenom'],0,1).substr($free['nom'],0,1)) ?></div>
                                    <div>
                                        <div class="freelancer-name"><?= htmlspecialchars($free['prenom'].' '.$free['nom']) ?></div>
                                        <div class="freelancer-id">#FL-<?= $free['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span style="color:var(--text-light); font-size:0.85rem;"><?= htmlspecialchars($free['email']) ?></span></td>
                            <td style="text-align:center;">
                                <span class="count-badge <?= $free['app_count'] == 0 ? 'zero' : '' ?>">
                                    <i class="fa-solid fa-briefcase" style="font-size:0.7rem; opacity:0.6;"></i>
                                    <?= $free['app_count'] ?>
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <?php if ($free['app_count'] > 0): ?>
                                    <button class="btn-details js-toggle-details" data-id="<?= $free['id'] ?>">
                                        <i class="fa-solid fa-eye"></i> Détails
                                    </button>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:0.8rem;">Aucune</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($free['app_count'] > 0): ?>
                        <tr id="details-<?= $free['id'] ?>" class="details-panel" style="display:none;">
                            <td colspan="5">
                                <div class="details-inner">
                                    <?php foreach ($free['applications'] as $app): $b = statutBadgeApp($app['status']); ?>
                                        <div class="app-card">
                                            <div class="app-job-name"><i class="fa-solid fa-briefcase"></i> <?= htmlspecialchars($app['job_title']) ?></div>
                                            <span class="app-status-badge" style="background:<?= $b['bg'] ?>; color:<?= $b['color'] ?>; border-color:<?= $b['border'] ?>;">
                                                <i class="fa-solid <?= $b['icon'] ?>"></i>
                                                <?= $b['label'] ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div><!-- /.admin-content -->
    </main>
</div>

<script>
// Toggle details rows
document.querySelectorAll('.js-toggle-details').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const row = document.getElementById('details-' + id);
        const open = row.style.display !== 'none';
        row.style.display = open ? 'none' : 'table-row';
        btn.classList.toggle('active', !open);
        btn.innerHTML = open
            ? '<i class="fa-solid fa-eye"></i> Détails'
            : '<i class="fa-solid fa-eye-slash"></i> Masquer';
    });
});

// Animated counters
function animateCount(el, target) {
    let current = 0, step = Math.ceil(target / 40);
    const t = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current;
        if (current >= target) clearInterval(t);
    }, 40);
}
animateCount(document.getElementById('count-freelancers'), <?= (int)($stats['total_freelancers'] ?? 0) ?>);
animateCount(document.getElementById('count-apps'), <?= (int)($stats['total_applications'] ?? 0) ?>);

// Growth Chart
Chart.defaults.color = '#64748b';
Chart.defaults.font.family = "'Space Grotesk', sans-serif";

const growthData = <?= json_encode($growthData ?? []) ?>;
const labels = growthData.length > 2
    ? growthData.map(d => { let dt = new Date(d.date_insc); return dt.toLocaleDateString('fr-FR', {day:'2-digit',month:'2-digit'}); })
    : ['15/04','17/04','19/04','21/04','23/04','25/04','27/04','29/04'];
const counts = growthData.length > 2
    ? growthData.map(d => d.count)
    : [3,7,2,0,5,8,1,4];

const ctx = document.getElementById('growthChart').getContext('2d');
const grad = ctx.createLinearGradient(0,0,0,110);
grad.addColorStop(0,'rgba(99,102,241,0.4)');
grad.addColorStop(1,'rgba(99,102,241,0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels,
        datasets: [{
            label: 'Missions',
            data: counts,
            borderColor: '#6366f1',
            backgroundColor: grad,
            borderWidth: 2.5,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#6366f1',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
        scales: {
            y: { display: false, beginAtZero: true },
            x: {
                grid: { color: 'rgba(99,102,241,0.08)' },
                ticks: { color: '#475569', font: { size: 10, weight: '600' } }
            }
        }
    }
});
</script>
</body>
</html>
