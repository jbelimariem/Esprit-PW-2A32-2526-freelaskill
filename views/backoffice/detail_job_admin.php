<?php
// views/backoffice/detail_job_admin.php — Admin: Détail complet d'une offre

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/JobOffer.php';

$model = new JobOffer();
$id    = (int)($_GET['id'] ?? 0);
$offre = $model->getById($id);

if (!$offre) {
    header('Location: dashboard.php'); exit;
}

// Action rapide depuis ce page
if (!empty($_GET['action'])) {
    if ($_GET['action'] === 'approve') { $model->updateStatut($id, 'approved'); header("Location: detail_job_admin.php?id=$id&success=approved"); exit; }
    if ($_GET['action'] === 'reject')  { $model->updateStatut($id, 'rejected'); header("Location: detail_job_admin.php?id=$id&success=rejected"); exit; }
}

$success     = $_GET['success'] ?? '';
$competences = array_map('trim', explode(',', $offre['competences']));

$statutConfig = [
    'pending'  => ['label'=>'En attente','class'=>'statut-pending', 'icon'=>'fa-clock',       'color'=>'#F59E0B'],
    'approved' => ['label'=>'Approuvée', 'class'=>'statut-approved','icon'=>'fa-circle-check','color'=>'#10b981'],
    'rejected' => ['label'=>'Rejetée',   'class'=>'statut-rejected','icon'=>'fa-circle-xmark','color'=>'#ef4444'],
];
$badge = $statutConfig[$offre['statut']] ?? $statutConfig['pending'];
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offre #<?= $offre['id'] ?> — Admin FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .detail-grid { display:grid; grid-template-columns:1fr 300px; gap:1.5rem; }
        .detail-section {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 1.25rem;
        }
        .detail-section-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #334155;
            font-weight: 700;
        }
        .detail-section-body { padding: 1.5rem; }
        .detail-text { color: var(--text-light); font-size: .92rem; line-height: 1.75; white-space: pre-wrap; }
        .skill-tag {
            display:inline-flex; align-items:center; gap:4px;
            background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.25);
            color:var(--tech-blue); padding:.3rem .85rem;
            border-radius:var(--radius-full); font-size:.78rem; font-weight:500; margin:.2rem;
        }
        .meta-row {
            display:flex; justify-content:space-between; align-items:center;
            padding:.75rem 0; border-bottom:1px solid rgba(255,255,255,0.04);
            font-size:.85rem;
        }
        .meta-row:last-child { border-bottom:none; }
        .meta-label { color:var(--text-muted); display:flex; align-items:center; gap:.5rem; }
        .meta-value { font-weight:600; color:white; }
    </style>
</head>
<body>

<?php if ($success): ?>
<div class="toast <?= in_array($success,['rejected']) ? 'toast-error' : 'toast-success' ?>" id="detail-toast">
    <?php if ($success==='approved'): ?><i class="fa-solid fa-circle-check"></i> Offre approuvée !
    <?php elseif ($success==='rejected'): ?><i class="fa-solid fa-circle-xmark"></i> Offre rejetée.
    <?php endif; ?>
</div>
<script>setTimeout(()=>{const t=document.getElementById('detail-toast');if(t)t.style.opacity='0';},3500);</script>
<?php endif; ?>

<aside class="sidebar">
    <div class="logo">
        <i class="fa-solid fa-briefcase"></i>
        Core<span>Panel</span>
        <small>Admin Jobs v1.0</small>
    </div>
    <div class="nav-section-title">Navigation</div>
    <a href="dashboard.php" class="nav-item" id="sidebar-nav-dashboard"><i class="fa-solid fa-gauge-high"></i> Tableau de bord</a>
    <a href="add_job_admin.php" class="nav-item" id="sidebar-nav-add"><i class="fa-solid fa-plus"></i> Ajouter une offre</a>
    <div class="sidebar-footer">
        <a href="../frontoffice/home.php" class="nav-item" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07);">
            <i class="fa-solid fa-globe"></i> Interface Client
        </a>
    </div>
</aside>

