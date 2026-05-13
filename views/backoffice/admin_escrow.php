<?php
$activePage = 'escrow';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/EscrowService.php';

$pdo    = config::getConnexion();
$escrow = new EscrowService($pdo);
$stats  = $escrow->getStats();
$transactions = $escrow->getAllTransactions();

// Actions admin (remboursement)
$message = '';
$msgType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_escrow'])) {
    $idC = intval($_POST['id_contrat'] ?? 0);
    $act = $_POST['action_escrow'];
    $com = $_POST['commentaire'] ?? '';
    if ($act === 'rembourser' && $idC) {
        $r = $escrow->rembourserPaiement($idC, $com, 'admin');
        $message = $r['message'];
        $msgType = $r['success'] ? 'success' : 'error';
        $transactions = $escrow->getAllTransactions();
        $stats = $escrow->getStats();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Gestion Escrow · FreelaSkill</title>
    <link rel="stylesheet" href="css/admin_v2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="css/admin.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="glow-orb" style="width:500px;height:500px;background:#10B981;top:-150px;right:-150px;"></div>

<main class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar animate-in">
        <div>
            <div class="admin-breadcrumb">
                <i class="fa-solid fa-house"></i>
                <span class="sep">/</span>
                <span class="current">Escrow</span>
            </div>
            <h1 class="admin-page-title">Gestion <span>Escrow</span></h1>
        </div>
        <div class="topbar-actions">
            <a href="admin_contrat_list.php" class="btn btn-secondary">
                <i class="fa-solid fa-file-contract"></i> Contrats
            </a>
            <div class="admin-badge"><i class="fa-solid fa-user-shield"></i> SuperAdmin</div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msgType; ?> animate-in">
            <i class="fa-solid fa-<?php echo $msgType === 'success' ? 'circle-check' : 'circle-exclamation'; ?>"></i>
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php
    // Vérifier si la colonne statut_paiement existe via INFORMATION_SCHEMA
    $checkStmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'contrat' 
        AND COLUMN_NAME = 'statut_paiement'");
    $migrationDone = (int)$checkStmt->fetchColumn() > 0;
    ?>
    <?php if (!$migrationDone): ?>
        <div class="alert animate-in" style="background:rgba(245,158,11,0.12);color:#FBBF24;border:1px solid rgba(245,158,11,0.25);">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <strong>Migration requise</strong> — La base de données n'est pas encore à jour.
                <a href="../../controllers/alter_db.php" target="_blank"
                   style="margin-left:0.75rem;background:rgba(245,158,11,0.2);color:#FBBF24;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.82rem;font-weight:600;text-decoration:none;">
                    <i class="fa-solid fa-database"></i> Exécuter la migration
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="stats-grid animate-in delay-1" style="grid-template-columns:repeat(4,1fr);">

        <div class="stat-card" style="--accent-color:#FBBF24;">
            <div class="stat-info">
                <div class="stat-label">En attente</div>
                <div class="stat-value"><?php echo intval($stats['en_attente']); ?></div>
                <div class="stat-sub">paiements</div>
            </div>
            <div class="stat-icon" style="background:rgba(245,158,11,0.15);color:#FBBF24;border-radius:50%;width:48px;height:48px;">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>

        <div class="stat-card" style="--accent-color:#60A5FA;">
            <div class="stat-info">
                <div class="stat-label">Bloqués</div>
                <div class="stat-value"><?php echo intval($stats['bloque']); ?></div>
                <div class="stat-sub"><?php echo number_format($stats['montant_bloque'] ?? 0, 0, ',', ' '); ?> DT</div>
            </div>
            <div class="stat-icon" style="background:rgba(37,99,235,0.15);color:#60A5FA;border-radius:50%;width:48px;height:48px;">
                <i class="fa-solid fa-lock"></i>
            </div>
        </div>

        <div class="stat-card" style="--accent-color:#34D399;">
            <div class="stat-info">
                <div class="stat-label">Libérés</div>
                <div class="stat-value"><?php echo intval($stats['libere']); ?></div>
                <div class="stat-sub"><?php echo number_format($stats['montant_libere'] ?? 0, 0, ',', ' '); ?> DT</div>
            </div>
            <div class="stat-icon" style="background:rgba(16,185,129,0.15);color:#34D399;border-radius:50%;width:48px;height:48px;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="stat-card" style="--accent-color:#F87171;">
            <div class="stat-info">
                <div class="stat-label">Remboursés</div>
                <div class="stat-value"><?php echo intval($stats['rembourse']); ?></div>
                <div class="stat-sub">litiges résolus</div>
            </div>
            <div class="stat-icon" style="background:rgba(239,68,68,0.15);color:#F87171;border-radius:50%;width:48px;height:48px;">
                <i class="fa-solid fa-rotate-left"></i>
            </div>
        </div>

    </div>

    <!-- Explication du système -->
    <div class="admin-card animate-in delay-1" style="background:rgba(37,99,235,0.05);border-color:rgba(37,99,235,0.2);margin-bottom:1.5rem;">
        <div style="padding:1.25rem 1.5rem;display:flex;align-items:flex-start;gap:1rem;">
            <div style="font-size:2rem;flex-shrink:0;">🔒</div>
            <div>
                <div style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:#60A5FA;margin-bottom:0.5rem;">
                    Système de paiement sécurisé (Escrow)
                </div>
                <div style="font-size:0.88rem;color:#94A3B8;line-height:1.7;">
                    Le budget du contrat est <strong style="color:#CBD5E1;">bloqué en séquestre</strong> dès que le client dépose le paiement.
                    Il est <strong style="color:#34D399;">libéré au freelancer</strong> uniquement après validation du travail par le client.
                    En cas de litige, l'admin peut <strong style="color:#F87171;">rembourser</strong> le client.
                </div>
                <div style="display:flex;gap:1.5rem;margin-top:0.75rem;font-size:0.82rem;">
                    <span style="color:#FBBF24;"><i class="fa-solid fa-clock"></i> En attente → client doit payer</span>
                    <span style="color:#60A5FA;"><i class="fa-solid fa-lock"></i> Bloqué → freelancer travaille</span>
                    <span style="color:#34D399;"><i class="fa-solid fa-check"></i> Libéré → contrat terminé</span>
                    <span style="color:#F87171;"><i class="fa-solid fa-rotate-left"></i> Remboursé → litige résolu</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des transactions -->
    <div class="admin-card animate-in delay-2">
        <div class="admin-card-header">
            <div class="admin-card-title">
                <i class="fa-solid fa-list-check"></i>
                Historique des transactions
                <span style="font-size:0.78rem;font-weight:400;color:var(--text-muted);">(<?php echo count($transactions); ?> transaction<?php echo count($transactions) > 1 ? 's' : ''; ?>)</span>
            </div>
        </div>

        <?php if (empty($transactions)): ?>
            <div style="padding:3rem;text-align:center;color:var(--text-muted);">
                <i class="fa-solid fa-inbox" style="font-size:2rem;display:block;margin-bottom:0.75rem;opacity:0.4;"></i>
                Aucune transaction escrow pour le moment.
            </div>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Contrat</th>
                    <th>Montant</th>
                    <th>Action</th>
                    <th>Avant</th>
                    <th>Après</th>
                    <th>Par</th>
                    <th>Date</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $t): ?>
                    <?php
                    $typeColors = [
                        'depot'          => ['bg'=>'rgba(37,99,235,0.12)',  'color'=>'#60A5FA',  'icon'=>'fa-arrow-down'],
                        'liberation'     => ['bg'=>'rgba(16,185,129,0.12)', 'color'=>'#34D399',  'icon'=>'fa-unlock'],
                        'remboursement'  => ['bg'=>'rgba(239,68,68,0.12)',  'color'=>'#F87171',  'icon'=>'fa-rotate-left'],
                        'blocage'        => ['bg'=>'rgba(245,158,11,0.12)', 'color'=>'#FBBF24',  'icon'=>'fa-lock'],
                    ];
                    $tc = $typeColors[$t['type_action']] ?? ['bg'=>'rgba(100,116,139,0.12)','color'=>'#94A3B8','icon'=>'fa-circle'];
                    ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo htmlspecialchars($t['titre_contrat'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--tech-blue);">
                            <?php echo number_format($t['montant'], 2, ',', ' '); ?> DT
                        </td>
                        <td>
                            <span style="background:<?php echo $tc['bg']; ?>;color:<?php echo $tc['color']; ?>;padding:0.25rem 0.65rem;border-radius:999px;font-size:0.75rem;font-weight:600;display:inline-flex;align-items:center;gap:0.35rem;">
                                <i class="fa-solid <?php echo $tc['icon']; ?>"></i>
                                <?php echo ucfirst($t['type_action']); ?>
                            </span>
                        </td>
                        <td>
                            <?php $ca = EscrowService::getColor($t['statut_avant']); ?>
                            <span style="background:<?php echo $ca['bg']; ?>;color:<?php echo $ca['color']; ?>;padding:0.2rem 0.55rem;border-radius:999px;font-size:0.72rem;font-weight:500;">
                                <?php echo EscrowService::getLabel($t['statut_avant']); ?>
                            </span>
                        </td>
                        <td>
                            <?php $cp = EscrowService::getColor($t['statut_apres']); ?>
                            <span style="background:<?php echo $cp['bg']; ?>;color:<?php echo $cp['color']; ?>;padding:0.2rem 0.55rem;border-radius:999px;font-size:0.72rem;font-weight:600;">
                                <?php echo EscrowService::getLabel($t['statut_apres']); ?>
                            </span>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.85rem;"><?php echo htmlspecialchars($t['effectue_par'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="color:var(--text-muted);font-size:0.82rem;"><?php echo date('d/m/Y H:i', strtotime($t['date_action'])); ?></td>
                        <td style="text-align:right;">
                            <?php if ($t['statut_apres'] === 'bloque'): ?>
                                <button type="button"
                                        onclick="openRefundModal(<?php echo intval($t['id_contrat']); ?>, '<?php echo htmlspecialchars($t['titre_contrat'], ENT_QUOTES); ?>')"
                                        class="btn btn-danger btn-icon" title="Rembourser">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            <?php else: ?>
                                <span style="color:var(--text-muted);font-size:0.75rem;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</main>

<!-- Modal remboursement -->
<div id="refund-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:1000;display:none;align-items:center;justify-content:center;">
    <div style="background:#0f172a;border:1px solid rgba(239,68,68,0.3);border-radius:20px;padding:2rem;max-width:440px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.5);">
        <h3 style="font-family:'Space Grotesk',sans-serif;color:#F87171;margin-bottom:0.5rem;">
            <i class="fa-solid fa-rotate-left"></i> Rembourser le client
        </h3>
        <p style="color:#94A3B8;font-size:0.88rem;margin-bottom:1.25rem;" id="refund-modal-title"></p>
        <form method="POST">
            <input type="hidden" name="action_escrow" value="rembourser">
            <input type="hidden" name="id_contrat" id="refund-id">
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.85rem;color:#94A3B8;display:block;margin-bottom:0.4rem;">Motif du remboursement</label>
                <textarea name="commentaire" rows="3"
                          style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:0.75rem;color:#CBD5E1;font-family:inherit;font-size:0.88rem;resize:none;outline:none;"
                          placeholder="Ex: Travail non livré, litige résolu..."></textarea>
            </div>
            <div style="display:flex;gap:0.75rem;">
                <button type="submit" class="btn btn-danger" style="flex:1;">
                    <i class="fa-solid fa-rotate-left"></i> Confirmer le remboursement
                </button>
                <button type="button" onclick="closeRefundModal()" class="btn btn-secondary">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRefundModal(id, titre) {
    document.getElementById('refund-id').value = id;
    document.getElementById('refund-modal-title').textContent = 'Contrat : ' + titre;
    document.getElementById('refund-modal').style.display = 'flex';
}
function closeRefundModal() {
    document.getElementById('refund-modal').style.display = 'none';
}
document.getElementById('refund-modal').addEventListener('click', function(e) {
    if (e.target === this) closeRefundModal();
});
</script>
</body>
</html>
