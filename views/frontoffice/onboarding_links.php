<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}
// Clients don't need this step
if ($_SESSION['user_role'] !== 'freelancer') {
    header('Location: profile.php'); exit;
}

require_once __DIR__ . '/../../controllers/ProfileController.php';
$userController = new ProfileController();
$userId    = (int) $_SESSION['user_id'];
$user      = $userController->getById($userId);

// Already filled? Skip
if (!empty($user->getGithubUrl()) || !empty($user->getLinkedinUrl())) {
    if (empty($_GET['force'])) {
        header('Location: profile.php'); exit;
    }
}

$errors = $userController->handleOnboardingLinks($user);
$fieldError = function ($field) use ($errors) {
    return $errors[$field] ?? '';
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../assets/theme-init.js"></script>
    <title>Complétez votre profil — FreelaSkill</title>
    <meta name="description" content="Ajoutez vos liens GitHub et LinkedIn pour maximiser votre visibilité sur FreelaSkill.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/theme.js" defer></script>
    <style>
        body { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; position: relative; overflow-x: hidden; }

        /* Ambient glows */
        .glow-top    { position: fixed; top: -180px; right: -120px; width: 650px; height: 650px; background: radial-gradient(circle, rgba(59,130,246,.13), transparent 60%); pointer-events: none; animation: floatGlow 12s ease-in-out infinite alternate; }
        .glow-bottom { position: fixed; bottom: -150px; left: -80px;  width: 500px; height: 500px; background: radial-gradient(circle, rgba(139,92,246,.09), transparent 60%); pointer-events: none; animation: floatGlow 18s ease-in-out infinite alternate-reverse; }

        /* Step pills */
        .step-pills {
            display: flex; align-items: center; gap: .5rem;
            margin-bottom: 2rem;
        }
        .step-pill {
            padding: .35rem 1rem;
            border-radius: 999px;
            font-size: .78rem; font-weight: 600;
            border: 1px solid var(--border);
            color: #475569;
            background: var(--surface-2);
            letter-spacing: .02em;
            transition: all .3s;
        }
        .step-pill.done  { border-color: rgba(16,185,129,.5); color: #6ee7b7; background: rgba(16,185,129,.1); }
        .step-pill.active{ border-color: var(--tech-blue); color: white; background: rgba(37,99,235,.18); box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
        .step-sep { color: #334155; font-size: .7rem; }

        /* Card */
        .ob-card {
            width: 100%; max-width: 700px;
            background: var(--surface-1);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(20px);
            animation: fadeUp .45s ease forwards;
            position: relative; z-index: 1;
            box-shadow: var(--card-shadow);
        }
        .ob-title { font-size: 2rem; font-weight: 800; color: var(--text-strong); margin-bottom: .5rem; line-height: 1.2; }
        .ob-sub   { color: var(--text-muted); font-size: .95rem; margin-bottom: 2rem; }

        /* Two-column layout */
        .ob-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        @media (max-width: 560px) { .ob-grid { grid-template-columns: 1fr; } }

        /* Social icon colors */
        .social-icon-wrap.github    { background: rgba(255,255,255,.08); color: white; }
        .social-icon-wrap.linkedin  { background: rgba(10,102,194,.2);   color: #60a5fa; }
        .social-icon-wrap.cv        { background: rgba(234,179,8,.15);   color: #fbbf24; }
        .social-icon-wrap.portfolio { background: rgba(139,92,246,.18);  color: #a78bfa; }

        /* Social input card */
        .social-card {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            transition: border-color .25s;
        }
        .social-card:focus-within { border-color: rgba(37,99,235,.5); }
        .social-card.github:focus-within  { border-color: rgba(255,255,255,.3); }
        .social-card.linkedin:focus-within { border-color: rgba(10,102,194,.6); }

        .social-icon-wrap {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        .social-icon-wrap.github   { background: rgba(255,255,255,.08); color: white; }
        .social-icon-wrap.linkedin { background: rgba(10,102,194,.2);   color: #60a5fa; }

        .social-label { font-size: .82rem; font-weight: 700; color: #94a3b8; letter-spacing: .04em; text-transform: uppercase; margin-bottom: .5rem; }
        .social-input {
            width: 100%; background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 10px; color: var(--text-strong);
            padding: .6rem .9rem;
            font-family: 'Space Grotesk', sans-serif; font-size: .88rem;
            outline: none; box-sizing: border-box;
            transition: border-color .2s;
        }
        .social-input::placeholder { color: var(--placeholder); }
        .social-input:focus { border-color: rgba(37,99,235,.6); }

        /* "No links" skip card */
        .skip-card {
            background: var(--surface-1);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            display: flex; flex-direction: column; gap: .3rem;
        }
        .skip-title { font-size: .95rem; font-weight: 700; color: var(--text-strong); }
        .skip-desc  { font-size: .83rem; color: var(--text-muted); line-height: 1.5; }

        /* Buttons row */
        .ob-actions {
            display: flex; align-items: center; justify-content: flex-end;
            gap: .85rem; margin-top: 1.5rem;
        }
        .btn-skip {
            background: none; border: 1px solid rgba(255,255,255,.1);
            color: var(--text-muted); padding: .65rem 1.5rem;
            border-radius: 999px; font-size: .88rem; font-weight: 600;
            cursor: pointer; font-family: 'Space Grotesk', sans-serif;
            transition: all .2s; text-decoration: none;
        }
        .btn-skip:hover { border-color: rgba(255,255,255,.25); color: var(--text-strong); }
        .btn-next {
            background: linear-gradient(135deg, var(--tech-blue), #3b82f6);
            color: white; padding: .7rem 2rem;
            border-radius: 999px; font-size: .92rem; font-weight: 700;
            border: none; cursor: pointer;
            font-family: 'Space Grotesk', sans-serif;
            box-shadow: 0 4px 18px rgba(37,99,235,.35);
            transition: all .25s;
        }
        .btn-next:hover { transform: translateY(-1px); box-shadow: 0 6px 22px rgba(37,99,235,.45); }

        .error-box {
            background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3);
            border-radius: 12px; padding: .85rem 1.25rem;
            color: #fca5a5; font-size: .88rem; margin-bottom: 1.25rem;
        }
        .field-error {
            margin-top: .55rem;
            color: #fca5a5;
            font-size: .8rem;
        }
        .social-input.input-error,
        .social-card.input-error,
        .input-error {
            border-color: rgba(239,68,68,.5) !important;
        }

        .login-hint { text-align: center; margin-top: 1.5rem; font-size: .85rem; color: var(--text-muted); }
        .login-hint a { color: var(--tech-blue); font-weight: 600; }
        html[data-theme='light'] .step-sep { color: #94a3b8; }
        html[data-theme='light'] .skip-desc strong[style*='color:white'] { color: var(--text-strong) !important; }
    </style>
</head>
<body class="page-anim">

<div class="glow-top"></div>
<div class="glow-bottom"></div>
<button type="button" class="theme-toggle theme-toggle--floating" data-theme-toggle>
    <i class="fa-solid fa-sun" data-theme-icon></i>
    <span data-theme-label>Jour</span>
</button>

<!-- Logo -->
<div style="position:relative;z-index:1;margin-bottom:1.75rem;font-size:1.3rem;font-weight:700;display:flex;align-items:center;gap:.5rem;">
    <i class="fa-solid fa-shapes" style="color:var(--tunisian-red);"></i>
    Freela<span style="color:var(--tech-blue);">Skill</span>
</div>

<!-- Step pills -->
<div class="step-pills" style="position:relative;z-index:1;">
    <span class="step-pill done"><i class="fa-solid fa-check" style="font-size:.65rem;margin-right:.3rem;"></i> Compte</span>
    <span class="step-sep"><i class="fa-solid fa-chevron-right"></i></span>
    <span class="step-pill done"><i class="fa-solid fa-check" style="font-size:.65rem;margin-right:.3rem;"></i> Profil</span>
    <span class="step-sep"><i class="fa-solid fa-chevron-right"></i></span>
    <span class="step-pill active">Réseaux</span>
</div>

<div class="ob-card">

    <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.08em;">Étape finale</p>
    <h1 class="ob-title">Réseaux &amp; Documents<br><span style="color:var(--tech-blue);">GitHub, LinkedIn, CV &amp; Portfolio</span></h1>
    <p class="ob-sub">Renforcez votre profil freelancer et attirez plus de clients.<br>Vous pourrez modifier ces informations à tout moment dans votre profil.</p>

    <?php if (!empty($errors['_global'])): ?>
        <div class="error-box"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($errors['_global']); ?></div>
    <?php endif; ?>

    <form method="POST" action="onboarding_links.php" id="links-form" enctype="multipart/form-data" novalidate>

        <div class="ob-grid">

            <!-- GitHub -->
            <div class="social-card github<?php echo $fieldError('github_url') !== '' ? ' input-error' : ''; ?>">
                <div class="social-icon-wrap github">
                    <i class="fa-brands fa-github"></i>
                </div>
                <div class="social-label">GitHub</div>
                <input class="social-input<?php echo $fieldError('github_url') !== '' ? ' input-error' : ''; ?>" type="text" name="github_url" id="github_url"
                       placeholder="https://github.com/votre-profil"
                       value="<?php echo htmlspecialchars($_POST['github_url'] ?? $user->getGithubUrl() ?? ''); ?>">
                <?php if ($fieldError('github_url') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError('github_url')); ?></div>
                <?php endif; ?>
                <div style="font-size:.74rem;color:#334155;margin-top:.5rem;">
                    <i class="fa-solid fa-circle-info" style="margin-right:.3rem;"></i>
                    Partagez vos projets open source
                </div>
            </div>

            <!-- LinkedIn -->
            <div class="social-card linkedin<?php echo $fieldError('linkedin_url') !== '' ? ' input-error' : ''; ?>">
                <div class="social-icon-wrap linkedin">
                    <i class="fa-brands fa-linkedin-in"></i>
                </div>
                <div class="social-label">LinkedIn</div>
                <input class="social-input<?php echo $fieldError('linkedin_url') !== '' ? ' input-error' : ''; ?>" type="text" name="linkedin_url" id="linkedin_url"
                       placeholder="https://linkedin.com/in/votre-profil"
                       value="<?php echo htmlspecialchars($_POST['linkedin_url'] ?? $user->getLinkedinUrl() ?? ''); ?>">
                <?php if ($fieldError('linkedin_url') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError('linkedin_url')); ?></div>
                <?php endif; ?>
                <div style="font-size:.74rem;color:#334155;margin-top:.5rem;">
                    <i class="fa-solid fa-circle-info" style="margin-right:.3rem;"></i>
                    Boostez votre crédibilité pro
                </div>
            </div>

            <!-- CV (file upload) -->
            <div class="social-card cv<?php echo $fieldError('cv_file') !== '' ? ' input-error' : ''; ?>" style="border-color:rgba(234,179,8,.2); position: relative;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <div class="social-icon-wrap cv">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <div class="social-label">CV <span style="font-size:.68rem;color:#475569;font-weight:500;text-transform:none;">(PDF · max 5 MB)</span></div>
                    </div>
                </div>
                <div id="cv-dropzone" onclick="document.getElementById('cv_file_ob').click();"
                     style="display:flex;flex-direction:column;align-items:center;gap:.45rem;
                            padding:1rem .75rem;border:2px dashed rgba(234,179,8,.3);border-radius:10px;
                            background:rgba(234,179,8,.04);cursor:pointer;
                            transition:border-color .2s,background .2s;margin-top:.35rem; position:relative;"
                     onmouseover="this.style.borderColor='rgba(234,179,8,.6)';this.style.background='rgba(234,179,8,.08)';"
                     onmouseout="this.style.borderColor='rgba(234,179,8,.3)';this.style.background='rgba(234,179,8,.04)';">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.3rem;color:#fbbf24;opacity:.75;pointer-events:none;"></i>
                    <span style="font-size:.78rem;color:#64748b;text-align:center;pointer-events:none;">Cliquez pour choisir ou modifier<br><strong style="color:#fbbf24;">votre CV (.pdf)</strong></span>
                    <span style="font-size:.7rem;color:#334155;pointer-events:none;" id="cv-ob-filename">Aucun fichier sélectionné</span>
                </div>
                <button type="button" id="cv-remove-btn" onclick="removeCv(event)" 
                        style="display:none; position:absolute; top:1.5rem; right:1.5rem; background:rgba(239,68,68,.15); color:#fca5a5; border:1px solid rgba(239,68,68,.3); border-radius:50%; width:30px; height:30px; cursor:pointer; align-items:center; justify-content:center; transition:all .2s;"
                        title="Supprimer le fichier">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <input type="file" name="cv_file" id="cv_file_ob" accept=".pdf"
                       style="position:absolute;width:0;height:0;opacity:0;overflow:hidden;"
                       onchange="updateCvUI(this)">
                <?php if ($fieldError('cv_file') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError('cv_file')); ?></div>
                <?php endif; ?>
            </div>

            <!-- Portfolio (file upload) -->
            <div class="social-card portfolio<?php echo $fieldError('portfolio_file') !== '' ? ' input-error' : ''; ?>" style="border-color:rgba(139,92,246,.2); position: relative;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <div class="social-icon-wrap portfolio">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                        <div class="social-label">Portfolio <span style="font-size:.68rem;color:#475569;font-weight:500;text-transform:none;">(PDF/ZIP/DOCX · max 5 MB)</span></div>
                    </div>
                </div>
                <div id="portfolio-dropzone" onclick="document.getElementById('portfolio_file_ob').click();"
                     style="display:flex;flex-direction:column;align-items:center;gap:.45rem;
                            padding:1rem .75rem;border:2px dashed rgba(139,92,246,.35);border-radius:10px;
                            background:rgba(139,92,246,.04);cursor:pointer;
                            transition:border-color .2s,background .2s;margin-top:.35rem; position:relative;"
                     onmouseover="this.style.borderColor='rgba(139,92,246,.65)';this.style.background='rgba(139,92,246,.09)';"
                     onmouseout="this.style.borderColor='rgba(139,92,246,.35)';this.style.background='rgba(139,92,246,.04)';">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.3rem;color:#a78bfa;opacity:.75;pointer-events:none;"></i>
                    <span style="font-size:.78rem;color:#64748b;text-align:center;pointer-events:none;">Cliquez pour choisir ou modifier<br><strong style="color:#a78bfa;">votre Portfolio (.pdf .zip .docx)</strong></span>
                    <span style="font-size:.7rem;color:#334155;pointer-events:none;" id="portfolio-ob-filename">Aucun fichier sélectionné</span>
                </div>
                <button type="button" id="portfolio-remove-btn" onclick="removePortfolio(event)" 
                        style="display:none; position:absolute; top:1.5rem; right:1.5rem; background:rgba(239,68,68,.15); color:#fca5a5; border:1px solid rgba(239,68,68,.3); border-radius:50%; width:30px; height:30px; cursor:pointer; align-items:center; justify-content:center; transition:all .2s;"
                        title="Supprimer le fichier">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <input type="file" name="portfolio_file" id="portfolio_file_ob" accept=".pdf,.zip,.docx"
                       style="position:absolute;width:0;height:0;opacity:0;overflow:hidden;"
                       onchange="updatePortfolioUI(this)">
                <?php if ($fieldError('portfolio_file') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError('portfolio_file')); ?></div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Skip card -->
        <div class="skip-card">
            <div class="skip-title">🔗 Pas de liens / fichiers pour l'instant ?</div>
            <div class="skip-desc">Aucun problème — vous pouvez compléter votre profil à tout moment depuis l'onglet <strong style="color:white;">Réseaux</strong> de votre profil.</div>
        </div>

        <div class="ob-actions">
            <a href="profile.php" class="btn-skip">
                <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right:.3rem;font-size:.8rem;"></i>
                Importer plus tard
            </a>
            <button type="submit" class="btn-next" id="submit-btn">
                Terminer &nbsp;<i class="fa-solid fa-check"></i>
            </button>
        </div>

    </form>

</div>

<div class="login-hint">
    Déjà un compte ? <a href="login.php">Se connecter</a>
</div>

<script>
function updateCvUI(input) {
    const filenameSpan = document.getElementById('cv-ob-filename');
    const removeBtn = document.getElementById('cv-remove-btn');
    const dropzone = document.getElementById('cv-dropzone');
    
    if (input.files && input.files[0]) {
        filenameSpan.textContent = input.files[0].name;
        filenameSpan.style.color = 'white';
        removeBtn.style.display = 'flex';
        dropzone.style.borderColor = 'rgba(234,179,8,.8)';
        dropzone.style.background = 'rgba(234,179,8,.1)';
    } else {
        filenameSpan.textContent = 'Aucun fichier sélectionné';
        filenameSpan.style.color = '#334155';
        removeBtn.style.display = 'none';
        dropzone.style.borderColor = 'rgba(234,179,8,.3)';
        dropzone.style.background = 'rgba(234,179,8,.04)';
    }
}

function removeCv(event) {
    event.stopPropagation();
    const input = document.getElementById('cv_file_ob');
    input.value = '';
    updateCvUI(input);
}

function updatePortfolioUI(input) {
    const filenameSpan = document.getElementById('portfolio-ob-filename');
    const removeBtn = document.getElementById('portfolio-remove-btn');
    const dropzone = document.getElementById('portfolio-dropzone');
    
    if (input.files && input.files[0]) {
        filenameSpan.textContent = input.files[0].name;
        filenameSpan.style.color = 'white';
        removeBtn.style.display = 'flex';
        dropzone.style.borderColor = 'rgba(139,92,246,.8)';
        dropzone.style.background = 'rgba(139,92,246,.1)';
    } else {
        filenameSpan.textContent = 'Aucun fichier sélectionné';
        filenameSpan.style.color = '#334155';
        removeBtn.style.display = 'none';
        dropzone.style.borderColor = 'rgba(139,92,246,.35)';
        dropzone.style.background = 'rgba(139,92,246,.04)';
    }
}

function removePortfolio(event) {
    event.stopPropagation();
    const input = document.getElementById('portfolio_file_ob');
    input.value = '';
    updatePortfolioUI(input);
}

document.getElementById('links-form').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement…';
    btn.disabled = true;
});
</script>

</body>
</html>

