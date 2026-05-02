<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header('Location: front_rules_role.php');
    exit;
}

require_once __DIR__ . '/../../controllers/contratController.php';

$stats = getContratStatistics();

$role = $_SESSION['user_role'];
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace <?php echo $roleName; ?> - Liste des contrats</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: var(--bg-main, #050812); font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; overflow-x: hidden; transition: background 0.3s, color 0.3s; color: var(--text-main, white); }
        .sidebar { width: 280px; background: var(--bg-sidebar, rgba(17, 24, 39, 0.4)); border-right: 1px solid var(--border-color, rgba(255,255,255,0.05)); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: background 0.3s; }
        .main-panel { margin-left: 280px; flex: 1; padding: 3rem 4rem; position: relative; }
        
        /* SIDEBAR NAV */
        .side-nav { padding: 0.75rem; display: flex; flex-direction: column; gap: 0.25rem; }
        .side-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm, 8px);
            color: var(--text-muted, #9ca3af);
            font-size: 0.88rem;
            transition: var(--transition, 0.3s);
            text-decoration: none;
        }
        .side-nav a:hover, .side-nav a.active { color: var(--text-main, white); background: var(--nav-hover, rgba(255,255,255,0.03)); }
        .side-nav a.active { border-left: 3px solid var(--tech-blue, #3b82f6); }
        .side-nav a.danger { color: var(--tunisian-red, #e3000f); }
        .side-nav a.danger:hover { background: rgba(239,68,68,0.1); }
        .nav-avatar.has-image {
            padding: 0;
            overflow: hidden;
            background: rgba(15,23,42,0.95);
        }
        .nav-avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 50%;
        }

        .btn-back { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: var(--btn-back-bg, rgba(255,255,255,0.05)); color: var(--text-main, white); border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; border: 1px solid var(--border-color, rgba(255,255,255,0.1)); }
        .btn-back:hover { background: var(--btn-back-hover, rgba(255,255,255,0.1)); }
        .breadcrumb { display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; }
        .breadcrumb a { color: var(--tech-blue); text-decoration: none; transition: color 0.3s; }
        .breadcrumb a:hover { color: #60A5FA; }
        .breadcrumb i { font-size: 0.8rem; }
        
        .contrats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem; }
        .contrat-card { background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem; display: flex; flex-direction: column; position: relative; overflow: hidden; transition: background 0.3s; }
        .contrat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--tech-blue); }
        .contrat-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
        .contrat-title { font-size: 1.2rem; font-weight: 600; color: var(--text-main, white); margin: 0; }
        .status-badge { padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
        .contrat-body { color: var(--text-muted); font-size: 0.9rem; flex: 1; }
        .contrat-meta { display: flex; gap: 1rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color, rgba(255,255,255,0.05)); font-size: 0.85rem; }
        .contrat-meta div { display: flex; align-items: center; gap: 0.4rem; color: var(--text-muted); }
        .contrat-actions { display: flex; gap: 0.5rem; margin-top: 1.5rem; }
        .btn-action { flex: 1; text-align: center; padding: 0.6rem; border-radius: 12px; font-size: 0.85rem; text-decoration: none; border: none; cursor: pointer; font-weight: 500; }
        .btn-edit { background: rgba(37,99,235,0.1); color: var(--tech-blue); }
        .btn-edit:hover { background: rgba(37,99,235,0.2); }
        .btn-delete { background: rgba(239,68,68,0.1); color: #F87171; }
        .btn-delete:hover { background: rgba(239,68,68,0.2); }
        .alert { padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; }
        .alert-success { background: rgba(34,197,94,0.15); color: #BBF7D0; }
        .alert-error { background: rgba(248,113,113,0.15); color: #fecaca; }

        /* Light Mode Variables */
        body.light-mode {
            --bg-main: #f8fafc;
            --bg-sidebar: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --card-bg: #ffffff;
            --nav-hover: #f1f5f9;
            --btn-back-bg: #e2e8f0;
            --btn-back-hover: #cbd5e1;
        }

        .theme-toggle {
            cursor: pointer;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-muted);
            border-radius: var(--radius-sm, 8px);
            transition: var(--transition, 0.3s);
            margin: 0 0.75rem;
            font-size: 0.88rem;
        }
        .theme-toggle:hover {
            background: var(--nav-hover, rgba(255,255,255,0.03));
            color: var(--text-main);
        }
    </style>
    <script>
        function toggleTheme() {
            document.body.classList.toggle('light-mode');
            const isLight = document.body.classList.contains('light-mode');
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
            const icon = document.getElementById('theme-icon');
            icon.className = isLight ? 'fa-solid fa-moon w-5' : 'fa-solid fa-sun w-5';
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('theme') === 'light') {
                document.body.classList.add('light-mode');
                document.getElementById('theme-icon').className = 'fa-solid fa-moon w-5';
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <aside class="sidebar animate-fade-up">
        <div style="padding: 0 2rem; margin-bottom: 3rem;">
            <div class="logo">
                <i class="fa-solid fa-shapes text-tech-blue" style="color: var(--tech-blue)"></i>
                Freela<span>Skill</span>
            </div>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; letter-spacing: 1px;">Espace <?php echo $roleName; ?></p>
        </div>
        
        <nav class="side-nav">
            <?php if ($isClient): ?>
                <a href="client_dashboard.html"><i class="fa-solid fa-chart-pie w-5"></i> Tableau de bord</a>
                <a href="publish_job.html"><i class="fa-solid fa-plus-circle w-5"></i> Lancer un Projet</a>
            <?php else: ?>
                <a href="freelancer_jobs.html"><i class="fa-solid fa-compass w-5"></i> Explorer Missions</a>
            <?php endif; ?>
            
            <a href="front_rules_index.php"><i class="fa-solid fa-gavel w-5"></i> Mes R+¿gles</a>
            <a href="front_contrat_index.php" class="active"><i class="fa-solid fa-file-contract w-5"></i> Mes Contrats</a>
        </nav>

        <div style="margin-top: auto; padding: 2rem 0;">
            <div class="theme-toggle" onclick="toggleTheme()">
                <i id="theme-icon" class="fa-solid fa-sun w-5"></i> Changer le th+¿me
            </div>
            <a href="front_rules_role.php" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; color: #F87171; text-decoration: none; font-size: 0.85rem; padding: 0.8rem; border: 1px solid rgba(248,113,113,0.3); border-radius: 999px; transition: 0.3s;">
                <i class="fa-solid fa-right-from-bracket"></i> Changer de profil
            </a>
        </div>
    </aside>
    
    <div class="hero-glow-bg-2" style="top: 10%; right: 0; opacity: 0.5;"></div>

    <main class="main-panel">
        
        <div class="breadcrumb animate-fade-up">
            <a href="#"><i class="fa-solid fa-home"></i> Accueil</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Espace <?php echo $roleName; ?></span>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="front_contrat_index.php">Gestion des contrats</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span style="color: white;">Liste</span>
        </div>

        <a href="front_contrat_index.php" class="btn-back animate-fade-up delay-1"><i class="fa-solid fa-arrow-left"></i> Retour au menu</a>

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h1 style="font-size: 2.5rem; color: white; margin: 0;">Mes <span style="color: var(--tech-blue)">Contrats</span></h1>
            <div style="display: flex; gap: 1rem;">
                <button onclick="toggleStats()" class="btn-action" style="background: rgba(59,130,246,0.15); color: #3B82F6; border: none; cursor: pointer; padding: 0.8rem 1.5rem; border-radius: 999px; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-chart-pie"></i> Statistiques</button>
                <a href="../Backoffice/admin_export_pdf.php?action=export_all" class="btn-action" style="background: rgba(168,85,247,0.15); color: #A855F7; text-decoration: none; padding: 0.8rem 1.5rem; border-radius: 999px; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-file-pdf"></i> Exporter Tous</a>
                <a href="front_contrat_form.php" style="background: var(--tech-blue); color: white; padding: 0.8rem 1.5rem; text-decoration: none; border-radius: 999px; font-weight: 500;"><i class="fa-solid fa-plus"></i> Nouveau Contrat</a>
            </div>
        </div>

        <!-- Statistiques Avanc+®es -->
        <div id="stats-container" style="display: none; margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h2 style="font-size: 1.2rem; color: var(--text-muted); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 1px;"><i class="fa-solid fa-chart-line"></i> Mon Activit+®</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <!-- Statistiques G+®n+®rales -->
                <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem; border-bottom: 4px solid var(--tech-blue);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 0.5rem 0;">Total Contrats</p>
                            <h3 style="font-size: 2rem; color: white; margin: 0;"><?php echo intval($stats['total']); ?></h3>
                        </div>
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(37,99,235,0.1); color: var(--tech-blue); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-file-contract"></i></div>
                    </div>
                </div>

                <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem; border-bottom: 4px solid #60A5FA;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 0.5rem 0;">Contrats Actifs</p>
                            <h3 style="font-size: 2rem; color: white; margin: 0;"><?php echo intval($stats['by_status']['actif']); ?></h3>
                        </div>
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(96,165,250,0.1); color: #60A5FA; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-spinner"></i></div>
                    </div>
                </div>

                <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem; border-bottom: 4px solid #4ADE80;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 0.5rem 0;">Contrats Termin+®s</p>
                            <h3 style="font-size: 2rem; color: white; margin: 0;"><?php echo intval($stats['by_status']['termine']); ?></h3>
                        </div>
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(74,222,128,0.1); color: #4ADE80; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-check-double"></i></div>
                    </div>
                </div>

                <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem; border-bottom: 4px solid #F87171;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 0.5rem 0;">Contrats Annul+®s</p>
                            <h3 style="font-size: 2rem; color: white; margin: 0;"><?php echo intval($stats['by_status']['annule']); ?></h3>
                        </div>
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(248,113,113,0.1); color: #F87171; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-xmark"></i></div>
                    </div>
                </div>
            </div>

            <!-- Ligne 2 : Finances et Graphique -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                
                <!-- Statistiques Financi+¿res -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(16,185,129,0.1); color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-sack-dollar"></i></div>
                            <span style="color: var(--text-muted); font-size: 0.9rem;">Budget Total</span>
                        </div>
                        <h3 style="font-size: 1.8rem; color: white; margin: 0;"><?php echo number_format($stats['total_budget'], 2, ',', ' '); ?> <span style="font-size: 1rem; color: var(--text-muted);">DT</span></h3>
                    </div>

                    <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(245,158,11,0.1); color: #F59E0B; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-scale-unbalanced"></i></div>
                            <span style="color: var(--text-muted); font-size: 0.9rem;">Budget Moyen / Contrat</span>
                        </div>
                        <h3 style="font-size: 1.8rem; color: white; margin: 0;"><?php echo number_format($stats['avg_budget'], 2, ',', ' '); ?> <span style="font-size: 1rem; color: var(--text-muted);">DT</span></h3>
                    </div>

                    <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(168,85,247,0.1); color: #A855F7; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-arrow-trend-up"></i></div>
                            <span style="color: var(--text-muted); font-size: 0.9rem;">Contrat le plus cher</span>
                        </div>
                        <h3 style="font-size: 1.8rem; color: white; margin: 0;"><?php echo number_format($stats['max_budget'], 2, ',', ' '); ?> <span style="font-size: 1rem; color: var(--text-muted);">DT</span></h3>
                    </div>

                    <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(100,116,139,0.1); color: #64748B; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-arrow-trend-down"></i></div>
                            <span style="color: var(--text-muted); font-size: 0.9rem;">Contrat le moins cher</span>
                        </div>
                        <h3 style="font-size: 1.8rem; color: white; margin: 0;"><?php echo number_format($stats['min_budget'], 2, ',', ' '); ?> <span style="font-size: 1rem; color: var(--text-muted);">DT</span></h3>
                    </div>
                </div>

                <!-- Graphique R+®partition -->
                <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem; align-self: flex-start;">R+®partition par Statut</span>
                    <div style="width: 100%; max-width: 200px; aspect-ratio: 1;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recherche et Tri -->
        <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem;" class="animate-fade-up delay-2">
            <form method="GET" action="front_contrat_list.php" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 250px;">
                    <label style="color: var(--text-muted); font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">Rechercher un contrat</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Titre ou description..." style="width: 100%; padding: 0.8rem 1rem 0.8rem 2.5rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: white;">
                    </div>
                </div>
                
                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">Trier par</label>
                    <select name="sort" style="padding: 0.8rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: white;">
                        <?php 
                        $sortOptions = [
                            'date_creation' => 'Date de cr+®ation',
                            'titre' => 'Titre',
                            'budget' => 'Budget',
                            'delai' => 'D+®lai',
                            'statut' => 'Statut'
                        ];
                        $currentSort = $_GET['sort'] ?? 'date_creation';
                        foreach ($sortOptions as $val => $label) {
                            $sel = ($val === $currentSort) ? 'selected' : '';
                            echo "<option value=\"$val\" $sel>$label</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">Ordre</label>
                    <select name="order" style="padding: 0.8rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: white;">
                        <option value="DESC" <?php echo ($_GET['order'] ?? 'DESC') === 'DESC' ? 'selected' : ''; ?>>D+®croissant</option>
                        <option value="ASC" <?php echo ($_GET['order'] ?? '') === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                    </select>
                </div>

                <button type="submit" style="background: var(--tech-blue); color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 12px; cursor: pointer; font-weight: 500;">Appliquer</button>
                <a href="front_contrat_list.php" style="padding: 0.8rem 1.5rem; color: var(--text-muted); text-decoration: none; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;">R+®initialiser</a>
            </form>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage) || isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php 
                if (isset($_GET['success']) && $_GET['success'] === 'delete') {
                    echo 'Contrat supprim+® avec succ+¿s.';
                } else {
                    echo htmlspecialchars($successMessage ?: 'Action r+®alis+®e avec succ+¿s.', ENT_QUOTES, 'UTF-8');
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="contrats-grid animate-fade-up delay-2">
            <?php if (empty($contrats)): ?>
                <p style="color: var(--text-muted); grid-column: 1/-1; text-align: center; padding: 3rem; background: rgba(255,255,255,0.02); border-radius: 20px;">Vous n'avez aucun contrat pour le moment.</p>
            <?php else: ?>
                <?php foreach ($contrats as $c): ?>
                    <div class="contrat-card">
                        <div class="contrat-header">
                            <h3 class="contrat-title"><?php echo htmlspecialchars($c['titre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <?php
                            $statutColors = [
                                'brouillon' => ['bg' => 'rgba(156,163,175,0.15)', 'color' => '#9CA3AF'],
                                'en_attente' => ['bg' => 'rgba(245,158,11,0.15)', 'color' => '#FBBF24'],
                                'actif' => ['bg' => 'rgba(37,99,235,0.15)', 'color' => '#60A5FA'],
                                'termine' => ['bg' => 'rgba(34,197,94,0.15)', 'color' => '#4ADE80'],
                                'annule' => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#F87171']
                            ];
                            $s = $c['statut'];
                            $bg = $statutColors[$s]['bg'] ?? 'rgba(255,255,255,0.1)';
                            $col = $statutColors[$s]['color'] ?? 'white';
                            ?>
                            <span class="status-badge" style="background: <?php echo $bg; ?>; color: <?php echo $col; ?>;">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $s)), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        
                        <div class="contrat-body">
                            <?php 
                                $desc = htmlspecialchars($c['description'], ENT_QUOTES, 'UTF-8');
                                echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;
                            ?>
                        </div>

                        <div class="contrat-meta">
                            <div><i class="fa-solid fa-coins"></i> <?php echo htmlspecialchars($c['budget'], ENT_QUOTES, 'UTF-8'); ?> DT</div>
                            <div><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($c['delai'], ENT_QUOTES, 'UTF-8'); ?> jours</div>
                            <div style="margin-left: auto;"><i class="fa-regular fa-calendar"></i> <?php echo date('d/m/Y', strtotime($c['date_creation'])); ?></div>
                        </div>

                        <div class="contrat-actions">
                            <a href="../Backoffice/admin_export_pdf.php?id=<?php echo intval($c['id_contrat']); ?>" class="btn-action" style="background: rgba(168,85,247,0.15); color: #A855F7; flex: none; width: 40px;" target="_blank" title="Exporter PDF"><i class="fa-solid fa-file-pdf"></i></a>
                            <a href="front_contrat_form.php?action=edit&id=<?php echo intval($c['id_contrat']); ?>" class="btn-action btn-edit">Modifier</a>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="margin:0; flex:1;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo intval($c['id_contrat']); ?>">
                                <button type="submit" class="btn-action btn-delete" style="width: 100%;" onclick="return confirm('+ètes-vous s++r de vouloir supprimer ce contrat ?');">Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleStats() {
            const statsContainer = document.getElementById('stats-container');
            if (statsContainer.style.display === 'none') {
                statsContainer.style.display = 'block';
            } else {
                statsContainer.style.display = 'none';
            }
        }

        // Initialisation du graphique Chart.js pour la r+®partition par statut
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('statusChart').getContext('2d');
            const statusData = <?php echo json_encode($stats['by_status']); ?>;
            
            // On filtre pour ne garder que les statuts qui ont au moins 1 contrat
            const labels = [];
            const data = [];
            const bgColors = [];
            
            const colorMap = {
                'brouillon': '#9CA3AF',
                'en_attente': '#FBBF24',
                'actif': '#60A5FA',
                'termine': '#4ADE80',
                'annule': '#F87171',
                'archive': '#6B7280'
            };

            for (const [key, value] of Object.entries(statusData)) {
                if (value > 0) {
                    labels.push(key.charAt(0).toUpperCase() + key.slice(1).replace('_', ' '));
                    data.push(value);
                    bgColors.push(colorMap[key] || '#ffffff');
                }
            }

            if (data.length > 0) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: bgColors,
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: document.body.classList.contains('light-mode') ? '#64748b' : '#9ca3af',
                                    usePointStyle: true,
                                    padding: 15,
                                    font: { size: 11, family: "'Inter', sans-serif" }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            } else {
                // Si aucune donn+®e, afficher un cercle vide
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Aucun'],
                        datasets: [{ data: [1], backgroundColor: ['#333333'], borderWidth: 0 }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } }, cutout: '70%' }
                });
            }
        });
    </script>
</body>
</html>

