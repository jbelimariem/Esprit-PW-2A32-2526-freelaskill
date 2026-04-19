<?php
require_once __DIR__ . '/../../controllers/ruleController.php';
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo isset($currentRule) ? 'Modifier' : 'Ajouter'; ?> une règle</title>
    <link rel="stylesheet" href="../Frontoffice/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; min-height: 100vh; background: #03060E; overflow-x: hidden; }
        .sidebar { width: 280px; background: rgba(17, 24, 39, 0.4); border-right: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .main-panel { margin-left: 280px; flex: 1; padding: 2rem 3rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(37,99,235,0.1); color: white; border-right: 4px solid var(--tech-blue); }
        .form-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 24px; padding: 2rem; margin-bottom: 2rem; }
        .form-card input, .form-card textarea, .form-card select { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 0.9rem 1rem; border-radius: 0.85rem; margin-top: 0.8rem; }
        .form-card label { color: var(--text-muted); font-size: 0.9rem; }
        .form-card button { margin-top: 1rem; background: var(--tech-blue); border: none; color: white; padding: 0.9rem 1.4rem; border-radius: 999px; cursor: pointer; transition: 0.3s; }
        .form-card button:hover { opacity: 0.95; }
        .btn-back { display: inline-block; padding: 0.5rem 1rem; background: rgba(255,255,255,0.1); color: white; border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; }
        .btn-back:hover { background: rgba(255,255,255,0.2); }
        .alert { padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; }
        .alert-error { background: rgba(248,113,113,0.15); color: #fecaca; }
    </style>    <script>
        function validateForm(event) {
            const titre = document.getElementById('titre').value.trim();
            const description = document.getElementById('description').value.trim();
            const type = document.getElementById('type').value.trim();
            const valeur = document.getElementById('valeur').value.trim();
            const idContrat = document.getElementById('id_contrat').value.trim();
            
            let errors = [];
            
            if (titre === '') errors.push('Le titre est obligatoire.');
            if (type === '') errors.push('Le type est obligatoire.');
            if (description === '') errors.push('La description est obligatoire.');
            
            if (valeur !== '' && isNaN(valeur)) errors.push('La valeur doit être uniquement un nombre.');
            if (idContrat !== '' && isNaN(idContrat)) errors.push('L\'ID contrat doit être uniquement un nombre.');

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
            const form = document.getElementById('ruleForm');
            if (form) form.addEventListener('submit', validateForm);

            // Validation en temps réel pour empêcher la saisie non numérique
            const numberFields = ['valeur', 'id_contrat'];
            numberFields.forEach(id => {
                const field = document.getElementById(id);
                if(field) {
                    field.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    });
                }
            });
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
        <a href="admin_rules.php" class="nav-item active"><i class="fa-solid fa-gavel w-5"></i> Gestion des règles</a>
    </aside>

    <main class="main-panel">
        <div class="hero-glow-bg-2" style="top: 0; right: 0; opacity: 0.5;"></div>

        <a href="admin_rules_list.php" class="btn-back animate-fade-up"><i class="fa-solid fa-arrow-left"></i> Retour à la liste</a>

        <header style="margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h1 style="font-family: 'Space Grotesk'; font-size: 2rem; color: white;"><?php echo isset($currentRule) ? 'Modifier' : 'Ajouter'; ?> une <span style="color: var(--tech-blue)">règle</span></h1>
        </header>

        <div id="js-errors" class="alert alert-error" style="display: none;"></div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="form-card animate-fade-up delay-2">
            <form id="ruleForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="redirect_to" value="admin_rules_list.php">
                <input type="hidden" name="id_rule" value="<?php echo $currentRule['id_rule'] ?? ''; ?>">
                <!-- Le statut est masqué et par défaut actif, ou conserve l'ancien s'il existe -->
                <input type="hidden" name="statut" value="<?php echo htmlspecialchars($currentRule['statut'] ?? 'actif', ENT_QUOTES, 'UTF-8'); ?>">

                <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem;">
                    <label>
                        Titre de la règle *
                        <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($currentRule['titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                    <label>
                        Type de clause *
                        <input type="text" id="type" name="type" value="<?php echo htmlspecialchars($currentRule['type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                    <label style="grid-column: span 2;">
                        Description détaillée (Unique) *
                        <textarea id="description" name="description" rows="3" required><?php echo htmlspecialchars($currentRule['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>
                    <label>
                        Valeur (Nombre uniquement)
                        <input type="text" id="valeur" name="valeur" value="<?php echo htmlspecialchars($currentRule['valeur'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" pattern="[0-9]*" title="Nombre uniquement">
                    </label>
                    <label>
                        ID Contrat (Nombre uniquement)
                        <input type="text" id="id_contrat" name="id_contrat" value="<?php echo htmlspecialchars($currentRule['id_contrat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" pattern="[0-9]*" title="Nombre uniquement">
                    </label>
                </div>

                <button type="submit"><?php echo isset($currentRule) ? 'Enregistrer les modifications' : 'Créer la règle'; ?></button>
            </form>
        </section>
    </main>
</body>
</html>
