<?php
require_once __DIR__ . '/../../controllers/PasswordResetController.php';

$ctrl   = new PasswordResetController();
$result = $ctrl->handleVerifyCode();
$error  = $result['error'];
$valid  = $result['valid'];
$email  = $result['email'];

if ($valid) {
    header('Location: reset_password.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du code — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/theme-init.js"></script>
    <script src="../assets/theme.js" defer></script>
    <style>
        .auth-wrapper { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; position:relative; }
        .auth-glow  { position:fixed; top:-200px; right:-150px; width:700px; height:700px; background:radial-gradient(circle,rgba(59,130,246,.12),transparent 60%); pointer-events:none; animation:floatGlow 12s ease-in-out infinite alternate; }
        .auth-glow2 { position:fixed; bottom:-200px; left:-100px; width:600px; height:600px; background:radial-gradient(circle,rgba(139,92,246,.08),transparent 60%); pointer-events:none; animation:floatGlow 18s ease-in-out infinite alternate-reverse; }
        .auth-box { position:relative; z-index:1; width:100%; max-width:460px; background:var(--surface-1); border:1px solid var(--border); border-radius:24px; padding:2.5rem; animation:fadeUp .5s ease forwards; box-shadow:var(--card-shadow); }
        .auth-logo { display:inline-flex; align-items:center; gap:.5rem; font-size:1.5rem; font-weight:700; margin-bottom:1.25rem; }
        .auth-logo i { color:var(--tunisian-red); }
        .auth-logo span { color:var(--tech-blue); }
        .step-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(139,92,246,.12); border:1px solid rgba(139,92,246,.25); color:#c4b5fd; font-size:.78rem; font-weight:600; padding:.35rem .85rem; border-radius:999px; margin-bottom:1rem; }
        .auth-title { font-size:1.5rem; font-weight:700; color:var(--text-strong); margin-bottom:.4rem; }
        .auth-sub   { color:var(--text-muted); font-size:.9rem; margin-bottom:2rem; line-height:1.6; }

        /* Code input boxes */
        .code-inputs { display:flex; gap:.75rem; justify-content:center; margin:1.5rem 0; }
        .code-digit {
            width: 52px; height: 60px;
            text-align: center; font-size: 1.6rem; font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            background: var(--surface-2, rgba(255,255,255,.04));
            border: 2px solid var(--border);
            border-radius: 14px;
            color: var(--text-strong);
            outline: none;
            transition: all .2s ease;
            caret-color: var(--tech-blue);
        }
        .code-digit:focus { border-color: var(--tech-blue); box-shadow: 0 0 0 3px rgba(37,99,235,.2); background: rgba(37,99,235,.06); }
        .code-digit.filled { border-color: #10b981; background: rgba(16,185,129,.06); }
        .back-link { display:inline-flex; align-items:center; gap:.4rem; color:var(--text-muted); font-size:.85rem; text-decoration:none; }
        .back-link:hover { color:var(--tech-blue); }
        .email-chip { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.06); border:1px solid var(--border); border-radius:8px; padding:.25rem .75rem; font-size:.82rem; font-family:monospace; color:var(--text-muted); margin-top:.25rem; }
        .timer { font-size:.8rem; color:var(--text-muted); text-align:center; margin-top:.75rem; }
        .timer span { color:#f59e0b; font-weight:700; font-family:'JetBrains Mono',monospace; }
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
            <div class="step-badge"><i class="fa-solid fa-shield-halved"></i> Étape 2 / 3</div>
            <h1 class="auth-title">Entrez votre code</h1>
            <p class="auth-sub">
                Un code à 6 chiffres a été envoyé à :<br>
                <span class="email-chip"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($email); ?></span>
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.25rem;color:#fca5a5;font-size:.88rem;display:flex;align-items:center;gap:.6rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="verify_code.php" id="code-form" novalidate>
            <!-- 6 individual digit inputs -->
            <div class="code-inputs">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <input class="code-digit" type="text" inputmode="numeric"
                           id="d<?php echo $i; ?>" autocomplete="off">
                <?php endfor; ?>
            </div>
            <!-- Hidden field that holds the assembled code -->
            <input type="hidden" name="code" id="code-hidden">

            <div class="timer">
                Code valide pendant <span id="countdown">15:00</span>
            </div>

            <button type="submit" class="btn-cart" style="width:100%;justify-content:center;gap:.5rem;margin-top:1.5rem;" id="verify-btn" disabled>
                <i class="fa-solid fa-check-double"></i> Vérifier le code
            </button>
        </form>

        <div style="text-align:center; margin-top:1.5rem; display:flex; justify-content:space-between; align-items:center;">
            <a href="forgot_password.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Changer d'email
            </a>
            <a href="forgot_password.php" class="back-link" style="color:var(--tech-blue);font-weight:600;">
                <i class="fa-solid fa-rotate-right"></i> Renvoyer le code
            </a>
        </div>

    </div>
</div>

<script>
const digits  = document.querySelectorAll('.code-digit');
const hidden  = document.getElementById('code-hidden');
const btn     = document.getElementById('verify-btn');

// Auto-focus next digit & assemble hidden value
digits.forEach((d, idx) => {
    d.addEventListener('input', () => {
        d.value = d.value.replace(/\D/g, '').slice(0, 1);
        if (d.value) {
            d.classList.add('filled');
            if (idx < 5) digits[idx + 1].focus();
        } else {
            d.classList.remove('filled');
        }
        assemble();
    });
    d.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !d.value && idx > 0) {
            digits[idx - 1].focus();
            digits[idx - 1].value = '';
            digits[idx - 1].classList.remove('filled');
            assemble();
        }
    });
    // Allow paste of full code
    d.addEventListener('paste', e => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
        [...pasted].forEach((ch, i) => {
            if (digits[i]) { digits[i].value = ch; digits[i].classList.add('filled'); }
        });
        if (pasted.length === 6) digits[5].focus();
        assemble();
    });
});

function assemble() {
    const code = [...digits].map(d => d.value).join('');
    hidden.value = code;
    btn.disabled = code.length < 6;
}

// Countdown 15 minutes
let secs = 900;
const timer = document.getElementById('countdown');
const iv = setInterval(() => {
    secs--;
    const m = String(Math.floor(secs / 60)).padStart(2,'0');
    const s = String(secs % 60).padStart(2,'0');
    timer.textContent = `${m}:${s}`;
    if (secs <= 0) {
        clearInterval(iv);
        timer.textContent = 'expiré';
        timer.style.color = '#ef4444';
        btn.disabled = true;
    }
}, 1000);

// Focus first digit on load
digits[0].focus();
</script>
<?php include __DIR__ . '/chatbot_widget.php'; ?>
</body>
</html>
