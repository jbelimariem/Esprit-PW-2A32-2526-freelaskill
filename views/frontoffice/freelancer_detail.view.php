<?php // views/frontoffice/freelancer_detail.view.php ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($offre->getTitre()) ?> — Détails Mission</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/theme-light.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <style>
        /* ── Modal Overlay ── */
        .modal-overlay {
            display: none;
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.75);
            backdrop-filter: blur(6px);
            align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }

        .modal-box {
            background: linear-gradient(160deg, #0f172a 0%, #1e293b 100%);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 28px;
            width: min(680px, 95vw);
            max-height: 90vh;
            overflow-y: auto;
            padding: 2.5rem;
            box-shadow: 0 40px 80px -20px rgba(0,0,0,0.8), inset 0 1px 0 rgba(255,255,255,0.08);
            animation: modalSlideIn 0.35s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes modalSlideIn {
            from { opacity:0; transform: translateY(40px) scale(0.96); }
            to   { opacity:1; transform: translateY(0)   scale(1); }
        }

        .modal-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 2rem; padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .modal-title { font-size: 1.4rem; font-weight: 700; color: white; }
        .modal-subtitle { font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; }
        .modal-close {
            width: 38px; height: 38px; border-radius: 50%;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-muted); cursor: pointer; font-size: 1.1rem;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .modal-close:hover { background: rgba(239,68,68,0.15); color: #ef4444; border-color: rgba(239,68,68,0.3); }

        /* ── Form Fields ── */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-group.full { grid-column: 1 / -1; }
        .form-label {
            font-size: 0.8rem; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.8px;
        }
        .form-label span.req { color: #f87171; margin-left: 3px; }
        .form-input, .form-textarea {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; color: white; font-size: 0.95rem;
            font-family: 'Space Grotesk', sans-serif;
            padding: 0.85rem 1.1rem;
            transition: border-color 0.25s, box-shadow 0.25s;
            outline: none; width: 100%; box-sizing: border-box;
        }
        .form-input:focus, .form-textarea:focus {
            border-color: rgba(96,165,250,0.6);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }
        .form-textarea { resize: vertical; min-height: 130px; }

        /* ── CV Upload Zone ── */
        .cv-upload-zone {
            border: 2px dashed rgba(255,255,255,0.15);
            border-radius: 16px; padding: 2rem;
            text-align: center; cursor: pointer;
            transition: all 0.3s; position: relative;
            background: rgba(255,255,255,0.02);
        }
        .cv-upload-zone:hover, .cv-upload-zone.drag-over {
            border-color: rgba(96,165,250,0.5);
            background: rgba(59,130,246,0.06);
        }
        .cv-upload-zone input[type="file"] {
            position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
        }
        .cv-icon { font-size: 2.5rem; margin-bottom: 0.75rem; color: var(--tech-blue); }
        .cv-hint { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.4rem; }
        .cv-filename {
            display: none; margin-top: 0.75rem; font-size: 0.85rem;
            color: #10b981; font-weight: 600;
        }

        /* ── Errors ── */
        .form-errors {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 12px; padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        .form-errors li { color: #f87171; font-size: 0.88rem; margin: 4px 0; }

        /* ── Submit Button ── */
        .btn-submit-modal {
            width: 100%; padding: 1.1rem; border-radius: 14px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white; font-size: 1rem; font-weight: 700;
            border: none; cursor: pointer; letter-spacing: 0.5px;
            box-shadow: 0 10px 25px -5px rgba(59,130,246,0.45);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-top: 1.5rem;
        }
        .btn-submit-modal:hover { transform: translateY(-2px); box-shadow: 0 16px 35px -8px rgba(59,130,246,0.6); }
    </style>
</head>
<body class="page-anim">

<nav>
    <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
    <ul class="nav-links">
        <li><span style="color:var(--text-muted);cursor:default;">Accueil</span></li>
        <li><a href="home.php">Marketplace</a></li>
        <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'client'): ?>
            <li><a href="missions.php">Missions</a></li>
        <?php else: ?>
            <li><a href="freelancer_home.php" class="active">Freelancers</a></li>
        <?php endif; ?>
        <li><a href="profile.php">Mon Profil</a></li>
    </ul>
    <div class="nav-right">
        <div class="nav-avatar">FR</div>
    </div>
</nav>

<div class="marketplace-layout">
    <aside class="mkt-sidebar">
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-store"></i></div>
                <div class="mkt-profile-name">Espace Freelancer</div>
                <div class="mkt-profile-sub">FreelaSkill Tunisia</div>
            </div>
        </div>
        <div class="mkt-sidebar-card">
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="freelancer_home.php" class="nav-item active"><i class="fa-solid fa-briefcase"></i> Missions</a>
                <a href="freelancer_applications.php" class="nav-item"><i class="fa-solid fa-paper-plane"></i> Candidatures</a>
                <a href="#" id="export-pdf" class="nav-item"><i class="fa-solid fa-file-pdf"></i> Export PDF</a>
                <a href="freelancer_home.php" class="nav-item danger"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            </div>
        </div>
    </aside>

    <div class="mkt-main">

        <section class="hero-banner" style="padding:3rem 4rem; position:relative; overflow:hidden;">
            <div class="hero-glow" style="width:800px; height:800px; top:-400px; left:-200px; opacity:0.8;"></div>
            <div class="hero-content" style="position:relative; z-index:2; text-align:center; max-width:900px; margin:0 auto; background:rgba(255,255,255,0.03); padding:3rem; border-radius:32px; border:1px solid rgba(255,255,255,0.08); backdrop-filter:blur(20px);">
                <div class="hero-tag" style="margin:0 auto 1.5rem; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.3); color:#60a5fa;"><i class="fa-solid fa-briefcase"></i> Détails de la mission</div>
                <h1 class="hero-title" style="font-size:3rem; margin-bottom:0.5rem;"><?= htmlspecialchars($offre->getTitre()) ?></h1>
            </div>
        </section>

        <div class="page-body" style="display:grid; grid-template-columns:1fr 360px; gap:2.5rem; padding-top:2rem; max-width:1200px; margin:0 auto;">
            <div style="display:flex; flex-direction:column; gap:2.5rem;">

                <!-- Description -->
                <div class="detail-card" style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:28px; overflow:hidden; backdrop-filter:blur(20px);">
                    <div class="card-body" style="padding:3rem;">
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:1.5rem;">
                            <div style="width:40px; height:40px; border-radius:12px; background:rgba(59,130,246,0.1); display:flex; align-items:center; justify-content:center; color:var(--tech-blue); font-size:1.2rem;"><i class="fa-solid fa-align-left"></i></div>
                            <h2 style="font-size:1.2rem; font-weight:700; color:white;">Description de la mission</h2>
                        </div>
                        <p style="white-space:pre-wrap; line-height:1.8; color:var(--text-light); font-size:1.05rem; opacity:0.9;"><?= nl2br(htmlspecialchars($offre->getDescription())) ?></p>
                    </div>
                </div>

                <!-- Compétences -->
                <div class="detail-card" style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:28px; overflow:hidden; backdrop-filter:blur(20px); padding:3rem;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:1.5rem;">
                        <div style="width:40px; height:40px; border-radius:12px; background:rgba(139,92,246,0.1); display:flex; align-items:center; justify-content:center; color:#8b5cf6; font-size:1.2rem;"><i class="fa-solid fa-layer-group"></i></div>
                        <h2 style="font-size:1.2rem; font-weight:700; color:white;">Compétences requises</h2>
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:12px;">
                        <?php foreach(explode(',', $offre->getCompetences()) as $skill): ?>
                            <span class="chip" style="background:linear-gradient(135deg,rgba(59,130,246,0.15),rgba(139,92,246,0.15)); border:1px solid rgba(139,92,246,0.3); color:white; padding:10px 20px; font-size:0.95rem; font-weight:600; border-radius:12px;"><?= trim(htmlspecialchars($skill)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <!-- Sidebar action -->
            <aside>
                <div style="position:sticky; top:100px;">
                    <div class="detail-card" style="background:linear-gradient(180deg,rgba(30,41,59,0.6) 0%,rgba(15,23,42,0.8) 100%); border:1px solid rgba(255,255,255,0.1); border-radius:28px; padding:2.5rem; backdrop-filter:blur(30px); box-shadow:0 20px 50px -10px rgba(0,0,0,0.5);">

                        <!-- Budget -->
                        <div style="text-align:center; margin-bottom:2.5rem; padding-bottom:2rem; border-bottom:1px solid rgba(255,255,255,0.08);">
                            <div style="color:var(--text-muted); font-size:0.9rem; text-transform:uppercase; letter-spacing:2px; font-weight:600; margin-bottom:1rem;">Budget Proposé</div>
                            <div style="font-size:3rem; font-weight:800; font-family:'JetBrains Mono',monospace; background:linear-gradient(135deg,#60a5fa,#a78bfa); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"><?= number_format($offre->getBudget(), 0, ',', ' ') ?></div>
                            <div style="font-size:1.2rem; color:var(--text-muted); font-weight:600; margin-top:0.5rem;">Dinars (DT)</div>
                        </div>

                        <!-- Infos -->
                        <div style="display:flex; flex-direction:column; gap:1.5rem; margin-bottom:2.5rem;">
                            <div style="display:flex; align-items:center; gap:15px; padding:1rem 1.25rem; background:rgba(255,255,255,0.02); border-radius:16px; border:1px solid rgba(255,255,255,0.05);">
                                <div style="width:36px; height:36px; border-radius:10px; background:rgba(16,185,129,0.1); display:flex; align-items:center; justify-content:center; color:#10b981;"><i class="fa-solid fa-clock"></i></div>
                                <div>
                                    <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:2px;">Délai estimé</div>
                                    <div style="font-weight:600; color:white;"><?= htmlspecialchars($offre->getDelai()) ?></div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:15px; padding:1rem 1.25rem; background:rgba(255,255,255,0.02); border-radius:16px; border:1px solid rgba(255,255,255,0.05);">
                                <div style="width:36px; height:36px; border-radius:10px; background:rgba(245,158,11,0.1); display:flex; align-items:center; justify-content:center; color:#f59e0b;"><i class="fa-solid fa-calendar-day"></i></div>
                                <div>
                                    <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:2px;">Date de publication</div>
                                    <div style="font-weight:600; color:white;"><?= date('d M Y', strtotime($offre->getDateCreation())) ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Action -->
                        <?php if ($has_applied): ?>
                            <div style="text-align:center; padding:1.2rem; border-radius:16px; background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#10b981; font-weight:700;">
                                <i class="fa-solid fa-check-circle" style="margin-right:8px;"></i> Candidature envoyée
                            </div>
                        <?php else: ?>
                            <button id="open-apply-modal" class="btn-search" style="width:100%; justify-content:center; padding:1.2rem; font-size:1.1rem; border-radius:16px; background:linear-gradient(135deg,var(--tech-blue),#2563eb); box-shadow:0 10px 25px -5px rgba(59,130,246,0.5); border:none; font-weight:700; color:white; cursor:pointer; display:flex; align-items:center; gap:8px;">
                                <i class="fa-solid fa-paper-plane"></i> Postuler Maintenant
                            </button>
                        <?php endif; ?>

                        <button id="download-job-pdf" class="btn-cart" style="width:100%; margin-top:1rem; justify-content:center; padding:1rem; font-size:0.95rem; border-radius:16px; background:rgba(255,255,255,0.03); color:white; border:1px solid rgba(255,255,255,0.1);">
                            <i class="fa-solid fa-download"></i> Enregistrer en PDF
                        </button>

                    </div>
                </div>
            </aside>
        </div>

    </div>
</div>

<!-- ════════════════════════════════════
     MODAL DE CANDIDATURE
════════════════════════════════════ -->
<div class="modal-overlay" id="apply-modal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title"><i class="fa-solid fa-paper-plane" style="color:#60a5fa; margin-right:8px;"></i>Postuler à cette mission</div>
                <div class="modal-subtitle"><?= htmlspecialchars($offre->getTitre()) ?></div>
            </div>
            <button class="modal-close" id="close-modal" title="Fermer"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <?php if (!empty($apply_errors)): ?>
            <ul class="form-errors">
                <?php foreach ($apply_errors as $err): ?>
                    <li><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" action="freelancer_detail.php?id=<?= $offre->getId() ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="apply">

            <div class="form-grid">

                <!-- Nom complet -->
                <div class="form-group">
                    <label class="form-label" for="app-name">Nom complet <span class="req">*</span></label>
                    <input class="form-input" type="text" id="app-name" name="name" placeholder="Jean Dupont"
                           value="<?= htmlspecialchars($form_data['name'] ?? '') ?>" required>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="app-email">Adresse e-mail <span class="req">*</span></label>
                    <input class="form-input" type="email" id="app-email" name="email" placeholder="jean@example.com"
                           value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required>
                </div>

                <!-- Téléphone -->
                <div class="form-group">
                    <label class="form-label" for="app-phone">Téléphone</label>
                    <input class="form-input" type="tel" id="app-phone" name="phone" placeholder="+216 XX XXX XXX"
                           value="<?= htmlspecialchars($form_data['phone'] ?? '') ?>">
                </div>

                <!-- Titre / poste -->
                <div class="form-group">
                    <label class="form-label" for="app-title">Votre titre / poste <span class="req">*</span></label>
                    <input class="form-input" type="text" id="app-title" name="job_title" placeholder="Développeur Full Stack"
                           value="<?= htmlspecialchars($form_data['job_title'] ?? '') ?>" required>
                </div>

                <!-- Upload CV -->
                <div class="form-group full">
                    <label class="form-label">CV (PDF, DOC, DOCX — max 5 Mo)</label>
                    <div class="cv-upload-zone" id="cv-drop-zone">
                        <input type="file" name="cv_file" id="cv-file-input" accept=".pdf,.doc,.docx">
                        <div class="cv-icon"><i class="fa-solid fa-file-arrow-up"></i></div>
                        <div style="color:white; font-weight:600;">Glissez votre CV ici ou <span style="color:#60a5fa;">parcourir</span></div>
                        <div class="cv-hint">Formats acceptés : PDF, DOC, DOCX</div>
                        <div class="cv-filename" id="cv-filename"><i class="fa-solid fa-check"></i> <span id="cv-name-text"></span></div>
                    </div>
                </div>

                <!-- Lettre de motivation -->
                <div class="form-group full">
                    <label class="form-label" for="app-cover">Lettre de motivation <span class="req">*</span></label>
                    <textarea class="form-textarea" id="app-cover" name="cover_letter" placeholder="Décrivez pourquoi vous êtes le bon candidat pour cette mission, vos expériences pertinentes et votre approche..." required><?= htmlspecialchars($form_data['cover_letter'] ?? '') ?></textarea>
                </div>

            </div>

            <button type="submit" class="btn-submit-modal">
                <i class="fa-solid fa-paper-plane"></i> Envoyer ma candidature
            </button>
        </form>
    </div>
</div>

<script>
// ── Modal open / close ──
const modal       = document.getElementById('apply-modal');
const openBtn     = document.getElementById('open-apply-modal');
const closeBtn    = document.getElementById('close-modal');

if (openBtn) openBtn.addEventListener('click', () => modal.classList.add('active'));
if (closeBtn) closeBtn.addEventListener('click', () => modal.classList.remove('active'));
modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('active'); });

// Auto-open if there are errors returned from POST
<?php if (!empty($apply_errors)): ?>
modal.classList.add('active');
<?php endif; ?>

// ── CV upload display & AI Parsing ──
const cvInput    = document.getElementById('cv-file-input');
const cvFilename = document.getElementById('cv-filename');
const cvNameText = document.getElementById('cv-name-text');
const dropZone   = document.getElementById('cv-drop-zone');

const appName = document.getElementById('app-name');
const appEmail = document.getElementById('app-email');
const appPhone = document.getElementById('app-phone');
const appTitle = document.getElementById('app-title');
const appCover = document.getElementById('app-cover');

async function processCvWithAi(file) {
    if (!file || file.type !== 'application/pdf') {
        alert("Seuls les fichiers PDF peuvent être analysés par l'IA.");
        return;
    }

    // Afficher l'état de chargement
    cvNameText.innerHTML = `<i class="fa-solid fa-spinner fa-spin" style="color:#60a5fa;"></i> Analyse du CV par l'IA en cours...`;
    cvFilename.style.display = 'block';

    const formData = new FormData();
    formData.append('cv_file', file);

    try {
        const res = await fetch('parse_cv_api.php', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if (result.status === 'success') {
            const data = result.data;
            cvNameText.innerHTML = `<i class="fa-solid fa-check" style="color:#10b981;"></i> CV analysé avec succès !`;
            
            // Auto-remplissage
            if (data.name && !appName.value) appName.value = data.name;
            if (data.email && !appEmail.value) appEmail.value = data.email;
            if (data.phone && !appPhone.value) appPhone.value = data.phone;
            
            // On peut mettre les compétences dans le titre si vide
            if (data.skills && !appTitle.value) {
                appTitle.value = data.skills.split(',').slice(0, 2).join(' & '); // Prend les 2 premières compétences
            }

            if (data.cover_letter && (!appCover.value || appCover.value.trim() === '')) {
                appCover.value = data.cover_letter;
            }

            // Animation visuelle pour montrer que les champs ont été remplis
            [appName, appEmail, appPhone, appTitle, appCover].forEach(el => {
                el.style.borderColor = '#10b981';
                setTimeout(() => el.style.borderColor = '', 2000);
            });

        } else {
            cvNameText.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="color:#f87171;"></i> Mode normal (Erreur IA: ${result.message})`;
        }
    } catch (e) {
        cvNameText.innerHTML = `<i class="fa-solid fa-check" style="color:#10b981;"></i> Fichier ajouté (Mode sans IA)`;
    }
}

cvInput.addEventListener('change', () => {
    if (cvInput.files.length > 0) {
        processCvWithAi(cvInput.files[0]);
    }
});

['dragenter','dragover'].forEach(evt => dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.add('drag-over'); }));
['dragleave','drop'].forEach(evt => dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.remove('drag-over'); }));
dropZone.addEventListener('drop', e => {
    const file = e.dataTransfer.files[0];
    if (file) { 
        cvInput.files = e.dataTransfer.files; 
        processCvWithAi(file);
    }
});

// ── PDF download ──
document.getElementById('download-job-pdf').addEventListener('click', async function(e) {
    e.preventDefault();
    const btn = this;
    const originalContent = btn.innerHTML;
    
    try {
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Génération...';
        btn.style.pointerEvents = 'none';

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.setFontSize(22); doc.setTextColor(37, 99, 235);
        doc.text("Détails de la Mission", 14, 20);
        doc.setFontSize(16); doc.setTextColor(0);
        doc.text("<?= addslashes($offre->getTitre()) ?>", 14, 35);
        doc.setFontSize(12);
        doc.text("Budget: <?= $offre->getBudget() ?> DT", 14, 50);
        doc.text("Délai: <?= addslashes($offre->getDelai()) ?>", 14, 60);
        doc.text("Compétences: <?= addslashes($offre->getCompetences()) ?>", 14, 70);
        doc.text("Description:", 14, 85);
        const splitDesc = doc.splitTextToSize("<?= addslashes(str_replace(["\r","\n"],' ',$offre->getDescription())) ?>", 180);
        doc.text(splitDesc, 14, 95);
        
        const pdfBlob = doc.output('blob');
        const formData = new FormData();
        formData.append('pdf', pdfBlob, 'mission_<?= $offre->getId() ?>.pdf');

        btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up fa-spin"></i> Cloud upload...';
        
        const response = await fetch('../../api/upload_pdf.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.ok) {
            window.open(result.url, '_blank');
            saveAs(pdfBlob, 'mission_<?= $offre->getId() ?>.pdf');
        } else {
            alert("Erreur Cloudinary : " + result.error);
        }
    } catch (err) {
        console.error(err);
        alert("Une erreur est survenue lors de l'export : " + err.message);
    } finally {
        btn.innerHTML = originalContent;
        btn.style.pointerEvents = 'all';
    }
});

document.getElementById('export-pdf').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('download-job-pdf').click();
});
</script>

<script src="../assets/theme.js"></script>
</body>
</html>
