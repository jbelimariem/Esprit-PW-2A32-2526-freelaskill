<?php
function getStatusLabel($s) {
    $map = [
        'pending'  => ['label'=>'En attente', 'class'=>'statut-pending'],
        'approved' => ['label'=>'Acceptée',   'class'=>'statut-approved'],
        'rejected' => ['label'=>'Refusée',    'class'=>'statut-rejected'],
    ];
    return $map[$s] ?? $map['pending'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Candidatures — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/theme-light.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <style>
        /* ── Job Grid & Cards Styling ── */
        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.2rem;
            margin-top: 1rem;
        }

        /* MODAL CSS */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 1.5rem;
            animation: fadeIn 0.3s ease;
        }
        .modal-overlay.active { display: flex; }
        .modal-card {
            background: #1a1a2e;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.5rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transform: scale(0.9);
            animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes modalPop { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-title { font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.75rem; }
        .modal-text { color: #94a3b8; line-height: 1.6; margin-bottom: 2rem; }
        .modal-actions { display: flex; gap: 1rem; justify-content: center; }
        .btn-modal { padding: 0.8rem 2rem; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; }
        .btn-modal-cancel { background: rgba(255, 255, 255, 0.05); color: white; border: 1px solid rgba(255, 255, 255, 0.1); }
        .btn-modal-cancel:hover { background: rgba(255, 255, 255, 0.1); }
        .btn-modal-confirm { background: #ef4444; color: white; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }
        .btn-modal-confirm:hover { background: #dc2626; transform: translateY(-2px); }

        /* Styles pour la modale de modification spécifique */
        #edit-modal .modal-card { max-width: 650px; text-align: left; }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.88rem; font-weight: 600; color: #94a3b8; margin-bottom: 0.5rem; }
        .form-input, .form-textarea { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 0.75rem 1rem; color: white; font-family: inherit; transition: all 0.2s; }
        .form-input:focus, .form-textarea:focus { outline: none; border-color: #3b82f6; background: rgba(59,130,246,0.05); box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }
        .btn-submit-modal { width: 100%; background: #3b82f6; color: white; border: none; padding: 1rem; border-radius: 14px; font-weight: 700; cursor: pointer; transition: all 0.25s; display: flex; align-items: center; justify-content: center; gap: 0.6rem; margin-top: 1.5rem; }
        .btn-submit-modal:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4); }
        .job-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 1.2rem;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        .job-card:hover {
            transform: translateY(-5px);
            border-color: rgba(59, 130, 246, 0.3);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5);
        }
        .job-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.2rem;
        }
        .job-icon {
            width: 38px;
            height: 38px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .job-titre {
            font-size: 1rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.4rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.5rem;
        }
        .job-card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        .job-budget {
            color: #10b981;
            font-weight: 700;
            font-size: 1rem;
        }
        .job-actions {
            display: flex;
            gap: 8px;
            margin-top: 1.2rem;
        }

        /* ── Status Badges Premium ── */
        .statut-pending {
            background: rgba(245, 158, 11, 0.1) !important;
            color: #f59e0b !important;
            border: 1px solid rgba(245, 158, 11, 0.2) !important;
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.1);
        }
        .statut-approved {
            background: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
            border: 1px solid rgba(16, 185, 129, 0.2) !important;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.1);
        }
        .statut-rejected {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
            border: 1px solid rgba(239, 68, 68, 0.2) !important;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.1);
        }
        .job-badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .job-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            display: inline-block;
        }

        /* ── Buttons Jolie ── */
        .btn-action {
            padding: 0.8rem 1rem;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: 1px solid transparent; /* Enlever le cadre par défaut */
            background: transparent; /* Enlever le fond par défaut */
            backdrop-filter: blur(5px);
        }
        .btn-view {
            background: rgba(59, 130, 246, 0.05);
            color: #60a5fa;
            flex: 1;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
        .btn-view:hover {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
            transform: translateY(-2px);
        }
        .btn-edit {
            color: #f59e0b !important;
            width: 48px !important;
            height: 48px !important;
            font-size: 1.1rem !important;
        }
        .btn-edit:hover {
            background: rgba(245, 158, 11, 0.1) !important;
            border: 1px solid rgba(245, 158, 11, 0.3) !important;
            box-shadow: 0 8px 15px rgba(245, 158, 11, 0.2);
            transform: translateY(-2px);
        }
        .btn-cancel {
            color: #ff3333 !important; /* Rouge intense */
            width: 48px !important;
            height: 48px !important;
            font-size: 1.1rem !important;
        }
        .btn-cancel:hover {
            background: #ff3333 !important; /* Fond rouge intense au hover */
            color: white !important;
            box-shadow: 0 10px 20px -5px rgba(255, 51, 51, 0.4);
            transform: translateY(-2px);
            border: 1px solid #ff3333 !important;
        }
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
        <li><a href="<?= (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? '/freelaskill/messagerie_index.php?page=admin' : '/freelaskill/messagerie_index.php?page=conversations' ?>" class="<?= (strpos($_SERVER['PHP_SELF'], 'essagerie') !== false) ? 'active' : '' ?>">Messagerie</a></li>
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
            <div class="mkt-profile-stats">
                <div class="mkt-stat"><div class="mkt-stat-val"><?= count($applications) ?></div><div class="mkt-stat-label">POSTULÉES</div></div>
            </div>
        </div>
        <div class="mkt-sidebar-card">
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="freelancer_home.php" class="nav-item"><i class="fa-solid fa-briefcase"></i> Missions</a>
                <a href="freelancer_applications.php" class="nav-item active"><i class="fa-solid fa-paper-plane"></i> Candidatures</a>
                <a href="#" id="export-pdf" class="nav-item"><i class="fa-solid fa-file-pdf"></i> Export PDF</a>
                <a href="home.php" class="nav-item danger"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            </div>
        </div>
    </aside>

    <div class="mkt-main">

        <section class="hero-banner">
            <div class="hero-glow"></div>
            <div class="hero-content">
                <div class="hero-tag"><i class="fa-solid fa-paper-plane"></i> Suivi</div>
                <h1 class="hero-title">Mes <span>Candidatures</span></h1>
                <p class="hero-sub">Retrouvez ici l'état de toutes les missions pour lesquelles vous avez postulé.</p>
            </div>
        </section>

        <div class="page-body" style="display:block; max-width:1100px; margin:0 auto; padding:2rem 1rem;">

            <!-- Search -->
            <div style="margin-bottom:2rem; background:rgba(255,255,255,0.03); padding:1.5rem; border-radius:16px; border:1px solid rgba(255,255,255,0.08);">
                <form method="GET" action="freelancer_applications.php" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
                    <div style="flex:1; min-width:250px; position:relative;">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:1.2rem; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.4);"></i>
                        <input type="text" name="search" placeholder="Rechercher par titre ou date..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:.85rem 1rem .85rem 3rem; border-radius:12px; font-family:inherit; font-size:.95rem; outline:none; box-sizing:border-box;">
                    </div>
                    <button type="submit" style="background:var(--primary,#3b82f6); color:white; border:none; padding:.85rem 1.8rem; border-radius:12px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:.5rem;">
                        <i class="fa-solid fa-filter"></i> Filtrer
                    </button>
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="freelancer_applications.php" style="background:rgba(255,255,255,0.1); color:white; text-decoration:none; padding:.85rem 1.8rem; border-radius:12px; font-weight:600; display:flex; align-items:center; gap:.5rem;">
                            <i class="fa-solid fa-rotate-right"></i> Réinitialiser
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Cards -->
            <div class="job-grid">
                <?php if (empty($applications)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <h3 style="color:white; margin-bottom:.5rem;">Aucune candidature</h3>
                        <p>Vous n'avez pas encore postulé à des missions.</p>
                        <a href="freelancer_home.php" class="btn-cart" style="display:inline-flex; width:auto; margin-top:1.5rem; padding:.75rem 2rem;">Voir les missions</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($applications as $app):
                        $status = getStatusLabel($app['status']);
                    ?>
                    <div class="job-card">
                        <div class="job-card-header">
                            <div class="job-icon">💼</div>
                            <div class="job-badge <?= $status['class'] ?>">
                                <?= $status['label'] ?>
                            </div>
                        </div>
                        <div class="job-card-body">
                            <div class="job-titre"><?= htmlspecialchars($app['job_title']) ?></div>
                            <div style="font-size:.82rem; color:var(--text-muted); margin:.4rem 0 .2rem;">
                                <i class="fa-solid fa-user"></i> <?= htmlspecialchars($app['name']) ?>
                                &nbsp;·&nbsp;
                                <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($app['email']) ?>
                            </div>
                            <?php if (!empty($app['phone'])): ?>
                            <div style="font-size:.82rem; color:var(--text-muted); margin-bottom:.2rem;">
                                <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($app['phone']) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($app['cover_letter'])): ?>
                            <div style="font-size:.82rem; color:var(--text-muted); margin-top:.5rem; padding:.6rem .8rem; background:rgba(255,255,255,.03); border-radius:8px; border:1px solid rgba(255,255,255,.06); max-height:60px; overflow:hidden; text-overflow:ellipsis;">
                                <i class="fa-solid fa-quote-left" style="color:#60a5fa; font-size:.7rem; margin-right:4px;"></i><?= htmlspecialchars(mb_substr($app['cover_letter'], 0, 120)) ?>...
                            </div>
                            <?php endif; ?>
                            <div class="job-meta" style="margin-top:.75rem;">
                                <span><i class="fa-solid fa-coins"></i> <span class="job-budget"><?= number_format($app['budget'], 0, ',', ' ') ?></span> <small>DT</small></span>
                                <span><i class="fa-solid fa-calendar-check"></i> Postulé le <?= date('d/m/Y', strtotime($app['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="job-actions" style="display:flex; gap:10px;">
                            <a href="freelancer_detail.php?id=<?= $app['job_id'] ?>" class="btn-action btn-view">
                                <i class="fa-solid fa-eye"></i> Voir la mission
                            </a>

                            <!-- Bouton Modifier (Visible pour tous) -->
                            <button type="button" class="btn-edit js-open-edit"
                                data-id="<?= $app['id'] ?>"
                                data-name="<?= htmlspecialchars($app['name'], ENT_QUOTES) ?>"
                                data-email="<?= htmlspecialchars($app['email'], ENT_QUOTES) ?>"
                                data-phone="<?= htmlspecialchars($app['phone'] ?? '', ENT_QUOTES) ?>"
                                data-title="<?= htmlspecialchars($app['job_title'], ENT_QUOTES) ?>"
                                data-cover="<?= htmlspecialchars($app['cover_letter'] ?? $app['message'] ?? '', ENT_QUOTES) ?>"
                                data-cv="<?= htmlspecialchars($app['cv_path'] ?? $app['cv_link'] ?? '', ENT_QUOTES) ?>"
                                title="Modifier ma candidature">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <!-- Bouton Annuler (Visible pour tous - Rouge) -->
                            <form method="POST" action="freelancer_applications.php" class="form-cancel" data-title="<?= htmlspecialchars($app['job_title']) ?>" style="margin:0;">
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                <button type="button" class="btn-action btn-cancel js-cancel" title="Annuler la candidature">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- ═══════════════════════════════════
     MODAL ANNULATION
═══════════════════════════════════ -->
<div class="modal-overlay" id="cancel-modal">
    <div class="modal-card">
        <div class="modal-title"><i class="fa-solid fa-circle-exclamation" style="color:var(--tunisian-red);"></i> Confirmation</div>
        <p class="modal-text" id="modal-desc">Voulez-vous vraiment annuler cette candidature ?</p>
        <div class="modal-actions">
            <button class="btn-modal btn-modal-cancel" id="confirm-cancel">Non, garder</button>
            <button class="btn-modal btn-modal-confirm" id="confirm-ok" style="background:var(--tunisian-red);">Oui, annuler</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════
     MODAL MODIFICATION CANDIDATURE
═══════════════════════════════════ -->
<div class="modal-overlay" id="edit-modal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title"><i class="fa-solid fa-pen" style="color:#f59e0b; margin-right:8px;"></i>Modifier ma candidature</div>
                <div class="modal-sub" id="edit-modal-sub">Mission</div>
            </div>
            <button class="modal-close" id="close-edit-modal" title="Fermer"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <?php if (!empty($edit_errors)): ?>
        <ul class="form-errors">
            <?php foreach ($edit_errors as $err): ?>
                <li><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <form method="POST" action="freelancer_applications.php" enctype="multipart/form-data" id="edit-form">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="app_id" id="edit-app-id" value="">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="edit-name">Nom complet <span class="req">*</span></label>
                    <input class="form-input" type="text" id="edit-name" name="name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-email">E-mail <span class="req">*</span></label>
                    <input class="form-input" type="email" id="edit-email" name="email" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-phone">Téléphone</label>
                    <input class="form-input" type="tel" id="edit-phone" name="phone" placeholder="+216 XX XXX XXX">
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-title">Titre / Poste <span class="req">*</span></label>
                    <input class="form-input" type="text" id="edit-title" name="job_title" required>
                </div>
                <div class="form-group full">
                    <label class="form-label">Nouveau CV <small style="text-transform:none; font-size:.75rem; color:var(--text-muted);">(laisser vide pour conserver l'actuel)</small></label>
                    <div class="cv-zone" id="edit-cv-zone">
                        <input type="file" name="cv_file" id="edit-cv-input" accept=".pdf,.doc,.docx">
                        <i class="fa-solid fa-file-arrow-up" style="font-size:2rem; color:var(--tech-blue); display:block; margin-bottom:.5rem;"></i>
                        <span style="color:white; font-weight:600;">Glissez un fichier ou <span style="color:#60a5fa;">parcourir</span></span>
                        <div style="font-size:.78rem; color:var(--text-muted); margin-top:.3rem;">PDF, DOC, DOCX — max 5 Mo</div>
                        <div class="cv-fname" id="edit-cv-fname"><i class="fa-solid fa-check"></i> <span id="edit-cv-name"></span></div>
                    </div>
                    <div id="edit-current-cv" style="font-size:.82rem; color:var(--text-muted); margin-top:.4rem;"></div>
                </div>
                <div class="form-group full">
                    <label class="form-label" for="edit-cover">Lettre de motivation <span class="req">*</span></label>
                    <textarea class="form-textarea" id="edit-cover" name="cover_letter" required></textarea>
                </div>
            </div>

            <button type="submit" class="btn-submit-modal">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
            </button>
        </form>
    </div>
</div>

<?php if (!empty($success)): ?>
<div class="toast toast-success" id="toast">
    <i class="fa-solid fa-circle-check"></i>
    <?= $success === 'updated' ? 'Candidature modifiée avec succès !' : 'Candidature annulée.' ?>
</div>
<?php endif; ?>

<script>
// ── Toast auto-dismiss ──
const toast = document.getElementById('toast');
if (toast) setTimeout(() => toast.style.opacity = '0', 3500);

// ── Modal annulation ──
const cancelModal = document.getElementById('cancel-modal');
const confirmOk   = document.getElementById('confirm-ok');
const confirmCancel = document.getElementById('confirm-cancel');
const modalDesc   = document.getElementById('modal-desc');
let formToSubmit  = null;

document.querySelectorAll('.js-cancel').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        formToSubmit = btn.closest('form');
        modalDesc.innerHTML = `Voulez-vous vraiment annuler votre candidature pour <strong style="color:white;">"${formToSubmit.dataset.title}"</strong> ?`;
        cancelModal.classList.add('active');
    });
});
confirmCancel.addEventListener('click', () => cancelModal.classList.remove('active'));
confirmOk.addEventListener('click', () => { if (formToSubmit) formToSubmit.submit(); });

