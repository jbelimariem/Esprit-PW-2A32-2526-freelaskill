<?php
session_start();
// Si déjà connecté → profil
if (!empty($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../assets/theme-init.js"></script>
    <title>Découvrir — FreelaSkill</title>
    <meta name="description" content="Découvrez FreelaSkill, la plateforme ultime pour les freelances et les clients.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/theme.js" defer></script>
    <style>
        .discover-wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .hero-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 2rem;
            position: relative;
            z-index: 1;
        }

        .hero-content {
            max-width: 850px;
            animation: fadeUp 0.8s ease forwards;
        }

        .action-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-top: 3.5rem;
            animation: fadeUp 1s ease forwards;
            animation-delay: 0.2s;
            opacity: 0;
        }

        .btn-large {
            padding: 1.1rem 2.5rem;
            font-size: 1.05rem;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto 5rem;
            padding: 0 2rem;
            position: relative;
            z-index: 1;
            animation: fadeUp 1.2s ease forwards;
            animation-delay: 0.4s;
            opacity: 0;
        }

        .feature-card {
            background: var(--surface-1);
            border: 1px solid var(--border);
            padding: 2.5rem 2rem;
            border-radius: var(--radius-lg);
            text-align: center;
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: var(--border-hover);
            background: var(--surface-2);
            box-shadow: var(--card-shadow), var(--neon-blue);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--tech-blue), #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-strong);
        }

        .feature-desc {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* ── ANIMATIONS FIXES ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

    </style>
</head>
<body class="page-anim">

<div class="discover-wrapper">
    <!-- Navbar -->
    <nav>
        <div class="logo">
            <i class="fa-solid fa-shapes"></i>
            Freela<span>Skill</span>
        </div>
        <div class="nav-right">
            <button type="button" class="theme-toggle" data-theme-toggle>
                <i class="fa-solid fa-sun" data-theme-icon></i>
                <span data-theme-label>Jour</span>
            </button>
            <a href="login.php" class="btn btn-outline" style="border: 1px solid rgba(255,255,255,0.2);">Log in</a>
            <a href="register.php" class="btn btn-primary">Sign up</a>
        </div>
    </nav>

    <!-- Background Glows -->
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-tag">
                <i class="fa-solid fa-rocket"></i> La plateforme #1 pour Freelances
            </div>
            <h1 class="hero-title" style="font-size: 3.5rem; margin-bottom: 1.5rem;">Découvrez <span>les meilleurs talents</span> ou des <span>missions incroyables</span></h1>
            <p class="hero-sub" style="font-size: 1.15rem; max-width: 700px; margin: 0 auto;">
                FreelaSkill connecte des entreprises innovantes aux freelances les plus qualifiés.
                Une plateforme moderne, sécurisée et pensée pour la réussite de vos projets.
            </p>
            <div class="action-buttons">
                <a href="register.php?role=freelancer" class="btn btn-primary btn-large">
                    <i class="fa-solid fa-user-astronaut"></i> Devenir Freelance
                </a>
                <a href="register.php?role=client" class="btn btn-outline btn-large" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                    <i class="fa-solid fa-briefcase"></i> Recruter des Talents
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="features">
        <div class="feature-card">
            <i class="fa-solid fa-shield-halved feature-icon"></i>
            <h3 class="feature-title">Confiance Totale</h3>
            <p class="feature-desc">Travaillez l'esprit tranquille. Notre système de protection garantit à la fois les paiements et le bon déroulement des missions.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-bolt feature-icon"></i>
            <h3 class="feature-title">Matching Ultra-rapide</h3>
            <p class="feature-desc">Trouvez le collaborateur id?al ou la mission parfaite en un temps record gr?ce ? nos algorithmes performants.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-gem feature-icon"></i>
            <h3 class="feature-title">Qualité Premium</h3>
            <p class="feature-desc">Accédez à une communauté de professionnels soigneusement sélectionnés pour la qualité de leurs compétences.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/chatbot_widget.php'; ?>
</body>
</html>

