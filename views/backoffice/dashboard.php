<?php
// views/backoffice/dashboard.php — Admin: Tableau de bord

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/JobOffer.php';

$model   = new JobOffer();
$success = $_GET['success'] ?? '';
$errors  = [];

// ── Actions CRUD rapides via GET ──────────────────────────────────────
if (!empty($_GET['action']) && !empty($_GET['id'])) {
    $actionId = (int)$_GET['id'];
    switch ($_GET['action']) {
        case 'approve':
            $model->updateStatut($actionId, 'approved');
            header('Location: dashboard.php?success=approved'); exit;
        case 'reject':
            $model->updateStatut($actionId, 'rejected');
            header('Location: dashboard.php?success=rejected'); exit;
        case 'delete':
            $model->delete($actionId);
            header('Location: dashboard.php?success=deleted'); exit;
    }
}

// ── Filtre et recherche ───────────────────────────────────────────────
$filtre      = $_GET['filtre']  ?? 'all';
$searchTitre = trim($_GET['titre'] ?? '');
$searchDate  = trim($_GET['date']  ?? '');

if (!empty($searchTitre) || !empty($searchDate)) {
    $offres = $model->search($searchTitre, $searchDate);
} elseif ($filtre !== 'all') {
    $offres = $model->getByStatut($filtre);
} else {
    $offres = $model->getAll();
}

// ── Export CSV admin ──────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $allOffres = $model->getAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="admin_offres_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['ID','Titre','Description','Compétences','Budget (DT)','Délai','Statut','Client ID','Date création'], ';');
    foreach ($allOffres as $o) {
        fputcsv($out, [$o['id'],$o['titre'],$o['description'],$o['competences'],$o['budget'],$o['delai'],$o['statut'],$o['client_id'],$o['date_creation']], ';');
    }
    fclose($out); exit;
}

// ── Stats ─────────────────────────────────────────────────────────────
$totalAll      = $model->countAll();
$totalPending  = $model->countByStatut('pending');
$totalApproved = $model->countByStatut('approved');
$totalRejected = $model->countByStatut('rejected');

function statutBadge($s) {
    $map = [
        'pending'  => ['label'=>'En attente','class'=>'statut-pending', 'icon'=>'fa-clock'],
        'approved' => ['label'=>'Approuvée', 'class'=>'statut-approved','icon'=>'fa-circle-check'],
        'rejected' => ['label'=>'Rejetée',   'class'=>'statut-rejected','icon'=>'fa-circle-xmark'],
    ];
    return $map[$s] ?? $map['pending'];
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — FreelaSkill Jobs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<?php if ($success): ?>
<div class="toast <?= $success === 'deleted' || $success === 'rejected' ? 'toast-error' : 'toast-success' ?>" id="admin-toast">
    <?php if ($success === 'approved'): ?><i class="fa-solid fa-circle-check"></i> Offre approuvée !
    <?php elseif ($success === 'rejected'): ?><i class="fa-solid fa-circle-xmark"></i> Offre rejetée.
    <?php elseif ($success === 'deleted'): ?><i class="fa-solid fa-trash"></i> Offre supprimée.
    <?php elseif ($success === 'added'): ?><i class="fa-solid fa-plus"></i> Offre ajoutée !
    <?php elseif ($success === 'updated'): ?><i class="fa-solid fa-floppy-disk"></i> Modifications sauvegardées !
    <?php endif; ?>
</div>
<script>setTimeout(()=>{const t=document.getElementById('admin-toast');if(t)t.style.opacity='0';},3500);</script>
<?php endif; ?>

<!-- ══ SIDEBAR ════════════════════════════════════════════════════ -->
<aside class="sidebar animate-fade-up">
    <div class="logo">
        <i class="fa-solid fa-briefcase"></i>
        Core<span>Panel</span>
        <small>Admin Jobs v1.0</small>
    </div>

    <div class="nav-section-title">Navigation</div>
    <a href="dashboard.php" class="nav-item active" id="nav-dashboard">
        <i class="fa-solid fa-gauge-high"></i> Tableau de bord
    </a>
    <a href="dashboard.php?filtre=pending" class="nav-item" id="nav-pending">
        <i class="fa-solid fa-hourglass-half"></i> En attente
        <?php if ($totalPending > 0): ?>
        <span style="margin-left:auto; background:rgba(245,158,11,0.2); color:#F59E0B; font-size:.65rem; padding:2px 7px; border-radius:var(--radius-full); font-weight:700;"><?= $totalPending ?></span>
        <?php endif; ?>
    </a>
    <a href="dashboard.php?filtre=approved" class="nav-item" id="nav-approved">
        <i class="fa-solid fa-circle-check"></i> Approuvées
    </a>
    <a href="dashboard.php?filtre=rejected" class="nav-item" id="nav-rejected">
        <i class="fa-solid fa-circle-xmark"></i> Rejetées
    </a>

    <div class="nav-section-title" style="margin-top:.5rem;">Actions</div>
    <a href="add_job_admin.php" class="nav-item" id="nav-add">
        <i class="fa-solid fa-plus"></i> Ajouter une offre
    </a>
    <a href="dashboard.php?export=csv" class="nav-item" id="nav-export">
        <i class="fa-solid fa-file-csv"></i> Exporter CSV
    </a>

    <div class="sidebar-footer">
        <a href="../frontoffice/home.php" class="nav-item" id="nav-frontoffice" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07);">
            <i class="fa-solid fa-globe"></i> Interface Client
        </a>
    </div>
