<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header('Location: front_rules_role.php');
    exit;
}

require_once __DIR__ . '/../../controllers/contratController.php';

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
        body { background: #050812; font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; overflow-x: hidden; }
        .sidebar { width: 280px; background: rgba(17, 24, 39, 0.4); border-right: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .main-panel { margin-left: 280px; flex: 1; padding: 3rem 4rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(37,99,235,0.1); color: white; border-right: 4px solid var(--tech-blue); }
        .btn-back { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); color: white; border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; border: 1px solid rgba(255,255,255,0.1); }
        .btn-back:hover { background: rgba(255,255,255,0.1); }
        .breadcrumb { display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; }
        .breadcrumb a { color: var(--tech-blue); text-decoration: none; transition: color 0.3s; }
        .breadcrumb a:hover { color: #60A5FA; }
        .breadcrumb i { font-size: 0.8rem; }
        
        .contrats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem; }
        .contrat-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 1.5rem; display: flex; flex-direction: column; position: relative; overflow: hidden; }
        .contrat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--tech-blue); }
        .contrat-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
        .contrat-title { font-size: 1.2rem; font-weight: 600; color: white; margin: 0; }
        .status-badge { padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
        .contrat-body { color: var(--text-muted); font-size: 0.9rem; flex: 1; }
        .contrat-meta { display: flex; gap: 1rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.05); font-size: 0.85rem; }
        .contrat-meta div { display: flex; align-items: center; gap: 0.4rem; color: #9CA3AF; }
        .contrat-actions { display: flex; gap: 0.5rem; margin-top: 1.5rem; }
        .btn-action { flex: 1; text-align: center; padding: 0.6rem; border-radius: 12px; font-size: 0.85rem; text-decoration: none; border: none; cursor: pointer; font-weight: 500; }
        .btn-edit { background: rgba(37,99,235,0.1); color: var(--tech-blue); }
        .btn-edit:hover { background: rgba(37,99,235,0.2); }
        .btn-delete { background: rgba(239,68,68,0.1); color: #F87171; }
        .btn-delete:hover { background: rgba(239,68,68,0.2); }
        .alert { padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; }
        .alert-success { background: rgba(34,197,94,0.15); color: #BBF7D0; }
        .alert-error { background: rgba(248,113,113,0.15); color: #fecaca; }
    </style>
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
        
        <?php if ($isClient): ?>
            <a href="client_dashboard.html" class="nav-item"><i class="fa-solid fa-chart-pie w-5"></i> Tableau de bord</a>
            <a href="publish_job.html" class="nav-item"><i class="fa-solid fa-plus-circle w-5"></i> Lancer un Projet</a>
        <?php else: ?>
            <a href="freelancer_jobs.html" class="nav-item"><i class="fa-solid fa-compass w-5"></i> Explorer Missions</a>
        <?php endif; ?>
        
        <a href="front_rules_index.php" class="nav-item"><i class="fa-solid fa-gavel w-5"></i> Mes Règles</a>
        <a href="front_contrat_index.php" class="nav-item active"><i class="fa-solid fa-file-contract w-5"></i> Mes Contrats</a>

        <div style="margin-top: auto; padding: 2rem;">
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
            <h1 style="font-size: 2.5rem; color: white; margin: 0;">Liste des <span style="color: var(--tech-blue)">Contrats</span></h1>
            <a href="front_contrat_form.php" style="background: var(--tech-blue); color: white; padding: 0.8rem 1.5rem; text-decoration: none; border-radius: 999px; font-weight: 500;"><i class="fa-solid fa-plus"></i> Nouveau Contrat</a>
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
                    echo 'Contrat supprimé avec succès.';
                } else {
                    echo htmlspecialchars($successMessage ?: 'Action réalisée avec succès.', ENT_QUOTES, 'UTF-8');
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
                            <a href="front_contrat_form.php?action=edit&id=<?php echo intval($c['id_contrat']); ?>" class="btn-action btn-edit">Modifier</a>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="margin:0; flex:1;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo intval($c['id_contrat']); ?>">
                                <button type="submit" class="btn-action btn-delete" style="width: 100%;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contrat ?');">Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
