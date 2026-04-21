<?php
// views/backoffice/dashboard.view.php — Template: Tableau de bord synchronisé

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="admin.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
</head>
<body class="page-anim">
    
    <div class="hero-glow"></div>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
            <nav class="admin-nav">
                <a href="dashboard.php" class="admin-nav-item active"><i class="fa-solid fa-briefcase"></i> Missions</a>
                <a href="add_job_admin.php" class="admin-nav-item"><i class="fa-solid fa-plus-circle"></i> Ajouter</a>
                <button onclick="exportToPDF()" class="admin-nav-item" style="width:100%; border:none; background:none; cursor:pointer; text-align:left;"><i class="fa-solid fa-file-pdf"></i> PDF</button>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <form class="admin-search" method="GET" novalidate>
                    <input type="hidden" name="filtre" value="<?= htmlspecialchars($filtre ?? 'all') ?>">
                    <input type="text" name="titre" placeholder="Titre..." value="<?= htmlspecialchars($searchTitre ?? '') ?>">
                    <input type="text" name="date" placeholder="Date..." value="<?= htmlspecialchars($searchDate ?? '') ?>">
                    <button type="submit" style="display:none;"></button>
                </form>
            </header>

            <div class="admin-content">
                <h1 class="admin-page-title">Gestion des <span>Missions</span></h1>

                <div class="admin-grid-4">
                    <div class="glass-card"><div>Total</div><div class="stat-card-value"><?= $totalAll ?></div></div>
                    <div class="glass-card"><div>En attente</div><div class="stat-card-value"><?= $totalPending ?></div></div>
                    <div class="glass-card"><div>Approuvées</div><div class="stat-card-value"><?= $totalApproved ?></div></div>
                    <div class="glass-card"><div>Rejetées</div><div class="stat-card-value"><?= $totalRejected ?></div></div>
                </div>

                <div class="glass-card" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Mission</th><th>Budget</th><th>Statut</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($offres as $o): $badge = statutBadge($o->getStatut()); ?>
                            <tr>
                                <td><?= htmlspecialchars($o->getTitre()) ?></td>
                                <td><?= number_format($o->getBudget(), 0, ',', ' ') ?> DT</td>
                                <td><span class="statut-badge <?= $badge['class'] ?>"><?= $badge['label'] ?></span></td>
                                <td>
                                    <a href="detail_job_admin.php?id=<?= $o->getId() ?>" class="btn">Voir</a>
                                    <?php if ($o->getStatut() === 'pending'): ?>
                                        <a href="?action=approve&id=<?= $o->getId() ?>" class="btn">Approuver</a>
                                    <?php else: ?>
                                        <a href="edit_job_admin.php?id=<?= $o->getId() ?>" class="btn">Edit</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const rows = <?= json_encode(array_map(fn($o) => [$o->getId(), $o->getTitre(), $o->getBudget() . " DT", $o->getStatut()], $offres)) ?>;
        doc.autoTable({ head: [["ID", "Mission", "Budget", "Statut"]], body: rows });
        saveAs(doc.output('blob'), 'admin_export_missions.pdf');
    }
    </script>
</body>
</html>
