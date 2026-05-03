<?php
// views/backoffice/users_dashboard.view.php -- Template dashboard users.
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users - FreelaSkill</title>
    <meta name="description" content="Tableau de bord administrateur - gestion des utilisateurs FreelaSkill.">
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="../assets/theme.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ── Modal ──────────────────────────────────────── */
        .modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.65);
            backdrop-filter: blur(6px);
            z-index: 1000;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity .25s ease;
        }
        .modal-backdrop.open { opacity: 1; pointer-events: all; }
        .modal {
            background: var(--surface-elevated);
            border: 1px solid var(--border);
            border-radius: 20px;
            width: 100%; max-width: 520px;
            padding: 2rem;
            box-shadow: 0 30px 80px rgba(0,0,0,.6);
            transform: translateY(24px) scale(.97);
            transition: transform .3s cubic-bezier(.4,0,.2,1);
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-backdrop.open .modal { transform: translateY(0) scale(1); }
        .modal-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .modal-title { font-size: 1.2rem; font-weight: 700; color: var(--text-strong); }
        .modal-close {
            background: none; border: none; color: #475569;
            font-size: 1.1rem; cursor: pointer; padding: .3rem .5rem;
            border-radius: 8px; transition: all .2s;
        }
        .modal-close:hover { background: var(--surface-2); color: var(--text-strong); }
        .modal .form-group { margin-bottom: 1rem; }
        .modal .form-label {
            display: block; font-size: .82rem; font-weight: 600;
            color: var(--text-muted); margin-bottom: .4rem; letter-spacing: .02em;
        }
        .modal .form-input {
            width: 100%; padding: .65rem 1rem;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 10px; color: var(--text-strong);
            font-family: 'Space Grotesk', sans-serif; font-size: .9rem;
            outline: none; box-sizing: border-box;
            transition: border-color .2s;
        }
        .modal .form-input:focus { border-color: var(--tech-blue); }
        .modal select.form-input option { background: var(--surface-elevated); color: var(--text-strong); }
        .modal-footer { display: flex; gap: .75rem; justify-content: flex-end; margin-top: 1.5rem; }

        /* Password strength */
        .strength-bar {
            height: 4px;
            border-radius: 4px;
            background: rgba(255,255,255,.06);
            margin-top: .5rem;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 4px;
            transition: width .45s cubic-bezier(.4,0,.2,1), background .45s ease;
            width: 0%;
        }
        .password-rules {
            display: flex;
            flex-wrap: nowrap;
            gap: .55rem;
            margin-top: .85rem;
        }
        .password-rule {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .42rem .9rem;
            border-radius: 999px;
            border: 1px solid rgba(71,85,105,.5);
            background: rgba(15,23,42,.7);
            color: #64748b;
            font-size: .78rem;
            font-weight: 500;
            letter-spacing: .01em;
            line-height: 1;
            transition: all .35s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
        }
        .password-rule::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(16,185,129,.15), rgba(5,150,105,.08));
            opacity: 0;
            transition: opacity .35s ease;
        }
        .password-rule .rule-icon {
            font-size: .72rem;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1.5px solid rgba(71,85,105,.6);
            transition: all .35s cubic-bezier(.4,0,.2,1);
            flex-shrink: 0;
        }
        .password-rule .rule-icon i {
            font-size: .6rem;
            color: #475569;
            transition: all .3s;
        }
        .password-rule.is-valid {
            border-color: rgba(16,185,129,.7);
            background: rgba(16,185,129,.12);
            color: #6ee7b7;
            box-shadow: 0 0 0 3px rgba(16,185,129,.1), 0 0 12px rgba(16,185,129,.12);
        }
        .password-rule.is-valid::before { opacity: 1; }
        .password-rule.is-valid .rule-icon {
            background: rgba(16,185,129,.25);
            border-color: rgba(16,185,129,.7);
            animation: rulePop .35s cubic-bezier(.4,0,.2,1);
        }
        .password-rule.is-valid .rule-icon i { color: #10b981; }
        @keyframes rulePop {
            0% { transform: scale(.7); }
            60% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        /* ── Flash ──────────────────────────────────────── */
        .flash {
            display: flex; align-items: flex-start; gap: .9rem;
            padding: .85rem 1.25rem;
            border-radius: 12px; margin-bottom: 1.5rem;
            font-size: .9rem; font-weight: 500;
            animation: slideDown .35s ease;
        }
        .flash-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(255,255,255,.08);
        }
        .flash-copy {
            display: flex;
            flex-direction: column;
            gap: .2rem;
        }
        .flash-title {
            font-size: .92rem;
            font-weight: 700;
            color: var(--text-strong);
        }
        .flash-sub {
            font-size: .82rem;
            color: inherit;
            opacity: .9;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .flash-success { background: rgba(16,185,129,.12); border: 1px solid rgba(16,185,129,.35); color: #6ee7b7; }
        .flash-warning { background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.35); color: #fcd34d; }
        .flash-error   { background: rgba(239,68,68,.12);  border: 1px solid rgba(239,68,68,.35);  color: #fca5a5; }

        /* ── Form errors inside modal ───────────────────── */
        .form-errors {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: 10px; padding: .75rem 1rem;
            margin-bottom: 1rem; font-size: .85rem; color: #fca5a5;
        }
        .field-error {
            margin-top: .45rem;
            color: #fca5a5;
            font-size: .8rem;
        }
        .modal .form-input.input-error {
            border-color: rgba(239,68,68,.55);
        }

        /* ── Add btn ────────────────────────────────────── */
        .btn-add {
            display: inline-flex; align-items: center; gap: .5rem;
            background: linear-gradient(135deg, var(--tech-blue), #3b82f6);
            color: white; padding: .65rem 1.4rem;
            border-radius: var(--radius-full); font-size: .88rem;
            font-weight: 600; border: none; cursor: pointer;
            transition: all .2s; text-decoration: none;
            box-shadow: 0 4px 16px rgba(37,99,235,.3);
        }
        .btn-add:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,.4); }

        /* ── Inline edit/ban btns ───────────────────────── */
        .action-btn {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .35rem .75rem; border-radius: 999px;
            font-size: .76rem; font-weight: 600;
            cursor: pointer; border: none; text-decoration: none;
            transition: all .2s; white-space: nowrap;
        }
        .action-btn-edit   { background: rgba(59,130,246,.15); color: #93c5fd; border: 1px solid rgba(59,130,246,.3); }
        .action-btn-edit:hover { background: rgba(59,130,246,.25); }
        .action-btn-ban    { background: rgba(245,158,11,.12); color: #fcd34d; border: 1px solid rgba(245,158,11,.3); }
        .action-btn-ban:hover { background: rgba(245,158,11,.22); }
        .action-btn-ok     { background: rgba(16,185,129,.12); color: #6ee7b7; border: 1px solid rgba(16,185,129,.3); }
        .action-btn-ok:hover { background: rgba(16,185,129,.22); }
        .action-btn-del    { background: rgba(239,68,68,.12); color: #fca5a5; border: 1px solid rgba(239,68,68,.3); }
        .action-btn-del:hover { background: rgba(239,68,68,.22); }
        .user-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            min-width: max-content;
        }
        .user-actions .action-btn {
            width: 38px;
            height: 38px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .user-actions .action-btn i {
            font-size: .95rem;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div style="padding: 0 0.5rem; margin-bottom: 2rem;">
            <div class="logo">
                <i class="fa-solid fa-shapes" style="color: var(--tunisian-red);"></i>
                Freela<span>Skill</span>
            </div>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; letter-spacing: 1px;">Admin Control v1.0</p>
        </div>
        <div class="nav-item active"><i class="fa-solid fa-users-viewfinder"></i> Gestion Users</div>
        <div class="nav-item"><i class="fa-solid fa-network-wired"></i> Flux de Missions</div>
        <div class="nav-item"><i class="fa-solid fa-store"></i> Marketplace</div>
        <div class="nav-item"><i class="fa-solid fa-shield-halved"></i> Securite</div>
        <div class="nav-item"><i class="fa-solid fa-comments"></i> Messagerie</div>
        <div style="margin-top: auto; padding-top: 2rem;">
            <a href="../frontoffice/profile.php" class="btn btn-outline"
               style="width:100%;font-size:.85rem;padding:.75rem;border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;gap:.5rem;">
                <i class="fa-solid fa-globe"></i> Retour au Hub
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-panel">
        <div class="hero-glow" style="top:-100px;right:-100px;opacity:.4;position:fixed;pointer-events:none;
            width:500px;height:500px;background:radial-gradient(circle,rgba(59,130,246,.15),transparent 60%);"></div>

        <!-- HEADER -->
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2.5rem;" class="animate-up">
            <div>
                <h1 class="admin-page-title">
                    Gestion des <span style="color:var(--tech-blue)">Utilisateurs</span>
                </h1>
                <p style="color:var(--text-muted);font-size:.9rem;">Visualisez, moderez et gerez tous les comptes de la plateforme.</p>
            </div>
            <div style="display:flex;align-items:center;gap:1rem;">
                <button type="button" class="theme-toggle" data-theme-toggle>
                    <i class="fa-solid fa-sun" data-theme-icon></i>
                    <span data-theme-label>Jour</span>
                </button>
                <div class="admin-status-pill">
                    <i class="fa-solid fa-satellite" style="color:var(--tech-green);"></i>
                    <span style="font-size:.85rem;color:var(--text-muted);">Systeme actif</span>
                </div>
                <!-- Add User Button -->
                <button class="btn-add" onclick="openCreateModal()" id="btn-add-user">
                    <i class="fa-solid fa-user-plus"></i> Ajouter un utilisateur
                </button>
            </div>
        </header>

        <!-- FLASH MESSAGE -->
        <?php if ($flash): ?>
            <div class="flash flash-<?php echo $flash[2]; ?>">
                <div class="flash-icon">
                    <i class="fa-solid <?php echo htmlspecialchars($flash[3]); ?>"></i>
                </div>
                <div class="flash-copy">
                    <div class="flash-title"><?php echo htmlspecialchars($flash[0]); ?></div>
                    <div class="flash-sub"><?php echo htmlspecialchars($flash[1]); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- METRICS ROW -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2rem;" class="animate-up">

            <!-- Card 1 -->
            <div class="stat-card stat-card--blue">
                <div class="stat-card__icon"><i class="fa-solid fa-users"></i></div>
                <div class="stat-card__body">
                    <p class="stat-card__label">Total Utilisateurs</p>
                    <h2 class="stat-card__value" data-target="<?php echo $totalUsers; ?>">0</h2>
                    <div class="stat-card__bar-wrap">
                        <div class="stat-card__bar" style="width:100%;background:rgba(59,130,246,.5);"></div>
                    </div>
                    <p class="stat-card__sub"><i class="fa-solid fa-circle-check"></i> <?php echo $totalActive; ?> actifs &nbsp;·&nbsp; <?php echo $totalPending; ?> en attente</p>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="stat-card stat-card--green">
                <div class="stat-card__icon"><i class="fa-solid fa-laptop-code"></i></div>
                <div class="stat-card__body">
                    <p class="stat-card__label">Freelancers</p>
                    <h2 class="stat-card__value" data-target="<?php echo $totalFreelancers; ?>">0</h2>
                    <div class="stat-card__bar-wrap">
                        <div class="stat-card__bar" style="width:<?php echo $pctFreelancer; ?>%;background:rgba(16,185,129,.6);"></div>
                    </div>
                    <p class="stat-card__sub"><i class="fa-solid fa-arrow-trend-up"></i> <?php echo $pctFreelancer; ?>% des comptes</p>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="stat-card stat-card--purple">
                <div class="stat-card__icon"><i class="fa-solid fa-building"></i></div>
                <div class="stat-card__body">
                    <p class="stat-card__label">Clients</p>
                    <h2 class="stat-card__value" data-target="<?php echo $totalClients; ?>">0</h2>
                    <div class="stat-card__bar-wrap">
                        <div class="stat-card__bar" style="width:<?php echo $pctClient; ?>%;background:rgba(139,92,246,.6);"></div>
                    </div>
                    <p class="stat-card__sub"><i class="fa-solid fa-briefcase"></i> <?php echo $pctClient; ?>% des comptes</p>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="stat-card stat-card--red">
                <div class="stat-card__icon"><i class="fa-solid fa-ban"></i></div>
                <div class="stat-card__body">
                    <p class="stat-card__label">Suspendus</p>
                    <h2 class="stat-card__value" data-target="<?php echo $totalBanned; ?>">0</h2>
                    <div class="stat-card__bar-wrap">
                        <div class="stat-card__bar" style="width:<?php echo $pctBanned; ?>%;background:rgba(239,68,68,.6);"></div>
                    </div>
                    <p class="stat-card__sub"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $pctBanned; ?>% taux de suspension</p>
                </div>
            </div>
        </div>

        <!-- CHARTS ROW -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;margin-bottom:2rem;" class="animate-up">

            <!-- Donut Répartition -->
            <div class="chart-card">
                <div class="chart-card__header">
                    <span><i class="fa-solid fa-chart-pie" style="color:var(--tech-blue);"></i> Répartition</span>
                </div>
                <div style="position:relative;height:180px;display:flex;align-items:center;justify-content:center;">
                    <canvas id="donutChart"></canvas>
                    <div class="donut-center">
                        <span class="admin-donut-value" style="font-size:1.6rem;font-weight:700;"><?php echo $totalUsers; ?></span>
                        <span style="font-size:.7rem;color:var(--text-muted);">comptes</span>
                    </div>
                </div>
                <div style="display:flex;gap:1rem;justify-content:center;margin-top:.75rem;flex-wrap:wrap;">
                    <span class="legend-dot" style="--c:#3b82f6;">Freelancers</span>
                    <span class="legend-dot" style="--c:#8b5cf6;">Clients</span>
                    <span class="legend-dot" style="--c:#ef4444;">Suspendus</span>
                </div>
            </div>

            <!-- Bar Chart Statuts -->
            <div class="chart-card">
                <div class="chart-card__header">
                    <span><i class="fa-solid fa-chart-bar" style="color:#10b981;"></i> Statuts</span>
                </div>
                <div style="height:180px;display:flex;align-items:flex-end;gap:.75rem;padding:.5rem .5rem 0;">
                    <?php
                        $barMax = max($totalActive, $totalPending, $totalBanned, 1);
                        $bars = [
                            ['label'=>'Actifs',   'val'=>$totalActive,  'color'=>'#10b981'],
                            ['label'=>'Attente',  'val'=>$totalPending, 'color'=>'#f59e0b'],
                            ['label'=>'Suspendus','val'=>$totalBanned,  'color'=>'#ef4444'],
                        ];
                    ?>
                    <?php foreach($bars as $b): ?>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.4rem;">
                        <span class="admin-bar-value" style="font-size:.75rem;font-weight:700;"><?php echo $b['val']; ?></span>
                        <div style="width:100%;border-radius:6px 6px 0 0;background:<?php echo $b['color']; ?>;opacity:.85;
                                    height:<?php echo max(8, round(($b['val']/$barMax)*130)); ?>px;
                                    transition:height .8s cubic-bezier(.4,0,.2,1);"></div>
                        <span style="font-size:.7rem;color:var(--text-muted);"><?php echo $b['label']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Area Chart -->
            <div class="chart-card">
                <div class="chart-card__header">
                    <span><i class="fa-solid fa-chart-area" style="color:var(--tech-blue);"></i> Croissance</span>
                </div>
                <div style="position:relative;height:180px;display:flex;align-items:center;justify-content:center;padding-top:.5rem;">
                    <canvas id="areaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- HEALTH BAR ROW -->
        <div class="health-bar-card animate-up" style="margin-bottom:2rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <span class="admin-health-title" style="font-weight:700;"><i class="fa-solid fa-heart-pulse" style="color:#ef4444;margin-right:.4rem;"></i>Santé de la plateforme</span>
                <span class="health-score"><?php
                    $score = $totalUsers > 0 ? max(0, 100 - $pctBanned*2) : 100;
                    echo $score;
                ?>% <span style="font-size:.7rem;color:var(--text-muted);">score</span></span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;">
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:.35rem;">
                        <span style="font-size:.78rem;color:var(--text-muted);">Actifs</span>
                        <span style="font-size:.78rem;color:#10b981;font-weight:600;"><?php echo $pctActive; ?>%</span>
                    </div>
                    <div class="hbar-track"><div class="hbar-fill" style="width:<?php echo $pctActive; ?>%;background:linear-gradient(90deg,#10b981,#34d399);"></div></div>
                </div>
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:.35rem;">
                        <span style="font-size:.78rem;color:var(--text-muted);">Freelancers</span>
                        <span style="font-size:.78rem;color:#3b82f6;font-weight:600;"><?php echo $pctFreelancer; ?>%</span>
                    </div>
                    <div class="hbar-track"><div class="hbar-fill" style="width:<?php echo $pctFreelancer; ?>%;background:linear-gradient(90deg,#3b82f6,#60a5fa);"></div></div>
                </div>
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:.35rem;">
                        <span style="font-size:.78rem;color:var(--text-muted);">Suspendus</span>
                        <span style="font-size:.78rem;color:#ef4444;font-weight:600;"><?php echo $pctBanned; ?>%</span>
                    </div>
                    <div class="hbar-track"><div class="hbar-fill" style="width:<?php echo $pctBanned; ?>%;background:linear-gradient(90deg,#ef4444,#f87171);"></div></div>
                </div>
            </div>
        </div>

        <!-- ══ PENDING APPROVALS PANEL ═══════════════════════════════════ -->
        <?php if (!empty($pendingUsers)): ?>
        <section class="pending-panel animate-up" id="pending-section">
            <div class="pending-panel__header">
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <div class="pending-panel__icon-wrap">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="admin-health-title" style="font-size:1.05rem;font-weight:700;">
                            Comptes en attente d'approbation
                        </div>
                        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.1rem;">
                            Ces utilisateurs ont créé un compte et attendent votre validation pour accéder à la plateforme.
                        </div>
                    </div>
                </div>
                <span class="pending-panel__count"><?php echo count($pendingUsers); ?></span>
            </div>

            <div class="pending-list">
                <?php foreach ($pendingUsers as $pu):
                    $pi = strtoupper(mb_substr($pu->getPrenom(),0,1) . mb_substr($pu->getNom(),0,1));
                    $since = date('d/m/Y à H:i', strtotime($pu->getCreatedAt()));
                    $roleBadgeColor = $pu->getRole() === 'freelancer' ? '#3b82f6' : '#8b5cf6';
                    $roleIcon = $pu->getRole() === 'freelancer' ? 'fa-laptop-code' : 'fa-building';
                ?>
                <div class="pending-row" id="pending-row-<?php echo $pu->getId(); ?>">

                    <!-- Avatar -->
                    <div class="pending-avatar">
                        <?php echo $pi; ?>
                        <span class="pending-avatar__pulse"></span>
                    </div>

                    <!-- Name + email -->
                    <div class="pending-info">
                        <div class="name"><?php echo htmlspecialchars($pu->getPrenom() . ' ' . $pu->getNom()); ?></div>
                        <div class="email"><?php echo htmlspecialchars($pu->getEmail()); ?></div>
                    </div>

                    <!-- Role badge -->
                    <span class="badge" style="background:<?php echo $pu->getRole()==='freelancer'?'rgba(59,130,246,.15)':'rgba(139,92,246,.15)'; ?>;color:<?php echo $pu->getRole()==='freelancer'?'#93c5fd':'#c4b5fd'; ?>;border:none;">
                        <i class="fa-solid <?php echo $roleIcon; ?>"></i>
                        <?php echo ucfirst($pu->getRole()); ?>
                    </span>

                    <!-- Date -->
                    <span style="font-size:.72rem;color:#64748b;white-space:nowrap;">
                        <i class="fa-regular fa-clock"></i> <?php echo $since; ?>
                    </span>

                    <!-- Approve -->
                    <a href="?action=activate&id=<?php echo $pu->getId(); ?>"
                       class="approve-btn approve-btn--yes"
                       onclick="return confirmApprove(<?php echo $pu->getId(); ?>, '<?php echo htmlspecialchars($pu->getPrenom()); ?>');"
                       title="Approuver">
                        <i class="fa-solid fa-circle-check"></i> Approuver
                    </a>

                    <!-- Reject -->
                    <a href="?action=reject&id=<?php echo $pu->getId(); ?>"
                       class="approve-btn approve-btn--no"
                       onclick="return confirm('Refuser le compte de <?php echo htmlspecialchars($pu->getPrenom().' '.$pu->getNom()); ?> ? L\'utilisateur sera notifie lors de sa prochaine connexion.');"
                       title="Rejeter">
                        <i class="fa-solid fa-xmark"></i> Rejeter
                    </a>

                </div>

                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- FILTERS + TABLE -->
        <section class="admin-section animate-up">
            <div class="section-title" style="margin-bottom:1.5rem;">
                <i class="fa-solid fa-users" style="color:var(--tech-blue);"></i>
                Liste des utilisateurs
            </div>

            <!-- Search & Filters -->
            <form method="GET" action="users_dashboard.php" id="admin-filter-form"
                  style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem;">
                <div class="admin-filter-wrap">
                    <i class="fa-solid fa-magnifying-glass admin-filter-icon"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Rechercher par nom, email..."
                           class="admin-filter-input">
                </div>
                <select name="role" class="admin-filter-select">
                    <option value="">Tous les roles</option>
                    <option value="freelancer" <?php echo $role==='freelancer'?'selected':''; ?>>Freelancer</option>
                    <option value="client"     <?php echo $role==='client'    ?'selected':''; ?>>Client</option>
                </select>
                <select name="status" class="admin-filter-select">
                    <option value="">Tous les statuts</option>
                    <option value="active"  <?php echo $status==='active' ?'selected':''; ?>>Actif</option>
                    <option value="banned"  <?php echo $status==='banned' ?'selected':''; ?>>Suspendu</option>
                    <option value="pending" <?php echo $status==='pending'?'selected':''; ?>>En attente</option>
                    <option value="rejected" <?php echo $status==='rejected'?'selected':''; ?>>Refusé</option>
                </select>
                <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                    <i class="fa-solid fa-filter"></i> Filtrer
                </button>
                <a href="users_dashboard.php" class="btn btn-outline" style="white-space:nowrap;">
                    <i class="fa-solid fa-rotate-left"></i> Reinitialiser
                </a>
            </form>

            <?php if (!empty($activeFilters)): ?>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin:-.75rem 0 1.5rem;">
                    <?php foreach ($activeFilters as $filterLabel): ?>
                        <span style="display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .75rem;border-radius:999px;background:rgba(59,130,246,.12);border:1px solid rgba(59,130,246,.25);color:#93c5fd;font-size:.78rem;font-weight:600;">
                            <i class="fa-solid fa-filter" style="font-size:.7rem;"></i>
                            <?php echo htmlspecialchars($filterLabel); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Statut</th>
                            <th>Membre depuis</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">
                                    <i class="fa-solid fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
                                    Aucun utilisateur ne correspond aux filtres selectionnes.
                                    <?php if (!empty($activeFilters)): ?>
                                        <div style="margin-top:.75rem;font-size:.8rem;color:#93c5fd;">
                                            <?php echo htmlspecialchars(implode(' | ', $activeFilters)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <?php
                                $initials  = strtoupper(mb_substr($u->getPrenom(),0,1) . mb_substr($u->getNom(),0,1));
                                $roleBadge = $u->getRole() === 'freelancer'
                                    ? '<span class="badge badge-freelancer"><i class="fa-solid fa-laptop-code"></i> Freelancer</span>'
                                    : '<span class="badge badge-client"><i class="fa-solid fa-building"></i> Client</span>';
                                if ($u->getStatus() === 'active') {
                                    $statusBadge = '<span class="badge badge-active"><i class="fa-solid fa-circle"></i> Actif</span>';
                                } elseif ($u->getStatus() === 'banned') {
                                    $statusBadge = '<span class="badge badge-banned"><i class="fa-solid fa-ban"></i> Suspendu</span>';
                                } elseif ($u->getStatus() === 'rejected') {
                                    $statusBadge = '<span class="badge badge-banned" style="background:rgba(239,68,68,.1);color:#ef4444;"><i class="fa-solid fa-xmark"></i> Refusé</span>';
                                } else {
                                    $statusBadge = '<span class="badge badge-pending"><i class="fa-solid fa-clock"></i> Attente</span>';
                                }
                                $since = date('d/m/Y', strtotime($u->getCreatedAt()));
                                ?>
                                <tr>
                                    <td style="font-family:'JetBrains Mono',monospace;font-size:.82rem;color:#475569;">#<?php echo $u->getId(); ?></td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:.75rem;">
                                            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,rgba(37,99,235,.4),rgba(139,92,246,.3));display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0;">
                                                <?php echo $initials; ?>
                                            </div>
                                            <div>
                                                <div class="admin-user-name" style="font-weight:600;font-size:.9rem;">
                                                    <?php echo htmlspecialchars($u->getPrenom() . ' ' . $u->getNom()); ?>
                                                </div>
                                                <?php if (!empty($u->getBio())): ?>
                                                    <div style="font-size:.75rem;color:var(--text-muted);max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                        <?php echo htmlspecialchars($u->getBio()); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color:var(--text-muted);font-size:.85rem;"><?php echo htmlspecialchars($u->getEmail()); ?></td>
                                    <td><?php echo $roleBadge; ?></td>
                                    <td><?php echo $statusBadge; ?></td>
                                    <td style="color:var(--text-muted);font-size:.82rem;"><?php echo $since; ?></td>
                                    <td>
                                        <div class="user-actions">
                                            <button class="action-btn action-btn-edit"
                                                    onclick="openEdit(<?php echo htmlspecialchars(json_encode($u)); ?>)"
                                                    title="Editer">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <?php if ($u->getStatus() === 'banned' || $u->getStatus() === 'rejected'): ?>
                                                <a href="?action=activate&id=<?php echo $u->getId(); ?>"
                                                   class="action-btn action-btn-ok" title="Activer"
                                                   onclick="return confirm('Activer ce compte ?');">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=ban&id=<?php echo $u->getId(); ?>"
                                                   class="action-btn action-btn-ban" title="Suspendre"
                                                   onclick="return confirm('Suspendre ce compte ?');">
                                                    <i class="fa-solid fa-ban"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?action=delete&id=<?php echo $u->getId(); ?>"
                                               class="action-btn action-btn-del" title="Supprimer"
                                               onclick="return confirm('Supprimer ce compte définitivement ?');">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Result count -->
            <div style="margin-top:1rem;font-size:.82rem;color:var(--text-muted);">
                <strong class="admin-result-count"><?php echo count($users); ?></strong>
                utilisateur<?php echo count($users) > 1 ? 's' : ''; ?> affiche<?php echo count($users) > 1 ? 's' : ''; ?>
            </div>
        </section>
    </main>
</div>

<!-- ════════════════════════════════════════════════
     MODAL — CREATE USER
════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="create-modal">
    <div class="modal" role="dialog" aria-labelledby="modal-create-title">
        <div class="modal-header">
            <div class="modal-title" id="modal-create-title">
                <i class="fa-solid fa-user-plus" style="color:var(--tech-blue);margin-right:.5rem;"></i>
                Ajouter un utilisateur
            </div>
            <button class="modal-close" onclick="closeModal('create-modal')" aria-label="Fermer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <?php if (!empty($createErrors['_global'])): ?>
            <div class="form-errors">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?php echo htmlspecialchars($createErrors['_global']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="users_dashboard.php" id="create-form" novalidate>
            <input type="hidden" name="_action" value="create">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                    <label class="form-label" for="c-nom">Nom</label>
                    <?php if ($fieldError($createErrors, 'nom') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($createErrors, 'nom')); ?></div>
                    <?php endif; ?>
                    <input class="form-input<?php echo $fieldError($createErrors, 'nom') !== '' ? ' input-error' : ''; ?>" type="text" id="c-nom" name="nom"
                           placeholder="Ben Ali" value="<?php echo htmlspecialchars($createValues['nom'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="c-prenom">Prenom</label>
                    <?php if ($fieldError($createErrors, 'prenom') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($createErrors, 'prenom')); ?></div>
                    <?php endif; ?>
                    <input class="form-input<?php echo $fieldError($createErrors, 'prenom') !== '' ? ' input-error' : ''; ?>" type="text" id="c-prenom" name="prenom"
                           placeholder="Mohamed" value="<?php echo htmlspecialchars($createValues['prenom'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="c-email">Email</label>
                <?php if ($fieldError($createErrors, 'email') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError($createErrors, 'email')); ?></div>
                <?php endif; ?>
                <input class="form-input<?php echo $fieldError($createErrors, 'email') !== '' ? ' input-error' : ''; ?>" type="text" id="c-email" name="email"
                       placeholder="user@exemple.com" value="<?php echo htmlspecialchars($createValues['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="c-password">Mot de passe</label>
                <?php if ($fieldError($createErrors, 'password') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError($createErrors, 'password')); ?></div>
                <?php endif; ?>
                <input class="form-input<?php echo $fieldError($createErrors, 'password') !== '' ? ' input-error' : ''; ?>" type="password" id="c-password" name="password"
                       placeholder="Minimum 8 caracteres"
                       oninput="updateAdminPasswordStrength(this.value)">
                <div class="strength-bar"><div class="strength-fill" id="admin-strength-fill"></div></div>
                <div class="password-rules">
                    <span class="password-rule" id="admin-rule-length">
                        <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                        8 caracteres min.
                    </span>
                    <span class="password-rule" id="admin-rule-upper">
                        <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                        1 majuscule
                    </span>
                    <span class="password-rule" id="admin-rule-special">
                        <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                        1 caractere special
                    </span>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                    <label class="form-label" for="c-role">Role</label>
                    <?php if ($fieldError($createErrors, 'role') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($createErrors, 'role')); ?></div>
                    <?php endif; ?>
                    <select class="form-input<?php echo $fieldError($createErrors, 'role') !== '' ? ' input-error' : ''; ?>" id="c-role" name="role">
                        <option value="freelancer" <?php echo (($createValues['role'] ?? '') === 'freelancer') ? 'selected' : ''; ?>>Freelancer</option>
                        <option value="client" <?php echo (($createValues['role'] ?? '') === 'client') ? 'selected' : ''; ?>>Client</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="c-bio">Bio <span style="color:#475569;font-size:.75rem;">(optionnel)</span></label>
                    <input class="form-input" type="text" id="c-bio" name="bio"
                           placeholder="Courte description..." value="<?php echo htmlspecialchars($createValues['bio'] ?? ''); ?>">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('create-modal')">Annuler</button>
                <button type="submit" class="btn btn-primary" id="create-submit-btn">
                    <i class="fa-solid fa-check"></i> Creer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════
     MODAL — EDIT USER
════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="edit-modal">
    <div class="modal" role="dialog" aria-labelledby="modal-edit-title">
        <div class="modal-header">
            <div class="modal-title" id="modal-edit-title">
                <i class="fa-solid fa-pen-to-square" style="color:var(--tech-blue);margin-right:.5rem;"></i>
                Modifier l'utilisateur
            </div>
            <button class="modal-close" onclick="closeModal('edit-modal')" aria-label="Fermer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <?php if (!empty($editErrors['_global'])): ?>
            <div class="form-errors">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?php echo htmlspecialchars($editErrors['_global']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="users_dashboard.php" id="edit-form" novalidate>
            <input type="hidden" name="_action" value="update">
            <input type="hidden" name="edit_id" id="edit-id" value="<?php echo htmlspecialchars((string) ($editValues['edit_id'] ?? '')); ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                    <label class="form-label" for="e-prenom">Prenom</label>
                    <?php if ($fieldError($editErrors, 'prenom') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($editErrors, 'prenom')); ?></div>
                    <?php endif; ?>
                    <input class="form-input<?php echo $fieldError($editErrors, 'prenom') !== '' ? ' input-error' : ''; ?>" type="text" id="e-prenom" name="prenom" value="<?php echo htmlspecialchars($editValues['prenom'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="e-nom">Nom</label>
                    <?php if ($fieldError($editErrors, 'nom') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($editErrors, 'nom')); ?></div>
                    <?php endif; ?>
                    <input class="form-input<?php echo $fieldError($editErrors, 'nom') !== '' ? ' input-error' : ''; ?>" type="text" id="e-nom" name="nom" value="<?php echo htmlspecialchars($editValues['nom'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="e-email">Email</label>
                <?php if ($fieldError($editErrors, 'email') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError($editErrors, 'email')); ?></div>
                <?php endif; ?>
                <input class="form-input<?php echo $fieldError($editErrors, 'email') !== '' ? ' input-error' : ''; ?>" type="text" id="e-email" name="email" value="<?php echo htmlspecialchars($editValues['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="e-bio">Bio</label>
                <input class="form-input" type="text" id="e-bio" name="bio" placeholder="Courte description..." value="<?php echo htmlspecialchars($editValues['bio'] ?? ''); ?>">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                    <label class="form-label" for="e-role">Role</label>
                    <?php if ($fieldError($editErrors, 'role') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($editErrors, 'role')); ?></div>
                    <?php endif; ?>
                    <select class="form-input<?php echo $fieldError($editErrors, 'role') !== '' ? ' input-error' : ''; ?>" id="e-role" name="role">
                        <option value="freelancer" <?php echo (($editValues['role'] ?? '') === 'freelancer') ? 'selected' : ''; ?>>Freelancer</option>
                        <option value="client" <?php echo (($editValues['role'] ?? '') === 'client') ? 'selected' : ''; ?>>Client</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="e-status">Statut</label>
                    <?php if ($fieldError($editErrors, 'status') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($editErrors, 'status')); ?></div>
                    <?php endif; ?>
                    <select class="form-input<?php echo $fieldError($editErrors, 'status') !== '' ? ' input-error' : ''; ?>" id="e-status" name="status">
                        <option value="active" <?php echo (($editValues['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Actif</option>
                        <option value="pending" <?php echo (($editValues['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>En attente</option>
                        <option value="banned" <?php echo (($editValues['status'] ?? '') === 'banned') ? 'selected' : ''; ?>>Suspendu</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('edit-modal')">Annuler</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Sauvegarder
                </button>
            </div>
        </form>
    </div>
</div>



<script>
// ── Modal helpers ──────────────────────────────────────────────
function openModal(id) {
    document.getElementById(id).classList.add('open');
    syncBodyScrollLock();
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    syncBodyScrollLock();
}
function syncBodyScrollLock() {
    document.body.style.overflow = document.querySelector('.modal-backdrop.open') ? 'hidden' : '';
}
// Close on backdrop click
document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});
// ESC key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop.open').forEach(m => closeModal(m.id));
    }
});

// ── Open edit modal and pre-fill ──────────────────────────────

function updateAdminPasswordStrength(val) {
    const fill = document.getElementById('admin-strength-fill');
    const checks = {
        length: val.length >= 8,
        upper: /[A-Z]/.test(val),
        special: /[^A-Za-z0-9]/.test(val)
    };
    const score = Object.values(checks).filter(Boolean).length;
    const pct = (score / 3) * 100;
    const color = score <= 1 ? '#ef4444' : score === 2 ? '#f59e0b' : '#10b981';

    document.getElementById('admin-rule-length').classList.toggle('is-valid', checks.length);
    document.getElementById('admin-rule-upper').classList.toggle('is-valid', checks.upper);
    document.getElementById('admin-rule-special').classList.toggle('is-valid', checks.special);

    fill.style.width = pct + '%';
    fill.style.background = color;
}

function clearModalValidation(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        return;
    }

    modal.querySelectorAll('.field-error').forEach(node => {
        node.style.display = 'none';
    });
    modal.querySelectorAll('.input-error').forEach(node => {
        node.classList.remove('input-error');
    });
    const errorBox = modal.querySelector('.form-errors');
    if (errorBox) {
        errorBox.style.display = 'none';
    }
}

function resetCreateForm() {
    const form = document.getElementById('create-form');

    form.reset();
    clearModalValidation('create-modal');
    updateAdminPasswordStrength('');
    document.getElementById('c-nom').focus();
}

function openCreateModal() {
    resetCreateForm();
    openModal('create-modal');
}



function openEdit(user) {
    clearModalValidation('edit-modal');
    document.getElementById('edit-id').value     = user.id;
    document.getElementById('e-prenom').value    = user.prenom;
    document.getElementById('e-nom').value       = user.nom;
    document.getElementById('e-email').value     = user.email;
    document.getElementById('e-bio').value       = user.bio || '';
    document.getElementById('e-role').value      = user.role;
    document.getElementById('e-status').value    = user.status;
    openModal('edit-modal');
}

// ── Auto-open modal on validation error ────────────────
<?php if ($activeModal === 'create'): ?>
openModal('create-modal');
<?php elseif ($activeModal === 'update'): ?>
openModal('edit-modal');
<?php endif; ?>

updateAdminPasswordStrength(document.getElementById('c-password').value);

// ── Flash auto-dismiss ────────────────────────────────────────
const flash = document.querySelector('.flash');
if (flash) setTimeout(() => flash.style.opacity = '0', 4000);

// ── Animated number counters ──────────────────────────────────
document.querySelectorAll('.stat-card__value[data-target]').forEach(el => {
    const target = parseInt(el.dataset.target, 10);
    if (isNaN(target)) return;
    const duration = 900;
    const start = performance.now();
    function step(now) {
        const p = Math.min((now - start) / duration, 1);
        const ease = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(ease * target);
        if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
});

// ── Donut chart (Chart.js) ────────────────────────────────────
(function() {
    const ctx = document.getElementById('donutChart');
    if (!ctx || typeof Chart === 'undefined') return;
    const freelancers = <?php echo (int)$totalFreelancers; ?>;
    const clients     = <?php echo (int)$totalClients; ?>;
    const banned      = <?php echo (int)$totalBanned; ?>;
    const total = freelancers + clients + banned || 1;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [freelancers, clients, banned],
                backgroundColor: ['rgba(59,130,246,.8)','rgba(139,92,246,.8)','rgba(239,68,68,.8)'],
                borderColor: ['#3b82f6','#8b5cf6','#ef4444'],
                borderWidth: 2,
                hoverOffset: 6
            }]
        },
        options: {
            cutout: '72%',
            plugins: { legend: { display: false }, tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const labels = ['Freelancers','Clients','Suspendus'];
                        const pct = Math.round(ctx.parsed / total * 100);
                        return ` ${labels[ctx.dataIndex]}: ${ctx.parsed} (${pct}%)`;
                    }
                }
            }},
            animation: { animateRotate: true, duration: 1000 }
        }
    });
})();

// ── Area chart (Chart.js) ─────────────────────────────────────
(function() {
    const ctx = document.getElementById('areaChart');
    if (!ctx || typeof Chart === 'undefined') return;
    
    // Create gradient
    let gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 180);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)'); // --tech-blue with opacity
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [{
                label: 'Inscriptions',
                data: <?php echo json_encode($chartData); ?>,
                backgroundColor: gradient,
                borderColor: '#3b82f6',
                borderWidth: 2,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#3b82f6',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4 // Smooth curve
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#94a3b8',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1
                }
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { 
                        color: '#64748b', 
                        font: { size: 10, family: "'Space Grotesk', sans-serif" },
                        maxRotation: 0
                    }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false },
                    ticks: { 
                        color: '#64748b', 
                        font: { size: 10, family: "'Space Grotesk', sans-serif" },
                        maxTicksLimit: 5 
                    },
                    beginAtZero: true
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
})();

// ── Pending approvals confirm ─────────────────────────────────
function confirmApprove(id, prenom) {
    return confirm('Approuver le compte de ' + prenom + ' ? Il pourra se connecter immédiatement.');
}
</script>

<?php
$chatbotAssetBase = '../assets';
$chatbotEndpoint = '../frontoffice/chatbot_api.php';
$chatbotStorageKey = 'freelaskill-admin-chat-history';
$chatbotKicker = 'FreelaSkill Admin AI';
$chatbotTitle = "Besoin d'aide admin ?";
$chatbotIntro = "Salut, je suis l assistant admin FreelaSkill. Je peux t aider a gerer les utilisateurs, comprendre les statuts et rediger des messages plus clairs.";
$chatbotSuggestions = [
    'Comment gerer un compte en attente ?' => 'Comptes en attente',
    'Que verifier avant de suspendre un utilisateur ?' => 'Suspendre un compte',
    'Redige un message court pour expliquer un refus d inscription.' => 'Refus inscription',
];
include __DIR__ . '/../frontoffice/chatbot_widget.php';

?>

</body>
</html>
