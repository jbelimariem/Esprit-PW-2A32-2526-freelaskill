<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header('Location: front_rules_role.php');
    exit;
}
$role = $_SESSION['user_role'];
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';
$roleIcon = $isClient ? 'fa-user-tie' : 'fa-laptop-code';
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace <?php echo $roleName; ?> - Menu Règles</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #050812; font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; overflow-x: hidden; }
        
        /* SIDEBAR STYLES */
        .sidebar { width: 280px; background: rgba(17, 24, 39, 0.4); border-right: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .main-panel { margin-left: 280px; flex: 1; padding: 3rem 4rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(37,99,235,0.1); color: white; border-right: 4px solid var(--tech-blue); }

        .menu-container { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 24px; padding: 3rem; margin-top: 2rem; max-width: 800px; width: 100%; margin-left: auto; margin-right: auto; }
        .action-button { 
            display: block; 
            width: 100%; 
            padding: 1.5rem; 
            margin-bottom: 1.5rem; 
            border-radius: 999px; 
            text-align: center; 
            font-size: 1.1rem; 
            font-weight: 600; 
            text-decoration: none; 
            transition: all 0.3s ease; 
        }
        .btn-primary-action { 
            background: #2563EB; 
            color: white; 
            box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.39); 
        }
        .btn-primary-action:hover { 
            background: #1D4ED8; 
            transform: translateY(-2px); 
        }
        .btn-secondary-action { 
            background: rgba(255,255,255,0.05); 
            color: white; 
            border: 1px solid rgba(255,255,255,0.1); 
        }
        .btn-secondary-action:hover { 
            background: rgba(255,255,255,0.1); 
            transform: translateY(-2px); 
        }
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
            <span style="color: white;">Gestion des règles</span>
        </div>

        <div style="text-align: center; margin-bottom: 2rem;" class="animate-fade-up delay-1">
            <h1 style="font-size: 2.5rem; color: white; margin-bottom: 0.5rem;">Gestion des <span style="color: var(--tech-blue)">règles</span></h1>
            <p style="color: var(--text-muted);">Sélectionnez votre action : créer un nouveau formulaire ou consulter la liste.</p>
        </div>

        <section class="menu-container animate-fade-up delay-2">
            <a href="front_rules_form.php" class="action-button btn-primary-action">
                Ouvrir la formulaire de règle
            </a>
            <a href="front_rules_list.php" class="action-button btn-secondary-action">
                Voir la liste des règles
            </a>
        </section>
    </main>
</body>
</html>
