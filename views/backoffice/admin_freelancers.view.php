<?php
// views/backoffice/admin_freelancers.view.php
function statutBadgeApp($s) {
    $map = [
        'pending'  => ['label'=>'En attente', 'color'=>'#f59e0b', 'bg'=>'rgba(245,158,11,0.1)'],
        'approved' => ['label'=>'Acceptée', 'color'=>'#10b981', 'bg'=>'rgba(16,185,129,0.1)'],
        'rejected' => ['label'=>'Refusée', 'color'=>'#ef4444', 'bg'=>'rgba(239,68,68,0.1)']
    ];
    return $map[$s] ?? $map['pending'];
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelancers & Stats | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="admin.css?v=<?= time() ?>">
    <style>
        .free-details {
            display: none;
            background: rgba(0,0,0,0.2);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 10px;
        }
        .free-details.active {
            display: block;
            animation: fadeInDown 0.3s ease forwards;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .app-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 6px;
            margin-bottom: 8px;
        }
        .app-item:last-child { margin-bottom: 0; }
    </style>
</head>
<body class="page-anim">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
            <nav class="admin-nav">
                <a href="dashboard.php" class="admin-nav-item"><i class="fa-solid fa-briefcase"></i> Missions</a>
                <a href="add_job_admin.php" class="admin-nav-item"><i class="fa-solid fa-plus-circle"></i> Ajouter</a>
                <a href="admin_freelancers.php" class="admin-nav-item active"><i class="fa-solid fa-users"></i> Freelancers</a>
            </nav>
            <div class="admin-sidebar-user">
                <div class="avatar">A</div>
                <div class="info">
                    <div class="name">Admin</div>
                    <div class="role">Superviseur</div>
                </div>
                <a href="../frontoffice/home.php" class="logout-btn" title="Quitter Admin"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <form class="admin-search" method="GET" novalidate>
                    <input type="text" name="q" placeholder="Rechercher un freelancer..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" style="display:none;"></button>
                </form>
            </header>

            <div class="admin-content">
                <h1 class="admin-page-title">Statistiques <span>Freelancers</span></h1>

                <div class="admin-grid-4">
                    <div class="glass-card">
                        <div class="stat-card-header">
                            <span>Total Freelancers</span>
                            <div class="stat-card-icon" style="background:rgba(59,130,246,0.1); color:var(--tech-blue);"><i class="fa-solid fa-users"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $stats['total_freelancers'] ?></div>
                        <div class="stat-card-trend">inscrits sur la plateforme</div>
                    </div>
                    <div class="glass-card">
                        <div class="stat-card-header">
                            <span>Candidatures Totales</span>
                            <div class="stat-card-icon" style="background:rgba(139,92,246,0.1); color:var(--tech-purple);"><i class="fa-solid fa-paper-plane"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $stats['total_applications'] ?></div>
                        <div class="stat-card-trend">soumises par les freelancers</div>
                    </div>
                </div>

                <div class="glass-card" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Freelancer</th>
                                <th>Email</th>
                                <th style="text-align: center;">Candidatures</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($freelancers)): ?>
                                <tr><td colspan="4" style="text-align:center; padding:2rem; color:var(--text-muted);">Aucun freelancer trouvé.</td></tr>
                            <?php else: ?>
                                <?php foreach ($freelancers as $free): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            <div style="width: 35px; height: 35px; background: linear-gradient(135deg, var(--tech-blue), #2563eb); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; color: white;">
                                                <?= strtoupper(substr($free['prenom'], 0, 1) . substr($free['nom'], 0, 1)) ?>
                                            </div>
                                            <span style="font-weight:600; color:white;"><?= htmlspecialchars($free['prenom'] . ' ' . $free['nom']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($free['email']) ?></td>
                                    <td style="text-align: center;">
                                        <span style="background:rgba(59,130,246,0.1); color:var(--tech-blue); padding:4px 12px; border-radius:12px; font-weight:700; font-size:0.85rem;">
                                            <?= $free['app_count'] ?> mission(s)
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($free['app_count'] > 0): ?>
                                            <button class="btn js-toggle-details" data-id="<?= $free['id'] ?>" style="background:rgba(255,255,255,0.05); color:white; border:1px solid rgba(255,255,255,0.1); padding:6px 12px; border-radius:4px; font-size:0.75rem; cursor:pointer;">
                                                <i class="fa-solid fa-eye"></i> Voir détails
                                            </button>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted); font-size:0.85rem;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($free['app_count'] > 0): ?>
                                <tr id="details-<?= $free['id'] ?>" class="free-details-row" style="display:none; background:rgba(0,0,0,0.2);">
                                    <td colspan="4" style="padding:1.5rem 2rem;">
                                        <h4 style="color:white; margin-bottom:1rem; font-size:0.9rem; text-transform:uppercase; letter-spacing:1px;"><i class="fa-solid fa-list-check" style="margin-right:8px; color:var(--tech-blue);"></i> Missions postulées</h4>
                                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(350px, 1fr)); gap:15px;">
                                            <?php foreach ($free['applications'] as $app): $b = statutBadgeApp($app['status']); ?>
                                                <div class="app-item">
                                                    <span style="color:white; font-weight:500; font-size:0.95rem;"><i class="fa-solid fa-briefcase" style="color:var(--text-muted); margin-right:8px;"></i> <?= htmlspecialchars($app['job_title']) ?></span>
                                                    <span style="background:<?= $b['bg'] ?>; color:<?= $b['color'] ?>; padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:700;"><?= $b['label'] ?></span>
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
            </div>
        </main>
    </div>

    <script>
        document.querySelectorAll('.js-toggle-details').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const detailsRow = document.getElementById('details-' + id);
                if (detailsRow.style.display === 'none') {
                    detailsRow.style.display = 'table-row';
                    btn.innerHTML = '<i class="fa-solid fa-eye-slash"></i> Masquer';
                } else {
                    detailsRow.style.display = 'none';
                    btn.innerHTML = '<i class="fa-solid fa-eye"></i> Voir détails';
                }
            });
        });
    </script>
</body>
</html>
