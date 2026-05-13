<?php
// Views/Backoffice/dashboard.php
if (!isset($stats) || !isset($allConversations) || !isset($flaggedMessages)) {
    header('Location: /Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin');
    exit;
}
$currentSearch = htmlspecialchars($_GET['search'] ?? '');
$currentTri    = $_GET['tri'] ?? 'date_desc';

// Donut chart values
$msgTotal  = $stats['msg_normal'] + $stats['msg_deleted'] + $stats['msg_flagged'];
$pNormal   = $msgTotal > 0 ? round(($stats['msg_normal']  / $msgTotal) * 100) : 0;
$pDeleted  = $msgTotal > 0 ? round(($stats['msg_deleted'] / $msgTotal) * 100) : 0;
$pFlagged  = $msgTotal > 0 ? round(($stats['msg_flagged'] / $msgTotal) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | FreelaSkill — Messagerie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <style>
        :root {
            --tech-blue: #3b82f6;
            --tech-blue-hover: #2563eb;
            --tech-green: #10b981;
            --tunisian-red: #ef4444;
            --tech-yellow: #f59e0b;
            --text-muted: #8b9cb8;
            --text-light: #cbd5e1;
            --bg-dark: #060a18;
            --bg-card: rgba(255,255,255,0.03);
            --border: rgba(255,255,255,0.07);
            --border-hover: rgba(59,130,246,0.4);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 18px;
            --radius-full: 9999px;
            --transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            --neon-blue: 0 0 32px rgba(59,130,246,0.4);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background: var(--bg-dark);
            color: white;
            min-height: 100vh;
        }

        /* Ambient glows */
        .hero-glow {
            position: fixed; top: -200px; right: -200px;
            width: 700px; height: 700px; border-radius: 50%;
            background: radial-gradient(circle, rgba(37,99,235,0.13) 0%, transparent 65%);
            pointer-events: none; z-index: 0;
        }
        .hero-glow-2 {
            position: fixed; bottom: -200px; left: 10%;
            width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(124,58,237,0.10) 0%, transparent 65%);
            pointer-events: none; z-index: 0;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: rgba(2, 6, 23, 0.95);
            border-right: 1px solid rgba(255,255,255,0.08);
            padding: 2rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: .75rem;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            z-index: 100;
            overflow: visible;
            box-shadow: 4px 0 24px rgba(0,0,0,0.25);
        }
        .sidebar .logo {
            display: flex; align-items: center; gap: 0.75rem;
            font-size: 1.4rem; font-weight: 700; margin-bottom: .5rem;
        }
        .sidebar .logo span { color: #3b82f6; }
        .sidebar .logo i { color: var(--tunisian-red) !important; }

        .nav-item {
            color: var(--text-muted);
            display: flex; align-items: center; gap: .85rem;
            padding: .85rem 1.1rem;
            border-radius: 12px; cursor: pointer;
            transition: all .2s;
            border: 1px solid transparent;
            font-size: .88rem;
        }
        .nav-item:hover, .nav-item.active {
            background: rgba(59,130,246,.14);
            color: white;
            border-color: rgba(59,130,246,.35);
        }

        .nav-item-wrapper { position: relative; width: 100%; }
        .submenu {
            position: absolute;
            left: calc(100% + 12px); top: 0;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px; padding: 0.75rem;
            min-width: 220px;
            opacity: 0; visibility: hidden;
            transform: translateX(10px);
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            z-index: 1000;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            pointer-events: none;
        }
        .nav-item-wrapper:hover .submenu {
            opacity: 1; visibility: visible;
            transform: translateX(0);
            pointer-events: auto;
        }
        .submenu-title {
            font-size: 0.7rem; text-transform: uppercase;
            color: #475569; padding: 0.5rem 0.75rem;
            font-weight: 800; letter-spacing: 1px; margin-bottom: 0.25rem;
        }
        .submenu-item {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.7rem 0.85rem; color: #94a3b8;
            text-decoration: none; font-size: 0.82rem;
            border-radius: 10px; transition: all 0.2s ease;
        }
        .submenu-item i { font-size: 0.9rem; width: 18px; text-align: center; color: #3b82f6; }
        .submenu-item:hover { background: rgba(255,255,255,0.05); color: white; transform: translateX(4px); }
        .nav-item-wrapper::after {
            content: ''; position: absolute; right: -12px; top: 0; width: 12px; height: 100%;
        }

        /* ── Main panel ── */
        .admin-main {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Topbar ── */
        .admin-topbar {
            height: 72px;
            border-bottom: 1px solid var(--border);
            background: rgba(2,6,23,0.7);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 90;
        }
        .topbar-search {
            display: flex; align-items: center; gap: 0.75rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 0.65rem 1.25rem;
            border-radius: var(--radius-md);
            width: 340px; transition: var(--transition);
        }
        .topbar-search:focus-within {
            border-color: rgba(37,99,235,0.5);
            background: rgba(37,99,235,0.05);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .topbar-search input {
            background: transparent; border: none; outline: none;
            color: white; font-family: inherit; font-size: 0.92rem; width: 100%;
        }
        .topbar-search input::placeholder { color: #334155; }
        .topbar-search i { color: #94a3b8; }

        .topbar-actions { display: flex; align-items: center; gap: 1rem; }

        .topbar-icon-btn {
            width: 38px; height: 38px; border-radius: 50%;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted); transition: var(--transition);
            cursor: pointer; position: relative; font-size: 1rem; text-decoration: none;
        }
        .topbar-icon-btn:hover { color: white; background: rgba(255,255,255,0.08); }
        .badge-dot {
            position: absolute; top: 0; right: 0;
            width: 16px; height: 16px; border-radius: 50%;
            background: var(--tunisian-red); color: white;
            font-size: 10px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .nav-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.82rem; color: white;
        }
        .admin-badge-pill {
            display: flex; align-items: center; gap: 0.5rem;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.25);
            padding: 0.4rem 1rem;
            border-radius: var(--radius-full);
            font-size: 0.78rem; color: var(--tunisian-red);
        }

        /* ── Content ── */
        .admin-content { padding: 2rem; flex: 1; }

        .admin-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .admin-page-title {
            font-size: 1.8rem;
            font-weight: 700;
        }
        .admin-page-sub {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        .admin-btn {
            background: linear-gradient(135deg, var(--tech-blue), #2563eb);
            color: white; border: none;
            padding: 0.75rem 1.75rem;
            border-radius: var(--radius-md);
            font-size: 0.92rem; font-weight: 600;
            cursor: pointer; transition: var(--transition);
            font-family: 'Space Grotesk', sans-serif;
            white-space: nowrap;
            text-decoration: none;
            display: inline-flex; align-items: center; gap: 0.5rem;
        }
        .admin-btn:hover { filter: brightness(1.1); box-shadow: var(--neon-blue); transform: translateY(-2px); }
        .admin-btn:active { transform: scale(0.96); }

        .admin-btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 0.65rem 1.25rem;
            border-radius: var(--radius-md);
            font-size: 0.88rem; cursor: pointer;
            transition: var(--transition);
            font-family: 'Space Grotesk', sans-serif;
            display: inline-flex; align-items: center; gap: 0.5rem;
            text-decoration: none;
        }
        .admin-btn-outline:hover { color: white; border-color: rgba(255,255,255,0.2); background: rgba(255,255,255,0.05); }

        .admin-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .glass-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: 0 28px 70px rgba(0,0,0,0.14);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .glass-card:hover {
            border-color: var(--border-hover);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.5), var(--neon-blue);
        }
        .glass-card.flex-col { display: flex; flex-direction: column; }

        /* ── KPI cards — marketplace style ── */
        .kpi-card {
            border-radius: 18px;
            padding: 1.35rem 1.5rem;
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .kpi-card:hover { transform: translateY(-3px); }

        .kpi-card--blue   { background: linear-gradient(135deg, rgba(29,78,216,.35) 0%, rgba(15,23,42,.95) 100%); border: 1px solid rgba(59,130,246,.35); }
        .kpi-card--green  { background: linear-gradient(135deg, rgba(4,120,87,.35) 0%, rgba(15,23,42,.95) 100%);  border: 1px solid rgba(16,185,129,.35); }
        .kpi-card--red    { background: linear-gradient(135deg, rgba(153,27,27,.35) 0%, rgba(15,23,42,.95) 100%);  border: 1px solid rgba(239,68,68,.35); }
        .kpi-card--yellow { background: linear-gradient(135deg, rgba(120,80,4,.35) 0%, rgba(15,23,42,.95) 100%);   border: 1px solid rgba(245,158,11,.35); }

        .kpi-card::after {
            content: ''; position: absolute; top: -20px; right: -20px;
            width: 100px; height: 100px; border-radius: 50%; pointer-events: none;
        }
        .kpi-card--blue::after   { background: radial-gradient(circle, rgba(59,130,246,.3), transparent 70%); }
        .kpi-card--green::after  { background: radial-gradient(circle, rgba(16,185,129,.3), transparent 70%); }
        .kpi-card--red::after    { background: radial-gradient(circle, rgba(239,68,68,.3), transparent 70%); }
        .kpi-card--yellow::after { background: radial-gradient(circle, rgba(245,158,11,.3), transparent 70%); }

        .kpi-icon {
            width: 50px; height: 50px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; flex-shrink: 0;
        }
        .kpi-card--blue   .kpi-icon { background: rgba(59,130,246,.2);  color: #93c5fd; }
        .kpi-card--green  .kpi-icon { background: rgba(16,185,129,.2);  color: #6ee7b7; }
        .kpi-card--red    .kpi-icon { background: rgba(239,68,68,.2);   color: #fca5a5; }
        .kpi-card--yellow .kpi-icon { background: rgba(245,158,11,.2);  color: #fcd34d; }

        .kpi-body { flex: 1; min-width: 0; }
        .kpi-label {
            font-size: .68rem; text-transform: uppercase; letter-spacing: 1.2px;
            color: var(--text-muted); margin-bottom: .2rem; font-weight: 600;
        }
        .kpi-value {
            font-size: 2rem; font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            color: white; line-height: 1.1; margin-bottom: .45rem;
        }
        .kpi-sub {
            font-size: .72rem; color: var(--text-muted);
            display: flex; align-items: center; gap: .35rem; flex-wrap: wrap;
        }
        .kpi-sub .trend-up   { color: var(--tech-green); font-weight: 600; }
        .kpi-sub .trend-flag { color: var(--tunisian-red); font-weight: 600; }
        .kpi-sub .trend-warn { color: var(--tech-yellow); font-weight: 600; }

        /* ═══════════════════════════════════════════════
           CHARTS ROW
        ═══════════════════════════════════════════════ */
        .admin-grid-2-1 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .admin-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .admin-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.9rem;
            border-bottom: 1px solid var(--border);
        }
        .admin-list-title {
            font-size: 1rem;
            font-weight: 600;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .admin-list-title i { color: var(--tech-blue); font-size: 0.95rem; }

        /* ═══════════════════════════════════════════════
           TABLE
        ═══════════════════════════════════════════════ */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th {
            text-align: left;
            padding: 0.85rem 1.5rem;
            background: rgba(0,0,0,0.2);
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .data-table td {
            padding: 0.9rem 1.5rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.87rem;
            color: white;
        }
        .data-table tbody tr:hover td { background: rgba(59,130,246,0.08); }
        .data-table tbody tr:last-child td { border-bottom: none; }

        /* ═══════════════════════════════════════════════
           BADGES & PILLS
        ═══════════════════════════════════════════════ */
        .status-pill {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.22rem 0.7rem; border-radius: var(--radius-full);
            font-size: 0.7rem; font-weight: 600;
        }
        .pill-active   { background: rgba(16,185,129,0.1); color: var(--tech-green); border: 1px solid rgba(16,185,129,0.2); }
        .pill-archived { background: rgba(100,116,139,0.1); color: #94a3b8; border: 1px solid rgba(100,116,139,0.2); }
        .pill-flagged  { background: rgba(239,68,68,0.1); color: var(--tunisian-red); border: 1px solid rgba(239,68,68,0.2); }

        .count-chip {
            font-size: 0.72rem; font-weight: 700;
            padding: 0.22rem 0.7rem; border-radius: var(--radius-full);
            background: rgba(59,130,246,0.1); color: var(--tech-blue);
            border: 1px solid rgba(59,130,246,0.2);
        }
        .count-chip.red {
            background: rgba(239,68,68,0.1); color: var(--tunisian-red);
            border-color: rgba(239,68,68,0.2);
        }
        .top-badge {
            font-size: 0.62rem; font-weight: 600;
            padding: 0.14rem 0.52rem; border-radius: var(--radius-full);
            background: rgba(245,158,11,0.1); color: var(--tech-yellow);
            border: 1px solid rgba(245,158,11,0.2); margin-left: 0.4rem; vertical-align: middle;
        }

        /* ═══════════════════════════════════════════════
           ACTION BUTTONS
        ═══════════════════════════════════════════════ */
        .btn-icon {
            background: transparent; border: none;
            color: var(--text-muted); cursor: pointer;
            padding: 0.38rem; border-radius: var(--radius-sm);
            transition: all 0.2s; margin: 0 1px; font-size: 0.88rem;
        }
        .btn-icon:hover        { background: rgba(59,130,246,0.1);  color: var(--tech-blue); }
        .btn-icon.del:hover    { background: rgba(239,68,68,0.1);   color: var(--tunisian-red); }
        .btn-icon.arch:hover   { background: rgba(100,116,139,0.12); color: #94a3b8; }

        .btn-sm {
            padding: 0.32rem 0.85rem;
            border-radius: var(--radius-sm);
            font-size: 0.72rem; font-weight: 500;
            border: none; cursor: pointer;
            transition: all 0.2s;
            font-family: 'Space Grotesk', sans-serif;
            display: inline-flex; align-items: center; gap: 0.3rem;
            text-decoration: none;
        }
        .btn-sm-primary { background: var(--tech-blue); color: white; }
        .btn-sm-primary:hover { background: #2563eb; }
        .btn-sm-danger  { background: var(--tunisian-red); color: white; }
        .btn-sm-danger:hover { background: #dc2626; }
        .btn-sm-ok      { background: var(--tech-green); color: white; }
        .btn-sm-ok:hover { background: #059669; }

        /* ═══════════════════════════════════════════════
           FLAGGED MESSAGES SECTION
        ═══════════════════════════════════════════════ */
        .flagged-item {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
            cursor: pointer;
        }
        .flagged-item:hover { background: rgba(239,68,68,0.04); }
        .flagged-item:last-child { border-bottom: none; }
        .flagged-sender { font-weight: 600; color: var(--tunisian-red); font-size: 0.85rem; margin-bottom: 0.25rem; }
        .flagged-text {
            color: rgba(255,255,255,0.78); font-size: 0.85rem; margin-bottom: 0.5rem;
            background: rgba(239,68,68,0.06);
            border: 1px solid rgba(239,68,68,0.14);
            border-left: 3px solid var(--tunisian-red);
            border-radius: 6px; padding: 0.55rem 0.9rem;
            font-style: italic;
        }
        .flagged-meta-row { display: flex; gap: 1rem; font-size: 0.72rem; color: #475569; }
        .flagged-actions  { display: flex; gap: 0.5rem; margin-top: 0.65rem; flex-wrap: wrap; }

        /* ═══════════════════════════════════════════════
           SEARCH / FILTER ROW
        ═══════════════════════════════════════════════ */
        .section-head {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap; gap: 0.75rem;
        }
        .section-heading {
            font-size: 0.95rem; font-weight: 600;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .section-heading i { color: var(--tech-blue); }

        .search-form { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }

        .search-wrap {
            display: flex; align-items: center;
            background: rgba(17,24,39,0.8);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: var(--radius-sm);
            padding: 0.4rem 0.85rem;
            transition: border-color 0.2s;
        }
        .search-wrap:focus-within { border-color: rgba(37,99,235,0.45); }
        .search-wrap i { color: var(--text-muted); margin-right: 0.5rem; font-size: 0.78rem; }
        .search-wrap input {
            background: transparent; border: none; outline: none;
            color: white; font-size: 0.82rem; font-family: inherit; width: 190px;
        }
        .search-wrap input::placeholder { color: var(--text-muted); }

        .tri-select {
            background: rgba(17,24,39,0.8);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: var(--radius-sm);
            padding: 0.43rem 0.85rem;
            color: white; font-size: 0.82rem; font-family: inherit;
            outline: none; cursor: pointer;
        }
        .tri-select:focus { border-color: rgba(37,99,235,0.45); }

        .result-badge {
            font-size: 0.72rem; color: var(--tech-blue);
            background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2);
            padding: 0.22rem 0.7rem; border-radius: var(--radius-full);
        }

        .empty-state {
            text-align: center; padding: 3rem; color: var(--text-muted);
        }
        .empty-state i { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; }

        /* ═══════════════════════════════════════════════
           DONUT CHART CARD
        ═══════════════════════════════════════════════ */
        .donut-section {
            display: flex; align-items: center;
            gap: 2.5rem; flex-wrap: wrap;
            padding: 1rem 0;
        }
        .donut-wrap { position: relative; width: 140px; height: 140px; flex-shrink: 0; }
        .donut-wrap svg { transform: rotate(-90deg); width: 140px; height: 140px; }
        .donut-center {
            position: absolute; inset: 0;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center;
        }
        .donut-total { font-size: 1.6rem; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
        .donut-label { font-size: 0.62rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0.15rem; }
        .donut-legend { display: flex; flex-direction: column; gap: 1rem; flex: 1; min-width: 180px; }
        .legend-row { display: flex; align-items: center; gap: 0.85rem; }
        .legend-dot { width: 11px; height: 11px; border-radius: 50%; flex-shrink: 0; }
        .ld-green  { background: var(--tech-green);   box-shadow: 0 0 6px rgba(16,185,129,0.5); }
        .ld-slate  { background: #64748b; }
        .ld-red    { background: var(--tunisian-red); box-shadow: 0 0 6px rgba(239,68,68,0.5); }
        .legend-info { flex: 1; }
        .legend-lbl {
            font-size: 0.8rem; color: var(--text-muted);
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 0.22rem;
        }
        .legend-lbl strong { color: white; font-size: 0.88rem; }
        .legend-bar { height: 4px; border-radius: 999px; background: rgba(255,255,255,0.05); overflow: hidden; }
        .legend-fill { height: 100%; border-radius: 999px; }
        .fill-green { background: var(--tech-green); }
        .fill-slate { background: #64748b; }
        .fill-red   { background: var(--tunisian-red); }

        /* ── Responsive ── */
        @media (max-width: 1280px) { .admin-grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 1100px) { .admin-grid-2-1 { grid-template-columns: 1fr; } }
        @media (max-width: 1024px) { .sidebar { width: 220px; padding: 1.5rem 1rem; } .admin-main { margin-left: 220px; } }
        @media (max-width: 860px) {
            .sidebar { display: none; }
            .admin-main { margin-left: 0; }
            .admin-grid-2 { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .admin-grid-4 { grid-template-columns: 1fr; }
            .admin-content { padding: 1.25rem; }
            .donut-section { flex-direction: column; }
        }
        /* ============================================
           LIGHT MODE - Backoffice Admin
        ============================================ */
        body.light-mode {
            background: #f1f5f9; color: #1e293b;
            --text-muted: #64748b; --border: rgba(0,0,0,0.08);
            --bg-dark: #f1f5f9; --bg-card: rgba(255,255,255,0.9);
        }
        body.light-mode .sidebar { background: rgba(255,255,255,0.95); border-right: 1px solid rgba(0,0,0,0.1); }
        body.light-mode .sidebar .logo { color: #1e293b; }
        body.light-mode .sidebar .logo span { color: #2563eb; }
        body.light-mode .nav-item { color: #475569; }
        body.light-mode .nav-item:hover { background: rgba(37,99,235,0.08); color: #2563eb; }
        body.light-mode .nav-item.active { background: rgba(37,99,235,0.12); color: #2563eb; border-color: rgba(37,99,235,0.25); }
        body.light-mode .admin-topbar { background: rgba(255,255,255,0.95); border-bottom: 1px solid rgba(0,0,0,0.08); }
        body.light-mode .topbar-search { background: rgba(0,0,0,0.04); border-color: rgba(0,0,0,0.1); }
        body.light-mode .topbar-search input { color: #1e293b; }
        body.light-mode .topbar-search input::placeholder { color: #94a3b8; }
        body.light-mode .topbar-icon-btn { background: rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.08); color: #475569; }
        body.light-mode .topbar-icon-btn:hover { background: rgba(37,99,235,0.1); color: #2563eb; }
        body.light-mode .glass-card, body.light-mode .admin-section { background: rgba(255,255,255,0.9); border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        body.light-mode .stat-card-value, body.light-mode .admin-page-title, body.light-mode .section-title, body.light-mode .admin-list-title, body.light-mode h1, body.light-mode h2, body.light-mode h3 { color: #0f172a; }
        body.light-mode .data-table { background: white; }
        body.light-mode .data-table th { color: #6b7280; border-bottom: 1px solid rgba(0,0,0,0.08); }
        body.light-mode .data-table td { color: #1e293b; border-bottom: 1px solid rgba(0,0,0,0.05); }
        body.light-mode .data-table tbody tr:hover { background: rgba(37,99,235,0.04); }
        body.light-mode .metric-card { background: white; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 4px 15px rgba(0,0,0,0.05); color: #0f172a; }
        body.light-mode .metric-card p { color: #475569; }
        body.light-mode .search-wrap { background: white; border-color: rgba(0,0,0,0.15); }
        body.light-mode .search-wrap input { color: #1e293b; }
        body.light-mode .tri-select { background: white; color: #1e293b; border-color: rgba(0,0,0,0.15); }
        body.light-mode .category-form input, body.light-mode .category-form textarea, body.light-mode input[type="text"], body.light-mode textarea, body.light-mode select { background: rgba(255,255,255,0.9); border-color: rgba(0,0,0,0.15); color: #1e293b; }
        body.light-mode .btn-outline { border-color: rgba(0,0,0,0.15); color: #374151; }
        body.light-mode .btn-outline:hover { background: rgba(0,0,0,0.05); color: #0f172a; }
        body.light-mode .sidebar-user-name { color: #1e293b; }
        body.light-mode .strip-num { color: #0f172a; }
        body.light-mode .nav-section-label { color: #94a3b8; }
        body.light-mode .donut-total { color: #0f172a; }
        body.light-mode .hero-glow, body.light-mode .hero-glow-2 { opacity: 0.04; }
    </style>
</head>
<body>

<div class="hero-glow"></div>
<div class="hero-glow-2"></div>

<div class="admin-layout">

    <!-- ══════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════ -->
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- ══════════════════════════════════════
         MAIN PANEL
    ══════════════════════════════════════ -->
    <main class="admin-main">

        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="topbar-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Rechercher dans le dashboard…">
            </div>
            <div class="topbar-actions">
                <div class="admin-badge-pill">
                    <i class="fa-solid fa-shield-halved"></i> Administrateur
                </div>
                <a href="/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin" class="topbar-icon-btn" title="Actualiser">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
                <button class="topbar-icon-btn" style="position:relative;" onclick="scrollTo('sec-flagged')">
                    <i class="fa-regular fa-bell"></i>
                    <?php if ($stats['flagged_messages'] > 0): ?>
                        <span class="badge-dot"><?= $stats['flagged_messages'] ?></span>
                    <?php endif; ?>
                </button>
                <?php 
                $initial = strtoupper(substr($_SESSION['user_prenom'] ?? 'A', 0, 1));
                $colors = ['#f44336','#e91e63','#9c27b0','#673ab7','#3f51b5','#2196f3','#03a9f4','#00bcd4','#009688','#4caf50','#8bc34a','#cddc39','#ffc107','#ff9800','#ff5722'];
                $bgColor = $colors[ord($initial) % count($colors)];
                ?>
                <div class="nav-avatar" style="background: <?= $bgColor ?>; color: white; border: none; font-weight: bold; font-size: 0.9rem; display: flex; align-items: center; justify-content: center;"><?= $initial ?></div>
            </div>
        </header>

        <!-- Content -->
        <div class="admin-content">

            <!-- Header row -->
            <div class="admin-header-row">
                <div>
                    <h1 class="admin-page-title">Dashboard Messagerie</h1>
                    <p class="admin-page-sub"><?= date('l d F Y') ?> · Vue d'ensemble de la plateforme</p>
                </div>
                <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
                    <a href="/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin&action=export-pdf" target="_blank" class="admin-btn-outline">
                        <i class="fa-regular fa-file-pdf"></i> Exporter PDF
                    </a>
                    <button class="admin-btn" onclick="location.reload()">
                        <i class="fa-solid fa-rotate-right"></i> Actualiser
                    </button>
                </div>
            </div>

            <!-- ── KPI cards ── -->
            <div class="admin-grid-4">

                <div class="kpi-card kpi-card--blue">
                    <div class="kpi-icon"><i class="fa-solid fa-comments"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Total Conversations</div>
                        <div class="kpi-value"><?= $stats['total_conversations'] ?></div>
                        <div class="kpi-sub">
                            <span class="trend-up"><i class="fa-solid fa-arrow-trend-up"></i> <?= $stats['active_conversations'] ?> actives</span>
                            sur la plateforme
                        </div>
                    </div>
                </div>

                <div class="kpi-card kpi-card--green">
                    <div class="kpi-icon"><i class="fa-regular fa-message"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Total Messages</div>
                        <div class="kpi-value"><?= $stats['total_messages'] ?></div>
                        <div class="kpi-sub">
                            <span class="trend-up"><i class="fa-solid fa-arrow-trend-up"></i> <?= $stats['msg_normal'] ?> normaux</span>
                            échangés
                        </div>
                    </div>
                </div>

                <div class="kpi-card kpi-card--red">
                    <div class="kpi-icon"><i class="fa-solid fa-flag"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Messages Signalés</div>
                        <div class="kpi-value"><?= $stats['flagged_messages'] ?></div>
                        <div class="kpi-sub">
                            <span class="trend-flag"><i class="fa-solid fa-triangle-exclamation"></i> À traiter</span>
                            en attente de modération
                        </div>
                    </div>
                </div>

                <div class="kpi-card kpi-card--yellow">
                    <div class="kpi-icon"><i class="fa-solid fa-trophy"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Conv. la + Active</div>
                        <div class="kpi-value">#<?= $stats['conv_plus_active'] ? $stats['conv_plus_active']['id_conversation'] : '—' ?></div>
                        <div class="kpi-sub">
                            <span class="trend-warn"><i class="fa-solid fa-fire"></i>
                            <?= $stats['conv_plus_active'] ? $stats['conv_plus_active']['total_messages'] . ' messages' : 'Aucune' ?></span>
                            record
                        </div>
                    </div>
                </div>

            </div>
            <!-- /kpi cards -->

            <!-- ── Charts row ── -->
            <div class="admin-grid-2-1" style="margin-bottom:1.5rem;">

                <!-- Line chart: messages timeline (simulated with message status breakdown) -->
                <div class="glass-card">
                    <div class="admin-list-header">
                        <span class="admin-list-title">
                            <i class="fa-solid fa-chart-line"></i>
                            Répartition des messages
                        </span>
                        <div style="display:flex;gap:0.5rem;">
                            <span class="count-chip"><?= $msgTotal ?> total</span>
                        </div>
                    </div>
                    <!-- Donut chart inside the chart card -->
                    <div class="donut-section">
                        <?php
                        $r = 54; $cx = 70; $cy = 70;
                        $circ = 2 * M_PI * $r;
                        $dashNormal  = ($circ * $pNormal  / 100);
                        $dashDeleted = ($circ * $pDeleted / 100);
                        $dashFlagged = ($circ * $pFlagged / 100);
                        ?>
                        <div class="donut-wrap">
                            <svg viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="16"/>
                                <?php if ($msgTotal === 0): ?>
                                    <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="16"/>
                                <?php else: ?>
                                    <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="none" stroke="#10b981" stroke-width="16"
                                            stroke-dasharray="<?= round($dashNormal,2) ?> <?= round($circ-$dashNormal,2) ?>"
                                            stroke-dashoffset="0" stroke-linecap="round"/>
                                    <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="none" stroke="#64748b" stroke-width="16"
                                            stroke-dasharray="<?= round($dashDeleted,2) ?> <?= round($circ-$dashDeleted,2) ?>"
                                            stroke-dashoffset="<?= round(-$dashNormal,2) ?>" stroke-linecap="round"/>
                                    <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="none" stroke="#ef4444" stroke-width="16"
                                            stroke-dasharray="<?= round($dashFlagged,2) ?> <?= round($circ-$dashFlagged,2) ?>"
                                            stroke-dashoffset="<?= round(-$dashNormal-$dashDeleted,2) ?>" stroke-linecap="round"/>
                                <?php endif; ?>
                            </svg>
                            <div class="donut-center">
                                <div class="donut-total"><?= $msgTotal ?></div>
                                <div class="donut-label">total</div>
                            </div>
                        </div>

                        <div class="donut-legend">
                            <div class="legend-row">
                                <div class="legend-dot ld-green"></div>
                                <div class="legend-info">
                                    <div class="legend-lbl">Messages normaux <strong><?= $stats['msg_normal'] ?></strong></div>
                                    <div class="legend-bar"><div class="legend-fill fill-green" style="width:<?= $pNormal ?>%"></div></div>
                                </div>
                            </div>
                            <div class="legend-row">
                                <div class="legend-dot ld-slate"></div>
                                <div class="legend-info">
                                    <div class="legend-lbl">Messages supprimés <strong><?= $stats['msg_deleted'] ?></strong></div>
                                    <div class="legend-bar"><div class="legend-fill fill-slate" style="width:<?= $pDeleted ?>%"></div></div>
                                </div>
                            </div>
                            <div class="legend-row">
                                <div class="legend-dot ld-red"></div>
                                <div class="legend-info">
                                    <div class="legend-lbl">Messages signalés <strong><?= $stats['msg_flagged'] ?></strong></div>
                                    <div class="legend-bar"><div class="legend-fill fill-red" style="width:<?= $pFlagged ?>%"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doughnut: statut des conversations -->
                <div class="glass-card">
                    <div class="admin-list-header">
                        <span class="admin-list-title">
                            <i class="fa-solid fa-circle-half-stroke"></i>
                            Statut des conv.
                        </span>
                    </div>
                    <div style="position:relative;height:220px;display:flex;justify-content:center;align-items:center;">
                        <canvas id="convStatusChart" style="max-width:200px;"></canvas>
                    </div>
                </div>

            </div>

            <!-- Bar chart: top conversations by message count -->
            <div class="admin-grid-2" style="margin-bottom:1.5rem;">

                <div class="glass-card">
                    <div class="admin-list-header">
                        <span class="admin-list-title">
                            <i class="fa-solid fa-chart-bar"></i>
                            Top 5 conversations (messages)
                        </span>
                    </div>
                    <div style="position:relative;height:220px;">
                        <canvas id="topConvChart"></canvas>
                    </div>
                </div>

                <div class="glass-card">
                    <div class="admin-list-header">
                        <span class="admin-list-title">
                            <i class="fa-solid fa-chart-pie"></i>
                            Messages par statut
                        </span>
                    </div>
                    <div style="position:relative;height:220px;">
                        <canvas id="msgStatusChart"></canvas>
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════
                 FLAGGED MESSAGES SECTION
            ══════════════════════════════════════ -->
            <div id="sec-flagged" class="glass-card" style="padding:0;margin-bottom:1.5rem;">
                <div class="section-head">
                    <div class="section-heading">
                        <i class="fa-solid fa-flag" style="color:var(--tunisian-red);"></i>
                        Messages signalés
                        <span class="count-chip red"><?= count($flaggedMessages) ?></span>
                    </div>
                    <div style="display:flex;gap:0.5rem;">
                        <a href="/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin&action=export-pdf" target="_blank"
                           class="btn-sm btn-sm-primary" style="text-decoration:none;">
                            <i class="fa-regular fa-file-pdf"></i> Exporter PDF
                        </a>
                        <button class="btn-sm btn-sm-primary" onclick="location.reload()">
                            <i class="fa-solid fa-rotate-right"></i> Actualiser
                        </button>
                    </div>
                </div>

                <?php if (empty($flaggedMessages)): ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-check-circle" style="color:var(--tech-green);"></i>
                        Aucun message signalé
                        <p style="font-size:0.8rem;margin-top:0.4rem;">Tous les messages respectent les règles</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($flaggedMessages as $msg): ?>
                        <div class="flagged-item" onclick="openChat(<?= $msg['id_conversation'] ?>, <?= $msg['id_message'] ?>)">
                            <div class="flagged-sender" style="display:flex; align-items:center; gap:0.5rem;">
                                <?php 
                                $fInitial = 'U'; // Default if name not in message array
                                $fBgColor = '#64748b';
                                ?>
                                <span style="width:24px; height:24px; border-radius:50%; background:<?= $fBgColor ?>; color:white; display:inline-flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:700;">
                                    <?= $fInitial ?>
                                </span>
                                Utilisateur #<?= $msg['id_expediteur'] ?? '?' ?>
                            </div>
                            <div class="flagged-text">
                                <i class="fa-solid fa-flag" style="color:var(--tunisian-red);margin-right:0.4rem;"></i>
                                <?= nl2br(htmlspecialchars(substr($msg['contenu'], 0, 120))) ?><?php if (strlen($msg['contenu']) > 120): ?>…<?php endif; ?>
                            </div>
                            <div class="flagged-meta-row">
                                <span><i class="fa-regular fa-clock"></i> <?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></span>
                                <span><i class="fa-regular fa-comment"></i> Conv. #<?= $msg['id_conversation'] ?></span>
                            </div>
                            <div class="flagged-actions">
                                <button class="btn-sm btn-sm-ok" onclick="event.stopPropagation(); ignoreMsg(<?= $msg['id_message'] ?>)">
                                    <i class="fa-regular fa-check-circle"></i> Ignorer
                                </button>
                                <button class="btn-sm btn-sm-danger" onclick="event.stopPropagation(); deleteMsg(<?= $msg['id_message'] ?>)">
                                    <i class="fa-regular fa-trash-can"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- ══════════════════════════════════════
                 ALL CONVERSATIONS TABLE
            ══════════════════════════════════════ -->
            <div id="sec-conversations" class="glass-card" style="padding:0;">
                <div class="section-head">
                    <div class="section-heading">
                        <i class="fa-solid fa-comments"></i>
                        Toutes les conversations
                        <span class="result-badge" id="convCount" style="display:none;"></span>
                    </div>
                    <div class="search-form">
                        <div class="search-wrap">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="convSearch"
                                   placeholder="Rechercher…"
                                   autocomplete="off"
                                   oninput="filterConvTable()">
                        </div>
                        <select class="tri-select" id="convTri" onchange="filterConvTable()">
                            <option value="date_desc">Plus récent</option>
                            <option value="date_asc">Plus ancien</option>
                            <option value="messages">Plus de messages</option>
                        </select>
                        <button type="button" class="btn-sm btn-sm-primary" onclick="clearConvSearch()">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Participants</th>
                            <th>Statut</th>
                            <th>Messages</th>
                            <th>Création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="convTbody">
                        <?php if (empty($allConversations)): ?>
                            <tr id="convEmpty" <?= !empty($allConversations) ? 'style="display:none"' : '' ?>>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fa-regular fa-folder-open"></i>
                                        Aucune conversation
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allConversations as $conv):
                                $isTop = $stats['conv_plus_active'] &&
                                         $conv['id_conversation'] === $stats['conv_plus_active']['id_conversation'];

                                // Decode titre: group JSON or plain string
                                $titreRaw  = $conv['titre'] ?? '';
                                $titreData = json_decode($titreRaw, true);
                                $isGroupe  = is_array($titreData) && !empty($titreData['groupe']);
                                if ($isGroupe) {
                                    $titreName    = $titreData['nom'] ?? 'Groupe';
                                    $membreCount  = count($titreData['membres'] ?? []);
                                } elseif ($titreRaw !== '') {
                                    $titreName   = $titreRaw;
                                    $isGroupe    = false;
                                    $membreCount = 0;
                                } else {
                                    $titreName   = null;
                                    $membreCount = 0;
                                }

                                $searchText = $conv['id_conversation'] . ' ' . $conv['id_user1'] . ' ' . $conv['id_user2'] . ' ' . $conv['statut'] . ' ' . strtolower($titreName ?? '');
                            ?>
                                <tr class="conv-row"
                                    data-search="<?= strtolower(htmlspecialchars($searchText)) ?>"
                                    data-date="<?= strtotime($conv['date_creation']) ?>"
                                    data-messages="<?= (int)($conv['total_messages'] ?? 0) ?>">
                                    <td style="font-family:'JetBrains Mono',monospace;color:var(--tech-blue);">
                                        #<?= $conv['id_conversation'] ?>
                                    </td>
                                    <td>
                                        <?php if ($isGroupe): ?>
                                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                                <span style="width:28px;height:28px;border-radius:50%;background:rgba(139,92,246,0.15);border:1px solid rgba(139,92,246,0.3);display:inline-flex;align-items:center;justify-content:center;font-size:0.75rem;color:#a78bfa;flex-shrink:0;">
                                                    <i class="fa-solid fa-users"></i>
                                                </span>
                                                <div>
                                                    <div style="font-weight:600;font-size:0.87rem;color:white;"><?= htmlspecialchars($titreName) ?></div>
                                                    <div style="font-size:0.72rem;color:var(--text-muted);"><?= $membreCount ?> membres</div>
                                                </div>
                                            </div>
                                        <?php elseif ($titreName): ?>
                                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                                <span style="width:28px;height:28px;border-radius:50%;background:rgba(37,99,235,0.13);border:1px solid rgba(59,130,246,0.25);display:inline-flex;align-items:center;justify-content:center;font-size:0.75rem;color:var(--tech-blue);flex-shrink:0;">
                                                    <i class="fa-regular fa-comment"></i>
                                                </span>
                                                <span style="font-size:0.87rem;color:white;"><?= htmlspecialchars($titreName) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span style="font-size:0.82rem;color:var(--text-muted);font-style:italic;">
                                                <i class="fa-regular fa-user" style="margin-right:0.3rem;"></i>Conversation privée
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:var(--text-muted);font-size:0.82rem;">
                                        <i class="fa-regular fa-user" style="margin-right:0.3rem;font-size:0.75rem;"></i><?= $conv['id_user1'] ?>
                                        <span style="margin:0 0.3rem;opacity:0.4;">·</span>
                                        <i class="fa-regular fa-user" style="margin-right:0.3rem;font-size:0.75rem;"></i><?= $conv['id_user2'] ?: '—' ?>
                                    </td>
                                    <td>
                                        <span class="status-pill <?= $conv['statut'] === 'active' ? 'pill-active' : 'pill-archived' ?>">
                                            <i class="fa-solid fa-circle" style="font-size:0.45rem;"></i>
                                            <?= $conv['statut'] === 'active' ? 'Active' : 'Archivée' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $conv['total_messages'] ?? 0 ?>
                                        <?php if ($isTop): ?>
                                            <span class="top-badge">🏆 Top</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:var(--text-muted);">
                                        <?= date('d/m/Y', strtotime($conv['date_creation'])) ?>
                                    </td>
                                    <td>
                                        <button class="btn-icon"      onclick="viewConv(<?= $conv['id_conversation'] ?>)" title="Voir">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                        <button class="btn-icon arch"  onclick="archiveConv(<?= $conv['id_conversation'] ?>)" title="Archiver">
                                            <i class="fa-regular fa-folder"></i>
                                        </button>
                                        <button class="btn-icon del"   onclick="deleteConv(<?= $conv['id_conversation'] ?>)" title="Supprimer">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /admin-content -->
    </main>
</div><!-- /admin-layout -->

<!-- ══════════════════════════════════════
     CHARTS — Chart.js (same config style as marketplace dashboard)
══════════════════════════════════════ -->
<script>
    Chart.defaults.color         = '#94a3b8';
    Chart.defaults.borderColor   = 'rgba(255,255,255,0.08)';
    Chart.defaults.font.family   = "'Space Grotesk', sans-serif";

    /* ── 1. Conversation status doughnut ── */
    const activeCount   = <?= $stats['active_conversations'] ?>;
    const archivedCount = <?= $stats['total_conversations'] - $stats['active_conversations'] ?>;

    new Chart(document.getElementById('convStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Actives', 'Archivées'],
            datasets: [{
                data: [activeCount, archivedCount],
                backgroundColor: ['rgba(16,185,129,0.85)', 'rgba(100,116,139,0.7)'],
                borderColor: ['#10b981', '#64748b'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, boxWidth: 12, boxHeight: 12 } }
            }
        }
    });

    /* ── 2. Top 5 conversations by message count (horizontal bar) ── */
    <?php
    $sorted = $allConversations;
    usort($sorted, fn($a,$b) => (int)$b['total_messages'] - (int)$a['total_messages']);
    $top5 = array_slice($sorted, 0, 5);
    $topLabels = array_map(fn($c) => 'Conv. #' . $c['id_conversation'], $top5);
    $topCounts = array_map(fn($c) => (int)$c['total_messages'], $top5);
    ?>
    new Chart(document.getElementById('topConvChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($topLabels) ?>,
            datasets: [{
                label: 'Messages',
                data: <?= json_encode($topCounts) ?>,
                backgroundColor: [
                    'rgba(59,130,246,0.8)','rgba(16,185,129,0.8)',
                    'rgba(245,158,11,0.8)','rgba(239,68,68,0.8)','rgba(139,92,246,0.8)'
                ],
                borderColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'],
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { grid: { display: false } }
            }
        }
    });

    /* ── 3. Message status bar chart ── */
    new Chart(document.getElementById('msgStatusChart'), {
        type: 'bar',
        data: {
            labels: ['Normaux', 'Supprimés', 'Signalés'],
            datasets: [{
                label: 'Messages',
                data: [<?= $stats['msg_normal'] ?>, <?= $stats['msg_deleted'] ?>, <?= $stats['msg_flagged'] ?>],
                backgroundColor: ['rgba(16,185,129,0.8)','rgba(100,116,139,0.7)','rgba(239,68,68,0.8)'],
                borderColor:     ['#10b981','#64748b','#ef4444'],
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

<!-- ══════════════════════════════════════
     CLIENT-SIDE FILTER + SORT (no page reload)
══════════════════════════════════════ -->
<script>
function filterConvTable() {
    const query  = document.getElementById('convSearch').value.toLowerCase().trim();
    const tri    = document.getElementById('convTri').value;
    const tbody  = document.getElementById('convTbody');
    const rows   = Array.from(tbody.querySelectorAll('tr.conv-row'));
    const empty  = document.getElementById('convEmpty');
    const countEl = document.getElementById('convCount');

    // Filter
    const visible = rows.filter(row => {
        const match = !query || row.dataset.search.includes(query);
        row.style.display = match ? '' : 'none';
        return match;
    });

    // Sort visible rows
    visible.sort((a, b) => {
        if (tri === 'messages')  return parseInt(b.dataset.messages) - parseInt(a.dataset.messages);
        if (tri === 'date_asc')  return parseInt(a.dataset.date)     - parseInt(b.dataset.date);
        return parseInt(b.dataset.date) - parseInt(a.dataset.date); // date_desc
    });
    visible.forEach(r => tbody.appendChild(r));

    // Empty state
    empty.style.display = visible.length === 0 ? '' : 'none';

    // Result badge
    if (query) {
        countEl.textContent = visible.length + ' résultat(s)';
        countEl.style.display = '';
    } else {
        countEl.style.display = 'none';
    }
}

function clearConvSearch() {
    document.getElementById('convSearch').value = '';
    filterConvTable();
}

/* ── ADMIN ACTIONS ── */
function scrollTo(id) {
    document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
}
function viewConv(id) {
    window.open('/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=chat&id=' + id, '_blank');
}
function openChat(convId, msgId) {
    window.open('/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=chat&id=' + convId + '&highlight=' + msgId, '_blank');
}
function archiveConv(id) {
    if (!confirm('Archiver cette conversation ?')) return;
    fetch('/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin&action=archive', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_conversation=' + id
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
function deleteConv(id) {
    if (!confirm('Supprimer définitivement cette conversation et tous ses messages ?')) return;
    fetch('/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin&action=delete-conv', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_conversation=' + id
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
function deleteMsg(id) {
    if (!confirm('Supprimer ce message ?')) return;
    fetch('/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin&action=delete-msg', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_message=' + id
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
function ignoreMsg(id) {
    if (!confirm('Ignorer ce signalement ?')) return;
    fetch('/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin&action=ignore-flag', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_message=' + id
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>

</body>
</html>