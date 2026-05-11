<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport FreelaSkill - <?= $dateExport ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: white; color: #111; font-size: 13px; }

        .header {
            background: #0a0a0a;
            color: white;
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-logo { font-size: 1.5rem; font-weight: bold; }
        .header-logo span { color: #2563eb; }
        .header-meta { font-size: 0.8rem; color: #94a3b8; text-align: right; }

        .content { padding: 2rem; }

        h2 {
            font-size: 1rem;
            color: #0a0a0a;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 0.4rem;
            margin: 1.5rem 0 1rem;
        }

        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }
        .stat-val  { font-size: 2rem; font-weight: 700; }
        .stat-label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0.2rem; }
        .blue  { color: #2563eb; }
        .green { color: #22c55e; }
        .red   { color: #e70013; }
        .gray  { color: #64748b; }

        /* Donut (texte uniquement pour PDF) */
        .msg-summary {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .msg-row {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.85rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .msg-dot {
            width: 12px; height: 12px;
            border-radius: 50%; flex-shrink: 0;
        }
        .dot-green { background: #22c55e; }
        .dot-gray  { background: #64748b; }
        .dot-red   { background: #e70013; }
        .msg-info-label { font-size: 0.75rem; color: #64748b; }
        .msg-info-val   { font-size: 1.1rem; font-weight: 700; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
        th {
            background: #f1f5f9;
            padding: 0.6rem 0.85rem;
            text-align: left;
            font-size: 0.72rem;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
        }
        td { padding: 0.6rem 0.85rem; border-bottom: 1px solid #f1f5f9; font-size: 0.82rem; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }

        .badge {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 600;
        }
        .badge-active   { background: #dcfce7; color: #15803d; }
        .badge-archived { background: #f1f5f9; color: #475569; }
        .badge-groupe   { background: #ede9fe; color: #7c3aed; }

        /* Flagged */
        .flagged-item {
            border: 1px solid #fee2e2;
            border-left: 4px solid #e70013;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            background: #fff5f5;
        }
        .flagged-from { font-weight: 600; color: #e70013; font-size: 0.8rem; margin-bottom: 0.3rem; }
        .flagged-text { font-size: 0.82rem; color: #374151; margin-bottom: 0.3rem; font-style: italic; }
        .flagged-date { font-size: 0.7rem; color: #9ca3af; }

        .footer {
            margin-top: 2rem;
            padding: 1rem 2rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 0.72rem;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        /* Print button - hidden in print */
        .print-bar {
            background: #2563eb;
            color: white;
            padding: 0.75rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .print-bar p { font-size: 0.85rem; }
        .btn-print {
            background: white;
            color: #2563eb;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .btn-back {
            background: transparent;
            color: white;
            border: 1px solid rgba(255,255,255,0.4);
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
        }

        @media print {
            .print-bar { display: none; }
            body { font-size: 11px; }
            .stats-grid { grid-template-columns: repeat(4,1fr); }
        }
    </style>
</head>
<body>

<!-- Barre d'impression -->
<div class="print-bar">
    <p><i>📄</i> Rapport FreelaSkill — Prêt à imprimer ou enregistrer en PDF</p>
    <div style="display:flex;gap:0.75rem;">
        <a href="messagerie_index.php?page=admin" class="btn-back">← Retour</a>
        <button class="btn-print" onclick="window.print()">🖨️ Imprimer / Enregistrer PDF</button>
    </div>
</div>

<!-- En-tête du rapport -->
<div class="header">
    <div class="header-logo">Freela<span>Skill</span> · Rapport Admin</div>
    <div class="header-meta">
        Généré le <?= $dateExport ?><br>
        Module 5 — Messagerie / Collaboration
    </div>
</div>

<div class="content">

    <!-- Statistiques globales -->
    <h2>📊 Statistiques globales</h2>
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-val blue"><?= $totalConvs ?></div>
            <div class="stat-label">Conversations</div>
        </div>
        <div class="stat-box">
            <div class="stat-val green"><?= $activeConvs ?></div>
            <div class="stat-label">Actives</div>
        </div>
        <div class="stat-box">
            <div class="stat-val blue"><?= $totalMessages ?></div>
            <div class="stat-label">Messages total</div>
        </div>
        <div class="stat-box">
            <div class="stat-val red"><?= $countFlagged ?></div>
            <div class="stat-label">Signalés</div>
        </div>
    </div>

    <!-- Répartition des messages -->
    <h2>💬 Répartition des messages</h2>
    <div class="msg-summary">
        <div class="msg-row">
            <div class="msg-dot dot-green"></div>
            <div>
                <div class="msg-info-label">Messages normaux</div>
                <div class="msg-info-val green"><?= $countNormal ?></div>
            </div>
        </div>
        <div class="msg-row">
            <div class="msg-dot dot-gray"></div>
            <div>
                <div class="msg-info-label">Messages supprimés</div>
                <div class="msg-info-val gray"><?= $countDeleted ?></div>
            </div>
        </div>
        <div class="msg-row">
            <div class="msg-dot dot-red"></div>
            <div>
                <div class="msg-info-label">Messages signalés</div>
                <div class="msg-info-val red"><?= $countFlagged ?></div>
            </div>
        </div>
    </div>

    <!-- Toutes les conversations -->
    <h2>🗂️ Toutes les conversations (<?= count($allConversations) ?>)</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom / Type</th>
                <th>Participants</th>
                <th>Statut</th>
                <th>Messages</th>
                <th>Supprimés</th>
                <th>Signalés</th>
                <th>Date création</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allConversations as $conv):
                $data     = json_decode($conv['titre'] ?? '', true);
                $isGroupe = is_array($data) && !empty($data['groupe']);
                $nom      = $isGroupe ? ($data['nom'] ?? 'Groupe') : ($conv['titre'] ?: 'Conversation #' . $conv['id_conversation']);
                $membres  = $isGroupe ? implode(', ', $data['membres'] ?? []) : $conv['id_user1'] . ' / ' . $conv['id_user2'];
            ?>
                <tr>
                    <td>#<?= $conv['id_conversation'] ?></td>
                    <td>
                        <?= htmlspecialchars($nom) ?>
                        <?php if ($isGroupe): ?>
                            <span class="badge badge-groupe">Groupe</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($membres) ?></td>
                    <td>
                        <span class="badge <?= $conv['statut'] === 'active' ? 'badge-active' : 'badge-archived' ?>">
                            <?= $conv['statut'] === 'active' ? 'Active' : 'Archivée' ?>
                        </span>
                    </td>
                    <td><?= $conv['total_messages'] ?? 0 ?></td>
                    <td><?= $conv['deleted_messages'] ?? 0 ?></td>
                    <td><?= $conv['flagged_messages_count'] ?? 0 ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($conv['date_creation'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Messages signalés -->
    <h2>🚩 Messages signalés (<?= count($flaggedMessages) ?>)</h2>
    <?php if (empty($flaggedMessages)): ?>
        <p style="color:#64748b;font-style:italic;">Aucun message signalé.</p>
    <?php else: ?>
        <?php foreach ($flaggedMessages as $msg): ?>
            <div class="flagged-item">
                <div class="flagged-from">
                    Utilisateur #<?= $msg['id_expediteur'] ?> — Conversation #<?= $msg['id_conversation'] ?>
                </div>
                <div class="flagged-text">"<?= htmlspecialchars(substr($msg['contenu'], 0, 200)) ?><?= strlen($msg['contenu']) > 200 ? '...' : '' ?>"</div>
                <div class="flagged-date"><?= date('d/m/Y à H:i', strtotime($msg['date_envoi'])) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<div class="footer">
    <span>FreelaSkill — Module 5 : Messagerie / Collaboration</span>
    <span>Rapport généré le <?= $dateExport ?></span>
</div>

<script>
// Auto-focus sur le bouton imprimer
window.onload = function() {
    // On ne lance pas l'impression automatiquement, l'utilisateur clique
};
</script>
</body>
</html>