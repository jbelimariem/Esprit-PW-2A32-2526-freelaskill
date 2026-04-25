<?php
// views/frontoffice/google_complete.php
session_start();
require_once __DIR__ . '/../../controllers/AuthController.php';

$email = $_GET['email'] ?? ($_POST['email'] ?? '');
if (empty($email)) {
    header('Location: register.php');
    exit;
}

$userController = new AuthController();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = $userController->handleRegister();
}

$fieldError = function ($field) use ($errors) {
    return $errors[$field] ?? '';
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compléter votre profil — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/theme-init.js"></script>
    <script src="../assets/theme.js" defer></script>
    <style>
        .auth-wrapper {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 3rem 2rem; position: relative;
        }
        .auth-box {
            width: 100%; max-width: 520px; background: var(--surface-1);
            border: 1px solid var(--border); border-radius: 24px; padding: 2.5rem;
            backdrop-filter: blur(20px); box-shadow: var(--card-shadow);
            animation: fadeUp 0.5s ease forwards;
        }
        .role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        .role-card {
            border: 2px solid var(--border); border-radius: var(--radius-lg);
            padding: 1.25rem 1rem; cursor: pointer; text-align: center; transition: var(--transition);
        }
        .role-card.selected { border-color: var(--tech-blue); background: rgba(37,99,235,0.1); box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
        .role-icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .role-name { font-weight: 700; color: var(--text-strong); }
        .input-wrap .form-input { padding-left: 2.6rem; }
    </style>
</head>
<body class="page-anim">
    <div class="auth-wrapper">
        <div class="auth-box">
            <div style="text-align:center; margin-bottom: 2rem;">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="40" style="margin-bottom:1rem;">
                <h2 style="font-size:1.6rem; margin-bottom:0.5rem; color:var(--text-strong);">Complétez votre profil</h2>
                <p style="color:var(--text-muted);">Connecté en tant que <strong><?php echo htmlspecialchars($email); ?></strong></p>
            </div>
            
            <?php if (!empty($errors['_global'])): ?>
                <div class="field-error" style="text-align:center; margin-bottom: 1rem;"><?php echo htmlspecialchars($errors['_global']); ?></div>
            <?php endif; ?>

            <form method="POST" action="google_complete.php">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                
                <div class="role-grid">
                    <label>
                        <input type="radio" name="role" value="freelancer" hidden checked>
                        <div class="role-card selected" onclick="selectRole(this, 'freelancer')">
                            <div class="role-icon">🧑‍💻</div>
                            <div class="role-name">Freelancer</div>
                        </div>
                    </label>
                    <label>
                        <input type="radio" name="role" value="client" hidden>
                        <div class="role-card" onclick="selectRole(this, 'client')">
                            <div class="role-icon">🏢</div>
                            <div class="role-name">Client</div>
                        </div>
                    </label>
                </div>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label class="form-label">Nom</label>
                        <input class="form-input" type="text" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
                        <?php if ($fieldError('nom')): ?><div class="field-error"><?php echo htmlspecialchars($fieldError('nom')); ?></div><?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label">Prénom</label>
                        <input class="form-input" type="text" name="prenom" required value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
                        <?php if ($fieldError('prenom')): ?><div class="field-error"><?php echo htmlspecialchars($fieldError('prenom')); ?></div><?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Mot de passe</label>
                    <div class="input-wrap" style="position:relative;">
                        <input class="form-input<?php echo $fieldError('password') !== '' ? ' input-error' : ''; ?>" type="password" id="password" name="password"
                               placeholder="Minimum 8 caracteres"
                               autocomplete="new-password"
                               style="padding-right:3rem;" required>
                        <i class="fa-solid fa-lock field-icon" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--input-icon);"></i>
                        <button type="button" class="toggle-password" onclick="togglePwd('password','eye1')" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0;">
                            <i class="fa-regular fa-eye" id="eye1"></i>
                        </button>
                    </div>
                    <?php if ($fieldError('password') !== ''): ?>
                        <div class="field-error" style="color:var(--tunisian-red);font-size:0.85rem;margin-top:0.4rem;"><?php echo htmlspecialchars($fieldError('password')); ?></div>
                    <?php endif; ?>
                    <div class="strength-bar" style="height:4px;background:rgba(255,255,255,0.1);border-radius:2px;margin-top:0.8rem;overflow:hidden;"><div class="strength-fill" id="strength-fill" style="height:100%;width:0;background:var(--tunisian-red);transition:all 0.3s;"></div></div>
                    <div class="password-rules">
                        <span class="password-rule" id="register-rule-length">
                            <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                            8 caractères min.
                        </span>
                        <span class="password-rule" id="register-rule-upper">
                            <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                            1 majuscule
                        </span>
                        <span class="password-rule" id="register-rule-special">
                            <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                            1 caractère spécial
                        </span>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <div class="input-wrap" style="position:relative;">
                        <input class="form-input<?php echo $fieldError('confirm_password') !== '' ? ' input-error' : ''; ?>" type="password" id="confirm_password" name="confirm_password"
                               placeholder="••••••••"
                               autocomplete="new-password"
                               style="padding-right:3rem;" required>
                        <i class="fa-solid fa-shield-halved field-icon" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--input-icon);"></i>
                        <button type="button" class="toggle-password" onclick="togglePwd('confirm_password','eye2')" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0;">
                            <i class="fa-regular fa-eye" id="eye2"></i>
                        </button>
                    </div>
                    <?php if ($fieldError('confirm_password') !== ''): ?>
                        <div class="field-error" style="color:var(--tunisian-red);font-size:0.85rem;margin-top:0.4rem;"><?php echo htmlspecialchars($fieldError('confirm_password')); ?></div>
                    <?php endif; ?>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label class="form-label">Bio (optionnel)</label>
                    <textarea class="form-input" name="bio" rows="2"><?php echo htmlspecialchars($_POST['bio'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn-cart" style="width:100%;">Terminer l'inscription</button>
                <a href="register.php" style="display:block; text-align:center; margin-top:1rem; color:var(--text-muted); font-size:0.9rem;">Annuler</a>
            </form>
        </div>
    </div>
    
    <script>
    function selectRole(card, role) {
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.parentElement.querySelector('input').checked = true;
    }
    
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
        const fill = document.getElementById('strength-fill');
        const checks = {
            length: val.length >= 8,
            upper: /[A-Z]/.test(val),
            special: /[^A-Za-z0-9]/.test(val)
        };
        const score = (checks.length ? 1 : 0) + (checks.upper ? 1 : 0) + (checks.special ? 1 : 0);
        const pct   = (score / 3) * 100;
        const color = score <= 1 ? '#ef4444' : score === 2 ? '#F59E0B' : '#10b981';

        const lengthRule = document.getElementById('register-rule-length');
        const upperRule = document.getElementById('register-rule-upper');
        const specialRule = document.getElementById('register-rule-special');

        if (lengthRule) lengthRule.classList.toggle('is-valid', checks.length);
        if (upperRule) upperRule.classList.toggle('is-valid', checks.upper);
        if (specialRule) specialRule.classList.toggle('is-valid', checks.special);

        if (fill) {
            fill.style.width = pct + '%';
            fill.style.background = color;
        }
    }

    function initRegisterPasswordRules() {
        const passwordInput = document.getElementById('password');
        if (!passwordInput) {
            return;
        }

        const refreshRules = function () {
            updateStrength(passwordInput.value);
        };

        passwordInput.addEventListener('input', refreshRules);
        passwordInput.addEventListener('keyup', refreshRules);
        passwordInput.addEventListener('change', refreshRules);
        refreshRules();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRegisterPasswordRules);
    } else {
        initRegisterPasswordRules();
    }
    </script>
    <style>
        .password-rules {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.55rem;
            margin-top: 0.85rem;
        }
        .password-rule {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            min-height: 42px;
            min-width: 0;
            padding: 0.42rem 0.7rem;
            border-radius: 999px;
            border: 1px solid rgba(71,85,105,0.5);
            background: rgba(15,23,42,0.7);
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 500;
            line-height: 1;
            text-align: center;
            transition: all 0.35s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
        }
        .password-rule::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(5,150,105,0.08));
            opacity: 0;
            transition: opacity 0.35s ease;
        }
        .password-rule .rule-icon,
        .password-rule > span:not(.rule-icon) {
            position: relative;
            z-index: 1;
        }
        .password-rule .rule-icon {
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1.5px solid rgba(71,85,105,0.6);
            flex-shrink: 0;
            transition: all 0.35s cubic-bezier(.4,0,.2,1);
        }
        .password-rule .rule-icon i {
            font-size: 0.6rem;
            color: #475569;
            transition: all 0.3s;
        }
        .password-rule.is-valid {
            border-color: rgba(16,185,129,0.7);
            background: rgba(16,185,129,0.12);
            color: #6ee7b7;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1), 0 0 12px rgba(16,185,129,0.12);
        }
        .password-rule.is-valid::before { opacity: 1; }
        .password-rule.is-valid .rule-icon {
            background: rgba(16,185,129,0.25);
            border-color: rgba(16,185,129,0.7);
        }
        .password-rule.is-valid .rule-icon i {
            color: #10b981;
        }
        html[data-theme='light'] .password-rule {
            border-color: rgba(148,163,184,0.5);
            background: #f8fafc;
            color: #64748b;
        }
        html[data-theme='light'] .password-rule.is-valid {
            border-color: rgba(16,185,129,0.65);
            background: rgba(16,185,129,0.12);
            color: #047857;
        }
        @media (max-width: 500px) {
            .password-rules { gap: 0.35rem; }
            .password-rule {
                gap: 0.25rem;
                padding-inline: 0.35rem;
                font-size: clamp(0.58rem, 2.8vw, 0.72rem);
            }
        }
    </style>
</body>
</html>
