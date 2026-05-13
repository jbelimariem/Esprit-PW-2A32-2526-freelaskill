<?php
$activePage  = $activePage  ?? '';
$roleName    = $roleName    ?? 'Utilisateur';
$isClient    = $isClient    ?? false;
$currentFile = basename($_SERVER['PHP_SELF']);
$inContrats  = ($activePage === 'contrats' || strpos($currentFile, 'contrat') !== false);
$inRules     = ($activePage === 'rules' || strpos($currentFile, 'rules') !== false);

// Stats rapides
$contratCount = 0;
$ruleCount    = 0;
try {
    require_once __DIR__ . '/../../../config.php';
    $pdo = config::getConnexion();
    $contratCount = $pdo->query('SELECT COUNT(*) FROM contrat')->fetchColumn();
    $ruleCount    = $pdo->query('SELECT COUNT(*) FROM rules')->fetchColumn();
} catch (Exception $e) {}
?>
<aside class="mkt-sidebar">
    <!-- Card 1 : Profil marketplace -->
    <div class="mkt-profile-card">
        <div class="mkt-profile-header">
            <div class="mkt-avatar"><i class="fa-solid <?php echo $isClient ? 'fa-user-tie' : 'fa-laptop-code'; ?>"></i></div>
            <div class="mkt-profile-name"><?php echo htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="mkt-profile-sub">FreelaSkill Tunisia</div>
        </div>
        <div class="mkt-profile-stats">
            <div class="mkt-stat">
                <div class="mkt-stat-val"><?php echo $contratCount; ?></div>
                <div class="mkt-stat-label">CONTRATS</div>
            </div>
            <div class="mkt-stat">
                <div class="mkt-stat-val"><?php echo $ruleCount; ?></div>
                <div class="mkt-stat-label">RÈGLES</div>
            </div>
        </div>
    </div>

    <!-- Card 2 : Navigation -->
    <div class="mkt-sidebar-card">
        <div class="mkt-sidebar-section">
            <div class="mkt-nav-label">Navigation</div>
            <?php if ($isClient): ?>
                <a href="client_dashboard.html" class="nav-item <?php echo $currentFile === 'client_dashboard.html' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-pie"></i> Tableau de bord
                </a>
                <a href="publish_job.html" class="nav-item <?php echo $currentFile === 'publish_job.html' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-plus-circle"></i> Lancer un Projet
                </a>
            <?php else: ?>
                <a href="freelancer_home.php" class="nav-item <?php echo $currentFile === 'freelancer_home.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-briefcase"></i> Missions
                </a>
                <a href="freelancer_applications.php" class="nav-item <?php echo $currentFile === 'freelancer_applications.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-paper-plane"></i> Candidatures
                </a>
            <?php endif; ?>

            <?php $isLegaleActive = ($inContrats || $inRules); ?>
            <div class="nav-item-wrapper <?php echo $isLegaleActive ? 'open' : ''; ?>">
                <a href="front_contrat_index.php" class="nav-item <?php echo $isLegaleActive ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-contract"></i> Contrats
                    <i class="fa-solid fa-chevron-right" style="margin-left:auto;font-size:0.75rem;"></i>
                </a>
                <div class="submenu">
                    <div class="submenu-title">Gestion Légale</div>
                    <a href="front_contrat_list.php" class="submenu-item <?php echo $currentFile === 'front_contrat_list.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-list-check"></i> Liste des contrats
                    </a>
                    <a href="front_contrat_form.php" class="submenu-item <?php echo $currentFile === 'front_contrat_form.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-file-signature"></i> Nouveau contrat
                    </a>
                    <a href="front_rules_list.php" class="submenu-item <?php echo $currentFile === 'front_rules_list.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-gavel"></i> Liste des règles
                    </a>
                    <a href="front_rules_form.php" class="submenu-item <?php echo $currentFile === 'front_rules_form.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-plus-circle"></i> Nouvelle règle
                    </a>
                </div>
            </div>

            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=conversations" class="nav-item">
                <i class="fa-solid fa-comments"></i> Messagerie
            </a>
        </div>
    </div>
</aside>
