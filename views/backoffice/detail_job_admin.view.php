<?php
// views/backoffice/detail_job_admin.view.php — Template: Admin Détail complet d'une offre

$competences = array_map('trim', explode(',', $offre->getCompetences()));
$statutConfig = [
    'pending'  => ['label'=>'En attente','class'=>'statut-pending', 'icon'=>'fa-clock',       'color'=>'#F59E0B'],
    'approved' => ['label'=>'Approuvée', 'class'=>'statut-approved','icon'=>'fa-circle-check','color'=>'#10b981'],
    'rejected' => ['label'=>'Rejetée',   'class'=>'statut-rejected','icon'=>'fa-circle-xmark','color'=>'#ef4444'],
];
$badge = $statutConfig[$offre->getStatut()] ?? $statutConfig['pending'];
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offre #<?= $offre['id'] ?> | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="admin.css?v=<?= time() ?>">
</head>
<body class="page-anim">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
            <nav class="admin-nav"><a href="dashboard.php" class="admin-nav-item"><i class="fa-solid fa-briefcase"></i> Missions</a></nav>
        </aside>

        <main class="admin-main">
            <div class="admin-content">
                <div class="admin-header-row">
                    <h1 class="admin-page-title">Détails de la <span>Mission</span></h1>
                    <div style="display:flex; gap:1rem;">
                        <a href="edit_job_admin.php?id=<?= $offre->getId() ?>" class="btn btn-primary">Modifier</a>
                        <a href="dashboard.php" class="btn btn-outline">Retour</a>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    <div>
                        <div class="glass-card">
                            <h3>Description</h3>
                            <p style="white-space: pre-wrap; line-height:1.6;"><?= nl2br(htmlspecialchars($offre->getDescription())) ?></p>
                        </div>
                        <div class="glass-card" style="margin-top:2rem;">
                            <h3>Compétences</h3>
                            <div style="display:flex; flex-wrap:wrap; gap:10px;">
                                <?php foreach ($competences as $comp): ?><span style="background:rgba(59,130,246,0.1); padding:5px 15px; border-radius:20px; color:var(--tech-blue); font-weight:700;"><?= htmlspecialchars($comp) ?></span><?php endforeach; ?>
                            </div>
                        </div>

                        <div class="glass-card" style="margin-top:2rem;">
                            <h3>Candidatures reçues (<?= count($candidats ?? []) ?>)</h3>
                            <div style="margin-top:1rem;">
                                <?php if (empty($candidats)): ?>
                                    <p style="color:var(--text-muted);">Aucune candidature pour le moment.</p>
                                <?php else: ?>
                                    <?php foreach ($candidats as $can): ?>
                                    <div style="display:flex; align-items:center; gap:1rem; padding:1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:12px; margin-bottom:1rem;">
                                        <div style="width:40px; height:40px; background:var(--tech-blue); border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;"><?= strtoupper(substr($can->getName(), 0, 1)) ?></div>
                                        <div style="flex:1;">
                                            <div style="font-weight:700;"><?= htmlspecialchars($can->getName()) ?></div>
                                            <div style="font-size:0.85rem; color:var(--text-muted);"><?= htmlspecialchars($can->getJobTitle()) ?></div>
                                            <div style="font-size:0.75rem; margin-top:4px;">Statut : <strong><?= htmlspecialchars($can->getStatus()) ?></strong></div>
                                        </div>
                                        <div style="display:flex; gap:0.5rem;">
                                            <form method="POST" style="margin:0;">
                                                <input type="hidden" name="app_id" value="<?= $can->getId() ?>">
                                                <input type="hidden" name="action_app" value="approved">
                                                <button type="submit" class="btn" style="background:#10b981; color:white; border:none; padding:5px 10px; cursor:pointer;">Valider</button>
                                            </form>
                                            <form method="POST" style="margin:0;">
                                                <input type="hidden" name="app_id" value="<?= $can->getId() ?>">
                                                <input type="hidden" name="action_app" value="rejected">
                                                <button type="submit" class="btn" style="background:#ef4444; color:white; border:none; padding:5px 10px; cursor:pointer;">Refuser</button>
                                            </form>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <aside>
                        <div class="glass-card">
                            <h3>Infos</h3>
                            <div style="margin-top:1rem;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Statut</span><span class="statut-badge <?= $badge['class'] ?>"><?= $badge['label'] ?></span></div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Budget</span><strong><?= number_format($offre->getBudget(), 0, ',', ' ') ?> DT</strong></div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
