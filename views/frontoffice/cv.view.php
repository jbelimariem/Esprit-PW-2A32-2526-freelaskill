<?php
// views/frontoffice/cv.view.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV de <?= htmlspecialchars($freelancer['prenom'] . ' ' . $freelancer['nom']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- jsPDF for export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="../assets/theme-light.css?v=<?= time() ?>">
    
    <style>
        :root {
            --primary: #2563eb;
            --text-main: #1f2937;
            --text-light: #6b7280;
            --bg-page: #f3f4f6;
            --paper: #ffffff;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            margin: 0;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .action-bar {
            width: 100%;
            max-width: 800px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .btn-action {
            background: white;
            border: 1px solid #d1d5db;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-main);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .btn-action:hover {
            background: #f9fafb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .btn-export {
            background: var(--primary);
            color: white;
            border: none;
        }
        .btn-export:hover {
            background: #1d4ed8;
        }
        
        /* A4 Paper Styling */
        .cv-document {
            width: 210mm;
            min-height: 297mm;
            background: var(--paper);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            padding: 0;
            overflow: hidden;
            display: flex;
        }
        
        .cv-sidebar {
            width: 35%;
            background: #1e293b;
            color: white;
            padding: 3rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        .cv-avatar {
            width: 120px;
            height: 120px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 800;
            margin: 0 auto;
            border: 4px solid rgba(255,255,255,0.2);
        }
        .sidebar-section h3 {
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #cbd5e1;
            word-break: break-all;
        }
        
        .cv-main {
            width: 65%;
            padding: 3rem;
            background: white;
        }
        .cv-header {
            margin-bottom: 3rem;
        }
        .cv-name {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-main);
            margin: 0 0 0.5rem 0;
            line-height: 1.1;
        }
        .cv-job-title {
            font-size: 1.3rem;
            color: var(--primary);
            font-weight: 500;
            margin: 0;
        }
        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-main);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        .bio-text {
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .experience-item {
            margin-bottom: 1.5rem;
        }
        .exp-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 0.5rem;
        }
        .exp-title {
            font-weight: 700;
            font-size: 1.1rem;
        }
        .exp-company {
            color: var(--primary);
            font-weight: 500;
        }
        .exp-date {
            font-size: 0.85rem;
            color: var(--text-light);
            font-weight: 500;
        }
        .exp-desc {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        @media print {
            body { background: white; padding: 0; }
            .action-bar { display: none; }
            .cv-document { box-shadow: none; border-radius: 0; width: 100%; min-height: 100vh; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <div style="display:flex; gap:10px;">
        <a href="client_freelancers.php" class="btn-action"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        <button class="theme-toggle-btn btn-action" title="Mode Nuit/Clair">
            <i class="fa-solid fa-moon"></i>
        </button>
    </div>
    <button id="export-btn" class="btn-action btn-export">
        <i class="fa-solid fa-file-pdf"></i> Télécharger en PDF
    </button>
</div>

<div class="cv-document" id="cv-content">
    <aside class="cv-sidebar">
        <div class="cv-avatar">
            <?= strtoupper(substr($freelancer['prenom'], 0, 1) . substr($freelancer['nom'], 0, 1)) ?>
        </div>
        
        <div class="sidebar-section">
            <h3>Contact</h3>
            <div class="contact-item">
                <i class="fa-solid fa-envelope"></i>
                <span><?= htmlspecialchars($freelancer['email']) ?></span>
            </div>
            <?php if (!empty($freelancer['linkedin_url'])): ?>
            <div class="contact-item">
                <i class="fa-brands fa-linkedin"></i>
                <span><?= htmlspecialchars(str_replace('https://', '', $freelancer['linkedin_url'])) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($freelancer['github_url'])): ?>
            <div class="contact-item">
                <i class="fa-brands fa-github"></i>
                <span><?= htmlspecialchars(str_replace('https://', '', $freelancer['github_url'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="sidebar-section">
            <h3>Compétences</h3>
            <!-- Mocks for UI completeness -->
            <ul style="padding-left: 20px; line-height: 1.8; color: #cbd5e1;">
                <li>Développement Web</li>
                <li>Design UI/UX</li>
                <li>Gestion de projet</li>
                <li>Travail en équipe</li>
            </ul>
        </div>
    </aside>
    
    <main class="cv-main">
        <header class="cv-header">
            <h1 class="cv-name"><?= htmlspecialchars($freelancer['prenom'] . ' ' . strtoupper($freelancer['nom'])) ?></h1>
            <h2 class="cv-job-title"><?= htmlspecialchars($freelancer['role'] == 'freelancer' ? 'Freelancer Indépendant' : 'Profil') ?></h2>
        </header>
        
        <section>
            <h3 class="section-title">À Propos</h3>
            <p class="bio-text">
                <?= nl2br(htmlspecialchars($freelancer['bio'] ?? 'Passionné(e) par la technologie et toujours prêt(e) à relever de nouveaux défis. J\'apporte mon expertise pour mener à bien vos projets avec qualité et professionnalisme.')) ?>
            </p>
        </section>
        
        <section>
            <h3 class="section-title">Expériences Professionnelles</h3>
            <?php foreach ($experiences as $exp): ?>
            <div class="experience-item">
                <div class="exp-header">
                    <div>
                        <span class="exp-title"><?= htmlspecialchars($exp['title']) ?></span> | 
                        <span class="exp-company"><?= htmlspecialchars($exp['company']) ?></span>
                    </div>
                    <span class="exp-date"><?= htmlspecialchars($exp['duration']) ?></span>
                </div>
                <p class="exp-desc"><?= htmlspecialchars($exp['desc']) ?></p>
            </div>
            <?php endforeach; ?>
        </section>
        
        <section style="margin-top: 2rem;">
            <h3 class="section-title">Formation</h3>
            <div class="experience-item">
                <div class="exp-header">
                    <div>
                        <span class="exp-title">Diplôme d'Ingénieur en Informatique</span>
                    </div>
                    <span class="exp-date">2016 - 2019</span>
                </div>
                <p class="exp-desc">École Nationale Supérieure d'Informatique. Spécialisation Génie Logiciel.</p>
            </div>
        </section>
    </div>
</div>

<script>
document.getElementById('export-btn').addEventListener('click', function() {
    // Hide the button during capture
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Génération...';
    btn.disabled = true;
    
    const cvElement = document.getElementById('cv-content');
    
    // Use html2canvas to capture the CV as an image
    html2canvas(cvElement, { scale: 2 }).then(canvas => {
        const imgData = canvas.toDataURL('image/jpeg', 1.0);
        const { jsPDF } = window.jspdf;
        
        // A4 dimension: 210 x 297 mm
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
        
        pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
        pdf.save('CV_<?= htmlspecialchars(addslashes($freelancer['prenom'] . '_' . $freelancer['nom'])) ?>.pdf');
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>

<script src="../assets/theme.js"></script>
</body>
</html>
