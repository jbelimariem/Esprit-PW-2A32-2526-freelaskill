<?php
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
        flex-shrink: 0;
    ">
        <div style="position:absolute;top:-40px;left:50%;transform:translateX(-50%);width:160px;height:160px;background:radial-gradient(circle,rgba(37,99,235,0.35) 0%,transparent 65%);border-radius:50%;pointer-events:none;"></div>
        <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#3B6FE8 0%,#5B5FE8 50%,#4F46E5 100%);display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:white;box-shadow:0 0 0 5px rgba(37,99,235,0.2),0 0 30px rgba(37,99,235,0.4),0 8px 20px rgba(0,0,0,0.4);position:relative;z-index:1;flex-shrink:0;">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:1.05rem;font-weight:700;color:#F1F5F9;position:relative;z-index:1;letter-spacing:-0.3px;">SuperAdmin</div>
        <div style="font-size:0.75rem;color:#64748B;position:relative;z-index:1;">Espace Administration</div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-section-label">Navigation</div>

    <a href="admin_dashboard.php" class="nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
        <i class="fa-solid fa-gauge-high"></i> Dashboard
    </a>

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

    <a href="admin_escrow.php" class="nav-item <?php echo $activePage === 'escrow' ? 'active' : ''; ?>" style="<?php echo $activePage === 'escrow' ? '' : ''; ?>">
        <i class="fa-solid fa-lock" style="color:#10B981;"></i> Escrow
        <span style="margin-left:auto;background:rgba(16,185,129,0.15);color:#34D399;font-size:0.65rem;font-weight:700;padding:0.15rem 0.45rem;border-radius:999px;">NEW</span>
    </a>

    <a href="admin_approbations.html" class="nav-item <?php echo $activePage === 'approbations' ? 'active' : ''; ?>">
        <i class="fa-solid fa-check-double"></i> Validations
    </a>
    <a href="admin_litiges.html" class="nav-item <?php echo $activePage === 'litiges' ? 'active' : ''; ?>">
        <i class="fa-solid fa-scale-balanced"></i> Litiges
    </a>
    <a href="admin_archivage.html" class="nav-item <?php echo $activePage === 'archivage' ? 'active' : ''; ?>">
        <i class="fa-solid fa-box-archive"></i> Archivage
    </a>

    <div class="sidebar-divider"></div>

    <!-- Notifications -->
    <div style="padding:0 0.75rem 0.5rem;">
        <button onclick="toggleNotifPanel()" style="
            width:100%;display:flex;align-items:center;gap:0.75rem;
            padding:0.75rem 1rem;background:transparent;border:none;cursor:pointer;
            color:var(--text-muted);font-size:0.9rem;font-weight:500;font-family:inherit;
            border-radius:8px;transition:all 0.2s;position:relative;border-left:3px solid transparent;
        "
        onmouseover="this.style.background='var(--nav-hover)';this.style.color='var(--text-light)'"
        onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
            <i class="fa-solid fa-bell" style="width:18px;text-align:center;font-size:0.95rem;"></i>
            Notifications
            <span id="notif-badge" style="display:none;margin-left:auto;background:#EF4444;color:white;font-size:0.65rem;font-weight:700;padding:0.15rem 0.45rem;border-radius:999px;min-width:18px;text-align:center;"></span>
        </button>

        <div id="notif-panel" style="display:none;margin-top:0.25rem;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden;max-height:280px;overflow-y:auto;">
            <div style="padding:0.65rem 1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:var(--bg-card);z-index:1;">
                <span style="font-size:0.75rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:0.5px;">
                    <i class="fa-solid fa-bell" style="color:var(--tech-blue);margin-right:0.3rem;"></i>Notifications
                </span>
                <button onclick="markAllRead()" style="background:transparent;border:none;cursor:pointer;color:var(--tech-blue);font-size:0.72rem;font-weight:600;font-family:inherit;">Tout lire</button>
            </div>
            <div id="notif-list">
                <div style="padding:1rem;text-align:center;color:var(--text-muted);font-size:0.82rem;">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Thème -->
    <div style="padding:0 0.75rem 1rem;">
        <div class="theme-toggle" onclick="toggleTheme()" title="Changer le thème">
            <i id="theme-icon" class="fa-solid fa-moon"></i>
            <span id="theme-label">Mode clair</span>
        </div>
    </div>

</aside>

<script>
const NOTIF_API = '/Esprit-PW-2A32-2526-TalentBridge-job/controllers/notificationController.php';

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
            list.innerHTML = `<div style="padding:1.25rem;text-align:center;color:var(--text-muted);font-size:0.82rem;">
                <i class="fa-solid fa-check-circle" style="color:#34D399;display:block;font-size:1.5rem;margin-bottom:0.5rem;"></i>
                Aucune nouvelle notification
            </div>`;
            return;
        }
        list.innerHTML = data.items.map(n => `
            <div onclick="markRead(${n.id},this)"
                 style="padding:0.75rem 1rem;border-bottom:1px solid var(--border);cursor:pointer;display:flex;gap:0.65rem;align-items:flex-start;transition:background 0.15s;"
                 onmouseover="this.style.background='var(--nav-hover)'" onmouseout="this.style.background='transparent'">
                <div style="width:30px;height:30px;border-radius:50%;background:${n.color}22;color:${n.color};display:flex;align-items:center;justify-content:center;font-size:0.8rem;flex-shrink:0;margin-top:1px;">
                    <i class="fa-solid ${n.icon}"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:0.8rem;font-weight:600;color:var(--text-light);margin-bottom:0.15rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${n.titre_contrat}</div>
                    <div style="font-size:0.73rem;color:var(--text-muted);line-height:1.4;">${n.message}</div>
                    <div style="font-size:0.67rem;color:#475569;margin-top:0.25rem;">${n.date_relative}</div>
                </div>
                <div style="width:7px;height:7px;border-radius:50%;background:#2563EB;flex-shrink:0;margin-top:5px;" class="unread-dot"></div>
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
