<?php
session_start();

$type  = $_SESSION['google_blocked'] ?? null;
$email = $_SESSION['google_email']   ?? '';

// Si aucune session de blocage, rediriger
if (!$type) {
    header('Location: login.php');
    exit;
}

// Nettoyer la session après lecture (on-time use)
unset($_SESSION['google_blocked'], $_SESSION['google_email']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $type === 'rejected' ? 'Accès refusé' : 'Compte en attente'; ?> — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/theme-init.js"></script>
    <script src="../assets/theme.js" defer></script>
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .blocked-card {
            width: 100%; max-width: 480px;
            background: var(--surface-1);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            text-align: center;
            border: 1px solid var(--border);
            box-shadow: var(--card-shadow);
            animation: fadeUp 0.5s ease forwards;
        }
        .blocked-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            display: block;
        }
        .blocked-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .blocked-email {
            display: inline-block;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.35rem 0.9rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 1.25rem;
            font-family: monospace;
        }
        .blocked-body {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        .blocked-body strong { color: var(--text-strong); }
        .blocked-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 1.5rem 0;
        }
        .blocked-note {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        /* Rejected */
        .type-rejected .blocked-icon { color: #ef4444; }
        .type-rejected .blocked-title { color: #fca5a5; }
        .type-rejected { border-color: rgba(239,68,68,0.3); background: linear-gradient(135deg, var(--surface-1), rgba(239,68,68,0.04)); }
        /* Pending */
        .type-pending .blocked-icon { color: #f59e0b; }
        .type-pending .blocked-title { color: #fbbf24; }
        .type-pending { border-color: rgba(245,158,11,0.3); background: linear-gradient(135deg, var(--surface-1), rgba(245,158,11,0.04)); }
        /* Banned */
        .type-banned .blocked-icon { color: #8b5cf6; }
        .type-banned .blocked-title { color: #c4b5fd; }
        .type-banned { border-color: rgba(139,92,246,0.3); background: linear-gradient(135deg, var(--surface-1), rgba(139,92,246,0.04)); }
    </style>
</head>
<body class="page-anim">

<div class="blocked-card type-<?php echo htmlspecialchars($type); ?>">

    <!-- Logo -->
    <div style="font-size:1.3rem;font-weight:700;margin-bottom:2rem;color:var(--text-strong);">
        <i class="fa-solid fa-shapes" style="color:var(--tunisian-red);"></i>
        Freela<span style="color:var(--tech-blue);">Skill</span>
    </div>

    <?php if ($type === 'rejected'): ?>
        <span class="blocked-icon"><i class="fa-solid fa-ban"></i></span>
        <div class="blocked-title">Inscription refusée</div>
        <?php if ($email): ?>
            <div class="blocked-email"><i class="fa-solid fa-envelope" style="margin-right:5px;"></i><?php echo htmlspecialchars($email); ?></div>
        <?php endif; ?>
        <div class="blocked-body">
            Votre demande d'inscription a été <strong>refusée</strong> par l'administrateur.<br>
            Vous ne pouvez plus accéder à la plateforme FreelaSkill avec ce compte.
        </div>
        <hr class="blocked-divider">
        <div class="blocked-note">
            <i class="fa-solid fa-circle-info" style="color:#ef4444;margin-right:5px;"></i>
            Si vous pensez qu'il s'agit d'une erreur, veuillez contacter le support.
        </div>

    <?php elseif ($type === 'pending'): ?>
        <span class="blocked-icon"><i class="fa-solid fa-hourglass-half"></i></span>
        <div class="blocked-title">Compte en attente de validation</div>
        <?php if ($email): ?>
            <div class="blocked-email"><i class="fa-solid fa-envelope" style="margin-right:5px;"></i><?php echo htmlspecialchars($email); ?></div>
        <?php endif; ?>
        <div class="blocked-body">
            Votre compte est en cours d'<strong>examen</strong> par notre équipe.<br>
            Vous recevrez l'accès dès qu'un administrateur aura approuvé votre inscription.
        </div>
        <hr class="blocked-divider">
        <div class="blocked-note">
            <i class="fa-solid fa-clock" style="color:#f59e0b;margin-right:5px;"></i>
            La validation peut prendre jusqu'à 24 heures.
        </div>

    <?php elseif ($type === 'banned'): ?>
        <span class="blocked-icon"><i class="fa-solid fa-shield-halved"></i></span>
        <div class="blocked-title">Compte suspendu</div>
        <?php if ($email): ?>
            <div class="blocked-email"><i class="fa-solid fa-envelope" style="margin-right:5px;"></i><?php echo htmlspecialchars($email); ?></div>
        <?php endif; ?>
        <div class="blocked-body">
            Votre compte a été <strong>suspendu</strong> par un administrateur.<br>
            L'accès à la plateforme est temporairement bloqué.
        </div>
        <hr class="blocked-divider">
        <div class="blocked-note">
            <i class="fa-solid fa-circle-info" style="color:#8b5cf6;margin-right:5px;"></i>
            Contactez l'administrateur pour plus d'informations.
        </div>
    <?php endif; ?>

    <div style="margin-top:2rem;">
        <a href="login.php" class="btn-cart" style="display:inline-flex;align-items:center;gap:0.5rem;text-decoration:none;padding:0.85rem 2rem;border-radius:12px;">
            <i class="fa-solid fa-arrow-left"></i>
            Retour à la connexion
        </a>
    </div>
</div>

</body>
</html>
