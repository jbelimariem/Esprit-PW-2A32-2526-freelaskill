<?php
// views/backoffice/detail_job_admin.view.php — Template: Admin Détail complet d'une offre

$competences = array_map('trim', explode(',', $offre->getCompetences()));
$statutConfig = [
    'pending'  => ['label'=>'En attente', 'color'=>'#F59E0B', 'bg'=>'rgba(245,158,11,0.1)', 'border'=>'rgba(245,158,11,0.2)', 'icon'=>'fa-clock'],
    'approved' => ['label'=>'Approuvée',  'color'=>'#10b981', 'bg'=>'rgba(16,185,129,0.1)',  'border'=>'rgba(16,185,129,0.2)',  'icon'=>'fa-circle-check'],
    'rejected' => ['label'=>'Rejetée',    'color'=>'#ef4444', 'bg'=>'rgba(239,68,68,0.1)',   'border'=>'rgba(239,68,68,0.2)',   'icon'=>'fa-circle-xmark'],
];
$s = $statutConfig[$offre->getStatut()] ?? $statutConfig['pending'];
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Mission #<?= $offre->getId() ?> | Admin FreelaSkill</title>
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin_v2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="../assets/theme.js" defer></script>
    <style>
        .detail-card {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .detail-label { color: #94A3B8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; }
        .detail-value { color: white; font-size: 1.1rem; font-weight: 500; }
        .comp-tag {
            background: rgba(59, 130, 246, 0.1);
            color: var(--tech-blue);
            padding: 6px 16px;
            border-radius: 99px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
        .candidate-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s ease;
        }
        .candidate-card:hover { background: rgba(255, 255, 255, 0.04); border-color: rgba(59, 130, 246, 0.2); transform: translateY(-2px); }
        .candidate-avatar {
            width: 48px; height: 48px; border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: white; font-size: 1.2rem;
        }
    </style>
</head>
<body class="page-anim">

<div class="hero-glow"></div>
<div class="hero-glow-2"></div>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- MAIN -->
    <main class="main-panel">

        <!-- HEADER -->
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3rem;" class="animate-up">
            <div>
                <h1 class="admin-page-title">Détails de la <span style="color:var(--tech-blue)">Mission</span></h1>
                <p style="color:var(--text-muted);font-size:.95rem;">Consultez les informations complètes et gérez les candidatures.</p>
            </div>
            <div style="display:flex;align-items:center;gap:1.25rem;">
                <button type="button" class="theme-toggle" data-theme-toggle>
                    <i class="fa-solid fa-sun" data-theme-icon></i>
                    <span data-theme-label>Jour</span>
                </button>
                <a href="edit_job_admin.php?id=<?= $offre->getId() ?>" class="btn-add" style="padding: 0.8rem 1.8rem; border-radius: 12px; text-decoration:none;">
                    <i class="fa-solid fa-pen-to-square"></i> Modifier
                </a>
                <a href="admin_missions.php" class="btn btn-outline" style="border-radius: 12px; padding: 0.8rem 1.5rem;">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </a>
            </div>
        </header>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;" class="animate-up">
            <div>
                <!-- Info Section -->
                <div class="detail-card">
                    <h3 style="margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; color:white;">
                        <i class="fa-solid fa-circle-info" style="color:var(--tech-blue);"></i> Description du poste
                    </h3>
                    <p style="white-space: pre-wrap; line-height:1.7; color:#cbd5e1; font-size:1rem;"><?= nl2br(htmlspecialchars($offre->getDescription())) ?></p>
                </div>

                <!-- Competences Section -->
                <div class="detail-card">
                    <h3 style="margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; color:white;">
                        <i class="fa-solid fa-brain" style="color:var(--tech-blue);"></i> Compétences requises
                    </h3>
                    <div style="display:flex; flex-wrap:wrap; gap:12px;">
                        <?php foreach ($competences as $comp): ?>
                            <span class="comp-tag"><?= htmlspecialchars($comp) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Candidates Section -->
                <div class="detail-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                        <h3 style="margin:0; display:flex; align-items:center; gap:10px; color:white;">
                            <i class="fa-solid fa-users-rectangle" style="color:var(--tech-blue);"></i> Candidatures
                            <span style="font-size:0.8rem; background:rgba(255,255,255,0.05); padding:2px 10px; border-radius:10px;"><?= count($candidats ?? []) ?></span>
                        </h3>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php if (empty($candidats)): ?>
                            <div style="text-align:center; padding:3rem; background:rgba(255,255,255,0.01); border:1px dashed var(--border); border-radius:16px;">
                                <i class="fa-solid fa-folder-open" style="font-size:2rem; color:var(--text-muted); opacity:0.3; margin-bottom:1rem;"></i>
                                <p style="color:var(--text-muted);">Aucune candidature pour le moment.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($candidats as $can): ?>
                                <div class="candidate-card">
                                    <div class="candidate-avatar"><?= strtoupper(substr($can->getName(), 0, 1)) ?></div>
                                    <div style="flex:1;">
                                        <div style="font-weight:700; color:white; font-size:1.05rem;"><?= htmlspecialchars($can->getName()) ?></div>
                                        <div style="font-size:0.88rem; color:#94A3B8;"><?= htmlspecialchars($can->getJobTitle()) ?></div>
                                        <div style="display:flex; align-items:center; gap:15px; margin-top:8px;">
                                            <span style="font-size:0.75rem; color:var(--text-muted);"><i class="fa-solid fa-calendar-day" style="margin-right:5px;"></i> Postulé récemment</span>
                                            <span style="font-size:0.75rem; color:var(--tech-blue); font-weight:600; text-transform:uppercase;">Statut: <?= htmlspecialchars($can->getStatus()) ?></span>
                                        </div>
                                    </div>
                                    <div style="display:flex; gap:0.75rem;">
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="app_id" value="<?= $can->getId() ?>">
                                            <input type="hidden" name="action_app" value="approved">
                                            <button type="submit" class="btn" style="background:#10b98120; color:#10b981; border:1px solid #10b98140; padding:8px 15px; border-radius:10px; cursor:pointer; font-weight:600;"><i class="fa-solid fa-check"></i></button>
                                        </form>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="app_id" value="<?= $can->getId() ?>">
                                            <input type="hidden" name="action_app" value="rejected">
                                            <button type="submit" class="btn" style="background:#ef444420; color:#ef4444; border:1px solid #ef444440; padding:8px 15px; border-radius:10px; cursor:pointer; font-weight:600;"><i class="fa-solid fa-times"></i></button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <aside>
                <div class="detail-card">
                    <h3 style="margin-bottom:1.5rem; font-size:1.1rem; color:white;">Résumé</h3>
                    <div style="display:flex; flex-direction:column; gap:1.25rem;">
                        <div>
                            <div class="detail-label">Statut actuel</div>
                            <div style="display:flex; align-items:center; gap:8px; color:<?= $s['color'] ?>; font-weight:700; font-size:0.95rem; background:<?= $s['bg'] ?>; border:1px solid <?= $s['border'] ?>; padding:8px 15px; border-radius:10px; width:fit-content;">
                                <i class="fa-solid <?= $s['icon'] ?>"></i> <?= $s['label'] ?>
                            </div>
                        </div>
                        <div>
                            <div class="detail-label">Budget Proposé</div>
                            <div class="detail-value" style="font-size:1.5rem; color:var(--tech-blue); font-weight:700;"><?= number_format($offre->getBudget(), 0, ',', ' ') ?> <span style="font-size:0.9rem; opacity:0.7;">DT</span></div>
                        </div>
                        <div>
                            <div class="detail-label">Délai estimé</div>
                            <div class="detail-value"><?= htmlspecialchars($offre->getDelai()) ?></div>
                        </div>
                    </div>
                </div>

                <div class="detail-card" style="background:linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));">
                    <h3 style="margin-bottom:1rem; font-size:1rem; color:white;">Actions rapides</h3>
                    <p style="font-size:0.85rem; color:#94A3B8; margin-bottom:1.5rem;">Vous pouvez modifier le statut de l'offre directement depuis le dashboard principal.</p>
                    <a href="admin_missions.php" class="btn btn-outline" style="width:100%; border-radius:12px; justify-content:center;">Voir sur le dashboard</a>
                </div>
            </aside>
        </div>
    </main>
</div>

</body>
</html>
