<?php
// views/frontoffice/register.view.php -- Template.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../assets/theme-init.js"></script>
    <title>Créer un compte — FreelaSkill</title>
    <meta name="description" content="Rejoignez FreelaSkill en tant que freelancer ou client et accédez à des milliers de missions.">
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
            padding: 3rem 2rem;
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
            max-width: 520px;
            background: var(--surface-1);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(20px);
            animation: fadeUp 0.5s ease forwards;
            box-shadow: var(--card-shadow);
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
        .auth-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text-strong); }
        .auth-sub { color: var(--text-muted); font-size: 0.9rem; }

        .auth-form { display: flex; flex-direction: column; gap: 1.1rem; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }

        .input-wrap { position: relative; }
        .input-wrap i.field-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--input-icon);
            font-size: 0.9rem;
            transition: var(--transition);
        }
        .input-wrap .form-input { padding-left: 2.6rem; }
        .input-wrap:focus-within i.field-icon { color: var(--tech-blue); }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--input-icon);
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
            background: none;
            border: none;
            padding: 0;
        }
        .toggle-password:hover { color: var(--text-muted); }

        /* Role picker */
        .role-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .role-card {
            border: 2px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1rem;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }
        .role-card:hover   { border-color: rgba(37,99,235,0.4); background: rgba(37,99,235,0.05); }
        .role-card.selected { border-color: var(--tech-blue); background: rgba(37,99,235,0.1); box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
        .role-card .role-icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .role-card .role-name { font-size: 0.95rem; font-weight: 700; color: var(--text-strong); margin-bottom: 0.3rem; }
        .role-card .role-desc { font-size: 0.78rem; color: var(--text-muted); line-height: 1.4; }

        /* Password strength */
        .strength-bar {
            height: 4px;
            border-radius: 4px;
            background: var(--surface-2);
            margin-top: 0.5rem;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.45s cubic-bezier(.4,0,.2,1), background 0.45s ease;
            width: 0%;
        }
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
            padding: 0.42rem 0.7rem;
            border-radius: 999px;
            border: 1px solid rgba(71,85,105,0.5);
            background: rgba(15,23,42,0.7);
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 500;
            line-height: 1;
            min-height: 42px;
            min-width: 0;
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
            font-size: 0.72rem;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1.5px solid rgba(71,85,105,0.6);
            transition: all 0.35s cubic-bezier(.4,0,.2,1);
            flex-shrink: 0;
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
            animation: rulePop 0.35s cubic-bezier(.4,0,.2,1);
        }
        .password-rule.is-valid .rule-icon i {
            color: #10b981;
        }
        @keyframes rulePop {
            0%   { transform: scale(0.7); }
            60%  { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.88rem;
            color: var(--text-muted);
        }
        .auth-footer a { color: var(--tech-blue); font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }

        .auth-divider {
            text-align: center;
            color: var(--placeholder);
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

        .modal-backdrop {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(5px);
            z-index: 1000; display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: var(--transition);
        }
        .modal-backdrop.active { opacity: 1; pointer-events: auto; }
        .step-pane { display: none; }
        .step-pane.active { display: block; animation: fadeUp 0.3s ease; }

        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.75rem;
        }
        .step-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #1e293b;
            transition: var(--transition);
        }
        .step-dot.active { background: var(--tech-blue); width: 24px; border-radius: 4px; }

        @media (max-width: 500px) {
            .form-row { grid-template-columns: 1fr; }
            .role-grid { grid-template-columns: 1fr; }
            .password-rules { gap: 0.35rem; }
            .password-rule {
                gap: 0.25rem;
                padding-inline: 0.35rem;
                font-size: clamp(0.58rem, 2.8vw, 0.72rem);
            }
        }
        .field-error {
            margin-top: 0.45rem;
            color: #fca5a5;
            font-size: 0.8rem;
        }
        .form-input.input-error,
        .role-card.input-error {
            border-color: rgba(239, 68, 68, 0.65);
        }
        html[data-theme='light'] .step-dot { background: #cbd5e1; }

        /* ── AI Password Suggester ── */
        .pw-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.35rem;
        }
        .btn-ai-pwd {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.76rem;
            font-weight: 600;
            color: #a78bfa;
            background: rgba(139,92,246,0.12);
            border: 1px solid rgba(139,92,246,0.3);
            border-radius: 999px;
            padding: 0.28rem 0.75rem;
            cursor: pointer;
            transition: all 0.25s;
            white-space: nowrap;
        }
        .btn-ai-pwd:hover {
            background: rgba(139,92,246,0.25);
            border-color: rgba(139,92,246,0.6);
            color: #c4b5fd;
            transform: scale(1.03);
        }
        .btn-ai-pwd i { font-size: 0.7rem; }
        #ai-pwd-modal {
            position: fixed; inset: 0; z-index: 9000;
            display: flex; align-items: center; justify-content: center;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(6px);
            opacity: 0; pointer-events: none;
            transition: opacity 0.25s;
        }
        #ai-pwd-modal.open { opacity: 1; pointer-events: auto; }
        .ai-pwd-box {
            background: #0f172a;
            border: 1px solid rgba(139,92,246,0.35);
            border-radius: 20px;
            padding: 2rem 1.75rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 60px rgba(139,92,246,0.2);
            animation: fadeUp 0.3s ease;
        }
        .ai-pwd-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; }
        .ai-pwd-title { font-size:1rem; font-weight:700; color:#e2e8f0; display:flex; align-items:center; gap:.5rem; }
        .ai-pwd-close { background:none; border:none; color:#64748b; cursor:pointer; font-size:1rem; transition:color .2s; }
        .ai-pwd-close:hover { color:#e2e8f0; }
        .ai-pwd-subtitle { font-size:.8rem; color:#64748b; margin-bottom:1rem; }
        #ai-pwd-list { display:flex; flex-direction:column; gap:.65rem; }
        .ai-pwd-option {
            display:flex; align-items:center; justify-content:space-between;
            background:rgba(139,92,246,.07); border:1px solid rgba(139,92,246,.2);
            border-radius:12px; padding:.75rem 1rem; cursor:pointer;
            transition:all .2s; font-family:'JetBrains Mono',monospace;
            font-size:.85rem; color:#c4b5fd; word-break:break-all;
        }
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
            <h1 class="auth-title">Créer un compte</h1>
            <p class="auth-sub">Rejoignez des milliers de talents et de clients sur FreelaSkill</p>
        </div>

        <!-- Step dots -->
        <div class="step-indicator">
            <div class="step-dot active" id="dot-1"></div>
            <div class="step-dot" id="dot-2"></div>
            <div class="step-dot" id="dot-3"></div>
        </div>

        <!-- Errors -->
        <?php if (!empty($errors['_global'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($errors['_global']); ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form class="auth-form" method="POST" action="register.php" id="register-form" novalidate>

            <!-- Step 1 : Identity -->
            <div class="form-group">
                <label class="form-label">Choisissez votre rôle</label>
                <div class="role-grid">
                    <label>
                        <input type="radio" name="role" value="freelancer" hidden
                            <?php echo (($data['role'] ?? '') === 'freelancer' ? 'checked' : ''); ?>>
                        <div class="role-card <?php echo (($data['role'] ?? '') === 'freelancer' ? 'selected' : ''); ?><?php echo $fieldError('role') !== '' ? ' input-error' : ''; ?>" onclick="selectRole(this, 'freelancer')" id="card-freelancer">
                            <div class="role-icon">🧑‍💻</div>
                            <div class="role-name">Freelancer</div>
                            <div class="role-desc">Je propose des services et je cherche des missions</div>
                        </div>
                    </label>
                    <label>
                        <input type="radio" name="role" value="client" hidden
                            <?php echo (($data['role'] ?? '') === 'client' ? 'checked' : ''); ?>>
                        <div class="role-card <?php echo (($data['role'] ?? '') === 'client' ? 'selected' : ''); ?><?php echo $fieldError('role') !== '' ? ' input-error' : ''; ?>" onclick="selectRole(this, 'client')" id="card-client">
                            <div class="role-icon">🏢</div>
                            <div class="role-name">Client</div>
                            <div class="role-desc">Je publie des missions et recrute des talents</div>
                        </div>
                    </label>
                </div>
                <?php if ($fieldError('role') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError('role')); ?></div>
                <?php endif; ?>
            </div>

            <div class="divider"></div>

            <!-- Name row -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="nom">Nom</label>
                    <div class="input-wrap">
                        <input class="form-input<?php echo $fieldError('nom') !== '' ? ' input-error' : ''; ?>" type="text" id="nom" name="nom"
                               placeholder="Ben Ali"
                               value="<?php echo htmlspecialchars($data['nom'] ?? ''); ?>"
                               autocomplete="family-name">
                        <i class="fa-solid fa-user field-icon"></i>
                    </div>
                    <?php if ($fieldError('nom') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError('nom')); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="prenom">Prénom</label>
                    <div class="input-wrap">
                        <input class="form-input<?php echo $fieldError('prenom') !== '' ? ' input-error' : ''; ?>" type="text" id="prenom" name="prenom"
                               placeholder="Mohamed"
                               value="<?php echo htmlspecialchars($data['prenom'] ?? ''); ?>"
                               autocomplete="given-name">
                        <i class="fa-solid fa-user field-icon"></i>
                    </div>
                    <?php if ($fieldError('prenom') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError('prenom')); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="email">Adresse email</label>
                <div class="input-wrap">
                    <input class="form-input<?php echo $fieldError('email') !== '' ? ' input-error' : ''; ?>" type="text" id="email" name="email"
                           placeholder="vous@exemple.com"
                           value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
                           autocomplete="email">
                    <i class="fa-solid fa-envelope field-icon"></i>
                </div>
                <?php if ($fieldError('email') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError('email')); ?></div>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div class="form-group">
                <div class="pw-label-row">
                    <label class="form-label" for="password" style="margin:0;">Mot de passe</label>
                    <button type="button" class="btn-ai-pwd" id="btn-suggest-pwd" onclick="openAiPwdModal()">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> IA : sugg&#233;rer un MDP fort
                    </button>
                </div>
                <div class="input-wrap" style="position:relative;">
                    <input class="form-input<?php echo $fieldError('password') !== '' ? ' input-error' : ''; ?>" type="password" id="password" name="password"
                           placeholder="Minimum 8 caracteres"
                           autocomplete="new-password"
                           style="padding-right:3rem;">
                    <i class="fa-solid fa-lock field-icon"></i>
                    <button type="button" class="toggle-password" onclick="togglePwd('password','eye1')">
                        <i class="fa-regular fa-eye" id="eye1"></i>
                    </button>
                </div>
                <?php if ($fieldError('password') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError('password')); ?></div>
                <?php endif; ?>
                <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
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
            <!-- Confirm Password -->
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirmer le mot de passe</label>
                <div class="input-wrap" style="position:relative;">
                    <input class="form-input<?php echo $fieldError('confirm_password') !== '' ? ' input-error' : ''; ?>" type="password" id="confirm_password" name="confirm_password"
                           placeholder="••••••••"
                           autocomplete="new-password"
                           style="padding-right:3rem;">
                    <i class="fa-solid fa-shield-halved field-icon"></i>
                    <button type="button" class="toggle-password" onclick="togglePwd('confirm_password','eye2')">
                        <i class="fa-regular fa-eye" id="eye2"></i>
                    </button>
                </div>
                <?php if ($fieldError('confirm_password') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError('confirm_password')); ?></div>
                <?php endif; ?>
            </div>

            <!-- Bio (optional) -->
            <div class="form-group">
                <div class="pw-label-row">
                    <label class="form-label" for="bio" style="margin:0;">Bio <span style="color:#475569; font-size:0.78rem;">(optionnel)</span></label>
                    <button type="button" class="btn-ai-pwd" id="btn-suggest-bio" onclick="fetchAiBio()">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> IA : suggérer une bio
                    </button>
                </div>
                <textarea class="form-input" id="bio" name="bio" rows="3"
                          placeholder="Quelques mots sur vous ou vos activités…"><?php echo htmlspecialchars($data['bio'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn-cart" id="submit-btn">
                <i class="fa-solid fa-user-plus"></i>
                Créer mon compte
            </button>

        </form>

        <!-- ── AI Password Modal ── -->
        <div id="ai-pwd-modal" role="dialog" aria-modal="true" aria-labelledby="ai-pwd-modal-title">
            <div class="ai-pwd-box">
                <div class="ai-pwd-header">
                    <div class="ai-pwd-title" id="ai-pwd-modal-title"><span>&#128272;</span> MDP sugg&#233;r&#233;s par l&#39;IA</div>
                    <button class="ai-pwd-close" onclick="closeAiPwdModal()" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="ai-pwd-subtitle">Cliquez sur un mot de passe pour l&#39;utiliser automatiquement.</div>
                <div id="ai-pwd-list"></div>
                <button class="ai-pwd-regen" id="ai-pwd-regen-btn" onclick="fetchAiPasswords()" style="display:none;">
                    <i class="fa-solid fa-rotate"></i> Reg&#233;n&#233;rer
                </button>
            </div>
        </div>

        <div class="auth-divider" style="margin-top:1.5rem;">ou</div>

        <!-- CUSTOM GOOGLE BUTTON WITH OAUTH REDIRECT -->
        <?php
        $clientId = "512696696631-585q3lbt2rps9g8o81e8vqr9mijdh8tq.apps.googleusercontent.com";
        $redirectUri = urlencode("http://localhost/projet2222/Views/Frontoffice/google_callback.php");
        $googleOAuthUrl = "https://accounts.google.com/o/oauth2/v2/auth?client_id={$clientId}&redirect_uri={$redirectUri}&response_type=id_token&scope=email%20profile&nonce=12345&response_mode=form_post";
        ?>
        <a href="<?php echo $googleOAuthUrl; ?>" class="btn-google" style="margin-top: 1rem;">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="20" height="20">
            Continuer avec Google
        </a>

        <div class="auth-footer" style="margin-top: 2rem;">
            Vous avez déjà un compte ? <a href="login.php">Se connecter</a>
        </div>

    </div>
</div>

<script>
// ── AI Password Suggester ──────────────────────────────────────────────────
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
    list.innerHTML = `<div class="ai-pwd-spinner"><i class="fa-solid fa-spinner"></i>L'IA génère vos mots de passe…</div>`;

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
    // Reveal both fields while we fill them
    pwdInput.type  = 'text';
    confInput.type = 'text';
    pwdInput.value  = pwd;
    confInput.value = pwd;
    // Trigger strength update
    if (typeof updateStrength === 'function') updateStrength(pwd);
    // Re-hide after short delay
    setTimeout(() => {
        pwdInput.type  = 'password';
        confInput.type = 'password';
    }, 1800);
    closeAiPwdModal();
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fetchAiBio() {
    const bioTextarea = document.getElementById('bio');
    const nom = document.getElementById('nom').value;
    const prenom = document.getElementById('prenom').value;
    const roleInput = document.querySelector('input[name="role"]:checked');
    const role = roleInput ? roleInput.value : 'freelancer';
    const btn = document.getElementById('btn-suggest-bio');
    
    if (!btn) return;

    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Génération...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('nom', nom);
    formData.append('prenom', prenom);
    formData.append('role', role);
    formData.append('bio', bioTextarea.value);

    fetch('bio_suggest_api.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.bio) {
            bioTextarea.value = data.bio;
            if (window.BadWordsGuard && typeof BadWordsGuard.check === 'function') {
                BadWordsGuard.check('bio', 'badwords_api.php');
            }
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erreur lors de la génération de la bio.');
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}
// ──────────────────────────────────────────────────────────────────────────


// Role selector
function selectRole(card, role) {
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    // Check the hidden radio
    document.querySelector('input[name="role"][value="' + role + '"]').checked = true;
    // Animate dots
    updateStepDots(2);
}

// Step dots
function updateStepDots(step) {
    for (let i = 1; i <= 3; i++) {
        const dot = document.getElementById('dot-' + i);
        dot.classList.toggle('active', i <= step);
    }
}

// Toggle show/hide password
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

// Password strength
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
    if (score >= 1) updateStepDots(3);
    else            updateStepDots(2);
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
// Loading state
document.getElementById('register-form').addEventListener('submit', function(e) {
    if (typeof BadWordsGuard !== 'undefined' && BadWordsGuard.isBlocked('bio')) {
        e.preventDefault();
        alert('Votre bio contient du contenu inapproprié. Merci de la modifier avant de continuer.');
        return;
    }
    const btn = document.getElementById('submit-btn');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Création en cours…';
    btn.disabled  = true;
});
</script>

<?php include __DIR__ . '/chatbot_widget.php'; ?>
<?php include __DIR__ . '/translate_widget.php'; ?>
<script src="../assets/badwords.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    BadWordsGuard.watch('bio', 'badwords_api.php', { delay: 900 });
});
</script>
</body>
</html>
