<?php
session_start();
// Admin guard – uncomment when role system is ready:
// if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     header('Location: ../frontoffice/login.php'); exit;
// }

require_once __DIR__ . '/../../controllers/BackofficeController.php';
$userController = new BackofficeController();

$errors  = [];
$success = '';

// ── CREATE (POST) ──────────────────────────────────────────────────────────
$dashboardData = $userController->handleAdminDashboard();
$errors = $dashboardData['errors'];
$activeModal = $dashboardData['form'] ?? '';
$createErrors = $activeModal === 'create' ? $errors : [];
$editErrors = $activeModal === 'update' ? $errors : [];
$fieldError = function ($bag, $field) {
    return $bag[$field] ?? '';
};
$createValues = $activeModal === 'create' ? $_POST : [];
$editValues = $activeModal === 'update' ? $_POST : [];

// ── Flash messages ─────────────────────────────────────────────────────────
$flashMap = [
    'created'  => ['Utilisateur cree', 'Le compte a ete ajoute avec succes.', 'success', 'fa-circle-check'],
    'updated'  => ['Profil mis a jour', 'Les informations du compte ont bien ete enregistrees.', 'success', 'fa-pen-to-square'],
    'ban'      => ['Compte suspendu', "L'utilisateur ne peut plus acceder a la plateforme pour le moment.", 'warning', 'fa-ban'],
    'activate' => ['Compte reactive', "L'utilisateur peut a nouveau acceder a son espace.", 'success', 'fa-bolt'],
    'delete'   => ['Compte supprime', 'Le compte a ete retire definitivement.', 'error', 'fa-trash-can'],
];
$flash = $flashMap[$_GET['msg'] ?? ''] ?? null;

// ── Filters & list ─────────────────────────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$role   = trim($_GET['role']   ?? '');
$status = trim($_GET['status'] ?? '');
$activeFilters = [];

if ($search !== '') {
    $activeFilters[] = 'Recherche: ' . $search;
}
if ($role !== '') {
    $activeFilters[] = 'Role: ' . ucfirst($role);
}
if ($status !== '') {
    $statusLabels = [
        'active' => 'Actif',
        'banned' => 'Suspendu',
        'pending' => 'En attente',
    ];
    $activeFilters[] = 'Statut: ' . ($statusLabels[$status] ?? $status);
}

if ($search !== '' || $role !== '' || $status !== '') {
    $users = $userController->filter($search, $role, $status);
} else {
    $users = $userController->getAll();
}

// ── Stats ──────────────────────────────────────────────────────────────────
$totalUsers       = $userController->countAll();
$totalFreelancers = $userController->countByRole('freelancer');
$totalClients     = $userController->countByRole('client');
$totalBanned      = $userController->countByStatus('banned');

// ── Edit modal pre-fill ────────────────────────────────────────────────────
$editUser = null;
if (!empty($_GET['edit'])) {
    $editUser = $userController->getById((int) $_GET['edit']);
}

