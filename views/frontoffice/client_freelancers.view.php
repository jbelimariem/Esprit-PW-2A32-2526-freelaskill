<?php
// views/frontoffice/client_freelancers.view.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talents Freelancers — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/theme-light.css?v=<?= time() ?>">
    <style>
        .freelancer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .freelancer-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            text-align: center;
            backdrop-filter: blur(20px);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }
        .freelancer-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4);
            border-color: rgba(59, 130, 246, 0.3);
        }
        .freelancer-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--tech-blue), #2563eb);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: white;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
            transform: rotate(-5deg);
            transition: transform 0.3s ease;
        }
        .freelancer-card:hover .freelancer-avatar {
            transform: rotate(0deg) scale(1.05);
        }
        .freelancer-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }
        .freelancer-title {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }
        .freelancer-email {
            font-size: 0.85rem;
            color: var(--tech-blue);
            background: rgba(59, 130, 246, 0.1);
            padding: 8px 16px;
            border-radius: 12px;
            display: inline-block;
        }
    </style>
</head>
<body class="page-anim home-page">

<nav>
    <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
    <ul class="nav-links">
        <li><span style="color:var(--text-muted);cursor:default;">Accueil</span></li>
        <li><a href="home.php">Marketplace</a></li>
        <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'client'): ?>
            <li><a href="missions.php">Missions</a></li>
        <?php else: ?>
            <li><a href="freelancer_home.php">Freelancers</a></li>
        <?php endif; ?>
        <li><a href="<?= (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? '/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin' : '/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=conversations' ?>" class="<?= (strpos($_SERVER['PHP_SELF'], 'essagerie') !== false) ? 'active' : '' ?>">Messagerie</a></li>
        <li><a href="profile.php">Mon Profil</a></li>
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
                    <div class="mkt-stat-val"><?= config::getConnexion()->query("SELECT COUNT(*) FROM offres_emploi")->fetchColumn() ?></div>
                    <div class="mkt-stat-label">OFFRES</div>
                </div>
                <div class="mkt-stat">
                    <div class="mkt-stat-val"><?= config::getConnexion()->query("SELECT COUNT(*) FROM job_applications")->fetchColumn() ?></div>
                    <div class="mkt-stat-label">CANDIDATS</div>
                </div>
            </div>
        </div>

        <!-- Card 2 : Navigation + Filtres -->
        <div class="mkt-sidebar-card">
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="missions.php" class="nav-item ">
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
        

        <section class="hero-banner" style="padding: 3rem 1rem; position: relative; overflow: hidden; min-height: auto;">
            <div class="hero-glow" style="width: 600px; height: 600px; top: -300px; left: -100px; opacity: 0.7;"></div>
            <div class="hero-content" style="max-width: 900px; margin: 0 auto; text-align: center; position: relative; z-index: 2;">
                <div class="hero-tag" style="margin: 0 auto 1.5rem;"><i class="fa-solid fa-star"></i> Talents</div>
                <h1 class="hero-title">Découvrez nos <span>Freelancers</span></h1>
                <p class="hero-sub" style="margin-left: auto; margin-right: auto;">Consultez la liste des talents ayant postulé sur la plateforme.</p>
                
                <form class="search-container" method="GET" action="client_freelancers.php" style="margin: 2rem auto 0; max-width: 600px;">
                    <div class="search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="q" placeholder="Rechercher par nom, prénom ou email..." value="<?= htmlspecialchars($q ?? '') ?>">
                    </div>
                    <button type="submit" class="btn-search">Rechercher</button>
                </form>
            </div>
        </section>

        <div class="page-body" style="display: block; max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
            <?php if (empty($freelancers)): ?>
                <div style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1); border-radius: 20px; padding: 4rem; text-align: center; color: var(--text-muted); margin-top: 2rem;">
                    <i class="fa-solid fa-user-slash" style="font-size: 3rem; margin-bottom: 1.5rem; opacity: 0.5;"></i>
                    <div style="font-size: 1.2rem; font-weight: 600;">Aucun talent n'a encore postulé sur la plateforme.</div>
                </div>
            <?php else: ?>
                <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 24px; padding: 1.5rem; overflow-x: auto; backdrop-filter: blur(20px);">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0 10px;">
                        <thead>
                            <tr style="text-align: left; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">
                                <th style="padding: 1rem 1.5rem; font-weight: 600;">Nom & Prénom</th>
                                <th style="padding: 1rem 1.5rem; font-weight: 600;">Email</th>
                                <th style="padding: 1rem 1.5rem; font-weight: 600; text-align: center;">Profil LinkedIn</th>
                                <th style="padding: 1rem 1.5rem; font-weight: 600; text-align: center;">Curriculum Vitae</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($freelancers as $free): ?>
                                <tr style="background: rgba(255,255,255,0.03); transition: transform 0.3s ease, background 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.1);" onmouseover="this.style.background='rgba(59,130,246,0.05)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(255,255,255,0.03)'; this.style.transform='translateY(0)';">
                                    <td style="padding: 1.2rem 1.5rem; border-top-left-radius: 16px; border-bottom-left-radius: 16px;">
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--tech-blue), #2563eb); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; color: white; box-shadow: 0 4px 10px rgba(59,130,246,0.3);">
                                                <?= strtoupper(substr($free['prenom'], 0, 1) . substr($free['nom'], 0, 1)) ?>
                                            </div>
                                            <div style="font-weight: 700; font-size: 1.1rem; color: white;">
                                                <?= htmlspecialchars($free['prenom'] . ' ' . $free['nom']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 1.2rem 1.5rem; color: var(--text-light);">
                                        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.05); padding: 6px 12px; border-radius: 10px; font-size: 0.9rem;">
                                            <i class="fa-solid fa-envelope" style="color: var(--text-muted);"></i> <?= htmlspecialchars($free['email']) ?>
                                        </div>
                                    </td>
                                    <td style="padding: 1.2rem 1.5rem; text-align: center;">
                                        <?php if (!empty($free['linkedin_url'])): ?>
                                            <a href="<?= htmlspecialchars($free['linkedin_url']) ?>" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: rgba(10, 102, 194, 0.1); color: #0a66c2; border-radius: 12px; text-decoration: none; transition: all 0.3s ease; border: 1px solid rgba(10, 102, 194, 0.2);" onmouseover="this.style.background='#0a66c2'; this.style.color='white';" onmouseout="this.style.background='rgba(10, 102, 194, 0.1)'; this.style.color='#0a66c2';">
                                                <i class="fa-brands fa-linkedin-in" style="font-size: 1.2rem;"></i>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.85rem;">Non renseigné</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 1.2rem 1.5rem; text-align: center; border-top-right-radius: 16px; border-bottom-right-radius: 16px;">
                                        <a href="cv.php?id=<?= htmlspecialchars($free['id']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, var(--tech-blue), #2563eb); color: white; padding: 8px 16px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: transform 0.3s ease, box-shadow 0.3s ease; box-shadow: 0 4px 15px rgba(59,130,246,0.3);" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';">
                                            <i class="fa-solid fa-file-pdf"></i> Voir CV
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="../assets/theme.js"></script>
</body>
</html>
