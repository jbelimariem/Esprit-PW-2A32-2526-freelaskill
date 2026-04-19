<?php
require_once __DIR__ . '/../../controllers/contratController.php';
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
        body { display: flex; min-height: 100vh; background: #03060E; overflow-x: hidden; font-family: 'Inter', sans-serif; }
        .sidebar { width: 280px; background: rgba(17, 24, 39, 0.4); border-right: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .main-panel { margin-left: 280px; flex: 1; padding: 2rem 3rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(37,99,235,0.1); color: white; border-right: 4px solid var(--tech-blue); }
        .table-container { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 24px; padding: 2rem; margin-bottom: 2rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 1.25rem 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .data-table th { color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        .data-table td { color: white; }
        .btn-action { display: inline-block; padding: 0.5rem 1rem; border-radius: 999px; text-decoration: none; font-size: 0.85rem; border: none; cursor: pointer; }
        .btn-edit { background: rgba(37,99,235,0.15); color: var(--tech-blue); }
        .btn-delete { background: rgba(239,68,68,0.15); color: #F87171; }
        .btn-add { background: var(--tech-blue); color: white; padding: 0.8rem 1.5rem; text-decoration: none; border-radius: 999px; font-weight: 500; transition: 0.3s; }
        .btn-add:hover { opacity: 0.9; }
        .alert { padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; }
        .alert-success { background: rgba(34,197,94,0.15); color: #BBF7D0; }
        .alert-error { background: rgba(248,113,113,0.15); color: #fecaca; }
        .status-badge { padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
    </style>
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
    </aside>

    <main class="main-panel">
        <div class="hero-glow-bg-2" style="top: 0; right: 0; opacity: 0.5;"></div>

        <a href="admin_contrat.php" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); color: white; border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; border: 1px solid rgba(255,255,255,0.1);" class="animate-fade-up"><i class="fa-solid fa-arrow-left"></i> Retour au menu</a>

        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h1 style="font-size: 2rem; color: white;">Liste des <span style="color: var(--tech-blue)">contrats</span></h1>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="admin_contrat_form.php" class="btn-add"><i class="fa-solid fa-plus"></i> Créer contrat</a>
                <div style="display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.05); padding: 0.5rem 1rem; border-radius: 999px; border: 1px solid rgba(255,255,255,0.05);">
                    <i class="fa-solid fa-user-shield" style="color: var(--tech-blue);"></i> <span style="font-size: 0.85rem; color: var(--text-muted);">SuperAdmin</span>
                </div>
            </div>
        </header>

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
</body>
</html>
