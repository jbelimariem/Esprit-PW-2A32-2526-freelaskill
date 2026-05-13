<?php
require_once __DIR__ . '/../../controllers/session.php';
requireLogin();
require_once __DIR__ . '/../../controllers/session.php';
requireLogin();

$role = $_SESSION['user_role'] ?? 'freelancer';
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';
$activePage = 'rules';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace <?php echo $roleName; ?> – Règles · FreelaSkill</title>
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
                <span class="current">Gestion des Règles</span>
            </div>
            <h1 class="admin-page-title">Gestion des <span>Règles</span></h1>
        </div>
        <div class="topbar-actions">
        </div>
    </div>

    <div class="menu-grid animate-in delay-1">

        <a href="front_rules_form.php" class="menu-card" style="--card-glow:rgba(37,99,235,0.12);">
            <div class="menu-card-icon" style="background:rgba(37,99,235,0.12);color:var(--tech-blue);">
                <i class="fa-solid fa-file-pen"></i>
            </div>
            <div>
                <div class="menu-card-title">Créer une Règle</div>
                <div class="menu-card-desc">Ajoutez une nouvelle règle, un tarif ou une exigence qui s'appliquera à vos contrats.</div>
            </div>
            <div class="menu-card-arrow" style="color:var(--tech-blue);">
                Commencer <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

        <a href="front_rules_list.php" class="menu-card" style="--card-glow:rgba(16,185,129,0.12);">
            <div class="menu-card-icon" style="background:rgba(16,185,129,0.12);color:#10B981;">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <div class="menu-card-title">Liste des Règles</div>
                <div class="menu-card-desc">Accédez à l'ensemble de vos règles définies, modifiez-les ou supprimez-les facilement.</div>
            </div>
            <div class="menu-card-arrow" style="color:#10B981;">
                Voir la liste <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

    </div>

</main>
</div>
</body>
</html>
