<?php
$activePage  = $activePage  ?? '';
$roleName    = $roleName    ?? 'Utilisateur';
$isClient    = $isClient    ?? false;
$currentFile = basename($_SERVER['PHP_SELF']);
$inContrats  = ($activePage === 'contrats');
$inRules     = ($activePage === 'rules');

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
<aside class="admin-sidebar front-sidebar-mk">

    <!-- Profile Card — style marketplace -->
    <div class="front-profile-card">
        <div class="front-profile-avatar">
            <i class="fa-solid <?php echo $isClient ? 'fa-user-tie' : 'fa-laptop-code'; ?>"></i>
        </div>
        <div class="front-profile-name"><?php echo htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="front-profile-sub">FreelaSkill Tunisia</div>

        <!-- Stats — comme marketplace (produits/catégories) -->
        <div class="front-profile-stats">
            <div class="front-stat">
                <div class="front-stat-value"><?php echo $contratCount; ?></div>
                <div class="front-stat-label">CONTRATS</div>
            </div>
            <div class="front-stat-divider"></div>
            <div class="front-stat">
                <div class="front-stat-value"><?php echo $ruleCount; ?></div>
                <div class="front-stat-label">RÈGLES</div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="front-nav-label">NAVIGATION</div>

    <nav class="front-sidebar-nav">

        <?php if ($isClient): ?>
            <a href="client_dashboard.html" class="front-nav-item">
                <div class="front-nav-icon"><i class="fa-solid fa-chart-pie"></i></div>
                <span>Tableau de bord</span>
            </a>
            <a href="publish_job.html" class="front-nav-item">
                <div class="front-nav-icon"><i class="fa-solid fa-plus-circle"></i></div>
                <span>Lancer un Projet</span>
            </a>
        <?php else: ?>
            <a href="freelancer_jobs.html" class="front-nav-item">
                <div class="front-nav-icon"><i class="fa-solid fa-compass"></i></div>
                <span>Explorer Missions</span>
            </a>
        <?php endif; ?>

        <!-- Mes Contrats -->
        <a href="front_contrat_index.php" class="front-nav-item <?php echo $inContrats ? 'active' : ''; ?>">
            <div class="front-nav-icon"><i class="fa-solid fa-file-contract"></i></div>
            <span>Mes Contrats</span>
        </a>
        <?php if ($inContrats): ?>
            <a href="front_contrat_form.php" class="front-nav-sub <?php echo $currentFile === 'front_contrat_form.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-plus"></i> Créer un contrat
            </a>
            <a href="front_contrat_list.php" class="front-nav-sub <?php echo $currentFile === 'front_contrat_list.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-list"></i> Liste des contrats
            </a>
        <?php endif; ?>

        <!-- Mes Règles -->
        <a href="front_rules_index.php" class="front-nav-item <?php echo $inRules ? 'active' : ''; ?>">
            <div class="front-nav-icon"><i class="fa-solid fa-gavel"></i></div>
            <span>Mes Règles</span>
        </a>
        <?php if ($inRules): ?>
            <a href="front_rules_form.php" class="front-nav-sub <?php echo $currentFile === 'front_rules_form.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-plus"></i> Créer une règle
            </a>
            <a href="front_rules_list.php" class="front-nav-sub <?php echo $currentFile === 'front_rules_list.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-list"></i> Liste des règles
            </a>
        <?php endif; ?>

<<<<<<< HEAD
        <!-- Messagerie -->
        <a href="/freelaskill/messagerie_index.php?page=conversations"
           class="front-nav-item <?php echo $activePage === 'messagerie' ? 'active' : ''; ?>">
            <div class="front-nav-icon" style="background:rgba(37,99,235,0.12);">
                <i class="fa-solid fa-comments" style="color:#60A5FA;"></i>
            </div>
            <span>Messagerie</span>
        </a>

=======
>>>>>>> 82705c67f6dd52e299a9ffa6fb62a7b16335bcf5
        <!-- Changer de profil -->
        <a href="front_rules_role.php" class="front-nav-item" style="color:#F87171;">
            <div class="front-nav-icon" style="background:rgba(239,68,68,0.1);">
                <i class="fa-solid fa-right-from-bracket" style="color:#F87171;"></i>
            </div>
            <span>Changer de profil</span>
        </a>

    </nav>

    <!-- Footer thème -->
    <div class="front-sidebar-footer">
        <button onclick="toggleTheme()" class="front-nav-item" style="width:100%;background:none;border:none;cursor:pointer;text-align:left;">
            <div class="front-nav-icon">
                <i id="theme-icon" class="fa-solid fa-moon"></i>
            </div>
            <span id="theme-label">Mode sombre</span>
        </button>
    </div>

</aside>

