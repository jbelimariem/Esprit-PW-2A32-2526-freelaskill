<?php
$activePage = 'email';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/EmailService.php';

$pdo     = config::getConnexion();
$email   = new EmailService($pdo);
$stats   = $email->getStats();
$logs    = $email->getLogs(30);

$message = ''; $msgType = 'success';

// Sauvegarder la config SMTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    try {
        $fields = ['smtp_user', 'smtp_password', 'from_name'];
        foreach ($fields as $f) {
            if (isset($_POST[$f])) {
                $stmt = $pdo->prepare("INSERT INTO email_config (cle, valeur) VALUES (:k, :v)
                    ON DUPLICATE KEY UPDATE valeur = :v2");
                $stmt->execute(['k' => $f, 'v' => $_POST[$f], 'v2' => $_POST[$f]]);
            }
        }
        $message = 'Configuration SMTP sauvegardée.';
        $email   = new EmailService($pdo); // recharger
    } catch (PDOException $e) {
        $message = 'Erreur : ' . $e->getMessage();
        $msgType = 'error';
    }
}

// Test d'envoi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $testTo = $_POST['test_to'] ?? '';
    if ($testTo) {
        $fakeContrat = [
            'id_contrat' => 0, 'titre' => 'Contrat Test FreelaSkill',
            'budget' => 500, 'delai' => 30, 'statut' => 'actif'
        ];
        $ok = $email->notifyContratCreated($fakeContrat, $testTo, 'Utilisateur Test');
        $message = $ok ? "Email de test envoyé à $testTo ✓" : "Échec de l'envoi. Vérifiez la configuration.";
        $msgType = $ok ? 'success' : 'error';
    }
}

// Déclencher les alertes délai manuellement
if (isset($_GET['check_delai'])) {
    $sent = $email->checkAndSendDelaiAlerts();
    $message = empty($sent) ? 'Aucune alerte délai à envoyer.' : implode('<br>', $sent);
    $logs = $email->getLogs(30);
    $stats = $email->getStats();
}

$config = [];
try {
    $rows = $pdo->query("SELECT cle, valeur FROM email_config")->fetchAll();
    foreach ($rows as $r) $config[$r['cle']] = $r['valeur'];
} catch (PDOException $e) {
    // Table pas encore créée — afficher le bouton de migration
    $migrationNeeded = true;
}
$migrationNeeded = $migrationNeeded ?? false;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Emails & Notifications · FreelaSkill</title>
    <link rel="stylesheet" href="css/admin_v2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="css/admin.js" defer></script>
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<div class="glow-orb" style="width:400px;height:400px;background:#6366F1;top:-100px;right:-100px;"></div>

