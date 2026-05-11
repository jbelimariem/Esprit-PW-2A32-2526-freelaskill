<?php
/**
 * Navbar partagée du frontoffice
 * Variables attendues : $isClient, $roleName, $activePage
 */
$isClient   = $isClient   ?? false;
$roleName   = $roleName   ?? 'Utilisateur';
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<nav style="
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 200;
    background: rgba(2,6,23,0.9);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255,255,255,0.06);
    padding: 0 1.5rem;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
">
    <!-- Logo -->
    <a href="<?php echo $isClient ? 'front_contrat_index.php' : 'front_rules_index.php'; ?>"
       style="display:flex;align-items:center;gap:0.6rem;text-decoration:none;flex-shrink:0;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
            <path d="M12 2L18 10H6L12 2Z" fill="#EF4444"/>
            <circle cx="6" cy="18" r="4" fill="#EF4444"/>
            <rect x="14" y="14" width="8" height="8" rx="1.5" fill="#EF4444"/>
        </svg>
        <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.1rem;color:white;">
            Freela<span style="color:#2563EB;">Skill</span>
        </span>
    </a>

    <!-- Liens de navigation -->
    <div style="display:flex;align-items:center;gap:0.15rem;flex:1;justify-content:center;">
        <?php if ($isClient): ?>
            <a href="client_dashboard.html" class="fnav-link">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
            <a href="publish_job.html" class="fnav-link">
                <i class="fa-solid fa-plus-circle"></i> Lancer un Projet
            </a>
        <?php else: ?>
            <a href="freelancer_jobs.html" class="fnav-link">
                <i class="fa-solid fa-compass"></i> Explorer Missions
            </a>
        <?php endif; ?>
        <a href="front_contrat_index.php" class="fnav-link <?php echo strpos($currentFile, 'contrat') !== false ? 'fnav-active' : ''; ?>">
            <i class="fa-solid fa-file-contract"></i> Mes Contrats
        </a>
        <a href="front_rules_index.php" class="fnav-link <?php echo strpos($currentFile, 'rule') !== false ? 'fnav-active' : ''; ?>">
            <i class="fa-solid fa-gavel"></i> Mes Règles
        </a>
    </div>

    <!-- Actions droite -->
    <div style="display:flex;align-items:center;gap:0.6rem;flex-shrink:0;">
        <button onclick="toggleTheme()" style="background:none;border:none;cursor:pointer;color:#94A3B8;font-size:0.95rem;padding:0.4rem;border-radius:8px;" title="Thème">
            <i id="theme-icon" class="fa-solid fa-moon"></i>
        </button>
        <div style="display:flex;align-items:center;gap:0.4rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);padding:0.35rem 0.8rem;border-radius:999px;">
            <i class="fa-solid <?php echo $isClient ? 'fa-user-tie' : 'fa-laptop-code'; ?>" style="color:#2563EB;font-size:0.78rem;"></i>
            <span style="font-size:0.78rem;font-weight:500;color:#CBD5E1;"><?php echo htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <a href="front_rules_role.php" style="display:flex;align-items:center;padding:0.35rem 0.7rem;background:rgba(239,68,68,0.1);color:#F87171;border:1px solid rgba(239,68,68,0.2);border-radius:999px;font-size:0.78rem;text-decoration:none;" title="Changer de profil">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</nav>

<!-- Spacer pour compenser la navbar fixe -->
<div style="height:60px;flex-shrink:0;"></div>

<style>
.fnav-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.75rem;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 500;
    color: #94A3B8;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
}
.fnav-link:hover { background: rgba(37,99,235,0.1); color: white; }
.fnav-active { background: rgba(37,99,235,0.12) !important; color: #2563EB !important; font-weight: 600 !important; }

body.light-mode nav[style*="position: fixed"] {
    background: rgba(255,255,255,0.95) !important;
    border-bottom-color: #e2e8f0 !important;
}
body.light-mode .fnav-link { color: #64748b; }
body.light-mode .fnav-link:hover { background: rgba(37,99,235,0.07); color: #1e293b; }
</style>
