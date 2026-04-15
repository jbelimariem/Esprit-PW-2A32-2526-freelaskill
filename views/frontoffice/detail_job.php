<?php
// views/frontoffice/detail_job.php — Client: Détail d'une offre & Candidatures

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/JobOffer.php';
require_once __DIR__ . '/../../Models/JobApplication.php';

$model = new JobOffer();
$appModel = new JobApplication();

$id    = (int)($_GET['id'] ?? 0);
$offre = $model->getById($id);

if (!$offre) {
    header('Location: home.php');
    exit;
}

// ── Actions Client sur les Candidatures ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_app'])) {
    $appId  = (int)$_POST['app_id'];
    $action = $_POST['action_app']; // 'contacted' or 'rejected'
    $appModel->updateStatus($appId, $action);
    header("Location: detail_job.php?id=$id&success=$action");
    exit;
}

$candidats = $appModel->getByJobId($id);
$competences = array_map('trim', explode(',', $offre['competences']));

$statutConfig = [
    'pending'  => ['label' => 'En attente',  'color' => '#F59E0B', 'bg' => 'rgba(245,158,11,0.12)',  'border' => 'rgba(245,158,11,0.3)',  'icon' => 'fa-clock'],
    'approved' => ['label' => 'Approuvée',   'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.12)',  'border' => 'rgba(16,185,129,0.3)',  'icon' => 'fa-circle-check'],
    'rejected' => ['label' => 'Rejetée',     'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.12)',   'border' => 'rgba(239,68,68,0.3)',   'icon' => 'fa-circle-xmark'],
];
$badge = $statutConfig[$offre['statut']] ?? $statutConfig['pending'];

// Config pour les statuts des candidats
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
    <title><?= htmlspecialchars($offre['titre']) ?> — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=<?= time() ?>">
    <!-- Librairie PDF Direct -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 2.5rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 4rem 4rem;
        }
        .detail-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            backdrop-filter: blur(10px);
            margin-bottom: 2rem;
        }
        .detail-header { padding: 2rem 2rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .detail-body { padding: 2rem; }
        .detail-section-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--tech-blue); font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .detail-text { color: var(--text-light); font-size: 1rem; line-height: 1.8; white-space: pre-wrap; }
        
        .skill-tag {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2);
            color: var(--tech-blue); padding: 0.4rem 1rem; border-radius: var(--radius-full);
            font-size: 0.82rem; font-weight: 600; margin: 0.3rem; 
        }

        .sidebar-card { background: rgba(2, 6, 23, 0.4); border: 1px solid var(--border); border-radius: var(--radius-lg); position: sticky; top: 100px; backdrop-filter: blur(10px); }
        .sidebar-section { padding: 1.5rem; border-bottom: 1px solid var(--border); }
        .sidebar-section:last-child { border-bottom: none; }
        .meta-row { display: flex; justify-content: space-between; padding: 0.8rem 0; font-size: 0.9rem; }
        .meta-label { color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; }
        .meta-value { font-weight: 700; color: white; }

        .toast-inline { padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; }
        .toast-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: var(--tech-green); }
        .toast-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: var(--tunisian-red); }
    </style>
</head>
<body class="page-anim">

<nav>
    <div class="logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </div>
    <ul class="nav-links">
        <li><a href="home.php">Accueil</a></li>
        <li><a href="home.php" class="active">job</a></li>
        <li><a href="#">Marketplace</a></li>
        <li><a href="#">Freelancers</a></li>
        <li><a href="#">Blog</a></li>
    </ul>
    <div class="nav-right" style="gap: 1rem;">
        <a href="home.php" class="cart-btn" style="background:rgba(255,255,255,0.06); border-color:rgba(255,255,255,0.12); color:white;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>
</nav>

<section class="hero-banner" style="padding:4rem 4rem 3rem;">
    <div class="hero-glow"></div>
    <div class="hero-content" style="max-width:900px;">
        <div class="hero-tag"><i class="fa-solid fa-briefcase"></i> Mission Détail #<?= $offre['id'] ?></div>
        <h1 class="hero-title"><?= htmlspecialchars($offre['titre']) ?></h1>
        <div style="display:flex; align-items:center; gap:1.5rem; margin-top:1rem; flex-wrap:wrap;">
            <span style="color:var(--tech-blue); font-weight:700; font-size:1.2rem; font-family:'JetBrains Mono';">
                <i class="fa-solid fa-coins"></i> <?= number_format($offre['budget'], 0, ',', ' ') ?> DT
            </span>
            <span style="color:var(--text-muted); font-size:0.9rem; display:flex; align-items:center; gap:0.5rem;">
                <i class="fa-solid fa-calendar-check"></i> Expire dans : <?= htmlspecialchars($offre['delai']) ?>
            </span>
            <span style="color:var(--text-muted); font-size:0.9rem; display:flex; align-items:center; gap:0.5rem;">
                <i class="fa-solid fa-clock"></i> Publiée le <?= date('d/m/Y', strtotime($offre['date_creation'])) ?>
            </span>
        </div>
    </div>
