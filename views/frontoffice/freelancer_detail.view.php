<?php
// views/frontoffice/freelancer_detail.view.php — Détail Mission pour Freelancer
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($offre->getTitre()) ?> — Détails Mission</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body class="page-anim">

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

<div class="sub-navbar">
    <div class="sub-nav-container">
        <ul class="sub-nav-links">
            <li><a href="freelancer_home.php"><i class="fa-solid fa-magnifying-glass"></i> Trouver des missions</a></li>
            <li><a href="freelancer_applications.php"><i class="fa-solid fa-paper-plane"></i> Mes Candidatures</a></li>
        </ul>
        <div class="sub-nav-actions">
            <a href="freelancer_home.php" class="cart-btn" style="background:rgba(255,255,255,0.06); color:white; text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        </div>
    </div>
</div>

<section class="hero-banner" style="padding: 3rem 4rem;">
    <div class="hero-glow"></div>
    <div class="hero-content">
        <div class="hero-tag"><i class="fa-solid fa-briefcase"></i> Détails de la mission</div>
        <h1 class="hero-title"><?= htmlspecialchars($offre->getTitre()) ?></h1>
    </div>
</section>

<div class="page-body" style="display: grid; grid-template-columns: 1fr 340px; gap: 2rem; padding-top: 2rem;">
    <div>
        <div class="detail-card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden;">
            <div class="card-body" style="padding: 2.5rem;">
                <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--tech-blue); font-weight: 700; margin-bottom: 1rem;">Description de la mission</div>
                <p style="white-space: pre-wrap; line-height: 1.7; color: var(--text-light); font-size: 1.05rem;"><?= nl2br(htmlspecialchars($offre->getDescription())) ?></p>
                
                <div style="margin-top: 3rem;">
                    <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--tech-blue); font-weight: 700; margin-bottom: 1rem;">Compétences requises</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php foreach(explode(',', $offre->getCompetences()) as $skill): ?>
                            <span class="chip" style="padding: 8px 15px; font-size: 0.9rem;"><?= trim($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <aside>
        <div class="detail-card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem;">
            <div style="margin-bottom: 2rem;">
                <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Budget proposé</div>
                <div style="font-size: 2rem; font-weight: 700; font-family: 'JetBrains Mono', monospace;">
                    <?= number_format($offre->getBudget(), 0, ',', ' ') ?> <span style="font-size: 1rem; color: var(--text-muted);">DT</span>
                </div>
            </div>

            <div style="margin-bottom: 2rem; padding: 1.5rem; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <i class="fa-solid fa-clock" style="color: var(--tech-blue);"></i>
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Délai :</span>
                    <strong style="color: white;"><?= htmlspecialchars($offre->getDelai()) ?></strong>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-calendar-day" style="color: var(--tech-blue);"></i>
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Publié le :</span>
                    <strong style="color: white;"><?= date('d/m/Y', strtotime($offre->getDateCreation())) ?></strong>
                </div>
            </div>

            <button class="btn-search" style="width: 100%; justify-content: center; background: var(--tech-blue); color: white; margin-bottom: 1rem; padding: 1rem;">
                Postuler à cette mission
            </button>
            <button id="download-job-pdf" class="btn-cart" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-file-pdf"></i> Télécharger en PDF
            </button>
        </div>
    </aside>
</div>

<script>
    document.getElementById('download-job-pdf').addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.setFontSize(22);
        doc.setTextColor(37, 99, 235);
        doc.text("Détails de la Mission", 14, 20);
        
        doc.setFontSize(16);
        doc.setTextColor(0);
        doc.text("<?= addslashes($offre->getTitre()) ?>", 14, 35);
        
        doc.setFontSize(12);
        doc.text("Budget: <?= $offre->getBudget() ?> DT", 14, 50);
        doc.text("Délai: <?= addslashes($offre->getDelai()) ?>", 14, 60);
        doc.text("Compétences: <?= addslashes($offre->getCompetences()) ?>", 14, 70);
        
        doc.text("Description:", 14, 85);
        const splitDesc = doc.splitTextToSize("<?= addslashes(str_replace(["\r", "\n"], ' ', $offre->getDescription())) ?>", 180);
        doc.text(splitDesc, 14, 95);
        
        doc.save("mission_<?= $offre->getId() ?>.pdf");
    });
</script>

</body>
</html>
