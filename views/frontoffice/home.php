<?php
// views/frontoffice/home.php — Client: liste des offres d'emploi

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/JobOffer.php';

define('BASE_URL', '/projet22/');

$model      = new JobOffer();
$q          = trim($_GET['q'] ?? '');
$success    = $_GET['success']    ?? '';

// ── Export logic moved to client-side (window.print)

// Recherche ou liste complète
if (!empty($q)) {
    $offres = $model->searchUnified($q);
} else {
    $offres = $model->getAll();
}

$totalCount = count($offres);

// Supprimer directement depuis la liste
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $model->delete((int)$_GET['id']);
    header('Location: home.php?success=deleted');
    exit;
}

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
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Offres d'Emploi — FreelaSkill</title>
    <meta name="description" content="Gérez vos offres d'emploi sur la plateforme FreelaSkill. Publiez, modifiez et suivez vos offres.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.25rem;
        }
        .job-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: var(--transition);
            position: relative;
            animation: fadeUp 0.5s ease forwards;
            opacity: 0;
            backdrop-filter: blur(10px);
        }
        .job-card:hover {
            border-color: var(--border-hover);
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.5), var(--neon-blue);
        }
        .job-card-header {
            padding: 1.5rem 1.5rem 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .job-icon {
            width: 48px; height: 48px;
            background: rgba(59,130,246,0.12);
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .job-statut-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.72rem; font-weight: 600;
            letter-spacing: 0.5px;
            border: 1px solid;
        }
        .job-card-body { padding: 1rem 1.5rem; }
        .job-titre {
            font-size: 1rem; font-weight: 700; color: white;
            margin-bottom: 0.5rem; line-height: 1.35;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .job-competences {
            font-size: 0.8rem; color: var(--tech-blue);
            margin-bottom: 0.85rem;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .job-meta {
            display: flex; align-items: center; gap: 1rem;
            font-size: 0.82rem; color: var(--text-muted);
            border-top: 1px solid var(--border);
            padding-top: 0.85rem; margin-top: 0.5rem;
        }
        .job-meta span { display: flex; align-items: center; gap: 0.35rem; }
        .job-budget {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.15rem; font-weight: 700; color: white;
        }
        .job-actions {
            display: flex; gap: 0.5rem;
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--border);
            margin-top: 0.75rem;
        }
        .btn-action {
            flex: 1;
            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
            padding: 0.6rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem; font-weight: 600;
            transition: var(--transition); cursor: pointer; border: 1px solid;
            font-family: 'Space Grotesk', sans-serif;
            text-decoration: none;
        }
        .btn-view   { background: rgba(59,130,246,0.08); color: var(--tech-blue); border-color: rgba(59,130,246,0.2); }
        .btn-edit   { background: rgba(245,158,11,0.08); color: #F59E0B; border-color: rgba(245,158,11,0.2); }
        .btn-delete { background: rgba(239,68,68,0.08); color: var(--tunisian-red); border-color: rgba(239,68,68,0.2); }
        .btn-view:hover   { background: var(--tech-blue); color: white; }
        .btn-edit:hover   { background: #F59E0B; color: white; }
        .btn-delete:hover { background: var(--tunisian-red); color: white; }
        .toast {
            position: fixed; bottom: 2rem; right: 2rem;
            background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4);
            color: #10b981; padding: 1rem 1.5rem; border-radius: var(--radius-md);
            font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 0.6rem;
            z-index: 9999; animation: fadeUp 0.4s ease;
        }
        .toast.error { background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.4); color: #ef4444; }
        .empty-state {
            grid-column: 1 / -1;
            text-align: center; padding: 4rem 2rem;
            border: 2px dashed var(--border); border-radius: var(--radius-lg);
            color: var(--text-muted);
        }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; color: #1e293b; }
        .job-card:nth-child(1){animation-delay:.05s}
        .job-card:nth-child(2){animation-delay:.1s}
        .job-card:nth-child(3){animation-delay:.15s}
        .job-card:nth-child(4){animation-delay:.2s}
        .job-card:nth-child(5){animation-delay:.25s}
        .job-card:nth-child(6){animation-delay:.3s}
    </style>
</head>
<body class="page-anim home-page">

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

<!-- NAVBAR -->
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
    <div class="nav-right">
        <div class="nav-avatar">CL</div>
    </div>
</nav>

<!-- HERO BANNER -->
<section class="hero-banner" style="padding: 2.5rem 1rem; min-height: auto;">
    <div class="hero-glow"></div>
    <div class="hero-content" style="max-width: 900px; margin: 0 auto; text-align: center;">
        
        <h1 class="hero-title">Trouvez vos prochaines <span>missions</span> en un clic</h1>
        <p class="hero-sub">Explorez les meilleures offres d'emploi freelance ou publiez vos propres besoins pour attirer des talents qualifiés.</p>

        <!-- SEARCH FORM -->
        <div style="display:flex; justify-content:center; width:100%; margin-top:2.5rem;">
            <form method="GET" action="home.php" style="width:100%; max-width:700px;">
                <div class="search-container" style="background: rgba(255,255,255,0.05); padding: 8px; border-radius: var(--radius-full); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); display: flex; gap: 10px;">
                    <div class="search-wrap" style="flex:1;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="q" id="search-q" value="<?= htmlspecialchars($q) ?>" placeholder="Rechercher par titre ou date (AAAA-MM-JJ)..." style="width:100%;">
                    </div>
                    <button type="submit" class="btn-search" id="btn-search">
                        <i class="fa-solid fa-magnifying-glass"></i> Rechercher
                    </button>
                    <?php if (!empty($q)): ?>
                    <a href="home.php" class="btn-search" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); width: 50px; padding: 0; justify-content: center;">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    
</section>

<!-- PAGE BODY -->
<div class="page-body" style="display: block; max-width: 1100px; margin: 0 auto; padding: 2rem 1rem;">

    <!-- HORIZONTAL TABS FILTERS -->
    <div style="display: flex; align-items: center; justify-content: flex-end; margin-bottom: 2.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
        <div style="display: flex; gap: 0.75rem;">
            <a href="add_job.php" style="display: flex; align-items: center; gap: 6px; background: var(--tech-blue); color: white; padding: 10px 20px; border-radius: 12px; font-size: 0.9rem; font-weight: 700; text-decoration: none; transition: var(--transition); box-shadow: var(--neon-blue);">
                <i class="fa-solid fa-plus"></i> Nouvelle offre
            </a>
            <button id="download-pdf-home" style="display: flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: white; padding: 10px 20px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; text-decoration: none; cursor:pointer;">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </button>
        </div>
    </div>

    <!-- OFFRES AREA -->
    <div class="products-area">

        <!-- Toolbar -->
        <div class="products-toolbar">
            <p class="result-count">
                <strong><?= $totalCount ?> offre<?= $totalCount > 1 ? 's' : '' ?></strong>
                <?= !empty($q) ? 'trouvée(s)' : 'au total' ?>
            </p>
            <div class="toolbar-right">
                <?php if (!empty($q)): ?>
                <div class="chip">
                    Résultats pour "<?= htmlspecialchars($q) ?>" <button onclick="window.location='home.php'">✕</button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- GRID -->
        <div class="job-grid">
            <?php if (empty($offres)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-folder-open"></i>
                <h3 style="color:white; margin-bottom:.5rem;">Aucune offre trouvée</h3>
                <p style="margin-bottom:1.5rem;">Publiez votre première offre via le bouton en haut de la page pour attirer les meilleurs freelancers.</p>
            </div>
            <?php else: ?>
            <?php foreach ($offres as $offre): 
                $badge = statutBadge($offre['statut']);
                $competencesList = array_slice(explode(',', $offre['competences']), 0, 3);
            ?>
            <div class="job-card">
                <div class="job-card-header">
                    <div class="job-icon">💼</div>
                    <div class="job-badge" style="background:rgba(59,130,246,0.1); color:var(--tech-blue); padding:4px 10px; border-radius:var(--radius-full); font-size:0.65rem; font-weight:700; text-transform:uppercase;">Job Offer</div>
                </div>
                <div class="job-card-body">
                    <div class="job-titre"><?= htmlspecialchars($offre['titre']) ?></div>
                    <div class="job-competences">
                        <?php foreach ($competencesList as $comp): ?>
                        <span style="display:inline-block; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.2); color:var(--tech-blue); padding:2px 8px; border-radius:var(--radius-full); font-size:.7rem; margin-right:4px;"><?= htmlspecialchars(trim($comp)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="job-meta">
                        <span><i class="fa-solid fa-coins"></i> <span class="job-budget"><?= number_format($offre['budget'], 0, ',', ' ') ?></span> <small style="color:#475569;">DT</small></span>
                        <span><i class="fa-solid fa-clock"></i> <?= htmlspecialchars($offre['delai']) ?></span>
                        <span><i class="fa-solid fa-calendar-days"></i> <?= date('d/m/Y', strtotime($offre['date_creation'])) ?></span>
                    </div>
                </div>
                <div class="job-actions">
                    <a href="detail_job.php?id=<?= $offre['id'] ?>" class="btn-action btn-view" id="view-<?= $offre['id'] ?>">
                        <i class="fa-solid fa-eye"></i> Voir
                    </a>
                    <a href="edit_job.php?id=<?= $offre['id'] ?>" class="btn-action btn-edit" id="edit-<?= $offre['id'] ?>">
                        <i class="fa-solid fa-pen"></i> Modifier
                    </a>
                    <a href="home.php?action=delete&id=<?= $offre['id'] ?>" 
                       class="btn-action btn-delete js-delete" 
                       id="delete-<?= $offre['id'] ?>"
                       data-title="<?= htmlspecialchars($offre['titre']) ?>">
                        <i class="fa-solid fa-trash"></i> Supprimer
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- CUSTOM MODAL -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal-card">
        <div class="modal-title">
            <i class="fa-solid fa-circle-exclamation" style="color:var(--tunisian-red);"></i>
            Confirmation
        </div>
        <p class="modal-text" id="modal-desc">Voulez-vous vraiment supprimer cette offre ? Cette action est irréversible.</p>
        <div class="modal-actions">
            <button class="btn-modal btn-modal-cancel" id="confirm-cancel">Annuler</button>
            <button class="btn-modal btn-modal-confirm" id="confirm-ok">Supprimer</button>
        </div>
    </div>
</div>

<script>
// Confirmation suppression personnalisée
const deleteModal = document.getElementById('delete-modal');
const confirmOk = document.getElementById('confirm-ok');
const confirmCancel = document.getElementById('confirm-cancel');
const modalDesc = document.getElementById('modal-desc');
let deleteUrl = '';

document.querySelectorAll('.js-delete').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        const titre = btn.dataset.title || 'cette offre';
        deleteUrl = btn.href;
        modalDesc.innerHTML = `Voulez-vous vraiment supprimer l'offre <strong style="color:white;">"${titre}"</strong> ? Cette action est irréversible.`;
        deleteModal.classList.add('active');
    });
});

