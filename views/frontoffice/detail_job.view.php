<?php
// views/frontoffice/detail_job.view.php — Template: Détail d'une offre & Candidatures

$competences = array_map('trim', explode(',', $offre->getCompetences()));
$safeTitre = preg_replace('/[^A-Za-z0-9]/', '_', $offre->getTitre());

$statutConfig = [
    'pending'  => ['label' => 'En attente',  'color' => '#F59E0B', 'bg' => 'rgba(245,158,11,0.12)',  'border' => 'rgba(245,158,11,0.3)',  'icon' => 'fa-clock'],
    'approved' => ['label' => 'Approuvée',   'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.12)',  'border' => 'rgba(16,185,129,0.3)',  'icon' => 'fa-circle-check'],
    'rejected' => ['label' => 'Rejetée',     'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.12)',   'border' => 'rgba(239,68,68,0.3)',   'icon' => 'fa-circle-xmark'],
];
$badge = $statutConfig[$offre->getStatut()] ?? $statutConfig['pending'];

$appStatutConfig = [
    'pending'   => ['label' => 'En attente',  'color' => 'var(--text-muted)', 'bg' => 'rgba(255,255,255,0.05)'],
    'contacted' => ['label' => 'Contacté',   'color' => '#10b981',           'bg' => 'rgba(16,185,129,0.15)'],
    'rejected'  => ['label' => 'Refusé',      'color' => '#ef4444',           'bg' => 'rgba(239,68,68,0.15)'],
];
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($offre->getTitre()) ?> — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <style>
        .detail-layout { display: grid; grid-template-columns: 1fr 340px; gap: 2.5rem; max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; }
        .detail-card { background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 2rem; }
        .detail-body { padding: 2rem; }
        .detail-section-title { font-size: 0.75rem; text-transform: uppercase; color: var(--tech-blue); font-weight: 700; margin-bottom: 1rem; }
        .skill-tag { display: inline-block; background: rgba(59,130,246,0.1); color: var(--tech-blue); padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; margin: 4px; }
        .sidebar-card { background: rgba(2, 6, 23, 0.4); border: 1px solid var(--border); border-radius: var(--radius-lg); position: sticky; top: 100px; }
        .sidebar-section { padding: 1.5rem; border-bottom: 1px solid var(--border); }
        .meta-row { display: flex; justify-content: space-between; padding: 0.5rem 0; }
        .candidate-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 1rem; }
        .candidate-avatar { width: 40px; height: 40px; background: var(--tech-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; }
    </style>
</head>
<body class="page-anim">

<nav style="position: sticky; top: 0; width: 100%; z-index: 100; padding: 0 2rem;">
            <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
            <ul class="nav-links">
                <li><a href="home.php">Accueil</a></li>
                <li><a href="home.php" class="active">Client</a></li>
                <li><a href="freelancer_home.php">Freelancer</a></li>
            </ul>
            <div class="nav-right">
                <div class="nav-avatar">CL</div>
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
                <a href="client_freelancers.php" class="nav-item ">
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
        <div class="hero-tag" style="margin: 0 auto 1.5rem; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #60a5fa;"><i class="fa-solid fa-hashtag"></i> Mission #<?= $offre->getId() ?></div>
        <h1 class="hero-title" style="font-size: 3.5rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($offre->getTitre()) ?></h1>
    </div>
</section>

<div class="page-body" style="display: grid; grid-template-columns: 1fr 360px; gap: 2.5rem; padding-top: 2rem; max-width: 1200px; margin: 0 auto;">
    <div style="display: flex; flex-direction: column; gap: 2.5rem;">
        
        <!-- Description Card -->
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

        <!-- Competences Card -->
        <div class="detail-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 28px; overflow: hidden; backdrop-filter: blur(20px); padding: 3rem;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1.5rem;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #8b5cf6; font-size: 1.2rem;">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <h2 style="font-size: 1.2rem; font-weight: 700; color: white; letter-spacing: 0.5px;">Compétences requises</h2>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                <?php foreach($competences as $comp): ?>
                    <span class="chip" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.15)); border: 1px solid rgba(139, 92, 246, 0.3); color: white; padding: 10px 20px; font-size: 0.95rem; font-weight: 600; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);"><i class="fa-solid fa-check" style="color: #60a5fa; margin-right: 6px;"></i> <?= htmlspecialchars($comp) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Participants List -->
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; margin-top: 1rem;">
                <h2 style="font-size: 1.4rem; color: white; font-weight: 700; display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-users" style="color: var(--tech-blue);"></i> Participants
                </h2>
                <div style="background: rgba(255,255,255,0.1); padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: white;"><?= count($candidats) ?> Candidat(s)</div>
            </div>

            <?php if (empty($candidats)): ?>
                <div style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1); border-radius: 20px; padding: 3rem; text-align: center; color: var(--text-muted);">
                    <i class="fa-solid fa-user-slash" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <div style="font-size: 1.1rem; font-weight: 600;">Aucune candidature pour le moment.</div>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($candidats as $can): 
                        $appBadge = $appStatutConfig[$can->getStatus() ?? 'pending'] ?? $appStatutConfig['pending'];
                    ?>
                    <div class="candidate-item" style="display: flex; align-items: center; gap: 1.5rem; padding: 1.5rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; transition: transform 0.3s ease, background 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        <div class="candidate-avatar" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--tech-blue), #2563eb); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; color: white; box-shadow: 0 4px 15px rgba(59,130,246,0.3);">
                            <?= strtoupper(substr($can->getName(), 0, 1)) ?>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                                <div style="font-weight: 700; font-size: 1.1rem; color: white;"><?= htmlspecialchars($can->getName()) ?></div>
                                <span style="font-size: 0.75rem; color: <?= $appBadge['color'] ?>; background: <?= $appBadge['bg'] ?>; padding: 4px 10px; border-radius: 12px; font-weight: 600;"><?= $appBadge['label'] ?></span>
                            </div>
                            <div style="font-size: 0.9rem; color: var(--text-muted);"><i class="fa-solid fa-user-tie" style="margin-right: 5px; opacity: 0.7;"></i> <?= htmlspecialchars($can->getJobTitle()) ?></div>
                        </div>
                        <form method="POST" novalidate style="margin: 0; display: flex; gap: 8px;">
                            <input type="hidden" name="app_id" value="<?= $can->getId() ?>">
                            <button type="submit" name="action_app" value="approved" class="btn-action" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 10px 16px; border-radius: 12px; cursor: pointer; font-weight: 600; font-size: 0.9rem; transition: transform 0.3s ease, box-shadow 0.3s ease; box-shadow: 0 4px 15px rgba(16,185,129,0.3);">
                                <i class="fa-solid fa-check" style="margin-right: 6px;"></i> Approuver
                            </button>
                            <button type="submit" name="action_app" value="rejected" class="btn-action" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; padding: 10px 16px; border-radius: 12px; cursor: pointer; font-weight: 600; font-size: 0.9rem; transition: transform 0.3s ease, box-shadow 0.3s ease; box-shadow: 0 4px 15px rgba(239,68,68,0.3);">
                                <i class="fa-solid fa-xmark" style="margin-right: 6px;"></i> Refuser
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Sidebar -->
    <aside>
        <div style="position: sticky; top: 100px;">
            <div class="sidebar-card" style="background: linear-gradient(180deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.8) 100%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 28px; padding: 2.5rem; backdrop-filter: blur(30px); box-shadow: 0 20px 50px -10px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.1);">
                
                <!-- Budget Highlight -->
                <div style="text-align: center; margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <div style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; margin-bottom: 1rem;">Budget Global</div>
                    <div style="font-size: 3rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; background: linear-gradient(135deg, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; filter: drop-shadow(0 0 20px rgba(96, 165, 250, 0.4)); line-height: 1;">
                        <?= number_format($offre->getBudget(), 0, ',', ' ') ?>
                    </div>
                    <div style="font-size: 1.2rem; color: var(--text-muted); font-weight: 600; margin-top: 0.5rem;">Dinars (DT)</div>
                </div>

                <!-- Meta Info -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2.5rem;">
                    <div style="display: flex; align-items: center; gap: 15px; padding: 1rem 1.25rem; background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10b981;">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px;">Délai estimé</div>
                            <div style="font-weight: 600; color: white; font-size: 1rem;"><?= htmlspecialchars($offre->getDelai()) ?></div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px; padding: 1rem 1.25rem; background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                            <i class="fa-solid fa-calendar-day"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px;">Date de publication</div>
                            <div style="font-weight: 600; color: white; font-size: 1rem;"><?= date('d M Y', strtotime($offre->getDateCreation())) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Status Badge -->
                <div style="margin-bottom: 2rem;">
                    <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-muted); font-weight: 600; margin-bottom: 10px; text-align: center;">Statut de la mission</div>
                    <div style="display: flex; justify-content: center; align-items: center; gap: 10px; color: <?= $badge['color'] ?>; padding: 15px; border-radius: 16px; background: <?= $badge['bg'] ?>; border: 1px solid <?= $badge['border'] ?>; font-weight: 700; font-size: 1.1rem; box-shadow: 0 0 20px <?= str_replace('1)', '0.2)', $badge['color']) ?>;">
                        <i class="fa-solid <?= $badge['icon'] ?? 'fa-circle' ?>"></i> <?= $badge['label'] ?>
                    </div>
                </div>

                <!-- Actions -->
                <button id="download-pdf" class="btn-search" style="width: 100%; justify-content: center; padding: 1.2rem; font-size: 1.05rem; border-radius: 16px; background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease; font-weight: 600;">
                    <i class="fa-solid fa-file-pdf" style="color: #ef4444; margin-right: 8px;"></i> Télécharger Facture PDF
                </button>
            </div>
        </div>
    </aside>
</div>

<script>
document.getElementById('download-pdf')?.addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text("Détail de la Mission", 14, 15);
    doc.autoTable({
        startY: 20,
        head: [["Caractéristique", "Détail"]],
        body: [
            ["Titre", "<?= addslashes($offre->getTitre()) ?>"],
            ["Budget", "<?= number_format($offre->getBudget(), 0, ',', ' ') ?> DT"],
            ["Délai", "<?= addslashes($offre->getDelai()) ?>"],
            ["Statut", "<?= $badge['label'] ?>"]
        ]
    });
    saveAs(doc.output('blob'), 'mission_<?= $safeTitre ?>.pdf');
});
</script>
    </div>
</div>
</body>
</html>
