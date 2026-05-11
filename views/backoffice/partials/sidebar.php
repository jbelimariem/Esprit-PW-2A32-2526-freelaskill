<?php
$activePage = $activePage ?? '';
$inContrats = ($activePage === 'contrats');
$inRules    = ($activePage === 'rules');
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">

    <!-- Logo — même style que marketplace -->
    <div class="sidebar-logo-mk">
        <div class="sidebar-logo-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L18 10H6L12 2Z" fill="#EF4444"/>
                <circle cx="6" cy="18" r="4" fill="#EF4444"/>
                <rect x="14" y="14" width="8" height="8" rx="1.5" fill="#EF4444"/>
            </svg>
        </div>
        <div>
            <div class="sidebar-logo-text">Freela <span>Skill</span></div>
            <div class="sidebar-logo-sub">Admin Control v1.0</div>
        </div>
    </div>

    <!-- Navigation principale -->
    <nav class="sidebar-nav">

        <a href="admin_dashboard.php" class="sidebar-nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
            <div class="sidebar-nav-icon">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <span>Dashboard</span>
        </a>

        <a href="admin_contrat_list.php" class="sidebar-nav-item <?php echo $inContrats ? 'active' : ''; ?>">
            <div class="sidebar-nav-icon">
                <i class="fa-solid fa-file-contract"></i>
            </div>
            <span>Contrats</span>
            <?php if ($inContrats): ?>
                <i class="fa-solid fa-chevron-down" style="margin-left:auto;font-size:0.65rem;opacity:0.5;"></i>
            <?php endif; ?>
        </a>
        <?php if ($inContrats): ?>
            <a href="admin_contrat_form.php" class="sidebar-nav-sub <?php echo $currentFile === 'admin_contrat_form.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-plus"></i> Créer un contrat
            </a>
            <a href="admin_contrat_list.php" class="sidebar-nav-sub <?php echo $currentFile === 'admin_contrat_list.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-list"></i> Liste des contrats
            </a>
        <?php endif; ?>

        <a href="admin_rules_list.php" class="sidebar-nav-item <?php echo $inRules ? 'active' : ''; ?>">
            <div class="sidebar-nav-icon">
                <i class="fa-solid fa-gavel"></i>
            </div>
            <span>Règles</span>
        </a>
        <?php if ($inRules): ?>
            <a href="admin_rules_form.php" class="sidebar-nav-sub <?php echo $currentFile === 'admin_rules_form.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-plus"></i> Créer une règle
            </a>
            <a href="admin_rules_list.php" class="sidebar-nav-sub <?php echo $currentFile === 'admin_rules_list.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-list"></i> Liste des règles
            </a>
        <?php endif; ?>

        <a href="admin_escrow.php" class="sidebar-nav-item <?php echo $activePage === 'escrow' ? 'active' : ''; ?>">
            <div class="sidebar-nav-icon" style="background:rgba(16,185,129,0.15);">
                <i class="fa-solid fa-lock" style="color:#10B981;"></i>
            </div>
            <span>Escrow</span>
            <span class="sidebar-badge" style="background:rgba(16,185,129,0.2);color:#34D399;">NEW</span>
        </a>

        <a href="admin_email_config.php" class="sidebar-nav-item <?php echo $activePage === 'email' ? 'active' : ''; ?>">
            <div class="sidebar-nav-icon" style="background:rgba(99,102,241,0.15);">
                <i class="fa-solid fa-envelope" style="color:#818CF8;"></i>
            </div>
            <span>Emails</span>
            <span class="sidebar-badge" style="background:rgba(99,102,241,0.2);color:#818CF8;">NEW</span>
        </a>

        <a href="admin_rules_list.php" class="sidebar-nav-item <?php echo $activePage === 'rules' ? 'active' : ''; ?>">
            <div class="sidebar-nav-icon" style="background:rgba(239,68,68,0.12);">
                <i class="fa-solid fa-shield-halved" style="color:#EF4444;"></i>
            </div>
            <span>Securité</span>
        </a>

        <!-- ── Messagerie ── -->
        <?php
        // Redirection selon le rôle : admin → dashboard messagerie, autre → conversations
        $msg_role = $_SESSION['user_role'] ?? 'user';
        $msg_url  = ($msg_role === 'admin')
            ? '/freelaskill/messagerie_index.php?page=admin'
            : '/freelaskill/messagerie_index.php?page=conversations';
        ?>
        <a href="<?php echo $msg_url; ?>"
           class="sidebar-nav-item <?php echo $activePage === 'messagerie' ? 'active' : ''; ?>">
            <div class="sidebar-nav-icon" style="background:rgba(37,99,235,0.12);">
                <i class="fa-solid fa-comments" style="color:#60A5FA;"></i>
            </div>
            <span>Messagerie</span>
        </a>

        <div class="sidebar-divider-mk"></div>

        <!-- Notifications -->
        <button onclick="toggleNotifPanel()" class="sidebar-nav-item" style="width:100%;background:none;border:none;cursor:pointer;text-align:left;">
            <div class="sidebar-nav-icon">
                <i class="fa-solid fa-bell"></i>
            </div>
            <span>Notifications</span>
            <span id="notif-badge" style="display:none;margin-left:auto;background:#EF4444;color:white;font-size:0.62rem;font-weight:700;padding:0.15rem 0.45rem;border-radius:999px;min-width:18px;text-align:center;"></span>
        </button>

        <div id="notif-panel" style="display:none;margin:0 0.75rem 0.5rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:12px;overflow:hidden;max-height:260px;overflow-y:auto;">
            <div style="padding:0.6rem 1rem;border-bottom:1px solid rgba(255,255,255,0.07);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:#0a0f1e;z-index:1;">
                <span style="font-size:0.72rem;font-weight:700;color:#CBD5E1;text-transform:uppercase;letter-spacing:0.5px;">Notifications</span>
                <button onclick="markAllRead()" style="background:transparent;border:none;cursor:pointer;color:#2563EB;font-size:0.7rem;font-weight:600;font-family:inherit;">Tout lire</button>
            </div>
            <div id="notif-list">
                <div style="padding:1rem;text-align:center;color:#475569;font-size:0.8rem;">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>

        <div class="sidebar-divider-mk"></div>

        <!-- Thème -->
        <button onclick="toggleTheme()" class="sidebar-nav-item" style="width:100%;background:none;border:none;cursor:pointer;text-align:left;">
            <div class="sidebar-nav-icon">
                <i id="theme-icon" class="fa-solid fa-moon"></i>
            </div>
            <span id="theme-label">Mode clair</span>
        </button>

    </nav>

    <!-- Footer -->
    <div class="sidebar-footer-mk">
        <a href="#" class="sidebar-nav-item" style="color:#F87171;">
            <div class="sidebar-nav-icon" style="background:rgba(239,68,68,0.1);">
                <i class="fa-solid fa-right-from-bracket" style="color:#F87171;"></i>
            </div>
            <span>Retour au Hub</span>
        </a>
    </div>

