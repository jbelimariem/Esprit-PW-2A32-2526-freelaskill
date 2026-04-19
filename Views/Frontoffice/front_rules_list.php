<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header('Location: front_rules_index.php');
    exit;
}
$role = $_SESSION['user_role'];
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';
$roleIcon = $isClient ? 'fa-user-tie' : 'fa-laptop-code';

require_once __DIR__ . '/../../controllers/ruleController.php';
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace <?php echo $roleName; ?> - Mes Règles</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #050812; font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; overflow-x: hidden; }
        
        /* SIDEBAR STYLES */
        .sidebar { width: 280px; background: rgba(17, 24, 39, 0.4); border-right: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .main-panel { margin-left: 280px; flex: 1; padding: 3rem 4rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(37,99,235,0.1); color: white; border-right: 4px solid var(--tech-blue); }

        .client-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: var(--radius-lg); padding: 1.5rem; transition: var(--transition); }
        .client-card:hover { border-color: rgba(255,255,255,0.1); transform: translateY(-3px); }
        .status-badge { padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
        .status-actif { background: rgba(34, 197, 94, 0.1); color: #4ADE80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .status-inactif { background: rgba(156, 163, 175, 0.1); color: #9CA3AF; border: 1px solid rgba(156, 163, 175, 0.3); }
        .btn-action { display: inline-block; padding: 0.5rem 1rem; border-radius: 999px; text-decoration: none; font-size: 0.85rem; border: none; cursor: pointer; }
        .btn-edit { background: rgba(37,99,235,0.15); color: var(--tech-blue); }
        .alert { padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; }
        .alert-success { background: rgba(34,197,94,0.15); color: #BBF7D0; }
        .breadcrumb { display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; }
        .breadcrumb a { color: var(--tech-blue); text-decoration: none; transition: color 0.3s; }
        .breadcrumb a:hover { color: #60A5FA; }
        .breadcrumb i { font-size: 0.8rem; }
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
        
        <a href="front_rules_index.php" class="nav-item active"><i class="fa-solid fa-gavel w-5"></i> Mes Règles</a>
        <a href="front_contrat_index.php" class="nav-item"><i class="fa-solid fa-file-contract w-5"></i> Mes Contrats</a>

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
            <a href="front_rules_index.php">Gestion des règles</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span style="color: white;">Liste des règles</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <div>
                <h1 style="font-size: 2.5rem; color: white; margin-bottom: 0.5rem;">Gestion des <span style="color: var(--tech-blue)">Règles</span></h1>
                <p style="color: var(--text-muted);">Créez et gérez vos règles de contrat et clauses personnalisées.</p>
            </div>
            <a href="front_rules_form.php" class="btn btn-primary" style="padding: 0.8rem 1.5rem;"><i class="fa-solid fa-plus"></i> Nouvelle Règle</a>
        </div>

        <?php if (!empty($successMessage) || isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php 
                if (isset($_GET['success']) && $_GET['success'] === 'create') echo 'Règle ajoutée avec succès.';
                elseif (isset($_GET['success']) && $_GET['success'] === 'update') echo 'Règle mise à jour avec succès.';
                else echo htmlspecialchars($successMessage ?: 'Action réalisée avec succès.', ENT_QUOTES, 'UTF-8');
                ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; gap: 1.5rem;" class="animate-fade-up delay-2">
            <?php if (empty($rules)): ?>
                <div class="client-card" style="text-align: center; padding: 3rem;">
                    <i class="fa-solid fa-gavel" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <h3 style="color: white; margin-bottom: 0.5rem;">Aucune règle n'a été trouvée</h3>
                    <p style="color: var(--text-muted);">Vous n'avez pas encore défini de règles. Commencez par en créer une !</p>
                </div>
            <?php else: ?>
                <?php foreach ($rules as $rule): ?>
                    <div class="client-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <h3 style="color: white; font-size: 1.25rem;"><?php echo htmlspecialchars($rule['titre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <?php if($rule['statut'] === 'actif'): ?>
                                    <span class="status-badge status-actif"><i class="fa-solid fa-check"></i> Actif</span>
                                <?php else: ?>
                                    <span class="status-badge status-inactif"><i class="fa-solid fa-xmark"></i> Inactif</span>
                                <?php endif; ?>
                            </div>
                            <span style="color: var(--text-muted); font-size: 0.9rem;">Contrat ID: <?php echo htmlspecialchars($rule['id_contrat'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <p style="color: var(--text-muted); margin-bottom: 1.5rem;"><?php echo htmlspecialchars($rule['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem;">
                            <span style="color: var(--text-muted);"><i class="fa-solid fa-tag"></i> Type: <span style="color: white;"><?php echo htmlspecialchars($rule['type'], ENT_QUOTES, 'UTF-8'); ?></span></span>
                            <div style="display: flex; gap: 0.5rem;">
                                <a class="btn-action btn-edit" href="front_rules_form.php?action=edit&id=<?php echo intval($rule['id_rule']); ?>"><i class="fa-solid fa-pen"></i> Modifier</a>
                                <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="margin:0; display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo intval($rule['id_rule']); ?>">
                                    <button class="btn-action" style="background: rgba(239,68,68,0.15); color: #F87171;" type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette règle ?');"><i class="fa-solid fa-trash"></i> Supprimer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
