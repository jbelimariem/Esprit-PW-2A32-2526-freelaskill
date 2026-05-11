<?php
// views/frontoffice/profile.view.php -- Template profile.
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil — FreelaSkill</title>
    <meta name="description" content="Gérez votre profil FreelaSkill — informations personnelles, sécurité et préférences.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=7">
    <style>
        .profile-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            padding: 3rem 4rem;
            max-width: 1200px;
            margin: 0 auto;
            align-items: start;
        }
        .profile-sidebar {
            position: sticky;
            top: 95px;
        }
        .profile-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            animation: fadeUp 0.4s ease forwards;
        }
        .profile-card-header {
            background: linear-gradient(135deg, rgba(37,99,235,0.2), rgba(139,92,246,0.15));
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid var(--border);
            position: relative;
        }
        .profile-card-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 60% 30%, rgba(59,130,246,0.15), transparent 60%);
            pointer-events: none;
        }
        .avatar-circle {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(37,99,235,0.5), rgba(139,92,246,0.4));
            border: 3px solid rgba(59,130,246,0.4);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; font-weight: 700; color: white;
            margin: 0 auto 1rem;
            box-shadow: 0 0 24px rgba(59,130,246,0.3);
            position: relative;
        }
        .avatar-circle.has-image {
            padding: 0;
            background: rgba(15,23,42,0.95);
        }
        .avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 50%;
        }
        .avatar-edit {
            position: absolute;
            bottom: -2px; right: -2px;
            width: 24px; height: 24px;
            background: var(--tech-blue);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.6rem;
            cursor: pointer;
            border: 2px solid var(--bg-dark);
            z-index: 2;
        }
        .avatar-edit-btn {
            width: 100%;
            height: 100%;
            border: none;
            background: transparent;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
        }
        .avatar-input { display: none; }
        .profile-name { font-size: 1.1rem; font-weight: 700; color: white; margin-bottom: 0.4rem; }
        .profile-email { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.85rem; }
        .profile-badges { display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap; }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0;
            border-top: 1px solid var(--border);
        }
        .stat-item {
            padding: 1rem;
            text-align: center;
            border-right: 1px solid var(--border);
            min-width: 0;
        }
        .stat-item:nth-child(even) { border-right: none; }
        .stat-item:last-child {
            grid-column: 1 / -1;
            border-top: 1px solid var(--border);
            border-right: none;
        }
        .stat-value { font-size: 1.3rem; font-weight: 700; color: white; font-family: 'JetBrains Mono', monospace; }
        .stat-value-date { font-size: 1rem; letter-spacing: -0.04em; }
        .stat-label { font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0.2rem; }

        /* TABS */
        .tab-nav {
            display: flex;
            gap: 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.75rem;
        }
        .tab-btn {
            padding: 0.85rem 1.25rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            border: none;
            border-bottom: 2px solid transparent;
            background: none;
            font-family: 'Space Grotesk', sans-serif;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .tab-btn:hover { color: white; }
        .tab-btn.active { color: white; border-bottom-color: var(--tech-blue); }

        .tab-panel { display: none; }
        .tab-panel.active { display: block; animation: fadeIn 0.3s ease; }

        /* SECTION CARDS */
        .section-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .section-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .section-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .section-card-title i { color: var(--tech-blue); }
        .section-card-body { padding: 1.5rem; }
        .password-rules {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.55rem;
            margin-top: 0.85rem;
        }
        .password-rule {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.42rem 0.9rem;
            border-radius: 999px;
            border: 1px solid rgba(71,85,105,0.5);
            background: rgba(15,23,42,0.7);
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.01em;
            line-height: 1;
            transition: all 0.35s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
        }
        .password-rule::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(5,150,105,0.08));
            opacity: 0;
            transition: opacity 0.35s ease;
        }
        .password-rule .rule-icon {
            font-size: 0.72rem;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1.5px solid rgba(71,85,105,0.6);
            transition: all 0.35s cubic-bezier(.4,0,.2,1);
            flex-shrink: 0;
        }
        .password-rule .rule-icon i { font-size: 0.6rem; color: #475569; transition: all 0.3s; }
        .password-rule.is-valid {
            border-color: rgba(16,185,129,0.7);
            background: rgba(16,185,129,0.12);
            color: #6ee7b7;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1), 0 0 12px rgba(16,185,129,0.12);
        }
        .password-rule.is-valid::before { opacity: 1; }
        .password-rule.is-valid .rule-icon {
            background: rgba(16,185,129,0.25);
            border-color: rgba(16,185,129,0.7);
            animation: rulePop 0.35s cubic-bezier(.4,0,.2,1);
        }
        .password-rule.is-valid .rule-icon i { color: #10b981; }
        @keyframes rulePop {
            0%   { transform: scale(0.7); }
            60%  { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        /* ACTIVITY FEED */
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }
        .activity-item:last-child { border-bottom: none; padding-bottom: 0; }
        .activity-icon {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .activity-text { font-size: 0.88rem; color: var(--text-muted); }
        .activity-text strong { color: white; }
        .activity-time { font-size: 0.75rem; color: #475569; margin-top: 0.2rem; }

        /* SIDEBAR NAV */
        .side-nav { padding: 0.75rem; }
        .side-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            color: var(--text-muted);
            font-size: 0.88rem;
            transition: var(--transition);
        }
        .side-nav a:hover { color: white; background: rgba(255,255,255,0.03); }
        .side-nav a.danger { color: var(--tunisian-red); }
        .side-nav a.danger:hover { background: rgba(239,68,68,0.1); }
        .nav-avatar.has-image {
            padding: 0;
            overflow: hidden;
            background: rgba(15,23,42,0.95);
        }
        .nav-avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 50%;
        }
        .field-error {
            margin-top: 0.45rem;
            color: #fca5a5;
            font-size: 0.8rem;
        }
        .form-input.input-error,
        .input-error {
            border-color: rgba(239, 68, 68, 0.65) !important;
        }

        @media (max-width: 900px) {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0;
            border-top: 1px solid var(--border);
        }
        .stat-item {
            padding: 1rem;
            text-align: center;
            border-right: 1px solid var(--border);
            min-width: 0;
        }
        .stat-item:nth-child(even) { border-right: none; }
        .stat-item:last-child {
            grid-column: 1 / -1;
            border-top: 1px solid var(--border);
            border-right: none;
        }
        .stat-value { font-size: 1.3rem; font-weight: 700; color: white; font-family: 'JetBrains Mono', monospace; }
        .stat-value-date { font-size: 1rem; letter-spacing: -0.04em; }
        .stat-label { font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0.2rem; }

        /* TABS */
        .tab-nav {
            display: flex;
            gap: 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.75rem;
        }
        .tab-btn {
            padding: 0.85rem 1.25rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            border: none;
            border-bottom: 2px solid transparent;
            background: none;
            font-family: 'Space Grotesk', sans-serif;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .tab-btn:hover { color: white; }
        .tab-btn.active { color: white; border-bottom-color: var(--tech-blue); }

        .tab-panel { display: none; }
        .tab-panel.active { display: block; animation: fadeIn 0.3s ease; }

        /* SECTION CARDS */
        .section-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .section-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .section-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .section-card-title i { color: var(--tech-blue); }
        .section-card-body { padding: 1.5rem; }
        .password-rules {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.55rem;
            margin-top: 0.85rem;
        }
        .password-rule {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.42rem 0.9rem;
            border-radius: 999px;
            border: 1px solid rgba(71,85,105,0.5);
            background: rgba(15,23,42,0.7);
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.01em;
            line-height: 1;
            transition: all 0.35s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
        }
        .password-rule::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(5,150,105,0.08));
            opacity: 0;
            transition: opacity 0.35s ease;
        }
        .password-rule .rule-icon {
            font-size: 0.72rem;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1.5px solid rgba(71,85,105,0.6);
            transition: all 0.35s cubic-bezier(.4,0,.2,1);
            flex-shrink: 0;
        }
        .password-rule .rule-icon i { font-size: 0.6rem; color: #475569; transition: all 0.3s; }
        .password-rule.is-valid {
            border-color: rgba(16,185,129,0.7);
            background: rgba(16,185,129,0.12);
            color: #6ee7b7;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1), 0 0 12px rgba(16,185,129,0.12);
        }
        .password-rule.is-valid::before { opacity: 1; }
        .password-rule.is-valid .rule-icon {
            background: rgba(16,185,129,0.25);
            border-color: rgba(16,185,129,0.7);
            animation: rulePop 0.35s cubic-bezier(.4,0,.2,1);
        }
        .password-rule.is-valid .rule-icon i { color: #10b981; }
        @keyframes rulePop {
            0%   { transform: scale(0.7); }
            60%  { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        /* ACTIVITY FEED */
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }
        .activity-item:last-child { border-bottom: none; padding-bottom: 0; }
        .activity-icon {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .activity-text { font-size: 0.88rem; color: var(--text-muted); }
        .activity-text strong { color: white; }
        .activity-time { font-size: 0.75rem; color: #475569; margin-top: 0.2rem; }

        /* SIDEBAR NAV */
        .side-nav { padding: 0.75rem; }
        .side-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            color: var(--text-muted);
            font-size: 0.88rem;
            transition: var(--transition);
        }
        .side-nav a:hover { color: white; background: rgba(255,255,255,0.03); }
        .side-nav a.danger { color: var(--tunisian-red); }
        .side-nav a.danger:hover { background: rgba(239,68,68,0.1); }
        .nav-avatar.has-image {
            padding: 0;
            overflow: hidden;
            background: rgba(15,23,42,0.95);
        }
        .nav-avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 50%;
        }
        .field-error {
            margin-top: 0.45rem;
            color: #fca5a5;
            font-size: 0.8rem;
        }
        .form-input.input-error,
        .input-error {
            border-color: rgba(239, 68, 68, 0.65) !important;
        }

        /* ── AI Password Suggester ── */
        .pw-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.35rem;
        }
        .btn-ai-pwd {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.76rem;
            font-weight: 600;
            color: #a78bfa;
            background: rgba(139,92,246,0.12);
            border: 1px solid rgba(139,92,246,0.3);
            border-radius: 999px;
            padding: 0.28rem 0.75rem;
            cursor: pointer;
            transition: all 0.25s;
            white-space: nowrap;
        }
        .btn-ai-pwd:hover {
            background: rgba(139,92,246,0.25);
            border-color: rgba(139,92,246,0.6);
            color: #c4b5fd;
            transform: scale(1.03);
        }
        .btn-ai-pwd i { font-size: 0.7rem; }
        #ai-pwd-modal {
            position: fixed; inset: 0; z-index: 9000;
            display: flex; align-items: center; justify-content: center;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(6px);
            opacity: 0; pointer-events: none;
            transition: opacity 0.25s;
        }
        #ai-pwd-modal.open { opacity: 1; pointer-events: auto; }
        .ai-pwd-box {
            background: #0f172a;
            border: 1px solid rgba(139,92,246,0.35);
            border-radius: 20px;
            padding: 2rem 1.75rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 60px rgba(139,92,246,0.2);
            animation: fadeUp 0.3s ease;
        }
        .ai-pwd-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; }
        .ai-pwd-title { font-size:1rem; font-weight:700; color:#e2e8f0; display:flex; align-items:center; gap:.5rem; }
        .ai-pwd-close { background:none; border:none; color:#64748b; cursor:pointer; font-size:1rem; transition:color .2s; }
        .ai-pwd-close:hover { color:#e2e8f0; }
        .ai-pwd-subtitle { font-size:.8rem; color:#64748b; margin-bottom:1rem; }
        #ai-pwd-list { display:flex; flex-direction:column; gap:.65rem; }
        .ai-pwd-option {
            display:flex; align-items:center; justify-content:space-between;
            background:rgba(139,92,246,.07); border:1px solid rgba(139,92,246,.2);
            border-radius:12px; padding:.75rem 1rem; cursor:pointer;
            transition:all .2s; font-family:'JetBrains Mono',monospace;
            font-size:.85rem; color:#c4b5fd; word-break:break-all;
        }
        .ai-pwd-option:hover { background:rgba(139,92,246,.2); border-color:rgba(139,92,246,.5); color:#a78bfa; }
        .ai-pwd-option i { font-size:.75rem; color:#7c3aed; margin-left:.5rem; flex-shrink:0; }
        .ai-pwd-spinner { text-align:center; padding:1.5rem 0; color:#a78bfa; font-size:.88rem; }
        .ai-pwd-spinner i { font-size:1.5rem; display:block; margin-bottom:.5rem; animation:fa-spin 1s linear infinite; }
        .ai-pwd-error { background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.3); border-radius:10px; padding:.7rem .9rem; color:#fca5a5; font-size:.82rem; text-align:center; }
        .ai-pwd-regen { margin-top:1rem; width:100%; display:flex; align-items:center; justify-content:center; gap:.4rem; background:transparent; border:1px dashed rgba(139,92,246,.35); border-radius:10px; color:#7c3aed; font-size:.8rem; font-weight:600; padding:.55rem; cursor:pointer; transition:all .2s; }
        .ai-pwd-regen:hover { background:rgba(139,92,246,.1); border-color:rgba(139,92,246,.55); color:#a78bfa; }

        @media (max-width: 900px) {
            .profile-layout { grid-template-columns: 1fr; padding: 1.5rem; }
            .profile-sidebar { position: static; }
        }
        @media (max-width: 480px) {
            .stat-value { font-size: 1.1rem; }
            .stat-value-date { font-size: 0.92rem; }
        }

        /* ── AI Bio Suggester ── */
        .pw-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .btn-ai-pwd {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: #a78bfa;
            background: rgba(139,92,246,0.12);
            border: 1px solid rgba(139,92,246,0.3);
            border-radius: 999px;
            padding: 0.35rem 0.9rem;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            font-family: 'Space Grotesk', sans-serif;
        }
        .btn-ai-pwd:hover {
            background: rgba(139,92,246,0.22);
            border-color: rgba(139,92,246,0.6);
            color: #c4b5fd;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
        }
        .btn-ai-pwd i { font-size: 0.75rem; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </div>
    <ul class="nav-links">
        <li><span style="color:var(--text-muted);cursor:default;">Accueil</span></li>
        <li><a href="home.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'home.php') ? 'active' : '' ?>">Marketplace</a></li>
        <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'client'): ?>
            <li><a href="missions.php">Missions</a></li>
        <?php else: ?>
            <li><a href="freelancer_home.php">Freelancers</a></li>
        <?php endif; ?>
        <li><a href="profile.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : '' ?>">Mon Profil</a></li>
    </ul>
    <div class="nav-right">
        <button type="button" class="theme-toggle" data-theme-toggle>
            <i class="fa-solid fa-sun" data-theme-icon></i>
            <span data-theme-label>Jour</span>
        </button>
        <div class="nav-avatar<?php echo $hasAvatar ? ' has-image' : ''; ?>" title="<?php echo htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()); ?>">
            <?php if ($hasAvatar): ?>
                <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Photo de profil" class="nav-avatar-image">
            <?php else: ?>
                <?php echo $initials; ?>
            <?php endif; ?>
        </div>
        <a href="logout.php" class="btn btn-outline" style="font-size:0.82rem; padding:0.45rem 1rem;">
            <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
        </a>
    </div>
</nav>

<!-- PAGE BODY -->
<div class="profile-layout">

    <!-- LEFT SIDEBAR -->
    <aside class="profile-sidebar">

        <!-- Profile card -->
        <div class="profile-card" style="margin-bottom:1.25rem;">
            <div class="profile-card-header">
                <form id="avatarUploadForm" method="POST" action="profile.php" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="action" value="avatar">
                    <input type="file" name="avatar_file" id="avatarInput" class="avatar-input" accept="image/jpeg,image/png,image/webp,image/gif">
                </form>
                <div class="avatar-circle<?php echo $hasAvatar ? ' has-image' : ''; ?>">
                    <?php if ($hasAvatar): ?>
                        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Photo de profil" class="avatar-image">
                    <?php else: ?>
                        <?php echo $initials; ?>
                    <?php endif; ?>
                    <div class="avatar-edit">
                        <button type="button" class="avatar-edit-btn" onclick="openAvatarPicker()" aria-label="Changer la photo de profil">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($user->getEmail()); ?></div>
                <div class="profile-badges">
                    <?php echo $roleBadge; ?>
                    <?php echo $statusBadge; ?>
                </div>
                <?php if ($fieldError($avatarErrors, 'avatar_file') !== ''): ?>
                    <div class="field-error" style="margin-top:1rem;"><?php echo htmlspecialchars($fieldError($avatarErrors, 'avatar_file')); ?></div>
                <?php endif; ?>
            </div>
            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Missions</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Contrats</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value stat-value-date"><?php echo $memberSince; ?></div>
                    <div class="stat-label">Membre depuis</div>
                </div>
            </div>
        </div>

        <!-- Sidebar nav -->
        <div class="profile-card">
            <div class="side-nav">
                <a href="#" onclick="showTab('info');return false;"><i class="fa-solid fa-user" style="color:var(--tech-blue);width:16px;"></i> Informations</a>
                <a href="#" onclick="showTab('security');return false;"><i class="fa-solid fa-shield-halved" style="color:var(--tech-blue);width:16px;"></i> Sécurité</a>
                <?php if ($user->getRole() === 'freelancer'): ?>
                <a href="#" onclick="showTab('networks');return false;"><i class="fa-solid fa-link" style="color:var(--tech-blue);width:16px;"></i> Réseaux</a>
                <?php endif; ?>
                <a href="#" onclick="showTab('activity');return false;"><i class="fa-solid fa-clock-rotate-left" style="color:var(--tech-blue);width:16px;"></i> Activité</a>
                <div style="height:1px;background:var(--border);margin:0.5rem 0;"></div>
                <a href="logout.php" class="danger"><i class="fa-solid fa-right-from-bracket" style="width:16px;"></i> Se déconnecter</a>
            </div>
        </div>

    </aside>

    <!-- RIGHT CONTENT -->
    <main>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                Modifications enregistrées avec succès !
            </div>
        <?php endif; ?>
        <?php if ($aiBioNotice !== ''): ?>
            <div class="alert alert-info">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <?php echo htmlspecialchars($aiBioNotice); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors['_global'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($errors['_global']); ?>
            </div>
        <?php endif; ?>

        <!-- TABS NAV -->
        <div class="tab-nav">
            <button class="tab-btn <?php echo $activeProfileTab === 'info' ? 'active' : ''; ?>" id="tab-info"     onclick="showTab('info')">
                <i class="fa-solid fa-user"></i> Informations personnelles
            </button>
            <button class="tab-btn <?php echo $activeProfileTab === 'security' ? 'active' : ''; ?>" id="tab-security" onclick="showTab('security')">
                <i class="fa-solid fa-shield-halved"></i> Sécurité
            </button>
            <?php if ($user->getRole() === 'freelancer'): ?>
            <button class="tab-btn <?php echo $activeProfileTab === 'networks' ? 'active' : ''; ?>" id="tab-networks" onclick="showTab('networks')">
                <i class="fa-brands fa-github"></i> Réseaux
            </button>
            <?php endif; ?>
            <button class="tab-btn <?php echo $activeProfileTab === 'activity' ? 'active' : ''; ?>" id="tab-activity" onclick="showTab('activity')">
                <i class="fa-solid fa-clock-rotate-left"></i> Activité
            </button>
        </div>

        <!-- TAB: INFO -->
        <div class="tab-panel <?php echo $activeProfileTab === 'info' ? 'active' : ''; ?>" id="panel-info">
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <i class="fa-solid fa-address-card"></i>
                        Informations du profil
                    </div>
                    <span class="badge badge-active"><i class="fa-solid fa-pen-to-square"></i> Éditable</span>
                </div>
                <div class="section-card-body">
                    <form method="POST" action="profile.php" style="display:flex;flex-direction:column;gap:1.25rem;" novalidate>
                        <input type="hidden" name="action" id="profile-form-action" value="update">

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="form-group">
                                <label class="form-label" for="nom">Nom</label>
                                <input class="form-input<?php echo $fieldError($updateErrors, 'nom') !== '' ? ' input-error' : ''; ?>" type="text" id="nom" name="nom"
                                       value="<?php echo htmlspecialchars($profileFormValues['nom'] ?? ''); ?>">
                                <?php if ($fieldError($updateErrors, 'nom') !== ''): ?>
                                    <div class="field-error"><?php echo htmlspecialchars($fieldError($updateErrors, 'nom')); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="prenom">Prénom</label>
                                <input class="form-input<?php echo $fieldError($updateErrors, 'prenom') !== '' ? ' input-error' : ''; ?>" type="text" id="prenom" name="prenom"
                                       value="<?php echo htmlspecialchars($profileFormValues['prenom'] ?? ''); ?>">
                                <?php if ($fieldError($updateErrors, 'prenom') !== ''): ?>
                                    <div class="field-error"><?php echo htmlspecialchars($fieldError($updateErrors, 'prenom')); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">Adresse email</label>
                            <input class="form-input<?php echo $fieldError($updateErrors, 'email') !== '' ? ' input-error' : ''; ?>" type="text" id="email" name="email"
                                   value="<?php echo htmlspecialchars($profileFormValues['email'] ?? ''); ?>">
                            <?php if ($fieldError($updateErrors, 'email') !== ''): ?>
                                <div class="field-error"><?php echo htmlspecialchars($fieldError($updateErrors, 'email')); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="role_display">Rôle</label>
                            <input class="form-input" type="text" id="role_display"
                                   value="<?php echo ucfirst($user->getRole()); ?>" disabled
                                   style="opacity:0.5;cursor:not-allowed;">
                        </div>

                        <div class="form-group">
                            <div class="pw-label-row">
                                <label class="form-label" for="bio" style="margin:0;">Bio <span style="color:#475569; font-size:0.78rem;">(optionnel)</span></label>
                                <button type="submit" class="btn-ai-pwd" id="btn-suggest-bio" onclick="document.getElementById('profile-form-action').value='generate_bio';">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> IA : suggérer une bio
                                </button>
                            </div>
                            <textarea class="form-input" id="bio" name="bio" rows="4"
                                      placeholder="Quelques mots sur vous ou vos activités…"><?php echo htmlspecialchars($profileFormValues['bio'] ?? ''); ?></textarea>
                            <div style="margin-top:0.45rem;">
                                <span style="font-size:0.78rem;color:var(--text-muted);">
                                    Utilise votre nom, votre rôle et votre texte actuel pour proposer une version plus pro.
                                </span>
                            </div>
                        </div>

                        <div style="display:flex;gap:1rem;align-items:center;">
                            <button type="submit" class="btn btn-primary" style="width:auto;padding:0.75rem 2rem;" onclick="document.getElementById('profile-form-action').value='update';">
                                <i class="fa-solid fa-floppy-disk"></i> Sauvegarder
                            </button>
                            <a href="profile.php" class="btn btn-outline">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB: NETWORKS (freelancer only) -->
        <?php if ($user->getRole() === 'freelancer'): ?>
        <div class="tab-panel <?php echo $activeProfileTab === 'networks' ? 'active' : ''; ?>" id="panel-networks">

            <!-- ===== SOCIAL LINKS CARD ===== -->
            <div class="section-card" style="margin-bottom:1.5rem;">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <i class="fa-solid fa-share-nodes"></i>
                        R&eacute;seaux professionnels
                    </div>
                    <span class="badge badge-freelancer"><i class="fa-solid fa-laptop-code"></i> Freelancer</span>
                </div>
                <div class="section-card-body">

                    <form method="POST" action="profile.php" style="display:flex;flex-direction:column;gap:1.1rem;" novalidate>
                        <input type="hidden" name="action" value="links">

                        <?php
                        $socialLinks = [
                            'github_url'   => ['label'=>'GitHub',   'icon'=>'fa-brands fa-github',      'color'=>'rgba(255,255,255,.08)', 'text'=>'white',    'placeholder'=>'https://github.com/votre-profil'],
                            'linkedin_url' => ['label'=>'LinkedIn',  'icon'=>'fa-brands fa-linkedin-in',  'color'=>'rgba(10,102,194,.2)',   'text'=>'#60a5fa',  'placeholder'=>'https://linkedin.com/in/votre-profil'],
                        ];
                        foreach ($socialLinks as $fieldKey => $s):
                            $currentLink = $profileSocialLinks[$fieldKey] ?? '';
                        ?>
                        <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:14px;padding:1.2rem;display:flex;flex-direction:column;gap:.85rem;" class="<?php echo $fieldError($linksErrors, $fieldKey) !== '' ? 'input-error' : ''; ?>">
                            <div style="display:flex;align-items:center;gap:.85rem;">
                                <div style="width:40px;height:40px;border-radius:11px;background:<?php echo $s['color']; ?>;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:<?php echo $s['text']; ?>;flex-shrink:0;">
                                    <i class="<?php echo $s['icon']; ?>"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:700;color:white;font-size:.92rem;"><?php echo $s['label']; ?></div>
                                    <?php if (!empty($currentLink)): ?>
                                        <div style="font-size:.75rem;color:#6ee7b7;display:flex;align-items:center;gap:.3rem;margin-top:.15rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            <i class="fa-solid fa-circle-check" style="font-size:.65rem;"></i>
                                            <?php echo htmlspecialchars($currentLink); ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="font-size:.75rem;color:#475569;margin-top:.15rem;">Non renseign&eacute;</div>
                                    <?php endif; ?>
                                </div>
                                <!-- Action buttons when link is set -->
                                <?php if (!empty($currentLink)): ?>
                                    <a href="<?php echo htmlspecialchars($currentLink); ?>" target="_blank"
                                       style="font-size:.78rem;color:var(--tech-blue);text-decoration:none;display:flex;align-items:center;gap:.3rem;padding:.35rem .7rem;border-radius:8px;border:1px solid rgba(37,99,235,.3);white-space:nowrap;transition:background .2s;"
                                       onmouseover="this.style.background='rgba(37,99,235,.12)'" onmouseout="this.style.background=''">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Voir
                                    </a>
                                    <form method="POST" action="profile.php" style="display:inline;" onsubmit="return confirm('Supprimer ce lien définitivement ?');">
                                        <input type="hidden" name="action" value="delete_link">
                                        <input type="hidden" name="field" value="<?php echo $fieldKey; ?>">
                                        <button type="submit" title="Supprimer"
                                                style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171;padding:.35rem .65rem;border-radius:8px;cursor:pointer;font-size:.8rem;transition:all .2s;"
                                                onmouseover="this.style.background='rgba(239,68,68,.2)'" onmouseout="this.style.background='rgba(239,68,68,.1)'">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <!-- URL input (visible to edit/add) -->
                            <div style="position:relative;">
                                <input class="form-input<?php echo $fieldError($linksErrors, $fieldKey) !== '' ? ' input-error' : ''; ?>" type="text" name="<?php echo $fieldKey; ?>" id="<?php echo $fieldKey; ?>"
                                       placeholder="<?php echo $s['placeholder']; ?>"
                                       value="<?php echo htmlspecialchars($profileFormValues[$fieldKey] ?? ''); ?>"
                                       style="padding-left:2.5rem;font-size:.88rem;">
                                <i class="<?php echo $s['icon']; ?>" style="position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:#334155;font-size:.85rem;"></i>
                            </div>
                            <?php if ($fieldError($linksErrors, $fieldKey) !== ''): ?>
                                <div class="field-error"><?php echo htmlspecialchars($fieldError($linksErrors, $fieldKey)); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>

                        <div>
                            <button type="submit" class="btn btn-primary" style="width:auto;padding:.7rem 1.8rem;">
                                <i class="fa-solid fa-floppy-disk"></i> Sauvegarder les liens
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- ===== DOCUMENTS CARD ===== -->
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <i class="fa-solid fa-folder-open"></i>
                        Documents professionnels
                    </div>
                    <span style="font-size:.75rem;color:var(--text-muted);">PDF &middot; ZIP &middot; DOCX &middot; max 5 MB</span>
                </div>
                <div class="section-card-body" style="display:flex;flex-direction:column;gap:1.25rem;">

                    <?php
                    $docFields = [
                        'cv_url'        => ['label'=>'CV',        'sub'=>'PDF uniquement',     'icon'=>'fa-solid fa-file-pdf',  'col'=>'rgba(234,179,8,.15)', 'txt'=>'#fbbf24', 'border'=>'rgba(234,179,8,.35)', 'bg'=>'rgba(234,179,8,.04)', 'accept'=>'.pdf',         'inputId'=>'cv_file',        'spanId'=>'cv-fn',  'zoneId'=>'cv-zone'],
                        'portfolio_url' => ['label'=>'Portfolio',  'sub'=>'PDF, ZIP, DOCX',     'icon'=>'fa-solid fa-briefcase', 'col'=>'rgba(139,92,246,.18)','txt'=>'#a78bfa','border'=>'rgba(139,92,246,.4)', 'bg'=>'rgba(139,92,246,.05)','accept'=>'.pdf,.zip,.docx','inputId'=>'portfolio_file', 'spanId'=>'pf-fn',  'zoneId'=>'pf-zone'],
                    ];
                    foreach ($docFields as $fkey => $d):
                        $filePath = $profileDocuments[$fkey] ?? '';
                        $hasFile = !empty($filePath);
                    ?>

                    <!-- <?php echo $d['label']; ?> doc card -->
                    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:14px;padding:1.3rem;" class="<?php echo $fieldError($filesErrors, $d['inputId']) !== '' ? 'input-error' : ''; ?>">

                        <!-- Header row -->
                        <div style="display:flex;align-items:center;gap:.9rem;margin-bottom:<?php echo $hasFile ? '1rem' : '.85rem'; ?>;">
                            <div style="width:42px;height:42px;border-radius:12px;background:<?php echo $d['col']; ?>;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:<?php echo $d['txt']; ?>;flex-shrink:0;">
                                <i class="<?php echo $d['icon']; ?>"></i>
                            </div>
                            <div style="flex:1;">
                                <div style="font-weight:700;color:white;font-size:.93rem;"><?php echo $d['label']; ?> <span style="font-size:.76rem;color:var(--text-muted);font-weight:400;">(<?php echo $d['sub']; ?>)</span></div>
                                <div style="font-size:.76rem;color:var(--text-muted);">Max 5 MB</div>
                            </div>
                        </div>

                        <?php if ($hasFile): ?>
                            <!-- ---- File EXISTS: show info + actions ---- -->
                            <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem 1rem;border-radius:10px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.28);margin-bottom:.85rem;">
                                <i class="fa-solid fa-circle-check" style="color:#10b981;font-size:.85rem;"></i>
                                <span style="font-size:.82rem;color:#6ee7b7;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars(basename($filePath)); ?></span>
                            </div>

                            <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;">
                                <!-- Download -->
                                <a href="<?php echo htmlspecialchars($filePath); ?>" download target="_blank"
                                   style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1.1rem;border-radius:10px;font-size:.82rem;font-weight:600;
                                          background:rgba(37,99,235,.12);border:1px solid rgba(37,99,235,.3);color:var(--tech-blue);text-decoration:none;transition:background .2s;"
                                   onmouseover="this.style.background='rgba(37,99,235,.22)'" onmouseout="this.style.background='rgba(37,99,235,.12)'">
                                    <i class="fa-solid fa-file-arrow-down"></i> T&eacute;l&eacute;charger
                                </a>
                                <!-- Replace -->
                                <button type="button"
                                        onclick="var z=document.getElementById('<?php echo $d['zoneId']; ?>');z.style.display=z.style.display==='none'?'flex':'none';"
                                        style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1.1rem;border-radius:10px;font-size:.82rem;font-weight:600;
                                               background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);color:#94a3b8;cursor:pointer;transition:all .2s;"
                                        onmouseover="this.style.background='rgba(255,255,255,.1)'" onmouseout="this.style.background='rgba(255,255,255,.05)'">
                                    <i class="fa-solid fa-arrows-rotate"></i> Remplacer
                                </button>
                                <!-- Delete -->
                                <form method="POST" action="profile.php" style="display:inline;"
                                      onsubmit="return confirm('Supprimer ce fichier définitivement ?');">
                                    <input type="hidden" name="action" value="delete_file">
                                    <input type="hidden" name="field" value="<?php echo $fkey; ?>">
                                    <button type="submit"
                                            style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1.1rem;border-radius:10px;font-size:.82rem;font-weight:600;
                                                   background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171;cursor:pointer;transition:all .2s;"
                                            onmouseover="this.style.background='rgba(239,68,68,.2)'" onmouseout="this.style.background='rgba(239,68,68,.1)'">
                                        <i class="fa-solid fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>

                            <!-- Replace zone (hidden by default) -->
                            <form method="POST" action="profile.php" enctype="multipart/form-data" novalidate
                                  id="form-<?php echo $d['inputId']; ?>" style="margin-top:1rem;">
                                <input type="hidden" name="action" value="files">
                                <input type="hidden" name="file_target" value="<?php echo $d['inputId']; ?>">
                                <div id="<?php echo $d['zoneId']; ?>"
                                     style="display:none;flex-direction:column;align-items:center;gap:.5rem;padding:1.25rem;
                                            border:2px dashed <?php echo $d['border']; ?>;border-radius:11px;background:<?php echo $d['bg']; ?>;
                                            cursor:pointer;text-align:center;transition:border-color .2s,background .2s;"
                                     onclick="document.getElementById('<?php echo $d['inputId']; ?>_r').click();"
                                     onmouseover="this.style.borderColor='<?php echo str_replace('.35',',.65',$d['border']); ?>'"
                                     onmouseout="this.style.borderColor='<?php echo $d['border']; ?>'">
                                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.4rem;color:<?php echo $d['txt']; ?>;opacity:.8;pointer-events:none;"></i>
                                    <span style="font-size:.82rem;color:var(--text-muted);pointer-events:none;">Choisir un nouveau fichier <span style="color:<?php echo $d['txt']; ?>;font-weight:600;">(<?php echo $d['sub']; ?>)</span></span>
                                    <span style="font-size:.72rem;color:#334155;pointer-events:none;" id="<?php echo $d['spanId']; ?>_r">Aucun fichier s&eacute;lectionn&eacute;</span>
                                </div>
                                <input type="file" id="<?php echo $d['inputId']; ?>_r" name="<?php echo $d['inputId']; ?>"
                                       accept="<?php echo $d['accept']; ?>"
                                       style="position:absolute;width:0;height:0;opacity:0;overflow:hidden;"
                                       onchange="
                                           document.getElementById('<?php echo $d['spanId']; ?>_r').textContent = this.files[0] ? this.files[0].name : 'Aucun fichier sélectionné';
                                           if(this.files[0]){ this.form.submit(); }
                                       ">
                            </form>

                        <?php else: ?>
                            <!-- ---- No file: show upload zone ---- -->
                            <form method="POST" action="profile.php" enctype="multipart/form-data" novalidate
                                  id="form-<?php echo $d['inputId']; ?>">
                                <input type="hidden" name="action" value="files">
                                <input type="hidden" name="file_target" value="<?php echo $d['inputId']; ?>">
                                <div onclick="document.getElementById('<?php echo $d['inputId']; ?>').click();"
                                     style="display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1.5rem;
                                            border:2px dashed <?php echo $d['border']; ?>;border-radius:11px;background:<?php echo $d['bg']; ?>;
                                            cursor:pointer;text-align:center;transition:border-color .2s,background .2s;"
                                     onmouseover="this.style.borderColor='<?php echo str_replace('.35',',.65',$d['border']); ?>'"
                                     onmouseout="this.style.borderColor='<?php echo $d['border']; ?>'">
                                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.6rem;color:<?php echo $d['txt']; ?>;opacity:.8;pointer-events:none;"></i>
                                    <span style="font-size:.85rem;color:var(--text-muted);pointer-events:none;">Cliquez pour choisir <span style="color:<?php echo $d['txt']; ?>;font-weight:600;">(<?php echo $d['sub']; ?>)</span></span>
                                    <span style="font-size:.73rem;color:#334155;pointer-events:none;" id="<?php echo $d['spanId']; ?>">Aucun fichier s&eacute;lectionn&eacute;</span>
                                </div>
                                <input type="file" id="<?php echo $d['inputId']; ?>" name="<?php echo $d['inputId']; ?>"
                                       accept="<?php echo $d['accept']; ?>"
                                       style="position:absolute;width:0;height:0;opacity:0;overflow:hidden;"
                                       onchange="
                                           document.getElementById('<?php echo $d['spanId']; ?>').textContent = this.files[0] ? this.files[0].name : 'Aucun fichier sélectionné';
                                           if(this.files[0]){ this.form.submit(); }
                                       ">
                            </form>
                        <?php endif; ?>

                        <?php if ($fieldError($filesErrors, $d['inputId']) !== ''): ?>
                            <div class="field-error"><?php echo htmlspecialchars($fieldError($filesErrors, $d['inputId'])); ?></div>
                        <?php endif; ?>

                    </div>
                    <?php endforeach; ?>

                </div>
            </div>

        </div>
        <?php endif; ?>




        <!-- TAB: SECURITY -->

        <div class="tab-panel <?php echo $activeProfileTab === 'security' ? 'active' : ''; ?>" id="panel-security">
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <i class="fa-solid fa-lock"></i>
                        Changer le mot de passe
                    </div>
                </div>
                <div class="section-card-body">
                    <form method="POST" action="profile.php" style="display:flex;flex-direction:column;gap:1.1rem;" novalidate>
                        <input type="hidden" name="action" value="password">

                        <div class="form-group">
                            <div class="pw-label-row" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <label class="form-label" for="new_password" style="margin:0;">Nouveau mot de passe</label>
                                <button type="button" class="btn-ai-pwd" onclick="openAiPwdModal()" style="background:transparent; border:none; color:#a78bfa; font-size:0.75rem; cursor:pointer; display:flex; align-items:center; gap:0.3rem;">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> IA : suggérer un MDP fort
                                </button>
                            </div>
                            <div style="position:relative;">
                                <input class="form-input<?php echo $fieldError($passwordErrors, 'new_password') !== '' ? ' input-error' : ''; ?>" type="password" id="new_password" name="new_password"
                                       placeholder="Minimum 8 caracteres"
                                       style="padding-right:3rem;"
                                       oninput="updatePasswordRules(this.value, 'profile');">
                                <button type="button" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#334155;cursor:pointer;font-size:0.9rem;"
                                        onclick="togglePwdField('new_password','eye-new')">
                                    <i class="fa-regular fa-eye" id="eye-new"></i>
                                </button>
                            </div>
                            <?php if ($fieldError($passwordErrors, 'new_password') !== ''): ?>
                                <div class="field-error"><?php echo htmlspecialchars($fieldError($passwordErrors, 'new_password')); ?></div>
                            <?php endif; ?>
                            <div class="password-rules">
                                <span class="password-rule" id="profile-rule-length">
                                    <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                                    8 caractères min.
                                </span>
                                <span class="password-rule" id="profile-rule-upper">
                                    <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                                    1 majuscule
                                </span>
                                <span class="password-rule" id="profile-rule-special">
                                    <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                                    1 caractère spécial
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirmer le mot de passe</label>
                            <div style="position:relative;">
                                <input class="form-input<?php echo $fieldError($passwordErrors, 'confirm_password') !== '' ? ' input-error' : ''; ?>" type="password" id="confirm_password" name="confirm_password"
                                       placeholder="••••••••" style="padding-right:3rem;">
                                <button type="button" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#334155;cursor:pointer;font-size:0.9rem;"
                                        onclick="togglePwdField('confirm_password','eye-confirm')">
                                    <i class="fa-regular fa-eye" id="eye-confirm"></i>
                                </button>
                            </div>
                            <?php if ($fieldError($passwordErrors, 'confirm_password') !== ''): ?>
                                <div class="field-error"><?php echo htmlspecialchars($fieldError($passwordErrors, 'confirm_password')); ?></div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary" style="width:auto;padding:0.75rem 2rem;">
                                <i class="fa-solid fa-key"></i> Mettre à jour le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ===== FACE ID SETUP CARD ===== -->
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <i class="fa-solid fa-face-smile-beam"></i>
                        Reconnaissance Faciale (Face ID)
                    </div>
                </div>
                <div class="section-card-body" style="text-align:center;">
                    <?php if ($user->getFaceDescriptor()): ?>
                        <div style="margin-bottom:1rem;color:#10b981;font-weight:600;display:flex;align-items:center;justify-content:center;gap:.5rem;">
                            <i class="fa-solid fa-circle-check"></i> Votre visage est enregistré !
                        </div>
                        <p style="font-size:0.9rem;color:var(--text-muted);margin-bottom:1.5rem;">Vous pouvez vous connecter en utilisant la reconnaissance faciale.</p>
                        <button type="button" class="btn btn-outline" onclick="startFaceRegistration()" style="width:auto;margin:0 auto;">
                            <i class="fa-solid fa-camera-rotate"></i> Mettre à jour mon visage
                        </button>
                    <?php else: ?>
                        <div style="margin-bottom:1rem;color:var(--text-muted);display:flex;align-items:center;justify-content:center;gap:.5rem;">
                            <i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;"></i> Visage non enregistré.
                        </div>
                        <p style="font-size:0.9rem;color:var(--text-muted);margin-bottom:1.5rem;">Activez Face ID pour vous connecter rapidement sans mot de passe.</p>
                        <button type="button" class="btn btn-primary" onclick="startFaceRegistration()" style="width:auto;margin:0 auto;">
                            <i class="fa-solid fa-camera"></i> Configurer Face ID
                        </button>
                    <?php endif; ?>

                    <!-- Face API Container (Hidden by default) -->
                    <div id="face-setup-container" style="display:none;margin-top:2rem;position:relative;">
                        <div style="position:relative;width:320px;height:240px;margin:0 auto;border-radius:16px;overflow:hidden;border:2px solid var(--tech-blue);box-shadow:0 0 20px rgba(37,99,235,0.3);">
                            <video id="face-video" width="320" height="240" autoplay muted style="object-fit:cover;"></video>
                            <canvas id="face-canvas" style="position:absolute;top:0;left:0;"></canvas>
                        </div>
                        <p id="face-status" style="margin-top:1rem;font-weight:600;color:var(--tech-blue);">Chargement des modèles...</p>
                        <button type="button" id="face-cancel-btn" class="btn btn-outline" onclick="stopFaceRegistration()" style="margin-top:1rem;width:auto;margin-left:auto;margin-right:auto;">Annuler</button>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <i class="fa-solid fa-circle-info"></i>
                        Informations du compte
                    </div>
                </div>
                <div class="section-card-body">
                    <div style="display:grid;gap:1rem;">
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 0;border-bottom:1px solid var(--border);">
                            <span style="color:var(--text-muted);font-size:0.88rem;">ID Utilisateur</span>
                            <span style="font-family:'JetBrains Mono',monospace;font-size:0.88rem;color:white;">#<?php echo $user->getId(); ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 0;border-bottom:1px solid var(--border);">
                            <span style="color:var(--text-muted);font-size:0.88rem;">Statut</span>
                            <?php echo $statusBadge; ?>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 0;border-bottom:1px solid var(--border);">
                            <span style="color:var(--text-muted);font-size:0.88rem;">Rôle</span>
                            <?php echo $roleBadge; ?>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 0;">
                            <span style="color:var(--text-muted);font-size:0.88rem;">Membre depuis</span>
                            <span style="font-size:0.88rem;color:white;"><?php echo $memberSince; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: ACTIVITY -->
        <div class="tab-panel <?php echo $activeProfileTab === 'activity' ? 'active' : ''; ?>" id="panel-activity">
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Activité récente
                    </div>
                </div>
                <div class="section-card-body">
                    <div class="activity-item">
                        <div class="activity-icon" style="background:rgba(37,99,235,0.15);">
                            <i class="fa-solid fa-user-plus" style="color:var(--tech-blue);"></i>
                        </div>
                        <div>
                            <div class="activity-text"><strong>Compte créé</strong> — Bienvenue sur FreelaSkill !</div>
                            <div class="activity-time"><?php echo $memberSince; ?></div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon" style="background:rgba(16,185,129,0.12);">
                            <i class="fa-solid fa-right-to-bracket" style="color:var(--tech-green);"></i>
                        </div>
                        <div>
                            <div class="activity-text"><strong>Connexion réussie</strong> — Session active</div>
                            <div class="activity-time">Maintenant</div>
                        </div>
                    </div>
                    <?php if ($passwordChangedTime !== null): ?>
                    <div class="activity-item">
                        <div class="activity-icon" style="background:rgba(234,179,8,0.15);">
                            <i class="fa-solid fa-key" style="color:#fbbf24;"></i>
                        </div>
                        <div>
                            <div class="activity-text"><strong>Mot de passe modifié</strong> — Sécurité mise à jour</div>
                            <div class="activity-time"><?php echo date('d/m/Y H:i', $passwordChangedTime); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
<!-- Load face-api.js -->
<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script>
let videoStream = null;

async function startFaceRegistration() {
    const container = document.getElementById('face-setup-container');
    const statusTxt = document.getElementById('face-status');
    const video = document.getElementById('face-video');
    container.style.display = 'block';
    
    statusTxt.textContent = "Chargement des modèles (Patientez...)";
    statusTxt.style.color = 'var(--tech-blue)';

    try {
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

        statusTxt.textContent = "Démarrage de la caméra...";
        videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = videoStream;

        video.addEventListener('play', () => {
            statusTxt.textContent = "Regardez la caméra. Analyse en cours...";
            const canvas = document.getElementById('face-canvas');
            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);

            const interval = setInterval(async () => {
                if (!videoStream) { clearInterval(interval); return; }
                const detections = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
                
                if (detections) {
                    const resized = faceapi.resizeResults(detections, displaySize);
                    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                    faceapi.draw.drawDetections(canvas, resized);

                    if (detections.descriptor) {
                        clearInterval(interval);
                        statusTxt.textContent = "Visage détecté ! Enregistrement...";
                        statusTxt.style.color = "#10b981";
                        
                        const response = await fetch('face_api_handler.php?action=register', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ descriptor: Array.from(detections.descriptor) })
                        });
                        
                        const result = await response.json();
                        if (result && result.success) {
                            statusTxt.textContent = "Succès ! Votre visage est enregistré.";
                            setTimeout(() => {
                                stopFaceRegistration();
                                window.location.reload();
                            }, 2000);
                        } else {
                            statusTxt.textContent = "Erreur: " + (result ? result.message : 'inconnue');
                            statusTxt.style.color = "#ef4444";
                            setTimeout(() => stopFaceRegistration(), 3000);
                        }
                    }
                }
            }, 500);
        });

    } catch (err) {
        console.error(err);
        statusTxt.textContent = "Erreur d'accès à la caméra ou chargement des modèles.";
        statusTxt.style.color = "#ef4444";
    }
}

function stopFaceRegistration() {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    document.getElementById('face-setup-container').style.display = 'none';
}
</script>

<?php include __DIR__ . '/chatbot_widget.php'; ?>
<?php include __DIR__ . '/translate_widget.php'; ?>

<!-- ── AI Password Modal ── -->
<div id="ai-pwd-modal" role="dialog" aria-modal="true" aria-labelledby="ai-pwd-modal-title">
    <div class="ai-pwd-box">
        <div class="ai-pwd-header">
            <div class="ai-pwd-title" id="ai-pwd-modal-title"><span>&#128272;</span> MDP sugg&#233;r&#233;s par l&#39;IA</div>
            <button class="ai-pwd-close" onclick="closeAiPwdModal()" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="ai-pwd-subtitle">Cliquez sur un mot de passe pour l&#39;utiliser automatiquement.</div>
        <div id="ai-pwd-list"></div>
        <button class="ai-pwd-regen" id="ai-pwd-regen-btn" onclick="fetchAiPasswords()" style="display:none;">
            <i class="fa-solid fa-rotate"></i> Reg&#233;n&#233;rer
        </button>
    </div>
</div>
<script src="../assets/badwords.js"></script>
<script>
// ── Avatar Picker ──
function openAvatarPicker() {
    const input = document.getElementById('avatarInput');
    if (input) input.click();
}

// Auto-submit avatar form when file is selected
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                document.getElementById('avatarUploadForm').submit();
            }
        });
    }
});

