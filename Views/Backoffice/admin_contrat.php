<?php $activePage = 'contrats'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Gestion des Contrats · FreelaSkill</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="css/admin.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<!-- Glow décoratif -->
<div class="glow-orb" style="width:500px;height:500px;background:#2563EB;top:-100px;right:-100px;"></div>
<div class="glow-orb" style="width:400px;height:400px;background:#EF4444;bottom:-100px;left:100px;"></div>

<main class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar animate-in">
        <div>
            <div class="admin-breadcrumb">
                <i class="fa-solid fa-house"></i>
                <span class="sep">/</span>
                <span class="current">Gestion des Contrats</span>
            </div>
            <h1 class="admin-page-title">Gestion des <span>Contrats</span></h1>
        </div>
        <div class="topbar-actions">
            <div class="admin-badge">
                <i class="fa-solid fa-user-shield"></i>
                SuperAdmin
            </div>
        </div>
    </div>

    <!-- Menu cards -->
    <div class="menu-grid animate-in delay-1">

        <a href="admin_contrat_form.php" class="menu-card" style="--card-glow: rgba(37,99,235,0.12);">
            <div class="menu-card-icon" style="background:rgba(37,99,235,0.12); color:var(--tech-blue);">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <div>
                <div class="menu-card-title">Créer un Contrat</div>
                <div class="menu-card-desc">Rédigez un nouveau contrat en définissant le budget, le délai, les règles et les conditions d'engagement.</div>
            </div>
            <div class="menu-card-arrow" style="color:var(--tech-blue);">
                Commencer <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

        <a href="admin_contrat_list.php" class="menu-card" style="--card-glow: rgba(16,185,129,0.12);">
            <div class="menu-card-icon" style="background:rgba(16,185,129,0.12); color:#10B981;">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <div>
                <div class="menu-card-title">Liste des Contrats</div>
                <div class="menu-card-desc">Consultez, filtrez et gérez tous les contrats de la plateforme. Exportez en PDF ou archivez.</div>
            </div>
            <div class="menu-card-arrow" style="color:#10B981;">
                Voir la liste <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

        <a href="admin_rules_list.php" class="menu-card" style="--card-glow: rgba(168,85,247,0.12);">
            <div class="menu-card-icon" style="background:rgba(168,85,247,0.12); color:#A855F7;">
                <i class="fa-solid fa-gavel"></i>
            </div>
            <div>
                <div class="menu-card-title">Gestion des Règles</div>
                <div class="menu-card-desc">Définissez et gérez les règles, clauses et conditions qui s'appliquent aux contrats.</div>
            </div>
            <div class="menu-card-arrow" style="color:#A855F7;">
                Gérer les règles <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

        <a href="admin_export_pdf.php?action=export_all" class="menu-card" target="_blank" style="--card-glow: rgba(245,158,11,0.12);">
            <div class="menu-card-icon" style="background:rgba(245,158,11,0.12); color:#F59E0B;">
                <i class="fa-solid fa-file-pdf"></i>
            </div>
            <div>
                <div class="menu-card-title">Export PDF Global</div>
                <div class="menu-card-desc">Exportez la liste complète de tous les contrats en un seul fichier PDF.</div>
            </div>
            <div class="menu-card-arrow" style="color:#F59E0B;">
                Exporter <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

    </div>

</main>
</body>
</html>
