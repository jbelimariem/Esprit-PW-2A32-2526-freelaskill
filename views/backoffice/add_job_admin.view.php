<?php
// views/backoffice/add_job_admin.view.php — Template: Admin Ajouter une offre
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Mission — Admin | FreelaSkill</title>
    <meta name="description" content="Publier une nouvelle mission sur la plateforme FreelaSkill.">
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin_v2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="../assets/theme.js" defer></script>
    <style>
        .form-group  { margin-bottom: 1.5rem; }
        .form-label  { display:block; margin-bottom:.5rem; color:#94A3B8; font-size:.88rem; font-weight:600; letter-spacing:.02em; }
        .form-label span { color: var(--tunisian-red); margin-left: 2px; }
        .form-input  {
            width: 100%;
            background: var(--input-bg, rgba(255,255,255,0.04));
            border: 1px solid var(--input-border, var(--border));
            border-radius: 12px;
            padding: 0.85rem 1.1rem;
            color: var(--text-strong);
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.95rem;
            outline: none;
            box-sizing: border-box;
            transition: all .2s;
        }
        .form-input:focus  { border-color: var(--tech-blue); background: rgba(59,130,246,0.05); box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }
        .form-input.error  { border-color: rgba(239,68,68,.5); }
        select.form-input option { background: #0f172a; }
        textarea.form-input { resize: vertical; min-height: 160px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .field-error { color: #fca5a5; font-size: .8rem; margin-top: .4rem; display:flex; align-items:center; gap:.3rem; }
        
        /* Glowing Blue Button */
        .btn-add {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
            position: relative;
            overflow: hidden;
        }
        .btn-add:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.6), 0 0 15px rgba(59, 130, 246, 0.4);
            background: linear-gradient(135deg, #60a5fa, #2563eb);
        }
        .btn-add::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .btn-add:hover::after {
            opacity: 1;
        }
        
        .premium-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }
    </style>
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
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3rem;" class="animate-up">
            <div>
                <h1 class="admin-page-title">Publier une <span style="color:var(--tech-blue)">Nouvelle Mission</span></h1>
                <p style="color:var(--text-muted);font-size:.95rem;">Interface de création rapide pour les offres de mission.</p>
            </div>
            <div style="display:flex;align-items:center;gap:1.25rem;">
                <button type="button" class="theme-toggle" data-theme-toggle>
                    <i class="fa-solid fa-sun" data-theme-icon></i>
                    <span data-theme-label>Jour</span>
                </button>
                <a href="admin_missions.php" class="btn btn-outline" style="border-radius: 12px; padding: 0.8rem 1.5rem;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i> Annuler
                </a>
            </div>
        </header>

        <!-- FORM CARD -->
        <section class="admin-section animate-up">
            <div class="premium-card">
                <form action="add_job_admin.php" method="POST" novalidate>

                    <div class="form-group">
                        <label class="form-label">Titre de la mission <span>*</span></label>
                        <input name="titre" type="text" class="form-input <?= isset($errors['titre']) ? 'error' : '' ?>"
                               placeholder="Ex. Senior Full-stack Developer (Remote)"
                               value="<?= htmlspecialchars($data['titre'] ?? '') ?>">
                        <?php if (isset($errors['titre'])): ?>
                            <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['titre'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Budget estimé (DT) <span>*</span></label>
                            <div style="position: relative;">
                                <input name="budget" type="text" class="form-input <?= isset($errors['budget']) ? 'error' : '' ?>"
                                       placeholder="Ex. 2500"
                                       value="<?= htmlspecialchars($data['budget'] ?? '') ?>">
                                <span style="position: absolute; right: 1.2rem; top: 50%; transform: translateY(-50%); color: #64748b; font-weight: 600; pointer-events: none;">DT</span>
                            </div>
                            <?php if (isset($errors['budget'])): ?>
                                <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['budget'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Délai d'exécution <span>*</span></label>
                            <input name="delai" type="text" class="form-input <?= isset($errors['delai']) ? 'error' : '' ?>"
                                   placeholder="Ex. 1 mois, 15 jours..."
                                   value="<?= htmlspecialchars($data['delai'] ?? '') ?>">
                            <?php if (isset($errors['delai'])): ?>
                                <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['delai'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Statut initial <span>*</span></label>
                            <select name="statut" class="form-input">
                                <option value="approved" <?= ($data['statut'] ?? '') === 'approved' ? 'selected' : '' ?>>Approuvée (Directement visible)</option>
                                <option value="pending"  <?= ($data['statut'] ?? '') === 'pending'  ? 'selected' : '' ?>>En attente (Modération)</option>
                                <option value="rejected" <?= ($data['statut'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejetée</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Compétences clés <span>*</span></label>
                            <input name="competences" type="text" class="form-input <?= isset($errors['competences']) ? 'error' : '' ?>"
                                   placeholder="Séparez par des virgules (ex: PHP, React, SQL)"
                                   value="<?= htmlspecialchars($data['competences'] ?? '') ?>">
                            <?php if (isset($errors['competences'])): ?>
                                <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['competences'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description détaillée <span>*</span></label>
                        <textarea name="description" class="form-input <?= isset($errors['description']) ? 'error' : '' ?>"
                                  placeholder="Décrivez les objectifs, les livrables et les attentes..."><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['description'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top:3rem; display:flex; gap:1.25rem; border-top:1px solid rgba(255,255,255,0.05); padding-top:2.5rem;">
                        <button type="submit" class="btn-add" style="padding:1rem 3.5rem; font-size: 1rem; border-radius: 14px;">
                            <i class="fa-solid fa-plus-circle" style="margin-right: 10px;"></i> Publier la Mission
                        </button>
                        <a href="admin_missions.php" class="btn btn-outline" style="padding:1rem 2.5rem; font-size: 1rem; border-radius: 14px; background: rgba(255,255,255,0.03);">
                            Annuler
                        </a>
                    </div>

                </form>
            </div>
        </section>

    </main>
</div>

</body>
</html>
