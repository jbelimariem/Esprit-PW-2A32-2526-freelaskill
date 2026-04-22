<?php
// views/frontoffice/freelancer_home.view.php — Interface Freelancer
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Freelancer — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf-autotable.min.js"></script>
</head>
<body class="page-anim">

<!-- NAVBAR -->
<nav>
    <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
    <ul class="nav-links">
        <li><a href="home.php">Accueil</a></li>
        <li><a href="home.php">Client</a></li>
        <li><a href="freelancer_home.php" class="active">Freelancer</a></li>
        <li><a href="#">Messagerie</a></li>
    </ul>
    <div class="nav-right">
        <div class="nav-avatar">FR</div>
    </div>
</nav>

<!-- SUB NAVBAR -->
<div class="sub-navbar">
    <div class="sub-nav-container">
        <ul class="sub-nav-links">
            <li><a href="freelancer_home.php" class="active"><i class="fa-solid fa-magnifying-glass"></i> Trouver des missions</a></li>
            <li><a href="freelancer_applications.php"><i class="fa-solid fa-paper-plane"></i> Mes Candidatures</a></li>
        </ul>
        <div class="sub-nav-actions">
            <button id="export-pdf" class="cart-btn" style="background:rgba(16,185,129,0.1); border-color:rgba(16,185,129,0.3); color:var(--tech-green);">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </button>
        </div>
    </div>
</div>

<section class="hero-banner">
    <div class="hero-glow"></div>
    <div class="hero-content" style="max-width: 800px; margin: 0 auto; text-align: center;">
        <div class="hero-tag" style="margin: 0 auto 1.5rem;"><i class="fa-solid fa-rocket"></i> Espace Freelancer</div>
        <h1 class="hero-title">Trouvez votre <span>prochaine mission</span></h1>
        <p class="hero-sub" style="margin-left: auto; margin-right: auto;">Explorez les meilleures opportunités publiées par nos clients et postulez en un clic.</p>
        
        <form class="search-container" method="GET" action="freelancer_home.php" style="margin: 2rem auto 0; max-width: 600px;">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" placeholder="Rechercher par titre, compétences..." value="<?= htmlspecialchars($q) ?>">
            </div>
            <button type="submit" class="btn-search">Rechercher</button>
        </form>
    </div>
</section>

<div class="page-body" style="display: block; max-width: 1100px; margin: 0 auto; padding: 2rem 1rem;">
    <!-- HORIZONTAL FILTERS INSTEAD OF SIDEBAR FOR CLEANER LOOK -->
    <div style="background:rgba(255,255,255,0.02); border:1px solid var(--border); border-radius:var(--radius-md); padding:1.5rem; margin-bottom:2.5rem; display:flex; align-items:center; justify-content:space-between; gap:2rem;">
        <form method="GET" action="freelancer_home.php" style="display:flex; align-items:center; gap:1.5rem; flex:1;">
            <?php if(!empty($q)): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:0.85rem; color:var(--text-muted); font-weight:600;">Budget:</span>
                <input type="number" name="min_price" placeholder="Min" value="<?= $min > 0 ? $min : '' ?>" style="background:rgba(255,255,255,0.05); border:1px solid var(--border); color:white; padding:8px 12px; border-radius:8px; width:100px; font-size:0.9rem;">
                <span style="color:var(--text-muted);">-</span>
                <input type="number" name="max_price" placeholder="Max" value="<?= $max < 999999 ? $max : '' ?>" style="background:rgba(255,255,255,0.05); border:1px solid var(--border); color:white; padding:8px 12px; border-radius:8px; width:100px; font-size:0.9rem;">
            </div>
            <button type="submit" class="cart-btn" style="padding:8px 20px;">Filtrer</button>
        </form>
        <div class="result-count" style="font-size:0.9rem; color:var(--text-muted);"><strong><?= count($offres) ?></strong> missions disponibles</div>
    </div>

    <!-- MISSIONS GRID -->
    <div class="job-grid">
        <?php if (empty($offres)): ?>
            <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem;">
                <i class="fa-solid fa-search" style="font-size: 3rem; margin-bottom: 1.5rem; color: var(--border);"></i>
                <h3 style="color:white; margin-bottom:0.5rem;">Aucune mission trouvée</h3>
                <p>Essayez de modifier vos critères de recherche ou vos filtres.</p>
            </div>
        <?php else: ?>
            <?php foreach ($offres as $o): 
                $competencesList = array_slice(explode(',', $o->getCompetences()), 0, 3);
            ?>
            <div class="job-card">
                <div class="job-card-header">
                    <div class="job-icon">💼</div>
                    <div class="job-badge" style="background:rgba(59,130,246,0.1); color:var(--tech-blue); padding:4px 10px; border-radius:var(--radius-full); font-size:0.65rem; font-weight:700; text-transform:uppercase;">Job Offer</div>
                </div>
                <div class="job-card-body">
                    <div class="job-titre"><?= htmlspecialchars($o->getTitre()) ?></div>
                    <div class="job-competences">
                        <?php foreach ($competencesList as $comp): ?>
                        <span style="display:inline-block; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.2); color:var(--tech-blue); padding:2px 8px; border-radius:var(--radius-full); font-size:.7rem; margin-right:4px;"><?= htmlspecialchars(trim($comp)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="job-meta">
                        <span><i class="fa-solid fa-coins"></i> <span class="job-budget"><?= number_format($o->getBudget(), 0, ',', ' ') ?></span> <small>DT</small></span>
                        <span><i class="fa-solid fa-calendar-days"></i> <?= date('d/m/Y', strtotime($o->getDateCreation())) ?></span>
                    </div>
                </div>
                <div class="job-actions">
                    <a href="freelancer_detail.php?id=<?= $o->getId() ?>" class="btn-action btn-view" style="width:100%; flex:none;"><i class="fa-solid fa-eye"></i> Voir Détails & Postuler</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // PDF Export Logic
    document.getElementById('export-pdf').addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.setFontSize(22);
        doc.setTextColor(37, 99, 235);
        doc.text("Liste des Missions — FreelaSkill", 14, 20);
        
        doc.setFontSize(10);
        doc.setTextColor(100);
        doc.text("Généré le : " + new Date().toLocaleDateString(), 14, 30);

        const data = <?= json_encode(array_map(fn($o) => [$o->getTitre(), $o->getBudget() . " DT", $o->getDelai(), $o->getCompetences()], $offres)) ?>;
        
        doc.autoTable({
            startY: 40,
            head: [['Titre', 'Budget', 'Délai', 'Compétences']],
            body: data,
            theme: 'striped',
            headStyles: { fillStyle: [37, 99, 235] }
        });
        
        doc.save("missions_freelancer.pdf");
    });
</script>

</body>
</html>
