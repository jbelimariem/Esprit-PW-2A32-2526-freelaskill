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
            <div class="admin-content" style="max-width: 900px;">
                <h1 class="admin-page-title">Créer une <span>Nouvelle Mission</span></h1>
                <div class="glass-card">
                    <form action="add_job_admin.php" method="POST" novalidate>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div style="grid-column: span 2;">
                                <label>Titre</label>
                                <input type="text" name="titre" value="<?= htmlspecialchars($data['titre'] ?? '') ?>" style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,0.03); color:white;">
                            </div>
                            <div>
                                <label>Budget (DT)</label>
                                <input type="text" name="budget" value="<?= htmlspecialchars($data['budget'] ?? '') ?>" style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,0.03); color:white;">
                            </div>
                            <div>
                                <label>Délai</label>
                                <select name="delai" style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid var(--border); background:rgba(2,6,23,0.9); color:white;">
                                    <?php foreach (["1 semaine","2 semaines","1 mois","2 mois","3 mois","6 mois"] as $d): ?>
                                    <option value="<?= $d ?>" <?= ($data['delai'] ?? '') === $d ? 'selected' : '' ?>><?= $d ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="grid-column: span 2;">
                                <label>Description</label>
                                <textarea name="description" rows="5" style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,0.03); color:white;"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">Publier</button>
                            <a href="dashboard.php" class="btn btn-outline">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
