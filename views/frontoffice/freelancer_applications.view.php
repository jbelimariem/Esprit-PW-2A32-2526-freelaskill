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

<nav>
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

<div class="sub-navbar">
    <div class="sub-nav-container">
        <ul class="sub-nav-links">
            <li><a href="freelancer_home.php"><i class="fa-solid fa-magnifying-glass"></i> Trouver des missions</a></li>
            <li><a href="freelancer_applications.php" class="active"><i class="fa-solid fa-paper-plane"></i> Mes Candidatures</a></li>
        </ul>
    </div>
</div>

<section class="hero-banner">
    <div class="hero-glow"></div>
    <div class="hero-content">
        <div class="hero-tag"><i class="fa-solid fa-paper-plane"></i> Suivi</div>
        <h1 class="hero-title">Mes <span>Candidatures</span></h1>
        <p class="hero-sub">Retrouvez ici l'état de toutes les missions pour lesquelles vous avez postulé.</p>
    </div>
</section>

<div class="page-body" style="display: block; max-width: 1100px; margin: 0 auto; padding: 2rem 1rem;">
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
                <div class="job-actions">
                    <a href="freelancer_detail.php?id=<?= $app['job_id'] ?>" class="btn-action btn-view" style="width:100%; flex:none;"><i class="fa-solid fa-eye"></i> Voir la mission</a>
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

</body>
</html>
