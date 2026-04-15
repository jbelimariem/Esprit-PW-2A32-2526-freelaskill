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

// Action rapide depuis cette page
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
    <title>Offre #<?= $offre['id'] ?> | Admin</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <!-- Theme CSS -->
    <link rel="stylesheet" href="admin.css?v=<?= time() ?>">
</head>
<body class="page-anim">
    
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>

    <div class="admin-layout">
        
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <div class="logo">
                <i class="fa-solid fa-shapes"></i>
                Freela<span>Skill</span>
            </div>
            
            <nav class="admin-nav">
                <div style="margin: 0.5rem 0 0.5rem 1rem; font-size: 0.7rem; text-transform: uppercase; color: #475569; font-weight: 700; letter-spacing: 1px;">Menu Principal</div>
                <a href="dashboard.php" class="admin-nav-item">
                    <i class="fa-solid fa-briefcase"></i> Gestion des Missions
                </a>

                <div style="margin: 1.5rem 0 0.5rem 1rem; font-size: 0.7rem; text-transform: uppercase; color: #475569; font-weight: 700; letter-spacing: 1px;">Actions</div>
                <a href="add_job_admin.php" class="admin-nav-item">
                    <i class="fa-solid fa-plus-circle"></i> Ajouter une Offre
                </a>
            </nav>
        </aside>

        <!-- MAIN AREA -->
        <main class="admin-main">
            <!-- TOPBAR -->
            <header class="admin-topbar">
                <div style="color: var(--text-muted); font-size: 0.9rem;">
                    Back-office / Missions / <span style="color: white;">Détails #<?= $offre['id'] ?></span>
                </div>
                <div class="admin-top-actions">
                    <div class="admin-icon-btn"><i class="fa-regular fa-bell"></i></div>
                    <div class="nav-avatar">AH</div>
                </div>
            </header>

            <!-- CONTENT -->
            <div class="admin-content">
                <div class="admin-header-row">
                    <div>
                        <h1 class="admin-page-title">Détails de la <span>Mission</span></h1>
                        <p style="color: var(--text-muted); margin-top: 0.5rem;">Visualisation complète de l'offre ID: #<?= $offre['id'] ?></p>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <a href="edit_job_admin.php?id=<?= $offre['id'] ?>" class="btn btn-primary">
                            <i class="fa-solid fa-pen"></i> Modifier
                        </a>
                        <a href="dashboard.php" class="btn btn-outline">
                            <i class="fa-solid fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    
                    <!-- Left Column -->
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        <!-- Description -->
                        <div class="glass-card">
                            <h3 style="color: white; font-size: 1.1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                                <i class="fa-solid fa-align-left" style="color: var(--tech-blue);"></i>
                                Description de la mission
                            </h3>
                            <div style="color: var(--text-light); line-height: 1.8; font-size: 0.95rem;">
                                <?= nl2br(htmlspecialchars($offre['description'])) ?>
                            </div>
                        </div>

                        <!-- Skills -->
                        <div class="glass-card">
                            <h3 style="color: white; font-size: 1.1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                                <i class="fa-solid fa-tags" style="color: var(--tech-blue);"></i>
                                Compétences requises
                            </h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                                <?php foreach ($competences as $comp): ?>
                                    <span style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); color: var(--tech-blue); padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600;">
                                        <?= htmlspecialchars($comp) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (Meta) -->
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        <div class="glass-card">
                            <h3 style="color: white; font-size: 1.1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                                <i class="fa-solid fa-info-circle" style="color: var(--tech-blue);"></i>
                                Informations clés
                            </h3>
                            
                            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                                    <span style="color: var(--text-muted);">Status</span>
                                    <span class="statut-badge <?= $badge['class'] ?>">
                                        <i class="fa-solid <?= $badge['icon'] ?>"></i> <?= $badge['label'] ?>
                                    </span>
                                </div>
                                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                                    <span style="color: var(--text-muted);">Budget</span>
                                    <span style="font-family: 'JetBrains Mono'; font-weight: 700; color: white;"><?= number_format($offre['budget'], 0, ',', ' ') ?> DT</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                                    <span style="color: var(--text-muted);">Délai</span>
                                    <span style="color: white; font-weight: 600;"><?= htmlspecialchars($offre['delai']) ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                                    <span style="color: var(--text-muted);">Créée le</span>
                                    <span style="color: white; font-size: 0.85rem;"><?= date('d/m/Y à H:i', strtotime($offre['date_creation'])) ?></span>
                                </div>
                            </div>

                            <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 0.75rem;">
                                <?php if ($offre['statut'] === 'pending'): ?>
                                    <a href="?id=<?= $offre['id'] ?>&action=approve" class="btn btn-primary" style="justify-content: center; background: var(--tech-green);">
                                        <i class="fa-solid fa-check"></i> Approuver la mission
                                    </a>
                                    <a href="?id=<?= $offre['id'] ?>&action=reject" class="btn btn-outline" style="justify-content: center; color: var(--tunisian-red); border-color: rgba(239, 68, 68, 0.2);">
                                        <i class="fa-solid fa-xmark"></i> Rejeter la mission
                                    </a>
                                <?php elseif ($offre['statut'] === 'approved'): ?>
                                    <a href="?id=<?= $offre['id'] ?>&action=reject" class="btn btn-outline" style="justify-content: center; color: var(--tunisian-red); border-color: rgba(239, 68, 68, 0.2);">
                                        <i class="fa-solid fa-ban"></i> Désactiver (Rejeter)
                                    </a>
                                <?php else: ?>
                                    <a href="?id=<?= $offre['id'] ?>&action=approve" class="btn btn-outline" style="justify-content: center; color: var(--tech-green); border-color: rgba(16, 185, 129, 0.2);">
                                        <i class="fa-solid fa-rotate-left"></i> Ré-approuver
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <!-- TOASTS -->
    <?php if ($success): ?>
    <div class="toast toast-success" id="toast">
        <i class="fa-solid fa-circle-check"></i>
        Action <?= htmlspecialchars($success) ?> réussie !
    </div>
    <script>setTimeout(()=>{const t=document.getElementById('toast'); if(t) t.style.opacity='0';}, 3500);</script>
    <?php endif; ?>

</body>
</html>