function showTab(tabId) {
    // Hide all panels
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    // Deactivate all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    // Show target panel
    const targetPanel = document.getElementById('panel-' + tabId);
    if (targetPanel) targetPanel.classList.add('active');
    // Activate target button
    const targetBtn = document.getElementById('tab-' + tabId);
    if (targetBtn) targetBtn.classList.add('active');
}

// ── AI Password Suggester ──
const AI_PWD_API = 'password_suggest_api.php';
function openAiPwdModal() {
    document.getElementById('ai-pwd-modal').classList.add('open');
    fetchAiPasswords();
}
function closeAiPwdModal() {
    document.getElementById('ai-pwd-modal').classList.remove('open');
}
function fetchAiPasswords() {
    const list = document.getElementById('ai-pwd-list');
    const regenBtn = document.getElementById('ai-pwd-regen-btn');
    regenBtn.style.display = 'none';
    list.innerHTML = `<div class="ai-pwd-spinner"><i class="fa-solid fa-spinner"></i>L'IA génère vos mots de passe…</div>`;
    fetch(AI_PWD_API, { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            if (!data.passwords || !data.passwords.length) throw new Error('Réponse vide.');
            renderPasswords(data.passwords);
        })
        .catch(err => {
            list.innerHTML = `<div class="ai-pwd-error"><i class="fa-solid fa-circle-exclamation"></i> ${err.message || 'Erreur réseau.'}</div>`;
        })
        .finally(() => { regenBtn.style.display = 'flex'; });
}
function renderPasswords(passwords) {
    const list = document.getElementById('ai-pwd-list');
    list.innerHTML = '';
    passwords.forEach(pwd => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ai-pwd-option';
        btn.innerHTML = `<span>${escHtml(pwd)}</span><i class="fa-solid fa-arrow-right-to-bracket"></i>`;
        btn.addEventListener('click', () => applyPassword(pwd));
        list.appendChild(btn);
    });
}
function applyPassword(pwd) {
    const pwdInput  = document.getElementById('new_password');
    const confInput = document.getElementById('confirm_password');
    pwdInput.type  = 'text';
    confInput.type = 'text';
    pwdInput.value  = pwd;
    confInput.value = pwd;
    updatePasswordRules(pwd, 'profile');
    setTimeout(() => {
        pwdInput.type  = 'password';
        confInput.type = 'password';
    }, 2000);
    closeAiPwdModal();
}
function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Password Helpers ──
function togglePwdField(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
function updatePasswordRules(val, prefix) {
    const checks = {
        length: val.length >= 8,
        upper: /[A-Z]/.test(val),
        special: /[^A-Za-z0-9]/.test(val)
    };
    const lengthRule = document.getElementById(prefix + '-rule-length');
    const upperRule = document.getElementById(prefix + '-rule-upper');
    const specialRule = document.getElementById(prefix + '-rule-special');
    if (lengthRule) lengthRule.classList.toggle('is-valid', checks.length);
    if (upperRule) upperRule.classList.toggle('is-valid', checks.upper);
    if (specialRule) specialRule.classList.toggle('is-valid', checks.special);
}

document.addEventListener('DOMContentLoaded', function() {
    // Watch bio field for inappropriate content
    BadWordsGuard.watch('bio', 'badwords_api.php', { delay: 900 });

    // Guard the profile save form
    const profileForms = document.querySelectorAll('form[action="profile.php"]');
    profileForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const action = form.querySelector('[name="action"]');
            if (action && action.value === 'update') {
                if (BadWordsGuard.isBlocked('bio')) {
                    e.preventDefault();
                    alert('Votre bio contient du contenu inapproprié. Merci de la modifier avant de sauvegarder.');
                }
            }
        });
    });
});
</script>
</body>
</html>

