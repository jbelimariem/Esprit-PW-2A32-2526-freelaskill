<?php
require_once __DIR__ . '/../../controllers/contratController.php';
$stats = getContratStatistics();
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Liste des contrats</title>
    <link rel="stylesheet" href="../Frontoffice/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; min-height: 100vh; background: var(--bg-main, #03060E); overflow-x: hidden; font-family: 'Inter', sans-serif; transition: background 0.3s, color 0.3s; color: var(--text-main, white); }
        .sidebar { width: 280px; background: var(--bg-sidebar, rgba(17, 24, 39, 0.4)); border-right: 1px solid var(--border-color, rgba(255,255,255,0.05)); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: background 0.3s; }
        .main-panel { margin-left: 280px; flex: 1; padding: 2rem 3rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition, 0.3s); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: var(--nav-hover, rgba(37,99,235,0.1)); color: var(--text-main, white); border-right: 4px solid var(--tech-blue); }
        .table-container { background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 24px; padding: 2rem; margin-bottom: 2rem; transition: background 0.3s; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 1.25rem 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .data-table th { color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        .data-table td { color: var(--text-main, white); }
        .btn-action { display: inline-block; padding: 0.5rem 1rem; border-radius: 999px; text-decoration: none; font-size: 0.85rem; border: none; cursor: pointer; }
        .btn-edit { background: rgba(37,99,235,0.15); color: var(--tech-blue); }
        .btn-delete { background: rgba(239,68,68,0.15); color: #F87171; }
        .btn-add { background: var(--tech-blue); color: white; padding: 0.8rem 1.5rem; text-decoration: none; border-radius: 999px; font-weight: 500; transition: 0.3s; }
        .btn-add:hover { opacity: 0.9; }
        .alert { padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; }
        .alert-success { background: rgba(34,197,94,0.15); color: #BBF7D0; }
        .alert-error { background: rgba(248,113,113,0.15); color: #fecaca; }
        .status-badge { padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }

        /* Light Mode Variables */
        body.light-mode {
            --bg-main: #f8fafc;
            --bg-sidebar: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --card-bg: #ffffff;
            --nav-hover: #f1f5f9;
        }

        .theme-toggle {
            cursor: pointer;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--text-muted);
            transition: var(--transition, 0.3s);
            font-size: 0.95rem;
            font-weight: 500;
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
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; letter-spacing: 1px;">Admin Control v3.0</p>
        </div>
        <a href="admin_dashboard.html" class="nav-item"><i class="fa-solid fa-cube w-5"></i> Dashboard Central</a>
        <a href="admin_approbations.html" class="nav-item"><i class="fa-solid fa-check-double w-5"></i> Validations</a>
        <a href="admin_litiges.html" class="nav-item"><i class="fa-solid fa-scale-balanced w-5"></i> Litiges</a>
        <a href="admin_archivage.html" class="nav-item"><i class="fa-solid fa-box-archive w-5"></i> Archivage</a>
        <a href="admin_rules_list.php" class="nav-item"><i class="fa-solid fa-gavel w-5"></i> Gestion des règles</a>
        <a href="admin_contrat.php" class="nav-item active"><i class="fa-solid fa-file-contract w-5"></i> Gestion des contrats</a>

        <div style="margin-top: auto;">
            <div class="theme-toggle" onclick="toggleTheme()">
                <i id="theme-icon" class="fa-solid fa-sun w-5"></i> Changer le thème
            </div>
        </div>
    </aside>

    <main class="main-panel">
        <div class="hero-glow-bg-2" style="top: 0; right: 0; opacity: 0.5;"></div>

        <a href="admin_contrat.php" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); color: white; border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; border: 1px solid rgba(255,255,255,0.1);" class="animate-fade-up"><i class="fa-solid fa-arrow-left"></i> Retour au menu</a>

        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h1 style="font-size: 2rem; color: var(--text-main, white);">Liste des <span style="color: var(--tech-blue)">contrats</span></h1>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button onclick="toggleStats()" class="btn-action" style="background: rgba(59,130,246,0.15); color: #3B82F6; border: none; cursor: pointer; padding: 0.8rem 1.5rem; border-radius: 999px; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-chart-pie"></i> Statistiques</button>
                <a href="admin_export_pdf.php?action=export_all" class="btn-action" style="background: rgba(168,85,247,0.15); color: #A855F7; text-decoration: none; padding: 0.8rem 1.5rem; border-radius: 999px; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-file-pdf"></i> Exporter Tous</a>
                <a href="admin_contrat_form.php" class="btn-add"><i class="fa-solid fa-plus"></i> Créer contrat</a>
                <div style="display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.05); padding: 0.5rem 1rem; border-radius: 999px; border: 1px solid rgba(255,255,255,0.05);">
                    <i class="fa-solid fa-user-shield" style="color: var(--tech-blue);"></i> <span style="font-size: 0.85rem; color: var(--text-muted);">SuperAdmin</span>
                </div>
            </div>
        </header>

        <!-- Statistiques Avancées (Cachées par défaut) -->
        <div id="stats-container" style="display: none; margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h2 style="font-size: 1.2rem; color: var(--text-muted); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 1px;"><i class="fa-solid fa-chart-line"></i> Vue d'ensemble de la plateforme</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <!-- Statistiques Générales -->
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
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 0.5rem 0;">Contrats Terminés</p>
                            <h3 style="font-size: 2rem; color: white; margin: 0;"><?php echo intval($stats['by_status']['termine']); ?></h3>
                        </div>
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(74,222,128,0.1); color: #4ADE80; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-check-double"></i></div>
                    </div>
                </div>

                <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem; border-bottom: 4px solid #F87171;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 0.5rem 0;">Contrats Annulés</p>
                            <h3 style="font-size: 2rem; color: white; margin: 0;"><?php echo intval($stats['by_status']['annule']); ?></h3>
                        </div>
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(248,113,113,0.1); color: #F87171; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-xmark"></i></div>
                    </div>
                </div>
            </div>

            <!-- Ligne 2 : Finances et Graphique -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                
                <!-- Statistiques Financières -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(16,185,129,0.1); color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-sack-dollar"></i></div>
                            <span style="color: var(--text-muted); font-size: 0.9rem;">Budget Global Généré</span>
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

                <!-- Graphique Répartition -->
                <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem; align-self: flex-start;">Répartition par Statut</span>
                    <div style="width: 100%; max-width: 200px; aspect-ratio: 1;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recherche et Tri -->
        <div style="background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem;" class="animate-fade-up delay-2">
            <form method="GET" action="admin_contrat_list.php" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
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
                            'date_creation' => 'Date de création',
                            'titre' => 'Titre',
                            'budget' => 'Budget',
                            'delai' => 'Délai',
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
                        <option value="DESC" <?php echo ($_GET['order'] ?? 'DESC') === 'DESC' ? 'selected' : ''; ?>>Décroissant</option>
                        <option value="ASC" <?php echo ($_GET['order'] ?? '') === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                    </select>
                </div>

                <button type="submit" style="background: var(--tech-blue); color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 12px; cursor: pointer; font-weight: 500;">Appliquer</button>
                <a href="admin_contrat_list.php" style="padding: 0.8rem 1.5rem; color: var(--text-muted); text-decoration: none; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;">Réinitialiser</a>
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
                if (isset($_GET['success'])) {
                    if ($_GET['success'] === 'delete') {
                        echo 'Contrat supprimé avec succès.';
                    } elseif ($_GET['success'] === 'archive') {
                        echo 'Contrat archivé avec succès.';
                    } elseif ($_GET['success'] === 'verify_ok') {
                        echo 'Le contrat est conforme aux règles.';
                    } else {
                        echo 'Action réalisée avec succès.';
                    }
                } else {
                    echo htmlspecialchars($successMessage ?: 'Action réalisée avec succès.', ENT_QUOTES, 'UTF-8');
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php 
                if ($_GET['error'] === 'verify_fail') {
                    echo 'Le contrat n\'est pas conforme (vérifiez le budget, le délai ou la description).';
                } else {
                    echo 'Une erreur est survenue.';
                }
                ?>
            </div>
        <?php endif; ?>

        <section class="table-container animate-fade-up delay-2">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Budget</th>
                        <th>Délai (jours)</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contrats)): ?>
                        <tr><td colspan="6" style="color:var(--text-muted); text-align:center;">Aucun contrat trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($contrats as $contrat): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($contrat['titre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($contrat['budget'], ENT_QUOTES, 'UTF-8'); ?> DT</td>
                                <td><?php echo htmlspecialchars($contrat['delai'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php
                                    $statutColors = [
                                        'brouillon' => ['bg' => 'rgba(156,163,175,0.15)', 'color' => '#9CA3AF'],
                                        'en_attente' => ['bg' => 'rgba(245,158,11,0.15)', 'color' => '#FBBF24'],
                                        'actif' => ['bg' => 'rgba(37,99,235,0.15)', 'color' => '#60A5FA'],
                                        'termine' => ['bg' => 'rgba(34,197,94,0.15)', 'color' => '#4ADE80'],
                                        'annule' => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#F87171'],
                                        'archive' => ['bg' => 'rgba(107,114,128,0.15)', 'color' => '#6B7280']
                                    ];
                                    $s = $contrat['statut'];
                                    $bg = $statutColors[$s]['bg'] ?? 'rgba(255,255,255,0.1)';
                                    $col = $statutColors[$s]['color'] ?? 'white';
                                    ?>
                                    <span class="status-badge" style="background: <?php echo $bg; ?>; color: <?php echo $col; ?>;">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $s)), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($contrat['date_creation'])); ?></td>
                                <td style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
                                    <a class="btn-action btn-edit" href="admin_contrat_form.php?action=edit&id=<?php echo intval($contrat['id_contrat']); ?>" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                                    
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="margin:0; display:inline;">
                                        <input type="hidden" name="action" value="archive">
                                        <input type="hidden" name="id" value="<?php echo intval($contrat['id_contrat']); ?>">
                                        <button class="btn-action" style="background: rgba(156,163,175,0.15); color: #9CA3AF;" type="submit" onclick="return confirm('Êtes-vous sûr de vouloir archiver ce contrat ?');" title="Archiver"><i class="fa-solid fa-box-archive"></i></button>
                                    </form>

                                    <a class="btn-action" style="background: rgba(16,185,129,0.15); color: #10B981;" href="admin_contrat_list.php?action=verify&id=<?php echo intval($contrat['id_contrat']); ?>" title="Vérifier conformité"><i class="fa-solid fa-clipboard-check"></i></a>

                                    <a class="btn-action" style="background: rgba(168,85,247,0.15); color: #A855F7;" href="admin_export_pdf.php?id=<?php echo intval($contrat['id_contrat']); ?>" title="Exporter PDF" target="_blank"><i class="fa-solid fa-file-pdf"></i></a>

                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="margin:0; display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo intval($contrat['id_contrat']); ?>">
                                        <button class="btn-action btn-delete" type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contrat ?');" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
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

        // Initialisation du graphique Chart.js pour la répartition par statut
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
                // Si aucune donnée, afficher un cercle vide
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
