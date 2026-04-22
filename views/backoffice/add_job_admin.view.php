<?php
// views/backoffice/add_job_admin.view.php — Template: Admin Ajouter une offre
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une mission | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="admin.css?v=<?= time() ?>">
</head>
<body class="page-anim">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
            <nav class="admin-nav">
                <a href="dashboard.php" class="admin-nav-item"><i class="fa-solid fa-briefcase"></i> Missions</a>
                <a href="add_job_admin.php" class="admin-nav-item active"><i class="fa-solid fa-plus-circle"></i> Ajouter</a>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-content" style="max-width: 1200px;">
                <h1 class="admin-page-title">Publier une <span>Nouvelle Mission</span></h1>
                
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
                    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
                    textarea.form-input { resize: vertical; min-height: 140px; }
                </style>

                <div class="glass-card" style="padding:3rem; border-radius:18px;">
                    <form action="add_job_admin.php" method="POST" novalidate>
                        <div class="form-group">
                            <label class="form-label">Titre de l'offre <span>*</span></label>
                            <input name="titre" type="text" class="form-input" placeholder="Ex. Développeur React.js" value="<?= htmlspecialchars($data['titre'] ?? '') ?>">
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Budget (DT) <span>*</span></label>
                                <input name="budget" type="text" class="form-input" placeholder="Ex. 1500" value="<?= htmlspecialchars($data['budget'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Délai <span>*</span></label>
                                <input name="delai" type="text" class="form-input" placeholder="Ex. 15 jours" value="<?= htmlspecialchars($data['delai'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Statut de la mission <span>*</span></label>
                                <select name="statut" class="form-input" style="background: #0f172a;">
                                    <option value="pending" <?= ($data['statut'] ?? '') === 'pending' ? 'selected' : '' ?>>En attente</option>
                                    <option value="approved" <?= ($data['statut'] ?? '') === 'approved' ? 'selected' : '' ?>>Approuvée</option>
                                    <option value="rejected" <?= ($data['statut'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejetée</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Compétences <span>*</span></label>
                                <input name="competences" type="text" class="form-input" placeholder="Ex. React.js, PHP" value="<?= htmlspecialchars($data['competences'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description <span>*</span></label>
                            <textarea name="description" rows="6" class="form-input"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                        </div>

                        <div style="margin-top: 2rem; display: flex; gap: 1rem; border-top: 1px solid var(--border); padding-top: 2rem;">
                            <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem;">Publier la Mission</button>
                            <a href="dashboard.php" class="btn btn-outline" style="padding: 1rem 2rem;">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
