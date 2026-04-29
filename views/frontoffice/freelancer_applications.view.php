<?php
// views/frontoffice/freelancer_applications.view.php
function getStatusLabel($s) {
    $map = [
        'pending'  => ['label'=>'En attente', 'class'=>'statut-pending'],
        'approved' => ['label'=>'Acceptée', 'class'=>'statut-approved'],
        'rejected' => ['label'=>'Refusée', 'class'=>'statut-rejected'],
    ];
    return $map[$s] ?? $map['pending'];
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Candidatures — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="page-anim">

<nav style="position: sticky; top: 0; width: 100%; z-index: 100; padding: 0 2rem;">
            <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
            <ul class="nav-links">
                <li><a href="home.php">Accueil</a></li>
                <li><a href="home.php">Client</a></li>
                <li><a href="freelancer_home.php" class="active">Freelancer</a></li>
                <li><a href="#">Messagerie</a></li>
            </ul>
            <div class="nav-right">
                <div class="nav-avatar">FR</div>
            </div>
        </nav>



<div class="marketplace-layout">
    
        <aside class="mkt-sidebar">
        <!-- Card 1 : Profil marketplace -->
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-store"></i></div>
                <div class="mkt-profile-name">Espace Freelancer</div>
                <div class="mkt-profile-sub">FreelaSkill Tunisia</div>
            </div>
            <div class="mkt-profile-stats">
                <div class="mkt-stat">
                    <div class="mkt-stat-val">12</div>
                    <div class="mkt-stat-label">MISSIONS</div>
                </div>
                <div class="mkt-stat">
                    <div class="mkt-stat-val">5</div>
                    <div class="mkt-stat-label">POSTULÉES</div>
                </div>
            </div>
        </div>

        <!-- Card 2 : Navigation + Filtres -->
        <div class="mkt-sidebar-card">
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="freelancer_home.php" class="nav-item ">
                    <i class="fa-solid fa-briefcase"></i> Missions
                </a>
                <a href="freelancer_applications.php" class="nav-item active">
                    <i class="fa-solid fa-paper-plane"></i> Candidatures
                </a>
                <a href="#" id="export-pdf" class="nav-item">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
                <a href="home.php" class="nav-item danger">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </aside>

    <div class="mkt-main">
        <!-- TOPBAR INSIDE MAIN -->
        

<section class="hero-banner">
    <div class="hero-glow"></div>
    <div class="hero-content">
        <div class="hero-tag"><i class="fa-solid fa-paper-plane"></i> Suivi</div>
        <h1 class="hero-title">Mes <span>Candidatures</span></h1>
        <p class="hero-sub">Retrouvez ici l'état de toutes les missions pour lesquelles vous avez postulé.</p>
    </div>
</section>

<div class="page-body" style="display: block; max-width: 1100px; margin: 0 auto; padding: 2rem 1rem;">
    <!-- SEARCH BAR -->
    <div style="margin-bottom: 2rem; background: rgba(255, 255, 255, 0.03); padding: 1.5rem; border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
        <form method="GET" action="freelancer_applications.php" style="display: flex; gap: 1rem; align-items: center; margin: 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px; position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.4);"></i>
                <input type="text" name="search" placeholder="Rechercher par titre ou date (AAAA-MM-JJ)..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.1); color: white; padding: 0.85rem 1rem 0.85rem 3rem; border-radius: 12px; font-family: inherit; font-size: 0.95rem; outline: none; transition: border-color 0.3s ease;">
            </div>
            <button type="submit" style="background: var(--primary, #3b82f6); color: white; border: none; padding: 0.85rem 1.8rem; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: transform 0.2s ease, opacity 0.2s ease;">
                <i class="fa-solid fa-filter"></i> Filtrer
            </button>
            <?php if (!empty($_GET['search'])): ?>
                <a href="freelancer_applications.php" style="background: rgba(255,255,255,0.1); color: white; text-decoration: none; padding: 0.85rem 1.8rem; border-radius: 12px; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; transition: background 0.2s ease;">
                    <i class="fa-solid fa-rotate-right"></i> Réinitialiser
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="job-grid">
        <?php if (empty($applications)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <h3 style="color:white; margin-bottom:.5rem;">Aucune candidature</h3>
                <p>Vous n'avez pas encore postulé à des missions. Explorez les offres disponibles !</p>
                <a href="freelancer_home.php" class="btn-cart" style="display:inline-flex; width:auto; margin-top:1.5rem; padding:0.75rem 2rem;">Voir les missions</a>
            </div>
        <?php else: ?>
            <?php foreach ($applications as $app): 
                $status = getStatusLabel($app['status']);
            ?>
            <div class="job-card">
                <div class="job-card-header">
                    <div class="job-icon">💼</div>
                    <div class="job-badge <?= $status['class'] ?>" style="padding:4px 10px; border-radius:var(--radius-full); font-size:0.65rem; font-weight:700; text-transform:uppercase; background:rgba(255,255,255,0.05);">
                        <?= $status['label'] ?>
                    </div>
                </div>
                <div class="job-card-body">
                    <div class="job-titre"><?= htmlspecialchars($app['job_title']) ?></div>
                    <div class="job-meta">
                        <span><i class="fa-solid fa-coins"></i> <span class="job-budget"><?= number_format($app['budget'], 0, ',', ' ') ?></span> <small>DT</small></span>
                        <span><i class="fa-solid fa-calendar-check"></i> Postulé le <?= date('d/m/Y', strtotime($app['created_at'])) ?></span>
                    </div>
                </div>
                <div class="job-actions" style="display: flex; gap: 8px;">
                    <a href="freelancer_detail.php?id=<?= $app['job_id'] ?>" class="btn-action btn-view" style="flex: 1; text-align: center;"><i class="fa-solid fa-eye"></i> Voir la mission</a>
                    <?php if ($app['status'] === 'pending'): ?>
                    <form method="POST" action="freelancer_applications.php" style="margin: 0;" class="form-cancel" data-title="<?= htmlspecialchars($app['job_title']) ?>">
                        <input type="hidden" name="action" value="cancel">
                        <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                        <button type="button" class="btn-action js-cancel" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; border-radius: 8px; cursor: pointer; height: 100%; padding: 0 15px;" title="Annuler la candidature">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .statut-pending { color: #F59E0B; border: 1px solid rgba(245,158,11,0.3); }
    .statut-approved { color: #10b981; border: 1px solid rgba(16,185,129,0.3); }
    .statut-rejected { color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
</style>

<!-- CUSTOM MODAL -->
<div class="modal-overlay" id="cancel-modal">
    <div class="modal-card">
        <div class="modal-title"><i class="fa-solid fa-circle-exclamation" style="color:var(--tunisian-red);"></i> Confirmation</div>
        <p class="modal-text" id="modal-desc">Voulez-vous vraiment annuler cette candidature ?</p>
        <div class="modal-actions">
            <button class="btn-modal btn-modal-cancel" id="confirm-cancel">Non, garder</button>
            <button class="btn-modal btn-modal-confirm" id="confirm-ok" style="background: var(--tunisian-red);">Oui, annuler</button>
        </div>
    </div>
</div>

<script>
const cancelModal = document.getElementById('cancel-modal');
const confirmOk = document.getElementById('confirm-ok');
const confirmCancel = document.getElementById('confirm-cancel');
const modalDesc = document.getElementById('modal-desc');
let formToSubmit = null;

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
</script>

    </div>
</div>
</body>
</html>
