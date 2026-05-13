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
    <title>Flux de Missions — Admin | FreelaSkill</title>
    <meta name="description" content="Tableau de bord administrateur - gestion des missions FreelaSkill.">
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin_v2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="../assets/theme.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
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
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2.5rem;" class="animate-up">
            <div>
                <h1 class="admin-page-title">Gestion des <span style="color:var(--tech-blue)">Missions</span></h1>
                <p style="color:var(--text-muted);font-size:.9rem;">Visualisez, modérez et gérez toutes les offres de la plateforme.</p>
            </div>
            <div style="display:flex;align-items:center;gap:1rem;">
                <button type="button" class="theme-toggle" data-theme-toggle>
                    <i class="fa-solid fa-sun" data-theme-icon></i>
                    <span data-theme-label>Jour</span>
                </button>
                <button onclick="exportToPDF()" class="btn btn-outline"><i class="fa-solid fa-file-pdf"></i> Exporter PDF</button>
                <a href="add_job_admin.php" class="btn-add"><i class="fa-solid fa-plus-circle"></i> Ajouter une mission</a>
            </div>
        </header>

        <!-- FLASH MESSAGES -->
        <?php $success = $_GET['success'] ?? ''; if ($success): ?>
        <?php
            $flashMap = [
                'approved' => ['bg'=>'rgba(16,185,129,.12)', 'border'=>'rgba(16,185,129,.35)', 'color'=>'#6ee7b7', 'icon'=>'fa-circle-check',       'title'=>'Mission approuvée !',  'sub'=>'L\'offre est maintenant visible par les freelancers.'],
                'rejected' => ['bg'=>'rgba(239,68,68,.12)',  'border'=>'rgba(239,68,68,.35)',  'color'=>'#fca5a5', 'icon'=>'fa-circle-xmark',       'title'=>'Mission rejetée.',     'sub'=>'L\'offre a été marquée comme non conforme.'],
                'deleted'  => ['bg'=>'rgba(245,158,11,.12)', 'border'=>'rgba(245,158,11,.35)', 'color'=>'#fcd34d', 'icon'=>'fa-triangle-exclamation','title'=>'Mission supprimée.',   'sub'=>'L\'offre a été définitivement supprimée.'],
                'added'    => ['bg'=>'rgba(16,185,129,.12)', 'border'=>'rgba(16,185,129,.35)', 'color'=>'#6ee7b7', 'icon'=>'fa-circle-check',       'title'=>'Mission créée !',      'sub'=>'La nouvelle offre a été ajoutée avec succès.'],
            ];
            $f = $flashMap[$success] ?? null;
        ?>
        <?php if ($f): ?>
        <div style="display:flex;align-items:flex-start;gap:.9rem;padding:.85rem 1.25rem;border-radius:12px;margin-bottom:1.5rem;border:1px solid <?= $f['border'] ?>;background:<?= $f['bg'] ?>;color:<?= $f['color'] ?>;font-size:.9rem;font-weight:500;" class="animate-up">
            <div style="width:36px;height:36px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.08);flex-shrink:0;">
                <i class="fa-solid <?= $f['icon'] ?>"></i>
            </div>
            <div>
                <div style="font-weight:700;font-size:.92rem;color:var(--text-strong);"><?= $f['title'] ?></div>
                <div style="font-size:.82rem;opacity:.9;"><?= $f['sub'] ?></div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- STAT CARDS -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2rem;" class="animate-up">
            <div class="stat-card stat-card--blue">
                <div class="stat-card__icon"><i class="fa-solid fa-briefcase"></i></div>
                <div class="stat-card__body">
                    <p class="stat-card__label">Total Missions</p>
                    <h2 class="stat-card__value"><?= $totalAll ?></h2>
                    <div class="stat-card__bar-wrap"><div class="stat-card__bar" style="width:100%;background:rgba(59,130,246,.5);"></div></div>
                    <p class="stat-card__sub"><i class="fa-solid fa-arrow-trend-up"></i> Toutes les offres publiées</p>
                </div>
            </div>
            <div class="stat-card stat-card--red">
                <div class="stat-card__icon"><i class="fa-solid fa-clock"></i></div>
                <div class="stat-card__body">
                    <p class="stat-card__label">En attente</p>
                    <h2 class="stat-card__value"><?= $totalPending ?></h2>
                    <div class="stat-card__bar-wrap"><div class="stat-card__bar" style="width:<?= $totalAll > 0 ? round($totalPending/$totalAll*100) : 0 ?>%;background:rgba(245,158,11,.6);"></div></div>
                    <p class="stat-card__sub"><i class="fa-solid fa-triangle-exclamation"></i> À réviser</p>
                </div>
            </div>
            <div class="stat-card stat-card--green">
                <div class="stat-card__icon"><i class="fa-solid fa-check-circle"></i></div>
                <div class="stat-card__body">
                    <p class="stat-card__label">Approuvées</p>
                    <h2 class="stat-card__value"><?= $totalApproved ?></h2>
                    <div class="stat-card__bar-wrap"><div class="stat-card__bar" style="width:<?= $totalAll > 0 ? round($totalApproved/$totalAll*100) : 0 ?>%;background:rgba(16,185,129,.6);"></div></div>
                    <p class="stat-card__sub"><i class="fa-solid fa-circle-check"></i> Missions actives</p>
                </div>
            </div>
            <div class="stat-card stat-card--purple">
                <div class="stat-card__icon"><i class="fa-solid fa-ban"></i></div>
                <div class="stat-card__body">
                    <p class="stat-card__label">Rejetées</p>
                    <h2 class="stat-card__value"><?= $totalRejected ?></h2>
                    <div class="stat-card__bar-wrap"><div class="stat-card__bar" style="width:<?= $totalAll > 0 ? round($totalRejected/$totalAll*100) : 0 ?>%;background:rgba(139,92,246,.6);"></div></div>
                    <p class="stat-card__sub"><i class="fa-solid fa-xmark"></i> Non conformes</p>
                </div>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <section class="admin-section animate-up">
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; gap:1rem;">
                <form method="GET" action="admin_missions.php" style="display:flex; gap:.75rem; flex:1;">
                    <input type="hidden" name="filtre" value="<?= htmlspecialchars($filtre ?? 'all') ?>">
                    <div class="admin-filter-wrap" style="flex:1;">
                        <i class="fa-solid fa-magnifying-glass admin-filter-icon"></i>
                        <input type="text" name="titre" class="admin-filter-input" 
                               placeholder="Rechercher une mission par titre..." 
                               value="<?= htmlspecialchars($_GET['titre'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                        <i class="fa-solid fa-filter"></i> Filtrer
                    </button>
                    <a href="admin_missions.php?filtre=<?= htmlspecialchars($filtre ?? 'all') ?>" class="btn btn-outline" style="white-space:nowrap;">
                        <i class="fa-solid fa-rotate-left"></i> Réinitialiser
                    </a>
                </form>
                
                <div style="display:flex; gap:.75rem;">
                     <div class="dropdown" style="position:relative;">
                        <button class="btn btn-outline dropdown-toggle" style="gap:.5rem;">
                            <i class="fa-solid fa-calendar-days"></i> <?= !empty($_GET['date']) ? $_GET['date'] : 'Toutes les dates' ?>
                        </button>
                        <div class="dropdown-menu" style="position:absolute; top:100%; right:0; background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:1rem; z-index:100; min-width:250px; display:none; margin-top:10px; box-shadow:0 10px 25px rgba(0,0,0,0.4);">
                            <form method="GET" action="admin_missions.php">
                                <input type="hidden" name="filtre" value="<?= htmlspecialchars($filtre ?? 'all') ?>">
                                <input type="hidden" name="titre" value="<?= htmlspecialchars($_GET['titre'] ?? '') ?>">
                                <label style="display:block; font-size:.8rem; margin-bottom:.5rem; color:var(--text-muted);">Choisir une date :</label>
                                <input type="date" name="date" class="form-input" style="width:100%; margin-bottom:1rem;" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
                                <button type="submit" class="btn-add" style="width:100%; padding:.6rem;">Appliquer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Simple dropdown toggle
                document.querySelector('.dropdown-toggle').addEventListener('click', function(e) {
                    e.stopPropagation();
                    const menu = this.nextElementSibling;
                    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
                });
                document.addEventListener('click', () => {
                    const menu = document.querySelector('.dropdown-menu');
                    if(menu) menu.style.display = 'none';
                });
                document.querySelector('.dropdown-menu').addEventListener('click', (e) => e.stopPropagation());
            </script>

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
                                        <a href="admin_missions.php?action=delete&id=<?= $o->getId() ?>" class="btn js-delete-admin" data-title="<?= htmlspecialchars($o->getTitre()) ?>" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2); padding:5px 10px; border-radius:4px; font-size:0.75rem;" title="Supprimer la mission"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
        </section>
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
            modalDesc.innerHTML = `Voulez-vous vraiment supprimer définitivement la mission <strong style="color:white;">&quot;${btn.dataset.title}&quot;</strong> ?`;
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