// ── Modal modification ──
const editModal      = document.getElementById('edit-modal');
const closeEditModal = document.getElementById('close-edit-modal');

document.querySelectorAll('.js-open-edit').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit-app-id').value  = btn.dataset.id;
        document.getElementById('edit-name').value    = btn.dataset.name;
        document.getElementById('edit-email').value   = btn.dataset.email;
        document.getElementById('edit-phone').value   = btn.dataset.phone;
        document.getElementById('edit-title').value   = btn.dataset.title;
        document.getElementById('edit-cover').value   = btn.dataset.cover;
        document.getElementById('edit-modal-sub').textContent = btn.dataset.title;

        const cvDiv = document.getElementById('edit-current-cv');
        cvDiv.textContent = btn.dataset.cv
            ? '📎 CV actuel : ' + btn.dataset.cv.split('/').pop()
            : 'Aucun CV actuellement enregistré.';

        // reset file display
        document.getElementById('edit-cv-fname').style.display = 'none';
        editModal.classList.add('active');
    });
});

closeEditModal.addEventListener('click', () => editModal.classList.remove('active'));
editModal.addEventListener('click', e => { if (e.target === editModal) editModal.classList.remove('active'); });

// Auto-open edit modal on POST error
<?php if (!empty($edit_errors)): ?>
editModal.classList.add('active');
<?php endif; ?>

