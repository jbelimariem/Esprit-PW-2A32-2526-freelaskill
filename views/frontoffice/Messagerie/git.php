<?php
/**
 * views/frontoffice/Messagerie/git.php
 * Professional Git-style interface for conversation audit logs
 */
$commitCount = isset($commits) ? count($commits) : 0;
$branchCount = 1; // Hardcoded for now
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Git — Conversation #<?= $id_conversation ?> | FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #060a18;
            --bg-secondary: #0a1020;
            --bg-card: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.07);
            --tech-blue: #3b82f6;
            --tech-blue-hover: #2563eb;
            --text-muted: #8b9cb8;
            --git-purple: #bc8cff;
            --radius-md: 12px;
            --radius-lg: 18px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background: var(--bg-dark);
            color: white;
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            background: rgba(2, 6, 23, 0.95);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            flex-shrink: 0;
        }

        .btn-retour {
            display: flex; align-items: center; gap: 0.5rem;
            color: var(--text-muted); text-decoration: none;
            font-size: 0.85rem; margin-bottom: 2rem;
            transition: color 0.2s;
        }
        .btn-retour:hover { color: white; }

        .sidebar-section-label {
            font-size: 0.65rem; text-transform: uppercase;
            color: #475569; letter-spacing: 1.5px;
            font-weight: 800; margin-bottom: 0.75rem;
        }

        .nav-item {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1rem; border-radius: 10px;
            color: var(--text-muted); text-decoration: none;
            font-size: 0.88rem; transition: all 0.2s;
            margin-bottom: 0.25rem;
        }
        .nav-item:hover, .nav-item.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--tech-blue);
        }
        .nav-item .badge {
            margin-left: auto;
            background: rgba(59, 130, 246, 0.15);
            padding: 2px 8px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 700;
        }

        .branch-item {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.6rem 1rem; color: var(--text-muted);
            font-size: 0.82rem; cursor: pointer;
        }
        .branch-item i { color: var(--tech-blue); font-size: 0.4rem; }
        .branch-item.active { color: white; font-weight: 600; }

        /* ── Main Panel ── */
        .main-panel {
            flex: 1; display: flex; flex-direction: column;
            overflow: hidden;
        }

        /* ── Header ── */
        .header {
            height: 70px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2rem; background: rgba(8, 12, 28, 0.5);
            backdrop-filter: blur(10px);
        }
        .header-title { display: flex; align-items: center; gap: 1rem; }
        .logo-wrap {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: white;
        }
        .logo-wrap i { font-size: 1.2rem; color: white; }
        .conv-name { font-weight: 700; font-size: 1.1rem; }
        .branch-pill {
            background: rgba(188, 140, 255, 0.1);
            border: 1px solid rgba(188, 140, 255, 0.2);
            color: var(--git-purple);
            padding: 0.2rem 0.7rem; border-radius: 999px;
            font-size: 0.75rem; display: flex; align-items: center; gap: 0.4rem;
        }

        .header-stats { display: flex; gap: 1rem; }
        .stat-pill {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.4rem 0.8rem; border-radius: 8px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            font-size: 0.8rem; color: var(--text-muted);
        }
        .stat-pill b { color: white; }

        /* ── Content ── */
        .content {
            flex: 1; overflow-y: auto; padding: 2rem;
            display: flex; flex-direction: column; gap: 2rem;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
        }

        /* ── Commit Form ── */
        .commit-form { display: flex; flex-direction: column; gap: 1.25rem; }
        .form-title {
            display: flex; align-items: center; gap: 0.5rem;
            color: var(--tech-blue); font-weight: 700; font-size: 0.95rem;
        }
        .form-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;
        }
        .form-group label {
            display: block; font-size: 0.75rem; color: var(--text-muted);
            margin-bottom: 0.5rem; font-weight: 600;
        }
        .form-group label i { margin-right: 0.4rem; color: var(--tech-blue); }
        
        .form-input, .form-select {
            width: 100%; background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border); border-radius: 8px;
            padding: 0.75rem 1rem; color: white;
            font-family: inherit; font-size: 0.9rem;
            outline: none; transition: border-color 0.2s;
        }
        .form-input:focus, .form-select:focus { border-color: rgba(59, 130, 246, 0.5); }

        .file-upload-wrap {
            display: flex; align-items: center; gap: 1rem;
            margin-top: 0.5rem;
        }
        .btn-file {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: var(--tech-blue); padding: 0.5rem 1rem;
            border-radius: 6px; font-size: 0.8rem; cursor: pointer;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .file-name { font-size: 0.8rem; color: var(--text-muted); }

        .btn-submit {
            align-self: flex-end;
            background: var(--tech-blue);
            color: white; border: none;
            padding: 0.65rem 1.75rem; border-radius: 8px;
            font-weight: 700; font-size: 0.9rem; cursor: pointer;
            display: flex; align-items: center; gap: 0.5rem;
            transition: all 0.2s;
        }
        .btn-submit:hover { background: var(--tech-blue-hover); transform: translateY(-1px); }

        /* ── History List ── */
        .history-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .history-title {
            font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 0.75rem;
        }
        .history-title i { color: var(--tech-blue); }
        .history-title span { color: var(--tech-blue); }

        .btn-refresh {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: var(--text-muted); padding: 0.4rem 0.9rem;
            border-radius: 6px; font-size: 0.8rem; cursor: pointer;
            display: flex; align-items: center; gap: 0.5rem;
        }

        .commit-list { display: flex; flex-direction: column; gap: 1rem; }
        
        .commit-item {
            padding: 1rem; background: rgba(0, 0, 0, 0.15);
            border: 1px solid var(--border); border-radius: 12px;
            transition: border-color 0.2s;
        }
        .commit-item:hover { border-color: rgba(255, 255, 255, 0.15); }

        .commit-top { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
        .commit-author { font-weight: 700; font-size: 0.9rem; }
        .commit-hash { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--tech-blue); }
        
        .commit-text { font-size: 0.88rem; color: rgba(255, 255, 255, 0.85); line-height: 1.5; margin-bottom: 0.75rem; }

        .commit-meta { display: flex; gap: 1rem; font-size: 0.72rem; color: var(--text-muted); }

        .empty-state {
            padding: 4rem; text-align: center; color: var(--text-muted);
        }
        .empty-state i { font-size: 3rem; margin-bottom: 1.5rem; opacity: 0.2; display: block; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 3px; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <a href="#" onclick="window.close()" class="btn-retour">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>

        <div class="sidebar-section-label">Navigation</div>
        <a href="#" class="nav-item active">
            <i class="fa-solid fa-history"></i> Historique
            <span class="badge"><?= $commitCount ?></span>
        </a>
        <a href="#" class="nav-item">
            <i class="fa-solid fa-code-branch"></i> Branches
            <span class="badge"><?= $branchCount ?></span>
        </a>

        <div class="sidebar-section-label" style="margin-top: 2rem;">Branches</div>
        <div class="branch-item active">
            <i class="fa-solid fa-circle"></i> main
            <span style="margin-left: auto; color: #475569; font-size: 0.7rem;"><?= $commitCount ?></span>
        </div>
    </aside>

    <main class="main-panel">
        <header class="header">
            <div class="header-title">
                <?php 
                $initial = strtoupper(substr($convName ?? 'C', 0, 1));
                $colors = ['#f44336','#e91e63','#9c27b0','#673ab7','#3f51b5','#2196f3','#03a9f4','#00bcd4','#009688','#4caf50','#8bc34a','#cddc39','#ffc107','#ff9800','#ff5722'];
                $bgColor = $colors[ord($initial) % count($colors)];
                ?>
                <div class="logo-wrap" style="background: <?= $bgColor ?>;"><?= $initial ?></div>
                <div class="conv-name"><?= htmlspecialchars($convName ?? 'Conversation') ?></div>
                <div class="branch-pill"><i class="fa-solid fa-code-branch"></i> main</div>
            </div>

            <div class="header-stats">
                <div class="stat-pill"><b><?= $commitCount ?></b> commits</div>
                <div class="stat-pill"><b><?= $branchCount ?></b> branches</div>
            </div>
        </header>

        <div class="content">
            
            <!-- Nouveau Commit Form -->
            <div class="glass-card">
                <form id="commitForm" class="commit-form">
                    <div class="form-title"><i class="fa-solid fa-plus"></i> Nouveau commit</div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fa-solid fa-code-branch"></i> Branche cible</label>
                            <select name="branch" class="form-select">
                                <option value="main">main</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-comment-dots"></i> Message de commit</label>
                            <input type="text" name="message" class="form-input" placeholder="ex : correction affichage messages" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fa-solid fa-paperclip"></i> Fichier joint <span style="font-weight: 400;">(optionnel, max 10 Mo)</span></label>
                        <div class="file-upload-wrap">
                            <input type="file" id="fileInput" name="file" style="display: none;" onchange="updateFileName(this)">
                            <button type="button" class="btn-file" onclick="document.getElementById('fileInput').click()">
                                <i class="fa-solid fa-upload"></i> Choisir un fichier
                            </button>
                            <span class="file-name" id="fileNameDisplay">Aucun fichier sélectionné</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-check"></i> Enregistrer
                    </button>
                </form>
            </div>

            <!-- Historique Section -->
            <div>
                <div class="history-header">
                    <div class="history-title">
                        <i class="fa-solid fa-history"></i> Historique — <span>main</span>
                    </div>
                    <button class="btn-refresh" onclick="location.reload()">
                        <i class="fa-solid fa-rotate"></i> Actualiser
                    </button>
                </div>

                <div class="commit-list">
                    <?php if (empty($commits)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-history"></i>
                            <p>Aucun commit sur cette branche.</p>
                            <p style="font-size: 0.8rem; margin-top: 0.5rem;">Commencez par enregistrer l'état actuel de la conversation.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_reverse($commits) as $commit): 
                            $data = json_decode($commit['contenu'], true);
                            $msg  = $data['message'] ?? 'Sans message';
                            $hash = substr(md5($commit['id_message'] . $commit['date_envoi']), 0, 7);
                            $author = trim(($commit['prenom'] ?? '') . ' ' . ($commit['nom'] ?? '')) ?: 'User #' . $commit['id_expediteur'];
                        ?>
                            <div class="commit-item">
                                <div class="commit-top">
                                    <div class="commit-author"><?= htmlspecialchars($author) ?></div>
                                    <div class="commit-hash"><?= $hash ?></div>
                                </div>
                                <div class="commit-text"><?= nl2br(htmlspecialchars($msg)) ?></div>
                                <?php if (!empty($data['file'])): ?>
                                    <a href="<?= htmlspecialchars($data['file']['url']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--tech-blue); font-size: 0.8rem; text-decoration: none; margin-bottom: 0.75rem;">
                                        <i class="fa-solid fa-paperclip"></i>
                                        <?= htmlspecialchars($data['file']['name']) ?> (<?= round($data['file']['size']/1024, 1) ?> KB)
                                    </a>
                                <?php endif; ?>
                                <div class="commit-meta">
                                    <span><i class="fa-regular fa-clock"></i> <?= date('d/m/Y H:i', strtotime($commit['date_envoi'])) ?></span>
                                    <span><i class="fa-solid fa-code-branch"></i> <?= htmlspecialchars($data['branch'] ?? 'main') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <script>
        function updateFileName(input) {
            const display = document.getElementById('fileNameDisplay');
            if (input.files && input.files[0]) {
                display.textContent = input.files[0].name;
            } else {
                display.textContent = 'Aucun fichier sélectionné';
            }
        }

        document.getElementById('commitForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('.btn-submit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement...';

            const formData = new FormData(this);
            
            fetch('messagerie_index.php?page=git&action=commit&id_conversation=<?= $id_conversation ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Une erreur est survenue');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Enregistrer';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erreur réseau');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Enregistrer';
            });
        });
    </script>
</body>
</html>
