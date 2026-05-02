<?php
require_once __DIR__ . '/../../controllers/PasswordResetController.php';

$ctrl   = new PasswordResetController();
$result = $ctrl->handleResetPassword();
$error    = $result['error'];
$success  = $result['success'];
$email    = $result['email'];
$nextPage = $result['nextPage'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/theme-init.js"></script>
    <script src="../assets/theme.js" defer></script>
    <style>
        .auth-wrapper { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; position:relative; }
        .auth-glow  { position:fixed; top:-200px; right:-150px; width:700px; height:700px; background:radial-gradient(circle,rgba(16,185,129,.1),transparent 60%); pointer-events:none; animation:floatGlow 12s ease-in-out infinite alternate; }
        .auth-glow2 { position:fixed; bottom:-200px; left:-100px; width:600px; height:600px; background:radial-gradient(circle,rgba(59,130,246,.08),transparent 60%); pointer-events:none; animation:floatGlow 18s ease-in-out infinite alternate-reverse; }
        .auth-box { position:relative; z-index:1; width:100%; max-width:460px; background:var(--surface-1); border:1px solid var(--border); border-radius:24px; padding:2.5rem; animation:fadeUp .5s ease forwards; box-shadow:var(--card-shadow); }
        .auth-logo { display:inline-flex; align-items:center; gap:.5rem; font-size:1.5rem; font-weight:700; margin-bottom:1.25rem; }
        .auth-logo i { color:var(--tunisian-red); }
        .auth-logo span { color:var(--tech-blue); }
        .step-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(16,185,129,.12); border:1px solid rgba(16,185,129,.25); color:#6ee7b7; font-size:.78rem; font-weight:600; padding:.35rem .85rem; border-radius:999px; margin-bottom:1rem; }
        .auth-title { font-size:1.5rem; font-weight:700; color:var(--text-strong); margin-bottom:.4rem; }
        .auth-sub   { color:var(--text-muted); font-size:.9rem; margin-bottom:2rem; line-height:1.6; }
        .input-wrap { position:relative; margin-bottom:1rem; }
        .input-wrap .field-icon { position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--input-icon); font-size:.9rem; pointer-events:none; }
        .input-wrap .form-input { padding-left:2.6rem; padding-right:3rem; }
        .toggle-password { position:absolute; right:1rem; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--input-icon); cursor:pointer; font-size:.9rem; padding:0; }
        .toggle-password:hover { color:var(--text-muted); }
        .field-error { color:#fca5a5; font-size:.8rem; margin-top:.4rem; }

        /* Strength bar */
        .strength-bar { height:4px; background:rgba(255,255,255,.08); border-radius:2px; margin-top:.75rem; overflow:hidden; }
        .strength-fill { height:100%; width:0; border-radius:2px; transition:all .3s; }

        /* Password rules */
        .password-rules { display:flex; gap:.5rem; flex-wrap:wrap; margin-top:.75rem; }
        .password-rule {
            display:inline-flex; align-items:center; gap:.4rem;
            padding:.4rem .9rem; border-radius:999px;
            border:1px solid var(--border); font-size:.78rem;
            color:var(--text-muted); background:rgba(255,255,255,.02);
            transition:all .3s;
        }
        .password-rule .rule-icon { width:16px; height:16px; display:flex; align-items:center; justify-content:center; border-radius:50%; border:1.5px solid rgba(71,85,105,.6); font-size:.6rem; transition:all .3s; }
        .password-rule.is-valid { border-color:rgba(16,185,129,.7); color:#6ee7b7; background:rgba(16,185,129,.1); box-shadow:0 0 0 3px rgba(16,185,129,.1); }
        .password-rule.is-valid .rule-icon { background:rgba(16,185,129,.25); border-color:rgba(16,185,129,.7); }
        .password-rule.is-valid .rule-icon i { color:#10b981; }

        /* Success */
        .success-box { background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.3); border-radius:16px; padding:2rem; text-align:center; }
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
            <div class="step-badge"><i class="fa-solid fa-lock-open"></i> Étape 3 / 3</div>
            <h1 class="auth-title">Nouveau mot de passe</h1>
            <p class="auth-sub">Choisissez un mot de passe fort pour sécuriser votre compte.</p>
        </div>

        <?php if ($success): ?>
            <div class="success-box">
                <div style="font-size:3.5rem; margin-bottom:1rem;">🎉</div>
                <div style="font-size:1.15rem; font-weight:700; color:#6ee7b7; margin-bottom:.6rem;">Mot de passe mis à jour !</div>
                <div style="font-size:.88rem; color:var(--text-muted); line-height:1.7; margin-bottom:1.5rem;">
                    Votre mot de passe a été réinitialisé avec succès.<br>Vous pouvez maintenant vous connecter.
                </div>
                <a href="<?php echo htmlspecialchars($nextPage); ?>" class="btn-cart" style="display:inline-flex;align-items:center;gap:.5rem;text-decoration:none;padding:.85rem 2rem;border-radius:12px;">
                    <i class="fa-solid fa-right-to-bracket"></i> Continuer vers mon espace
                </a>
            </div>
        <?php else: ?>

            <?php if (!empty($error)): ?>
                <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.25rem;color:#fca5a5;font-size:.88rem;display:flex;align-items:center;gap:.6rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="reset_password.php" novalidate>

                <!-- New Password -->
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Nouveau mot de passe</label>
                    <div class="input-wrap">
                        <input class="form-input" type="password" id="password" name="password"
                               placeholder="Minimum 8 caractères"
                               autocomplete="new-password"
                               oninput="updateStrength(this.value)">
                        <i class="fa-solid fa-lock field-icon"></i>
                        <button type="button" class="toggle-password" onclick="togglePwd('password','eye1')">
                            <i class="fa-regular fa-eye" id="eye1"></i>
                        </button>
                    </div>

                    <!-- Strength bar -->
                    <div class="strength-bar">
                        <div class="strength-fill" id="strength-fill"></div>
                    </div>

                    <!-- Rules -->
                    <div class="password-rules">
                        <span class="password-rule" id="rule-length">
                            <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                            8 caractères min.
                        </span>
                        <span class="password-rule" id="rule-upper">
                            <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                            1 majuscule
                        </span>
                        <span class="password-rule" id="rule-special">
                            <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                            1 caractère spécial
                        </span>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <div class="input-wrap">
                        <input class="form-input" type="password" id="confirm_password" name="confirm_password"
                               placeholder="••••••••"
                               autocomplete="new-password">
                        <i class="fa-solid fa-shield-halved field-icon"></i>
                        <button type="button" class="toggle-password" onclick="togglePwd('confirm_password','eye2')">
                            <i class="fa-regular fa-eye" id="eye2"></i>
                        </button>
                    </div>
                    <div class="field-error" id="match-error" style="display:none;">
                        <i class="fa-solid fa-xmark"></i> Les mots de passe ne correspondent pas.
                    </div>
                </div>

                <button type="submit" class="btn-cart" style="width:100%;justify-content:center;gap:.5rem;" id="submit-btn">
                    <i class="fa-solid fa-shield-check"></i> Réinitialiser le mot de passe
                </button>

            </form>
        <?php endif; ?>

    </div>
</div>

<script>
function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function updateStrength(val) {
    const checks = {
        length:  val.length >= 8,
        upper:   /[A-Z]/.test(val),
        special: /[^A-Za-z0-9]/.test(val)
    };
    const score = Object.values(checks).filter(Boolean).length;
    const fill  = document.getElementById('strength-fill');
    const colors = ['', '#ef4444', '#f59e0b', '#10b981'];
    fill.style.width      = (score / 3 * 100) + '%';
    fill.style.background = colors[score] || 'transparent';

    document.getElementById('rule-length').classList.toggle('is-valid', checks.length);
    document.getElementById('rule-upper').classList.toggle('is-valid', checks.upper);
    document.getElementById('rule-special').classList.toggle('is-valid', checks.special);
}

// Live confirm match
document.getElementById('confirm_password').addEventListener('input', function () {
    const match = this.value === document.getElementById('password').value;
    document.getElementById('match-error').style.display = (!match && this.value) ? 'block' : 'none';
});
</script>
<?php include __DIR__ . '/chatbot_widget.php'; ?>
</body>
</html>