</section>

<div class="detail-layout">

    <?php if (isset($_GET['success'])): ?>
    <div class="toast" id="action-toast" style="bottom:2rem; right:2rem; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.4); color:#10b981; padding:1rem 1.5rem; border-radius:var(--radius-md); font-weight:600; font-size:0.9rem; display:flex; align-items:center; gap:0.6rem; position:fixed; z-index:9999;">
        <i class="fa-solid fa-circle-check"></i> 
        <?= $_GET['success'] === 'contacted' ? 'Freelancer marqué comme contacté !' : 'Candidature refusée.' ?>
    </div>
    <script>setTimeout(() => { document.getElementById('action-toast').style.opacity='0'; }, 3000);</script>
    <?php endif; ?>

    <!-- CONTENU PRINCIPAL -->
    <div>

        <!-- 1. Description -->
        <div class="detail-card">
            <div class="detail-body">
                <div class="detail-section-title"><i class="fa-solid fa-align-left"></i> À propos de la mission</div>
                <p class="detail-text"><?= nl2br(htmlspecialchars($offre['description'])) ?></p>
            </div>
        </div>

        <!-- 2. Compétences -->
        <div class="detail-card">
            <div class="detail-body">
                <div class="detail-section-title"><i class="fa-solid fa-tags"></i> Compétences recherchées</div>
                <div style="margin-top:0.5rem;">
                    <?php foreach ($competences as $comp): ?>
                    <span class="skill-tag"> <?= htmlspecialchars($comp) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>


        <!-- 4. Liste des candidats -->
        <div class="candidates-list">
            <div class="detail-section-title" style="font-size:1rem; color:white; text-transform:none; margin-bottom:1.5rem;">
                <i class="fa-solid fa-users" style="color:var(--tech-blue);"></i> Participants (<?= count($candidats) ?>)
            </div>
            
            <?php if (empty($candidats)): ?>
                <div style="padding:2.5rem; text-align:center; background:rgba(255,255,255,0.02); border:1px dashed var(--border); border-radius:var(--radius-lg); color:var(--text-muted);">
                    Soyez le premier à postuler pour cette mission !
                </div>
            <?php else: ?>
                <?php foreach ($candidats as $can): 
                    $statutKey = $can['status'] ?? 'pending';
                    $appBadge = $appStatutConfig[$statutKey] ?? $appStatutConfig['pending'];
                ?>
                <div class="candidate-item" id="app-<?= $can['id'] ?>">
                    <div class="candidate-avatar"><?= strtoupper(substr($can['name'], 0, 1)) ?></div>
                    <div class="candidate-info">
                        <div class="candidate-name">
                            <?= htmlspecialchars($can['name']) ?>
                            <span class="candidate-date">• <?= date('d M Y', strtotime($can['created_at'])) ?></span>
                            <span style="font-size:0.65rem; padding:2px 8px; border-radius:10px; background:<?= $appBadge['bg'] ?>; color:<?= $appBadge['color'] ?>; margin-left:10px; font-weight:700; text-transform:uppercase;">
                                <?= $appBadge['label'] ?>
                            </span>
                        </div>
                        <div class="candidate-job"><?= htmlspecialchars($can['job_title']) ?></div>
                        
                        <div style="display:flex; align-items:center; justify-content:flex-end; margin-top:0.5rem;" class="no-print">
                            <div style="display:flex; gap:0.5rem;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="app_id" value="<?= $can['id'] ?>">
                                    <input type="hidden" name="action_app" value="contacted">
                                    <button type="submit" class="btn-search" style="padding:0.4rem 1rem; font-size:0.8rem; background:rgba(16,185,129,0.1); color:#10b981; border:1px solid rgba(16,185,129,0.3);">
                                        <i class="fa-solid fa-envelope"></i> Contacter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- SIDEBAR -->
    <aside>
        <div class="sidebar-card">
            <div class="sidebar-section">
                <div style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--tech-blue); letter-spacing:1px; margin-bottom:1rem;">Détails rapides</div>
                <div class="meta-row">
                    <span class="meta-label"><i class="fa-solid fa-coins"></i> Budget</span>
                    <span class="meta-value"><?= number_format($offre['budget'], 0, ',', ' ') ?> DT</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label"><i class="fa-solid fa-hourglass-half"></i> Délai</span>
                    <span class="meta-value"><?= htmlspecialchars($offre['delai']) ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label"><i class="fa-solid fa-calendar"></i> Créé le</span>
                    <span class="meta-value"><?= date('d/m/Y', strtotime($offre['date_creation'])) ?></span>
                </div>
            </div>
            <div class="sidebar-section">
                <div class="statut-pill" style="width:100%; justify-content:center; color:<?= $badge['color'] ?>; background:<?= $badge['bg'] ?>; border-color:<?= $badge['border'] ?>; padding:0.75rem;">
                    <i class="fa-solid <?= $badge['icon'] ?>"></i>
                    Statut : <?= $badge['label'] ?>
                </div>
            </div>
            <div class="sidebar-section">
                <button id="download-pdf" class="btn-search" style="width:100%; justify-content:center; gap:0.75rem; background:var(--tech-blue); border-color:rgba(59,130,246,0.3); color:white; padding:0.85rem; border-radius:var(--radius-md); font-weight:700;">
                    <i class="fa-solid fa-file-pdf"></i> Télécharger PDF (.pdf)
                </button>
            </div>
            <div class="sidebar-section" style="background:rgba(59,130,246,0.05);">
                <div style="font-size:0.85rem; color:var(--text-light); text-align:center; line-height:1.5;">
                    Besoin de modifier cette offre ?<br>
                    <a href="edit_job.php?id=<?= $offre['id'] ?>" style="color:var(--tech-blue); font-weight:700; text-decoration:underline; display:block; margin-top:0.5rem;">Éditer l'annonce</a>
                </div>
            </div>
        </div>
    </aside>

