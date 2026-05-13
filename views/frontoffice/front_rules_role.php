<?php
session_start();
unset($_SESSION['user_role']);
if (isset($_GET['role']) && in_array($_GET['role'], ['client', 'freelancer'])) {
    $_SESSION['user_role'] = $_GET['role'];
    header('Location: front_rules_index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir votre profil Â· FreelaSkill</title>
    <link rel="stylesheet" href="css/front.css?v=1778626722">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="css/front.js" defer></script>
    <style>
        body { justify-content: center; align-items: center; min-height: 100vh; }
        .role-page { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; position: relative; }
        .role-logo { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2.5rem; }
        .role-logo-text { font-family: 'Space Grotesk', sans-serif; font-size: 1.8rem; font-weight: 700; color: var(--text-light); }
        .role-logo-text span { color: var(--tech-blue); }
        .role-header { text-align: center; margin-bottom: 3rem; }
        .role-title { font-family: 'Space Grotesk', sans-serif; font-size: 2rem; font-weight: 700; color: var(--text-light); margin-bottom: 0.75rem; }
        .role-title span { color: var(--tech-blue); }
        .role-subtitle { font-size: 0.95rem; color: var(--text-muted); max-width: 460px; margin: 0 auto; line-height: 1.6; }
        .role-cards { display: flex; gap: 1.5rem; flex-wrap: wrap; justify-content: center; max-width: 680px; width: 100%; }
    </style>
</head>
<body>




<div class="role-page">

    <div class="role-header animate-in">
        <div class="role-logo">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L18 10H6L12 2Z" fill="#EF4444"/>
                <circle cx="6" cy="18" r="4" fill="#EF4444"/>
                <rect x="14" y="14" width="8" height="8" rx="1.5" fill="#EF4444"/>
            </svg>
            <div class="role-logo-text">Freela<span>Skill</span></div>
        </div>
        <h1 class="role-title">Bienvenue sur votre <span>Espace</span></h1>
        <p class="role-subtitle">Pour vous proposer une expÃ©rience personnalisÃ©e, veuillez indiquer votre profil.</p>
    </div>

    <div class="role-cards animate-in delay-1">

        <a href="front_rules_role.php?role=client" class="menu-card" style="--card-glow:rgba(37,99,235,0.15);min-width:280px;max-width:300px;text-align:center;align-items:center;">
            <div class="sidebar-avatar" style="margin:0 auto;">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div>
                <div class="menu-card-title">Je suis Client</div>
                <div class="menu-card-desc">DÃ©finissez vos rÃ¨gles, clauses de confidentialitÃ© et exigences pour vos futurs projets.</div>
            </div>
            <div class="menu-card-arrow" style="color:var(--tech-blue);justify-content:center;">
                Continuer <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

        <a href="front_rules_role.php?role=freelancer" class="menu-card" style="--card-glow:rgba(168,85,247,0.15);min-width:280px;max-width:300px;text-align:center;align-items:center;">
            <div class="sidebar-avatar" style="margin:0 auto;background:linear-gradient(135deg,#A855F7,#6366F1);box-shadow:0 0 0 4px rgba(168,85,247,0.2),0 0 20px rgba(168,85,247,0.3);">
                <i class="fa-solid fa-laptop-code"></i>
            </div>
            <div>
                <div class="menu-card-title">Je suis Freelancer</div>
                <div class="menu-card-desc">Ajoutez vos propres rÃ¨gles de travail, conditions de livraison ou exigences techniques.</div>
            </div>
            <div class="menu-card-arrow" style="color:#A855F7;justify-content:center;">
                Continuer <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

    </div>

</div>
</body>
</html>

