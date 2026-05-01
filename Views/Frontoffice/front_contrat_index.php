<?php
session_start();
if (!isset($_SESSION['user_role'])) { header('Location: front_rules_role.php'); exit; }
$role = $_SESSION['user_role'];
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';
$activePage = 'contrats';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace <?php echo $roleName; ?> — Contrats · FreelaSkill</title>
    <link rel="stylesheet" href="css/front.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="css/front.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="glow-orb" style="width:500px;height:500px;background:#2563EB;top:-100px;right:-100px;"></div>

<main class="admin-main">

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
                <i class="fa-solid <?php echo $isClient ? 'fa-user-tie' : 'fa-laptop-code'; ?>"></i>
                <?php echo htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8'); ?>
            </div>
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
