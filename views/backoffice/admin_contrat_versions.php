<?php
$activePage = 'contrats';
require_once __DIR__ . '/../../controllers/versionController.php';

if (!$contrat) { header('Location: admin_contrat_list.php'); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique — <?php echo htmlspecialchars($contrat['titre'], ENT_QUOTES, 'UTF-8'); ?> · FreelaSkill</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin_v2.css">
    <link rel="stylesheet" href="css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="css/admin.js" defer></script>
    <style>
        /* Diff styles */
        .diff-added   { background: rgba(52,211,153,0.15); color: #34D399; border-left: 3px solid #34D399; padding: 0.5rem 0.75rem; border-radius: 0 6px 6px 0; }
        .diff-removed { background: rgba(248,113,113,0.15); color: #F87171; border-left: 3px solid #F87171; padding: 0.5rem 0.75rem; border-radius: 0 6px 6px 0; text-decoration: line-through; }
        .diff-unchanged { color: var(--text-muted); padding: 0.5rem 0.75rem; }
        .version-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 1.25rem; margin-bottom: 1rem; transition: border-color 0.2s; }
        .version-card:hover { border-color: rgba(37,99,235,0.3); }
        .version-badge { display: inline-flex; align-items: center; gap: 0.3rem; background: rgba(37,99,235,0.12); color: var(--tech-blue); padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; }
        .diff-field-label { font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.5rem; }
        .diff-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        .diff-col-header { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; }
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<main class="admin-main">

    <div class="admin-topbar animate-in">
        <div>
            <div class="admin-breadcrumb">
                <i class="fa-solid fa-house"></i>
                <span class="sep">/</span>
                <a href="admin_contrat_list.php">Contrats</a>
                <span class="sep">/</span>
                <a href="admin_contrat_list.php"><?php echo htmlspecialchars($contrat['titre'], ENT_QUOTES, 'UTF-8'); ?></a>
                <span class="sep">/</span>
                <span class="current">Historique</span>
            </div>
            <h1 class="admin-page-title">Historique des <span>Versions</span></h1>
        </div>
        <div class="topbar-actions">
            <a href="admin_contrat_list.php" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
            <div class="admin-badge"><i class="fa-solid fa-user-shield"></i> SuperAdmin</div>
        </div>
    </div>

    <?php if (empty($versions)): ?>
        <div class="admin-card animate-in">
            <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                <i class="fa-solid fa-clock-rotate-left" style="font-size:2.5rem;display:block;margin-bottom:1rem;opacity:0.3;"></i>
                <h3 style="color:var(--text-light);margin-bottom:0.5rem;">Aucune version enregistrée</h3>
                <p style="font-size:0.88rem;">Les versions seront créées automatiquement à chaque modification du contrat.</p>
            </div>
        </div>
    <?php else: ?>

        <!-- Contrat actuel -->
        <div class="admin-card animate-in delay-1">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="fa-solid fa-file-contract"></i>
                    Version actuelle
                    <span class="version-badge"><i class="fa-solid fa-star"></i> Courante</span>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;">
                <div>
                    <div class="diff-field-label">Titre</div>
                    <div style="color:var(--text-light);font-weight:600;"><?php echo htmlspecialchars($contrat['titre'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div>
                    <div class="diff-field-label">Budget</div>
                    <div style="color:var(--tech-blue);font-weight:600;"><?php echo number_format($contrat['budget'], 2, ',', ' '); ?> DT</div>
                </div>
                <div>
                    <div class="diff-field-label">Délai</div>
                    <div style="color:var(--text-light);"><?php echo intval($contrat['delai']); ?> jours</div>
                </div>
                <div>
                    <div class="diff-field-label">Statut</div>
                    <span class="badge badge-<?php echo htmlspecialchars($contrat['statut'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $contrat['statut'])); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Comparaison avec version sélectionnée -->
        <?php if ($action === 'compare' && isset($oldVersion)): ?>
            <?php
            // Créer une ContratVersion depuis le contrat actuel pour la comparaison
            $currentVersion = ContratVersion::fromContrat($contrat, 0, 'current');
            $changes = $oldVersion->diff($currentVersion);
            ?>
            <div class="admin-card animate-in delay-2">
                <div class="admin-card-header">
                    <div class="admin-card-title">
                        <i class="fa-solid fa-code-compare"></i>
                        Comparaison — Version <?php echo $oldVersion->getVersionNumber(); ?> vs Actuelle
                    </div>
                    <a href="admin_contrat_versions.php?action=history&id_contrat=<?php echo $idContrat; ?>" class="btn btn-secondary" style="font-size:0.82rem;padding:0.4rem 0.9rem;">
                        <i class="fa-solid fa-list"></i> Voir tout l'historique
                    </a>
                </div>

                <?php if (empty($changes)): ?>
                    <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                        <i class="fa-solid fa-equals" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;color:#34D399;"></i>
                        Aucune différence — les versions sont identiques.
                    </div>
                <?php else: ?>
                    <?php foreach ($changes as $field => $change): ?>
                        <div style="margin-bottom:1.25rem;">
                            <div class="diff-field-label"><?php echo htmlspecialchars($change['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="diff-grid">
                                <div>
                                    <div class="diff-col-header" style="color:#F87171;">
                                        <i class="fa-solid fa-minus"></i> Version <?php echo $oldVersion->getVersionNumber(); ?>
                                    </div>
                                    <div class="diff-removed">
                                        <?php echo nl2br(htmlspecialchars(substr($change['old'], 0, 300), ENT_QUOTES, 'UTF-8')); ?>
                                        <?php if (strlen($change['old']) > 300): ?><span style="opacity:0.5;">...</span><?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="diff-col-header" style="color:#34D399;">
                                        <i class="fa-solid fa-plus"></i> Version actuelle
                                    </div>
                                    <div class="diff-added">
                                        <?php echo nl2br(htmlspecialchars(substr($change['new'], 0, 300), ENT_QUOTES, 'UTF-8')); ?>
                                        <?php if (strlen($change['new']) > 300): ?><span style="opacity:0.5;">...</span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div style="margin-top:1rem;padding:0.75rem 1rem;background:rgba(37,99,235,0.06);border-radius:10px;font-size:0.82rem;color:var(--text-muted);">
                        <i class="fa-solid fa-circle-info" style="color:var(--tech-blue);margin-right:0.4rem;"></i>
                        <?php echo count($changes); ?> champ(s) modifié(s) entre la version <?php echo $oldVersion->getVersionNumber(); ?> et la version actuelle.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Liste de toutes les versions -->
        <div class="admin-card animate-in delay-3">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Historique complet
                    <span style="font-size:0.78rem;font-weight:400;color:var(--text-muted);">(<?php echo count($versions); ?> version<?php echo count($versions) > 1 ? 's' : ''; ?>)</span>
                </div>
            </div>

            <?php foreach ($versions as $v): ?>
                <div class="version-card">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                        <div style="flex:1;">
                            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;flex-wrap:wrap;">
                                <span class="version-badge">
                                    <i class="fa-solid fa-code-branch"></i> v<?php echo $v->getVersionNumber(); ?>
                                </span>
                                <span class="badge badge-<?php echo htmlspecialchars($v->getStatut(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $v->getStatut())); ?>
                                </span>
                                <span style="font-size:0.78rem;color:var(--text-muted);">
                                    <i class="fa-solid fa-clock"></i>
                                    <?php echo date('d/m/Y à H:i', strtotime($v->getDateVersion())); ?>
                                </span>
                                <span style="font-size:0.78rem;color:var(--text-muted);">
                                    <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($v->getModifiePar(), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:0.75rem;">
                                <div>
                                    <div class="diff-field-label">Titre</div>
                                    <div style="font-size:0.88rem;color:var(--text-light);font-weight:500;"><?php echo htmlspecialchars($v->getTitre(), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div>
                                    <div class="diff-field-label">Budget</div>
                                    <div style="font-size:0.88rem;color:var(--tech-blue);font-weight:600;"><?php echo number_format($v->getBudget(), 2, ',', ' '); ?> DT</div>
                                </div>
                                <div>
                                    <div class="diff-field-label">Délai</div>
                                    <div style="font-size:0.88rem;color:var(--text-light);"><?php echo $v->getDelai(); ?> jours</div>
                                </div>
                                <div>
                                    <div class="diff-field-label">Freelancer</div>
                                    <div style="font-size:0.85rem;color:var(--text-muted);"><?php echo htmlspecialchars($v->getFreelanceInfo() ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;gap:0.5rem;flex-shrink:0;">
                            <a href="admin_contrat_versions.php?action=compare&id_contrat=<?php echo $idContrat; ?>&id_version=<?php echo $v->getIdVersion(); ?>"
                               class="btn btn-secondary" style="font-size:0.8rem;padding:0.45rem 0.9rem;" title="Comparer avec la version actuelle">
                                <i class="fa-solid fa-code-compare"></i> Comparer
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($v->getDescription())): ?>
                        <details style="margin-top:0.75rem;">
                            <summary style="font-size:0.78rem;color:var(--text-muted);cursor:pointer;user-select:none;">
                                <i class="fa-solid fa-chevron-right" style="font-size:0.65rem;margin-right:0.3rem;"></i>
                                Voir la description
                            </summary>
                            <div style="margin-top:0.5rem;padding:0.75rem;background:rgba(255,255,255,0.02);border-radius:8px;font-size:0.85rem;color:var(--text-muted);line-height:1.6;border:1px solid var(--border);">
                                <?php echo nl2br(htmlspecialchars(substr($v->getDescription(), 0, 500), ENT_QUOTES, 'UTF-8')); ?>
                                <?php if (strlen($v->getDescription()) > 500): ?><span style="opacity:0.5;">...</span><?php endif; ?>
                            </div>
                        </details>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>
</body>
</html>