</aside>

<style>
/* ── Sidebar Marketplace Style ─────────────────────────────── */
.admin-sidebar {
    background: #080d1a !important;
    border-right: 1px solid rgba(255,255,255,0.06) !important;
    width: 240px !important;
}

/* Logo */
.sidebar-logo-mk {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.5rem 1.25rem 1.25rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.sidebar-logo-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: rgba(239,68,68,0.12);
    border: 1px solid rgba(239,68,68,0.2);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.sidebar-logo-text {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: #F1F5F9;
    letter-spacing: -0.3px;
}

.sidebar-logo-text span { color: #2563EB; }

.sidebar-logo-sub {
    font-size: 0.62rem;
    color: #475569;
    letter-spacing: 0.5px;
    margin-top: 0.1rem;
}

/* Navigation */
.sidebar-nav {
    flex: 1;
    padding: 0.75rem 0;
    overflow-y: auto;
}

.sidebar-nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 1.25rem;
    color: #64748B;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    border-radius: 0;
    position: relative;
    font-family: 'Inter', sans-serif;
}

.sidebar-nav-item:hover {
    color: #CBD5E1;
    background: rgba(255,255,255,0.04);
}

.sidebar-nav-item.active {
    color: #F1F5F9;
    background: rgba(37,99,235,0.12);
    border-left: 2px solid #2563EB;
}

.sidebar-nav-item.active .sidebar-nav-icon {
    background: rgba(37,99,235,0.2);
    color: #60A5FA;
}

/* Icône ronde */
.sidebar-nav-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: rgba(255,255,255,0.05);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.82rem;
    color: #64748B;
    flex-shrink: 0;
    transition: all 0.2s;
}

.sidebar-nav-item:hover .sidebar-nav-icon {
    background: rgba(255,255,255,0.08);
    color: #CBD5E1;
}

/* Sous-items */
.sidebar-nav-sub {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.5rem 1.25rem 0.5rem 3.5rem;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 400;
    text-decoration: none;
    transition: all 0.2s;
    font-family: 'Inter', sans-serif;
}

