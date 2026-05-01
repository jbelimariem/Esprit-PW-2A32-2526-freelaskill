<?php
// partials/sidebar.php — Sidebar partagée du backoffice
$activePage = $activePage ?? '';

$inContrats = ($activePage === 'contrats');
$inRules    = ($activePage === 'rules');
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
            <div class="logo-badge">Admin Panel</div>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="sidebar-profile-card">
        <div class="sidebar-avatar">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <div class="sidebar-profile-name">SuperAdmin</div>
        <div class="sidebar-profile-sub">Espace Administration</div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-section-label">Navigation</div>

    <a href="admin_dashboard.html" class="nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
        <i class="fa-solid fa-gauge-high"></i> Dashboard
    </a>

    <!-- Contrats : toujours visible, sous-nav uniquement si on est dans contrats -->
    <a href="admin_contrat.php" class="nav-item <?php echo $inContrats ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-contract"></i> Contrats
    </a>
    <?php if ($inContrats): ?>
        <a href="admin_contrat_form.php" class="nav-item nav-sub <?php echo basename($_SERVER['PHP_SELF']) === 'admin_contrat_form.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-plus"></i> Créer un contrat
        </a>
        <a href="admin_contrat_list.php" class="nav-item nav-sub <?php echo basename($_SERVER['PHP_SELF']) === 'admin_contrat_list.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-list"></i> Liste des contrats
        </a>
    <?php endif; ?>

    <!-- Règles : TOUJOURS visible -->
    <a href="admin_rules_list.php" class="nav-item <?php echo $inRules ? 'active' : ''; ?>">
        <i class="fa-solid fa-gavel"></i> Règles
    </a>
    <?php if ($inRules): ?>
        <a href="admin_rules_form.php" class="nav-item nav-sub <?php echo basename($_SERVER['PHP_SELF']) === 'admin_rules_form.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-plus"></i> Créer une règle
        </a>
        <a href="admin_rules_list.php" class="nav-item nav-sub <?php echo basename($_SERVER['PHP_SELF']) === 'admin_rules_list.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-list"></i> Liste des règles
        </a>
    <?php endif; ?>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section-label">Gestion</div>

    <a href="admin_approbations.html" class="nav-item <?php echo $activePage === 'approbations' ? 'active' : ''; ?>">
        <i class="fa-solid fa-check-double"></i> Validations
    </a>
    <a href="admin_litiges.html" class="nav-item <?php echo $activePage === 'litiges' ? 'active' : ''; ?>">
        <i class="fa-solid fa-scale-balanced"></i> Litiges
    </a>
    <a href="admin_archivage.html" class="nav-item <?php echo $activePage === 'archivage' ? 'active' : ''; ?>">
        <i class="fa-solid fa-box-archive"></i> Archivage
    </a>

    <!-- Footer sidebar -->
    <div class="sidebar-footer">
        <div class="theme-toggle" onclick="toggleTheme()" title="Changer le thème">
            <i id="theme-icon" class="fa-solid fa-moon"></i>
            <span id="theme-label">Mode clair</span>
        </div>
    </div>

</aside>
