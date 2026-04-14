<?php
// views/backoffice/add_job_admin.php — Admin: Ajouter une offre

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/JobOffer.php';

$errors  = [];
$data    = ['titre'=>'','description'=>'','competences'=>'','budget'=>'','delai'=>'','statut'=>'pending'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre'       => trim($_POST['titre']       ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'competences' => trim($_POST['competences'] ?? ''),
        'budget'      => trim($_POST['budget']       ?? ''),
        'delai'       => trim($_POST['delai']        ?? ''),
        'statut'      => trim($_POST['statut']       ?? 'pending'),
        'client_id'   => 1
    ];

    // Validation
    if (empty($data['titre']))                    { $errors['titre']       = "Le titre est obligatoire."; }
    elseif (strlen($data['titre']) < 5)           { $errors['titre']       = "Min. 5 caractères."; }
    elseif (strlen($data['titre']) > 255)         { $errors['titre']       = "Max. 255 caractères."; }
    if (empty($data['description']))              { $errors['description'] = "La description est obligatoire."; }
    elseif (strlen($data['description']) < 20)    { $errors['description'] = "Min. 20 caractères."; }
    if (empty($data['competences']))              { $errors['competences'] = "Champ obligatoire."; }
    if (empty($data['budget']) || !is_numeric($data['budget'])) { $errors['budget'] = "Budget numérique requis."; }
    elseif ((float)$data['budget'] <= 0)          { $errors['budget']      = "Budget > 0 requis."; }
    if (empty($data['delai']))                    { $errors['delai']       = "Champ obligatoire."; }
    if (!in_array($data['statut'], ['pending','approved','rejected'])) { $data['statut'] = 'pending'; }

    if (empty($errors)) {
        $model    = new JobOffer();
        $insertId = $model->create($data);
        $model->updateStatut($insertId, $data['statut']);
        header('Location: dashboard.php?success=added'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une offre — Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="logo">
        <i class="fa-solid fa-briefcase"></i>
        Core<span>Panel</span>
        <small>Admin Jobs v1.0</small>
    </div>
    <div class="nav-section-title">Navigation</div>
    <a href="dashboard.php" class="nav-item" id="nav-dashboard-back"><i class="fa-solid fa-gauge-high"></i> Tableau de bord</a>
    <a href="add_job_admin.php" class="nav-item active" id="nav-add-active"><i class="fa-solid fa-plus"></i> Ajouter une offre</a>
    <div class="sidebar-footer">
        <a href="../frontoffice/home.php" class="nav-item" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07);">
            <i class="fa-solid fa-globe"></i> Interface Client
        </a>
    </div>
</aside>

<main class="main-panel">

    <div class="topbar">
        <div>
            <h1 class="topbar-title">Ajouter une <span>offre</span></h1>
            <p style="color:var(--text-muted); font-size:.82rem; margin-top:.2rem;">Créez une nouvelle offre d'emploi freelance</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline" id="back-to-dashboard">
            <i class="fa-solid fa-arrow-left"></i> Retour au dashboard
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:var(--radius-md); padding:1rem 1.25rem; margin-bottom:1.5rem; color:var(--tunisian-red); font-size:.88rem;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <strong style="margin-left:.4rem;">Erreurs à corriger :</strong>
        <ul style="margin-top:.5rem; padding-left:1.5rem;">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form id="admin-add-form" action="add_job_admin.php" method="POST" novalidate>
        <div class="form-section animate-fade-up">
            <h3 style="color:white; font-size:1rem; margin-bottom:1.25rem; padding-bottom:.75rem; border-bottom:1px solid rgba(255,255,255,0.06);">
                <i class="fa-solid fa-pen-to-square" style="color:var(--tech-blue);"></i> Informations de l'offre
            </h3>

            <!-- Titre -->
            <div class="form-group">
                <label class="form-label" for="admin-titre">Titre <span>*</span></label>
                <input id="admin-titre" name="titre" type="text" class="form-input <?= isset($errors['titre'])?'error':'' ?>"
                       placeholder="Ex. Développeur Full Stack pour app e-commerce"
                       value="<?= htmlspecialchars($data['titre']) ?>" minlength="5" maxlength="255" required>
                <?php if (isset($errors['titre'])): ?>
                <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['titre']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-grid-2">
                <!-- Budget -->
                <div class="form-group">
                    <label class="form-label" for="admin-budget">Budget (DT) <span>*</span></label>
                    <input id="admin-budget" name="budget" type="number" min="1" step="0.01"
                           class="form-input <?= isset($errors['budget'])?'error':'' ?>"
                           placeholder="Ex. 2500"
                           value="<?= htmlspecialchars($data['budget']) ?>" required>
                    <?php if (isset($errors['budget'])): ?>
                    <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['budget']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Délai -->
                <div class="form-group">
                    <label class="form-label" for="admin-delai">Délai <span>*</span></label>
                    <select id="admin-delai" name="delai" class="form-input <?= isset($errors['delai'])?'error':'' ?>" required>
                        <option value="">Sélectionnez</option>
                        <?php foreach (["1 semaine","2 semaines","1 mois","2 mois","3 mois","6 mois","Plus de 6 mois"] as $d): ?>
                        <option value="<?= $d ?>" <?= $data['delai']===$d?'selected':'' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['delai'])): ?>
                    <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['delai']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Compétences -->
            <div class="form-group">
                <label class="form-label" for="admin-competences">Compétences requises <span>*</span></label>
                <input id="admin-competences" name="competences" type="text"
                       class="form-input <?= isset($errors['competences'])?'error':'' ?>"
                       placeholder="Ex. PHP, MySQL, React.js (séparées par virgules)"
                       value="<?= htmlspecialchars($data['competences']) ?>" required>
                <?php if (isset($errors['competences'])): ?>
                <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['competences']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label" for="admin-description">Description <span>*</span></label>
                <textarea id="admin-description" name="description" rows="6"
                          class="form-input <?= isset($errors['description'])?'error':'' ?>"
                          placeholder="Description complète de la mission..."
                          minlength="20" maxlength="2000" required><?= htmlspecialchars($data['description']) ?></textarea>
                <?php if (isset($errors['description'])): ?>
                <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['description']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Statut -->
            <div class="form-group">
                <label class="form-label" for="admin-statut">Statut de publication</label>
                <select id="admin-statut" name="statut" class="form-input">
                    <option value="pending"  <?= $data['statut']==='pending' ?'selected':'' ?>>⏳ En attente</option>
                    <option value="approved" <?= $data['statut']==='approved'?'selected':'' ?>>✅ Approuvée</option>
                    <option value="rejected" <?= $data['statut']==='rejected'?'selected':'' ?>>❌ Rejetée</option>
                </select>
            </div>
        </div>

        <div style="display:flex; gap:1rem; align-items:center;">
            <button type="submit" class="btn btn-primary" id="admin-add-submit" style="padding:.85rem 2.5rem;">
                <i class="fa-solid fa-plus"></i> Créer l'offre
            </button>
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fa-solid fa-xmark"></i> Annuler
            </a>
        </div>
    </form>

</main>

<script>
document.getElementById('admin-add-form').addEventListener('submit', function(e) {
    const titre = document.getElementById('admin-titre').value.trim();
    const desc  = document.getElementById('admin-description').value.trim();
    const comp  = document.getElementById('admin-competences').value.trim();
    const bud   = parseFloat(document.getElementById('admin-budget').value);
    const del   = document.getElementById('admin-delai').value;
    let valid = true;
    if (titre.length < 5)    { alert('Titre : min. 5 caractères.'); valid = false; }
    if (desc.length < 20)    { alert('Description : min. 20 caractères.'); valid = false; }
    if (!comp)               { alert('Compétences obligatoires.'); valid = false; }
    if (isNaN(bud)||bud<=0) { alert('Budget positif requis.'); valid = false; }
    if (!del)                { alert('Veuillez sélectionner un délai.'); valid = false; }
    if (!valid) e.preventDefault();
});
</script>
</body>
</html>