if ($activeModal === 'update') {
    $editUser = (object) [
        'id' => (int) ($editValues['edit_id'] ?? 0),
        'prenom' => $editValues['prenom'] ?? '',
        'nom' => $editValues['nom'] ?? '',
        'email' => $editValues['email'] ?? '',
        'bio' => $editValues['bio'] ?? '',
        'role' => $editValues['role'] ?? 'freelancer',
        'status' => $editValues['status'] ?? 'active',
    ];
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users - FreelaSkill</title>
    <meta name="description" content="Tableau de bord administrateur - gestion des utilisateurs FreelaSkill.">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* ── Modal ──────────────────────────────────────── */
        .modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.65);
            backdrop-filter: blur(6px);
            z-index: 1000;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity .25s ease;
        }
        .modal-backdrop.open { opacity: 1; pointer-events: all; }
        .modal {
            background: #0d1525;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 20px;
            width: 100%; max-width: 520px;
            padding: 2rem;
            box-shadow: 0 30px 80px rgba(0,0,0,.6);
            transform: translateY(24px) scale(.97);
            transition: transform .3s cubic-bezier(.4,0,.2,1);
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-backdrop.open .modal { transform: translateY(0) scale(1); }
        .modal-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .modal-title { font-size: 1.2rem; font-weight: 700; color: white; }
        .modal-close {
            background: none; border: none; color: #475569;
            font-size: 1.1rem; cursor: pointer; padding: .3rem .5rem;
            border-radius: 8px; transition: all .2s;
        }
        .modal-close:hover { background: rgba(255,255,255,.06); color: white; }
        .modal .form-group { margin-bottom: 1rem; }
        .modal .form-label {
            display: block; font-size: .82rem; font-weight: 600;
            color: #94a3b8; margin-bottom: .4rem; letter-spacing: .02em;
        }
        .modal .form-input {
            width: 100%; padding: .65rem 1rem;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 10px; color: white;
            font-family: 'Space Grotesk', sans-serif; font-size: .9rem;
            outline: none; box-sizing: border-box;
            transition: border-color .2s;
        }
        .modal .form-input:focus { border-color: var(--tech-blue); }
        .modal select.form-input option { background: #0d1525; }
        .modal-footer { display: flex; gap: .75rem; justify-content: flex-end; margin-top: 1.5rem; }

        /* Password strength */
        .strength-bar {
            height: 4px;
            border-radius: 4px;
            background: rgba(255,255,255,.06);
            margin-top: .5rem;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 4px;
            transition: width .45s cubic-bezier(.4,0,.2,1), background .45s ease;
            width: 0%;
        }
        .password-rules {
            display: flex;
            flex-wrap: nowrap;
            gap: .55rem;
            margin-top: .85rem;
        }
        .password-rule {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .42rem .9rem;
            border-radius: 999px;
            border: 1px solid rgba(71,85,105,.5);
            background: rgba(15,23,42,.7);
            color: #64748b;
            font-size: .78rem;
            font-weight: 500;
            letter-spacing: .01em;
            line-height: 1;
            transition: all .35s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
        }
        .password-rule::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(16,185,129,.15), rgba(5,150,105,.08));
            opacity: 0;
            transition: opacity .35s ease;
        }
        .password-rule .rule-icon {
            font-size: .72rem;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1.5px solid rgba(71,85,105,.6);
            transition: all .35s cubic-bezier(.4,0,.2,1);
            flex-shrink: 0;
        }
        .password-rule .rule-icon i {
            font-size: .6rem;
            color: #475569;
            transition: all .3s;
        }
        .password-rule.is-valid {
            border-color: rgba(16,185,129,.7);
            background: rgba(16,185,129,.12);
            color: #6ee7b7;
            box-shadow: 0 0 0 3px rgba(16,185,129,.1), 0 0 12px rgba(16,185,129,.12);
        }
        .password-rule.is-valid::before { opacity: 1; }
        .password-rule.is-valid .rule-icon {
            background: rgba(16,185,129,.25);
            border-color: rgba(16,185,129,.7);
            animation: rulePop .35s cubic-bezier(.4,0,.2,1);
        }
        .password-rule.is-valid .rule-icon i { color: #10b981; }
        @keyframes rulePop {
            0% { transform: scale(.7); }
            60% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        /* ── Flash ──────────────────────────────────────── */
        .flash {
            display: flex; align-items: flex-start; gap: .9rem;
            padding: .85rem 1.25rem;
            border-radius: 12px; margin-bottom: 1.5rem;
            font-size: .9rem; font-weight: 500;
            animation: slideDown .35s ease;
        }
        .flash-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(255,255,255,.08);
        }
        .flash-copy {
            display: flex;
            flex-direction: column;
            gap: .2rem;
        }
        .flash-title {
            font-size: .92rem;
            font-weight: 700;
            color: #fff;
        }
        .flash-sub {
            font-size: .82rem;
            color: inherit;
            opacity: .9;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .flash-success { background: rgba(16,185,129,.12); border: 1px solid rgba(16,185,129,.35); color: #6ee7b7; }
        .flash-warning { background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.35); color: #fcd34d; }
        .flash-error   { background: rgba(239,68,68,.12);  border: 1px solid rgba(239,68,68,.35);  color: #fca5a5; }

        /* ── Form errors inside modal ───────────────────── */
        .form-errors {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: 10px; padding: .75rem 1rem;
            margin-bottom: 1rem; font-size: .85rem; color: #fca5a5;
        }
        .field-error {
            margin-top: .45rem;
            color: #fca5a5;
            font-size: .8rem;
        }
        .modal .form-input.input-error {
            border-color: rgba(239,68,68,.55);
        }

        /* ── Add btn ────────────────────────────────────── */
        .btn-add {
            display: inline-flex; align-items: center; gap: .5rem;
            background: linear-gradient(135deg, var(--tech-blue), #3b82f6);
            color: white; padding: .65rem 1.4rem;
            border-radius: var(--radius-full); font-size: .88rem;
            font-weight: 600; border: none; cursor: pointer;
            transition: all .2s; text-decoration: none;
            box-shadow: 0 4px 16px rgba(37,99,235,.3);
        }
        .btn-add:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,.4); }

        /* ── Inline edit/ban btns ───────────────────────── */
        .action-btn {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .35rem .75rem; border-radius: 999px;
            font-size: .76rem; font-weight: 600;
            cursor: pointer; border: none; text-decoration: none;
            transition: all .2s; white-space: nowrap;
        }
        .action-btn-edit   { background: rgba(59,130,246,.15); color: #93c5fd; border: 1px solid rgba(59,130,246,.3); }
        .action-btn-edit:hover { background: rgba(59,130,246,.25); }
        .action-btn-ban    { background: rgba(245,158,11,.12); color: #fcd34d; border: 1px solid rgba(245,158,11,.3); }
        .action-btn-ban:hover { background: rgba(245,158,11,.22); }
        .action-btn-ok     { background: rgba(16,185,129,.12); color: #6ee7b7; border: 1px solid rgba(16,185,129,.3); }
        .action-btn-ok:hover { background: rgba(16,185,129,.22); }
        .action-btn-del    { background: rgba(239,68,68,.12); color: #fca5a5; border: 1px solid rgba(239,68,68,.3); }
        .action-btn-del:hover { background: rgba(239,68,68,.22); }
        .user-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            min-width: max-content;
        }
        .user-actions .action-btn {
            width: 38px;
            height: 38px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .user-actions .action-btn i {
            font-size: .95rem;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div style="padding: 0 0.5rem; margin-bottom: 2rem;">
            <div class="logo">
                <i class="fa-solid fa-shapes" style="color: var(--tunisian-red);"></i>
                Freela<span>Skill</span>
            </div>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; letter-spacing: 1px;">Admin Control v1.0</p>
        </div>
        <div class="nav-item active"><i class="fa-solid fa-users-viewfinder"></i> Gestion Users</div>
        <div class="nav-item"><i class="fa-solid fa-network-wired"></i> Flux de Missions</div>
        <div class="nav-item"><i class="fa-solid fa-store"></i> Marketplace</div>
        <div class="nav-item"><i class="fa-solid fa-shield-halved"></i> Securite</div>
        <div class="nav-item"><i class="fa-solid fa-comments"></i> Messagerie</div>
        <div style="margin-top: auto; padding-top: 2rem;">
            <a href="../frontoffice/profile.php" class="btn btn-outline"
               style="width:100%;font-size:.85rem;padding:.75rem;border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;gap:.5rem;">
                <i class="fa-solid fa-globe"></i> Retour au Hub
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-panel">
        <div class="hero-glow" style="top:-100px;right:-100px;opacity:.4;position:fixed;pointer-events:none;
            width:500px;height:500px;background:radial-gradient(circle,rgba(59,130,246,.15),transparent 60%);"></div>

        <!-- HEADER -->
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2.5rem;" class="animate-up">
            <div>
                <h1 style="font-family:'Space Grotesk';font-size:2rem;color:white;margin-bottom:.3rem;">
                    Gestion des <span style="color:var(--tech-blue)">Utilisateurs</span>
                </h1>
                <p style="color:var(--text-muted);font-size:.9rem;">Visualisez, moderez et gerez tous les comptes de la plateforme.</p>
            </div>
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="display:flex;align-items:center;gap:.75rem;background:rgba(255,255,255,0.05);padding:.5rem 1rem;border-radius:var(--radius-full);border:1px solid rgba(255,255,255,.05);">
                    <i class="fa-solid fa-satellite" style="color:var(--tech-green);"></i>
                    <span style="font-size:.85rem;color:var(--text-muted);">Systeme actif</span>
                </div>
                <!-- Add User Button -->
                <button class="btn-add" onclick="openCreateModal()" id="btn-add-user">
                    <i class="fa-solid fa-user-plus"></i> Ajouter un utilisateur
                </button>
            </div>
        </header>

        <!-- FLASH MESSAGE -->
        <?php if ($flash): ?>
            <div class="flash flash-<?php echo $flash[2]; ?>">
                <div class="flash-icon">
                    <i class="fa-solid <?php echo htmlspecialchars($flash[3]); ?>"></i>
                </div>
                <div class="flash-copy">
                    <div class="flash-title"><?php echo htmlspecialchars($flash[0]); ?></div>
                    <div class="flash-sub"><?php echo htmlspecialchars($flash[1]); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- METRICS ROW -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;margin-bottom:2.5rem;" class="animate-up">
            <div class="metric-card">
                <p style="color:var(--text-muted);font-size:.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:.5rem;">Total Utilisateurs</p>
                <h2 style="font-size:2.2rem;color:white;font-family:'JetBrains Mono',monospace;margin-bottom:.3rem;"><?php echo $totalUsers; ?></h2>
                <p style="color:var(--tech-blue);font-size:.82rem;"><i class="fa-solid fa-users"></i> Comptes enregistres</p>
            </div>
            <div class="metric-card">
                <p style="color:var(--text-muted);font-size:.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:.5rem;">Freelancers</p>
                <h2 style="font-size:2.2rem;color:white;font-family:'JetBrains Mono',monospace;margin-bottom:.3rem;"><?php echo $totalFreelancers; ?></h2>
                <p style="color:var(--tech-blue);font-size:.82rem;"><i class="fa-solid fa-laptop-code"></i> Talents actifs</p>
            </div>
            <div class="metric-card">
                <p style="color:var(--text-muted);font-size:.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:.5rem;">Clients</p>
                <h2 style="font-size:2.2rem;color:white;font-family:'JetBrains Mono',monospace;margin-bottom:.3rem;"><?php echo $totalClients; ?></h2>
                <p style="color:#8b5cf6;font-size:.82rem;"><i class="fa-solid fa-building"></i> Entreprises / Particuliers</p>
            </div>
            <div class="metric-card" style="border-color:rgba(239,68,68,.2);">
                <p style="color:var(--text-muted);font-size:.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:.5rem;">Suspendus</p>
                <h2 style="font-size:2.2rem;color:var(--tunisian-red);font-family:'JetBrains Mono',monospace;margin-bottom:.3rem;"><?php echo $totalBanned; ?></h2>
                <p style="color:var(--tunisian-red);font-size:.82rem;"><i class="fa-solid fa-triangle-exclamation"></i> Requiert attention</p>
            </div>
        </div>

        <!-- FILTERS + TABLE -->
        <section class="admin-section animate-up">
            <div class="section-title" style="margin-bottom:1.5rem;">
                <i class="fa-solid fa-users" style="color:var(--tech-blue);"></i>
                Liste des utilisateurs
            </div>

            <!-- Search & Filters -->
            <form method="GET" action="users_dashboard.php" id="admin-filter-form"
                  style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem;">
                <div style="display:flex;align-items:center;gap:.6rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius-md);padding:.6rem 1rem;flex:1;min-width:200px;">
                    <i class="fa-solid fa-magnifying-glass" style="color:#334155;font-size:.9rem;"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Rechercher par nom, email..."
                           style="background:transparent;border:none;outline:none;color:white;font-family:'Space Grotesk',sans-serif;font-size:.9rem;width:100%;">
                </div>
                <select name="role" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);color:var(--text-muted);border-radius:var(--radius-md);padding:.65rem 1rem;font-family:'Space Grotesk',sans-serif;font-size:.85rem;outline:none;">
                    <option value="">Tous les roles</option>
                    <option value="freelancer" <?php echo $role==='freelancer'?'selected':''; ?>>Freelancer</option>
                    <option value="client"     <?php echo $role==='client'    ?'selected':''; ?>>Client</option>
                </select>
                <select name="status" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);color:var(--text-muted);border-radius:var(--radius-md);padding:.65rem 1rem;font-family:'Space Grotesk',sans-serif;font-size:.85rem;outline:none;">
                    <option value="">Tous les statuts</option>
                    <option value="active"  <?php echo $status==='active' ?'selected':''; ?>>Actif</option>
                    <option value="banned"  <?php echo $status==='banned' ?'selected':''; ?>>Suspendu</option>
                    <option value="pending" <?php echo $status==='pending'?'selected':''; ?>>En attente</option>
                </select>
                <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                    <i class="fa-solid fa-filter"></i> Filtrer
                </button>
                <a href="users_dashboard.php" class="btn btn-outline" style="white-space:nowrap;">
                    <i class="fa-solid fa-rotate-left"></i> Reinitialiser
                </a>
            </form>

            <?php if (!empty($activeFilters)): ?>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin:-.75rem 0 1.5rem;">
                    <?php foreach ($activeFilters as $filterLabel): ?>
                        <span style="display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .75rem;border-radius:999px;background:rgba(59,130,246,.12);border:1px solid rgba(59,130,246,.25);color:#93c5fd;font-size:.78rem;font-weight:600;">
                            <i class="fa-solid fa-filter" style="font-size:.7rem;"></i>
                            <?php echo htmlspecialchars($filterLabel); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Statut</th>
                            <th>Membre depuis</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">
                                    <i class="fa-solid fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
                                    Aucun utilisateur ne correspond aux filtres selectionnes.
                                    <?php if (!empty($activeFilters)): ?>
                                        <div style="margin-top:.75rem;font-size:.8rem;color:#93c5fd;">
                                            <?php echo htmlspecialchars(implode(' | ', $activeFilters)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <?php
                                $initials  = strtoupper(mb_substr($u->getPrenom(),0,1) . mb_substr($u->getNom(),0,1));
                                $roleBadge = $u->getRole() === 'freelancer'
                                    ? '<span class="badge badge-freelancer"><i class="fa-solid fa-laptop-code"></i> Freelancer</span>'
                                    : '<span class="badge badge-client"><i class="fa-solid fa-building"></i> Client</span>';
                                if ($u->getStatus() === 'active') {
                                    $statusBadge = '<span class="badge badge-active"><i class="fa-solid fa-circle"></i> Actif</span>';
                                } elseif ($u->getStatus() === 'banned') {
                                    $statusBadge = '<span class="badge badge-banned"><i class="fa-solid fa-ban"></i> Suspendu</span>';
                                } else {
                                    $statusBadge = '<span class="badge badge-pending"><i class="fa-solid fa-clock"></i> Attente</span>';
                                }
                                $since = date('d/m/Y', strtotime($u->getCreatedAt()));
                                ?>
                                <tr>
                                    <td style="font-family:'JetBrains Mono',monospace;font-size:.82rem;color:#475569;">#<?php echo $u->getId(); ?></td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:.75rem;">
                                            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,rgba(37,99,235,.4),rgba(139,92,246,.3));display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0;">
                                                <?php echo $initials; ?>
                                            </div>
                                            <div>
                                                <div style="font-weight:600;color:white;font-size:.9rem;">
                                                    <?php echo htmlspecialchars($u->getPrenom() . ' ' . $u->getNom()); ?>
                                                </div>
                                                <?php if (!empty($u->getBio())): ?>
                                                    <div style="font-size:.75rem;color:var(--text-muted);max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                        <?php echo htmlspecialchars($u->getBio()); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color:var(--text-muted);font-size:.85rem;"><?php echo htmlspecialchars($u->getEmail()); ?></td>
                                    <td><?php echo $roleBadge; ?></td>
                                    <td><?php echo $statusBadge; ?></td>
                                    <td style="color:var(--text-muted);font-size:.82rem;"><?php echo $since; ?></td>
                                    <td>
                                        <div class="user-actions">
                                            <button class="action-btn action-btn-edit"
                                                    onclick="openEdit(<?php echo htmlspecialchars(json_encode($u)); ?>)"
                                                    title="Editer">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <?php if ($u->getStatus() === 'banned'): ?>
                                                <a href="?action=activate&id=<?php echo $u->getId(); ?>"
                                                   class="action-btn action-btn-ok" title="Activer"
                                                   onclick="return confirm('Réactiver ce compte ?');">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=ban&id=<?php echo $u->getId(); ?>"
                                                   class="action-btn action-btn-ban" title="Suspendre"
                                                   onclick="return confirm('Suspendre ce compte ?');">
                                                    <i class="fa-solid fa-ban"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?action=delete&id=<?php echo $u->getId(); ?>"
                                               class="action-btn action-btn-del" title="Supprimer"
                                               onclick="return confirm('Supprimer ce compte définitivement ?');">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Result count -->
            <div style="margin-top:1rem;font-size:.82rem;color:var(--text-muted);">
                <strong style="color:white;"><?php echo count($users); ?></strong>
                utilisateur<?php echo count($users) > 1 ? 's' : ''; ?> affiche<?php echo count($users) > 1 ? 's' : ''; ?>
            </div>
        </section>
    </main>
</div>

<!-- ════════════════════════════════════════════════
     MODAL — CREATE USER
════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="create-modal">
    <div class="modal" role="dialog" aria-labelledby="modal-create-title">
        <div class="modal-header">
            <div class="modal-title" id="modal-create-title">
                <i class="fa-solid fa-user-plus" style="color:var(--tech-blue);margin-right:.5rem;"></i>
                Ajouter un utilisateur
            </div>
            <button class="modal-close" onclick="closeModal('create-modal')" aria-label="Fermer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <?php if (!empty($createErrors['_global'])): ?>
            <div class="form-errors">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?php echo htmlspecialchars($createErrors['_global']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="users_dashboard.php" id="create-form" novalidate>
            <input type="hidden" name="_action" value="create">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                    <label class="form-label" for="c-nom">Nom</label>
                    <?php if ($fieldError($createErrors, 'nom') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($createErrors, 'nom')); ?></div>
                    <?php endif; ?>
                    <input class="form-input<?php echo $fieldError($createErrors, 'nom') !== '' ? ' input-error' : ''; ?>" type="text" id="c-nom" name="nom"
                           placeholder="Ben Ali" value="<?php echo htmlspecialchars($createValues['nom'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="c-prenom">Prenom</label>
                    <?php if ($fieldError($createErrors, 'prenom') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($createErrors, 'prenom')); ?></div>
                    <?php endif; ?>
                    <input class="form-input<?php echo $fieldError($createErrors, 'prenom') !== '' ? ' input-error' : ''; ?>" type="text" id="c-prenom" name="prenom"
                           placeholder="Mohamed" value="<?php echo htmlspecialchars($createValues['prenom'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="c-email">Email</label>
                <?php if ($fieldError($createErrors, 'email') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError($createErrors, 'email')); ?></div>
                <?php endif; ?>
                <input class="form-input<?php echo $fieldError($createErrors, 'email') !== '' ? ' input-error' : ''; ?>" type="text" id="c-email" name="email"
                       placeholder="user@exemple.com" value="<?php echo htmlspecialchars($createValues['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="c-password">Mot de passe</label>
                <?php if ($fieldError($createErrors, 'password') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError($createErrors, 'password')); ?></div>
                <?php endif; ?>
                <input class="form-input<?php echo $fieldError($createErrors, 'password') !== '' ? ' input-error' : ''; ?>" type="password" id="c-password" name="password"
                       placeholder="Minimum 8 caracteres"
                       oninput="updateAdminPasswordStrength(this.value)">
                <div class="strength-bar"><div class="strength-fill" id="admin-strength-fill"></div></div>
                <div class="password-rules">
                    <span class="password-rule" id="admin-rule-length">
                        <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                        8 caracteres min.
                    </span>
                    <span class="password-rule" id="admin-rule-upper">
                        <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                        1 majuscule
                    </span>
                    <span class="password-rule" id="admin-rule-special">
                        <span class="rule-icon"><i class="fa-solid fa-check"></i></span>
                        1 caractere special
                    </span>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                    <label class="form-label" for="c-role">Role</label>
                    <?php if ($fieldError($createErrors, 'role') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($createErrors, 'role')); ?></div>
                    <?php endif; ?>
                    <select class="form-input<?php echo $fieldError($createErrors, 'role') !== '' ? ' input-error' : ''; ?>" id="c-role" name="role">
                        <option value="freelancer" <?php echo (($createValues['role'] ?? '') === 'freelancer') ? 'selected' : ''; ?>>Freelancer</option>
                        <option value="client" <?php echo (($createValues['role'] ?? '') === 'client') ? 'selected' : ''; ?>>Client</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="c-bio">Bio <span style="color:#475569;font-size:.75rem;">(optionnel)</span></label>
                    <input class="form-input" type="text" id="c-bio" name="bio"
                           placeholder="Courte description..." value="<?php echo htmlspecialchars($createValues['bio'] ?? ''); ?>">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('create-modal')">Annuler</button>
                <button type="submit" class="btn btn-primary" id="create-submit-btn">
                    <i class="fa-solid fa-check"></i> Creer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════
     MODAL — EDIT USER
════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="edit-modal">
    <div class="modal" role="dialog" aria-labelledby="modal-edit-title">
        <div class="modal-header">
            <div class="modal-title" id="modal-edit-title">
                <i class="fa-solid fa-pen-to-square" style="color:var(--tech-blue);margin-right:.5rem;"></i>
                Modifier l'utilisateur
            </div>
            <button class="modal-close" onclick="closeModal('edit-modal')" aria-label="Fermer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <?php if (!empty($editErrors['_global'])): ?>
            <div class="form-errors">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?php echo htmlspecialchars($editErrors['_global']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="users_dashboard.php" id="edit-form" novalidate>
            <input type="hidden" name="_action" value="update">
            <input type="hidden" name="edit_id" id="edit-id" value="<?php echo htmlspecialchars((string) ($editValues['edit_id'] ?? '')); ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                    <label class="form-label" for="e-prenom">Prenom</label>
                    <?php if ($fieldError($editErrors, 'prenom') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($editErrors, 'prenom')); ?></div>
                    <?php endif; ?>
                    <input class="form-input<?php echo $fieldError($editErrors, 'prenom') !== '' ? ' input-error' : ''; ?>" type="text" id="e-prenom" name="prenom" value="<?php echo htmlspecialchars($editValues['prenom'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="e-nom">Nom</label>
                    <?php if ($fieldError($editErrors, 'nom') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($editErrors, 'nom')); ?></div>
                    <?php endif; ?>
                    <input class="form-input<?php echo $fieldError($editErrors, 'nom') !== '' ? ' input-error' : ''; ?>" type="text" id="e-nom" name="nom" value="<?php echo htmlspecialchars($editValues['nom'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="e-email">Email</label>
                <?php if ($fieldError($editErrors, 'email') !== ''): ?>
                    <div class="field-error"><?php echo htmlspecialchars($fieldError($editErrors, 'email')); ?></div>
                <?php endif; ?>
                <input class="form-input<?php echo $fieldError($editErrors, 'email') !== '' ? ' input-error' : ''; ?>" type="text" id="e-email" name="email" value="<?php echo htmlspecialchars($editValues['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="e-bio">Bio</label>
                <input class="form-input" type="text" id="e-bio" name="bio" placeholder="Courte description..." value="<?php echo htmlspecialchars($editValues['bio'] ?? ''); ?>">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                    <label class="form-label" for="e-role">Role</label>
                    <?php if ($fieldError($editErrors, 'role') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($editErrors, 'role')); ?></div>
                    <?php endif; ?>
                    <select class="form-input<?php echo $fieldError($editErrors, 'role') !== '' ? ' input-error' : ''; ?>" id="e-role" name="role">
                        <option value="freelancer" <?php echo (($editValues['role'] ?? '') === 'freelancer') ? 'selected' : ''; ?>>Freelancer</option>
                        <option value="client" <?php echo (($editValues['role'] ?? '') === 'client') ? 'selected' : ''; ?>>Client</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="e-status">Statut</label>
                    <?php if ($fieldError($editErrors, 'status') !== ''): ?>
                        <div class="field-error"><?php echo htmlspecialchars($fieldError($editErrors, 'status')); ?></div>
                    <?php endif; ?>
                    <select class="form-input<?php echo $fieldError($editErrors, 'status') !== '' ? ' input-error' : ''; ?>" id="e-status" name="status">
                        <option value="active" <?php echo (($editValues['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Actif</option>
                        <option value="pending" <?php echo (($editValues['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>En attente</option>
                        <option value="banned" <?php echo (($editValues['status'] ?? '') === 'banned') ? 'selected' : ''; ?>>Suspendu</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('edit-modal')">Annuler</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Sauvegarder
                </button>
            </div>
        </form>
    </div>
</div>



<script>
// ── Modal helpers ──────────────────────────────────────────────
function openModal(id) {
    document.getElementById(id).classList.add('open');
    syncBodyScrollLock();
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    syncBodyScrollLock();
}
function syncBodyScrollLock() {
    document.body.style.overflow = document.querySelector('.modal-backdrop.open') ? 'hidden' : '';
}
// Close on backdrop click
document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});
// ESC key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop.open').forEach(m => closeModal(m.id));
    }
});

// ── Open edit modal and pre-fill ──────────────────────────────

function updateAdminPasswordStrength(val) {
    const fill = document.getElementById('admin-strength-fill');
    const checks = {
        length: val.length >= 8,
        upper: /[A-Z]/.test(val),
        special: /[^A-Za-z0-9]/.test(val)
    };
    const score = Object.values(checks).filter(Boolean).length;
    const pct = (score / 3) * 100;
    const color = score <= 1 ? '#ef4444' : score === 2 ? '#f59e0b' : '#10b981';

    document.getElementById('admin-rule-length').classList.toggle('is-valid', checks.length);
    document.getElementById('admin-rule-upper').classList.toggle('is-valid', checks.upper);
    document.getElementById('admin-rule-special').classList.toggle('is-valid', checks.special);

    fill.style.width = pct + '%';
    fill.style.background = color;
}

function clearModalValidation(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        return;
    }

    modal.querySelectorAll('.field-error').forEach(node => {
        node.style.display = 'none';
    });
    modal.querySelectorAll('.input-error').forEach(node => {
        node.classList.remove('input-error');
    });
    const errorBox = modal.querySelector('.form-errors');
    if (errorBox) {
        errorBox.style.display = 'none';
    }
}

function resetCreateForm() {
    const form = document.getElementById('create-form');

    form.reset();
    clearModalValidation('create-modal');
    updateAdminPasswordStrength('');
    document.getElementById('c-nom').focus();
}

function openCreateModal() {
    resetCreateForm();
    openModal('create-modal');
}



function openEdit(user) {
    clearModalValidation('edit-modal');
    document.getElementById('edit-id').value     = user.id;
    document.getElementById('e-prenom').value    = user.prenom;
    document.getElementById('e-nom').value       = user.nom;
    document.getElementById('e-email').value     = user.email;
    document.getElementById('e-bio').value       = user.bio || '';
    document.getElementById('e-role').value      = user.role;
    document.getElementById('e-status').value    = user.status;
    openModal('edit-modal');
}

// ── Auto-open modal on validation error ────────────────
<?php if ($activeModal === 'create'): ?>
openModal('create-modal');
<?php elseif ($activeModal === 'update'): ?>
openModal('edit-modal');
<?php endif; ?>

updateAdminPasswordStrength(document.getElementById('c-password').value);

// ── Flash auto-dismiss ────────────────────────────────────────
const flash = document.querySelector('.flash');
if (flash) setTimeout(() => flash.style.opacity = '0', 4000);
</script>

</body>
</html>



