<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique — Conversation #<?= $id_conversation ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:           #05081a;
            --surface:      rgba(255,255,255,0.04);
            --surface2:     rgba(255,255,255,0.03);
            --border:       rgba(255,255,255,0.08);
            --text:         #e2e8f0;
            --text-muted:   #94a3b8;
            --text-dim:     #475569;
            --primary:      #2563eb;
            --primary-dark: #1d4ed8;
            --primary-bg:   rgba(37,99,235,0.12);
            --primary-glow: rgba(37,99,235,0.25);
            --green:        #22c55e;
            --green-bg:     rgba(34,197,94,0.08);
            --green-border: rgba(34,197,94,0.25);
            --red:          #ef4444;
            --red-bg:       rgba(239,68,68,0.08);
            --red-border:   rgba(239,68,68,0.25);
            --yellow:       #f59e0b;
            --yellow-bg:    rgba(245,158,11,0.08);
            --yellow-border:rgba(245,158,11,0.25);
            --purple:       #7c3aed;
            --purple-bg:    rgba(124,58,237,0.10);
            --radius:       8px;
            --radius-lg:    14px;
            --shadow:       0 1px 3px rgba(0,0,0,0.4), 0 1px 2px rgba(0,0,0,0.3);
            --shadow-md:    0 4px 24px rgba(0,0,0,0.5);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background:
                radial-gradient(ellipse 70% 60% at 75% 30%, rgba(37,99,235,0.13) 0%, transparent 70%),
                radial-gradient(ellipse 55% 50% at 20% 20%, rgba(124,58,237,0.10) 0%, transparent 65%),
                #05081a;
            color: var(--text);
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.6;
        }

        /* ── TOPBAR ── */
        .topbar {
            background: rgba(5,8,26,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 0 rgba(255,255,255,0.05);
        }

        .topbar-back {
            display: flex;
            align-items: center;
            gap: .4rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: .82rem;
            font-weight: 500;
            padding: .35rem .7rem;
            border-radius: var(--radius);
            transition: background .15s, color .15s;
        }
        .topbar-back:hover { background: var(--bg); color: var(--primary); }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            flex: 1;
        }
        .brand-logo {
            width: 32px;
            height: 32px;
            background: var(--primary);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: .9rem;
        }
        .brand-name {
            font-weight: 700;
            font-size: .95rem;
            color: var(--primary);
        }
        .brand-sep { color: var(--border); font-size: 1.2rem; font-weight: 300; }
        .brand-conv {
            font-size: .82rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .branch-pill {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            background: var(--primary-bg);
            border: 1px solid var(--primary-glow);
            color: var(--primary);
            padding: .2rem .65rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 600;
        }

        .topbar-stats {
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .topbar-stat {
            display: flex;
            align-items: center;
            gap: .35rem;
            font-size: .78rem;
            color: var(--text-muted);
            background: var(--bg);
            padding: .3rem .7rem;
            border-radius: 999px;
            border: 1px solid var(--border);
        }

        /* ── LAYOUT ── */
        .git-layout {
            display: flex;
            gap: 0;
            max-width: 1200px;
            margin: 1.5rem auto;
            padding: 0 1.25rem;
            align-items: flex-start;
        }

        /* ── SIDEBAR ── */
        .git-sidebar {
            width: 220px;
            flex-shrink: 0;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1rem;
            box-shadow: var(--shadow);
            position: sticky;
            top: 80px;
            margin-right: 1.25rem;
        }
        .sidebar-section-title {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text-dim);
            margin-bottom: .5rem;
            padding: 0 .4rem;
        }
        .sidebar-nav-item {
            display: flex;
            align-items: center;
            gap: .6rem;
            width: 100%;
            padding: .5rem .75rem;
            border-radius: var(--radius);
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: .84rem;
            font-weight: 500;
            cursor: pointer;
            transition: background .15s, color .15s;
            text-align: left;
        }
        .sidebar-nav-item:hover { background: var(--bg); color: var(--text); }
        .sidebar-nav-item.active { background: var(--primary-bg); color: var(--primary); }
        .nav-count {
            margin-left: auto;
            background: var(--border);
            color: var(--text-muted);
            font-size: .68rem;
            font-weight: 700;
            padding: .05rem .45rem;
            border-radius: 999px;
        }
        .sidebar-nav-item.active .nav-count {
            background: var(--primary-glow);
            color: var(--primary);
        }

        .branch-list { margin-top: .35rem; }
        .branch-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem .6rem;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: .8rem;
            color: var(--text-muted);
            transition: background .15s, color .15s;
        }
        .branch-item:hover { background: var(--bg); color: var(--text); }
        .branch-item.active { color: var(--primary); font-weight: 600; background: var(--primary-bg); }
        .branch-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--border);
            flex-shrink: 0;
        }
        .branch-item.active .branch-dot { background: var(--primary); }
        .branch-commits { margin-left: auto; font-size: .68rem; color: var(--text-dim); }
        .btn-delete-branch {
            margin-left: auto;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-dim);
            padding: .1rem .3rem;
            border-radius: 4px;
            font-size: .72rem;
            transition: color .15s, background .15s;
            display: none;
        }
        .branch-item:hover .btn-delete-branch { display: block; }
        .btn-delete-branch:hover { color: var(--red); background: var(--red-bg); }

        /* ── MAIN ── */
        .git-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 1.25rem; }

        /* ── CARDS ── */
        .git-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .git-card-header {
            display: flex;
            align-items: center;
            padding: .85rem 1.25rem;
            border-bottom: 1px solid var(--border);
            gap: .75rem;
            background: var(--surface2);
        }
        .git-card-header h3 {
            font-size: .9rem;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .git-card-header h3 i { color: var(--primary); }
        .card-actions { margin-left: auto; display: flex; gap: .5rem; }
        .git-card-body { padding: 1.25rem; }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem 1rem;
            border-radius: var(--radius);
            border: 1px solid transparent;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            white-space: nowrap;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .btn-secondary {
            background: var(--surface);
            color: var(--text-muted);
            border-color: var(--border);
        }
        .btn-secondary:hover { background: var(--bg); color: var(--text); border-color: #c9cdd4; }
        .btn-danger {
            background: var(--red-bg);
            color: var(--red);
            border-color: var(--red-border);
        }
        .btn-danger:hover { background: #fee2e2; }
        .btn-sm { padding: .3rem .7rem; font-size: .77rem; }
        .btn:disabled { opacity: .55; cursor: not-allowed; }

        /* ── COMMIT FORM ── */
        .commit-form { display: flex; flex-direction: column; gap: .85rem; }
        .form-row { display: flex; gap: 1rem; }
        .form-row > div { flex: 1; display: flex; flex-direction: column; gap: .35rem; }
        label {
            font-size: .75rem;
            font-weight: 600;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: .3rem;
        }
        label i { color: var(--primary); }
        input[type="text"], select {
            width: 100%;
            padding: .55rem .85rem;
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: var(--radius);
            font-size: .84rem;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: rgba(255,255,255,0.05);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        select option {
            background: #0e1530;
            color: #e2e8f0;
        }
        input[type="text"]:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-bg);
        }

        /* ── COMMIT LIST ── */
        .commit-list { display: flex; flex-direction: column; }
        .commit-entry {
            display: flex;
            align-items: flex-start;
            gap: .85rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            transition: background .12s;
        }
        .commit-entry:last-child { border-bottom: none; }
        .commit-entry:hover { background: var(--surface2); }

        .commit-timeline {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
            padding-top: .15rem;
        }
        .commit-dot {
            width: 11px; height: 11px;
            border-radius: 50%;
            background: var(--primary);
            border: 2px solid var(--primary-bg);
            box-shadow: 0 0 0 2px var(--primary-glow);
            flex-shrink: 0;
        }
        .commit-line {
            width: 2px;
            flex: 1;
            min-height: 28px;
            background: var(--border);
            margin-top: 4px;
        }

        .commit-info { flex: 1; min-width: 0; }
        .commit-msg {
            font-weight: 600;
            font-size: .88rem;
            color: var(--text);
            margin-bottom: .3rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .commit-meta { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .73rem;
            color: var(--text-muted);
            background: var(--bg);
            padding: .15rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
        }
        .meta-chip i { font-size: .65rem; color: var(--text-dim); }
        .commit-hash {
            font-family: 'Courier New', monospace;
            font-size: .72rem;
            color: var(--primary);
            background: var(--primary-bg);
            padding: .15rem .5rem;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background .15s;
        }
        .commit-hash:hover { background: var(--primary-glow); }
        .commit-actions { display: flex; gap: .4rem; flex-shrink: 0; padding-top: .05rem; }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--text-dim);
        }
        .empty-state i { font-size: 2.2rem; color: var(--border); margin-bottom: 1rem; display: block; }
        .empty-state p { font-size: .85rem; }

        /* ── MODALS ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.65);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(3px);
        }
        .modal-overlay.show { display: flex; }
        .modal-box {
            background: #0c1229;
            border-radius: var(--radius-lg);
            width: 680px;
            max-width: 95vw;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0,0,0,.7);
            border: 1px solid rgba(255,255,255,0.09);
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface2);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .modal-header h3 { font-size: .95rem; font-weight: 700; display: flex; align-items: center; gap: .5rem; }
        .modal-header h3 i { color: var(--primary); }
        .modal-close {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            font-size: 1rem;
            padding: .3rem;
            border-radius: 6px;
            transition: background .15s, color .15s;
        }
        .modal-close:hover { background: var(--bg); color: var(--text); }
        .modal-body { padding: 1.5rem; overflow-y: auto; }

        /* ── DIFF VIEWER ── */
        .diff-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .65rem 1rem;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: .78rem;
            color: var(--text-muted);
            margin-bottom: .85rem;
        }
        .diff-stats { display: flex; gap: .6rem; }
        .diff-stat-add { color: var(--green); font-weight: 600; }
        .diff-stat-del { color: var(--red); font-weight: 600; }

        .diff-block {
            border-radius: var(--radius);
            margin-bottom: .65rem;
            border: 1px solid;
            overflow: hidden;
        }
        .diff-block.added { border-color: var(--green-border); }
        .diff-block.removed { border-color: var(--red-border); }
        .diff-block.modified { border-color: var(--yellow-border); }

        .diff-label {
            padding: .4rem .9rem;
            font-size: .73rem;
            font-weight: 600;
        }
        .diff-block.added .diff-label { background: var(--green-bg); color: var(--green); }
        .diff-block.removed .diff-label { background: var(--red-bg); color: var(--red); }
        .diff-block.modified .diff-label { background: var(--yellow-bg); color: var(--yellow); }

        .diff-content {
            padding: .65rem .9rem;
            font-size: .82rem;
            color: var(--text);
            border-top: 1px solid var(--border);
            background: var(--surface);
        }

        /* ── LOADER ── */
        .loader {
            display: inline-block;
            width: 14px; height: 14px;
            border: 2px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin .6s linear infinite;
            vertical-align: middle;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── TOAST ── */
        .git-toast {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .75rem 1.2rem;
            border-radius: var(--radius);
            font-size: .84rem;
            font-weight: 600;
            box-shadow: var(--shadow-md);
            z-index: 9999;
            border: 1px solid;
            animation: slideUp .2s ease;
        }
        @keyframes slideUp { from { transform: translateY(10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .git-toast.success { background: var(--green-bg); color: var(--green); border-color: var(--green-border); }
        .git-toast.error   { background: var(--red-bg);   color: var(--red);   border-color: var(--red-border); }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <a class="topbar-back" href="javascript:history.back()">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
    <div class="topbar-brand">
        <div class="brand-logo"><i class="fa-solid fa-code-branch"></i></div>
        <span class="brand-name">FreelaSkill</span>
        <span class="brand-sep">|</span>
        <span class="brand-conv">Conversation #<?= $id_conversation ?></span>
        <span class="branch-pill">
            <i class="fa-solid fa-code-branch" style="font-size:.6rem;"></i>
            <?= htmlspecialchars($brancheActive) ?>
        </span>
    </div>
    <div class="topbar-stats">
        <div class="topbar-stat">
            <i class="fa-solid fa-circle-dot" style="color:var(--green);"></i>
            <?= $totalCommits ?> commits
        </div>
        <div class="topbar-stat">
            <i class="fa-solid fa-code-branch" style="color:var(--purple);"></i>
            <?= count($branches) ?> branches
        </div>
    </div>
</div>

<!-- LAYOUT -->
<div class="git-layout">

    <!-- SIDEBAR -->
    <aside class="git-sidebar">
        <div class="sidebar-section-title">Navigation</div>
        <button class="sidebar-nav-item active" onclick="showSection('commits')">
            <i class="fa-solid fa-clock-rotate-left"></i> Historique
            <span class="nav-count"><?= $totalCommits ?></span>
        </button>
        <button class="sidebar-nav-item" onclick="showSection('branches')">
            <i class="fa-solid fa-code-branch"></i> Branches
            <span class="nav-count"><?= count($branches) ?></span>
        </button>

        <div class="sidebar-section-title" style="margin-top:1rem;">Branches</div>
        <div class="branch-list" id="branchListSidebar">
            <?php foreach ($branches as $b): ?>
            <div class="branch-item <?= $b['nom'] === $brancheActive ? 'active' : '' ?>"
                 onclick="switchBranch('<?= htmlspecialchars($b['nom']) ?>')">
                <span class="branch-dot"></span>
                <span><?= htmlspecialchars($b['nom']) ?></span>
                <span class="branch-commits" id="bc-<?= htmlspecialchars($b['nom']) ?>">…</span>
                <?php if ($b['nom'] !== 'main'): ?>
                <button class="btn-delete-branch" title="Supprimer"
                        onclick="event.stopPropagation(); deleteBranche('<?= htmlspecialchars($b['nom']) ?>')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="git-main">

        <!-- ══ COMMITS SECTION ══ -->
        <div id="section-commits">

            <!-- Commit form -->
            <div class="git-card">
                <div class="git-card-header">
                    <h3><i class="fa-solid fa-plus"></i> Nouveau commit</h3>
                </div>
                <div class="git-card-body">
                    <div class="commit-form">
                        <div class="form-row">
                            <div>
                                <label><i class="fa-solid fa-code-branch"></i> Branche cible</label>
                                <select id="selectBranche">
                                    <?php foreach ($branches as $b): ?>
                                    <option value="<?= htmlspecialchars($b['nom']) ?>" <?= $b['nom'] === $brancheActive ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b['nom']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label><i class="fa-solid fa-message"></i> Message de commit</label>
                                <input type="text" id="commitMessage" placeholder="ex : correction affichage messages" maxlength="500"
                                       onkeydown="if(event.key==='Enter') doCommit()">
                            </div>
                        </div>
                        <!-- File attachment row -->
                        <div style="margin-top:.75rem;">
                            <label style="font-size:.78rem;color:var(--text-dim);font-weight:500;display:block;margin-bottom:.4rem;">
                                <i class="fa-solid fa-paperclip"></i> Fichier joint <span style="font-weight:400;opacity:.7;">(optionnel, max 10 Mo)</span>
                            </label>
                            <div style="display:flex;align-items:center;gap:.6rem;">
                                <button type="button" onclick="document.getElementById('commitFileInput').click()"
                                        style="display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;background:rgba(99,102,241,.1);border:1px dashed rgba(99,102,241,.4);border-radius:8px;color:var(--primary);font-size:.8rem;cursor:pointer;transition:background .2s;"
                                        onmouseover="this.style.background='rgba(99,102,241,.2)'" onmouseout="this.style.background='rgba(99,102,241,.1)'">
                                    <i class="fa-solid fa-upload"></i> Choisir un fichier
                                </button>
                                <span id="commitFileLabel" style="font-size:.8rem;color:var(--text-dim);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Aucun fichier sélectionné</span>
                                <button type="button" id="commitFileClear" onclick="clearCommitFile()" style="display:none;background:none;border:none;color:#e70013;cursor:pointer;font-size:.85rem;" title="Retirer le fichier">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <input type="file" id="commitFileInput" style="display:none;" onchange="onCommitFileChange(event)">
                        </div>
                        <div style="display:flex;justify-content:flex-end;">
                            <button class="btn btn-primary" onclick="doCommit()" id="btnCommit">
                                <i class="fa-solid fa-check"></i> Enregistrer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commit history -->
            <div class="git-card">
                <div class="git-card-header">
                    <h3><i class="fa-solid fa-clock-rotate-left"></i>
                        Historique &mdash; <span style="color:var(--primary);font-weight:700;"><?= htmlspecialchars($brancheActive) ?></span>
                    </h3>
                    <div class="card-actions">
                        <button class="btn btn-secondary btn-sm" onclick="refreshCommits()">
                            <i class="fa-solid fa-rotate-right"></i> Actualiser
                        </button>
                    </div>
                </div>
                <div id="commitListContainer">
                    <?php if (empty($commits)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <p>Aucun commit sur cette branche.</p>
                        <p style="margin-top:.5rem;font-size:.78rem;color:var(--text-dim);">Commencez par enregistrer l'état actuel de la conversation.</p>
                    </div>
                    <?php else: ?>
                    <div class="commit-list" id="commitListEl">
                        <?php foreach ($commits as $i => $c): ?>
                        <div class="commit-entry" id="entry-<?= $c['hash'] ?>">
                            <div class="commit-timeline">
                                <div class="commit-dot"></div>
                                <?php if ($i < count($commits) - 1): ?>
                                <div class="commit-line"></div>
                                <?php endif; ?>
                            </div>
                            <div class="commit-info">
                                <div class="commit-msg"><?= htmlspecialchars($c['message']) ?></div>
                                <div class="commit-meta">
                                    <span class="meta-chip"><i class="fa-solid fa-user"></i><?= htmlspecialchars($c['auteur']) ?></span>
                                    <span class="meta-chip"><i class="fa-regular fa-clock"></i><?= date('d/m/Y H:i', strtotime($c['date_commit'])) ?></span>
                                    <span class="commit-hash" onclick="openDiff('<?= $c['hash'] ?>')" title="Voir les modifications"><?= $c['hash'] ?></span>
                                    <?php if (!empty($c['file_name']) && !empty($c['file_path'])): ?>
                                    <a href="<?= htmlspecialchars($c['file_path']) ?>" target="_blank" download="<?= htmlspecialchars($c['file_name']) ?>" class="meta-chip" style="color:var(--primary);text-decoration:none;cursor:pointer;" title="Télécharger le fichier joint">
                                        <i class="fa-solid fa-paperclip"></i><?= htmlspecialchars($c['file_name']) ?>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="commit-actions">
                                <button class="btn btn-secondary btn-sm" onclick="openDiff('<?= $c['hash'] ?>')" title="Voir les changements">
                                    <i class="fa-solid fa-file-lines"></i> Diff
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="confirmRestore('<?= $c['hash'] ?>', '<?= htmlspecialchars($c['message'], ENT_QUOTES) ?>')" title="Restaurer cette version">
                                    <i class="fa-solid fa-rotate-left"></i> Restaurer
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /section-commits -->

        <!-- ══ BRANCHES SECTION ══ -->
        <div id="section-branches" style="display:none;">

            <!-- Créer une branche -->
            <div class="git-card">
                <div class="git-card-header">
                    <h3><i class="fa-solid fa-code-branch"></i> Créer une branche</h3>
                </div>
                <div class="git-card-body">
                    <div style="display:flex;gap:1rem;align-items:flex-end;">
                        <div style="flex:1;">
                            <label for="newBranchName"><i class="fa-solid fa-tag"></i> Nom de la branche</label>
                            <input type="text" id="newBranchName" placeholder="ex : feature/amelioration-ui" maxlength="100"
                                   onkeydown="if(event.key==='Enter') doCreateBranche()">
                        </div>
                        <div>
                            <button class="btn btn-primary" onclick="doCreateBranche()">
                                <i class="fa-solid fa-plus"></i> Créer
                            </button>
                        </div>
                    </div>
                    <p style="font-size:.72rem;color:var(--text-dim);margin-top:.6rem;">
                        <i class="fa-solid fa-circle-info" style="margin-right:.3rem;"></i>
                        Caractères autorisés : lettres, chiffres, <code>-</code> <code>_</code> <code>/</code>
                    </p>
                </div>
            </div>

            <!-- Liste des branches -->
            <div class="git-card">
                <div class="git-card-header">
                    <h3><i class="fa-solid fa-list"></i> Toutes les branches</h3>
                </div>
                <div id="branchesTableContainer">
                    <?php foreach ($branches as $b): ?>
                    <div style="display:flex;align-items:center;gap:1rem;padding:.85rem 1.25rem;border-bottom:1px solid var(--border);">
                        <div style="display:flex;align-items:center;gap:.6rem;flex:1;">
                            <i class="fa-solid fa-code-branch" style="color:<?= $b['nom'] === 'main' ? 'var(--green)' : 'var(--primary)' ?>;font-size:.85rem;"></i>
                            <span style="font-weight:600;font-size:.85rem;"><?= htmlspecialchars($b['nom']) ?></span>
                            <?php if ($b['nom'] === 'main'): ?>
                            <span style="background:var(--green-bg);border:1px solid var(--green-border);color:var(--green);padding:.1rem .55rem;border-radius:999px;font-size:.65rem;font-weight:700;">principale</span>
                            <?php endif; ?>
                            <?php if ($b['nom'] === $brancheActive): ?>
                            <span style="background:var(--primary-bg);border:1px solid var(--primary-glow);color:var(--primary);padding:.1rem .55rem;border-radius:999px;font-size:.65rem;font-weight:700;">active</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:.73rem;color:var(--text-muted);">
                            <i class="fa-regular fa-calendar" style="margin-right:.25rem;"></i>
                            <?= date('d/m/Y', strtotime($b['date_creation'])) ?>
                        </div>
                        <div style="display:flex;gap:.5rem;">
                            <button class="btn btn-secondary btn-sm" onclick="switchBranch('<?= htmlspecialchars($b['nom']) ?>')">
                                <i class="fa-solid fa-eye"></i> Voir commits
                            </button>
                            <?php if ($b['nom'] !== 'main'): ?>
                            <button class="btn btn-danger btn-sm" onclick="deleteBranche('<?= htmlspecialchars($b['nom']) ?>')">
                                <i class="fa-solid fa-trash-can"></i> Supprimer
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div><!-- /section-branches -->

    </main>
</div><!-- /git-layout -->

<!-- MODAL — Diff viewer -->
<div class="modal-overlay" id="diffModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="diffModalTitle"><i class="fa-solid fa-file-lines"></i> Différences du commit</h3>
            <button class="modal-close" onclick="closeDiff()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="diffModalBody">
            <div style="text-align:center;padding:2rem;"><div class="loader"></div></div>
        </div>
    </div>
</div>

<!-- MODAL — Confirmation restauration -->
<div class="modal-overlay" id="restoreModal">
    <div class="modal-box" style="width:440px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-triangle-exclamation" style="color:var(--yellow);"></i> Confirmer la restauration</h3>
            <button class="modal-close" onclick="closeRestoreModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <p style="color:var(--text-muted);font-size:.87rem;margin-bottom:1rem;">
                Vous allez restaurer la conversation à l'état du commit :
            </p>
            <div style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem 1rem;margin-bottom:1.25rem;">
                <div style="font-family:monospace;font-size:.78rem;color:var(--primary);font-weight:700;" id="restoreHash"></div>
                <div style="font-size:.88rem;font-weight:600;margin-top:.25rem;color:var(--text);" id="restoreMsg"></div>
            </div>
            <div style="background:var(--red-bg);border:1px solid var(--red-border);border-radius:var(--radius);padding:.75rem 1rem;margin-bottom:1.25rem;">
                <p style="color:var(--red);font-size:.8rem;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right:.3rem;"></i>
                    Cette action remplacera tous les messages actuels par ceux du snapshot. Un commit de restauration sera automatiquement créé.
                </p>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.6rem;">
                <button class="btn btn-secondary" onclick="closeRestoreModal()">Annuler</button>
                <button class="btn btn-danger" onclick="doRestore()" id="btnDoRestore">
                    <i class="fa-solid fa-rotate-left"></i> Restaurer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const CONV_ID = <?= $id_conversation ?>;
let activeBranche = '<?= htmlspecialchars($brancheActive) ?>';
let restoreTargetHash = null;

// ── NAVIGATION ──
function showSection(name) {
    document.querySelectorAll('[id^="section-"]').forEach(el => el.style.display = 'none');
    document.getElementById('section-' + name).style.display = 'block';
    document.querySelectorAll('.sidebar-nav-item').forEach(el => el.classList.remove('active'));
    const items = document.querySelectorAll('.sidebar-nav-item');
    if (name === 'commits') items[0].classList.add('active');
    if (name === 'branches') items[1].classList.add('active');
}

function switchBranch(branche) {
    activeBranche = branche;
    showSection('commits');
    refreshCommits();
    document.querySelectorAll('.branch-item').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.branch-item').forEach(el => {
        if (el.querySelector('span:nth-child(2)')?.textContent.trim() === branche) {
            el.classList.add('active');
        }
    });
}

// ── COMMIT ──
async function doCommit() {
    const msg = document.getElementById('commitMessage').value.trim();
    const br  = document.getElementById('selectBranche').value;
    if (!msg) { toast('Le message de commit est requis.', 'error'); return; }

    const btn = document.getElementById('btnCommit');
    btn.disabled = true;
    btn.innerHTML = '<span class="loader"></span> En cours...';

    try {
        const formData = new FormData();
        formData.append('id_conversation', CONV_ID);
        formData.append('message', msg);
        formData.append('branche', br);
        const fileInput = document.getElementById('commitFileInput');
        if (fileInput.files[0]) {
            formData.append('commit_file', fileInput.files[0]);
        }

        const res = await fetch('index.php?page=git&action=commit', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            const fileMsg = data.file_name ? ` · 📎 ${data.file_name}` : '';
            toast('Commit ' + data.hash + ' enregistré' + fileMsg, 'success');
            document.getElementById('commitMessage').value = '';
            clearCommitFile();
            activeBranche = br;
            await refreshCommits();
            await refreshBranchCounts();
        } else {
            toast(data.error || 'Erreur lors du commit', 'error');
        }
    } catch(e) { toast('Erreur réseau', 'error'); }

    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Enregistrer';
}

function onCommitFileChange(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (file.size > 10 * 1024 * 1024) {
        toast('Fichier trop volumineux (max 10 Mo)', 'error');
        event.target.value = '';
        return;
    }
    document.getElementById('commitFileLabel').textContent = file.name;
    document.getElementById('commitFileClear').style.display = 'inline-flex';
}

function clearCommitFile() {
    document.getElementById('commitFileInput').value = '';
    document.getElementById('commitFileLabel').textContent = 'Aucun fichier sélectionné';
    document.getElementById('commitFileClear').style.display = 'none';
}

// ── REFRESH COMMITS ──
async function refreshCommits() {
    const container = document.getElementById('commitListContainer');
    container.innerHTML = '<div style="padding:2rem;text-align:center;"><div class="loader"></div></div>';

    try {
        const res = await fetch(`index.php?page=git&action=list-commits&id_conversation=${CONV_ID}&branche=${encodeURIComponent(activeBranche)}`);
        const data = await res.json();
        if (!data.commits) { container.innerHTML = '<div class="empty-state"><i class="fa-solid fa-circle-exclamation"></i><p>Erreur de chargement</p></div>'; return; }
        if (data.commits.length === 0) {
            container.innerHTML = '<div class="empty-state"><i class="fa-solid fa-clock-rotate-left"></i><p>Aucun commit sur cette branche.</p></div>';
            return;
        }
        let html = '<div class="commit-list">';
        data.commits.forEach((c, i) => {
            const date = new Date(c.date_commit).toLocaleDateString('fr-FR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
            html += `<div class="commit-entry" id="entry-${c.hash}">
                <div class="commit-timeline">
                    <div class="commit-dot"></div>
                    ${i < data.commits.length - 1 ? '<div class="commit-line"></div>' : ''}
                </div>
                <div class="commit-info">
                    <div class="commit-msg">${escHtml(c.message)}</div>
                    <div class="commit-meta">
                        <span class="meta-chip"><i class="fa-solid fa-user"></i>${escHtml(c.auteur)}</span>
                        <span class="meta-chip"><i class="fa-regular fa-clock"></i>${date}</span>
                        <span class="commit-hash" onclick="openDiff('${c.hash}')" title="Voir les modifications">${c.hash}</span>
                        ${c.file_name ? `<a href="${escHtml(c.file_path)}" target="_blank" download="${escHtml(c.file_name)}" class="meta-chip" style="color:var(--primary);text-decoration:none;cursor:pointer;" title="Télécharger le fichier joint"><i class="fa-solid fa-paperclip"></i>${escHtml(c.file_name)}</a>` : ''}
                    </div>
                </div>
                <div class="commit-actions">
                    <button class="btn btn-secondary btn-sm" onclick="openDiff('${c.hash}')"><i class="fa-solid fa-file-lines"></i> Diff</button>
                    <button class="btn btn-danger btn-sm" onclick="confirmRestore('${c.hash}', '${escHtml(c.message).replace(/'/g,"\\'")}')">
                        <i class="fa-solid fa-rotate-left"></i> Restaurer
                    </button>
                </div>
            </div>`;
        });
        html += '</div>';
        container.innerHTML = html;
    } catch(e) { container.innerHTML = '<div class="empty-state"><i class="fa-solid fa-wifi"></i><p>Erreur réseau</p></div>'; }
}

// ── DIFF ──
async function openDiff(hash) {
    document.getElementById('diffModal').classList.add('show');
    document.getElementById('diffModalBody').innerHTML = '<div style="text-align:center;padding:2rem;"><div class="loader"></div></div>';
    document.getElementById('diffModalTitle').innerHTML = `<i class="fa-solid fa-file-lines"></i> Diff &mdash; <span style="color:var(--primary);font-family:monospace;">${hash}</span>`;

    try {
        const res = await fetch(`index.php?page=git&action=show-commit&id_conversation=${CONV_ID}&hash=${hash}`);
        const data = await res.json();
        if (data.error) { document.getElementById('diffModalBody').innerHTML = `<p style="color:var(--red);">${escHtml(data.error)}</p>`; return; }

        const added    = data.diff.added    || [];
        const removed  = data.diff.removed  || [];
        const modified = data.diff.modified || [];
        const total    = added.length + removed.length + modified.length;

        let html = `<div class="diff-header">
            <span>Branche : <strong>${escHtml(data.commit.branche)}</strong></span>
            <div class="diff-stats">
                <span class="diff-stat-add">+${added.length} ajouté(s)</span>
                <span class="diff-stat-del">-${removed.length} supprimé(s)</span>
                ${modified.length > 0 ? `<span style="color:var(--yellow);font-weight:600;">~${modified.length} modifié(s)</span>` : ''}
            </div>
        </div>`;

        if (total === 0) {
            html += '<div class="empty-state"><i class="fa-solid fa-equals"></i><p>Aucune différence — snapshot identique au précédent.</p></div>';
        }

        added.forEach(m => {
            html += `<div class="diff-block added">
                <div class="diff-label"><i class="fa-solid fa-plus" style="margin-right:.25rem;"></i>Message ajouté — Expéditeur #${m.id_expediteur}</div>
                <div class="diff-content">${parseMsgContent(m.contenu)}</div>
            </div>`;
        });
        removed.forEach(m => {
            html += `<div class="diff-block removed">
                <div class="diff-label"><i class="fa-solid fa-minus" style="margin-right:.25rem;"></i>Message supprimé — Expéditeur #${m.id_expediteur}</div>
                <div class="diff-content">${parseMsgContent(m.contenu)}</div>
            </div>`;
        });
        modified.forEach(m => {
            html += `<div class="diff-block modified">
                <div class="diff-label"><i class="fa-solid fa-pen" style="margin-right:.25rem;"></i>Message modifié — Expéditeur #${m.before.id_expediteur}</div>
                <div class="diff-content">
                    <div style="color:var(--red);text-decoration:line-through;margin-bottom:.35rem;">${parseMsgContent(m.before.contenu)}</div>
                    <div style="color:var(--green);">${parseMsgContent(m.after.contenu)}</div>
                </div>
            </div>`;
        });

        document.getElementById('diffModalBody').innerHTML = html;
    } catch(e) {
        document.getElementById('diffModalBody').innerHTML = '<p style="color:var(--red);">Erreur réseau</p>';
    }
}

function closeDiff() { document.getElementById('diffModal').classList.remove('show'); }

function parseMsgContent(raw) {
    try {
        const p = JSON.parse(raw);
        if (p && p.ephemeral) return `🔥 [Éphémère] ${escHtml(p.text || '')}`;
        if (p && p.type === 'file') return `📎 ${escHtml(p.name || 'Fichier')}`;
    } catch(e) {}
    return escHtml(raw);
}

// ── RESTORE ──
function confirmRestore(hash, msg) {
    restoreTargetHash = hash;
    document.getElementById('restoreHash').textContent = hash;
    document.getElementById('restoreMsg').textContent  = msg;
    document.getElementById('restoreModal').classList.add('show');
}
function closeRestoreModal() { document.getElementById('restoreModal').classList.remove('show'); restoreTargetHash = null; }

async function doRestore() {
    if (!restoreTargetHash) return;
    const btn = document.getElementById('btnDoRestore');
    btn.disabled = true;
    btn.innerHTML = '<span class="loader"></span> Restauration...';

    try {
        const res = await fetch('index.php?page=git&action=restore', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_conversation=${CONV_ID}&hash=${restoreTargetHash}`
        });
        const data = await res.json();
        if (data.success) {
            toast('Conversation restaurée avec succès', 'success');
            closeRestoreModal();
            await refreshCommits();
        } else {
            toast(data.error || 'Erreur lors de la restauration', 'error');
        }
    } catch(e) { toast('Erreur réseau', 'error'); }

    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-rotate-left"></i> Restaurer';
}

// ── BRANCHES ──
async function doCreateBranche() {
    const nom = document.getElementById('newBranchName').value.trim();
    if (!nom) { toast('Le nom de la branche est requis.', 'error'); return; }

    try {
        const res = await fetch('index.php?page=git&action=create-branche', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_conversation=${CONV_ID}&nom=${encodeURIComponent(nom)}`
        });
        const data = await res.json();
        if (data.success) {
            toast('Branche "' + data.nom + '" créée', 'success');
            document.getElementById('newBranchName').value = '';
            setTimeout(() => location.reload(), 800);
        } else {
            toast(data.error || 'Erreur', 'error');
        }
    } catch(e) { toast('Erreur réseau', 'error'); }
}

async function deleteBranche(nom) {
    if (nom === 'main') { toast('Impossible de supprimer la branche principale.', 'error'); return; }
    if (!confirm('Supprimer la branche "' + nom + '" et tous ses commits ?')) return;

    try {
        const res = await fetch('index.php?page=git&action=delete-branche', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_conversation=${CONV_ID}&nom=${encodeURIComponent(nom)}`
        });
        const data = await res.json();
        if (data.success) {
            toast('Branche "' + nom + '" supprimée', 'success');
            setTimeout(() => location.reload(), 600);
        } else {
            toast(data.error || 'Erreur', 'error');
        }
    } catch(e) { toast('Erreur réseau', 'error'); }
}

async function refreshBranchCounts() {
    try {
        const res = await fetch(`index.php?page=git&action=list-branches&id_conversation=${CONV_ID}`);
        const data = await res.json();
        if (!data.branches) return;
        data.branches.forEach(b => {
            const el = document.getElementById('bc-' + b.nom);
            if (el) el.textContent = b.nb_commits;
        });
    } catch(e) {}
}

// ── TOAST ──
function toast(message, type = 'success') {
    const el = document.createElement('div');
    el.className = `git-toast ${type}`;
    const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';
    el.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${message}</span>`;
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(() => el.remove(), 300); }, 3500);
}

// ── UTILS ──
function escHtml(str) {
    const d = document.createElement('div'); d.textContent = String(str || ''); return d.innerHTML;
}

document.getElementById('diffModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeDiff(); });
document.getElementById('restoreModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeRestoreModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeDiff(); closeRestoreModal(); } });

refreshBranchCounts();
</script>
</body>
</html>