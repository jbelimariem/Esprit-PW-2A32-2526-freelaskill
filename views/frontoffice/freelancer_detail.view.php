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

<nav style="position: sticky; top: 0; width: 100%; z-index: 100; padding: 0 2rem;">
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



<div class="marketplace-layout">
        <aside class="mkt-sidebar">
        <!-- Card 1 : Profil marketplace -->
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-building"></i></div>
                <div class="mkt-profile-name">Espace Client</div>
                <div class="mkt-profile-sub">FreelaSkill Tunisia</div>
            </div>
            <div class="mkt-profile-stats">
                <div class="mkt-stat">
                    <div class="mkt-stat-val">3</div>
                    <div class="mkt-stat-label">OFFRES</div>
                </div>
                <div class="mkt-stat">
                    <div class="mkt-stat-val">8</div>
                    <div class="mkt-stat-label">CANDIDATS</div>
                </div>
            </div>
        </div>

        <!-- Card 2 : Navigation + Filtres -->
        <div class="mkt-sidebar-card">
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="home.php" class="nav-item ">
                    <i class="fa-solid fa-list-ul"></i> Mes Offres
                </a>
                <a href="add_job.php" class="nav-item ">
                    <i class="fa-solid fa-plus-circle"></i> Nouveau Offre
                </a>
                <a href="client_freelancers.php" class="nav-item active">
                    <i class="fa-solid fa-users"></i> Freelancers
                </a>
                <a href="#" id="download-pdf-home" class="nav-item">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>
    </aside>

    <div class="mkt-main">
        <!-- TOPBAR INSIDE MAIN -->
        

<section class="hero-banner" style="padding: 3rem 4rem; position: relative; overflow: hidden;">
    <div class="hero-glow" style="width: 800px; height: 800px; top: -400px; left: -200px; opacity: 0.8;"></div>
    <div class="hero-glow-2" style="width: 600px; height: 600px; bottom: -300px; right: -100px; opacity: 0.6;"></div>
    <div class="hero-content" style="position: relative; z-index: 2; text-align: center; max-width: 900px; margin: 0 auto; background: rgba(255, 255, 255, 0.03); padding: 3rem; border-radius: 32px; border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(20px); box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.5);">
        <div class="hero-tag" style="margin: 0 auto 1.5rem; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #60a5fa;"><i class="fa-solid fa-briefcase"></i> Détails de la mission</div>
        <h1 class="hero-title" style="font-size: 3.5rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($offre->getTitre()) ?></h1>
    </div>
</section>

<div class="page-body" style="display: grid; grid-template-columns: 1fr 360px; gap: 2.5rem; padding-top: 2rem; max-width: 1200px; margin: 0 auto;">
    <div style="display: flex; flex-direction: column; gap: 2.5rem;">
        <div class="detail-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 28px; overflow: hidden; backdrop-filter: blur(20px); transition: transform 0.4s ease, box-shadow 0.4s ease;">
            <div class="card-body" style="padding: 3rem;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1.5rem;">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: var(--tech-blue); font-size: 1.2rem;">
                        <i class="fa-solid fa-align-left"></i>
                    </div>
                    <h2 style="font-size: 1.2rem; font-weight: 700; color: white; letter-spacing: 0.5px;">Description de la mission</h2>
                </div>
                <p style="white-space: pre-wrap; line-height: 1.8; color: var(--text-light); font-size: 1.05rem; opacity: 0.9;"><?= nl2br(htmlspecialchars($offre->getDescription())) ?></p>
            </div>
        </div>
        
        <div class="detail-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 28px; overflow: hidden; backdrop-filter: blur(20px); padding: 3rem;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1.5rem;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #8b5cf6; font-size: 1.2rem;">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <h2 style="font-size: 1.2rem; font-weight: 700; color: white; letter-spacing: 0.5px;">Compétences requises</h2>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                <?php foreach(explode(',', $offre->getCompetences()) as $skill): ?>
                    <span class="chip" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.15)); border: 1px solid rgba(139, 92, 246, 0.3); color: white; padding: 10px 20px; font-size: 0.95rem; font-weight: 600; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease;"><i class="fa-solid fa-check" style="color: #60a5fa; margin-right: 6px;"></i> <?= trim($skill) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <aside>
        <div style="position: sticky; top: 100px;">
            <div class="detail-card" style="background: linear-gradient(180deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.8) 100%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 28px; padding: 2.5rem; backdrop-filter: blur(30px); box-shadow: 0 20px 50px -10px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.1);">
                
                <!-- Budget Highlight -->
                <div style="text-align: center; margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <div style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; margin-bottom: 1rem;">Budget Proposé</div>
                    <div style="font-size: 3rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; background: linear-gradient(135deg, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; filter: drop-shadow(0 0 20px rgba(96, 165, 250, 0.4)); line-height: 1;">
                        <?= number_format($offre->getBudget(), 0, ',', ' ') ?>
                    </div>
                    <div style="font-size: 1.2rem; color: var(--text-muted); font-weight: 600; margin-top: 0.5rem;">Dinars (DT)</div>
                </div>

                <!-- Meta Info -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2.5rem;">
                    <div style="display: flex; align-items: center; gap: 15px; padding: 1rem 1.25rem; background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); transition: background 0.3s ease;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10b981;">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px;">Délai estimé</div>
                            <div style="font-weight: 600; color: white; font-size: 1rem;"><?= htmlspecialchars($offre->getDelai()) ?></div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px; padding: 1rem 1.25rem; background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); transition: background 0.3s ease;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                            <i class="fa-solid fa-calendar-day"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px;">Date de publication</div>
                            <div style="font-weight: 600; color: white; font-size: 1rem;"><?= date('d M Y', strtotime($offre->getDateCreation())) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <?php if ($has_applied): ?>
                    <div style="margin-bottom: 1rem; width: 100%; text-align: center; padding: 1.2rem; font-size: 1.1rem; border-radius: 16px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; font-weight: 700;">
                        <i class="fa-solid fa-check-circle" style="margin-right: 8px;"></i> Candidature envoyée
                    </div>
                <?php else: ?>
                    <form method="POST" action="freelancer_detail.php?id=<?= $offre->getId() ?>" style="margin-bottom: 1rem;">
                        <input type="hidden" name="action" value="apply">
                        <button type="submit" class="btn-search" style="width: 100%; justify-content: center; padding: 1.2rem; font-size: 1.1rem; border-radius: 16px; background: linear-gradient(135deg, var(--tech-blue), #2563eb); box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5); border: none; font-weight: 700; transition: transform 0.3s ease, box-shadow 0.3s ease; color: white; cursor: pointer;">
                            <i class="fa-solid fa-paper-plane" style="margin-right: 8px;"></i> Postuler Maintenant
                        </button>
                    </form>
                <?php endif; ?>
                <button id="download-job-pdf" class="btn-cart" style="width: 100%; justify-content: center; padding: 1rem; font-size: 0.95rem; border-radius: 16px; background: rgba(255,255,255,0.03); color: white; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease;">
                    <i class="fa-solid fa-download"></i> Enregistrer en PDF
                </button>
            </div>
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

    </div>
</div>
</body>
</html>
