<?php
// views/backoffice/dashboard.php — Admin: Tableau de bord synchronisé
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
    <title>Administration | FreelaSkill</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <!-- Theme CSS -->
    <link rel="stylesheet" href="admin.css?v=<?= time() ?>">
    <!-- PDF Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
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
                
                <a href="dashboard.php" class="admin-nav-item <?= empty($_GET['filtre']) || $_GET['filtre']==='all' ? 'active' : '' ?>">
                    <i class="fa-solid fa-briefcase"></i> Gestion des Missions
                </a>

                <div style="margin: 1.5rem 0 0.5rem 1rem; font-size: 0.7rem; text-transform: uppercase; color: #475569; font-weight: 700; letter-spacing: 1px;">Actions</div>
                
                <a href="add_job_admin.php" class="admin-nav-item">
                    <i class="fa-solid fa-plus-circle"></i> Ajouter une Offre
                </a>
                
                <button onclick="exportToPDF()" class="admin-nav-item" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left;">
                    <i class="fa-solid fa-file-pdf"></i> Télécharger PDF
                </button>

            </nav>
            
            <div style="padding: 1.5rem; border-top: 1px solid var(--border);">
                <a href="#" class="admin-nav-item" style="color: var(--tunisian-red);">
                    <i class="fa-solid fa-power-off"></i> Déconnexion
                </a>
            </div>
        </aside>

        <!-- MAIN AREA -->
        <main class="admin-main">
            
            <!-- TOPBAR -->
            <header class="admin-topbar">
                <form class="admin-search" method="GET">
                    <input type="hidden" name="filtre" value="<?= htmlspecialchars($filtre) ?>">
                    <i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted);"></i>
                    <input type="text" name="titre" placeholder="Rechercher par titre..." value="<?= htmlspecialchars($searchTitre) ?>">
                    <input type="text" name="date" placeholder="Date (AAAA-MM-JJ)" value="<?= htmlspecialchars($searchDate) ?>" style="width: 160px; color: var(--text-muted); cursor: pointer;">
                    <button type="submit" style="display:none;"></button>
                </form>
                
                <div class="admin-top-actions">
                    <div class="admin-icon-btn">
                        <i class="fa-regular fa-bell"></i>
                    </div>
                    <div class="nav-avatar">AH</div>
                </div>
            </header>

            <!-- CONTENT -->
            <div class="admin-content">
                
                <div class="admin-header-row">
                    <div>
                        <h1 class="admin-page-title">Gestion des <span>Missions</span></h1>
                        <p style="color: var(--text-muted); margin-top: 0.5rem;">
                            Administrez les offres d'emploi et modérez les publications en temps réel.
                        </p>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <a href="add_job_admin.php" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Nouvelle Mission
                        </a>
                    </div>
                </div>

                <!-- QUICK FILTER TABS -->
                <div style="display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                    <a href="dashboard.php?filtre=all" class="btn <?= $filtre==='all'?'btn-primary':'btn-outline' ?>" style="border-radius: 8px; font-size: 0.8rem;">
                        Tous
                    </a>
                    <a href="dashboard.php?filtre=pending" class="btn <?= $filtre==='pending'?'btn-primary':'btn-outline' ?>" style="border-radius: 8px; font-size: 0.8rem; display: flex; gap: 8px;">
                        Modération 
                        <span style="background: rgba(0,0,0,0.2); padding: 1px 6px; border-radius: 4px;"><?= $totalPending ?></span>
                    </a>
                    <a href="dashboard.php?filtre=approved" class="btn <?= $filtre==='approved'?'btn-primary':'btn-outline' ?>" style="border-radius: 8px; font-size: 0.8rem;">
                        Approuvées
                    </a>
                    <a href="dashboard.php?filtre=rejected" class="btn <?= $filtre==='rejected'?'btn-primary':'btn-outline' ?>" style="border-radius: 8px; font-size: 0.8rem;">
                        Rejetées
                    </a>
                </div>

                <!-- METRICS -->
                <div class="admin-grid-4">
                    <div class="glass-card flex-col">
                        <div class="stat-card-header">
                            <span>Total Missions</span>
                            <div class="stat-card-icon"><i class="fa-solid fa-briefcase"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $totalAll ?></div>
                        <div class="stat-card-trend trend-up">
                            <i class="fa-solid fa-database"></i> Base de données active
                        </div>
                    </div>

                    <div class="glass-card flex-col">
                        <div class="stat-card-header">
                            <span>En attente</span>
                            <div class="stat-card-icon" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1);"><i class="fa-solid fa-clock"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $totalPending ?></div>
                        <div class="stat-card-trend trend-down">
                            Requiert votre attention
                        </div>
                    </div>

                    <div class="glass-card flex-col">
                        <div class="stat-card-header">
                            <span>Approuvées</span>
                            <div class="stat-card-icon" style="color: var(--tech-green); background: rgba(16, 185, 129, 0.1);"><i class="fa-solid fa-check-double"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $totalApproved ?></div>
                        <div class="stat-card-trend trend-up">
                            Visibles par les freelancers
                        </div>
                    </div>

                    <div class="glass-card flex-col">
                        <div class="stat-card-header">
                            <span>Rejetées</span>
                            <div class="stat-card-icon" style="color: var(--tunisian-red); background: rgba(239, 68, 68, 0.1);"><i class="fa-solid fa-ban"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $totalRejected ?></div>
                        <div class="stat-card-trend">
                            Archive des refus
                        </div>
                    </div>
                </div>

                <!-- TABLE CARD -->
                <div class="glass-card" style="padding: 0; overflow: hidden;">
                    <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 1.1rem; color: white; display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-list-ul" style="color: var(--tech-blue);"></i>
                            Registre des Missions
                        </h3>
                        <span style="font-size: 0.8rem; color: var(--text-muted);"><?= count($offres) ?> items trouvés</span>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="data-table" id="jobs-table">
                            <thead>
                                <tr>
                                    <th>Mission / Description</th>
                                    <th>Budget</th>
                                    <th>Délai</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($offres)): ?>
                                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 4rem;">Aucune donnée disponible.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($offres as $o): 
                                        $badge = statutBadge($o['statut']);
                                    ?>
                                    <tr>
                                        <td style="max-width: 300px;">
                                            <div style="font-weight: 700; color: white; margin-bottom: 4px;"><?= htmlspecialchars($o['titre']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($o['description']) ?></div>
                                        </td>
                                        <td>
                                            <div style="font-family: 'JetBrains Mono'; font-weight: 700; color: var(--tech-blue);"><?= number_format($o['budget'], 0, ',', ' ') ?> DT</div>
                                        </td>
                                        <td style="font-size: 0.85rem;"><?= htmlspecialchars($o['delai']) ?></td>
                                        <td>
                                            <span class="statut-badge <?= $badge['class'] ?>">
                                                <i class="fa-solid <?= $badge['icon'] ?>"></i> <?= $badge['label'] ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('d/m/Y', strtotime($o['date_creation'])) ?></td>
                                        <td style="text-align: right;">
                                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                                <a href="detail_job_admin.php?id=<?= $o['id'] ?>" class="btn btn-outline" style="padding: 0.4rem 0.6rem;" title="Détails"><i class="fa-solid fa-eye"></i></a>
                                                
                                                <?php if ($o['statut'] === 'pending'): ?>
                                                    <a href="?action=approve&id=<?= $o['id'] ?>" class="btn btn-approve" style="padding: 0.4rem 0.6rem;" title="Approuver"><i class="fa-solid fa-check"></i></a>
                                                    <a href="?action=reject&id=<?= $o['id'] ?>" class="btn btn-reject" style="padding: 0.4rem 0.6rem;" title="Rejeter"><i class="fa-solid fa-x"></i></a>
                                                <?php else: ?>
                                                    <a href="edit_job_admin.php?id=<?= $o['id'] ?>" class="btn btn-edit" style="padding: 0.4rem 0.6rem;" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                                                    <a href="?action=delete&id=<?= $o['id'] ?>" class="btn btn-danger" style="padding: 0.4rem 0.6rem;" onclick="return confirm('Confirmer la suppression ?')" title="Supprimer"><i class="fa-solid fa-trash"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- TOASTS -->
    <?php if ($success): ?>
    <div class="toast <?= ($success === 'deleted' || $success === 'rejected') ? 'toast-error' : 'toast-success' ?>" id="toast">
        <i class="fa-solid <?= ($success === 'deleted' || $success === 'rejected') ? 'fa-triangle-exclamation' : 'fa-circle-check' ?>"></i>
        Action "<?= ucfirst($success) ?>" réussie !
    </div>
    <script>setTimeout(()=>{const t=document.getElementById('toast'); if(t) t.style.opacity='0';}, 3500);</script>
    <?php endif; ?>

    <!-- DATA FOR EXPORT -->
    <?php $allOffres = $model->getAll(); ?>
    <script>
    const allOffresData = <?= json_encode($allOffres) ?>;

    async function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        const headers = [["ID", "Mission", "Budget", "Délai", "Statut", "Création"]];
        const rows = allOffresData.map(o => [
            o.id,
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
            styles: { fontSize: 8, cellPadding: 3, textColor: [0, 0, 0] },
            alternateRowStyles: { fillColor: [245, 245, 245] }
        });

        // Download Robuste avec FileSaver.js
        const blob = doc.output('blob');
        saveAs(blob, 'admin_export_missions.pdf');
    }
    </script>

</body>
</html>
