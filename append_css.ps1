$css = @"

/* ══════════════════════════════════════════════
   MARKETPLACE LAYOUT — Sidebar & Nav
══════════════════════════════════════════════ */

.marketplace-layout {
    display: flex;
    max-width: 100%;
    margin: 0;
    padding: 0;
    align-items: stretch;
}

.mkt-sidebar {
    position: fixed;
    top: 80px; /* height of navbar */
    left: 0;
    bottom: 0;
    width: 280px;
    background: rgba(2, 6, 23, 0.7);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border-right: 1px solid var(--border);
    z-index: 90;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding: 1.5rem 1rem 2rem 1.5rem;
    overflow-y: auto;
}

/* Sidebar profile card */
.mkt-profile-card {
    background: linear-gradient(145deg, rgba(30, 41, 59, 0.4), rgba(15, 23, 42, 0.6));
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 1rem;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
    flex-shrink: 0;
}
.mkt-profile-header {
    background: linear-gradient(135deg, rgba(37,99,235,0.2), rgba(139,92,246,0.15));
    padding: 1.5rem;
    text-align: center;
    border-bottom: 1px solid var(--border);
    position: relative;
}
.mkt-profile-header::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(circle at 60% 30%, rgba(59,130,246,0.15), transparent 60%);
    pointer-events: none;
}
.mkt-avatar {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(37,99,235,0.5), rgba(139,92,246,0.4));
    border: 3px solid rgba(59,130,246,0.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; font-weight: 700; color: white;
    margin: 0 auto 0.85rem;
    box-shadow: 0 0 24px rgba(59,130,246,0.3);
    position: relative; z-index: 1;
}
.mkt-profile-name { font-size: 1.05rem; font-weight: 700; color: white; margin-bottom: 0.3rem; position: relative; z-index: 1; }
.mkt-profile-sub  { font-size: 0.8rem; color: var(--text-muted); position: relative; z-index: 1; }
.mkt-profile-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-top: 1px solid var(--border);
}
.mkt-stat {
    padding: 0.9rem;
    text-align: center;
    border-right: 1px solid var(--border);
}
.mkt-stat:last-child { border-right: none; }
.mkt-stat-val   { font-size: 1.2rem; font-weight: 700; color: white; font-family: 'JetBrains Mono', monospace; }
.mkt-stat-label { font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0.15rem; }

/* Second sidebar card: nav */
.mkt-sidebar-card {
    background: rgba(15, 23, 42, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.03);
    border-radius: 1rem;
    padding: 1rem 0;
    display: flex;
    flex-direction: column;
}
.mkt-sidebar-section {
    padding: 0.5rem 0.75rem;
}
.mkt-nav-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #475569;
    font-weight: 700;
    padding: 0.5rem 0.75rem 0.5rem;
}
.nav-item {
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.85rem 1rem;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: var(--transition);
    border: 1px solid transparent;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 2px;
}
.nav-item i { width: 20px; text-align: center; font-size: 1.05rem; flex-shrink: 0; }
.nav-item:hover, .nav-item.active {
    background: rgba(59,130,246,0.14);
    color: white;
    border-color: rgba(59,130,246,0.35);
}
.nav-item.active { color: var(--tech-blue); }
.nav-item.danger  { color: #f87171; }
.nav-item.danger:hover { background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); }

/* Main panel */
.mkt-main {
    flex: 1;
    margin-left: 280px; /* same as sidebar width */
    padding: 2rem 3rem 4rem;
    min-width: 0;
    display: flex;
    flex-direction: column;
}
"@

Add-Content -Path "c:\xampp\htdocs\projet22\views\assets\style.css" -Value $css