</div>

    <!-- PDF Logic -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnPdf = document.getElementById('download-pdf');
        if (btnPdf) {
            btnPdf.addEventListener('click', function() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                btnPdf.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Génération...';

                // 1. Infos Mission
                const infoHeaders = [["Caractéristique", "Détail"]];
                const infoRows = [
                    ["Titre", "<?= addslashes($offre['titre']) ?>"],
                    ["Budget", "<?= number_format($offre['budget'], 0, ',', ' ') ?> DT"],
                    ["Délai", "<?= addslashes($offre['delai']) ?>"],
                    ["Date de création", "<?= date('d/m/Y', strtotime($offre['date_creation'])) ?>"],
                    ["Statut", "<?= $badge['label'] ?>"]
                ];

                doc.autoTable({
                    head: infoHeaders,
                    body: infoRows,
                    startY: 10,
                    theme: 'grid',
                    headStyles: { fillColor: [0, 0, 0], textColor: [255, 255, 255] },
                    styles: { fontSize: 10, textColor: [0, 0, 0] }
                });

                // 2. Participants
                const partHeaders = [["Participant", "Role", "Date de candidature", "Statut"]];
                const partRows = [
                    <?php foreach ($candidats as $can): ?>
                    [
                        "<?= addslashes($can['name']) ?>",
                        "<?= addslashes($can['job_title']) ?>",
                        "<?= date('d/m/Y', strtotime($can['created_at'])) ?>",
                        "<?= $appStatutConfig[$can['status'] ?? 'pending']['label'] ?>"
                    ],
                    <?php endforeach; ?>
                ];

                if (partRows.length > 0) {
                    doc.text("Liste des Participants", 14, doc.lastAutoTable.finalY + 15);
                    doc.autoTable({
                        head: partHeaders,
                        body: partRows,
                        startY: doc.lastAutoTable.finalY + 20,
                        theme: 'striped',
                        headStyles: { fillColor: [0, 0, 0], textColor: [255, 255, 255] },
                        styles: { fontSize: 9, textColor: [0, 0, 0] }
                    });
                }

                // Download
                const pdfBlob = doc.output('blob');
                const url = URL.createObjectURL(pdfBlob);
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Offre_<?= $id ?>.pdf');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
                
                btnPdf.innerHTML = '<i class="fa-solid fa-file-pdf"></i> Télécharger PDF (.pdf)';
            });
        }
    });
    </script>

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
