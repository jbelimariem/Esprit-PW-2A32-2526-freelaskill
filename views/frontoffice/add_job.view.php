<?php
// views/frontoffice/add_job.view.php — Template: Ajouter une offre
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une offre — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
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

<nav style="position: sticky; top: 0; width: 100%; z-index: 100; padding: 0 2rem;">
            <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
            <ul class="nav-links">
                <li><a href="home.php">Accueil</a></li>
                <li><a href="home.php" class="active">Client</a></li>
                <li><a href="freelancer_home.php">Freelancers</a></li>
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
                <a href="add_job.php" class="nav-item active">
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
        <div class="hero-tag"><i class="fa-solid fa-plus"></i> Nouvelle offre</div>
        <h1 class="hero-title">Publiez votre offre <span>en quelques minutes</span></h1>
    </div>
</section>

<div class="page-body" style="padding: 2rem 1rem; display: block;">
    <div style="max-width:1200px; margin:0 auto; width:100%;">

        <div class="product-card" style="opacity:1; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
            <div class="card-body" style="padding:3rem;">
                <form id="add-form" action="add_job.php" method="POST" novalidate>
                    <div class="form-group">
                        <label class="form-label" for="titre">Titre de l'offre <span>*</span></label>
                        <?php if (isset($errors['titre'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['titre'] ?></div><?php endif; ?>
                        <input id="titre" name="titre" type="text" class="form-input <?= isset($errors['titre']) ? 'error' : '' ?>" placeholder="Ex. Développeur React.js" value="<?= htmlspecialchars($data['titre'] ?? '') ?>">
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="budget">Budget (DT) <span>*</span></label>
                            <?php if (isset($errors['budget'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['budget'] ?></div><?php endif; ?>
                            <input id="budget" name="budget" type="text" class="form-input <?= isset($errors['budget']) ? 'error' : '' ?>" placeholder="Ex. 1500" value="<?= htmlspecialchars($data['budget'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="delai">Délai <span>*</span></label>
                            <?php if (isset($errors['delai'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['delai'] ?></div><?php endif; ?>
                            <input id="delai" name="delai" type="text" class="form-input <?= isset($errors['delai']) ? 'error' : '' ?>" placeholder="Ex. 15 jours" value="<?= htmlspecialchars($data['delai'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="competences">Compétences <span>*</span></label>
                        <?php if (isset($errors['competences'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['competences'] ?></div><?php endif; ?>
                        <input id="competences" name="competences" type="text" class="form-input <?= isset($errors['competences']) ? 'error' : '' ?>" placeholder="Ex. React.js, PHP" value="<?= htmlspecialchars($data['competences'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Description <span>*</span></label>
                        <?php if (isset($errors['description'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['description'] ?></div><?php endif; ?>
                        <textarea id="description" name="description" rows="8" class="form-input <?= isset($errors['description']) ? 'error' : '' ?>"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                    </div>

                    <div style="display:flex; gap:1rem; border-top:1px solid var(--border); padding-top:2rem; margin-top:1rem;">
                        <button type="submit" class="btn-cart" style="width:auto; padding:1rem 3rem;">Publier l'offre</button>
                        <a href="home.php" class="btn-cart" style="width:auto; padding:1rem 2rem; background:rgba(255,255,255,0.05); color:white; text-decoration:none; display:flex; align-items:center; justify-content:center; border:1px solid var(--border);">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
</body>
</html>
