<?php
require_once __DIR__ . '/../../controllers/PasswordResetController.php';

$ctrl    = new PasswordResetController();
$result  = $ctrl->handleForgotPassword();
$error   = $result['error'];
$success = $result['success'];
$mailErr = $result['mailErr'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/theme-init.js"></script>
    <script src="../assets/theme.js" defer></script>
    <style>
        .auth-wrapper { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; position:relative; }
        .auth-glow  { position:fixed; top:-200px; right:-150px; width:700px; height:700px; background:radial-gradient(circle,rgba(59,130,246,.12),transparent 60%); pointer-events:none; animation:floatGlow 12s ease-in-out infinite alternate; }
        .auth-glow2 { position:fixed; bottom:-200px; left:-100px; width:600px; height:600px; background:radial-gradient(circle,rgba(139,92,246,.08),transparent 60%); pointer-events:none; animation:floatGlow 18s ease-in-out infinite alternate-reverse; }
        .auth-box { position:relative; z-index:1; width:100%; max-width:460px; background:var(--surface-1); border:1px solid var(--border); border-radius:24px; padding:2.5rem; backdrop-filter:blur(20px); animation:fadeUp .5s ease forwards; box-shadow:var(--card-shadow); }
        .auth-logo { display:inline-flex; align-items:center; gap:.5rem; font-size:1.5rem; font-weight:700; margin-bottom:1.25rem; }
        .auth-logo i { color:var(--tunisian-red); }
        .auth-logo span { color:var(--tech-blue); }
        .step-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(59,130,246,.12); border:1px solid rgba(59,130,246,.25); color:#93c5fd; font-size:.78rem; font-weight:600; padding:.35rem .85rem; border-radius:999px; margin-bottom:1rem; }
        .auth-title { font-size:1.5rem; font-weight:700; color:var(--text-strong); margin-bottom:.4rem; }
        .auth-sub { color:var(--text-muted); font-size:.9rem; margin-bottom:2rem; line-height:1.6; }
        .input-wrap { position:relative; }
        .input-wrap i { position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--input-icon); font-size:.9rem; }
        .input-wrap .form-input { padding-left:2.6rem; }
        .field-error { margin-top:.45rem; color:#fca5a5; font-size:.8rem; }
        .success-box { background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.3); border-radius:16px; padding:1.75rem; text-align:center; }
        .success-icon { font-size:3rem; color:#10b981; margin-bottom:1rem; }
        .back-link { display:inline-flex; align-items:center; gap:.4rem; color:var(--text-muted); font-size:.85rem; text-decoration:none; margin-top:1.5rem; }
        .back-link:hover { color:var(--tech-blue); }
    </style>
</head>
<body class="page-anim">
<div class="auth-glow"></div>
<div class="auth-glow2"></div>

<div class="auth-wrapper">
    <div class="auth-box">

        <div style="text-align:center; margin-bottom:1.5rem;">
            <div class="auth-logo">
                <i class="fa-solid fa-shapes"></i>
                Freela<span>Skill</span>
            </div>
            <div class="step-badge"><i class="fa-solid fa-key"></i> Étape 1 / 3</div>
            <h1 class="auth-title">Mot de passe oublié ?</h1>
            <p class="auth-sub">Entrez votre adresse email. Nous vous enverrons un code de vérification à 6 chiffres.</p>
        </div>

        <?php if ($success): ?>
            <div class="success-box">
                <div class="success-icon"><i class="fa-solid fa-paper-plane"></i></div>
                <div style="font-weight:700; color:#6ee7b7; font-size:1.05rem; margin-bottom:.5rem;">Email envoyé !</div>
                <div style="font-size:.88rem; color:var(--text-muted); line-height:1.6;">
                    Si un compte est associé à cet email, vous recevrez le code dans quelques secondes.<br><br>
                    <strong style="color:var(--text-strong);">Vérifiez aussi vos spams.</strong>
                </div>

                <?php if (!empty($mailErr)): ?>
                <div style="margin-top:1rem;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:.85rem 1rem;font-size:.82rem;color:#fca5a5;">
                    <i class="fa-solid fa-triangle-exclamation" style="margin-right:.4rem;"></i>
                    <strong>Erreur API email :</strong> <?php echo htmlspecialchars($mailErr); ?><br>
                    <span style="color:#94a3b8;margin-top:.3rem;display:block;">Vérifiez <code>controllers/email_config.local.php</code> (provider, API key, sender vérifié).</span>
                </div>
                <?php endif; ?>

                <a href="verify_code.php" class="btn-cart" style="display:inline-flex;align-items:center;gap:.5rem;margin-top:1.5rem;text-decoration:none;padding:.85rem 2rem;border-radius:12px;">
                    <i class="fa-solid fa-arrow-right"></i> Entrer mon code
                </a>
            </div>
        <?php else: ?>
            <?php if (!empty($error)): ?>
                <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.25rem;color:#fca5a5;font-size:.88rem;display:flex;align-items:center;gap:.6rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="forgot_password.php" novalidate>
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label" for="email">Adresse email</label>
                    <div class="input-wrap">
                        <input class="form-input" type="text" id="email" name="email"
                               placeholder="vous@exemple.com"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               autocomplete="email">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                </div>

                <button type="submit" class="btn-cart" style="width:100%;justify-content:center;gap:.5rem;">
                    <i class="fa-solid fa-paper-plane"></i> Envoyer le code
                </button>
            </form>
        <?php endif; ?>

        <div style="text-align:center; margin-top:1.5rem;">
            <a href="login.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Retour à la connexion
            </a>
        </div>

    </div>
</div>
<?php include __DIR__ . '/chatbot_widget.php'; ?>
<?php include __DIR__ . '/translate_widget.php'; ?>
</body>
</html>
