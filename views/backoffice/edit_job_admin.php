<?php
// views/backoffice/edit_job_admin.php — Admin: Modifier une offre

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/JobOffer.php';

$model  = new JobOffer();
$id     = (int)($_GET['id'] ?? 0);
$offre  = $model->getById($id);
$errors = [];

if (!$offre) {
    header('Location: dashboard.php'); exit;
}

$data = $offre;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statut = trim($_POST['statut'] ?? 'pending');
    if (!in_array($statut, ['pending','approved','rejected'])) $statut = 'pending';

    $data = [
        'titre'       => trim($_POST['titre']       ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'competences' => trim($_POST['competences'] ?? ''),
        'budget'      => trim($_POST['budget']       ?? ''),
        'delai'       => trim($_POST['delai']        ?? ''),
        'statut'      => $statut,
        'id'          => $offre['id'],
        'date_creation' => $offre['date_creation'],
    ];

    if (empty($data['titre']))                    { $errors['titre']       = "Le titre est obligatoire."; }
    elseif (strlen($data['titre']) < 5)           { $errors['titre']       = "Min. 5 caractères."; }
    if (empty($data['description']))              { $errors['description'] = "La description est obligatoire."; }
    elseif (strlen($data['description']) < 20)    { $errors['description'] = "Min. 20 caractères."; }
    if (empty($data['competences']))              { $errors['competences'] = "Champ obligatoire."; }
    if (empty($data['budget'])||!is_numeric($data['budget'])) { $errors['budget'] = "Budget numérique requis."; }
    elseif ((float)$data['budget'] <= 0)          { $errors['budget']      = "Budget > 0 requis."; }
    if (empty($data['delai']))                    { $errors['delai']       = "Champ obligatoire."; }

    if (empty($errors)) {
        $model->update($id, $data);
        header('Location: dashboard.php?success=updated'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier offre #<?= $offre['id'] ?> — Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<aside class="sidebar">
    <div class="logo">
        <i class="fa-solid fa-briefcase"></i>
        Core<span>Panel</span>
        <small>Admin Jobs v1.0</small>
    </div>
    <div class="nav-section-title">Navigation</div>
    <a href="dashboard.php" class="nav-item" id="sidebar-dashboard"><i class="fa-solid fa-gauge-high"></i> Tableau de bord</a>
    <a href="add_job_admin.php" class="nav-item" id="sidebar-add"><i class="fa-solid fa-plus"></i> Ajouter une offre</a>
    <div class="sidebar-footer">
        <a href="../frontoffice/home.php" class="nav-item" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07);">
            <i class="fa-solid fa-globe"></i> Interface Client
        </a>
    </div>
</aside>

<main class="main-panel">

    <div class="topbar">
        <div>
            <h1 class="topbar-title">Modifier <span>offre #<?= $offre['id'] ?></span></h1>
            <p style="color:var(--text-muted); font-size:.82rem; margin-top:.2rem;">
                Publiée le <?= date('d/m/Y à H:i', strtotime($offre['date_creation'])) ?>
            </p>
        </div>
        <div style="display:flex; gap:.75rem;">
            <a href="detail_job_admin.php?id=<?= $offre['id'] ?>" id="view-detail-btn" class="btn btn-outline">
                <i class="fa-solid fa-eye"></i> Voir détail
            </a>
            <a href="dashboard.php" class="btn btn-outline" id="back-dashboard-edit">
                <i class="fa-solid fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:var(--radius-md); padding:1rem 1.25rem; margin-bottom:1.5rem; color:var(--tunisian-red); font-size:.88rem;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <strong style="margin-left:.4rem;">Erreurs :</strong>
        <ul style="margin-top:.5rem; padding-left:1.5rem;">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form id="admin-edit-form" action="edit_job_admin.php?id=<?= $offre['id'] ?>" method="POST" novalidate>
        <div class="form-section animate-fade-up">
            <h3 style="color:white; font-size:1rem; margin-bottom:1.25rem; padding-bottom:.75rem; border-bottom:1px solid rgba(255,255,255,0.06);">
                <i class="fa-solid fa-pen-to-square" style="color:#F59E0B;"></i> Modifier les informations
            </h3>

            <div class="form-group">
                <label class="form-label" for="edit-titre">Titre <span>*</span></label>
                <input id="edit-titre" name="titre" type="text" class="form-input <?= isset($errors['titre'])?'error':'' ?>"
                       value="<?= htmlspecialchars($data['titre']) ?>" minlength="5" maxlength="255" required>
                <?php if (isset($errors['titre'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['titre']) ?></div><?php endif; ?>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="edit-budget">Budget (DT) <span>*</span></label>
                    <input id="edit-budget" name="budget" type="number" min="1" step="0.01"
                           class="form-input <?= isset($errors['budget'])?'error':'' ?>"
                           value="<?= htmlspecialchars($data['budget']) ?>" required>
                    <?php if (isset($errors['budget'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['budget']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-delai">Délai <span>*</span></label>
                    <select id="edit-delai" name="delai" class="form-input <?= isset($errors['delai'])?'error':'' ?>" required>
                        <option value="">Sélectionnez</option>
                        <?php foreach (["1 semaine","2 semaines","1 mois","2 mois","3 mois","6 mois","Plus de 6 mois"] as $d): ?>
                        <option value="<?= $d ?>" <?= $data['delai']===$d?'selected':'' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['delai'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['delai']) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit-competences">Compétences requises <span>*</span></label>
                <input id="edit-competences" name="competences" type="text"
                       class="form-input <?= isset($errors['competences'])?'error':'' ?>"
                       value="<?= htmlspecialchars($data['competences']) ?>" required>
                <?php if (isset($errors['competences'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['competences']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit-description">Description <span>*</span></label>
                <textarea id="edit-description" name="description" rows="6"
                          class="form-input <?= isset($errors['description'])?'error':'' ?>"
                          minlength="20" maxlength="2000" required><?= htmlspecialchars($data['description']) ?></textarea>
                <?php if (isset($errors['description'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['description']) ?></div><?php endif; ?>
            </div>

            <!-- Statut — admin peut changer le statut directement -->
            <div class="form-group">
                <label class="form-label" for="edit-statut">Statut de l'offre</label>
                <select id="edit-statut" name="statut" class="form-input">
                    <option value="pending"  <?= $data['statut']==='pending' ?'selected':'' ?>>⏳ En attente</option>
                    <option value="approved" <?= $data['statut']==='approved'?'selected':'' ?>>✅ Approuvée</option>
                    <option value="rejected" <?= $data['statut']==='rejected'?'selected':'' ?>>❌ Rejetée</option>
                </select>
                <div style="font-size:.76rem; color:#475569; margin-top:.3rem;"><i class="fa-solid fa-circle-info"></i> L'admin peut changer le statut directement.</div>
            </div>
        </div>

        <div style="display:flex; gap:1rem; align-items:center;">
            <button type="submit" id="admin-save-btn" class="btn btn-primary" style="padding:.85rem 2.5rem;">
                <i class="fa-solid fa-floppy-disk"></i> Sauvegarder
            </button>
            <a href="detail_job_admin.php?id=<?= $offre['id'] ?>" class="btn btn-outline">
                <i class="fa-solid fa-eye"></i> Annuler
            </a>
        </div>
    </form>

</main>
</body>
</html>
