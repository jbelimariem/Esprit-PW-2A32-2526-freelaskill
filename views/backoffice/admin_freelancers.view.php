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
:root {
    --accent-1:#6366f1;
    --accent-2:#8b5cf6;
    --accent-3:#06b6d4;
    --accent-gold:#f59e0b;
    --accent-green:#10b981;
    --accent-red:#ef4444;
}

/* ── PAGE BACKGROUND ── */
body{background:linear-gradient(135deg,#020617 0%,#0a0f2e 50%,#020617 100%);min-height:100vh;}

/* ── ANIMATED BG ORBS ── */
.bg-orb{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0;animation:orb-float 8s ease-in-out infinite;}
.bg-orb-1{width:500px;height:500px;background:radial-gradient(circle,rgba(99,102,241,0.12),transparent 70%);top:-100px;right:10%;animation-delay:0s;}
.bg-orb-2{width:400px;height:400px;background:radial-gradient(circle,rgba(6,182,212,0.08),transparent 70%);bottom:10%;left:5%;animation-delay:-3s;}
.bg-orb-3{width:300px;height:300px;background:radial-gradient(circle,rgba(139,92,246,0.1),transparent 70%);top:40%;left:30%;animation-delay:-5s;}
@keyframes orb-float{0%,100%{transform:translateY(0) scale(1);}50%{transform:translateY(-30px) scale(1.05);}}

/* ── SIDEBAR ENHANCED ── */
.admin-sidebar{background:rgba(2,6,23,0.85);border-right:1px solid rgba(99,102,241,0.15);}
.admin-sidebar .logo{background:linear-gradient(135deg,rgba(99,102,241,0.1),transparent);}
.admin-sidebar .logo i{color:var(--accent-1);filter:drop-shadow(0 0 12px rgba(99,102,241,0.6));}
.admin-nav-item.active{background:linear-gradient(90deg,rgba(99,102,241,0.2),transparent);border-left:3px solid var(--accent-1);}
.admin-nav-item.active i{color:var(--accent-1);}
.admin-nav-item:hover{background:rgba(99,102,241,0.08);}

/* ── TOPBAR ── */
.admin-topbar{background:rgba(2,6,23,0.7);backdrop-filter:blur(20px);border-bottom:1px solid rgba(99,102,241,0.1);}
.admin-search{background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.15);transition:all .3s;}
.admin-search:focus-within{border-color:rgba(99,102,241,0.5);box-shadow:0 0 0 3px rgba(99,102,241,0.1);}
.search-icon{color:var(--accent-1);}

/* ── PAGE TITLE ── */
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;}
.admin-page-title{font-size:2rem;font-weight:700;background:linear-gradient(135deg,#fff 30%,var(--accent-1));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.page-subtitle{color:#94a3b8;font-size:0.9rem;margin-top:4px;}
.title-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);color:var(--accent-1);padding:6px 14px;border-radius:20px;font-size:0.8rem;font-weight:600;}

