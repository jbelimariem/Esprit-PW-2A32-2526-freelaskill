<?php
require_once __DIR__ . '/../../controllers/session.php';
requireLogin();
require_once __DIR__ . '/../../controllers/session.php';
requireLogin();

$role = $_SESSION['user_role'] ?? 'freelancer';
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';
$activePage = 'contrats';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace <?php echo $roleName; ?> – Dashboard Contrats</title>
    <link rel="stylesheet" href="../assets/style.css?v=7">
    <link rel="stylesheet" href="css/front.css?v=1778626722">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="css/front.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="marketplace-layout">

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<main class="mkt-main" style="padding-top:1.5rem; position:relative;">


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
        </div>
    </div>

    <div class="menu-grid animate-in delay-1">

        <a href="front_contrat_form.php" class="menu-card" style="--card-glow:rgba(37,99,235,0.12);">
            <div class="menu-card-icon" style="background:rgba(37,99,235,0.12);color:var(--tech-blue);">
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

        <a href="front_contrat_list.php" class="menu-card" style="--card-glow:rgba(16,185,129,0.12);">
            <div class="menu-card-icon" style="background:rgba(16,185,129,0.12);color:#10B981;">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <div>
                <div class="menu-card-title">Liste des Contrats</div>
                <div class="menu-card-desc">Consultez, filtrez et gérez tous vos contrats. Exportez en PDF ou modifiez-les.</div>
            </div>
            <div class="menu-card-arrow" style="color:#10B981;">
                Voir la liste <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

    </div>

</main>
</body>
</html>
