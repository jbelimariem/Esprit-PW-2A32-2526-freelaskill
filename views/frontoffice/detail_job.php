<?php
// views/frontoffice/detail_job.php — Client: Détail d'une offre

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/JobOffer.php';

$model = new JobOffer();
$id    = (int)($_GET['id'] ?? 0);
$offre = $model->getById($id);

if (!$offre) {
    header('Location: home.php');
    exit;
}

$competences = array_map('trim', explode(',', $offre['competences']));

$statutConfig = [
    'pending'  => ['label' => 'En attente',  'color' => '#F59E0B', 'bg' => 'rgba(245,158,11,0.12)',  'border' => 'rgba(245,158,11,0.3)',  'icon' => 'fa-clock'],
    'approved' => ['label' => 'Approuvée',   'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.12)',  'border' => 'rgba(16,185,129,0.3)',  'icon' => 'fa-circle-check'],
    'rejected' => ['label' => 'Rejetée',     'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.12)',   'border' => 'rgba(239,68,68,0.3)',   'icon' => 'fa-circle-xmark'],
];
$badge = $statutConfig[$offre['statut']] ?? $statutConfig['pending'];
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($offre['titre']) ?> — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 2rem;
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 4rem 4rem;
        }
        .detail-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        .detail-header {
            padding: 2rem 2rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .detail-body { padding: 2rem; }
        .detail-section-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #334155;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .detail-text {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.7;
            white-space: pre-wrap;
        }
        .skill-tag {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(59,130,246,0.1);
            border: 1px solid rgba(59,130,246,0.25);
            color: var(--tech-blue);
            padding: 0.3rem 0.85rem;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 500;
            margin: 0.25rem;
        }
        .sidebar-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            position: sticky;
            top: 86px;
        }
        .sidebar-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-card-body { padding: 1.5rem; }
        .meta-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            font-size: 0.88rem;
        }
        .meta-row:last-child { border-bottom: none; }
        .meta-label { color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; }
        .meta-value { font-weight: 600; color: white; }
        .statut-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 0.35rem 0.9rem;
            border-radius: var(--radius-full);
            font-size: 0.78rem; font-weight: 600;
            border: 1px solid;
        }
    </style>
</head>
<body class="page-anim">

<nav>
    <div class="logo">
        <i class="fa-solid fa-briefcase"></i>
        Freela<span>Skill</span>
    </div>
    <div class="nav-right">
        <a href="home.php" class="cart-btn" style="background:rgba(255,255,255,0.06); border-color:rgba(255,255,255,0.12); color:white;">
            <i class="fa-solid fa-arrow-left"></i> Mes offres
        </a>
    </div>
</nav>

<section class="hero-banner" style="padding:3rem 4rem 2rem;">
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>
    <div class="hero-content" style="max-width:800px;">
        <div class="hero-tag"><i class="fa-solid fa-briefcase"></i> Détail de l'offre #<?= $offre['id'] ?></div>
        <h1 class="hero-title" style="font-size:2rem;"><?= htmlspecialchars($offre['titre']) ?></h1>
        <div style="display:flex; align-items:center; gap:1rem; margin-top:0.75rem; flex-wrap:wrap;">
            <span class="statut-pill" style="color:<?= $badge['color'] ?>; background:<?= $badge['bg'] ?>; border-color:<?= $badge['border'] ?>;">
                <i class="fa-solid <?= $badge['icon'] ?>"></i>
                <?= $badge['label'] ?>
            </span>
            <span style="color:var(--text-muted); font-size:.85rem;">
                <i class="fa-solid fa-calendar-days"></i>
                Publiée le <?= date('d/m/Y à H:i', strtotime($offre['date_creation'])) ?>
            </span>
        </div>
    </div>
</section>

