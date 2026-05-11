<?php
// views/backoffice/edit_job_admin.view.php — Template: Admin Modifier une offre
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Mission — Admin | FreelaSkill</title>
    <meta name="description" content="Modifier une mission existante sur la plateforme FreelaSkill.">
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin.css">
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
        select.form-input option { background: #0f172a; }
        textarea.form-input { resize: vertical; min-height: 160px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        
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
            text-decoration: none;
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
        .mission-id-badge {
            background: rgba(59, 130, 246, 0.1);
            color: var(--tech-blue);
            padding: 4px 12px;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            border: 1px solid rgba(59, 130, 246, 0.2);
            margin-left: 1rem;
        }
    </style>
</head>
<body class="page-anim">

<div class="hero-glow"></div>
<div class="hero-glow-2"></div>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div style="padding: 0 0.5rem; margin-bottom: 2.5rem;">
            <div class="logo">
                <i class="fa-solid fa-shapes" style="color: #3b82f6;"></i>
                Freela<span>Skill</span>
            </div>
            <p style="font-size: 0.72rem; color: #475569; margin-top: 0.5rem; letter-spacing: 1.2px; font-weight: 600; text-transform: uppercase;">Admin Control v1.0</p>
        </div>

        <a href="users_dashboard.php" class="nav-item" style="text-decoration:none;"><i class="fa-solid fa-users-viewfinder"></i> Gestion Users</a>
        <a href="admin_missions.php" class="nav-item active" style="text-decoration:none;"><i class="fa-solid fa-network-wired"></i> Flux de Missions</a>
        <a href="admin_freelancers.php" class="nav-item" style="text-decoration:none;"><i class="fa-solid fa-user-tie"></i> Freelancers</a>
        <a href="admin_contrat.php" class="nav-item" style="text-decoration:none;"><i class="fa-solid fa-file-signature"></i> Contrats</a>

        <div class="nav-item-wrapper">
            <a href="dashboard.php" class="nav-item" style="text-decoration:none;">
                <i class="fa-solid fa-store"></i> Marketplace
                <i class="fa-solid fa-chevron-right" style="margin-left:auto; font-size:0.7rem; opacity:0.5;"></i>
            </a>
            <div class="submenu">
                <div class="submenu-title">Marketplace Admin</div>
                <a href="dashboard.php"         class="submenu-item"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="produits.php"           class="submenu-item"><i class="fa-solid fa-box"></i> Gestion Produits</a>
                <a href="mes_achats.php"         class="submenu-item"><i class="fa-solid fa-user-tag"></i> Mes produits admin</a>
                <a href="pending_products.php"   class="submenu-item"><i class="fa-solid fa-clock"></i> Validation Produits</a>
                <a href="ajouter_produit.php"    class="submenu-item"><i class="fa-solid fa-plus"></i> Ajouter Produit</a>
                <a href="liste_categories.php"   class="submenu-item"><i class="fa-solid fa-list"></i> Liste Catégories</a>
                <a href="ajouter_categorie.php"  class="submenu-item"><i class="fa-solid fa-folder-plus"></i> Ajouter Catégorie</a>
                <a href="liste_commandes.php"    class="submenu-item"><i class="fa-solid fa-cart-shopping"></i> Commandes</a>
            </div>
        </div>

        <div class="nav-item" style="opacity:.4;cursor:not-allowed;"><i class="fa-solid fa-shield-halved"></i> Securite</div>
        <div class="nav-item" style="opacity:.4;cursor:not-allowed;"><i class="fa-solid fa-comments"></i> Messagerie</div>

        <div style="margin-top: auto; padding-top: 2rem;">
            <a href="../frontoffice/home.php" class="btn btn-outline"
               style="width:100%;font-size:.85rem;padding:.85rem;border-radius:14px;display:flex;align-items:center;justify-content:center;gap:.6rem;color:#f87171;border-color:rgba(239,68,68,0.15); background: rgba(239,68,68,0.05);">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Quitter l'Admin
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="main-panel">

        <!-- HEADER -->
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3rem;" class="animate-up">
            <div>
                <h1 class="admin-page-title">Modifier la <span style="color:var(--tech-blue)">Mission</span> <span class="mission-id-badge">#<?= $offre->getId() ?></span></h1>
                <p style="color:var(--text-muted);font-size:.95rem;">Mettez à jour les informations de l'offre sélectionnée.</p>
            </div>
            <div style="display:flex;align-items:center;gap:1.25rem;">
                <button type="button" class="theme-toggle" data-theme-toggle>
                    <i class="fa-solid fa-sun" data-theme-icon></i>
                    <span data-theme-label>Jour</span>
                </button>
                <a href="admin_missions.php" class="btn btn-outline" style="border-radius: 12px; padding: 0.8rem 1.5rem;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i> Retour
                </a>
            </div>
        </header>

        <!-- FORM CARD -->
        <section class="admin-section animate-up">
            <div class="premium-card">
                <form action="edit_job_admin.php?id=<?= $offre->getId() ?>" method="POST" novalidate>

                    <div class="form-group">
                        <label class="form-label">Titre de la mission</label>
                        <input name="titre" type="text" class="form-input"
                               value="<?= htmlspecialchars($offre->getTitre()) ?>">
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Budget estimé (DT)</label>
                            <div style="position: relative;">
                                <input name="budget" type="text" class="form-input"
                                       value="<?= htmlspecialchars($offre->getBudget()) ?>">
                                <span style="position: absolute; right: 1.2rem; top: 50%; transform: translateY(-50%); color: #64748b; font-weight: 600; pointer-events: none;">DT</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Statut actuel</label>
                            <select name="statut" class="form-input">
                                <option value="pending" <?= $offre->getStatut()==='pending'?'selected':'' ?>>En attente (Modération)</option>
                                <option value="approved" <?= $offre->getStatut()==='approved'?'selected':'' ?>>Approuvée (Visible)</option>
                                <option value="rejected" <?= $offre->getStatut()==='rejected'?'selected':'' ?>>Rejetée</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Délai d'exécution</label>
                        <input name="delai" type="text" class="form-input"
                               value="<?= htmlspecialchars($offre->getDelai()) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description complète</label>
                        <textarea name="description" class="form-input"
                                  placeholder="Détails de la mission..."><?= htmlspecialchars($offre->getDescription()) ?></textarea>
                    </div>

                    <div style="margin-top:3rem; display:flex; gap:1.25rem; border-top:1px solid rgba(255,255,255,0.05); padding-top:2.5rem;">
                        <button type="submit" class="btn-add" style="padding:1rem 3.5rem; font-size: 1rem; border-radius: 14px;">
                            <i class="fa-solid fa-save" style="margin-right: 10px;"></i> Enregistrer les modifications
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