</aside>

<!-- ══ MAIN PANEL ════════════════════════════════════════════════ -->
<main class="main-panel">
    <div style="position:absolute; top:-100px; right:-100px; width:500px; height:500px; background:radial-gradient(circle,rgba(59,130,246,0.08),transparent 60%); pointer-events:none;"></div>

    <!-- TOPBAR -->
    <div class="topbar animate-fade-up">
        <div>
            <h1 class="topbar-title">Gestion des <span>Offres Job</span></h1>
            <p style="color:var(--text-muted); font-size:.82rem; margin-top:.2rem;">Modération, validation et gestion complète des offres freelance</p>
        </div>
        <div class="topbar-right">
            <a href="dashboard.php?export=csv" id="topbar-export" class="btn btn-export">
                <i class="fa-solid fa-file-csv"></i> Exporter CSV
            </a>
            <a href="add_job_admin.php" id="topbar-add" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Ajouter offre
            </a>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="metric-grid animate-fade-up delay-1">
        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(59,130,246,0.1); color:var(--tech-blue);"><i class="fa-solid fa-briefcase"></i></div>
            <div class="metric-label">Total offres</div>
            <div class="metric-value"><?= $totalAll ?></div>
            <div class="metric-sub"><i class="fa-solid fa-database"></i> Toutes les offres</div>
        </div>
        <div class="metric-card" style="border-color:rgba(245,158,11,0.2);">
            <div class="metric-icon" style="background:rgba(245,158,11,0.1); color:#F59E0B;"><i class="fa-solid fa-hourglass-half"></i></div>
            <div class="metric-label">En attente</div>
            <div class="metric-value" style="color:#F59E0B;"><?= $totalPending ?></div>
            <div class="metric-sub"><i class="fa-solid fa-triangle-exclamation" style="color:#F59E0B;"></i> Requiert attention</div>
        </div>
        <div class="metric-card" style="border-color:rgba(16,185,129,0.2);">
            <div class="metric-icon" style="background:rgba(16,185,129,0.1); color:var(--tech-green);"><i class="fa-solid fa-circle-check"></i></div>
            <div class="metric-label">Approuvées</div>
            <div class="metric-value" style="color:var(--tech-green);"><?= $totalApproved ?></div>
            <div class="metric-sub trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Visibles freelancers</div>
        </div>
        <div class="metric-card" style="border-color:rgba(239,68,68,0.2);">
            <div class="metric-icon" style="background:rgba(239,68,68,0.1); color:var(--tunisian-red);"><i class="fa-solid fa-circle-xmark"></i></div>
            <div class="metric-label">Rejetées</div>
            <div class="metric-value" style="color:var(--tunisian-red);"><?= $totalRejected ?></div>
            <div class="metric-sub"><i class="fa-solid fa-ban"></i> Non publiées</div>
        </div>
    </div>

    <!-- TABLE SECTION -->
    <div class="admin-card animate-fade-up delay-2">

        <div class="admin-card-header">
            <div>
                <div class="admin-card-title">Liste des offres d'emploi</div>
                <div class="admin-card-sub">
                    <?= count($offres) ?> offre<?= count($offres) > 1 ? 's' : '' ?> affichée<?= count($offres) > 1 ? 's' : '' ?>
                </div>
            </div>

            <!-- Actions header -->
            <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
                <!-- Search -->
                <form method="GET" action="dashboard.php" style="display:flex; gap:.5rem; align-items:center;">
                    <input type="hidden" name="filtre" value="<?= htmlspecialchars($filtre) ?>">
                    <div class="admin-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="titre" id="admin-search-titre" placeholder="Rechercher par titre…" value="<?= htmlspecialchars($searchTitre) ?>">
                    </div>
                    <div class="admin-search" style="width:auto;">
                        <i class="fa-solid fa-calendar"></i>
                        <input type="date" name="date" id="admin-search-date" value="<?= htmlspecialchars($searchDate) ?>" style="color:<?= empty($searchDate)?'#334155':'white' ?>;">
                    </div>
                    <button type="submit" class="btn btn-outline" id="admin-search-btn"><i class="fa-solid fa-search"></i></button>
                    <?php if (!empty($searchTitre)||!empty($searchDate)): ?>
                    <a href="dashboard.php?filtre=<?= $filtre ?>" class="btn btn-outline"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- FILTER TABS -->
        <div style="padding:1rem 2rem; border-bottom:1px solid rgba(255,255,255,0.05);">
            <div class="filter-tabs">
                <a href="dashboard.php?filtre=all" class="filter-tab <?= $filtre==='all'?'active':'' ?>" id="tab-all">
                    <i class="fa-solid fa-list"></i> Toutes <span class="tab-count"><?= $totalAll ?></span>
                </a>
                <a href="dashboard.php?filtre=pending" class="filter-tab <?= $filtre==='pending'?'active':'' ?>" id="tab-pending">
                    <i class="fa-solid fa-clock"></i> En attente <span class="tab-count"><?= $totalPending ?></span>
                </a>
                <a href="dashboard.php?filtre=approved" class="filter-tab <?= $filtre==='approved'?'active':'' ?>" id="tab-approved">
                    <i class="fa-solid fa-check"></i> Approuvées <span class="tab-count"><?= $totalApproved ?></span>
                </a>
                <a href="dashboard.php?filtre=rejected" class="filter-tab <?= $filtre==='rejected'?'active':'' ?>" id="tab-rejected">
                    <i class="fa-solid fa-xmark"></i> Rejetées <span class="tab-count"><?= $totalRejected ?></span>
                </a>
            </div>
        </div>

        <!-- TABLE -->
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Compétences</th>
                        <th>Budget</th>
                        <th>Délai</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($offres)): ?>
                    <tr class="empty-row">
                        <td colspan="8">
                            <i class="fa-solid fa-folder-open"></i>
                            Aucune offre trouvée
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($offres as $offre):
                        $badge = statutBadge($offre['statut']);
                        $comps = array_slice(array_map('trim', explode(',', $offre['competences'])), 0, 2);
                    ?>
                    <tr>
                        <td style="font-family:'JetBrains Mono',monospace; color:var(--text-muted); font-size:.78rem;">#<?= $offre['id'] ?></td>
                        <td>
                            <div class="td-title"><?= htmlspecialchars($offre['titre']) ?></div>
                            <div class="td-desc"><?= htmlspecialchars(mb_strimwidth($offre['description'], 0, 60, '…')) ?></div>
                        </td>
                        <td>
                            <?php foreach ($comps as $c): ?>
                            <span style="display:inline-block; background:rgba(59,130,246,0.08); border:1px solid rgba(59,130,246,0.18); color:var(--tech-blue); padding:1px 7px; border-radius:var(--radius-full); font-size:.68rem; margin:1px;">
                                <?= htmlspecialchars($c) ?>
                            </span>
                            <?php endforeach; ?>
                        </td>
                        <td style="font-family:'JetBrains Mono',monospace; font-weight:700; color:white;">
                            <?= number_format($offre['budget'],0,',',' ') ?> <span style="font-size:.7rem; color:var(--text-muted); font-family:inherit;">DT</span>
                        </td>
                        <td style="color:var(--text-muted);"><?= htmlspecialchars($offre['delai']) ?></td>
                        <td>
                            <span class="statut-badge <?= $badge['class'] ?>">
                                <i class="fa-solid <?= $badge['icon'] ?>" style="font-size:.6rem;"></i>
                                <?= $badge['label'] ?>
                            </span>
                        </td>
                        <td style="font-size:.78rem; color:var(--text-muted);"><?= date('d/m/Y', strtotime($offre['date_creation'])) ?></td>
                        <td>
                            <div class="action-group">
                                <!-- Voir détail -->
                                <a href="detail_job_admin.php?id=<?= $offre['id'] ?>" class="btn btn-outline" title="Voir détail" id="view-admin-<?= $offre['id'] ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <!-- Modifier -->
                                <a href="edit_job_admin.php?id=<?= $offre['id'] ?>" class="btn btn-edit" title="Modifier" id="edit-admin-<?= $offre['id'] ?>">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <?php if ($offre['statut'] === 'pending'): ?>
                                <!-- Approuver -->
                                <a href="dashboard.php?action=approve&id=<?= $offre['id'] ?>" class="btn btn-approve" title="Approuver" id="approve-<?= $offre['id'] ?>">
                                    <i class="fa-solid fa-check"></i>
                                </a>
                                <!-- Rejeter -->
                                <a href="dashboard.php?action=reject&id=<?= $offre['id'] ?>" class="btn btn-reject" title="Rejeter" id="reject-<?= $offre['id'] ?>">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                                <?php elseif ($offre['statut'] === 'approved'): ?>
                                <a href="dashboard.php?action=reject&id=<?= $offre['id'] ?>" class="btn btn-reject" title="Désapprouver" id="disapprove-<?= $offre['id'] ?>">
                                    <i class="fa-solid fa-ban"></i>
                                </a>
                                <?php elseif ($offre['statut'] === 'rejected'): ?>
                                <a href="dashboard.php?action=approve&id=<?= $offre['id'] ?>" class="btn btn-approve" title="Ré-approuver" id="reapprove-<?= $offre['id'] ?>">
                                    <i class="fa-solid fa-check"></i>
                                </a>
                                <?php endif; ?>
                                <!-- Supprimer -->
                                <a href="dashboard.php?action=delete&id=<?= $offre['id'] ?>"
                                   class="btn btn-danger js-admin-delete"
                                   title="Supprimer"
                                   id="delete-admin-<?= $offre['id'] ?>"
                                   data-title="<?= htmlspecialchars($offre['titre']) ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- fin admin-card -->

</main>

<script>
// Confirmation suppression
document.querySelectorAll('.js-admin-delete').forEach(btn => {
    btn.addEventListener('click', e => {
        const titre = btn.dataset.title || 'cette offre';
        if (!confirm(`⚠️ Supprimer l'offre "${titre}" ?\n\nCette action est irréversible.`)) {
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>
