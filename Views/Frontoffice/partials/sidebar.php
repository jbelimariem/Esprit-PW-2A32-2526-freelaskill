<?php
$activePage  = $activePage  ?? '';
$roleName    = $roleName    ?? 'Utilisateur';
$isClient    = $isClient    ?? false;
$currentFile = basename($_SERVER['PHP_SELF']);
$inContrats  = ($activePage === 'contrats');
$inRules     = ($activePage === 'rules');
?>
<aside class="admin-sidebar">

    <!-- Profile Card (sans logo — la navbar a déjà le logo) -->
    <div style="
        margin: 1rem 0.75rem 0.5rem;
        background: linear-gradient(145deg, rgba(20,30,70,0.8) 0%, rgba(10,15,40,0.95) 100%);
        border: 1px solid rgba(37,99,235,0.3);
        border-radius: 20px;
        padding: 1.75rem 1rem 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.6rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    ">
        <div style="
            position: absolute; top: -40px; left: 50%;
            transform: translateX(-50%);
            width: 160px; height: 160px;
            background: radial-gradient(circle, rgba(37,99,235,0.35) 0%, transparent 65%);
            border-radius: 50%; pointer-events: none;
        "></div>

        <div style="
            width: 72px; height: 72px; border-radius: 50%;
            background: linear-gradient(135deg, #3B6FE8 0%, #5B5FE8 50%, #4F46E5 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: white;
            box-shadow: 0 0 0 5px rgba(37,99,235,0.2), 0 0 30px rgba(37,99,235,0.4), 0 8px 20px rgba(0,0,0,0.4);
            position: relative; z-index: 1; flex-shrink: 0;
        ">
            <i class="fa-solid <?php echo $isClient ? 'fa-user-tie' : 'fa-laptop-code'; ?>"></i>
        </div>

        <div style="
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.05rem; font-weight: 700; color: #F1F5F9;
            position: relative; z-index: 1; letter-spacing: -0.3px;
        "><?php echo htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8'); ?></div>

        <div style="font-size: 0.75rem; color: #64748B; position: relative; z-index: 1;">
            Espace Personnel
        </div>
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