// ── CV file name display ──
const cvInput = document.getElementById('edit-cv-input');
cvInput.addEventListener('change', () => {
    if (cvInput.files.length > 0) {
        document.getElementById('edit-cv-name').textContent = cvInput.files[0].name;
        document.getElementById('edit-cv-fname').style.display = 'block';
    }
});

// ── PDF Export ──
document.getElementById('export-pdf').addEventListener('click', async function(e) {
    e.preventDefault();
    const btn = this;
    const originalContent = btn.innerHTML;
    
    try {
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Génération...';
        btn.style.pointerEvents = 'none';

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.setFontSize(22); doc.setTextColor(37, 99, 235);
        doc.text("Mes Candidatures — FreelaSkill", 14, 20);
        doc.setFontSize(10); doc.setTextColor(100);
        doc.text("Généré le : " + new Date().toLocaleDateString(), 14, 30);
        const data = <?= json_encode(array_map(fn($app) => [$app['job_title'], date('d/m/Y H:i', strtotime($app['created_at'])), ucfirst($app['status'])], $applications)) ?>;
        doc.autoTable({ startY:40, head:[['Mission','Date','Statut']], body:data, theme:'striped', headStyles:{fillStyle:[37,99,235]} });
        
        const pdfBlob = doc.output('blob');
        const formData = new FormData();
        formData.append('pdf', pdfBlob, 'mes_candidatures.pdf');

        btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up fa-spin"></i> Cloud upload...';
        
        const response = await fetch('../../api/upload_pdf.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.ok) {
            window.open(result.url, '_blank');
            saveAs(pdfBlob, 'mes_candidatures.pdf');
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
</script>

    </div>
</div>
<!-- ═══════════════════════════════════
     AI ASSISTANT WIDGET
═══════════════════════════════════ -->
<div class="ai-fab" id="ai-fab" title="Assistant IA">
    <i class="fa-solid fa-robot"></i>
</div>

<div class="ai-chat-panel" id="ai-chat-panel">
    <div class="ai-chat-header">
        <div class="ai-avatar"><i class="fa-solid fa-robot"></i></div>
        <div class="ai-header-info">
            <div class="ai-title">Assistant FreelaSkill</div>
            <div class="ai-status">En ligne</div>
        </div>
        <button class="ai-close" id="ai-close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="ai-chat-body" id="ai-chat-body">
        <div class="ai-msg bot">
            <div class="ai-bubble">
                Bonjour ! Je suis l'assistant FreelaSkill. Je peux vous aider à trouver les meilleures missions. Quelles sont vos compétences principales ? (ex: PHP, React, Design)
            </div>
        </div>
    </div>
    <div class="ai-chat-input-area">
        <form id="ai-form" style="display:flex; width:100%; gap:8px;">
            <input type="text" id="ai-input" placeholder="Tapez votre message..." autocomplete="off">
            <button type="submit" id="ai-send"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>
</div>

<style>
/* AI Fab Button */
.ai-fab { position:fixed; bottom:2rem; right:2rem; width:60px; height:60px; border-radius:50%; background:linear-gradient(135deg, #3b82f6, #8b5cf6); color:white; display:flex; align-items:center; justify-content:center; font-size:1.8rem; cursor:pointer; box-shadow:0 10px 25px rgba(59,130,246,0.5); z-index:9999; transition:transform 0.3s, box-shadow 0.3s; }
.ai-fab:hover { transform:scale(1.1) translateY(-5px); box-shadow:0 15px 35px rgba(59,130,246,0.6); }

/* Chat Panel */
.ai-chat-panel { position:fixed; bottom:6rem; right:2rem; width:380px; height:600px; max-height:80vh; background:linear-gradient(160deg, #0f172a 0%, #1e293b 100%); border:1px solid rgba(255,255,255,0.1); border-radius:24px; display:flex; flex-direction:column; box-shadow:0 30px 60px rgba(0,0,0,0.8); z-index:9998; opacity:0; pointer-events:none; transform:translateY(20px) scale(0.95); transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1); overflow:hidden; }
.ai-chat-panel.active { opacity:1; pointer-events:auto; transform:none; }

.ai-chat-header { display:flex; align-items:center; padding:1.2rem 1.5rem; background:rgba(255,255,255,0.03); border-bottom:1px solid rgba(255,255,255,0.08); }
.ai-avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg, #3b82f6, #8b5cf6); display:flex; align-items:center; justify-content:center; font-size:1.2rem; color:white; margin-right:12px; }
.ai-header-info { flex:1; }
.ai-title { font-weight:700; font-size:1rem; color:white; }
.ai-status { font-size:0.75rem; color:#10b981; display:flex; align-items:center; gap:5px; }
.ai-status::before { content:''; display:block; width:6px; height:6px; background:#10b981; border-radius:50%; box-shadow:0 0 8px #10b981; }
.ai-close { background:none; border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer; transition:color 0.2s; }
.ai-close:hover { color:#ef4444; }

.ai-chat-body { flex:1; padding:1.5rem; overflow-y:auto; display:flex; flex-direction:column; gap:1rem; }
.ai-chat-body::-webkit-scrollbar { width:6px; }
.ai-chat-body::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:10px; }

.ai-msg { display:flex; max-width:85%; }
.ai-msg.bot { align-self:flex-start; }
.ai-msg.user { align-self:flex-end; }

.ai-bubble { padding:1rem 1.2rem; border-radius:18px; font-size:0.9rem; line-height:1.4; }
.ai-msg.bot .ai-bubble { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#e2e8f0; border-bottom-left-radius:4px; }
.ai-msg.user .ai-bubble { background:linear-gradient(135deg, #3b82f6, #2563eb); color:white; border-bottom-right-radius:4px; }

.ai-chat-input-area { padding:1rem 1.5rem; border-top:1px solid rgba(255,255,255,0.08); background:rgba(0,0,0,0.2); }
#ai-input { flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:20px; padding:10px 15px; color:white; outline:none; font-family:inherit; transition:border-color 0.2s; }
#ai-input:focus { border-color:#3b82f6; }
#ai-send { background:#3b82f6; border:none; width:40px; height:40px; border-radius:50%; color:white; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s; }
#ai-send:hover { background:#2563eb; }

/* Typing indicator */
.typing-dots { display:flex; gap:4px; padding:5px 0; }
.typing-dots span { width:6px; height:6px; background:var(--text-muted); border-radius:50%; animation:typing 1.4s infinite ease-in-out; }
.typing-dots span:nth-child(1) { animation-delay:0s; }
.typing-dots span:nth-child(2) { animation-delay:0.2s; }
.typing-dots span:nth-child(3) { animation-delay:0.4s; }
@keyframes typing { 0%, 100% { transform:scale(1); opacity:0.5; } 50% { transform:scale(1.2); opacity:1; background:white; } }

/* Job Card in Chat */
.ai-job-card { background:rgba(0,0,0,0.3); border:1px solid rgba(59,130,246,0.3); border-radius:12px; padding:1rem; margin-top:10px; width:100%; box-sizing:border-box; }
.ai-job-title { font-weight:700; color:white; font-size:0.95rem; margin-bottom:5px; }
.ai-job-meta { font-size:0.8rem; color:var(--text-muted); margin-bottom:8px; display:flex; gap:10px; }
.ai-job-score { background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); padding:2px 8px; border-radius:10px; font-size:0.75rem; font-weight:600; display:inline-block; margin-bottom:8px;}
.ai-job-btn { display:block; text-align:center; background:rgba(59,130,246,0.1); color:#60a5fa; border:1px solid rgba(59,130,246,0.3); border-radius:8px; padding:8px; text-decoration:none; font-size:0.85rem; font-weight:600; transition:all 0.2s; }
.ai-job-btn:hover { background:rgba(59,130,246,0.2); color:white; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fab = document.getElementById('ai-fab');
    const panel = document.getElementById('ai-chat-panel');
    const closeBtn = document.getElementById('ai-close');
    const form = document.getElementById('ai-form');
    const input = document.getElementById('ai-input');
    const body = document.getElementById('ai-chat-body');

    let chatHistory = [];

    fab.addEventListener('click', () => {
        panel.classList.toggle('active');
        if(panel.classList.contains('active')) input.focus();
    });

    closeBtn.addEventListener('click', () => panel.classList.remove('active'));

    // Fonction basique pour transformer le Markdown (gras, liens, listes) en HTML
    function parseMarkdown(text) {
        let html = text;
        // Echapper le HTML basique d'abord pour éviter les failles XSS (sauf les sauts de ligne)
        html = html.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        // Gras : **texte**
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Liens : [texte](url)
        html = html.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" class="ai-job-btn" style="display:inline-block; margin-top:5px; margin-bottom:5px;">$1</a>');
        // Listes à puces simples (lignes commençant par * ou -)
        html = html.replace(/^[\*\-]\s+(.*)$/gm, '<li style="margin-left: 20px;">$1</li>');
        // Retours à la ligne
        html = html.replace(/\n/g, '<br>');
        return html;
    }

    function appendMsg(text, type, isHtml = false) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-msg ${type}`;
        msgDiv.innerHTML = `<div class="ai-bubble">${isHtml ? text : text}</div>`;
        body.appendChild(msgDiv);
        body.scrollTop = body.scrollHeight;
    }

    function appendTyping() {
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-msg bot typing-indicator`;
        msgDiv.innerHTML = `<div class="ai-bubble"><div class="typing-dots"><span></span><span></span><span></span></div></div>`;
        body.appendChild(msgDiv);
        body.scrollTop = body.scrollHeight;
        return msgDiv;
    }

    async function sendToAi(userMessage) {
        const typing = appendTyping();
        try {
            const res = await fetch('ai_assistant.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: userMessage, history: chatHistory })
            });
            const data = await res.json();
            typing.remove();

            if (data.status === 'success') {
                const replyHtml = parseMarkdown(data.reply);
                appendMsg(replyHtml, 'bot', true);
                
                // Ajouter à l'historique pour que l'IA se souvienne de la conversation
                chatHistory.push({ role: 'user', text: userMessage });
                chatHistory.push({ role: 'bot', text: data.reply });
            } else {
                appendMsg("Désolé, une erreur technique est survenue.", 'bot');
            }
        } catch (err) {
            typing.remove();
            appendMsg("Oups, impossible de joindre l'IA.", 'bot');
        }
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const val = input.value.trim();
        if (!val) return;

        appendMsg(val, 'user');
        input.value = '';
        
        sendToAi(val);
    });
});
</script>
<script src="../assets/theme.js"></script>
</body>
</html>
