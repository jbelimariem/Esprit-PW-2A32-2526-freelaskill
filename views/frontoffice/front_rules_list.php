<?php
session_start();
if (!isset($_SESSION['user_role'])) { header('Location: front_rules_index.php'); exit; }
require_once __DIR__ . '/../../controllers/ruleController.php';
$role     = $_SESSION['user_role'];
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';
$activePage = 'rules';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace <?php echo $roleName; ?> &ndash; Mes Règles &middot; FreelaSkill</title>
    <link rel="stylesheet" href="css/front.css?v=4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        window.API_BASE = '/freelaskill/controllers/apiController.php';
    </script>
    <script src="css/front.js" defer></script>
    <script src="../assets/api.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="glow-orb" style="width:450px;height:450px;background:#A855F7;top:-100px;right:-100px;"></div>

<main class="admin-main" style="padding-top:1.5rem;">

    <!-- Topbar -->
    <div class="animate-in" style="margin-bottom:2rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border);">
        <div class="admin-breadcrumb">
            <i class="fa-solid fa-house"></i>
            <span class="sep">/</span>
            <a href="front_rules_index.php">Règles</a>
            <span class="sep">/</span>
            <span class="current">Liste</span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;margin-top:0.25rem;">
            <h1 class="admin-page-title">Liste des <span>Règles</span></h1>
            <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                <a href="front_rules_index.php" class="btn btn-secondary" style="padding:0.5rem 0.9rem;font-size:0.82rem;">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </a>
                <a href="front_rules_form.php" class="btn btn-primary" style="padding:0.5rem 0.9rem;font-size:0.82rem;">
                    <i class="fa-solid fa-plus"></i> Nouvelle règle
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success animate-in">
            <i class="fa-solid fa-circle-check"></i>
            <?php
                $msgs = [
                    'create' => 'Règle créée.',
                    'update' => 'Règle mise à jour.',
                    'delete' => 'Règle supprimée.',
                    'toggle' => 'Statut de la règle modifié.',
                ];
                echo $msgs[$_GET['success']] ?? 'Action réalisée.';
            ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <?php
    $totalRules  = count($rules);
    $activeRules = count(array_filter($rules, fn($r) => $r['statut'] === 'actif'));
    $inactRules  = $totalRules - $activeRules;
    ?>
    <div class="stats-grid animate-in delay-1" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.5rem;">
        <div class="stat-card" style="--accent-color:#A855F7;">
            <div class="stat-info"><div class="stat-label">Total Règles</div><div class="stat-value"><?php echo $totalRules; ?></div></div>
            <div class="stat-icon" style="background:rgba(168,85,247,0.15);color:#A855F7;border-radius:50%;width:48px;height:48px;"><i class="fa-solid fa-gavel"></i></div>
        </div>
        <div class="stat-card" style="--accent-color:#34D399;">
            <div class="stat-info"><div class="stat-label">Actives</div><div class="stat-value"><?php echo $activeRules; ?></div></div>
            <div class="stat-icon" style="background:rgba(52,211,153,0.15);color:#34D399;border-radius:50%;width:48px;height:48px;"><i class="fa-solid fa-circle-check"></i></div>
        </div>
        <div class="stat-card" style="--accent-color:#F87171;">
            <div class="stat-info"><div class="stat-label">Inactives</div><div class="stat-value"><?php echo $inactRules; ?></div></div>
            <div class="stat-icon" style="background:rgba(248,113,113,0.15);color:#F87171;border-radius:50%;width:48px;height:48px;"><i class="fa-solid fa-circle-xmark"></i></div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="admin-card animate-in delay-2">
        <div class="admin-card-header">
            <div class="admin-card-title">
                <i class="fa-solid fa-table-list"></i>
                Toutes les règles
                <span style="font-size:0.78rem;font-weight:400;color:var(--text-muted);">(<?php echo $totalRules; ?> règle<?php echo $totalRules > 1 ? 's' : ''; ?>)</span>
            </div>
            <a href="front_rules_form.php" class="btn btn-primary" style="padding:0.5rem 1rem;font-size:0.82rem;">
                <i class="fa-solid fa-plus"></i> Ajouter
            </a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Valeur</th>
                    <th>Contrat associé</th>
                    <th>Statut</th>
                    <th>Créé le</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rules)): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:3rem;">
                        <i class="fa-solid fa-inbox" style="font-size:2rem;display:block;margin-bottom:0.75rem;opacity:0.4;"></i>
                        Aucune règle trouvée.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($rules as $rule): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo htmlspecialchars($rule['titre'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span style="background:rgba(37,99,235,0.1);color:var(--tech-blue);padding:0.2rem 0.6rem;border-radius:999px;font-size:0.78rem;font-weight:500;">
                                    <?php echo htmlspecialchars($rule['type'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td style="color:var(--text-muted);font-size:0.88rem;"><?php echo htmlspecialchars($rule['valeur'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="color:var(--text-muted);font-size:0.88rem;"><?php echo htmlspecialchars($rule['titre_contrat'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge <?php echo $rule['statut'] === 'actif' ? 'badge-actif-rule' : 'badge-inactif'; ?>">
                                    <?php echo ucfirst($rule['statut']); ?>
                                </span>
                            </td>
                            <td style="color:var(--text-muted);font-size:0.85rem;"><?php echo date('d/m/Y', strtotime($rule['date_creation'])); ?></td>
                            <td>
                                <div style="display:flex;gap:0.4rem;justify-content:flex-end;">
                                    <a href="front_rules_form.php?action=edit&id=<?php echo intval($rule['id_rule']); ?>" class="btn btn-secondary btn-icon" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                                    <a href="front_rules_list.php?action=toggle&id=<?php echo intval($rule['id_rule']); ?>" class="btn btn-warning btn-icon" title="Changer statut"><i class="fa-solid fa-toggle-on"></i></a>
                                    <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="margin:0;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo intval($rule['id_rule']); ?>">
                                        <button type="submit" class="btn btn-danger btn-icon" onclick="return confirm('Supprimer cette règle ?');" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>
</body>
</html>
