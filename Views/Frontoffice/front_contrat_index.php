<?php
session_start();
// Vérifier si le rôle a été choisi
if (!isset($_SESSION['user_role'])) {
    header('Location: front_rules_role.php'); // on utilise le même sélecteur de rôle pour l'instant
    exit;
}

$role = $_SESSION['user_role'];
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace <?php echo $roleName; ?> - Mes Contrats</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #050812; font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; overflow-x: hidden; }
        .sidebar { width: 280px; background: rgba(17, 24, 39, 0.4); border-right: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .main-panel { margin-left: 280px; flex: 1; padding: 3rem 4rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(37,99,235,0.1); color: white; border-right: 4px solid var(--tech-blue); }
        .action-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 24px; padding: 3rem; text-align: center; transition: all 0.3s ease; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 1rem; }
        .action-card:hover { transform: translateY(-5px); background: rgba(255,255,255,0.04); border-color: rgba(37,99,235,0.3); box-shadow: 0 10px 30px -10px rgba(37,99,235,0.2); }
        .action-icon { width: 80px; height: 80px; border-radius: 50%; background: rgba(37,99,235,0.1); color: var(--tech-blue); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1rem; }
        .action-title { color: white; font-size: 1.5rem; font-weight: 600; }
        .action-desc { color: var(--text-muted); font-size: 0.95rem; line-height: 1.5; }
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
        <header style="margin-bottom: 4rem;" class="animate-fade-up">
            <h1 style="font-size: 3rem; color: white; margin-bottom: 1rem;">Mes <span style="color: var(--tech-blue)">Contrats</span></h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 600px;">Gérez vos contrats en cours, suivez leur statut et créez-en de nouveaux pour vos collaborations.</p>
        </header>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;" class="animate-fade-up delay-1">
            <a href="front_contrat_form.php" class="action-card">
                <div class="action-icon">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <div class="action-title">Créer un Contrat</div>
                <div class="action-desc">Rédigez un nouveau contrat en définissant le budget, le délai et les conditions.</div>
            </a>

            <a href="front_contrat_list.php" class="action-card">
                <div class="action-icon">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <div class="action-title">Consulter mes Contrats</div>
                <div class="action-desc">Accédez à la liste de tous vos contrats, visualisez leur statut actuel et effectuez des modifications.</div>
            </a>
        </div>
    </main>
</body>
</html>
