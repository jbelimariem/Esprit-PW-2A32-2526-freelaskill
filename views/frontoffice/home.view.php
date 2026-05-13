<?php
// views/frontoffice/home.view.php — Template: liste des offres d'emploi

function statutBadge($statut) {
    $map = [
        'pending'  => ['label' => 'En attente',  'color' => '#F59E0B', 'bg' => 'rgba(245,158,11,0.12)',  'border' => 'rgba(245,158,11,0.3)'],
        'approved' => ['label' => 'Approuvée',   'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.12)',  'border' => 'rgba(16,185,129,0.3)'],
        'rejected' => ['label' => 'Rejetée',     'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.12)',   'border' => 'rgba(239,68,68,0.3)'],
    ];
    return $map[$statut] ?? $map['pending'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Offres d'Emploi — FreelaSkill</title>
    <meta name="description" content="Gérez vos offres d'emploi sur la plateforme FreelaSkill. Publiez, modifiez et suivez vos offres.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/theme-light.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <style>
        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.25rem;
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
    </style>

<style>
/* FLYOUT MENU STYLES */
.nav-item-wrapper { position: relative; width: 100%; z-index: 1000; }
.submenu { position: absolute; left: calc(100% + 12px); top: 0; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 0.75rem; min-width: 220px; opacity: 0; visibility: hidden; transform: translateX(10px); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5); pointer-events: none; }
.nav-item-wrapper:hover .submenu { opacity: 1; visibility: visible; transform: translateX(0); pointer-events: auto; }
.submenu-title { font-size: 0.7rem; text-transform: uppercase; color: #475569; padding: 0.5rem 0.75rem; font-weight: 800; letter-spacing: 1px; margin-bottom: 0.25rem; }
.submenu-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.7rem 0.85rem; color: #94a3b8; text-decoration: none; font-size: 0.82rem; border-radius: 10px; transition: all 0.2s ease; }
.submenu-item i { font-size: 0.9rem; width: 18px; text-align: center; color: #3b82f6; }
.submenu-item:hover { background: rgba(255, 255, 255, 0.05); color: white; transform: translateX(4px); }
.nav-item-wrapper::after { content: ''; position: absolute; right: -12px; top: 0; width: 12px; height: 100%; }
.mkt-sidebar { overflow: visible !important; }
.marketplace-layout { overflow: visible !important; }
</style>
</head>

<body class="page-anim home-page">

<nav>
    <div class="logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </div>
    <ul class="nav-links">
        <li><span style="color:var(--text-muted);cursor:default;">Accueil</span></li>
        <li><a href="home.php">Marketplace</a></li>
        <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'client'): ?>
            <li><a href="missions.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'missions.php') ? 'active' : '' ?>">Missions</a></li>
        <?php else: ?>
            <li><a href="freelancer_home.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'freelancer_home.php') ? 'active' : '' ?>">Freelancers</a></li>
        <?php endif; ?>
        <li><a href="<?= (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? '/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin' : '/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=conversations' ?>" class="<?= (strpos($_SERVER['PHP_SELF'], 'essagerie') !== false) ? 'active' : '' ?>">Messagerie</a></li>
        <li><a href="profile.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : '' ?>">Mon Profil</a></li>
    </ul>
    <div class="nav-right">
        <button type="button" class="theme-toggle" data-theme-toggle>
            <i class="fa-solid fa-sun" data-theme-icon></i>
            <span data-theme-label>Jour</span>
        </button>
        <div class="nav-avatar">CL</div>
    </div>
</nav>

<?php $success = $_GET['success'] ?? ''; ?>
<?php if ($success): ?>
<div class="toast" id="toast">
    <?php if ($success === 'added'): ?>
        <i class="fa-solid fa-circle-check"></i> Offre publiée avec succès !
    <?php elseif ($success === 'updated'): ?>
        <i class="fa-solid fa-circle-check"></i> Offre modifiée avec succès !
    <?php elseif ($success === 'deleted'): ?>
        <i class="fa-solid fa-circle-check"></i> Offre supprimée.
    <?php endif; ?>
</div>
<script>setTimeout(() => { const t = document.getElementById('toast'); if(t) t.style.opacity='0'; }, 3500);</script>
<?php endif; ?>

<div class="marketplace-layout">
        <aside class="mkt-sidebar">
        <!-- Card 1 : Profil marketplace -->
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-building"></i></div>
                <div class="mkt-profile-name">Espace Client</div>
                <div class="mkt-profile-sub">FreelaSkill Tunisia</div>
            </div>
            <div class="mkt-profile-stats">
                <div class="mkt-stat">
                    <div class="mkt-stat-val"><?= config::getConnexion()->query("SELECT COUNT(*) FROM offres_emploi")->fetchColumn() ?></div>
                    <div class="mkt-stat-label">OFFRES</div>
                </div>
                <div class="mkt-stat">
                    <div class="mkt-stat-val"><?= config::getConnexion()->query("SELECT COUNT(*) FROM job_applications")->fetchColumn() ?></div>
                    <div class="mkt-stat-label">CANDIDATS</div>
                </div>
            </div>
        </div>

        <!-- Card 2 : Navigation + Filtres -->
        <div class="mkt-sidebar-card">
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="missions.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'missions.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-list-ul"></i> Mes Offres
                </a>
                <a href="add_job.php" class="nav-item ">
                    <i class="fa-solid fa-plus-circle"></i> Nouveau Offre
                </a>
                <a href="client_freelancers.php" class="nav-item ">
                    <i class="fa-solid fa-users"></i> Freelancers
                </a>
                <div class="nav-item-wrapper">
                    <a href="front_contrat_index.php" class="nav-item">
                        <i class="fa-solid fa-file-contract"></i> Contrats
                        <i class="fa-solid fa-chevron-right" style="margin-left:auto;font-size:0.75rem;"></i>
                    </a>
                    <div class="submenu">
                        <div class="submenu-title">Gestion Légale</div>
                        <a href="front_contrat_list.php" class="submenu-item">
                            <i class="fa-solid fa-list-check"></i> Liste des contrats
                        </a>
                        <a href="front_contrat_form.php" class="submenu-item">
                            <i class="fa-solid fa-file-signature"></i> Nouveau contrat
                        </a>
                        <a href="front_rules_list.php" class="submenu-item">
                            <i class="fa-solid fa-gavel"></i> Liste des règles
                        </a>
                        <a href="front_rules_form.php" class="submenu-item">
                            <i class="fa-solid fa-plus-circle"></i> Nouvelle règle
                        </a>
                    </div>
                </div>
                <a href="#" id="download-pdf-home" class="nav-item">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>
    </aside>

    <div class="mkt-main">
        <!-- TOPBAR INSIDE MAIN -->
        

<!-- HERO BANNER -->
<section class="hero-banner" style="padding: 2.5rem 1rem; min-height: auto;">
    <div class="hero-glow"></div>
    <div class="hero-content" style="max-width: 900px; margin: 0 auto; text-align: center;">
        
        <h1 class="hero-title">Trouvez vos <span>missions</span> en un clic</h1>
        <p class="hero-sub">Explorez les meilleures offres d'emploi freelance ou publiez vos propres besoins pour attirer des talents qualifiés.</p>

        <!-- SEARCH FORM -->
        <div style="display:flex; justify-content:center; width:100%; margin-top:2.5rem;">
            <form method="GET" action="missions.php" style="width:100%; max-width:700px;" novalidate>
                <div class="search-container" style="background: rgba(255,255,255,0.05); padding: 8px; border-radius: var(--radius-full); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); display: flex; gap: 10px;">
                    <div class="search-wrap" style="flex:2;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="q" id="search-q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Titre ou mots-clés..." style="width:100%;">
                    </div>
                    <div class="search-wrap" style="flex:1; border-left: 1px solid rgba(255,255,255,0.1); padding-left: 15px;">
                        <i class="fa-solid fa-coins"></i>
                        <input type="text" name="budget" value="<?= htmlspecialchars($_GET['budget'] ?? '') ?>" placeholder="Budget Max (DT)" style="width:100%;">
                    </div>
                    <button type="submit" class="btn-search" id="btn-search">
                        <i class="fa-solid fa-magnifying-glass"></i> Rechercher
                    </button>
                    <?php if (!empty($q) || !empty($_GET['budget'])): ?>
                    <a href="missions.php" class="btn-search" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); width: 50px; padding: 0; justify-content: center;">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- PAGE BODY -->
    <!-- OFFRES AREA -->
    <div class="products-area">
        <div class="products-toolbar">
            <p class="result-count">
                <strong><?= count($offres) ?> offre<?= count($offres) > 1 ? 's' : '' ?></strong>
                <?= !empty($q) ? 'trouvée(s)' : 'au total' ?>
            </p>
        </div>

        <!-- GRID -->
        <div class="job-grid">
            <?php if (empty($offres)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-folder-open"></i>
                <h3 style="color:white; margin-bottom:.5rem;">Aucune offre trouvée</h3>
                <p>Publiez votre première offre via le bouton en haut de la page.</p>
            </div>
            <?php else: ?>
            <?php foreach ($offres as $offre): 
                $competencesList = array_slice(explode(',', $offre->getCompetences()), 0, 3);
            ?>
            <div class="job-card">
                <div class="job-card-header">
                    <div class="job-icon">💼</div>
                    <div class="job-badge" style="background:rgba(59,130,246,0.1); color:var(--tech-blue); padding:4px 10px; border-radius:var(--radius-full); font-size:0.65rem; font-weight:700; text-transform:uppercase;">Job Offer</div>
                </div>
                <div class="job-card-body">
                    <div class="job-titre"><?= htmlspecialchars($offre->getTitre()) ?></div>
                    <div class="job-competences">
                        <?php foreach ($competencesList as $comp): ?>
                        <span style="display:inline-block; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.2); color:var(--tech-blue); padding:2px 8px; border-radius:var(--radius-full); font-size:.7rem; margin-right:4px;"><?= htmlspecialchars(trim($comp)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="job-meta">
                        <span><i class="fa-solid fa-coins"></i> <span class="job-budget"><?= number_format($offre->getBudget(), 0, ',', ' ') ?></span> <small>DT</small></span>
                        <span><i class="fa-solid fa-calendar-days"></i> <?= date('d/m/Y', strtotime($offre->getDateCreation())) ?></span>
                    </div>
                </div>
                <div class="job-actions">
                    <a href="detail_job.php?id=<?= $offre->getId() ?>" class="btn-action btn-view"><i class="fa-solid fa-eye"></i> Voir</a>
                    <a href="edit_job.php?id=<?= $offre->getId() ?>" class="btn-action btn-edit"><i class="fa-solid fa-pen"></i> Modifier</a>
                    <a href="missions.php?action=delete&id=<?= $offre->getId() ?>" class="btn-action btn-delete js-delete" data-title="<?= htmlspecialchars($offre->getTitre()) ?>"><i class="fa-solid fa-trash"></i> Supprimer</a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

<!-- CUSTOM MODAL -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal-card">
        <div class="modal-title"><i class="fa-solid fa-circle-exclamation" style="color:var(--tunisian-red);"></i> Confirmation</div>
        <p class="modal-text" id="modal-desc">Voulez-vous vraiment supprimer cette offre ?</p>
        <div class="modal-actions">
            <button class="btn-modal btn-modal-cancel" id="confirm-cancel">Annuler</button>
            <button class="btn-modal btn-modal-confirm" id="confirm-ok">Supprimer</button>
        </div>
    </div>
</div>

<script>
const deleteModal = document.getElementById('delete-modal');
const confirmOk = document.getElementById('confirm-ok');
const confirmCancel = document.getElementById('confirm-cancel');
const modalDesc = document.getElementById('modal-desc');
let deleteUrl = '';

document.querySelectorAll('.js-delete').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        deleteUrl = btn.href;
        modalDesc.innerHTML = `Voulez-vous vraiment supprimer l'offre <strong style="color:white;">"${btn.dataset.title}"</strong> ?`;
        deleteModal.classList.add('active');
    });
});
confirmCancel.addEventListener('click', () => deleteModal.classList.remove('active'));
confirmOk.addEventListener('click', () => { if (deleteUrl) window.location.href = deleteUrl; });

document.getElementById('download-pdf-home')?.addEventListener('click', async function(e) {
    e.preventDefault();
    const btn = this;
    const originalContent = btn.innerHTML;
    
    try {
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Génération...';
        btn.style.pointerEvents = 'none';

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const rows = <?= json_encode(array_map(fn($o) => [$o->getTitre(), $o->getBudget() . " DT", $o->getDelai(), $o->getStatut(), $o->getDateCreation()], $offres)) ?>;
        doc.autoTable({ head: [["Mission", "Budget", "Délai", "Statut", "Date"]], body: rows });
        
        const pdfBlob = doc.output('blob');
        const formData = new FormData();
        formData.append('pdf', pdfBlob, 'liste_missions.pdf');

        btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up fa-spin"></i> Cloud upload...';
        
        const response = await fetch('../../api/upload_pdf.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.ok) {
            // Open the Cloudinary URL in a new tab
            window.open(result.url, '_blank');
            // Also trigger local download for the user
            saveAs(pdfBlob, 'liste_missions.pdf');
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
<script src="../assets/theme.js"></script>
</body>
</html>