confirmCancel.addEventListener('click', () => {
    deleteModal.classList.remove('active');
});

confirmOk.addEventListener('click', () => {
    if (deleteUrl) window.location.href = deleteUrl;
});

// DATA FOR FULL EXPORT
<?php $allOffres = $model->getAll(); ?>
const allJobsData = <?= json_encode($allOffres) ?>;

// PDF Direct Download (Professional Table Mode)
document.addEventListener('DOMContentLoaded', function() {
    const btnPdf = document.getElementById('download-pdf-home');
    if (btnPdf) {
        btnPdf.addEventListener('click', async function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            btnPdf.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Génération...';

            const headers = [["Mission", "Budget", "Délai", "Statut", "Date"]];
            const rows = allJobsData.map(o => [
                o.titre,
                o.budget + " DT",
                o.delai,
                o.statut === 'approved' ? 'Approuvée' : (o.statut === 'rejected' ? 'Rejetée' : 'En attente'),
                o.date_creation
            ]);

            doc.autoTable({
                head: headers,
                body: rows,
                startY: 10,
                theme: 'grid',
                headStyles: { fillColor: [0, 0, 0], textColor: [255, 255, 255] },
                styles: { fontSize: 8, textColor: [0, 0, 0] },
                alternateRowStyles: { fillColor: [245, 245, 245] }
            });

            const pdfBlob = doc.output('blob');
            const url = URL.createObjectURL(pdfBlob);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'Mes_Offres_FreelaSkill.pdf');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            btnPdf.innerHTML = '<i class="fa-solid fa-file-pdf"></i> Export PDF';
        });
    }
});

// Fermer modal en cliquant sur l'overlay
deleteModal.addEventListener('click', (e) => {
    if (e.target === deleteModal) deleteModal.classList.remove('active');
});

// Filter options click
document.querySelectorAll('.filter-option').forEach(opt => {
    opt.addEventListener('click', () => {
        document.querySelectorAll('.filter-option').forEach(o => o.classList.remove('active'));
        opt.classList.add('active');
    });
});
</script>

</body>
</html>
