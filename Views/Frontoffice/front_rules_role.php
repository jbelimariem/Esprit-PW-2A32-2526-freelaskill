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
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Règles - Sélection du profil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #050812; display: flex; flex-direction: column; min-height: 100vh; font-family: 'Inter', sans-serif; }
        .hero-section { flex: 1; display: flex; align-items: center; justify-content: center; position: relative; z-index: 10; padding: 2rem; }
        .role-cards-container { display: flex; gap: 2rem; max-width: 900px; width: 100%; justify-content: center; flex-wrap: wrap; }
        
        .role-card { 
            background: rgba(255,255,255,0.02); 
            border: 1px solid rgba(255,255,255,0.05); 
            border-radius: 24px; 
            padding: 3rem 2rem; 
            text-align: center; 
            flex: 1; 
            min-width: 300px;
            cursor: pointer; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        .role-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: radial-gradient(circle at center, rgba(37,99,235,0.1) 0%, transparent 70%); opacity: 0; transition: opacity 0.4s ease;
        }
        .role-card:hover { 
            border-color: var(--tech-blue); 
            transform: translateY(-5px); 
            box-shadow: 0 10px 30px -10px rgba(37,99,235,0.3);
        }
        .role-card:hover::before { opacity: 1; }
        
        .role-icon { 
            font-size: 3.5rem; 
            color: var(--tech-blue); 
            margin-bottom: 1.5rem; 
            display: inline-block;
            background: rgba(37,99,235,0.1);
            width: 100px; height: 100px;
            line-height: 100px;
            border-radius: 50%;
        }
        .role-card.freelancer .role-icon {
            color: #A855F7;
            background: rgba(168,85,247,0.1);
        }
        .role-card.freelancer:hover {
            border-color: #A855F7;
            box-shadow: 0 10px 30px -10px rgba(168,85,247,0.3);
        }
        .role-card.freelancer::before {
            background: radial-gradient(circle at center, rgba(168,85,247,0.1) 0%, transparent 70%);
        }
        
        .role-title { color: white; font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; position: relative; z-index: 1; }
        .role-desc { color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; position: relative; z-index: 1; }
    </style>
</head>
<body>
    <nav class="navbar animate-fade-up">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="#" class="logo">
                <i class="fa-solid fa-shapes text-tech-blue" style="color: var(--tech-blue)"></i>
                Freela<span>Skill</span>
            </a>
        </div>
    </nav>
    
    <div class="hero-glow-bg-2" style="top: 20%; left: 30%;"></div>

    <main class="hero-section">
        <div style="width: 100%; max-width: 900px;">
            <div style="text-align: center; margin-bottom: 4rem;" class="animate-fade-up">
                <h1 style="font-size: 3rem; color: white; margin-bottom: 1rem;">Bienvenue sur l'espace <span style="color: var(--tech-blue)">Règles</span></h1>
                <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Pour vous proposer une expérience personnalisée, veuillez indiquer votre profil.</p>
            </div>
            
            <div class="role-cards-container animate-fade-up delay-1">
                <a href="front_rules_role.php?role=client" class="role-card">
                    <div class="role-icon"><i class="fa-solid fa-user-tie"></i></div>
                    <h2 class="role-title">Je suis Client</h2>
                    <p class="role-desc">Définissez vos règles, clauses de confidentialité, et exigences pour vos futurs projets ou contrats en cours.</p>
                </a>
                
                <a href="front_rules_role.php?role=freelancer" class="role-card freelancer">
                    <div class="role-icon"><i class="fa-solid fa-laptop-code"></i></div>
                    <h2 class="role-title">Je suis Freelancer</h2>
                    <p class="role-desc">Ajoutez vos propres règles de travail, conditions de livraison ou exigences techniques pour vos missions.</p>
                </a>
            </div>
        </div>
    </main>
</body>
</html>