.sidebar-nav-sub:hover { color: #94A3B8; background: rgba(255,255,255,0.03); }
.sidebar-nav-sub.active { color: #60A5FA; font-weight: 600; }
.sidebar-nav-sub i { font-size: 0.7rem; opacity: 0.6; }

/* Badge NEW */
.sidebar-badge {
    margin-left: auto;
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
    letter-spacing: 0.3px;
}

/* Divider */
.sidebar-divider-mk {
    height: 1px;
    background: rgba(255,255,255,0.05);
    margin: 0.5rem 1.25rem;
}

/* Footer */
.sidebar-footer-mk {
    border-top: 1px solid rgba(255,255,255,0.06);
    padding: 0.5rem 0;
    margin-top: auto;
}

/* Ajuster la largeur du main */
.admin-main {
    margin-left: 240px !important;
}

/* Light mode overrides */
body.light-mode .admin-sidebar {
    background: #ffffff !important;
    border-right-color: #e2e8f0 !important;
}
body.light-mode .sidebar-nav-item { color: #64748b; }
body.light-mode .sidebar-nav-item:hover { background: rgba(37,99,235,0.05); color: #1e293b; }
body.light-mode .sidebar-nav-item.active { background: rgba(37,99,235,0.08); color: #1e293b; }
body.light-mode .sidebar-nav-icon { background: rgba(0,0,0,0.04); color: #64748b; }
body.light-mode .sidebar-logo-text { color: #0f172a; }
body.light-mode .sidebar-logo-sub { color: #94a3b8; }
body.light-mode .sidebar-divider-mk { background: #e2e8f0; }
body.light-mode .sidebar-footer-mk { border-top-color: #e2e8f0; }
</style>

<script>
const NOTIF_API = '/freelaskill/controllers/notificationController.php';

async function loadNotifications() {
    try {
        const r    = await fetch(NOTIF_API + '?action=get_unread');
        const data = await r.json();
        if (!data.success) return;
        const badge = document.getElementById('notif-badge');
        const list  = document.getElementById('notif-list');
        if (!badge || !list) return;
        badge.textContent   = data.count > 9 ? '9+' : data.count;
        badge.style.display = data.count > 0 ? 'inline-block' : 'none';
        if (data.items.length === 0) {
            list.innerHTML = `<div style="padding:1.25rem;text-align:center;color:#475569;font-size:0.8rem;">
                <i class="fa-solid fa-check-circle" style="color:#34D399;display:block;font-size:1.3rem;margin-bottom:0.4rem;"></i>
                Aucune nouvelle notification
            </div>`;
            return;
        }
        list.innerHTML = data.items.map(n => `
            <div onclick="markRead(${n.id},this)"
                 style="padding:0.7rem 1rem;border-bottom:1px solid rgba(255,255,255,0.05);cursor:pointer;display:flex;gap:0.6rem;align-items:flex-start;transition:background 0.15s;"
                 onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='transparent'">
                <div style="width:28px;height:28px;border-radius:50%;background:${n.color}22;color:${n.color};display:flex;align-items:center;justify-content:center;font-size:0.75rem;flex-shrink:0;margin-top:1px;">
                    <i class="fa-solid ${n.icon}"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:0.78rem;font-weight:600;color:#CBD5E1;margin-bottom:0.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${n.titre_contrat}</div>
                    <div style="font-size:0.7rem;color:#475569;line-height:1.4;">${n.message}</div>
                    <div style="font-size:0.65rem;color:#334155;margin-top:0.2rem;">${n.date_relative}</div>
                </div>
                <div style="width:6px;height:6px;border-radius:50%;background:#2563EB;flex-shrink:0;margin-top:4px;" class="unread-dot"></div>
            </div>
        `).join('');
    } catch(e) {}
}

function toggleNotifPanel() {
    const panel = document.getElementById('notif-panel');
    const open  = panel.style.display !== 'none';
    panel.style.display = open ? 'none' : 'block';
    if (!open) loadNotifications();
}

async function markRead(id, el) {
    await fetch(NOTIF_API + '?action=mark_read&id=' + id);
    if (el) { el.style.opacity='0.5'; const d=el.querySelector('.unread-dot'); if(d) d.style.display='none'; }
    loadNotifications();
}

async function markAllRead() {
    await fetch(NOTIF_API + '?action=mark_all_read');
    loadNotifications();
}

document.addEventListener('DOMContentLoaded', () => {
    loadNotifications();
    setInterval(loadNotifications, 30000);
});
</script>
