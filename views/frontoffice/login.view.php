<?php
// views/frontoffice/login.view.php -- Template.
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — FreelaSkill</title>
    <meta name="description" content="Connectez-vous à FreelaSkill pour accéder à vos missions, contrats et marketplace.">
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/theme.js" defer></script>
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
        .input-wrap > i {
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
        .input-wrap:focus-within > i { color: var(--tech-blue); }

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
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
<button type="button" class="theme-toggle theme-toggle--floating" data-theme-toggle>
    <i class="fa-solid fa-sun" data-theme-icon></i>
    <span data-theme-label>Jour</span>
</button>

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
            <div class="alert alert-error" style="display: flex; align-items: flex-start; gap: 12px;">
                <i class="fa-solid fa-circle-exclamation" style="margin-top: 3px;"></i>
                <div style="flex: 1;">
                    <span id="lockout-msg">
                        <?php 
                        if (!empty($errors['_locked'])) {
                            echo "Trop de tentatives. Compte bloque.";
                        } else {
                            echo htmlspecialchars($errors['_global']);
                        }
                        ?>
                    </span>
                    <?php if (!empty($errors['_locked'])): ?>
                        <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px; font-family: 'JetBrains Mono', monospace; font-size: 1.1rem; color: #f87171;">
                            <i class="fa-solid fa-clock-rotate-left fa-spin-pulse"></i>
                            <span id="lockout-countdown">--:--</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form class="auth-form" method="POST" action="login.php" id="login-form" autocomplete="off" novalidate>

            <div class="form-group">
                <label class="form-label" for="email">Adresse email</label>
                <div class="input-wrap">
                    <input
                        class="form-input<?php echo $fieldError('email') !== '' ? ' input-error' : ''; ?>"
                        type="text"
                        id="email"
                        name="email"
                        placeholder="vous@exemple.com"
                        value=""
                        autocomplete="off"
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
                        value=""
                        autocomplete="new-password"
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
                <div class="forgot-link"><a href="forgot_password.php">Mot de passe oublié ?</a></div>
            </div>

            <button type="submit" class="btn-cart" id="submit-btn">
                <i class="fa-solid fa-right-to-bracket"></i>
                Se connecter
            </button>

        </form>

        <div class="auth-divider" style="margin-top:1.5rem;">ou</div>

        <!-- CUSTOM GOOGLE BUTTON WITH OAUTH REDIRECT -->
        <?php
        $clientId = "512696696631-585q3lbt2rps9g8o81e8vqr9mijdh8tq.apps.googleusercontent.com";
        $redirectUri = urlencode("http://localhost/projet2222/views/frontoffice/google_callback.php");
        $googleOAuthUrl = "https://accounts.google.com/o/oauth2/v2/auth?client_id={$clientId}&redirect_uri={$redirectUri}&response_type=id_token&scope=email%20profile&nonce=12345&response_mode=form_post";
        ?>
        <a href="<?php echo $googleOAuthUrl; ?>" class="btn-google" style="margin-top: 1rem;">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="20" height="20">
            Continuer avec Google
        </a>

        <!-- FACE ID LOGIN BUTTON -->
        <button type="button" class="btn-cart" onclick="startFaceLogin()" style="margin-top:0.75rem; background:rgba(16,185,129,0.1); color:#10b981; border:1px solid rgba(16,185,129,0.3);">
            <i class="fa-solid fa-face-smile-beam"></i> Continuer avec Face ID
        </button>

        <!-- Face Login Container (Hidden) -->
        <div id="face-login-container" style="display:none;margin-top:1.5rem;text-align:center;">
            <div style="position:relative;width:280px;height:210px;margin:0 auto;border-radius:16px;overflow:hidden;border:2px solid #10b981;box-shadow:0 0 20px rgba(16,185,129,0.2);">
                <video id="face-video" width="280" height="210" autoplay muted style="object-fit:cover;"></video>
                <canvas id="face-canvas" style="position:absolute;top:0;left:0;"></canvas>
            </div>
            <p id="face-status" style="margin-top:1rem;font-weight:600;color:#10b981;">Chargement des modèles...</p>
            <button type="button" class="btn-outline" onclick="stopFaceLogin()" style="margin-top:0.5rem;font-size:0.8rem;padding:0.4rem 1rem;">Annuler</button>
        </div>

        <div class="auth-footer">
            Pas encore de compte ?
            <a href="register.php">Créer un compte</a>
        </div>

    </div>
</div>

<script>
function clearLoginInputs() {
    const form = document.getElementById('login-form');
    const email = document.getElementById('email');
    const password = document.getElementById('password');

    form?.reset();
    if (email) email.value = '';
    if (password) {
        password.value = '';
        password.type = 'password';
    }
}

window.addEventListener('pageshow', clearLoginInputs);
document.addEventListener('DOMContentLoaded', function() {
    clearLoginInputs();
    setTimeout(clearLoginInputs, 150);
    setTimeout(clearLoginInputs, 600);
});

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

<?php if (!empty($errors['_locked'])): ?>
(function() {
    let remaining = <?php echo (int) $errors['_locked']; ?>;
    const display = document.getElementById('lockout-countdown');
    const msg     = document.getElementById('lockout-msg');
    const alertBox = document.querySelector('.alert-error');
    const inputs  = document.querySelectorAll('#login-form input, #login-form button[type="submit"], .btn-google, [onclick="startFaceLogin()"]');
    
    inputs.forEach(el => {
        el.disabled = true;
        el.style.opacity = '0.5';
        el.style.pointerEvents = 'none';
    });

    function fmt(s) {
        const m = Math.floor(s / 60), sec = s % 60;
        return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
    }

    function tick() {
        if (display) display.textContent = fmt(remaining);
        if (remaining <= 0) {
            inputs.forEach(el => {
                el.disabled = false;
                el.style.opacity = '1';
                el.style.pointerEvents = 'auto';
            });
            if (msg) msg.textContent = 'Securite retablie.';
            if (display) { 
                display.parentElement.style.color = '#10b981';
                display.textContent = 'Vous pouvez reessayer !';
                display.previousElementSibling.classList.remove('fa-spin-pulse');
                display.previousElementSibling.className = 'fa-solid fa-check-circle';
            }
            if (alertBox) {
                alertBox.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                alertBox.style.background = 'rgba(16, 185, 129, 0.1)';
                alertBox.style.color = '#34d399';
            }
            return;
        }
        remaining--;
        setTimeout(tick, 1000);
    }
    tick();
})();
<?php endif; ?>
</script>


<!-- Load face-api.js -->
<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script>
let videoStreamLogin = null;

async function startFaceLogin() {
    const container = document.getElementById('face-login-container');
    const statusTxt = document.getElementById('face-status');
    const video = document.getElementById('face-video');
    container.style.display = 'block';
    
    statusTxt.textContent = "Chargement des modèles (Patientez...)";
    statusTxt.style.color = '#10b981';

    try {
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

        statusTxt.textContent = "Démarrage de la caméra...";
        videoStreamLogin = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = videoStreamLogin;

        video.addEventListener('play', () => {
            statusTxt.textContent = "Analyse de votre visage...";
            const canvas = document.getElementById('face-canvas');
            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);

            const interval = setInterval(async () => {
                if (!videoStreamLogin) { clearInterval(interval); return; }
                const detections = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
                
                if (detections) {
                    const resized = faceapi.resizeResults(detections, displaySize);
                    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                    faceapi.draw.drawDetections(canvas, resized);

                    if (detections.descriptor) {
                        clearInterval(interval);
                        statusTxt.textContent = "Visage détecté, vérification...";
                        
                        const response = await fetch('face_api_handler.php?action=login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ descriptor: Array.from(detections.descriptor) })
                        });
                        
                        const result = await response.json();
                        if (result && result.success) {
                            statusTxt.textContent = "Connexion réussie ! Redirection...";
                            setTimeout(() => {
                                window.location.href = result.nextPage || 'profile.php';
                            }, 1000);
                        } else {
                            statusTxt.textContent = "Échec : " + (result ? result.message : 'Visage inconnu');
                            statusTxt.style.color = "#ef4444";
                            setTimeout(() => stopFaceLogin(), 3000);
                        }
                    }
                }
            }, 500);
        });

    } catch (err) {
        console.error(err);
        statusTxt.textContent = "Erreur caméra/modèles.";
        statusTxt.style.color = "#ef4444";
    }
}

function stopFaceLogin() {
    if (videoStreamLogin) {
        videoStreamLogin.getTracks().forEach(track => track.stop());
        videoStreamLogin = null;
    }
    document.getElementById('face-login-container').style.display = 'none';
}
</script>

<?php include __DIR__ . '/chatbot_widget.php'; ?>
</body>
</html>