<div class="detail-layout">

    <!-- MAIN CONTENT -->
    <div>
        <!-- Description -->
        <div class="detail-card" style="margin-bottom:1.5rem;">
            <div class="detail-header">
                <div class="detail-section-title">Description de la mission</div>
            </div>
            <div class="detail-body">
                <p class="detail-text"><?= nl2br(htmlspecialchars($offre['description'])) ?></p>
            </div>
        </div>

        <!-- Compétences -->
        <div class="detail-card" style="margin-bottom:1.5rem;">
            <div class="detail-header">
                <div class="detail-section-title">Compétences requises</div>
            </div>
            <div class="detail-body">
                <?php foreach ($competences as $comp): ?>
                <span class="skill-tag"><i class="fa-solid fa-check" style="font-size:.65rem;"></i> <?= htmlspecialchars($comp) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Actions -->
        <div style="display:flex; gap:1rem; flex-wrap:wrap;">
            <a href="edit_job.php?id=<?= $offre['id'] ?>" id="btn-edit-detail" class="btn-search" style="background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.3); color:#F59E0B;">
                <i class="fa-solid fa-pen"></i> Modifier l'offre
            </a>
            <a href="home.php?action=delete&id=<?= $offre['id'] ?>" id="btn-delete-detail"
               class="btn-search js-delete"
               style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color:var(--tunisian-red);"
               data-title="<?= htmlspecialchars($offre['titre']) ?>">
                <i class="fa-solid fa-trash"></i> Supprimer
            </a>
            <a href="home.php" class="btn-search" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:var(--text-muted);">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="sidebar-card">
        <div class="sidebar-card-header">
            <div style="font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; color:#334155; font-weight:700;">Informations</div>
        </div>
        <div class="sidebar-card-body">
            <div class="meta-row">
                <span class="meta-label"><i class="fa-solid fa-hashtag"></i> ID</span>
                <span class="meta-value" style="font-family:'JetBrains Mono',monospace;">#<?= $offre['id'] ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label"><i class="fa-solid fa-coins"></i> Budget</span>
                <span class="meta-value" style="font-family:'JetBrains Mono',monospace; color:var(--tech-blue);"><?= number_format($offre['budget'], 0, ',', ' ') ?> DT</span>
            </div>
            <div class="meta-row">
                <span class="meta-label"><i class="fa-solid fa-clock"></i> Délai</span>
                <span class="meta-value"><?= htmlspecialchars($offre['delai']) ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label"><i class="fa-solid fa-circle-half-stroke"></i> Statut</span>
                <span class="statut-pill" style="font-size:.72rem; color:<?= $badge['color'] ?>; background:<?= $badge['bg'] ?>; border-color:<?= $badge['border'] ?>;">
                    <?= $badge['label'] ?>
                </span>
            </div>
            <div class="meta-row">
                <span class="meta-label"><i class="fa-solid fa-calendar"></i> Publié le</span>
                <span class="meta-value" style="font-size:.82rem;"><?= date('d/m/Y', strtotime($offre['date_creation'])) ?></span>
            </div>

            <?php if ($offre['statut'] === 'approved'): ?>
            <div style="margin-top:1.5rem; padding:1rem; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2); border-radius:var(--radius-md); font-size:.83rem; color:var(--tech-green); text-align:center;">
                <i class="fa-solid fa-circle-check"></i> Votre offre est visible par les freelancers !
            </div>
            <?php elseif ($offre['statut'] === 'pending'): ?>
            <div style="margin-top:1.5rem; padding:1rem; background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.2); border-radius:var(--radius-md); font-size:.83rem; color:#F59E0B; text-align:center;">
                <i class="fa-solid fa-clock"></i> En attente de validation admin.
            </div>
            <?php elseif ($offre['statut'] === 'rejected'): ?>
            <div style="margin-top:1.5rem; padding:1rem; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius-md); font-size:.83rem; color:var(--tunisian-red); text-align:center;">
                <i class="fa-solid fa-circle-xmark"></i> Offre rejetée par l'admin.
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
document.querySelectorAll('.js-delete').forEach(btn => {
    btn.addEventListener('click', e => {
        const titre = btn.dataset.title || 'cette offre';
        if (!confirm(`Supprimer l'offre "${titre}" ? Cette action est irréversible.`)) {
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>
