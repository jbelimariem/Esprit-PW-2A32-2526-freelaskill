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
    <link rel="stylesheet" href="../assets/theme-light.css?v=<?= time() ?>">
    <script src="../assets/theme.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
</head>
<body class="page-anim">
    
    <div class="hero-glow"></div>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="logo-container">
                <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
                <div class="admin-version">Admin Control v1.0</div>
            </div>
            <nav class="admin-nav">
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-users-viewfinder"></i> Gestion Users</a>
                <a href="dashboard.php" class="admin-nav-item active"><i class="fa-solid fa-sitemap"></i> Flux de Missions</a>
                <a href="admin_freelancers.php" class="admin-nav-item"><i class="fa-solid fa-user-tie"></i> Freelancers</a>
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-store"></i> Marketplace <i class="fa-solid fa-chevron-right arrow"></i></a>
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-shield-halved"></i> Securite</a>
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-comment-dots"></i> Messagerie</a>
            </nav>
            <div class="admin-sidebar-user">
                <div class="avatar">A</div>
                <div class="info">
                    <div class="name">Admin</div>
                    <div class="role">Superviseur</div>
                </div>
                <a href="#" class="logout-btn" title="Déconnexion"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <form class="admin-search" method="GET" novalidate>
                    <input type="hidden" name="filtre" value="<?= htmlspecialchars($filtre ?? 'all') ?>">
                    <input type="text" name="titre" placeholder="Titre..." value="<?= htmlspecialchars($searchTitre ?? '') ?>">
                    <input type="text" name="date" placeholder="Date..." value="<?= htmlspecialchars($searchDate ?? '') ?>">
                    <button type="submit" style="display:none;"></button>
                </form>
                <button class="theme-toggle-btn" id="theme-btn" title="Mode clair"><i class="fa-solid fa-sun"></i></button>
            </header>

            <div class="admin-content">
                <div class="admin-header-row">
                    <h1 class="admin-page-title">Gestion des <span>Missions</span></h1>
                    <div style="display:flex; gap:1rem;">
                        <button onclick="exportToPDF()" class="btn btn-outline"><i class="fa-solid fa-file-pdf"></i> Exporter PDF</button>
                        <a href="add_job_admin.php" class="btn btn-primary"><i class="fa-solid fa-plus-circle"></i> Ajouter une mission</a>
                    </div>
                </div>

                <div class="admin-grid-4">
                    <div class="glass-card">
                        <div class="stat-card-header">
                            <span>Total Missions</span>
                            <div class="stat-card-icon"><i class="fa-solid fa-briefcase"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $totalAll ?></div>
                        <div class="stat-card-trend"><span class="trend-up"><i class="fa-solid fa-arrow-up"></i> 12%</span> vs mois dernier</div>
                    </div>
                    <div class="glass-card">
                        <div class="stat-card-header">
                            <span>En attente</span>
                            <div class="stat-card-icon" style="background:rgba(245,158,11,0.1); color:#f59e0b;"><i class="fa-solid fa-clock"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $totalPending ?></div>
                        <div class="stat-card-trend">Missions à réviser</div>
                    </div>
                    <div class="glass-card">
                        <div class="stat-card-header">
                            <span>Approuvées</span>
                            <div class="stat-card-icon" style="background:rgba(16,185,129,0.1); color:var(--tech-green);"><i class="fa-solid fa-check-circle"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $totalApproved ?></div>
                        <div class="stat-card-trend"><span class="trend-up"><i class="fa-solid fa-arrow-up"></i> 5%</span> actives</div>
                    </div>
                    <div class="glass-card">
                        <div class="stat-card-header">
                            <span>Rejetées</span>
                            <div class="stat-card-icon" style="background:rgba(239,68,68,0.1); color:var(--tunisian-red);"><i class="fa-solid fa-ban"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $totalRejected ?></div>
                        <div class="stat-card-trend">Non conformes</div>
                    </div>
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
                                    <div style="display:flex; gap:0.5rem;">
                                        <a href="detail_job_admin.php?id=<?= $o->getId() ?>" class="btn" style="background:rgba(255,255,255,0.05); color:white; border:1px solid rgba(255,255,255,0.1); padding:5px 10px; border-radius:4px; font-size:0.75rem;"><i class="fa-solid fa-eye"></i></a>
                                        <?php if ($o->getStatut() === 'pending'): ?>
                                            <a href="?action=approve&id=<?= $o->getId() ?>" class="btn" style="background:#10b981; color:white; border:none; padding:5px 10px; border-radius:4px; font-size:0.75rem;"><i class="fa-solid fa-check"></i></a>
                                            <a href="?action=reject&id=<?= $o->getId() ?>" class="btn" style="background:#ef4444; color:white; border:none; padding:5px 10px; border-radius:4px; font-size:0.75rem;"><i class="fa-solid fa-xmark"></i></a>
                                        <?php endif; ?>
                                        <a href="edit_job_admin.php?id=<?= $o->getId() ?>" class="btn" style="background:rgba(59,130,246,0.1); color:var(--tech-blue); border:1px solid rgba(59,130,246,0.2); padding:5px 10px; border-radius:4px; font-size:0.75rem;"><i class="fa-solid fa-pen"></i></a>
                                        <a href="dashboard.php?action=delete&id=<?= $o->getId() ?>" class="btn js-delete-admin" data-title="<?= htmlspecialchars($o->getTitre()) ?>" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2); padding:5px 10px; border-radius:4px; font-size:0.75rem;" title="Supprimer la mission"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

<!-- CUSTOM MODAL -->
<div class="modal-overlay" id="delete-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center; backdrop-filter:blur(5px);">
    <div class="modal-card" style="background:#1e293b; padding:2rem; border-radius:16px; border:1px solid rgba(255,255,255,0.1); text-align:center; max-width:400px; width:90%;">
        <div style="font-size:3rem; color:#ef4444; margin-bottom:1rem;"><i class="fa-solid fa-circle-exclamation"></i></div>
        <h3 style="color:white; margin-bottom:1rem; font-size:1.5rem;">Confirmation</h3>
        <p id="modal-desc" style="color:#cbd5e1; margin-bottom:2rem; line-height:1.5;"></p>
        <div style="display:flex; gap:1rem; justify-content:center;">
            <button id="confirm-cancel" style="padding:10px 20px; border-radius:8px; border:none; background:rgba(255,255,255,0.1); color:white; cursor:pointer; font-weight:600; transition:background 0.3s;">Annuler</button>
            <button id="confirm-ok" style="padding:10px 20px; border-radius:8px; border:none; background:#ef4444; color:white; cursor:pointer; font-weight:600; transition:background 0.3s;">Oui, supprimer</button>
        </div>
    </div>
</div>

<script>
    const deleteModal = document.getElementById('delete-modal');
    const confirmOk = document.getElementById('confirm-ok');
    const confirmCancel = document.getElementById('confirm-cancel');
    const modalDesc = document.getElementById('modal-desc');
    let deleteUrl = '';

    document.querySelectorAll('.js-delete-admin').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            deleteUrl = btn.href;
            modalDesc.innerHTML = `Voulez-vous vraiment supprimer définitivement la mission <strong style="color:white;">"${btn.dataset.title}"</strong> ?`;
            deleteModal.style.display = 'flex';
        });
    });

    confirmCancel.addEventListener('click', () => deleteModal.style.display = 'none');
    confirmOk.addEventListener('click', () => { if (deleteUrl) window.location.href = deleteUrl; });
</script>

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
