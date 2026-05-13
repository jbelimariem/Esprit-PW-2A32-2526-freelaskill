<?php
// views/backoffice/admin_freelancers.view.php
function statutBadgeApp($s) {
    $map = [
        'pending'  => ['label'=>'En attente', 'color'=>'#f59e0b', 'bg'=>'rgba(245,158,11,0.15)',  'border'=>'rgba(245,158,11,0.3)',  'icon'=>'fa-clock'],
        'approved' => ['label'=>'Acceptée',   'color'=>'#10b981', 'bg'=>'rgba(16,185,129,0.15)',   'border'=>'rgba(16,185,129,0.3)',   'icon'=>'fa-check-circle'],
        'rejected' => ['label'=>'Refusée',    'color'=>'#ef4444', 'bg'=>'rgba(239,68,68,0.15)',    'border'=>'rgba(239,68,68,0.3)',    'icon'=>'fa-times-circle'],
    ];
    return $map[$s] ?? $map['pending'];
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelancers — Admin | FreelaSkill</title>
    <meta name="description" content="Gestion des freelancers sur la plateforme FreelaSkill.">
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin_v2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="../assets/theme.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .freelancer-avatar {
            width:40px; height:40px; border-radius:12px;
            background: linear-gradient(135deg, var(--tech-blue), #8b5cf6);
            display:flex; align-items:center; justify-content:center;
            font-weight:700; font-size:.9rem; color:white;
            box-shadow: 0 4px 12px rgba(59,130,246,.3);
        }
        .freelancer-name { font-weight:600; color:var(--text-strong); font-size:.95rem; }
        .freelancer-id   { font-size:.75rem; color:var(--text-muted); font-family:'JetBrains Mono',monospace; }
        .count-badge {
            display:inline-flex; align-items:center; gap:6px;
            padding:4px 12px; border-radius:var(--radius-full);
            font-size:.75rem; font-weight:600;
            background:rgba(59,130,246,.1); color:var(--tech-blue);
            border:1px solid rgba(59,130,246,.2);
        }
        .count-badge.zero { background:rgba(255,255,255,.03); color:var(--text-muted); border-color:var(--border); }
        .btn-details {
            display:inline-flex; align-items:center; gap:6px;
            padding:6px 12px; border-radius:var(--radius-sm);
            font-size:.75rem; font-weight:600; cursor:pointer;
            background:rgba(255,255,255,.05); color:var(--text-strong);
            border:1px solid var(--border); transition:var(--transition);
        }
        .btn-details:hover { background:rgba(255,255,255,.1); border-color:var(--tech-blue); }
        .btn-details.active { background:rgba(239,68,68,.1); color:var(--tunisian-red); border-color:rgba(239,68,68,.2); }
        .details-panel { background:rgba(255,255,255,.01); }
        .details-inner { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1rem; padding:1.5rem; }
        .app-card {
            display:flex; justify-content:space-between; align-items:center;
            padding:1rem; border-radius:var(--radius-md);
            background:rgba(255,255,255,.03); border:1px solid var(--border);
        }
        .app-job-name { font-weight:600; font-size:.85rem; color:var(--text-strong); display:flex; align-items:center; gap:8px; }
        .app-status-badge {
            display:inline-flex; align-items:center; gap:6px;
            padding:4px 10px; border-radius:var(--radius-full);
            font-size:.7rem; font-weight:600; border:1px solid;
        }
    </style>
</head>
<body>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- MAIN -->
    <main class="main-panel">

        <!-- HEADER -->
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2.5rem;" class="animate-up">
            <div>
                <h1 class="admin-page-title">Gestion des <span style="color:var(--tech-blue)">Freelancers</span></h1>
                <p style="color:var(--text-muted);font-size:.9rem;">Visualisez les freelancers inscrits et leurs candidatures.</p>
            </div>
            <div style="display:flex;align-items:center;gap:1rem;">
                <button type="button" class="theme-toggle" data-theme-toggle>
                    <i class="fa-solid fa-sun" data-theme-icon></i>
                    <span data-theme-label>Jour</span>
                </button>
            </div>
        </header>

        <!-- STAT CARDS -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2rem;" class="animate-up">
            <div class="stat-card stat-card--blue">
                <div class="stat-card__icon"><i class="fa-solid fa-users"></i></div>
                <div class="stat-card__body">
                    <p class="stat-card__label">Total Freelancers</p>
                    <h2 class="stat-card__value" id="count-freelancers">0</h2>
                    <div class="stat-card__bar-wrap"><div class="stat-card__bar" style="width:100%;background:rgba(59,130,246,.5);"></div></div>
                    <p class="stat-card__sub"><i class="fa-solid fa-arrow-trend-up"></i> Inscrits sur la plateforme</p>
                </div>
            </div>
            <div class="stat-card stat-card--green">
                <div class="stat-card__icon"><i class="fa-solid fa-paper-plane"></i></div>
                <div class="stat-card__body">
                    <p class="stat-card__label">Candidatures</p>
                    <h2 class="stat-card__value" id="count-apps">0</h2>
                    <div class="stat-card__bar-wrap"><div class="stat-card__bar" style="width:80%;background:rgba(16,185,129,.6);"></div></div>
                    <p class="stat-card__sub"><i class="fa-solid fa-circle-check"></i> Offres postulées</p>
                </div>
            </div>
            <div class="stat-card stat-card--purple" style="grid-column:span 2;">
                <div class="stat-card__icon"><i class="fa-solid fa-chart-line"></i></div>
                <div class="stat-card__body" style="flex:1;">
                    <p class="stat-card__label">Croissance des inscriptions</p>
                    <div style="flex:1; min-height:70px; position:relative; margin-top:.5rem;">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <section class="admin-section animate-up">
            <div class="section-title" style="margin-bottom:1.5rem;">
                <i class="fa-solid fa-user-tie" style="color:var(--tech-blue);"></i>
                Liste des Freelancers
            </div>

            <?php if (!empty($_GET['q'])): ?>
            <div style="margin-bottom:1rem; font-size:.85rem; color:var(--text-muted);">
                Résultats pour : <strong style="color:white;"><?= htmlspecialchars($_GET['q']) ?></strong>
                <a href="admin_freelancers.php" style="margin-left:.5rem; color:#ef4444; font-size:.8rem;"><i class="fa-solid fa-xmark"></i> Effacer</a>
            </div>
            <?php endif; ?>

            <!-- Search -->
            <form method="GET" action="admin_freelancers.php" style="display:flex;gap:.75rem;margin-bottom:1.5rem;">
                <div class="admin-filter-wrap" style="flex:1;">
                    <i class="fa-solid fa-magnifying-glass admin-filter-icon"></i>
                    <input type="text" name="q" class="admin-filter-input"
                           placeholder="Rechercher par nom ou email..."
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                    <i class="fa-solid fa-filter"></i> Filtrer
                </button>
                <a href="admin_freelancers.php" class="btn btn-outline" style="white-space:nowrap;">
                    <i class="fa-solid fa-rotate-left"></i> Réinitialiser
                </a>
            </form>

            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Freelancer</th>
                            <th>Email</th>
                            <th style="text-align:center;">Missions</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($freelancers)): ?>
                        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:3rem;">
                            <i class="fa-solid fa-users-slash" style="font-size:2rem;display:block;margin-bottom:.75rem;opacity:.3;"></i>
                            Aucun freelancer trouvé.
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($freelancers as $i => $free): ?>
                        <tr>
                            <td style="font-family:'JetBrains Mono',monospace;font-size:.8rem;color:#475569;"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div class="freelancer-avatar"><?= strtoupper(substr($free['prenom'],0,1).substr($free['nom'],0,1)) ?></div>
                                    <div>
                                        <div class="freelancer-name"><?= htmlspecialchars($free['prenom'].' '.$free['nom']) ?></div>
                                        <div class="freelancer-id">#FL-<?= $free['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="color:var(--text-muted);font-size:.85rem;"><?= htmlspecialchars($free['email']) ?></td>
                            <td style="text-align:center;">
                                <span class="count-badge <?= $free['app_count'] == 0 ? 'zero' : '' ?>">
                                    <i class="fa-solid fa-briefcase" style="font-size:.7rem;opacity:.6;"></i>
                                    <?= $free['app_count'] ?>
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <?php if ($free['app_count'] > 0): ?>
                                    <button class="btn-details js-toggle-details" data-id="<?= $free['id'] ?>">
                                        <i class="fa-solid fa-eye"></i> Détails
                                    </button>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);font-size:.8rem;">Aucune</span>
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
                                        <span class="app-status-badge" style="background:<?= $b['bg'] ?>;color:<?= $b['color'] ?>;border-color:<?= $b['border'] ?>;">
                                            <i class="fa-solid <?= $b['icon'] ?>"></i> <?= $b['label'] ?>
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

            <div style="margin-top:1rem;font-size:.82rem;color:var(--text-muted);">
                <strong><?= count($freelancers) ?></strong> freelancer<?= count($freelancers) > 1 ? 's' : '' ?> affiché<?= count($freelancers) > 1 ? 's' : '' ?>
            </div>
        </section>

    </main>
