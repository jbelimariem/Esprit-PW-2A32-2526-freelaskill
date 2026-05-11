<?php
// views/frontoffice/edit_job.view.php — Template: Modifier une offre
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'offre — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/theme-light.css?v=<?= time() ?>">
    <style>
        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; margin-bottom: .5rem; color: #94A3B8; font-size: .9rem; font-weight: 500; }
        .form-label span { color: var(--tunisian-red); margin-left: 2px; }
        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.85rem 1.1rem;
            color: white;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.95rem;
            outline: none;
            transition: var(--transition);
        }
        .form-input:focus { background: rgba(59,130,246,0.05); border-color: var(--tech-blue); box-shadow: 0 0 0 4px rgba(59,130,246,0.12); }
        .form-input.error { border-color: rgba(239,68,68,0.5); background: rgba(239,68,68,0.03); }
        .error-msg { color: var(--tunisian-red); font-size: .8rem; margin-top: .4rem; display: flex; align-items: center; gap: .3rem; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        textarea.form-input { resize: vertical; min-height: 140px; }
    <link rel="stylesheet" href="../assets/theme-light.css?v=<?= time() ?>">
    <style>
        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; margin-bottom: .5rem; color: #94A3B8; font-size: .9rem; font-weight: 500; }
        .form-label span { color: var(--tunisian-red); margin-left: 2px; }
        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.85rem 1.1rem;
            color: white;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.95rem;
            outline: none;
            transition: var(--transition);
        }
        .form-input:focus { background: rgba(59,130,246,0.05); border-color: var(--tech-blue); box-shadow: 0 0 0 4px rgba(59,130,246,0.12); }
        .form-input.error { border-color: rgba(239,68,68,0.5); background: rgba(239,68,68,0.03); }
        .error-msg { color: var(--tunisian-red); font-size: .8rem; margin-top: .4rem; display: flex; align-items: center; gap: .3rem; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        textarea.form-input { resize: vertical; min-height: 140px; }
        select.form-input { cursor: pointer; }
        select.form-input option { background: #0f172a; }
    </style>
</head>
<body class="page-anim">

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
        <li><a href="<?= (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? '/freelaskill/messagerie_index.php?page=admin' : '/freelaskill/messagerie_index.php?page=conversations' ?>" class="<?= (strpos($_SERVER['PHP_SELF'], 'essagerie') !== false) ? 'active' : '' ?>">Messagerie</a></li>
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
                <a href="missions.php" class="nav-item ">
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
        

<section class="hero-banner">
    <div class="hero-glow"></div>
    <div class="hero-content">
        <div class="hero-tag"><i class="fa-solid fa-pen"></i> Modification</div>
        <h1 class="hero-title">Modifier <span>votre offre</span></h1>
    </div>
</section>

<div class="page-body" style="padding: 2rem 1rem; display: block;">
    <div style="max-width:1200px; margin:0 auto; width:100%;">

        <?php if (!empty($errors)): ?>
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:var(--radius-md); padding:1rem 1.25rem; margin-bottom:1.5rem; color:var(--tunisian-red);">
            <strong>Veuillez corriger les erreurs :</strong>
            <ul style="margin-top:.5rem;">
                <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="product-card" style="opacity:1; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
            <div class="card-body" style="padding:2.5rem;">
                <form id="edit-form" action="edit_job.php?id=<?= $offre->getId() ?>" method="POST" novalidate>
                    <div class="form-group">
                        <label class="form-label" for="titre">Titre de l'offre <span>*</span></label>
                        <input id="titre" name="titre" type="text" class="form-input" placeholder="Titre de l'offre" value="<?= htmlspecialchars($offre->getTitre()) ?>">
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="budget">Budget (DT) <span>*</span></label>
                            <input id="budget" name="budget" type="text" class="form-input" placeholder="Ex. 1500" value="<?= htmlspecialchars($offre->getBudget()) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="delai">Délai <span>*</span></label>
                            <input id="delai" name="delai" type="text" class="form-input" placeholder="Ex. 15 jours" value="<?= htmlspecialchars($offre->getDelai()) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="competences">Compétences <span>*</span></label>
                        <input id="competences" name="competences" type="text" class="form-input" placeholder="Ex. React.js, PHP" value="<?= htmlspecialchars($offre->getCompetences()) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Description <span>*</span></label>
                        <textarea id="description" name="description" rows="6" class="form-input"><?= htmlspecialchars($offre->getDescription()) ?></textarea>
                    </div>

                    <div style="display:flex; gap:1rem; border-top:1px solid var(--border); padding-top:1.5rem;">
                        <button type="submit" class="btn-cart" style="width:auto; padding:.9rem 2.5rem;">Sauvegarder</button>
                        <a href="missions.php" style="color:var(--text-muted); text-decoration:none;">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
<script src="../assets/theme.js"></script>
</body>
</html>