<main class="main-panel">

    <!-- TOPBAR -->
    <div class="topbar">
        <div>
            <h1 class="topbar-title">Détail <span>offre #<?= $offre['id'] ?></span></h1>
            <p style="color:var(--text-muted); font-size:.82rem; margin-top:.2rem;">
                Publiée le <?= date('d/m/Y à H:i', strtotime($offre['date_creation'])) ?>
            </p>
        </div>
        <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
            <?php if ($offre['statut'] === 'pending'): ?>
            <a href="detail_job_admin.php?id=<?= $offre['id'] ?>&action=approve" id="detail-approve" class="btn btn-approve">
                <i class="fa-solid fa-check"></i> Approuver
            </a>
            <a href="detail_job_admin.php?id=<?= $offre['id'] ?>&action=reject" id="detail-reject" class="btn btn-reject">
                <i class="fa-solid fa-xmark"></i> Rejeter
            </a>
            <?php elseif ($offre['statut'] === 'approved'): ?>
            <a href="detail_job_admin.php?id=<?= $offre['id'] ?>&action=reject" id="detail-disapprove" class="btn btn-reject">
                <i class="fa-solid fa-ban"></i> Désapprouver
            </a>
            <?php elseif ($offre['statut'] === 'rejected'): ?>
            <a href="detail_job_admin.php?id=<?= $offre['id'] ?>&action=approve" id="detail-reapprove" class="btn btn-approve">
                <i class="fa-solid fa-rotate-left"></i> Ré-approuver
            </a>
            <?php endif; ?>
            <a href="edit_job_admin.php?id=<?= $offre['id'] ?>" id="detail-edit" class="btn btn-edit">
                <i class="fa-solid fa-pen"></i> Modifier
            </a>
            <a href="dashboard.php?action=delete&id=<?= $offre['id'] ?>"
               id="detail-delete"
               class="btn btn-danger js-admin-delete"
               data-title="<?= htmlspecialchars($offre['titre']) ?>">
                <i class="fa-solid fa-trash"></i> Supprimer
            </a>
            <a href="dashboard.php" id="detail-back" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- STATUS BANNER -->
    <div style="background:<?= ['pending'=>'rgba(245,158,11,0.07)','approved'=>'rgba(16,185,129,0.07)','rejected'=>'rgba(239,68,68,0.07)'][$offre['statut']] ?>; border:1px solid <?= ['pending'=>'rgba(245,158,11,0.2)','approved'=>'rgba(16,185,129,0.2)','rejected'=>'rgba(239,68,68,0.2)'][$offre['statut']] ?>; border-radius:var(--radius-md); padding:.85rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:.75rem; font-size:.88rem;">
        <i class="fa-solid <?= $badge['icon'] ?>" style="color:<?= $badge['color'] ?>;"></i>
        <span style="color:<?= $badge['color'] ?>; font-weight:600;"><?= $badge['label'] ?></span>
        <span style="color:var(--text-muted); margin-left:.25rem;">— Cette offre est actuellement <strong style="color:<?= $badge['color'] ?>;"><?= strtolower($badge['label']) ?></strong>.</span>
    </div>

    <div class="detail-grid animate-fade-up">

        <!-- LEFT: Main content -->
        <div>
            <!-- Description -->
            <div class="detail-section">
                <div class="detail-section-header">Description de la mission</div>
                <div class="detail-section-body">
                    <p class="detail-text"><?= nl2br(htmlspecialchars($offre['description'])) ?></p>
                </div>
            </div>

            <!-- Compétences -->
            <div class="detail-section">
                <div class="detail-section-header">Compétences requises</div>
                <div class="detail-section-body">
                    <?php foreach ($competences as $comp): ?>
                    <span class="skill-tag"><i class="fa-solid fa-check" style="font-size:.6rem;"></i> <?= htmlspecialchars($comp) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: Meta info -->
        <div>
            <div class="detail-section" style="position:sticky; top:20px;">
                <div class="detail-section-header">Informations</div>
                <div class="detail-section-body">
                    <div class="meta-row">
                        <span class="meta-label"><i class="fa-solid fa-hashtag"></i> ID</span>
                        <span class="meta-value" style="font-family:'JetBrains Mono',monospace;">#<?= $offre['id'] ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fa-solid fa-heading"></i> Titre</span>
                        <span class="meta-value" style="font-size:.82rem; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($offre['titre']) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fa-solid fa-coins"></i> Budget</span>
                        <span class="meta-value" style="font-family:'JetBrains Mono',monospace; color:var(--tech-blue);">
                            <?= number_format($offre['budget'],0,',',' ') ?> DT
                        </span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fa-solid fa-clock"></i> Délai</span>
                        <span class="meta-value"><?= htmlspecialchars($offre['delai']) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fa-solid fa-circle-half-stroke"></i> Statut</span>
                        <span class="statut-badge <?= $badge['class'] ?>">
                            <i class="fa-solid <?= $badge['icon'] ?>" style="font-size:.6rem;"></i>
                            <?= $badge['label'] ?>
                        </span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fa-solid fa-user"></i> Client ID</span>
                        <span class="meta-value"><?= $offre['client_id'] ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fa-solid fa-calendar"></i> Créée le</span>
                        <span class="meta-value" style="font-size:.82rem;"><?= date('d/m/Y à H:i', strtotime($offre['date_creation'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</main>

<script>
document.querySelectorAll('.js-admin-delete').forEach(btn => {
    btn.addEventListener('click', e => {
        const titre = btn.dataset.title || 'cette offre';
        if (!confirm(`⚠️ Supprimer l'offre "${titre}" ?\n\nCette action est irréversible.`)) e.preventDefault();
    });
});
</script>

</body>
</html>