<style>
/* ── Frontoffice Sidebar — Style Marketplace ─────────────────── */
.front-sidebar-mk {
    background: #080d1a !important;
    border-right: 1px solid rgba(255,255,255,0.06) !important;
    top: 60px !important;
    height: calc(100vh - 60px) !important;
    width: 200px !important;
    display: flex !important;
    flex-direction: column !important;
    overflow-y: auto !important;
    padding: 0 !important;
}

/* Profile Card */
.front-profile-card {
    padding: 1.25rem 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.front-profile-avatar {
    width: 56px; height: 56px;
    border-radius: 14px;
    background: linear-gradient(135deg, #2563EB 0%, #6366F1 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: white;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.25), 0 0 20px rgba(37,99,235,0.3);
    margin-bottom: 0.25rem;
}

.front-profile-name {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    color: #F1F5F9;
}

.front-profile-sub {
    font-size: 0.68rem;
    color: #475569;
}

/* Stats */
.front-profile-stats {
    display: flex;
    align-items: center;
    gap: 0;
    margin-top: 0.5rem;
    width: 100%;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 10px;
    overflow: hidden;
}

.front-stat {
    flex: 1;
    padding: 0.5rem 0.25rem;
    text-align: center;
}

.front-stat-value {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: #F1F5F9;
    line-height: 1;
}

.front-stat-label {
    font-size: 0.58rem;
    color: #475569;
    letter-spacing: 0.5px;
    margin-top: 0.2rem;
    font-weight: 600;
}

.front-stat-divider {
    width: 1px;
    height: 30px;
    background: rgba(255,255,255,0.06);
    flex-shrink: 0;
}

/* Nav label */
.front-nav-label {
    font-size: 0.6rem;
    font-weight: 700;
    color: #334155;
    letter-spacing: 1.5px;
    padding: 0.85rem 1rem 0.4rem;
}

/* Nav items */
.front-sidebar-nav {
    flex: 1;
    padding: 0.25rem 0;
}

.front-nav-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.6rem 1rem;
    color: #64748B;
    font-size: 0.82rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    font-family: 'Inter', sans-serif;
    border-left: 2px solid transparent;
}

.front-nav-item:hover {
    color: #CBD5E1;
    background: rgba(255,255,255,0.04);
}

.front-nav-item.active {
    color: #F1F5F9;
    background: rgba(37,99,235,0.1);
    border-left-color: #2563EB;
}

.front-nav-item.active .front-nav-icon {
    background: rgba(37,99,235,0.2);
    color: #60A5FA;
}

/* Icône ronde */
.front-nav-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: rgba(255,255,255,0.05);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.78rem;
    color: #64748B;
    flex-shrink: 0;
    transition: all 0.2s;
}

.front-nav-item:hover .front-nav-icon {
    background: rgba(255,255,255,0.08);
    color: #CBD5E1;
}

/* Sous-items */
.front-nav-sub {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 1rem 0.45rem 3rem;
    color: #475569;
    font-size: 0.76rem;
    font-weight: 400;
    text-decoration: none;
    transition: all 0.2s;
    font-family: 'Inter', sans-serif;
}

.front-nav-sub:hover { color: #94A3B8; background: rgba(255,255,255,0.03); }
.front-nav-sub.active { color: #60A5FA; font-weight: 600; }
.front-nav-sub i { font-size: 0.65rem; opacity: 0.6; }

/* Footer */
.front-sidebar-footer {
    border-top: 1px solid rgba(255,255,255,0.06);
    padding: 0.4rem 0;
    margin-top: auto;
}

/* Light mode */
body.light-mode .front-sidebar-mk {
    background: #ffffff !important;
    border-right-color: #e2e8f0 !important;
}
body.light-mode .front-profile-card { border-bottom-color: #e2e8f0; }
body.light-mode .front-profile-name { color: #0f172a; }
body.light-mode .front-profile-sub  { color: #94a3b8; }
body.light-mode .front-profile-stats { background: #f8fafc; border-color: #e2e8f0; }
body.light-mode .front-stat-value   { color: #0f172a; }
body.light-mode .front-stat-label   { color: #94a3b8; }
body.light-mode .front-stat-divider { background: #e2e8f0; }
body.light-mode .front-nav-label    { color: #94a3b8; }
body.light-mode .front-nav-item     { color: #64748b; }
body.light-mode .front-nav-item:hover { background: rgba(37,99,235,0.05); color: #1e293b; }
body.light-mode .front-nav-item.active { background: rgba(37,99,235,0.08); color: #1e293b; }
body.light-mode .front-nav-icon     { background: rgba(0,0,0,0.04); color: #64748b; }
body.light-mode .front-nav-sub      { color: #94a3b8; }
body.light-mode .front-sidebar-footer { border-top-color: #e2e8f0; }
</style>
