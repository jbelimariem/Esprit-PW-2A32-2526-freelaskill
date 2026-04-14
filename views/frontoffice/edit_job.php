<?php
// views/frontoffice/edit_job.php — Client: Modifier une offre

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/JobOffer.php';

$model  = new JobOffer();
$id     = (int)($_GET['id'] ?? 0);
$offre  = $model->getById($id);
$errors = [];

if (!$offre) {
    header('Location: home.php');
    exit;
}

$data = $offre; // pré-remplir le formulaire

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre'       => trim($_POST['titre']       ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'competences' => trim($_POST['competences'] ?? ''),
        'budget'      => trim($_POST['budget']       ?? ''),
        'delai'       => trim($_POST['delai']        ?? ''),
        'statut'      => $offre['statut'], // conserve le statut actuel
        'id'          => $offre['id'],
        'date_creation' => $offre['date_creation'],
    ];

    // Validation
    if (empty($data['titre'])) {
        $errors['titre'] = "Le titre est obligatoire.";
    } elseif (strlen($data['titre']) < 5) {
        $errors['titre'] = "Le titre doit contenir au moins 5 caractères.";
    } elseif (strlen($data['titre']) > 255) {
        $errors['titre'] = "Le titre ne doit pas dépasser 255 caractères.";
    }

    if (empty($data['description'])) {
        $errors['description'] = "La description est obligatoire.";
    } elseif (strlen($data['description']) < 20) {
        $errors['description'] = "La description doit contenir au moins 20 caractères.";
    }

    if (empty($data['competences'])) {
        $errors['competences'] = "Les compétences requises sont obligatoires.";
    }

    if (empty($data['budget']) || !is_numeric($data['budget'])) {
        $errors['budget'] = "Le budget doit être un nombre valide.";
    } elseif ((float)$data['budget'] <= 0) {
        $errors['budget'] = "Le budget doit être supérieur à 0.";
    }

    if (empty($data['delai'])) {
        $errors['delai'] = "Le délai est obligatoire.";
    }

    if (empty($errors)) {
        $model->update($id, $data);
        header('Location: home.php?success=updated');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'offre — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; margin-bottom: .5rem; color: #94A3B8; font-size: .9rem; font-weight: 500; }
        .form-label span { color: var(--tunisian-red); margin-left: 2px; }
        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.85rem 1.1rem;
            color: white;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.95rem;
            outline: none;
            transition: var(--transition);
        }
        .form-input:focus { background: rgba(59,130,246,0.05); border-color: var(--tech-blue); box-shadow: 0 0 0 4px rgba(59,130,246,0.12); }
        .form-input::placeholder { color: #334155; }
        .form-input.error { border-color: rgba(239,68,68,0.5); background: rgba(239,68,68,0.03); }
        .error-msg { color: var(--tunisian-red); font-size: .8rem; margin-top: .4rem; display: flex; align-items: center; gap: .3rem; }
        .char-count { font-size: .75rem; color: #475569; text-align: right; margin-top: .25rem; }
        textarea.form-input { resize: vertical; min-height: 140px; }
        select.form-input { cursor: pointer; }
        select.form-input option { background: #0f172a; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    </style>
</head>
<body class="page-anim">

<nav>
    <div class="logo">
        <i class="fa-solid fa-briefcase"></i>
        Freela<span>Skill</span>
    </div>
    <div class="nav-right">
        <a href="home.php" class="cart-btn" style="background:rgba(255,255,255,0.06); border-color:rgba(255,255,255,0.12); color:white;">
            <i class="fa-solid fa-arrow-left"></i> Retour à mes offres
        </a>
    </div>
</nav>

<section class="hero-banner">
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>
    <div class="hero-content">
        <div class="hero-tag"><i class="fa-solid fa-pen"></i> Modification</div>
        <h1 class="hero-title">Modifier <span>votre offre</span></h1>
        <p class="hero-sub">Mettez à jour les informations de votre offre de mission freelance.</p>
    </div>
</section>

<div class="page-body" style="grid-template-columns:1fr; padding:2rem 4rem 4rem;">
    <div class="products-area" style="padding-right:0; max-width:860px; margin:0 auto; width:100%;">

        <!-- Info ID -->
        <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:1.5rem; color:var(--text-muted); font-size:.85rem;">
            <i class="fa-solid fa-id-badge" style="color:var(--tech-blue);"></i>
            Offre #<?= $offre['id'] ?> — publiée le <?= date('d/m/Y à H:i', strtotime($offre['date_creation'])) ?>
        </div>

        <?php if (!empty($errors)): ?>
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:var(--radius-md); padding:1rem 1.25rem; margin-bottom:1.5rem; color:var(--tunisian-red); font-size:.9rem;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <strong style="margin-left:.4rem;">Veuillez corriger les erreurs :</strong>
            <ul style="margin-top:.5rem; padding-left:1.5rem;">
                <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="product-card" style="opacity:1; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
            <div class="card-body" style="padding:2.5rem;">
                <form id="edit-form" action="edit_job.php?id=<?= $offre['id'] ?>" method="POST" novalidate>

                    <div class="form-group">
                        <label class="form-label" for="titre">Titre de l'offre <span>*</span></label>
                        <input id="titre" name="titre" type="text" class="form-input <?= isset($errors['titre']) ? 'error' : '' ?>"
                               placeholder="Titre de l'offre"
                               value="<?= htmlspecialchars($data['titre']) ?>"
                               minlength="5" maxlength="255" required>
                        <div class="char-count"><span id="titre-count"><?= strlen($data['titre']) ?></span>/255</div>
                        <?php if (isset($errors['titre'])): ?>
                        <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['titre']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="budget">Budget (DT) <span>*</span></label>
                            <input id="budget" name="budget" type="number" min="1" step="0.01"
                                   class="form-input <?= isset($errors['budget']) ? 'error' : '' ?>"
                                   placeholder="Ex. 1500"
                                   value="<?= htmlspecialchars($data['budget']) ?>" required>
                            <?php if (isset($errors['budget'])): ?>
                            <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['budget']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="delai">Délai de livraison <span>*</span></label>
                            <select id="delai" name="delai" class="form-input <?= isset($errors['delai']) ? 'error' : '' ?>" required>
                                <option value="">Sélectionnez un délai</option>
                                <?php
                                $delais = ["1 semaine","2 semaines","1 mois","2 mois","3 mois","6 mois","Plus de 6 mois"];
                                foreach ($delais as $d): ?>
                                <option value="<?= $d ?>" <?= $data['delai'] === $d ? 'selected' : '' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['delai'])): ?>
                            <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['delai']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="competences">Compétences requises <span>*</span></label>
                        <input id="competences" name="competences" type="text"
                               class="form-input <?= isset($errors['competences']) ? 'error' : '' ?>"
                               placeholder="Ex. React.js, Node.js, MongoDB"
                               value="<?= htmlspecialchars($data['competences']) ?>" required>
                        <div style="font-size:.75rem; color:#475569; margin-top:.3rem;"><i class="fa-solid fa-circle-info"></i> Séparez par des virgules</div>
                        <?php if (isset($errors['competences'])): ?>
                        <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['competences']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Description de la mission <span>*</span></label>
                        <textarea id="description" name="description" rows="6"
                                  class="form-input <?= isset($errors['description']) ? 'error' : '' ?>"
                                  placeholder="Description détaillée de la mission..."
                                  minlength="20" maxlength="2000" required><?= htmlspecialchars($data['description']) ?></textarea>
                        <div class="char-count"><span id="desc-count"><?= strlen($data['description']) ?></span>/2000</div>
                        <?php if (isset($errors['description'])): ?>
                        <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['description']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div style="background:rgba(245,158,11,0.06); border:1px solid rgba(245,158,11,0.2); border-radius:var(--radius-md); padding:.85rem 1.1rem; margin-bottom:1.5rem; font-size:.83rem; color:var(--text-muted); display:flex; align-items:center; gap:.6rem;">
                        <i class="fa-solid fa-triangle-exclamation" style="color:#F59E0B;"></i>
                        La modification remettra l'offre en statut <strong style="color:#F59E0B;">En attente</strong> pour révision.
                    </div>

                    <div style="display:flex; gap:1rem; align-items:center; border-top:1px solid var(--border); padding-top:1.5rem;">
                        <button type="submit" class="btn-cart" id="btn-submit-edit" style="width:auto; padding:.9rem 2.5rem; font-size:1.05rem;">
                            <i class="fa-solid fa-floppy-disk"></i> Sauvegarder les modifications
                        </button>
                        <a href="detail_job.php?id=<?= $offre['id'] ?>" style="color:var(--tech-blue); font-size:.9rem; text-decoration:none;">
                            <i class="fa-solid fa-eye"></i> Voir le détail
                        </a>
                        <a href="home.php" style="color:var(--text-muted); font-size:.9rem; text-decoration:none; margin-left:auto;">
                            <i class="fa-solid fa-xmark"></i> Annuler
                        </a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<script>
const titreInput = document.getElementById('titre');
const descInput  = document.getElementById('description');
if (titreInput) titreInput.addEventListener('input', () => {
    document.getElementById('titre-count').textContent = titreInput.value.length;
});
if (descInput) descInput.addEventListener('input', () => {
    document.getElementById('desc-count').textContent = descInput.value.length;
});

document.getElementById('edit-form').addEventListener('submit', function(e) {
    const titre = document.getElementById('titre').value.trim();
    const desc  = document.getElementById('description').value.trim();
    const comp  = document.getElementById('competences').value.trim();
    const bud   = parseFloat(document.getElementById('budget').value);
    const del   = document.getElementById('delai').value;
    let valid   = true;
    if (titre.length < 5)   { showError('titre', 'Min. 5 caractères.'); valid = false; }
    if (desc.length < 20)   { showError('description', 'Min. 20 caractères.'); valid = false; }
    if (!comp)               { showError('competences', 'Champ obligatoire.'); valid = false; }
    if (isNaN(bud)||bud<=0) { showError('budget', 'Budget positif requis.'); valid = false; }
    if (!del)                { showError('delai', 'Sélectionnez un délai.'); valid = false; }
    if (!valid) e.preventDefault();
});

function showError(fieldId, msg) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.add('error');
    let existing = field.parentElement.querySelector('.error-msg');
    if (!existing) {
        const div = document.createElement('div');
        div.className = 'error-msg';
        div.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${msg}`;
        field.parentElement.appendChild(div);
    }
}
</script>

</body>
</html>
