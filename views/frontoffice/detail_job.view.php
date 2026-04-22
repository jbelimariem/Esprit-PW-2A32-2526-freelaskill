<?php
// views/frontoffice/detail_job.view.php — Template: Détail d'une offre & Candidatures

$competences = array_map('trim', explode(',', $offre->getCompetences()));
$safeTitre = preg_replace('/[^A-Za-z0-9]/', '_', $offre->getTitre());

$statutConfig = [
    'pending'  => ['label' => 'En attente',  'color' => '#F59E0B', 'bg' => 'rgba(245,158,11,0.12)',  'border' => 'rgba(245,158,11,0.3)',  'icon' => 'fa-clock'],
    'approved' => ['label' => 'Approuvée',   'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.12)',  'border' => 'rgba(16,185,129,0.3)',  'icon' => 'fa-circle-check'],
    'rejected' => ['label' => 'Rejetée',     'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.12)',   'border' => 'rgba(239,68,68,0.3)',   'icon' => 'fa-circle-xmark'],
];
$badge = $statutConfig[$offre->getStatut()] ?? $statutConfig['pending'];

$appStatutConfig = [
    'pending'   => ['label' => 'En attente',  'color' => 'var(--text-muted)', 'bg' => 'rgba(255,255,255,0.05)'],
    'contacted' => ['label' => 'Contacté',   'color' => '#10b981',           'bg' => 'rgba(16,185,129,0.15)'],
    'rejected'  => ['label' => 'Refusé',      'color' => '#ef4444',           'bg' => 'rgba(239,68,68,0.15)'],
];
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($offre->getTitre()) ?> — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <style>
        .detail-layout { display: grid; grid-template-columns: 1fr 340px; gap: 2.5rem; max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; }
        .detail-card { background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 2rem; }
        .detail-body { padding: 2rem; }
        .detail-section-title { font-size: 0.75rem; text-transform: uppercase; color: var(--tech-blue); font-weight: 700; margin-bottom: 1rem; }
        .skill-tag { display: inline-block; background: rgba(59,130,246,0.1); color: var(--tech-blue); padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; margin: 4px; }
        .sidebar-card { background: rgba(2, 6, 23, 0.4); border: 1px solid var(--border); border-radius: var(--radius-lg); position: sticky; top: 100px; }
        .sidebar-section { padding: 1.5rem; border-bottom: 1px solid var(--border); }
        .meta-row { display: flex; justify-content: space-between; padding: 0.5rem 0; }
        .candidate-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 1rem; }
        .candidate-avatar { width: 40px; height: 40px; background: var(--tech-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; }
    </style>
</head>
<body class="page-anim">

<nav>
    <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
    <div class="nav-right">
        <a href="home.php" class="cart-btn" style="background:rgba(255,255,255,0.06); color:white;"><i class="fa-solid fa-arrow-left"></i> Retour</a>
    </div>
</nav>

<section class="hero-banner" style="padding:3rem 1rem;">
    <div class="hero-content" style="max-width:1100px; margin:0 auto;">
        <div class="hero-tag">Mission #<?= $offre->getId() ?></div>
        <h1 class="hero-title"><?= htmlspecialchars($offre->getTitre()) ?></h1>
    </div>
</section>

<div class="detail-layout">
    <div>
        <div class="detail-card">
            <div class="detail-body">
                <div class="detail-section-title">Description</div>
                <p style="white-space: pre-wrap; line-height: 1.6;"><?= nl2br(htmlspecialchars($offre->getDescription())) ?></p>
            </div>
        </div>
        <div class="detail-card">
            <div class="detail-body">
                <div class="detail-section-title">Compétences</div>
                <div><?php foreach ($competences as $comp): ?><span class="skill-tag"><?= htmlspecialchars($comp) ?></span><?php endforeach; ?></div>
            </div>
        </div>

        <div class="detail-section-title" style="color:white; font-size:1.1rem; margin-top:2rem;">Participants (<?= count($candidats) ?>)</div>
        <?php foreach ($candidats as $can): 
            $appBadge = $appStatutConfig[$can->getStatus() ?? 'pending'] ?? $appStatutConfig['pending'];
        ?>
        <div class="candidate-item">
            <div class="candidate-avatar"><?= strtoupper(substr($can->getName(), 0, 1)) ?></div>
            <div style="flex:1;">
                <div style="font-weight:700;"><?= htmlspecialchars($can->getName()) ?> <span style="font-size:0.7rem; color:<?= $appBadge['color'] ?>; margin-left:10px;"><?= $appBadge['label'] ?></span></div>
                <div style="font-size:0.85rem; color:var(--text-muted);"><?= htmlspecialchars($can->getJobTitle()) ?></div>
            </div>
            <form method="POST" novalidate>
                <input type="hidden" name="app_id" value="<?= $can->getId() ?>">
                <input type="hidden" name="action_app" value="contacted">
                <button type="submit" class="btn-action" style="background:rgba(16,185,129,0.1); color:#10b981; border:none; padding:5px 15px; border-radius:5px; cursor:pointer;">Contacter</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <aside>
        <div class="sidebar-card">
            <div class="sidebar-section">
                <div class="meta-row"><span>Budget</span><strong><?= number_format($offre->getBudget(), 0, ',', ' ') ?> DT</strong></div>
                <div class="meta-row"><span>Délai</span><strong><?= htmlspecialchars($offre->getDelai()) ?></strong></div>
                <div class="meta-row"><span>Publiée</span><strong><?= date('d/m/Y', strtotime($offre->getDateCreation())) ?></strong></div>
            </div>
            <div class="sidebar-section">
                <div style="text-align:center; color:<?= $badge['color'] ?>; padding:10px; border-radius:5px; background:<?= $badge['bg'] ?>; font-weight:700;">Statut : <?= $badge['label'] ?></div>
            </div>
            <div class="sidebar-section">
                <button id="download-pdf" class="btn-search" style="width:100%; justify-content:center; background:rgba(255,255,255,0.05); color:white; padding:12px; border:1px solid var(--border); border-radius:8px; cursor:pointer; font-weight:700;">Télécharger PDF</button>
            </div>
        </div>
    </aside>
</div>

<script>
document.getElementById('download-pdf')?.addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text("Détail de la Mission", 14, 15);
    doc.autoTable({
        startY: 20,
        head: [["Caractéristique", "Détail"]],
        body: [
            ["Titre", "<?= addslashes($offre->getTitre()) ?>"],
            ["Budget", "<?= number_format($offre->getBudget(), 0, ',', ' ') ?> DT"],
            ["Délai", "<?= addslashes($offre->getDelai()) ?>"],
            ["Statut", "<?= $badge['label'] ?>"]
        ]
    });
    saveAs(doc.output('blob'), 'mission_<?= $safeTitre ?>.pdf');
});
</script>
</body>
</html>
