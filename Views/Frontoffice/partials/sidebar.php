<?php
$activePage  = $activePage  ?? '';
$roleName    = $roleName    ?? 'Utilisateur';
$isClient    = $isClient    ?? false;
$currentFile = basename($_SERVER['PHP_SELF']);
$inContrats  = ($activePage === 'contrats');
$inRules     = ($activePage === 'rules');
?>
<aside class="admin-sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
            <path d="M12 2L18 10H6L12 2Z" fill="#EF4444"/>
            <circle cx="6" cy="18" r="4" fill="#EF4444"/>
            <rect x="14" y="14" width="8" height="8" rx="1.5" fill="#EF4444"/>
        </svg>
        <div>
            <div class="logo-text">Freela<span>Skill</span></div>
            <div class="logo-badge">Espace Personnel</div>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="sidebar-profile-card">
        <div class="sidebar-avatar">
            <i class="fa-solid <?php echo $isClient ? 'fa-user-tie' : 'fa-laptop-code'; ?>"></i>
        </div>
        <div class="sidebar-profile-name"><?php echo htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="sidebar-profile-sub">Espace Personnel</div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-section-label">Navigation</div>

    <?php if ($isClient): ?>
        <a href="client_dashboard.html" class="nav-item"><i class="fa-solid fa-chart-pie"></i> Tableau de bord</a>
        <a href="publish_job.html" class="nav-item"><i class="fa-solid fa-plus-circle"></i> Lancer un Projet</a>
    <?php else: ?>
        <a href="freelancer_jobs.html" class="nav-item"><i class="fa-solid fa-compass"></i> Explorer Missions</a>
    <?php endif; ?>

    <!-- Contrats — toujours visible -->
    <a href="front_contrat_index.php" class="nav-item <?php echo $inContrats ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-contract"></i> Mes Contrats
    </a>
    <?php if ($inContrats): ?>
        <a href="front_contrat_form.php" class="nav-item nav-sub <?php echo $currentFile === 'front_contrat_form.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-plus"></i> Créer un contrat
        </a>
        <a href="front_contrat_list.php" class="nav-item nav-sub <?php echo $currentFile === 'front_contrat_list.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-list"></i> Liste des contrats
        </a>
    <?php endif; ?>

    <!-- Règles — toujours visible -->
    <a href="front_rules_index.php" class="nav-item <?php echo $inRules ? 'active' : ''; ?>">
        <i class="fa-solid fa-gavel"></i> Mes Règles
    </a>
    <?php if ($inRules): ?>
        <a href="front_rules_form.php" class="nav-item nav-sub <?php echo $currentFile === 'front_rules_form.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-plus"></i> Créer une règle
        </a>
        <a href="front_rules_list.php" class="nav-item nav-sub <?php echo $currentFile === 'front_rules_list.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-list"></i> Liste des règles
        </a>
    <?php endif; ?>

    <div class="sidebar-divider"></div>

    <a href="front_rules_role.php" class="nav-item" style="color:#F87171;">
        <i class="fa-solid fa-right-from-bracket"></i> Changer de profil
    </a>

    <!-- Footer -->
    <div class="sidebar-footer">
        <div class="theme-toggle" onclick="toggleTheme()" title="Changer le thème">
            <i id="theme-icon" class="fa-solid fa-moon"></i>
            <span id="theme-label">Mode clair</span>
        </div>
    </div>

</aside>