/* ── STAT CARDS ENHANCED ── */
.stat-grid{display:grid;grid-template-columns:1fr 1fr 2fr;gap:1.5rem;margin-bottom:2rem;}
.stat-card-new{position:relative;overflow:hidden;border-radius:18px;padding:1.75rem;border:1px solid;animation:slideUp .5s ease both;}
.stat-card-new::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;}
.stat-card-new.blue{background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(2,6,23,0.8));border-color:rgba(99,102,241,0.2);}
.stat-card-new.blue::before{background:linear-gradient(90deg,var(--accent-1),var(--accent-3));}
.stat-card-new.cyan{background:linear-gradient(135deg,rgba(6,182,212,0.12),rgba(2,6,23,0.8));border-color:rgba(6,182,212,0.2);}
.stat-card-new.cyan::before{background:linear-gradient(90deg,var(--accent-3),#22d3ee);}
.stat-card-new.chart-card{background:linear-gradient(135deg,rgba(139,92,246,0.08),rgba(2,6,23,0.8));border-color:rgba(139,92,246,0.15);}
.stat-card-new:nth-child(1){animation-delay:.1s;}
.stat-card-new:nth-child(2){animation-delay:.2s;}
.stat-card-new:nth-child(3){animation-delay:.3s;}
@keyframes slideUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}

.stat-icon-wrap{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin-bottom:1rem;}
.blue .stat-icon-wrap{background:rgba(99,102,241,0.2);color:#818cf8;box-shadow:0 0 20px rgba(99,102,241,0.2);}
.cyan .stat-icon-wrap{background:rgba(6,182,212,0.2);color:#22d3ee;box-shadow:0 0 20px rgba(6,182,212,0.2);}
.stat-num{font-size:2.8rem;font-weight:800;color:#fff;font-family:'JetBrains Mono',monospace;line-height:1;}
.stat-lbl{font-size:0.8rem;color:#64748b;text-transform:uppercase;letter-spacing:1.5px;margin-top:.5rem;font-weight:600;}

.chart-card-inner{display:flex;flex-direction:column;height:100%;}
.chart-card-title{font-size:.85rem;font-weight:700;color:#94a3b8;letter-spacing:1px;text-transform:uppercase;margin-bottom:.75rem;display:flex;align-items:center;gap:8px;}
.chart-card-title i{color:var(--accent-2);}
.chart-wrap{position:relative;height:110px;flex:1;}

/* ── TABLE ENHANCED ── */
.table-wrapper{border-radius:18px;overflow:hidden;border:1px solid rgba(99,102,241,0.1);background:rgba(2,6,23,0.6);backdrop-filter:blur(10px);}
.table-header-bar{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid rgba(99,102,241,0.1);background:rgba(99,102,241,0.04);}
.table-title-h{font-size:1rem;font-weight:700;color:#fff;display:flex;align-items:center;gap:10px;}
.table-title-h i{color:var(--accent-1);}
.table-count{font-size:.8rem;background:rgba(99,102,241,0.15);color:var(--accent-1);padding:4px 12px;border-radius:20px;font-weight:600;}

.data-table th{background:rgba(99,102,241,0.05);color:#64748b;font-size:.7rem;letter-spacing:2px;text-transform:uppercase;padding:1rem 1.25rem;border-bottom:1px solid rgba(99,102,241,0.1);}
.data-table td{padding:1rem 1.25rem;border-bottom:1px solid rgba(255,255,255,0.03);vertical-align:middle;}
.data-table tbody tr{transition:background .2s,transform .2s;animation:rowFade .4s ease both;}
.data-table tbody tr:nth-child(1){animation-delay:.05s;}
.data-table tbody tr:nth-child(2){animation-delay:.1s;}
.data-table tbody tr:nth-child(3){animation-delay:.15s;}
.data-table tbody tr:nth-child(4){animation-delay:.2s;}
.data-table tbody tr:nth-child(5){animation-delay:.25s;}
@keyframes rowFade{from{opacity:0;transform:translateX(-10px);}to{opacity:1;transform:translateX(0);}}
.data-table tbody tr:hover{background:rgba(99,102,241,0.06);}
.data-table tbody tr:last-child td{border-bottom:none;}

/* ── AVATAR ENHANCED ── */
.freelancer-avatar{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--accent-1),var(--accent-2));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;color:#fff;box-shadow:0 4px 15px rgba(99,102,241,0.3);position:relative;flex-shrink:0;}
.freelancer-avatar::after{content:'';position:absolute;inset:-2px;border-radius:14px;background:linear-gradient(135deg,var(--accent-1),var(--accent-3));z-index:-1;opacity:.4;}
.freelancer-name{font-weight:700;color:#fff;font-size:.95rem;}
.freelancer-id{font-size:.75rem;color:#475569;font-family:'JetBrains Mono',monospace;}

/* ── COUNT BADGE ── */
.count-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:20px;font-weight:700;font-size:.82rem;background:rgba(99,102,241,0.12);color:#818cf8;border:1px solid rgba(99,102,241,0.2);}
.count-badge.zero{background:rgba(255,255,255,0.03);color:#475569;border-color:rgba(255,255,255,0.05);}

/* ── DETAILS TOGGLE BTN ── */
.btn-details{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:10px;font-size:.8rem;font-weight:600;cursor:pointer;border:1px solid rgba(99,102,241,0.25);background:rgba(99,102,241,0.08);color:#818cf8;transition:all .25s;font-family:inherit;}
.btn-details:hover{background:rgba(99,102,241,0.2);border-color:rgba(99,102,241,0.5);transform:translateY(-1px);box-shadow:0 4px 15px rgba(99,102,241,0.2);}
.btn-details.active{background:rgba(239,68,68,0.08);border-color:rgba(239,68,68,0.25);color:#f87171;}

/* ── DETAILS ROW ── */
.details-panel{background:rgba(99,102,241,0.04);border-top:1px solid rgba(99,102,241,0.08);}
.details-panel td{padding:1.5rem 2rem;}
.details-inner{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px;}
.details-title{font-size:.75rem;font-weight:700;color:#64748b;letter-spacing:2px;text-transform:uppercase;margin-bottom:1rem;display:flex;align-items:center;gap:8px;}
.details-title i{color:var(--accent-1);}

.app-card{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-radius:12px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.02);transition:all .2s;gap:12px;}
.app-card:hover{background:rgba(99,102,241,0.06);border-color:rgba(99,102,241,0.15);transform:translateX(4px);}
.app-job-name{color:#e2e8f0;font-weight:600;font-size:.9rem;display:flex;align-items:center;gap:8px;}
.app-job-name i{color:#475569;}
.app-status-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:.72rem;font-weight:700;border:1px solid;white-space:nowrap;}

/* ── EMPTY STATE ── */
.empty-state{text-align:center;padding:4rem 2rem;}
.empty-icon{font-size:4rem;color:#1e293b;margin-bottom:1rem;animation:pulse 2s ease-in-out infinite;}
@keyframes pulse{0%,100%{opacity:.5;}50%{opacity:1;}}
.empty-text{color:#475569;font-size:1rem;}

/* ── PAGE ANIMATION ── */
.page-anim{animation:pageIn .6s ease-out;}
@keyframes pageIn{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}

/* ── SCROLLBAR ── */
::-webkit-scrollbar{width:5px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:rgba(99,102,241,0.3);border-radius:4px;}

/* ── GLOWING DOTS ── */
.pulse-dot{width:8px;height:8px;border-radius:50%;background:var(--accent-green);box-shadow:0 0 8px var(--accent-green);animation:blink 1.5s ease-in-out infinite;}
@keyframes blink{0%,100%{opacity:1;}50%{opacity:.3;}}
</style>
</head>
<body class="page-anim">

<!-- Animated background orbs -->
<div class="bg-orb bg-orb-1"></div>
<div class="bg-orb bg-orb-2"></div>
<div class="bg-orb bg-orb-3"></div>

<div class="admin-layout" style="position:relative;z-index:1;">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
        <nav class="admin-nav">
            <a href="dashboard.php" class="admin-nav-item"><i class="fa-solid fa-briefcase"></i> Missions</a>
            <a href="add_job_admin.php" class="admin-nav-item"><i class="fa-solid fa-plus-circle"></i> Ajouter</a>
            <a href="admin_freelancers.php" class="admin-nav-item active"><i class="fa-solid fa-users"></i> Freelancers</a>
            <button onclick="return false;" class="admin-nav-item" style="width:100%;border:none;background:none;cursor:default;text-align:left;font-family:inherit;color:#334155;"><i class="fa-solid fa-file-pdf"></i> PDF</button>
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
            <form class="admin-search" method="GET" novalidate style="display:flex;align-items:center;gap:.75rem;">
                <i class="fa-solid fa-magnifying-glass search-icon" style="color:#6366f1;"></i>
                <input type="text" name="q" placeholder="Rechercher un freelancer par nom ou email..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="background:transparent;border:none;outline:none;color:#fff;font-family:inherit;font-size:.9rem;width:100%;">
                <button type="submit" style="display:none;"></button>
            </form>
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:.82rem;">
                    <div class="pulse-dot"></div>
                    Système actif
                </div>
                <button class="theme-toggle-btn" title="Mode clair"><i class="fa-solid fa-sun"></i></button>
            </div>
        </header>

        <!-- CONTENT -->
        <div class="admin-content">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="admin-page-title">Freelancers <i class="fa-solid fa-users" style="font-size:1.5rem;-webkit-text-fill-color:#6366f1;"></i></h1>
                    <p class="page-subtitle">Gestion et suivi des freelancers inscrits sur la plateforme</p>
                </div>
                <div class="title-badge">
                    <i class="fa-solid fa-circle-check"></i>
                    <?= $stats['total_freelancers'] ?? 0 ?> membres actifs
                </div>
            </div>

            <!-- STAT CARDS -->
            <div class="stat-grid">
                <!-- Card 1 -->
                <div class="stat-card-new blue">
                    <div class="stat-icon-wrap"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-num" id="count-freelancers">0</div>
                    <div class="stat-lbl">Total Freelancers</div>
                </div>
                <!-- Card 2 -->
                <div class="stat-card-new cyan">
                    <div class="stat-icon-wrap" style="background:rgba(6,182,212,0.2);color:#22d3ee;"><i class="fa-solid fa-paper-plane"></i></div>
                    <div class="stat-num" id="count-apps">0</div>
                    <div class="stat-lbl">Candidatures</div>
                </div>
                <!-- Chart Card -->
                <div class="stat-card-new chart-card">
                    <div class="chart-card-title"><i class="fa-solid fa-chart-area"></i> Croissance des missions</div>
                    <div class="chart-wrap"><canvas id="growthChart"></canvas></div>
                </div>
            </div>

            <!-- FREELANCERS TABLE -->
            <div class="table-wrapper">
                <div class="table-header-bar">
                    <div class="table-title-h">
                        <i class="fa-solid fa-table-list"></i>
                        Liste des Freelancers
                    </div>
                    <span class="table-count"><?= count($freelancers ?? []) ?> résultats</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Freelancer</th>
                            <th>Email</th>
                            <th style="text-align:center;">Candidatures</th>
                            <th>Détails</th>
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
                            <td><span style="color:#334155;font-family:'JetBrains Mono',monospace;font-size:.8rem;"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></span></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div class="freelancer-avatar"><?= strtoupper(substr($free['prenom'],0,1).substr($free['nom'],0,1)) ?></div>
                                    <div>
                                        <div class="freelancer-name"><?= htmlspecialchars($free['prenom'].' '.$free['nom']) ?></div>
                                        <div class="freelancer-id">ID #<?= $free['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="color:#94a3b8;font-size:.88rem;"><?= htmlspecialchars($free['email']) ?></td>
                            <td style="text-align:center;">
                                <span class="count-badge <?= $free['app_count'] == 0 ? 'zero' : '' ?>">
                                    <?php if($free['app_count'] > 0): ?><i class="fa-solid fa-briefcase" style="font-size:.7rem;"></i><?php endif; ?>
                                    <?= $free['app_count'] ?> mission<?= $free['app_count'] > 1 ? 's' : '' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($free['app_count'] > 0): ?>
                                <button class="btn-details js-toggle-details" data-id="<?= $free['id'] ?>">
                                    <i class="fa-solid fa-eye"></i> Voir détails
                                </button>
                                <?php else: ?>
                                <span style="color:#334155;font-size:.85rem;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($free['app_count'] > 0): ?>
                        <tr id="details-<?= $free['id'] ?>" class="details-panel" style="display:none;">
                            <td colspan="5">
                                <div class="details-title"><i class="fa-solid fa-list-check"></i> Missions postulées</div>
                                <div class="details-inner">
                                    <?php foreach ($free['applications'] as $app):
                                        $b = statutBadgeApp($app['status']); ?>
                                    <div class="app-card">
                                        <div class="app-job-name"><i class="fa-solid fa-briefcase"></i> <?= htmlspecialchars($app['job_title']) ?></div>
                                        <span class="app-status-badge" style="background:<?= $b['bg'] ?>;color:<?= $b['color'] ?>;border-color:<?= $b['border'] ?>;">
                                            <i class="fa-solid <?= $b['icon'] ?>" style="font-size:.7rem;"></i>
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
            ? '<i class="fa-solid fa-eye"></i> Voir détails'
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
