<?php
session_start();
require_once __DIR__ . '/../../controllers/AuthController.php';

// Si déjà connecté → profil
if (!empty($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$userController = new AuthController();
$errors = $userController->handleLogin();
$fieldError = function ($field) use ($errors) {
    return $errors[$field] ?? '';
};
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — FreelaSkill</title>
    <meta name="description" content="Connectez-vous à FreelaSkill pour accéder à vos missions, contrats et marketplace.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 2rem;
        }
        .auth-glow {
            position: fixed;
            top: -200px; right: -150px;
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.12), transparent 60%);
            pointer-events: none;
            animation: floatGlow 12s ease-in-out infinite alternate;
        }
        .auth-glow-2 {
            position: fixed;
            bottom: -200px; left: -100px;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.08), transparent 60%);
            pointer-events: none;
            animation: floatGlow 18s ease-in-out infinite alternate-reverse;
        }
        .auth-box {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(20px);
            animation: fadeUp 0.5s ease forwards;
        }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-logo {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
        }
        .auth-logo i { color: var(--tunisian-red); }
        .auth-logo span { color: var(--tech-blue); }
        .auth-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.4rem; }
        .auth-sub { color: var(--text-muted); font-size: 0.9rem; }

        .auth-form { display: flex; flex-direction: column; gap: 1.1rem; }

        .input-wrap {
            position: relative;
        }
        .input-wrap i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #334155;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        .input-wrap .form-input { padding-left: 2.6rem; }
        .input-wrap .form-input:focus + i,
        .input-wrap:focus-within i { color: var(--tech-blue); }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #334155;
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
            background: none;
            border: none;
            padding: 0;
        }
        .toggle-password:hover { color: var(--text-muted); }

        .auth-divider {
            text-align: center;
            color: #334155;
            font-size: 0.82rem;
            position: relative;
            margin: 0.5rem 0;
        }
        .auth-divider::before, .auth-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 42%;
            height: 1px;
            background: var(--border);
        }
        .auth-divider::before { left: 0; }
        .auth-divider::after  { right: 0; }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.88rem;
            color: var(--text-muted);
        }
        .auth-footer a { color: var(--tech-blue); font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }

        .forgot-link {
            text-align: right;
            font-size: 0.82rem;
        }
        .forgot-link a { color: var(--text-muted); }
        .forgot-link a:hover { color: white; }
        .field-error {
            margin-top: 0.45rem;
            color: #fca5a5;
            font-size: 0.8rem;
        }
        .form-input.input-error {
            border-color: rgba(239, 68, 68, 0.65);
        }
    </style>
</head>
<body class="page-anim">

<div class="auth-glow"></div>
<div class="auth-glow-2"></div>

<div class="auth-wrapper">
    <div class="auth-box">

        <!-- Header -->
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fa-solid fa-shapes"></i>
                Freela<span>Skill</span>
            </div>
            <h1 class="auth-title">Bon retour 👋</h1>
            <p class="auth-sub">Connectez-vous à votre compte FreelaSkill</p>
        </div>

        <!-- Errors -->
        <?php if (!empty($errors['_global'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($errors['_global']); ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form class="auth-form" method="POST" action="login.php" id="login-form" novalidate>

            <div class="form-group">
                <label class="form-label" for="email">Adresse email</label>
                <div class="input-wrap">
                    <input
                        class="form-input<?php echo $fieldError('email') !== '' ? ' input-error' : ''; ?>"
                        type="text"
                        id="email"
                        name="email"
                        placeholder="vous@exemple.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        autocomplete="email"
                    >
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <?php if ($fieldError('email') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError('email')); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <div class="input-wrap" style="position:relative;">
                    <input
                        class="form-input<?php echo $fieldError('password') !== '' ? ' input-error' : ''; ?>"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        style="padding-right: 3rem;"
                    >
                    <i class="fa-solid fa-lock"></i>
                    <button type="button" class="toggle-password" onclick="togglePwd()" id="pwd-toggle" title="Afficher / Masquer">
                        <i class="fa-regular fa-eye" id="eye-icon"></i>
                    </button>
                </div>
                <?php if ($fieldError('password') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError('password')); ?></div>
                <?php endif; ?>
                <div class="forgot-link"><a href="#">Mot de passe oublié ?</a></div>
            </div>

            <button type="submit" class="btn-cart" id="submit-btn">
                <i class="fa-solid fa-right-to-bracket"></i>
                Se connecter
            </button>

        </form>

        <div class="auth-divider" style="margin-top:1.5rem;">ou</div>

        <div class="auth-footer">
            Pas encore de compte ?
            <a href="register.php">Créer un compte</a>
        </div>

    </div>
</div>

<script>
function togglePwd() {
    const pwd  = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Loading state on submit
document.getElementById('login-form').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Connexion...';
    btn.disabled = true;
});
</script>

</body>
</html>