</div>

<script>
document.querySelectorAll('.js-toggle-details').forEach(btn => {
    btn.addEventListener('click', () => {
        const id  = btn.dataset.id;
        const row = document.getElementById('details-' + id);
        const open = row.style.display !== 'none';
        row.style.display = open ? 'none' : 'table-row';
        btn.classList.toggle('active', !open);
        btn.innerHTML = open
            ? '<i class="fa-solid fa-eye"></i> Détails'
            : '<i class="fa-solid fa-eye-slash"></i> Masquer';
    });
});

function animateCount(el, target) {
    let current = 0, step = Math.ceil(target / 40);
    const t = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current;
        if (current >= target) clearInterval(t);
    }, 40);
}
animateCount(document.getElementById('count-freelancers'), <?= (int)($stats['total_freelancers'] ?? 0) ?>);
animateCount(document.getElementById('count-apps'),        <?= (int)($stats['total_applications'] ?? 0) ?>);

Chart.defaults.color = '#64748b';
Chart.defaults.font.family = "'Space Grotesk', sans-serif";
const growthData = <?= json_encode($growthData ?? []) ?>;
const labels = growthData.length > 2
    ? growthData.map(d => { let dt = new Date(d.date_insc); return dt.toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit'}); })
    : ['15/04','17/04','19/04','21/04','23/04','25/04','27/04','29/04'];
const counts = growthData.length > 2 ? growthData.map(d => d.count) : [3,7,2,0,5,8,1,4];
const ctx = document.getElementById('growthChart').getContext('2d');
const grad = ctx.createLinearGradient(0,0,0,110);
grad.addColorStop(0,'rgba(99,102,241,0.4)');
grad.addColorStop(1,'rgba(99,102,241,0)');
new Chart(ctx, {
    type:'line', data:{ labels, datasets:[{ data:counts, borderColor:'#6366f1', backgroundColor:grad, borderWidth:2.5, pointBackgroundColor:'#fff', pointBorderColor:'#6366f1', pointBorderWidth:2, pointRadius:4, tension:0.4, fill:true }] },
    options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{display:false,beginAtZero:true}, x:{grid:{color:'rgba(99,102,241,.08)'}, ticks:{color:'#475569',font:{size:10}}} } }
});
</script>
</body>
</html>
