<?php
// views/backoffice/edit_job_admin.view.php — Template: Admin Modifier une offre
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mission | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="admin.css?v=<?= time() ?>">
</head>
<body class="page-anim">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="logo-container">
                <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
                <div class="admin-version">Admin Control v1.0</div>
            </div>
            <nav class="admin-nav">
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-users-viewfinder"></i> Gestion Users</a>
                <a href="dashboard.php" class="admin-nav-item active"><i class="fa-solid fa-sitemap"></i> Flux de Missions</a>
                <a href="admin_freelancers.php" class="admin-nav-item"><i class="fa-solid fa-user-tie"></i> Freelancers</a>
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-store"></i> Marketplace <i class="fa-solid fa-chevron-right arrow"></i></a>
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-shield-halved"></i> Securite</a>
                <a href="#" class="admin-nav-item"><i class="fa-solid fa-comment-dots"></i> Messagerie</a>
            </nav>
            <div class="admin-sidebar-user">
                <div class="avatar">A</div>
                <div class="info">
                    <div class="name">Admin</div>
                    <div class="role">Superviseur</div>
                </div>
                <a href="../frontoffice/home.php" class="logout-btn" title="Quitter"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="admin-main">
            <div class="admin-content" style="max-width: 1200px;">
                <h1 class="admin-page-title">Modifier la <span>Mission #<?= $offre->getId() ?></span></h1>
                <div class="glass-card">
                    <form action="edit_job_admin.php?id=<?= $offre->getId() ?>" method="POST" novalidate>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div style="grid-column: span 2;">
                                <label>Titre</label>
                                <input type="text" name="titre" value="<?= htmlspecialchars($offre->getTitre()) ?>" style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,0.03); color:white;">
                            </div>
                            <div>
                                <label>Budget (DT)</label>
                                <input type="text" name="budget" value="<?= htmlspecialchars($offre->getBudget()) ?>" style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,0.03); color:white;">
                            </div>
                            <div>
                                <label>Statut</label>
                                <select name="statut" style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid var(--border); background:rgba(2,6,23,0.9); color:white;">
                                    <option value="pending" <?= $offre->getStatut()==='pending'?'selected':'' ?>>En attente</option>
                                    <option value="approved" <?= $offre->getStatut()==='approved'?'selected':'' ?>>Approuvée</option>
                                    <option value="rejected" <?= $offre->getStatut()==='rejected'?'selected':'' ?>>Rejetée</option>
                                </select>
                            </div>
                            <div>
                                <label>Délai</label>
                                <input type="text" name="delai" value="<?= htmlspecialchars($offre->getDelai()) ?>" style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,0.03); color:white;">
                            </div>
                            <div style="grid-column: span 2;">
                                <label>Description</label>
                                <textarea name="description" rows="5" style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,0.03); color:white;"><?= htmlspecialchars($offre->getDescription()) ?></textarea>
                            </div>
                        </div>
                        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">Sauvegarder</button>
                            <a href="dashboard.php" class="btn btn-outline">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
