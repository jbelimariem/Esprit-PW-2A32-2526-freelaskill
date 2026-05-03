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

        /* ── AI Password Suggester ── */
        .pw-label-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:0.35rem; }
        .btn-ai-pwd { display:inline-flex; align-items:center; gap:.35rem; font-size:.76rem; font-weight:600; color:#a78bfa; background:rgba(139,92,246,.12); border:1px solid rgba(139,92,246,.3); border-radius:999px; padding:.28rem .75rem; cursor:pointer; transition:all .25s; white-space:nowrap; }
        .btn-ai-pwd:hover { background:rgba(139,92,246,.25); border-color:rgba(139,92,246,.6); color:#c4b5fd; transform:scale(1.03); }
        .btn-ai-pwd i { font-size:.7rem; }
        #ai-pwd-modal { position:fixed; inset:0; z-index:9000; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,.65); backdrop-filter:blur(6px); opacity:0; pointer-events:none; transition:opacity .25s; }
        #ai-pwd-modal.open { opacity:1; pointer-events:auto; }
        .ai-pwd-box { background:#0f172a; border:1px solid rgba(139,92,246,.35); border-radius:20px; padding:2rem 1.75rem; width:100%; max-width:400px; box-shadow:0 0 60px rgba(139,92,246,.2); animation:fadeUp .3s ease; }
        .ai-pwd-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; }
        .ai-pwd-title { font-size:1rem; font-weight:700; color:#e2e8f0; display:flex; align-items:center; gap:.5rem; }
        .ai-pwd-close { background:none; border:none; color:#64748b; cursor:pointer; font-size:1rem; transition:color .2s; }
        .ai-pwd-close:hover { color:#e2e8f0; }
        .ai-pwd-subtitle { font-size:.8rem; color:#64748b; margin-bottom:1rem; }
        #ai-pwd-list { display:flex; flex-direction:column; gap:.65rem; }
        .ai-pwd-option { display:flex; align-items:center; justify-content:space-between; background:rgba(139,92,246,.07); border:1px solid rgba(139,92,246,.2); border-radius:12px; padding:.75rem 1rem; cursor:pointer; transition:all .2s; font-family:'JetBrains Mono',monospace; font-size:.85rem; color:#c4b5fd; word-break:break-all; }
        .ai-pwd-option:hover { background:rgba(139,92,246,.2); border-color:rgba(139,92,246,.5); color:#a78bfa; }
        .ai-pwd-option i { font-size:.75rem; color:#7c3aed; margin-left:.5rem; flex-shrink:0; }
        .ai-pwd-spinner { text-align:center; padding:1.5rem 0; color:#a78bfa; font-size:.88rem; }
        .ai-pwd-spinner i { font-size:1.5rem; display:block; margin-bottom:.5rem; animation:fa-spin 1s linear infinite; }
        .ai-pwd-error { background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.3); border-radius:10px; padding:.7rem .9rem; color:#fca5a5; font-size:.82rem; text-align:center; }
        .ai-pwd-regen { margin-top:1rem; width:100%; display:flex; align-items:center; justify-content:center; gap:.4rem; background:transparent; border:1px dashed rgba(139,92,246,.35); border-radius:10px; color:#7c3aed; font-size:.8rem; font-weight:600; padding:.55rem; cursor:pointer; transition:all .2s; }
        .ai-pwd-regen:hover { background:rgba(139,92,246,.1); border-color:rgba(139,92,246,.55); color:#a78bfa; }
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
                    <div class="pw-label-row">
                        <label class="form-label" style="margin:0;">Nouveau mot de passe</label>
                        <button type="button" class="btn-ai-pwd" onclick="openAiPwdModal()">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> IA : sugg&#233;rer un MDP fort
                        </button>
                    </div>
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

        <!-- ── AI Password Modal ── -->
        <div id="ai-pwd-modal" role="dialog" aria-modal="true" aria-labelledby="ai-pwd-reset-title">
            <div class="ai-pwd-box">
                <div class="ai-pwd-header">
                    <div class="ai-pwd-title" id="ai-pwd-reset-title"><span>&#128272;</span> MDP sugg&#233;r&#233;s par l&#39;IA</div>
                    <button class="ai-pwd-close" onclick="closeAiPwdModal()" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="ai-pwd-subtitle">Cliquez sur un mot de passe pour l&#39;utiliser automatiquement.</div>
                <div id="ai-pwd-list"></div>
                <button class="ai-pwd-regen" id="ai-pwd-regen-btn" onclick="fetchAiPasswords()" style="display:none;">
                    <i class="fa-solid fa-rotate"></i> Reg&#233;n&#233;rer
                </button>
            </div>
        </div>

    </div>
</div>

<script>
// ── AI Password Suggester ─────────────────────────────────────────────────────────────────
const AI_PWD_API = 'password_suggest_api.php';

function openAiPwdModal() {
    document.getElementById('ai-pwd-modal').classList.add('open');
    fetchAiPasswords();
}
function closeAiPwdModal() {
    document.getElementById('ai-pwd-modal').classList.remove('open');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeAiPwdModal();
});
document.getElementById('ai-pwd-modal').addEventListener('click', function(e) {
    if (e.target === this) closeAiPwdModal();
});

function fetchAiPasswords() {
    const list = document.getElementById('ai-pwd-list');
    const regenBtn = document.getElementById('ai-pwd-regen-btn');
    regenBtn.style.display = 'none';
    list.innerHTML = `<div class="ai-pwd-spinner"><i class="fa-solid fa-spinner"></i>L'IA g&#233;n&#232;re vos mots de passe&hellip;</div>`;
    fetch(AI_PWD_API, { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            if (!data.passwords || !data.passwords.length) throw new Error('Réponse vide.');
            renderPasswords(data.passwords);
        })
        .catch(err => {
            list.innerHTML = `<div class="ai-pwd-error"><i class="fa-solid fa-circle-exclamation"></i> ${err.message || 'Erreur réseau.'}</div>`;
        })
        .finally(() => { regenBtn.style.display = 'flex'; });
}

function renderPasswords(passwords) {
    const list = document.getElementById('ai-pwd-list');
    list.innerHTML = '';
    passwords.forEach(pwd => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ai-pwd-option';
        btn.innerHTML = `<span>${escHtml(pwd)}</span><i class="fa-solid fa-arrow-right-to-bracket"></i>`;
        btn.addEventListener('click', () => applyPassword(pwd));
        list.appendChild(btn);
    });
}

function applyPassword(pwd) {
    const pwdInput  = document.getElementById('password');
    const confInput = document.getElementById('confirm_password');
    pwdInput.type  = 'text';
    if (confInput) { confInput.type = 'text'; confInput.value = pwd; }
    pwdInput.value = pwd;
    if (typeof updateStrength === 'function') updateStrength(pwd);
    setTimeout(() => {
        pwdInput.type = 'password';
        if (confInput) confInput.type = 'password';
    }, 1800);
    closeAiPwdModal();
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
// ─────────────────────────────────────────────────────────────────
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
<?php include __DIR__ . '/translate_widget.php'; ?>
</body>
</html>
