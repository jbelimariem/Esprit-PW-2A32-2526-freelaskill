<?php
session_start();
$name  = htmlspecialchars($_SESSION['pending_name']  ?? 'utilisateur');
$email = htmlspecialchars($_SESSION['pending_email'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte en attente — FreelaSkill</title>
    <meta name="description" content="Votre compte FreelaSkill est en cours de validation par notre équipe.">
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="../assets/theme.js" defer></script>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--page-bg);
            position: relative;
            overflow: hidden;
        }

        /* Ambient glows */
        .glow-1 {
            position: fixed; top: -120px; left: -80px;
            width: 520px; height: 520px; border-radius: 50%;
            background: radial-gradient(circle, rgba(245,158,11,.12), transparent 70%);
            pointer-events: none; animation: floatGlow 10s ease-in-out infinite alternate;
        }
        .glow-2 {
            position: fixed; bottom: -100px; right: -60px;
            width: 440px; height: 440px; border-radius: 50%;
            background: radial-gradient(circle, rgba(59,130,246,.1), transparent 70%);
            pointer-events: none; animation: floatGlow 14s ease-in-out infinite alternate-reverse;
        }

        .pending-card {
            position: relative;
            background: var(--surface-elevated);
            border: 1px solid rgba(245,158,11,.25);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            max-width: 520px;
            width: 90%;
            text-align: center;
            backdrop-filter: blur(24px);
            box-shadow: 0 32px 80px rgba(0,0,0,.45), 0 0 0 1px rgba(245,158,11,.08);
            animation: fadeUp .6s ease forwards;
        }

        /* Pulsing icon ring */
        .pending-icon-wrap {
            position: relative;
            width: 88px; height: 88px;
            margin: 0 auto 1.75rem;
        }
        .pending-icon-wrap::before,
        .pending-icon-wrap::after {
            content: '';
            position: absolute; inset: -8px;
            border-radius: 50%;
            border: 2px solid rgba(245,158,11,.25);
            animation: ringPulse 2s ease-in-out infinite;
        }
        .pending-icon-wrap::after { inset: -18px; animation-delay: .6s; border-color: rgba(245,158,11,.12); }

        @keyframes ringPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50%       { transform: scale(1.06); opacity: .5; }
        }

        .pending-icon {
            width: 88px; height: 88px; border-radius: 50%;
            background: linear-gradient(135deg, rgba(245,158,11,.2), rgba(251,191,36,.1));
            border: 2px solid rgba(245,158,11,.4);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem; color: #f59e0b;
        }

        .pending-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.3);
            color: #fbbf24; font-size: .72rem; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; padding: .3rem .9rem; border-radius: 999px;
            margin-bottom: 1.2rem;
        }

        .pending-title {
            font-size: 1.75rem; font-weight: 700; color: white;
            margin-bottom: .6rem; line-height: 1.2;
        }
        .pending-title span { color: #f59e0b; }

        .pending-sub {
            color: var(--text-muted); font-size: .95rem; line-height: 1.65;
            margin-bottom: 2rem;
        }
        .pending-email-chip {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(59,130,246,.08); border: 1px solid rgba(59,130,246,.2);
            color: #93c5fd; font-family: 'JetBrains Mono', monospace;
            font-size: .82rem; padding: .35rem 1rem; border-radius: 999px;
            margin-bottom: 2rem;
        }

        /* Steps */
        .steps {
            display: flex; flex-direction: column; gap: .75rem;
            margin-bottom: 2rem; text-align: left;
        }
        .step {
            display: flex; align-items: flex-start; gap: .9rem;
            padding: .8rem 1rem; border-radius: 12px;
            background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.06);
        }
        .step-num {
            width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: .72rem; font-weight: 800; font-family: 'JetBrains Mono', monospace;
        }
        .step-num--done   { background: rgba(16,185,129,.2); color: #10b981; border: 1px solid rgba(16,185,129,.4); }
        .step-num--active { background: rgba(245,158,11,.2); color: #f59e0b; border: 1px solid rgba(245,158,11,.4); animation: ringPulse 1.8s ease-in-out infinite; }
        .step-num--next   { background: rgba(255,255,255,.05); color: var(--text-muted); border: 1px solid rgba(255,255,255,.08); }
        .step-text { font-size: .85rem; }
        .step-text strong { color: white; display: block; margin-bottom: .15rem; }
        .step-text span { color: var(--text-muted); font-size: .78rem; }

        .btn-back {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .7rem 1.4rem; border-radius: 999px;
            border: 1px solid rgba(255,255,255,.1);
            background: rgba(255,255,255,.04);
            color: var(--text-muted); font-size: .88rem; font-weight: 600;
            text-decoration: none; transition: all .2s; font-family: 'Space Grotesk', sans-serif;
        }
        .btn-back:hover { border-color: rgba(255,255,255,.2); color: white; background: rgba(255,255,255,.08); }

        .logo-top {
            position: fixed; top: 1.5rem; left: 1.5rem;
            display: flex; align-items: center; gap: .5rem;
            font-size: 1.2rem; font-weight: 700; color: white; text-decoration: none;
        }
        .logo-top i { color: #ef4444; }
        .logo-top span { color: #3b82f6; }
    </style>
</head>
<body>

<div class="glow-1"></div>
<div class="glow-2"></div>

<a href="discover.php" class="logo-top">
    <i class="fa-solid fa-shapes"></i> Freela<span>Skill</span>
</a>

<div class="pending-card">

    <div class="pending-icon-wrap">
        <div class="pending-icon">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
    </div>

    <div class="pending-badge">
        <i class="fa-solid fa-clock"></i> En cours de validation
    </div>

    <h1 class="pending-title">
        Bienvenue, <span><?php echo $name; ?></span> !<br>
        Votre compte est soumis.
    </h1>

    <p class="pending-sub">
        Votre inscription a bien été enregistrée. Un administrateur va examiner votre profil et l'activer dans les plus brefs délais.
    </p>

    <?php if ($email): ?>
    <div class="pending-email-chip">
        <i class="fa-solid fa-envelope"></i> <?php echo $email; ?>
    </div>
    <?php endif; ?>

    <!-- Progress steps -->
    <div class="steps">
        <div class="step">
            <div class="step-num step-num--done"><i class="fa-solid fa-check"></i></div>
            <div class="step-text">
                <strong>Inscription complétée</strong>
                <span>Vos informations ont été enregistrées avec succès.</span>
            </div>
        </div>
        <div class="step">
            <div class="step-num step-num--active">2</div>
            <div class="step-text">
                <strong>Validation administrateur</strong>
                <span>Un admin vérifie votre profil — généralement sous 24h.</span>
            </div>
        </div>
        <div class="step">
            <div class="step-num step-num--next">3</div>
            <div class="step-text">
                <strong>Accès à la plateforme</strong>
                <span>Une fois approuvé, vous pourrez vous connecter normalement.</span>
            </div>
        </div>
    </div>

    <a href="login.php" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour à la connexion
    </a>

</div>

<?php include __DIR__ . '/chatbot_widget.php'; ?>
<?php include __DIR__ . '/translate_widget.php'; ?>
</body>
</html>
