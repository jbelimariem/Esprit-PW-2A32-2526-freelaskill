<?php
require_once __DIR__ . '/../../controllers/contratController.php';

// Si on est en mode édition, récupérer l'ID depuis l'URL
$isEdit = false;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $currentContrat = getContratById($id);
    if ($currentContrat) {
        $isEdit = true;
    } else {
        $errors[] = "Contrat introuvable.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo $isEdit ? 'Modifier' : 'Créer'; ?> un contrat</title>
    <link rel="stylesheet" href="../Frontoffice/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; min-height: 100vh; background: #03060E; overflow-x: hidden; font-family: 'Inter', sans-serif; }
        .sidebar { width: 280px; background: rgba(17, 24, 39, 0.4); border-right: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .main-panel { margin-left: 280px; flex: 1; padding: 2rem 3rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(37,99,235,0.1); color: white; border-right: 4px solid var(--tech-blue); }
        .form-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 24px; padding: 3rem; margin-bottom: 2rem; max-width: 800px; }
        .form-card input, .form-card textarea, .form-card select { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 1rem 1.25rem; border-radius: 0.85rem; margin-top: 0.8rem; font-size: 0.95rem; transition: border-color 0.3s; }
        .form-card input:focus, .form-card textarea:focus, .form-card select:focus { outline: none; border-color: var(--tech-blue); }
        .form-card label { color: var(--text-muted); font-size: 0.95rem; display: block; margin-bottom: 1.5rem; font-weight: 500; }
        .form-card button { margin-top: 2rem; background: var(--tech-blue); border: none; color: white; padding: 1rem 2rem; border-radius: 999px; cursor: pointer; font-size: 1rem; font-weight: 600; transition: transform 0.3s, background 0.3s; width: 100%; }
        .form-card button:hover { background: #1D4ED8; transform: translateY(-2px); }
        .alert { padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; max-width: 800px; }
        .alert-error { background: rgba(248,113,113,0.15); color: #fecaca; }
        .btn-back { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); color: white; border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; border: 1px solid rgba(255,255,255,0.1); }
        .btn-back:hover { background: rgba(255,255,255,0.1); }
    </style>
    <script>
        function validateForm(event) {
            const titre = document.getElementById('titre').value.trim();
            const description = document.getElementById('description').value.trim();
            const budget = document.getElementById('budget').value.trim();
            const delai = document.getElementById('delai').value.trim();
            const statut = document.getElementById('statut').value;
            
            let errors = [];
            
            if (titre === '') errors.push('Le titre est obligatoire.');
            if (description === '') errors.push('La description est obligatoire.');
            
            if (budget === '' || isNaN(budget) || parseFloat(budget) <= 0) {
                errors.push('Le budget doit être un nombre strictement positif.');
            }
            
            if (delai === '' || isNaN(delai) || parseInt(delai) <= 0 || !Number.isInteger(parseFloat(delai))) {
                errors.push('Le délai doit être un nombre entier strictement positif (en jours).');
            }

            if (errors.length > 0) {
                event.preventDefault();
                const errorDiv = document.getElementById('js-errors');
                errorDiv.innerHTML = errors.map(e => `<div>${e}</div>`).join('');
                errorDiv.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return false;
            }
            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('contratForm');
            if (form) form.addEventListener('submit', validateForm);

            // Restrict input to numbers
            const budgetField = document.getElementById('budget');
            if(budgetField) {
                budgetField.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9.]/g, '');
                });
            }

            const delaiField = document.getElementById('delai');
            if(delaiField) {
                delaiField.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
        });
    </script>
</head>
<body>
    <aside class="sidebar animate-fade-up">
        <div style="padding: 0 2rem; margin-bottom: 3rem;">
            <div class="logo">
                <i class="fa-solid fa-shapes text-tech-blue" style="color: var(--tech-blue)"></i>
                Freela<span>Skill</span>
            </div>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; letter-spacing: 1px;">Admin Control v3.0</p>
        </div>
        <a href="admin_dashboard.html" class="nav-item"><i class="fa-solid fa-cube w-5"></i> Dashboard Central</a>
        <a href="admin_approbations.html" class="nav-item"><i class="fa-solid fa-check-double w-5"></i> Validations</a>
        <a href="admin_litiges.html" class="nav-item"><i class="fa-solid fa-scale-balanced w-5"></i> Litiges</a>
        <a href="admin_archivage.html" class="nav-item"><i class="fa-solid fa-box-archive w-5"></i> Archivage</a>
        <a href="admin_rules_list.php" class="nav-item"><i class="fa-solid fa-gavel w-5"></i> Gestion des règles</a>
        <a href="admin_contrat.php" class="nav-item active"><i class="fa-solid fa-file-contract w-5"></i> Gestion des contrats</a>
    </aside>

    <main class="main-panel">
        <div class="hero-glow-bg-2" style="top: 10%; right: 0; opacity: 0.5;"></div>

        <a href="admin_contrat_list.php" class="btn-back animate-fade-up"><i class="fa-solid fa-arrow-left"></i> Retour à la liste</a>

        <header style="margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h1 style="font-size: 2.5rem; color: white; margin-bottom: 0.5rem;"><?php echo $isEdit ? 'Modifier le' : 'Nouveau'; ?> <span style="color: var(--tech-blue)">Contrat</span></h1>
        </header>

        <div id="js-errors" class="alert alert-error" style="display: none;"></div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error animate-fade-up">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="form-card animate-fade-up delay-2">
            <form id="contratForm" method="post" action="admin_contrat_list.php">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="redirect_to" value="admin_contrat_list.php">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id_contrat" value="<?php echo intval($currentContrat['id_contrat']); ?>">
                <?php endif; ?>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 0 1.5rem;">
                    <label style="grid-column: span 2;">
                        Titre du contrat *
                        <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($currentContrat['titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required maxlength="255" placeholder="Ex: Développement du site e-commerce">
                    </label>

                    <label style="grid-column: span 2;">
                        Description détaillée *
                        <textarea id="description" name="description" rows="5" required placeholder="Décrivez les attentes et livrables..."><?php echo htmlspecialchars($currentContrat['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>

                    <label>
                        Budget (DT) *
                        <input type="text" id="budget" name="budget" value="<?php echo htmlspecialchars($currentContrat['budget'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required placeholder="Ex: 1500.00">
                    </label>

                    <label>
                        Délai (Jours) *
                        <input type="text" id="delai" name="delai" value="<?php echo htmlspecialchars($currentContrat['delai'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required placeholder="Ex: 30">
                    </label>

                    <label style="grid-column: span 2;">
                        Statut
                        <select id="statut" name="statut">
                            <?php 
                            $statuts = ['brouillon', 'en_attente', 'actif', 'termine', 'annule'];
                            $currentStatus = $currentContrat['statut'] ?? 'brouillon';
                            foreach ($statuts as $s) {
                                $selected = ($s === $currentStatus) ? 'selected' : '';
                                $label = ucfirst(str_replace('_', ' ', $s));
                                echo "<option value=\"$s\" $selected>$label</option>";
                            }
                            ?>
                        </select>
                    </label>
                </div>

                <button type="submit">
                    <?php echo $isEdit ? '<i class="fa-solid fa-save"></i> Mettre à jour le contrat' : '<i class="fa-solid fa-paper-plane"></i> Créer le contrat'; ?>
                </button>
            </form>
        </section>
    </main>
</body>
</html>
