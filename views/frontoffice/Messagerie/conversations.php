<?php
// Views/Frontoffice/conversations.php - Interface style WhatsApp
if (!isset($conversations)) {
    die("Erreur: Données non disponibles");
}
$currentUserId = $_SESSION['user_id'];
$currentSearch = htmlspecialchars($_GET['search'] ?? '');
$currentTri    = $_GET['tri'] ?? 'date_desc';

$totalConvs   = count($conversations);
$activeConvs  = count(array_filter($conversations, fn($c) => $c['statut'] === 'active'));
$totalMsgs    = array_sum(array_column($conversations, 'total_messages'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark:       #020617;
            --sidebar-bg:    rgba(2, 6, 23, 0.7);
            --card-bg:       rgba(255, 255, 255, 0.03);
            --border-color:  rgba(255, 255, 255, 0.08);
            --text-muted:    #94a3b8;
            --tech-blue:     #3b82f6;
            --tech-blue-hover:#2563eb;
            --tunisian-red:  #ef4444;
            --tech-green:    #10b981;
            --tech-yellow:   #f59e0b;
            --radius-lg:     18px;
            --radius-md:     12px;
            --radius-sm:     8px;
            --radius-full:   9999px;
            --transition:    all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --neon-blue:     0 0 32px rgba(59, 130, 246, 0.4);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
            background-color: #020617;
            background-image:
                radial-gradient(ellipse 80% 70% at 75% 0%, rgba(30,58,138,0.3) 0%, transparent 60%),
                radial-gradient(ellipse 50% 50% at 85% 15%, rgba(59,130,246,0.15) 0%, transparent 50%),
                radial-gradient(ellipse 40% 40% at 60% 5%, rgba(88,28,135,0.08) 0%, transparent 50%);
            color: #fff;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* ══════════════════════════════════════
           LEFT SIDEBAR — FreelaSkill style
        ══════════════════════════════════════ */

        /* Logo at top of sidebar — removed, logo is now in navbar */
        .sidebar-logo {
            display: none;
        }
        .sidebar-logo a {
            display: flex; align-items: center; gap: .5rem;
            text-decoration: none;
        }
        .sidebar-logo i { color: var(--tunisian-red); font-size: 1.2rem; }
        .sidebar-logo .logo-freela { font-size: 1.25rem; font-weight: 700; color: white; }
        .sidebar-logo .logo-skill  { font-size: 1.25rem; font-weight: 700; color: var(--tech-blue); }

        /* Page layout wrapper below navbar */
        .page-layout {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .app-sidebar {
            width: 280px;
            background: rgba(2, 6, 23, 0.85);
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-color);
            z-index: 10;
        }
        .sidebar-content {
            flex: 1;
            overflow-y: scroll;
            padding-bottom: 2rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.5) rgba(255,255,255,0.08);
        }
        .sidebar-content::-webkit-scrollbar { width: 6px; }
        .sidebar-content::-webkit-scrollbar-track { background: rgba(255,255,255,0.08); border-radius: 3px; }
        .sidebar-content::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.5); border-radius: 3px; min-height: 40px; }
        .sidebar-content::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.7); }

        /* Module card — unified profile-style card */
        .sidebar-module-card {
            margin: 0 0 1rem 0;
            background: linear-gradient(180deg, #1e3a8a 0%, #0f172a 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            overflow: hidden;
            position: relative;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .sidebar-module-header {
            padding: 1.5rem 1.25rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            background: transparent;
        }
        .sidebar-module-icon {
            width: 68px; height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(37,99,235,0.8), rgba(139,92,246,0.6));
            border: 2px solid rgba(255, 255, 255, 0.1);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem;
            box-shadow: 0 0 25px rgba(59,130,246,0.5);
            position: relative; z-index: 1;
            color: white;
        }
        .sidebar-module-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.15rem; font-weight: 700; color: white;
            position: relative; z-index: 1;
            margin-bottom: .2rem;
        }
        .sidebar-module-sub {
            font-size: .85rem; color: #94a3b8;
            position: relative; z-index: 1;
        }

        /* Stats row — inside module card */
        .sidebar-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: rgba(0, 0, 0, 0.25);
        }
        .sidebar-stat {
            padding: 1rem .5rem;
            text-align: center;
        }
        .sidebar-stat + .sidebar-stat {
            border-left: 1px solid rgba(255, 255, 255, 0.08);
        }
        .sidebar-stat-num {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.3rem; font-weight: 700;
            color: white;
            line-height: 1.2;
        }
        .sidebar-stat-label {
            font-size: .65rem; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 1px;
            margin-top: .3rem;
            font-weight: 600;
        }

        /* Nav section */
        .sidebar-nav-label {
            font-size: .68rem; text-transform: uppercase;
            color: #334155; letter-spacing: 1.5px;
            padding: 1.1rem 1.25rem .4rem;
            font-weight: 700;
        }
        .nav-item {
            display: flex; align-items: center; gap: .85rem;
            padding: .85rem 1rem;
            color: var(--text-muted);
            cursor: pointer; font-size: .9rem; font-weight: 500;
            text-decoration: none;
            border: 1px solid transparent;
            background: none;
            width: 100%;
            border-radius: var(--radius-md);
            transition: var(--transition);
            position: relative;
        }
        .nav-item i { width: 18px; font-size: .95rem; text-align: center; flex-shrink: 0; transition: var(--transition); }
        .nav-item:hover {
            background: rgba(59,130,246,0.08);
            color: white;
            border-color: rgba(59,130,246,0.2);
        }
        .nav-item:hover i { transform: scale(1.1); color: var(--tech-blue); }
        .nav-item.active {
            background: rgba(59,130,246,0.14);
            color: white;
            border-color: rgba(59,130,246,0.35);
        }
        .nav-item.active i { color: var(--tech-blue); filter: drop-shadow(0 0 8px rgba(59,130,246,0.5)); }
        .nav-item .nav-badge {
            margin-left: auto;
            background: var(--tech-blue);
            color: white; font-size: .65rem; font-weight: 700;
            padding: .15rem .5rem; border-radius: var(--radius-full);
            min-width: 20px; text-align: center;
        }
        .nav-item .nav-badge-red {
            margin-left: auto;
            background: var(--tunisian-red);
            color: white; font-size: .65rem; font-weight: 700;
            padding: .15rem .5rem; border-radius: var(--radius-full);
        }

        /* Sidebar footer */
        .sidebar-footer {
            margin-top: auto;
            padding: 1rem;
            border-top: 1px solid var(--border-color);
        }
        .sidebar-user {
            display: flex; align-items: center; gap: .75rem;
        }
        .sidebar-user-av {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, rgba(37,99,235,0.3), rgba(231,0,19,0.2));
            border: 1px solid rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .85rem; flex-shrink: 0;
        }
        .sidebar-user-name { font-size: .85rem; font-weight: 600; color: white; }
        .sidebar-user-role { font-size: .7rem; color: var(--text-muted); }

        /* ══════════════════════════════════════
           MAIN AREA  (navbar + whatsapp layout)
        ══════════════════════════════════════ */
        .main-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow: hidden;
        }

        /* ══════════════════════════════════════
           TOP NAVBAR — FreelaSkill style
        ══════════════════════════════════════ */
        .navbar {
            background: rgba(2, 6, 23, 0.7);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            padding: 0 4rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            z-index: 100;
            height: 75px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        /* Logo in navbar */
        .navbar-logo {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            flex-shrink: 0;
            color: white;
        }
        .navbar-logo i { color: var(--tunisian-red); }
        .navbar-logo span { color: var(--tech-blue); }

        /* Center nav links */
        .navbar-center {
            display: flex; align-items: center; gap: 2rem;
            position: absolute; left: 50%; transform: translateX(-50%);
        }
        .nav-link-btn {
            background: transparent;
            border: none;
            color: var(--text-muted); font-size: .9rem;
            padding: .5rem 0; border-radius: 0;
            cursor: pointer; text-decoration: none;
            transition: var(--transition); font-family: inherit;
            display: flex; align-items: center; gap: .4rem;
            font-weight: 500; white-space: nowrap;
            position: relative;
        }
        .nav-link-btn:hover { color: white; }
        .nav-link-btn.active { color: white; }
        .nav-link-btn.active::after {
            content: '';
            position: absolute; bottom: -4px; left: 0; right: 0;
            height: 2px; background: var(--tech-blue);
            border-radius: 2px;
        }

        /* Right side controls */
        .navbar-right {
            display: flex; align-items: center; gap: 1rem; flex-shrink: 0;
        }
        .navbar-icon-btn {
            background: rgba(255,255,255,.03);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 1rem;
            transition: var(--transition); text-decoration: none;
            position: relative;
        }
        .navbar-icon-btn:hover { background: rgba(255,255,255,.08); color: white; }
        .navbar-icon-btn.active-page { background: rgba(37,99,235,.15); color: var(--tech-blue); border-color: rgba(37,99,235,.35); }
        .navbar-notif-dot {
            position: absolute; top: 6px; right: 6px;
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--tunisian-red);
            border: 2px solid var(--bg-dark);
        }
        .navbar-page-title {
            display: flex; align-items: center; gap: .5rem;
            background: rgba(37,99,235,.12);
            border: 1px solid rgba(37,99,235,.25);
            color: var(--tech-blue);
            padding: .45rem 1rem; border-radius: var(--radius-full);
            font-size: .85rem; font-weight: 600;
        }
        .navbar-user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, rgba(37,99,235,0.3), rgba(231,0,19,0.2));
            border: 1px solid rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .75rem; color: white;
            cursor: pointer;
            transition: var(--transition);
        }
        .navbar-user-avatar:hover { border-color: var(--tech-blue); }

        /* ══════════════════════════════════════
           WHATSAPP LAYOUT
        ══════════════════════════════════════ */
        .whatsapp-layout {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .conversations-sidebar {
            width: 340px;
            background: rgba(2, 6, 23, 0.5);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            flex-shrink: 0;
        }

        .sidebar-header {
            padding: 1rem 1rem .75rem;
            background: rgba(0,0,0,.2);
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-header h2 {
            font-size: 1rem; font-weight: 600; margin-bottom: .75rem;
            color: white;
        }

        .search-tri-form { display: flex; flex-direction: column; gap: .5rem; }

        .search-box {
            display: flex; align-items: center;
            background: rgba(255,255,255,.04);
            border-radius: var(--radius-sm);
            padding: .5rem .9rem;
            border: 1px solid rgba(255,255,255,.07);
            transition: border-color .2s;
        }
        .search-box:focus-within { border-color: rgba(37,99,235,.4); }
        .search-box i { color: var(--text-muted); margin-right: .5rem; font-size: .8rem; }
        .search-box input {
            flex: 1; background: transparent; border: none; outline: none;
            color: white; font-size: .85rem; font-family: inherit;
        }
        .search-box input::placeholder { color: var(--text-muted); }
        .search-box button {
            background: transparent; border: none; color: var(--text-muted);
            cursor: pointer; font-size: .75rem; transition: color .2s;
        }
        .search-box button:hover { color: var(--tech-blue); }

        .tri-row { display: flex; align-items: center; gap: .5rem; }
        .tri-label { font-size: .72rem; color: var(--text-muted); white-space: nowrap; }
        .tri-select {
            flex: 1; background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: var(--radius-sm);
            padding: .38rem .7rem; color: white;
            font-size: .78rem; outline: none; cursor: pointer;
            font-family: inherit; transition: border-color .2s;
        }
        .tri-select:focus { border-color: rgba(37,99,235,.4); }
        .search-result-info { font-size: .72rem; color: var(--tech-blue); padding: .3rem 0 0; }

        .conversation-list { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #1e2a3a #06091a; }
        .conversation-list::-webkit-scrollbar { width: 3px; }
        .conversation-list::-webkit-scrollbar-thumb { background: #1e2a3a; border-radius: 2px; }

        .conversation-item {
            display: flex; align-items: center; gap: .85rem;
            padding: .8rem 1rem;
            cursor: pointer; transition: background .2s;
            border-left: 3px solid transparent;
        }
        .conversation-item:hover { background: rgba(255,255,255,.03); }
        .conversation-item.active {
            background: rgba(37,99,235,.08);
            border-left-color: var(--tech-blue);
        }

        .conv-avatar {
            width: 44px; height: 44px; border-radius: 50%;
            background: linear-gradient(135deg, var(--tech-blue), #7c3aed);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1rem; flex-shrink: 0;
            position: relative;
        }
        .conv-avatar .unread-badge {
            position: absolute;
            top: -4px; right: -4px;
            background: var(--tunisian-red);
            color: white;
            border-radius: 999px;
            padding: .12rem .38rem;
            font-size: .6rem;
            font-weight: 700;
            border: 2px solid var(--bg-secondary);
            min-width: 16px;
            text-align: center;
            display: none;
        }
        .conv-info { flex: 1; min-width: 0; }
        .conv-name {
            font-weight: 600; font-size: .88rem;
            margin-bottom: .15rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .conv-name span { font-size: .72rem; color: var(--text-muted); font-weight: normal; }
        .conv-last-message { font-size: .75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .conv-status { font-size: .65rem; color: var(--tech-green); margin-top: .15rem; }
        .conv-status.offline { color: var(--text-muted); }
        .conv-msg-count { font-size: .62rem; color: #475569; margin-top: .1rem; }

        .suggestion-card {
            background: rgba(37,99,235,.08);
            border: 1px solid rgba(37,99,235,.25);
            border-radius: var(--radius-md);
            padding: .75rem 1rem; margin: .5rem;
            display: flex; align-items: center; justify-content: space-between;
            cursor: pointer; transition: background .2s;
        }
        .suggestion-card:hover { background: rgba(37,99,235,.14); }
        .suggestion-info h4 { font-size: .85rem; color: var(--tech-blue); margin-bottom: .15rem; }
        .suggestion-info p  { font-size: .7rem; color: var(--text-muted); }
        .suggestion-btn {
            background: var(--tech-blue); color: white; border: none;
            padding: .4rem .8rem; border-radius: var(--radius-sm);
            cursor: pointer; font-size: .72rem; font-family: inherit;
        }

        /* ── Chat area ── */
        .chat-area {
            flex: 1; display: flex; flex-direction: column;
            background: transparent; position: relative;
        }
        .chat-header {
            background: rgba(13,17,23,.9);
            padding: .8rem 1.5rem;
            display: flex; align-items: center; gap: 1rem;
            border-bottom: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
        }
        .chat-header-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, var(--tech-blue), #7c3aed);
            display: flex; align-items: center; justify-content: center; font-weight: 700;
        }
        .chat-header-info { flex: 1; }
        .chat-header-name { font-weight: 600; font-size: .95rem; }
        .chat-header-status { font-size: .72rem; color: var(--tech-green); }
        .chat-header-status.offline { color: var(--text-muted); }
        .chat-header-actions { display: flex; gap: .5rem; }
        .chat-header-actions button {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            color: var(--text-muted); cursor: pointer;
            width: 34px; height: 34px; border-radius: 50%;
            font-size: .85rem; transition: all .2s;
            display: flex; align-items: center; justify-content: center;
        }
        .chat-header-actions button:hover {
            background: rgba(37,99,235,.15); color: var(--tech-blue);
            border-color: rgba(37,99,235,.3);
        }

        .messages-container {
            flex: 1; overflow-y: auto; padding: 1.5rem;
            display: flex; flex-direction: column; gap: .8rem;
            scrollbar-width: thin; scrollbar-color: #1e2a3a var(--bg-dark);
        }
        .messages-container::-webkit-scrollbar { width: 4px; }
        .messages-container::-webkit-scrollbar-thumb { background: #1e2a3a; border-radius: 2px; }

        .message-row { display: flex; width: 100%; }
        .message-row.sent     { justify-content: flex-end; }
        .message-row.received { justify-content: flex-start; }
        .message-wrapper { display: flex; align-items: flex-end; gap: .3rem; max-width: 70%; }
        .sent .message-wrapper { flex-direction: row-reverse; }

        .message-bubble {
            padding: .6rem 1rem; border-radius: 18px;
            font-size: .87rem; line-height: 1.5; word-wrap: break-word;
        }
        .sent .message-bubble {
            background: rgba(37,99,235,.3);
            border: 1px solid rgba(37,99,235,.35);
            color: white; border-bottom-right-radius: 4px;
        }
        .received .message-bubble {
            background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.08);
            color: rgba(255,255,255,.88); border-bottom-left-radius: 4px;
        }
        .message-time { font-size: .6rem; color: rgba(255,255,255,.3); margin-top: .25rem; text-align: right; }
        .edit-timer   { font-size: .6rem; color: var(--tech-yellow); margin-left: .5rem; }

        .message-menu { opacity: 0; transition: opacity .2s; }
        .message-row:hover .message-menu { opacity: 1; }
        .menu-btn {
            background: transparent; border: none; color: var(--text-muted);
            cursor: pointer; padding: .3rem; border-radius: 50%; font-size: .8rem;
        }
        .menu-btn:hover { background: rgba(255,255,255,.06); color: var(--tech-blue); }

        .dropdown {
            position: fixed; background: #06091a;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: var(--radius-md);
            box-shadow: 0 8px 24px rgba(0,0,0,.5);
            z-index: 1000; min-width: 160px; overflow: hidden;
        }
        .dropdown button {
            width: 100%; padding: .6rem 1rem; background: none; border: none;
            color: rgba(255,255,255,.8); text-align: left; cursor: pointer;
            display: flex; align-items: center; gap: .6rem; font-size: .8rem;
            font-family: inherit; transition: background .15s;
        }
        .dropdown button:hover { background: rgba(255,255,255,.05); }
        .dropdown .delete   { color: var(--tunisian-red); }
        .dropdown .edit     { color: var(--tech-blue); }
        .dropdown .report   { color: var(--tech-yellow); }
        .dropdown .disabled { color: #475569; cursor: not-allowed; }

        .edit-textarea {
            width: 100%; background: rgba(255,255,255,.06);
            border: 1px solid rgba(37,99,235,.4);
            border-radius: var(--radius-sm); padding: .5rem;
            color: white; font-family: inherit; font-size: .85rem;
            resize: none; outline: none;
        }
        .edit-actions { display: flex; gap: .5rem; margin-top: .5rem; justify-content: flex-end; }
        .edit-actions button {
            padding: .3rem .8rem; border-radius: var(--radius-sm); border: none; cursor: pointer; font-size: .7rem;
        }
        .save-edit   { background: var(--tech-blue); color: white; }
        .cancel-edit { background: rgba(255,255,255,.08); color: var(--text-muted); }

        .empty-chat {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; height: 100%;
            color: var(--text-muted); text-align: center; gap: 1rem;
        }
        .empty-chat i { font-size: 4rem; opacity: .2; }

        .no-conversation-selected {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; height: 100%;
            color: var(--text-muted); gap: 1rem; text-align: center;
        }
        .no-conversation-selected i { font-size: 3.5rem; opacity: .15; }

        /* Input zone */
        .input-zone {
            padding: .9rem 1.5rem 1.1rem;
            background: rgba(13,17,23,.92);
            border-top: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
        }
        .input-row-wrap { position: relative; }
        .validation-tooltip {
            display: none; position: absolute;
            bottom: calc(100% + 10px); left: 14px;
            background: #1a2234;
            color: #fff; font-size: .78rem;
            padding: 6px 12px 6px 9px; border-radius: 6px;
            border: 1px solid rgba(37,99,235,.3);
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(0,0,0,.4);
            align-items: center; gap: 7px; z-index: 999; pointer-events: none;
        }
        .validation-tooltip::after {
            content: ''; position: absolute; top: 100%; left: 20px;
            border: 6px solid transparent; border-top-color: rgba(37,99,235,.3);
        }
        .validation-tooltip.show { display: flex; }
        .validation-tooltip i { color: var(--tech-yellow); font-size: .85rem; flex-shrink: 0; }

        .input-row {
            display: flex; align-items: flex-end; gap: .75rem;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: var(--radius-full);
            padding: .55rem 1rem; transition: border-color .2s, box-shadow .2s;
        }
        .input-row.focused {
            border-color: rgba(37,99,235,.45);
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }
        .input-row.error {
            border-color: rgba(231,0,19,.45);
            box-shadow: 0 0 0 3px rgba(231,0,19,.1);
        }
        #msgInput {
            flex: 1; background: transparent; border: none; outline: none;
            color: white; font-size: .9rem; resize: none;
            font-family: inherit; max-height: 100px;
        }
        #msgInput::placeholder { color: var(--text-muted); }
        .send-btn {
            background: var(--tech-blue); border: none; color: white;
            width: 34px; height: 34px; border-radius: 50%;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all .2s; box-shadow: 0 0 12px rgba(37,99,235,.3);
            font-size: .8rem; flex-shrink: 0;
        }
        .send-btn:hover { background: #1d4ed8; transform: scale(1.05); }
        .send-btn:active { transform: scale(.96); }
        .send-btn:disabled { background: rgba(255,255,255,.08); box-shadow: none; cursor: not-allowed; }
        
        .input-btn {
            background: transparent; border: none; color: var(--text-muted);
            width: 30px; height: 30px; border-radius: 50%;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all .2s; font-size: .85rem; flex-shrink: 0;
        }
        .input-btn:hover { background: rgba(37,99,235,.15); color: var(--tech-blue); }
        .input-btn:active { transform: scale(.95); }
        
        .char-counter { font-size: .67rem; color: var(--text-muted); text-align: right; margin-top: .3rem; }
        
        .toast-notification {
            position: fixed; top: 20px; right: 20px;
            background: rgba(13,17,23,.95); border: 1px solid rgba(37,99,235,.3);
            border-radius: 12px; padding: 1rem 1.2rem;
            color: white; font-size: .85rem;
            box-shadow: 0 8px 24px rgba(0,0,0,.5);
            backdrop-filter: blur(10px);
            animation: slideInRight .3s ease-out;
            z-index: 2000;
        }
        @keyframes slideInRight {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            to { transform: translateX(400px); opacity: 0; }
        }

        .empty-search {
            text-align: center; padding: 2rem 1rem;
            color: var(--text-muted); font-size: .85rem;
        }

        /* Modal */
        #groupeModal .modal-inner {
            background: #06091a;
            border: 1px solid rgba(255,255,255,.09);
            border-radius: var(--radius-lg);
        }

        @media (max-width: 1100px) {
            .app-sidebar { width: 220px; }
        }
        @media (max-width: 860px) {
            .app-sidebar { display: none; }
            .conversations-sidebar { width: 300px; }
        }
        @media (max-width: 640px) {
            .conversations-sidebar { width: 100%; position: absolute; z-index: 10; height: calc(100vh - 56px); }
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════
     FULL-WIDTH NAVBAR (top of page)
════════════════════════════════════════ -->
<nav class="navbar">

    <!-- Logo (left) -->
    <a href="/freelaskill/views/frontoffice/home.php" class="navbar-logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </a>

    <!-- Center nav links -->
    <div class="navbar-center">
        <span style="color:var(--text-muted);cursor:default;" class="nav-link-btn">Accueil</span>
        <a href="/freelaskill/views/frontoffice/home.php" class="nav-link-btn">Marketplace</a>
        <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'client'): ?>
            <a href="/freelaskill/views/frontoffice/missions.php" class="nav-link-btn">Missions</a>
        <?php else: ?>
            <a href="/freelaskill/views/frontoffice/freelancer_home.php" class="nav-link-btn">Freelancers</a>
        <?php endif; ?>
        <a href="messagerie_index.php?page=conversations" class="nav-link-btn active">Messagerie</a>
        <a href="/freelaskill/views/frontoffice/profile.php" class="nav-link-btn">Mon Profil</a>
    </div>

    <!-- Right controls -->
    <div class="navbar-right">
        <!-- Dark mode toggle -->
        <button class="navbar-icon-btn" title="Thème" onclick="toggleTheme(this)">
            <i class="fa-solid fa-moon"></i>
        </button>
        <!-- Notifications -->
        <button class="navbar-icon-btn" title="Notifications">
            <i class="fa-regular fa-bell"></i>
            <span class="navbar-notif-dot"></span>
        </button>
        <!-- Messagerie page badge -->
        <div class="navbar-page-title">
            <i class="fa-regular fa-comments"></i>
            Messagerie
        </div>
        <!-- User avatar -->
        <?php 
        $initial = strtoupper(substr($_SESSION['user_prenom'] ?? $_SESSION['user_nom'] ?? 'U', 0, 1));
        $colors = ['#f44336','#e91e63','#9c27b0','#673ab7','#3f51b5','#2196f3','#03a9f4','#00bcd4','#009688','#4caf50','#8bc34a','#cddc39','#ffc107','#ff9800','#ff5722'];
        $bgColor = $colors[ord($initial) % count($colors)];
        ?>
        <div class="navbar-user-avatar" title="Mon profil" style="background: <?= $bgColor ?>; color: white; border: none; font-weight: bold; font-size: 1rem;">
            <?= $initial ?>
        </div>
    </div>
</nav>

<!-- ══════════════════════════════════════
     PAGE LAYOUT (sidebar + main)
════════════════════════════════════════ -->
<div class="page-layout">

<!-- ══════════════════════════════════════
     LEFT APP SIDEBAR
════════════════════════════════════════ -->
<aside class="app-sidebar">
    <div class="sidebar-content">
        <!-- Module Card -->
        <div class="sidebar-module-card">
            <div class="sidebar-module-header">
                <div class="sidebar-module-icon">
                    <i class="fa-regular fa-comments"></i>
                </div>
                <div class="sidebar-module-title">Messagerie</div>
                <div class="sidebar-module-sub">FreelaSkill Tunisia</div>
            </div>
            
            <div class="sidebar-stats">
                <div class="sidebar-stat">
                    <div class="sidebar-stat-num"><?= $totalConvs ?></div>
                    <div class="sidebar-stat-label">DISCUSSIONS</div>
                </div>
                <div class="sidebar-stat">
                    <div class="sidebar-stat-num"><?= $totalMsgs ?></div>
                    <div class="sidebar-stat-label">MESSAGES</div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="sidebar-nav-label">NAVIGATION</div>
        
        <a href="messagerie_index.php?page=conversations" class="nav-item active">
            <i class="fa-regular fa-comments"></i>
            Toutes les discussions
        </a>
        
        <a href="#" class="nav-item" onclick="document.getElementById('searchInput').value = ''; document.querySelector('.tri-select').value = 'date_desc'; document.getElementById('searchTriForm').submit(); return false;">
            <i class="fa-solid fa-circle" style="color: var(--tech-green); font-size: 8px;"></i>
            Conversations actives
        </a>
        
        <a href="#" class="nav-item" onclick="openGroupeModal(); return false;">
            <i class="fa-solid fa-people-group"></i>
            Créer un groupe
        </a>

        <!-- Outils -->
        <div class="sidebar-nav-label" style="margin-top: 1rem;">OUTILS</div>
        
        <a href="/freelaskill/views/frontoffice/home.php" class="nav-item">
            <i class="fa-solid fa-house"></i>
            Accueil
        </a>
        
        <a href="/freelaskill/views/frontoffice/missions.php" class="nav-item">
            <i class="fa-solid fa-briefcase"></i>
            Missions
        </a>
    </div>
</aside>

<!-- ══════════════════════════════════════
     MAIN AREA
════════════════════════════════════════ -->
<div class="main-area">

    <!-- WhatsApp layout -->
    <div class="whatsapp-layout">

        <!-- Conversations list pane -->
        <div class="conversations-sidebar" id="conversationsSidebar">
            <div class="sidebar-header">
                <div style="display:flex;justify-content:flex-end;align-items:center;margin-bottom:.75rem;">
                    <button type="button" onclick="openGroupeModal()" title="Créer un groupe"
                        style="background:rgba(124,58,237,.2);border:1px solid rgba(124,58,237,.35);color:#a78bfa;width:30px;height:30px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.85rem;">
                        <i class="fa-solid fa-people-group"></i>
                    </button>
                </div>

                <form class="search-tri-form" method="GET" action="/freelaskill/messagerie_index.php" id="searchTriForm">
                    <input type="hidden" name="page" value="conversations">
                    <div class="search-box">
                        <button type="button" onclick="checkAndShowSuggestion()"><i class="fa-solid fa-magnifying-glass"></i></button>
                        <input type="text" name="search" id="searchInput"
                               placeholder="Rechercher..."
                               value="<?= $currentSearch ?>" autocomplete="off"
                               onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();checkAndShowSuggestion();}">
                        <?php if ($currentSearch !== ''): ?>
                            <button type="button" onclick="clearSearch()"><i class="fa-solid fa-xmark"></i></button>
                        <?php endif; ?>
                    </div>
                    <div class="tri-row">
                        <span class="tri-label"><i class="fa-solid fa-arrow-up-wide-short"></i> Tri :</span>
                        <select class="tri-select" name="tri" onchange="document.getElementById('searchTriForm').submit()">
                            <option value="date_desc" <?= $currentTri === 'date_desc' ? 'selected' : '' ?>>Plus récent</option>
                            <option value="date_asc"  <?= $currentTri === 'date_asc'  ? 'selected' : '' ?>>Plus ancien</option>
                            <option value="messages"  <?= $currentTri === 'messages'  ? 'selected' : '' ?>>Plus de messages</option>
                        </select>
                    </div>
                    <?php if ($currentSearch !== ''): ?>
                        <div class="search-result-info">
                            <i class="fa-solid fa-circle-info"></i>
                            <?= count($conversations) ?> résultat(s) pour "<?= $currentSearch ?>"
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <div class="conversation-list" id="conversationList">
                <?php if (empty($conversations)): ?>
                    <div class="empty-search">
                        <?php if ($currentSearch !== ''): ?>
                            <i class="fa-solid fa-magnifying-glass" style="font-size:1.5rem;margin-bottom:.5rem;opacity:.3;display:block;"></i>
                            <p>Aucun résultat pour "<?= $currentSearch ?>"</p>
                        <?php else: ?>
                            <i class="fa-regular fa-comment-dots" style="font-size:2rem;margin-bottom:.5rem;opacity:.3;display:block;"></i>
                            <p>Aucune conversation</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv):
                        $otherUser = ($conv['id_user1'] == $currentUserId) ? $conv['id_user2'] : $conv['id_user1'];
                        // Decode titre if it's JSON (group conversations store metadata as JSON)
                        $titreDecoded = !empty($conv['titre']) ? json_decode($conv['titre'], true) : null;
                        if (is_array($titreDecoded)) {
                            $convName = !empty($titreDecoded['nom']) ? $titreDecoded['nom'] : 'Groupe';
                            $isGroupe = !empty($titreDecoded['groupe']);
                        } else {
                            $convName = !empty($conv['titre']) ? $conv['titre'] : 'Utilisateur #' . $otherUser;
                            $isGroupe = false;
                        }
                    ?>
                        <div class="conversation-item"
                             data-id="<?= $conv['id_conversation'] ?>"
                             data-user1="<?= $conv['id_user1'] ?>"
                             data-user2="<?= $conv['id_user2'] ?>"
                             data-name="<?= htmlspecialchars($convName) ?>"
                             onclick="loadConversation(<?= $conv['id_conversation'] ?>, this)">
                        <?php 
                        $initial = strtoupper(substr($convName, 0, 1));
                        $colors = ['#f44336','#e91e63','#9c27b0','#673ab7','#3f51b5','#2196f3','#03a9f4','#00bcd4','#009688','#4caf50','#8bc34a','#cddc39','#ffc107','#ff9800','#ff5722'];
                        $bgColor = $colors[ord($initial) % count($colors)];
                        ?>
                        <div class="conv-avatar" style="<?= $isGroupe ? 'background:linear-gradient(135deg,#7c3aed,#a855f7)' : 'background:' . $bgColor . '; border:none; color:white; font-weight:700;' ?>">
                            <?= $isGroupe ? '<i class="fa-solid fa-people-group" style="font-size:.9rem;"></i>' : $initial ?>
                            <span class="unread-badge" id="unread-<?= $conv['id_conversation'] ?>"></span>
                        </div>
                            <div class="conv-info">
                                <div class="conv-name">
                                    <?= htmlspecialchars($convName) ?>
                                    <span><?= date('d/m', strtotime($conv['date_creation'])) ?></span>
                                </div>
                                <div class="conv-last-message">
                                    <?php
                                    $dm = $conv['dernier_message'] ?? '';
                                    $dm_decoded = json_decode($dm, true);
                                    if (is_array($dm_decoded)) {
                                        if (!empty($dm_decoded['ephemeral'])) echo '🔥 Message éphémère';
                                        elseif (isset($dm_decoded['type']) && $dm_decoded['type'] === 'file') echo '📎 ' . htmlspecialchars($dm_decoded['name'] ?? 'Fichier');
                                        else echo htmlspecialchars(substr($dm, 0, 38));
                                    } else {
                                        echo !empty($dm) ? htmlspecialchars(substr($dm, 0, 38)) : 'Aucun message';
                                    }
                                    ?>
                                </div>
                                <div class="conv-status <?= $conv['statut'] === 'active' ? '' : 'offline' ?>">
                                    <?= $conv['statut'] === 'active' ? '● Actif' : '○ ' . $conv['statut'] ?>
                                </div>
                                <div class="conv-msg-count">
                                    <i class="fa-regular fa-comment"></i> <?= (int)($conv['total_messages'] ?? 0) ?> msg

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="suggestionContainer" style="padding:.5rem;"></div>
        </div>

        <!-- Chat area -->
        <div class="chat-area" id="chatArea">
            <div class="no-conversation-selected">
                <i class="fa-regular fa-comment-dots"></i>
                <p style="font-weight:600;">Sélectionnez une conversation</p>
                <p style="font-size:.82rem;color:var(--text-muted);">Choisissez une discussion dans la liste</p>
            </div>
        </div>
    </div><!-- /whatsapp-layout -->
</div><!-- /main-area -->


<!-- ══════════════════════════════════════
     MODAL : Créer un groupe
════════════════════════════════════════ -->
<div id="groupeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:2000;align-items:center;justify-content:center;">
    <div class="modal-inner" style="background:#06091a;border:1px solid rgba(255,255,255,.09);border-radius:16px;width:380px;max-width:92vw;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="display:flex;align-items:center;gap:.6rem;font-size:1rem;">
                <i class="fa-solid fa-people-group" style="color:#a78bfa;"></i> Nouveau groupe
            </h3>
            <button type="button" onclick="closeGroupeModal()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.1rem;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div style="padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.05);">
            <label style="font-size:.7rem;color:#a78bfa;text-transform:uppercase;letter-spacing:.8px;font-weight:600;display:block;margin-bottom:.4rem;"><i class="fa-solid fa-tag"></i> Nom du groupe *</label>
            <input id="groupeNomInput" type="text" placeholder="ex : Équipe projet, Amis..." autocomplete="off" data-role="groupe-nom" oninput="_groupeNomValue = this.value"
                   style="width:100%;background:rgba(255,255,255,.05);border:2px solid rgba(124,58,237,.6);border-radius:8px;padding:.6rem .9rem;color:white;font-size:.9rem;outline:none;font-family:inherit;"
                   onkeydown="if(event.key==='Enter'){event.preventDefault();submitGroupe();}">
            <p id="groupeError" style="color:var(--tunisian-red);font-size:.75rem;margin-top:.4rem;min-height:.9em;"></p>
        </div>
        <div style="padding:.65rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.05);">
            <div style="display:flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:.42rem .8rem;">
                <i class="fa-solid fa-magnifying-glass" style="color:var(--text-muted);font-size:.8rem;"></i>
                <input id="groupeSearch" type="text" placeholder="Rechercher un contact..."
                       style="background:none;border:none;outline:none;color:white;font-size:.83rem;font-family:inherit;flex:1;" oninput="filterGroupeContacts()">
            </div>
        </div>
        <div id="groupeChips" style="padding:.55rem 1.5rem;display:flex;flex-wrap:wrap;gap:.4rem;min-height:34px;border-bottom:1px solid rgba(255,255,255,.04);"></div>
        <div id="groupeContactList" style="flex:1;overflow-y:auto;padding:.5rem 0;scrollbar-width:thin;scrollbar-color:#1e2a3a #06091a;"></div>
        <div style="padding:1rem 1.5rem;border-top:1px solid rgba(255,255,255,.05);display:flex;justify-content:space-between;align-items:center;">
            <span id="groupeCount" style="font-size:.77rem;color:var(--text-muted);">0 membre(s) sélectionné(s)</span>
            <div style="display:flex;gap:.6rem;">
                <button type="button" onclick="closeGroupeModal()" style="background:rgba(255,255,255,.06);border:none;color:var(--text-muted);padding:.5rem 1rem;border-radius:8px;cursor:pointer;font-size:.83rem;font-family:inherit;">Annuler</button>
                <button type="button" onclick="submitGroupe()" style="background:rgba(124,58,237,.85);border:none;color:white;padding:.5rem 1.1rem;border-radius:8px;cursor:pointer;font-weight:600;font-size:.83rem;font-family:inherit;"><i class="fa-solid fa-check"></i> Créer</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentConversationId = null;
let currentUserId = <?= $currentUserId ?>;
let messages = [];
let activeDropdown = null;
let unreadCounts = {};
let lastNotificationTimestamps = {};

function clearSearch() {
    window.location.href = 'messagerie_index.php?page=conversations&tri=<?= $currentTri ?>';
}

function loadConversation(id, element) {
    document.querySelectorAll('.conversation-item').forEach(item => item.classList.remove('active'));
    element.classList.add('active');
    currentConversationId = id;
    const badge = document.getElementById('unread-' + id);
    if (badge) {
        badge.style.display = 'none';
        unreadCounts[id] = 0;
    }
    fetch('messagerie_get_messages.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.error) { console.error(data.error); return; }
            messages = data.messages || [];
            renderChatArea(data);
        })
        .catch(err => console.error('Erreur:', err));
}

function renderChatArea(data) {
    const chatArea = document.getElementById('chatArea');
    const conversation = data.conversation;
    const messagesList = data.messages || [];
    if (!conversation) {
        chatArea.innerHTML = `<div class="no-conversation-selected"><i class="fa-regular fa-comment-dots"></i><p>Conversation introuvable</p></div>`;
        return;
    }
    const otherUser = (conversation.id_user1 == currentUserId) ? conversation.id_user2 : conversation.id_user1;
    const convName  = conversation.titre || 'Utilisateur #' + otherUser;
    let messagesHtml = '';
    let currentDate = '';
    messagesList.forEach(msg => {
        const msgDate = new Date(msg.date_envoi).toLocaleDateString('fr-FR');
        const msgTime = new Date(msg.date_envoi).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
        const isSent  = msg.id_expediteur == currentUserId;
        const seconds_ago  = parseInt(msg.seconds_ago) || 999;
        const can_edit     = isSent && seconds_ago <= 60;
        const seconds_left = can_edit ? Math.max(0, 60 - seconds_ago) : 0;
        
        if (currentDate !== msgDate) {
            messagesHtml += `<div style="text-align:center;margin:.5rem 0;"><span style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);padding:.2rem .9rem;border-radius:999px;font-size:.68rem;color:var(--text-muted);">${msgDate}</span></div>`;
            currentDate = msgDate;
        }
        
        // Detect file messages
        let contentHtml = '';
        let isFileMsg = false;
        let parsedMsg = null;
        try {
            parsedMsg = JSON.parse(msg.contenu);

            // ── Ephemeral messages ──
            if (parsedMsg && parsedMsg.ephemeral === true) {
                const text = parsedMsg.text || '';
                const exp  = parsedMsg.expiration || '';
                const createdAt = parsedMsg.created_at || 0;
                const expLabel = exp === '10s' ? '10 secondes' : exp === '1h' ? '1 heure' : exp === '24h' ? '24 heures' : exp === 'read' ? 'après lecture' : exp;

                // Compute time left
                let timerHtml = '';
                if (exp === '10s' || exp === '1h' || exp === '24h') {
                    const seconds = exp === '10s' ? 10 : exp === '1h' ? 3600 : 86400;
                    const elapsed = Math.floor(Date.now() / 1000) - createdAt;
                    const left    = Math.max(0, seconds - elapsed);
                    const mm = String(Math.floor(left / 60)).padStart(2,'0');
                    const ss = String(left % 60).padStart(2,'0');
                    timerHtml = `<span style="font-size:11px;color:#f59e0b;margin-left:8px;"><i class="fa-regular fa-clock"></i> ${mm}:${ss}</span>`;
                }

                contentHtml = `<div style="display:flex;align-items:flex-start;gap:8px;">
                    <i class="fa-solid fa-fire" style="color:#f59e0b;font-size:13px;margin-top:2px;flex-shrink:0;"></i>
                    <div>
                        <div>${escapeHtml(text)}</div>
                        <div style="font-size:10px;color:#f59e0b;margin-top:3px;">⏳ Expire ${expLabel}${timerHtml}</div>
                    </div>
                </div>`;
                isFileMsg = true; // reuse flag to skip edit in menu

            } else if (parsedMsg && parsedMsg.type === 'file') {
                isFileMsg = true;
                const fname = escapeHtml(parsedMsg.name || 'Fichier');
                const furl  = escapeHtml(parsedMsg.url  || '');
                const fsize = parsedMsg.size ? (Math.round(parsedMsg.size / 1024 * 10) / 10) + ' KB' : '';
                const ext   = (parsedMsg.name || '').split('.').pop().toLowerCase();
                const isImg = ['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext);
                const isPdf = (ext === 'pdf');
                if (isImg) {
                    contentHtml = `<div class="file-msg">
                        <img src="${furl}" alt="${fname}" style="max-width:220px;max-height:180px;border-radius:10px;display:block;cursor:pointer;" onclick="window.open('${furl}','_blank')">
                        <div style="font-size:11px;color:#aaa;margin-top:4px;">${fname}${fsize ? ' (' + fsize + ')' : ''}</div>
                    </div>`;
                } else if (isPdf) {
                    contentHtml = `<div class="file-msg" onclick="window.open('${furl}','_blank')" style="cursor:pointer;display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.07);padding:10px 14px;border-radius:12px;">
                        <i class="fa-solid fa-file-pdf" style="font-size:26px;color:#e70013;flex-shrink:0;"></i>
                        <div><div style="font-size:13px;font-weight:600;">${fname}</div>${fsize ? `<div style="font-size:11px;color:#aaa;">${fsize}</div>` : ''}</div>
                        <i class="fa-solid fa-download" style="margin-left:auto;color:#aaa;font-size:14px;"></i>
                    </div>`;
                } else {
                    contentHtml = `<div class="file-msg" onclick="window.open('${furl}','_blank')" style="cursor:pointer;display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.07);padding:10px 14px;border-radius:12px;">
                        <i class="fa-solid fa-file" style="font-size:26px;color:#2563eb;flex-shrink:0;"></i>
                        <div><div style="font-size:13px;font-weight:600;">${fname}</div>${fsize ? `<div style="font-size:11px;color:#aaa;">${fsize}</div>` : ''}</div>
                        <i class="fa-solid fa-download" style="margin-left:auto;color:#aaa;font-size:14px;"></i>
                    </div>`;
                }
            } else {
                contentHtml = escapeHtml(msg.contenu);
            }
        } catch(e) {
            contentHtml = escapeHtml(msg.contenu);
        }

        messagesHtml += `
            <div class="message-row ${isSent ? 'sent' : 'received'}" data-message-id="${msg.id_message}" data-seconds-left="${seconds_left}">
                <div class="message-wrapper">
                    <div class="message-bubble" id="bubble-${msg.id_message}">
                        <div class="message-content" id="content-${msg.id_message}">
                            ${contentHtml}
                        </div>
                        <div class="message-actions" style="margin-top:.4rem;padding-top:.4rem;border-top:1px solid rgba(255,255,255,.1);display:flex;gap:.3rem;flex-wrap:wrap;font-size:.7rem;">
                            <button class="msg-btn-translate" onclick="translateMessage(event, ${msg.id_message}, 'fr')" style="background:none;border:none;color:var(--tech-blue);cursor:pointer;padding:0;display:flex;align-items:center;gap:.2rem;transition:opacity .2s;" title="Traduire en Français">🇫🇷 FR</button>
                            <button class="msg-btn-translate" onclick="translateMessage(event, ${msg.id_message}, 'en')" style="background:none;border:none;color:var(--tech-blue);cursor:pointer;padding:0;display:flex;align-items:center;gap:.2rem;transition:opacity .2s;" title="Traduire en Anglais">🇬🇧 EN</button>
                            <button class="msg-btn-translate" onclick="translateMessage(event, ${msg.id_message}, 'ar')" style="background:none;border:none;color:var(--tech-blue);cursor:pointer;padding:0;display:flex;align-items:center;gap:.2rem;transition:opacity .2s;" title="Traduire en Arabe">🇹🇳 AR</button>
                        </div>
                        <div class="message-time">
                            ${msgTime}
                            ${can_edit ? `<span class="edit-timer"><i class="fa-regular fa-clock"></i> ${seconds_left}s</span>` : ''}
                        </div>
                    </div>
                    ${isSent ? `<div class="message-menu"><button class="menu-btn" onclick="showMessageMenu(event, ${msg.id_message}, ${isFileMsg ? false : can_edit}, '${isFileMsg ? '' : escapeHtml(msg.contenu).replace(/'/g, "\\'")}')"><i class="fa-solid fa-ellipsis-h"></i></button></div>` : ''}
                </div>
            </div>`;
    });
    if (messagesList.length === 0) {
        messagesHtml = `<div class="empty-chat"><i class="fa-regular fa-comment-dots"></i><p>Aucun message</p><p style="font-size:.75rem;">Envoyez le premier message !</p></div>`;
    }
    const initial = convName.charAt(0).toUpperCase();
    const colors  = ['#f44336','#e91e63','#9c27b0','#673ab7','#3f51b5','#2196f3','#03a9f4','#00bcd4','#009688','#4caf50','#8bc34a','#cddc39','#ffc107','#ff9800','#ff5722'];
    const bgColor = colors[initial.charCodeAt(0) % colors.length];
    
    chatArea.innerHTML = `
        <div class="chat-header">
            <div class="chat-header-avatar" style="background: ${bgColor}; color: white; border: none;">${initial}</div>
            <div class="chat-header-info">
                <div class="chat-header-name">${escapeHtml(convName)}</div>
                <div class="chat-header-status ${conversation.statut === 'active' ? '' : 'offline'}">${conversation.statut === 'active' ? '● En ligne' : '○ ' + conversation.statut}</div>
            </div>
            <div class="chat-header-actions">
                <button onclick="renameConversation()" title="Renommer"><i class="fa-solid fa-pen"></i></button>
                <button onclick="deleteCurrentConversation()" title="Supprimer"><i class="fa-regular fa-trash-can"></i></button>
                <button onclick="openGitPanel()" title="Git — Historique &amp; versions" style="background:rgba(188,140,255,.12);border-color:rgba(188,140,255,.3);color:#bc8cff;"><i class="fa-brands fa-git-alt"></i></button>
            </div>
        </div>
        <div class="messages-container" id="messagesContainer">${messagesHtml}</div>
        <div class="input-zone">
            <div class="input-row-wrap">
                <div class="validation-tooltip" id="validationMsg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span id="validationText"></span>
                </div>
                <div class="input-row" id="inputRow">
                    <button type="button" class="input-btn" id="fileBtn" onclick="document.getElementById('fileInput').click()" title="Joindre un fichier">
                        <i class="fa-solid fa-paperclip"></i>
                    </button>
                    <input type="file" id="fileInput" style="display:none;" onchange="handleFileUpload(event)">
                    <textarea id="msgInput" rows="1" placeholder="Écrivez un message..." maxlength="150"></textarea>
                    <button type="button" class="input-btn" id="ephemeralBtn" onclick="showEphemeralOptions()" title="Message éphémère">
                        <i class="fa-solid fa-hourglass-end"></i>
                    </button>
                    <button type="button" class="send-btn" id="sendBtn" onclick="sendMessage()">
                        <i class="fa-regular fa-paper-plane"></i>
                    </button>
                </div>
            </div>
            <div class="char-counter" id="charCounter">0 / 150</div>
        </div>
        
        <!-- Ephemeral options dropdown -->
        <div id="ephemeralMenu" style="display:none;position:fixed;background:#06091a;border:1px solid rgba(255,255,255,.1);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.5);z-index:1000;min-width:180px;overflow:hidden;">
            <button onclick="selectEphemeral('10s')" style="width:100%;padding:.6rem 1rem;background:none;border:none;color:rgba(255,255,255,.8);text-align:left;cursor:pointer;display:flex;align-items:center;gap:.6rem;font-size:.8rem;font-family:inherit;transition:background .15s;">
                <i class="fa-solid fa-bolt"></i> 10 secondes
            </button>
            <button onclick="selectEphemeral('1h')" style="width:100%;padding:.6rem 1rem;background:none;border:none;color:rgba(255,255,255,.8);text-align:left;cursor:pointer;display:flex;align-items:center;gap:.6rem;font-size:.8rem;font-family:inherit;transition:background .15s;border-top:1px solid rgba(255,255,255,.05);">
                <i class="fa-solid fa-clock"></i> 1 heure
            </button>
            <button onclick="selectEphemeral('24h')" style="width:100%;padding:.6rem 1rem;background:none;border:none;color:rgba(255,255,255,.8);text-align:left;cursor:pointer;display:flex;align-items:center;gap:.6rem;font-size:.8rem;font-family:inherit;transition:background .15s;border-top:1px solid rgba(255,255,255,.05);">
                <i class="fa-solid fa-hourglass"></i> 24 heures
            </button>
            <button onclick="selectEphemeral('read')" style="width:100%;padding:.6rem 1rem;background:none;border:none;color:rgba(255,255,255,.8);text-align:left;cursor:pointer;display:flex;align-items:center;gap:.6rem;font-size:.8rem;font-family:inherit;transition:background .15s;border-top:1px solid rgba(255,255,255,.05);">
                <i class="fa-solid fa-eye"></i> Après lecture
            </button>
        </div>`;
    setTimeout(() => {
        const c = document.getElementById('messagesContainer');
        if (c) c.scrollTop = c.scrollHeight;
    }, 80);
    const msgInput = document.getElementById('msgInput');
    const charCounter = document.getElementById('charCounter');
    const inputRow = document.getElementById('inputRow');
    if (msgInput) {
        msgInput.addEventListener('input', function() {
            const len = this.value.length;
            charCounter.textContent = len + ' / 150';
            charCounter.style.color = len > 150 ? '#e70013' : len > 130 ? '#f59e0b' : 'var(--text-muted)';
        });
        msgInput.addEventListener('focus', () => inputRow.classList.add('focused'));
        msgInput.addEventListener('blur',  () => inputRow.classList.remove('focused'));
        msgInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
    }
}

function updateTimers() {
    document.querySelectorAll('.message-row').forEach(row => {
        const timerSpan = row.querySelector('.edit-timer');
        if (!timerSpan) return;
        let sl = parseInt(row.dataset.secondsLeft);
        if (sl > 0) { sl--; row.dataset.secondsLeft = sl; timerSpan.innerHTML = `<i class="fa-regular fa-clock"></i> ${sl}s`; if (sl === 0) timerSpan.remove(); }
    });
}
setInterval(updateTimers, 1000);

function showChatError(message) {
    const vm = document.getElementById('validationMsg');
    const vt = document.getElementById('validationText');
    const ir = document.getElementById('inputRow');
    if (!vm || !vt) return;
    clearTimeout(window._chatErrTimer);
    vt.textContent = message;
    vm.classList.add('show');
    if (ir) ir.classList.add('error');
    window._chatErrTimer = setTimeout(() => { vm.classList.remove('show'); if (ir) ir.classList.remove('error'); }, 3000);
}
function hideChatError() {
    const vm = document.getElementById('validationMsg');
    const ir = document.getElementById('inputRow');
    if (vm) vm.classList.remove('show');
    if (ir) ir.classList.remove('error');
    clearTimeout(window._chatErrTimer);
}

function sendMessage() {
    const msgInput = document.getElementById('msgInput');
    if (!msgInput) return;
    const text = msgInput.value.trim();
    hideChatError();
    if (text === '') { showChatError('Veuillez écrire un message.'); msgInput.focus(); return; }
    if (text.length > 150) { showChatError('Message trop long (max 150 caractères).'); return; }
    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    
    // Use ephemeral endpoint if option selected
    const endpoint = currentEphemeralOption ? 'ephemeral' : 'send';
    const body = 'id_conversation=' + currentConversationId + '&contenu=' + encodeURIComponent(text) + (currentEphemeralOption ? '&expiration=' + encodeURIComponent(currentEphemeralOption) : '');
    
    fetch('messagerie_index.php?page=chat&action=' + endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            msgInput.value = '';
            msgInput.style.height = 'auto';
            document.getElementById('charCounter').textContent = '0 / 150';
            currentEphemeralOption = null;
            document.getElementById('ephemeralBtn').style.color = '';
            const a = document.querySelector('.conversation-item.active');
            if (a) loadConversation(currentConversationId, a);
        } else {
            showChatError(data.error || 'Erreur lors de l\'envoi.');
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fa-regular fa-paper-plane"></i>';
        }
    })
    .catch(() => {
        showChatError('Erreur réseau. Veuillez réessayer.');
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fa-regular fa-paper-plane"></i>';
    });
}

function refreshMessages() {
    if (!currentConversationId) return;
    fetch('messagerie_get_messages.php?id=' + currentConversationId)
        .then(r => r.json())
        .then(data => {
            if (data.error) return;
            if (data.messages && data.messages.length !== messages.length) {
                messages = data.messages;
                const a = document.querySelector('.conversation-item.active');
                if (a) loadConversation(currentConversationId, a);
            } else if (data.messages) { messages = data.messages; }
        });
}
setInterval(refreshMessages, 2000);

// New feature functions

function translateMessage(event, messageId, targetLang) {
    const contentEl = document.getElementById('content-' + messageId);
    if (!contentEl) return;
    
    // Check if already translated
    let translatedDiv = contentEl.querySelector('.message-translated');
    if (translatedDiv) {
        translatedDiv.remove();
        return;
    }
    
    // Show loading state
    const btn = event?.currentTarget || (event?.target?.closest ? event.target.closest('.msg-btn-translate') : null);
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.5';
    }
    
    // Call translation API
    fetch('messagerie_index.php?page=chat&action=translate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_message=' + messageId + '&target_lang=' + encodeURIComponent(targetLang)
    }).then(r => r.json()).then(data => {
        if (data.translated_text) {
            // Create translated div
            translatedDiv = document.createElement('div');
            translatedDiv.className = 'message-translated';
            translatedDiv.style.cssText = 'margin-top:.4rem;padding-top:.4rem;border-top:1px solid rgba(255,255,255,.1);color:var(--tech-blue);font-style:italic;font-size:.85rem;';
            translatedDiv.innerHTML = '<i class="fa-solid fa-language" style="margin-right:.3rem;"></i>' + escapeHtml(data.translated_text) + (data.source_lang ? ' <span style="opacity:.7;">(' + escapeHtml(data.source_lang) + '→' + escapeHtml(targetLang) + ')</span>' : '');
            contentEl.appendChild(translatedDiv);
            
            // Update button
            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        } else {
            showChatError(data.error || 'Erreur lors de la traduction.');
            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        }
    }).catch(() => {
        showChatError('Erreur réseau.');
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    });
}

function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const maxSize = 10 * 1024 * 1024; // 10 MB
    if (file.size > maxSize) {
        showChatError('Fichier trop volumineux (max 10 MB)');
        return;
    }
    
    const formData = new FormData();
    formData.append('id_conversation', currentConversationId);
    formData.append('file', file);
    
    const fileBtn = document.getElementById('fileBtn');
    fileBtn.disabled = true;
    fileBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    
    fetch('messagerie_index.php?page=chat&action=upload-file', {
        method: 'POST',
        body: formData
    }).then(r => r.json()).then(data => {
        fileBtn.disabled = false;
        fileBtn.innerHTML = '<i class="fa-solid fa-paperclip"></i>';
        
        if (data.success) {
            // Reload conversation to show file
            const a = document.querySelector('.conversation-item.active');
            if (a) loadConversation(currentConversationId, a);
            showToast('Fichier envoyé ✓');
        } else {
            showChatError(data.error || 'Erreur lors du téléchargement.');
        }
    }).catch(() => {
        fileBtn.disabled = false;
        fileBtn.innerHTML = '<i class="fa-solid fa-paperclip"></i>';
        showChatError('Erreur réseau.');
    });
    
    // Reset input
    event.target.value = '';
}

let currentEphemeralOption = null;

function showEphemeralOptions() {
    const menu = document.getElementById('ephemeralMenu');
    const btn = document.getElementById('ephemeralBtn');
    const rect = btn.getBoundingClientRect();
    menu.style.top = (rect.top - 140) + 'px';
    menu.style.left = (rect.left - 50) + 'px';
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    
    document.addEventListener('click', function closeMenu(e) {
        if (e.target !== btn && !menu.contains(e.target)) {
            menu.style.display = 'none';
            document.removeEventListener('click', closeMenu);
        }
    });
}

function selectEphemeral(option) {
    currentEphemeralOption = option;
    document.getElementById('ephemeralMenu').style.display = 'none';
    const ephemeralBtn = document.getElementById('ephemeralBtn');
    ephemeralBtn.style.color = 'var(--tech-blue)';
    showToast('Mode éphémère: ' + (option === '10s' ? '10 secondes' : option === '1h' ? '1 heure' : option === '24h' ? '24 heures' : 'Après lecture'));
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideOutRight .3s ease-out forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function showUnreadNotification(convId, name, preview) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.style.minWidth = '260px';
    toast.style.maxWidth = '320px';
    toast.innerHTML = `<strong>${escapeHtml(name)}</strong><div style="margin-top:.35rem;color:var(--text-muted);font-size:.82rem;">${escapeHtml(preview)}</div>`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideOutRight .3s ease-out forwards';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function loadUnreadCounts() {
    fetch('messagerie_index.php?page=chat&action=unread-counts')
        .then(r => r.json())
        .then(data => {
            if (!data.unread_counts) return;
            Object.entries(data.unread_counts).forEach(([convId, info]) => {
                const badge = document.getElementById('unread-' + convId);
                const count = info.count;
                if (badge) {
                    if (count > 0 && currentConversationId != convId) {
                        badge.textContent = count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
                if (unreadCounts[convId] === undefined) {
                    unreadCounts[convId] = count;
                    lastNotificationTimestamps[convId] = Date.now();
                    return;
                }
                if (count > unreadCounts[convId] && currentConversationId != convId) {
                    const currentTime = Date.now();
                    if (currentTime - (lastNotificationTimestamps[convId] || 0) > 3000) {
                        showUnreadNotification(convId, info.name, info.preview || 'Nouveau message');
                        lastNotificationTimestamps[convId] = currentTime;
                    }
                }
                unreadCounts[convId] = count;
            });
        })
        .catch(() => {});
}

loadUnreadCounts();
setInterval(loadUnreadCounts, 3000);

function renameConversation() {
    const newName = prompt('Nouveau nom de la conversation :');
    if (newName && newName.trim()) {
        fetch('messagerie_index.php?page=chat&action=rename', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_conversation=' + currentConversationId + '&titre=' + encodeURIComponent(newName.trim())
        }).then(r => r.json()).then(data => {
            if (data.success) { const a = document.querySelector('.conversation-item.active'); loadConversation(currentConversationId, a); }
            else showChatError('Erreur lors du renommage.');
        });
    }
}

function deleteCurrentConversation() {
    if (confirm('Supprimer cette conversation ?')) {
        fetch('messagerie_index.php?page=chat&action=delete-conv-user', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_conversation=' + currentConversationId
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); else showChatError('Erreur lors de la suppression.'); });
    }
}

function showMessageMenu(event, messageId, canEdit, currentContent) {
    event.stopPropagation();
    if (activeDropdown) { activeDropdown.remove(); activeDropdown = null; }
    const btn = event.currentTarget;
    const rect = btn.getBoundingClientRect();
    const dropdown = document.createElement('div');
    dropdown.className = 'dropdown';
    dropdown.style.top   = rect.bottom + 5 + 'px';
    dropdown.style.right = (window.innerWidth - rect.right) + 'px';
    dropdown.innerHTML = `
        ${canEdit ? `<button onclick="editMessage(${messageId}, '${currentContent.replace(/'/g, "\\'")}'); this.closest('.dropdown').remove();" class="edit"><i class="fa-regular fa-pen-to-square"></i> Modifier (1min)</button>` : `<button class="disabled" disabled><i class="fa-regular fa-pen-to-square"></i> Modifier (délai dépassé)</button>`}
        <button onclick="reportMessage(${messageId}); this.closest('.dropdown').remove();" class="report"><i class="fa-solid fa-flag"></i> Signaler</button>
        <button onclick="deleteMessage(${messageId}); this.closest('.dropdown').remove();" class="delete"><i class="fa-regular fa-trash-can"></i> Supprimer</button>`;
    document.body.appendChild(dropdown);
    activeDropdown = dropdown;
}

function editMessage(messageId, currentContent) {
    const bubble = document.getElementById(`bubble-${messageId}`);
    if (!bubble) return;
    if (activeDropdown) { activeDropdown.remove(); activeDropdown = null; }
    bubble.innerHTML = `<textarea class="edit-textarea" rows="2" maxlength="150">${currentContent}</textarea><div class="edit-actions"><button class="save-edit" onclick="saveEdit(${messageId}, this)">✓ Modifier</button><button class="cancel-edit" onclick="cancelEdit(${messageId})">✗ Annuler</button></div>`;
    const ta = bubble.querySelector('.edit-textarea');
    ta.focus(); ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight, 100) + 'px';
}

function saveEdit(messageId, btn) {
    const bubble = btn.closest('.message-bubble');
    const textarea = bubble.querySelector('.edit-textarea');
    const newContent = textarea.value.trim();
    if (!newContent) { showChatError('Message requis.'); cancelEdit(messageId); return; }
    if (newContent.length > 150) { showChatError('Max 150 caractères.'); return; }
    btn.disabled = true; btn.textContent = '...';
    fetch('messagerie_index.php?page=chat&action=edit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_message=' + messageId + '&contenu=' + encodeURIComponent(newContent)
    }).then(r => r.json()).then(data => {
        if (data.success) { const a = document.querySelector('.conversation-item.active'); if (a) loadConversation(currentConversationId, a); }
        else { showChatError('Impossible de modifier.'); cancelEdit(messageId); }
    }).catch(() => { showChatError('Erreur réseau.'); cancelEdit(messageId); });
}

function cancelEdit(messageId) {
    const a = document.querySelector('.conversation-item.active');
    if (a) loadConversation(currentConversationId, a);
}

function deleteMessage(messageId) {
    if (confirm('Supprimer ce message ?')) {
        fetch('messagerie_index.php?page=chat&action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_message=' + messageId
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const row = document.querySelector(`.message-row[data-message-id="${messageId}"]`);
                if (row) { row.style.opacity = '0'; row.style.transition = 'opacity .3s'; setTimeout(() => row.remove(), 300); }
                messages = messages.filter(m => m.id_message != messageId);
            } else showChatError('Impossible de supprimer.');
        });
    }
}

function reportMessage(messageId) {
    if (confirm('Signaler ce message ?')) {
        fetch('messagerie_index.php?page=chat&action=report', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_message=' + messageId
        }).then(r => r.json()).then(data => {
            if (data.success) showChatError('Message signalé ✓');
            else showChatError('Erreur lors du signalement.');
        });
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('click', function() {
    if (activeDropdown) { activeDropdown.remove(); activeDropdown = null; }
});

/* ── Groupe modal ── */
let groupeSelectedIds = new Set();
let _groupeNomValue = '';

function buildContactList() {
    const items = document.querySelectorAll('.conversation-item');
    const contacts = [];
    const seen = new Set();
    items.forEach(item => {
        const u1 = parseInt(item.dataset.user1);
        const u2 = parseInt(item.dataset.user2);
        const uid = (u1 === currentUserId) ? u2 : u1;
        const name = item.dataset.name || ('Utilisateur #' + uid);
        if (!seen.has(uid)) { seen.add(uid); contacts.push({ uid, name }); }
    });
    return contacts;
}

function renderGroupeContacts(filter) {
    filter = (filter || '').toLowerCase();
    const list = document.getElementById('groupeContactList');
    const contacts = buildContactList().filter(c => c.name.toLowerCase().includes(filter) || String(c.uid).includes(filter));
    if (contacts.length === 0) {
        list.innerHTML = `<div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.83rem;">Aucun contact trouvé</div>`;
        return;
    }
    list.innerHTML = contacts.map(c => {
        const sel = groupeSelectedIds.has(c.uid);
        return `<div onclick="toggleGroupeMembre(${c.uid},'${c.name.replace(/'/g,"\\'")}') "
             style="display:flex;align-items:center;gap:.85rem;padding:.65rem 1.5rem;cursor:pointer;transition:background .15s;${sel?'background:rgba(124,58,237,.1);':''}">
            <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-weight:bold;flex-shrink:0;">${c.name.charAt(0).toUpperCase()}</div>
            <div style="flex:1;min-width:0;"><div style="font-size:.87rem;font-weight:500;">${c.name}</div><div style="font-size:.7rem;color:var(--text-muted);">Utilisateur #${c.uid}</div></div>
            <div style="width:22px;height:22px;border-radius:50%;border:2px solid ${sel?'#a78bfa':'rgba(255,255,255,.15)'};background:${sel?'#a78bfa':'transparent'};display:flex;align-items:center;justify-content:center;flex-shrink:0;">${sel?'<i class="fa-solid fa-check" style="color:white;font-size:.6rem;"></i>':''}</div>
        </div>`;
    }).join('');
}

function toggleGroupeMembre(uid, name) {
    if (groupeSelectedIds.has(uid)) groupeSelectedIds.delete(uid);
    else groupeSelectedIds.add(uid);
    updateGroupeChips();
    renderGroupeContacts(document.getElementById('groupeSearch').value);
}

function updateGroupeChips() {
    const contacts = buildContactList();
    document.getElementById('groupeChips').innerHTML = [...groupeSelectedIds].map(uid => {
        const c = contacts.find(x => x.uid === uid);
        const name = c ? c.name : '#' + uid;
        return `<span style="background:rgba(124,58,237,.2);border:1px solid rgba(124,58,237,.35);color:#a78bfa;border-radius:999px;padding:.25rem .65rem .25rem .5rem;font-size:.73rem;display:inline-flex;align-items:center;gap:.35rem;">${name} <span onclick="toggleGroupeMembre(${uid},'')" style="cursor:pointer;opacity:.7;">✕</span></span>`;
    }).join('');
    document.getElementById('groupeCount').textContent = groupeSelectedIds.size + ' membre(s) sélectionné(s)';
}

function filterGroupeContacts() { renderGroupeContacts(document.getElementById('groupeSearch').value); }

function openGroupeModal() {
    groupeSelectedIds.clear();
    _groupeNomValue = '';
    document.getElementById('groupeModal').style.display = 'flex';
    setTimeout(() => {
        const inp = document.getElementById('groupeNomInput');
        if (inp) { inp.value = ''; inp.focus(); }
        document.getElementById('groupeSearch').value = '';
        document.getElementById('groupeError').textContent = '';
        updateGroupeChips();
        renderGroupeContacts('');
    }, 30);
}

function closeGroupeModal() { document.getElementById('groupeModal').style.display = 'none'; }

function submitGroupe() {
    const inp = document.getElementById('groupeNomInput');
    const nom = (inp ? inp.value : '').replace(/^\s+|\s+$/gu, '').replace(/[\u200B-\u200D\uFEFF]/g, '');
    const errEl = document.getElementById('groupeError');
    if (nom.length === 0) { errEl.textContent = 'Le nom du groupe est requis.'; return; }
    if (groupeSelectedIds.size < 1) { errEl.textContent = 'Sélectionnez au moins un membre.'; return; }
    errEl.textContent = '';
    const fd = new FormData();
    fd.append('nom', nom);
    groupeSelectedIds.forEach(id => fd.append('membres[]', id));
    fetch('messagerie_index.php?page=chat&action=create-groupe', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.success) { closeGroupeModal(); location.reload(); } else { errEl.textContent = data.error || 'Erreur serveur.'; } })
        .catch(() => { errEl.textContent = 'Erreur réseau.'; });
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeGroupeModal(); });

function toggleTheme(btn) {
    const icon = btn.querySelector('i');
    if (icon.classList.contains('fa-moon')) {
        icon.classList.replace('fa-moon', 'fa-sun');
        document.body.style.filter = 'brightness(1.08)';
    } else {
        icon.classList.replace('fa-sun', 'fa-moon');
        document.body.style.filter = '';
    }
}

function openGitPanel() {
    if (!currentConversationId) { showToast('Sélectionnez une conversation d\'abord.'); return; }
    window.open('messagerie_index.php?page=git&id_conversation=' + currentConversationId, '_blank', 'width=1100,height=750,resizable=yes,scrollbars=yes');
}


/* ── Suggestion nouvelle conversation ── */
const searchInputEl       = document.getElementById('searchInput');
const suggestionContainer = document.getElementById('suggestionContainer');

function nameAlreadyExists(name) {
    const items = document.querySelectorAll('.conversation-item');
    const q = name.trim().toLowerCase();
    for (const item of items) {
        if ((item.dataset.name || '').toLowerCase().includes(q)) return true;
    }
    return false;
}

function checkAndShowSuggestion() {
    const query = searchInputEl.value.trim();
    suggestionContainer.innerHTML = '';
    suggestionContainer.style.display = 'none';
    if (query.length === 0) return;
    if (nameAlreadyExists(query)) {
        document.getElementById('searchTriForm').submit();
    } else {
        suggestionContainer.style.display = 'block';
        suggestionContainer.innerHTML = `
            <div class="suggestion-card" onclick="startConversationByName('${query.replace(/'/g, "\\'")}')">
                <div class="suggestion-info">
                    <h4><i class="fa-regular fa-user"></i> ${escapeHtml(query)}</h4>
                    <p>Cliquez pour démarrer</p>
                </div>
                <button class="suggestion-btn"><i class="fa-regular fa-message"></i> Démarrer</button>
            </div>`;
    }
}

searchInputEl.addEventListener('input', function() {
    if (this.value.trim() === '') { suggestionContainer.innerHTML = ''; suggestionContainer.style.display = 'none'; }
});

function startConversationByName(name) {
    fetch('messagerie_index.php?page=chat&action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'user_id=0&titre=' + encodeURIComponent(name)
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); else showChatError(data.error || 'Erreur.'); });
}

document.addEventListener('click', function(e) {
    if (!suggestionContainer.contains(e.target) && e.target !== searchInputEl) {
        suggestionContainer.style.display = 'none';
    }
});
</script>
</div><!-- /page-layout -->
</body>
</html>