<main class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar animate-in">
        <div>
            <div class="admin-breadcrumb">
                <i class="fa-solid fa-house"></i><span class="sep">/</span>
                <span class="current">Emails & Notifications</span>
            </div>
            <h1 class="admin-page-title">Notifications <span>Email</span></h1>
        </div>
        <div class="topbar-actions">
            <a href="?check_delai=1" class="btn btn-warning">
                <i class="fa-solid fa-clock"></i> Vérifier alertes délai
            </a>
            <div class="admin-badge"><i class="fa-solid fa-user-shield"></i> SuperAdmin</div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msgType; ?> animate-in">
            <i class="fa-solid fa-<?php echo $msgType === 'success' ? 'circle-check' : 'circle-exclamation'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($migrationNeeded): ?>
        <div class="alert animate-in" style="background:rgba(239,68,68,0.1);color:#F87171;border:1px solid rgba(239,68,68,0.25);">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <strong>Migration requise</strong> — Les tables email n'existent pas encore.
                <a href="../../controllers/alter_db.php" target="_blank"
                   style="margin-left:0.75rem;background:rgba(239,68,68,0.2);color:#F87171;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.82rem;font-weight:600;text-decoration:none;">
                    <i class="fa-solid fa-database"></i> Exécuter la migration
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- KPIs -->
    <div class="stats-grid animate-in delay-1" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card" style="--accent-color:#6366F1;">
            <div class="stat-info"><div class="stat-label">Total envoyés</div><div class="stat-value"><?php echo intval($stats['total']); ?></div></div>
            <div class="stat-icon" style="background:rgba(99,102,241,0.15);color:#6366F1;border-radius:50%;width:48px;height:48px;"><i class="fa-solid fa-envelope"></i></div>
        </div>
        <div class="stat-card" style="--accent-color:#34D399;">
            <div class="stat-info"><div class="stat-label">Réussis</div><div class="stat-value"><?php echo intval($stats['envoyes']); ?></div></div>
            <div class="stat-icon" style="background:rgba(52,211,153,0.15);color:#34D399;border-radius:50%;width:48px;height:48px;"><i class="fa-solid fa-circle-check"></i></div>
        </div>
        <div class="stat-card" style="--accent-color:#F87171;">
            <div class="stat-info"><div class="stat-label">Échecs</div><div class="stat-value"><?php echo intval($stats['echecs']); ?></div></div>
            <div class="stat-icon" style="background:rgba(248,113,113,0.15);color:#F87171;border-radius:50%;width:48px;height:48px;"><i class="fa-solid fa-circle-xmark"></i></div>
        </div>
        <div class="stat-card" style="--accent-color:#FBBF24;">
            <div class="stat-info"><div class="stat-label">Types d'événements</div><div class="stat-value"><?php echo intval($stats['types']); ?></div></div>
            <div class="stat-icon" style="background:rgba(251,191,36,0.15);color:#FBBF24;border-radius:50%;width:48px;height:48px;"><i class="fa-solid fa-bell"></i></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

        <!-- Config SMTP -->
        <div class="admin-card animate-in delay-1">
            <div class="admin-card-header">
                <div class="admin-card-title"><i class="fa-solid fa-gear"></i> Configuration SMTP</div>
                <?php if ($email->isConfigured()): ?>
                    <span style="background:rgba(16,185,129,0.15);color:#34D399;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.75rem;font-weight:700;">
                        <i class="fa-solid fa-circle-check"></i> Configuré
                    </span>
                <?php else: ?>
                    <span style="background:rgba(245,158,11,0.15);color:#FBBF24;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.75rem;font-weight:700;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Mode simulation
                    </span>
                <?php endif; ?>
            </div>

            <!-- Guide Gmail -->
            <div style="background:rgba(37,99,235,0.06);border:1px solid rgba(37,99,235,0.15);border-radius:10px;padding:1rem;margin-bottom:1.25rem;font-size:0.82rem;color:#94A3B8;line-height:1.7;">
                <div style="font-weight:700;color:#60A5FA;margin-bottom:0.4rem;"><i class="fa-brands fa-google"></i> Comment obtenir un App Password Gmail :</div>
                1. Allez sur <strong style="color:#CBD5E1;">myaccount.google.com</strong><br>
                2. Sécurité → Validation en 2 étapes (activer)<br>
                3. Sécurité → Mots de passe des applications<br>
                4. Créez un mot de passe pour "Mail"<br>
                5. Copiez le code de 16 caractères ci-dessous
            </div>

            <form method="POST">
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Email Gmail (expéditeur)</label>
                    <input type="email" name="smtp_user" class="form-input"
                           value="<?php echo htmlspecialchars($config['smtp_user'] ?? '', ENT_QUOTES); ?>"
                           placeholder="votre.email@gmail.com">
                </div>
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">App Password Gmail (16 caractères)</label>
                    <input type="password" name="smtp_password" class="form-input"
                           value="<?php echo htmlspecialchars($config['smtp_password'] ?? '', ENT_QUOTES); ?>"
                           placeholder="xxxx xxxx xxxx xxxx">
                </div>
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Nom de l'expéditeur</label>
                    <input type="text" name="from_name" class="form-input"
                           value="<?php echo htmlspecialchars($config['from_name'] ?? 'FreelaSkill', ENT_QUOTES); ?>">
                </div>
                <button type="submit" name="save_config" class="btn btn-primary" style="width:100%;">
                    <i class="fa-solid fa-save"></i> Sauvegarder la configuration
                </button>
            </form>

            <!-- Test d'envoi -->
            <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border);">
                <div style="font-size:0.82rem;font-weight:600;color:#94A3B8;margin-bottom:0.75rem;">
                    <i class="fa-solid fa-paper-plane"></i> Tester l'envoi
                </div>
                <form method="POST" style="display:flex;gap:0.5rem;">
                    <input type="email" name="test_to" class="form-input" placeholder="email@test.com" style="flex:1;">
                    <button type="submit" name="test_email" class="btn btn-secondary">
                        <i class="fa-solid fa-paper-plane"></i> Tester
                    </button>
                </form>
            </div>
        </div>

        <!-- Événements configurés -->
        <div class="admin-card animate-in delay-2">
            <div class="admin-card-header">
                <div class="admin-card-title"><i class="fa-solid fa-bell"></i> Événements automatiques</div>
            </div>
            <?php
            $events = [
                ['icon'=>'fa-file-contract', 'color'=>'#2563EB', 'bg'=>'rgba(37,99,235,0.12)',
                 'title'=>'Création de contrat', 'desc'=>'Email au freelancer quand un contrat lui est assigné', 'trigger'=>'Automatique'],
                ['icon'=>'fa-lock', 'color'=>'#10B981', 'bg'=>'rgba(16,185,129,0.12)',
                 'title'=>'Paiement bloqué (Escrow)', 'desc'=>'Email au freelancer quand le client dépose le paiement', 'trigger'=>'Automatique'],
                ['icon'=>'fa-circle-check', 'color'=>'#7C3AED', 'bg'=>'rgba(124,58,237,0.12)',
                 'title'=>'Travail validé', 'desc'=>'Email au freelancer quand le client valide et libère le paiement', 'trigger'=>'Automatique'],
                ['icon'=>'fa-clock', 'color'=>'#F59E0B', 'bg'=>'rgba(245,158,11,0.12)',
                 'title'=>'Alerte délai ⏱️', 'desc'=>'Email J-3, J-2, J-1 avant expiration du contrat', 'trigger'=>'Bouton ci-dessus'],
                ['icon'=>'fa-rotate', 'color'=>'#60A5FA', 'bg'=>'rgba(96,165,250,0.12)',
                 'title'=>'Changement de statut', 'desc'=>'Email au client quand le statut du contrat change', 'trigger'=>'Automatique'],
            ];
            foreach ($events as $ev): ?>
                <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.85rem 0;border-bottom:1px solid var(--border);">
                    <div style="width:36px;height:36px;border-radius:10px;background:<?php echo $ev['bg']; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa-solid <?php echo $ev['icon']; ?>" style="color:<?php echo $ev['color']; ?>;font-size:0.9rem;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-weight:600;color:var(--text-light);font-size:0.88rem;"><?php echo $ev['title']; ?></div>
                        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.15rem;"><?php echo $ev['desc']; ?></div>
                    </div>
                    <span style="font-size:0.68rem;background:rgba(16,185,129,0.12);color:#34D399;padding:0.2rem 0.5rem;border-radius:999px;white-space:nowrap;flex-shrink:0;">
                        <?php echo $ev['trigger']; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Historique des emails -->
    <div class="admin-card animate-in delay-3" style="margin-top:1.5rem;">
        <div class="admin-card-header">
            <div class="admin-card-title">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Historique des emails
                <span style="font-size:0.78rem;font-weight:400;color:var(--text-muted);">(<?php echo count($logs); ?> derniers)</span>
            </div>
        </div>

        <?php if (empty($logs)): ?>
            <div style="padding:3rem;text-align:center;color:var(--text-muted);">
                <i class="fa-solid fa-inbox" style="font-size:2rem;display:block;margin-bottom:0.75rem;opacity:0.4;"></i>
                Aucun email envoyé pour le moment.
                <?php if (!$email->isConfigured()): ?>
                    <div style="margin-top:0.5rem;font-size:0.82rem;">En mode simulation — les emails sont loggés sans être envoyés.</div>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Contrat</th>
                    <th>Type</th>
                    <th>Destinataire</th>
                    <th>Sujet</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log):
                    $typeColors = [
                        'contrat_created'  => ['#2563EB', 'fa-file-contract'],
                        'escrow_bloque'    => ['#10B981', 'fa-lock'],
                        'paiement_libere'  => ['#7C3AED', 'fa-circle-check'],
                        'alerte_delai'     => ['#F59E0B', 'fa-clock'],
                        'statut_change'    => ['#60A5FA', 'fa-rotate'],
                    ];
                    $tc = $typeColors[$log['type_email']] ?? ['#94A3B8', 'fa-envelope'];
                ?>
                    <tr>
                        <td style="font-weight:600;font-size:0.85rem;"><?php echo htmlspecialchars($log['titre_contrat'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <span style="background:<?php echo $tc[0]; ?>22;color:<?php echo $tc[0]; ?>;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.72rem;font-weight:600;display:inline-flex;align-items:center;gap:0.3rem;">
                                <i class="fa-solid <?php echo $tc[1]; ?>"></i>
                                <?php echo str_replace('_', ' ', $log['type_email']); ?>
                            </span>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.82rem;"><?php echo htmlspecialchars($log['to_email'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="font-size:0.82rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($log['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php if ($log['statut'] === 'envoye'): ?>
                                <span style="background:rgba(16,185,129,0.12);color:#34D399;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.72rem;font-weight:600;">✓ Envoyé</span>
                            <?php elseif ($log['statut'] === 'echec'): ?>
                                <span style="background:rgba(239,68,68,0.12);color:#F87171;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.72rem;font-weight:600;">✗ Échec</span>
                            <?php else: ?>
                                <span style="background:rgba(245,158,11,0.12);color:#FBBF24;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.72rem;font-weight:600;">⟳ Simulé</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.8rem;"><?php echo date('d/m/Y H:i', strtotime($log['date_envoi'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</main>
</body>
</html>
