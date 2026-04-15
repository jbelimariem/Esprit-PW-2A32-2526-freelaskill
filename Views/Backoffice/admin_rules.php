<?php
require_once __DIR__ . '/../../controllers/ruleController.php';
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des règles</title>
    <link rel="stylesheet" href="../Frontoffice/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; min-height: 100vh; background: #03060E; overflow-x: hidden; }
        .sidebar { width: 280px; background: rgba(17, 24, 39, 0.4); border-right: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .main-panel { margin-left: 280px; flex: 1; padding: 2rem 3rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(37,99,235,0.1); color: white; border-right: 4px solid var(--tech-blue); }
        .form-card, .table-container { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 24px; padding: 2rem; margin-bottom: 2rem; }
        .form-card input, .form-card textarea, .form-card select { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 0.9rem 1rem; border-radius: 0.85rem; margin-top: 0.8rem; }
        .form-card label { color: var(--text-muted); font-size: 0.9rem; }
        .form-card button { margin-top: 1rem; background: var(--tech-blue); border: none; color: white; padding: 0.9rem 1.4rem; border-radius: 999px; cursor: pointer; }
        .form-card button:hover { opacity: 0.95; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 1.25rem 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .data-table th { color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        .data-table td { color: white; }
        .btn-toggle-active { background: rgba(34,197,94,0.15); color: var(--tech-green); }
        .btn-toggle-inactive { background: rgba(156,163,175,0.15); color: var(--text-muted); }
        .btn-edit { background: rgba(37,99,235,0.15); color: var(--tech-blue); }
        .btn-delete { background: rgba(239,68,68,0.15); color: #F87171; }
        .alert { padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; }
        .alert-success { background: rgba(34,197,94,0.15); color: #BBF7D0; }
        .alert-error { background: rgba(248,113,113,0.15); color: #fecaca; }
    </style>
    <script>
        function validateForm() {
            const titre = document.getElementById('titre').value.trim();
            const description = document.getElementById('description').value.trim();
            const type = document.getElementById('type').value.trim();
            const valeur = document.getElementById('valeur').value.trim();
            const idContrat = document.getElementById('id_contrat').value.trim();

            let errors = [];

            if (titre === '') {
                errors.push('Le titre est requis.');
            } else if (titre.length > 255) {
                errors.push('Le titre ne peut pas dépasser 255 caractères.');
            }

            if (description === '') {
                errors.push('La description est requise.');
            } else if (description.length > 1000) {
                errors.push('La description ne peut pas dépasser 1000 caractères.');
            }

            if (type.length > 100) {
                errors.push('Le type ne peut pas dépasser 100 caractères.');
            }

            if (valeur.length > 500) {
                errors.push('La valeur ne peut pas dépasser 500 caractères.');
            }

            if (idContrat !== '' && isNaN(idContrat)) {
                errors.push('L\'ID contrat doit être un nombre.');
            }

            if (errors.length > 0) {
                alert('Erreurs de validation :\n' + errors.join('\n'));
                return false;
            }

            return true;
        }

        // Limiter la longueur des champs en temps réel
        document.addEventListener('DOMContentLoaded', function() {
            const titreField = document.getElementById('titre');
            const descriptionField = document.getElementById('description');
            const typeField = document.getElementById('type');
            const valeurField = document.getElementById('valeur');

            if (titreField) {
                titreField.addEventListener('input', function() {
                    if (this.value.length > 255) {
                        this.value = this.value.substring(0, 255);
                    }
                });
            }

            if (descriptionField) {
                descriptionField.addEventListener('input', function() {
                    if (this.value.length > 1000) {
                        this.value = this.value.substring(0, 1000);
                    }
                });
            }

            if (typeField) {
                typeField.addEventListener('input', function() {
                    if (this.value.length > 100) {
                        this.value = this.value.substring(0, 100);
                    }
                });
            }

            if (valeurField) {
                valeurField.addEventListener('input', function() {
                    if (this.value.length > 500) {
                        this.value = this.value.substring(0, 500);
                    }
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
        <a href="admin_rules.php" class="nav-item active"><i class="fa-solid fa-gavel w-5"></i> Gestion des règles</a>
    </aside>

    <main class="main-panel">
        <div class="hero-glow-bg-2" style="top: 0; right: 0; opacity: 0.5;"></div>

        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h1 style="font-family: 'Space Grotesk'; font-size: 2rem; color: white;">Gestion des <span style="color: var(--tech-blue)">règles</span></h1>
            <div style="display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.05); padding: 0.5rem 1rem; border-radius: var(--radius-full); border: 1px solid rgba(255,255,255,0.05);">
                <i class="fa-solid fa-user-shield" style="color: var(--tech-blue);"></i> <span style="font-size: 0.85rem; color: var(--text-muted);">SuperAdmin Connecté</span>
            </div>
        </header>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage) || isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php 
                if (isset($_GET['success']) && $_GET['success'] === 'toggle') {
                    echo 'Statut de la règle changé avec succès.';
                } elseif (isset($_GET['success']) && $_GET['success'] === 'delete') {
                    echo 'Règle supprimée avec succès.';
                } else {
                    echo htmlspecialchars($successMessage ?: 'Action réalisée avec succès.', ENT_QUOTES, 'UTF-8');
                }
                ?>
            </div>
        <?php endif; ?>

        <section class="form-card animate-fade-up delay-1">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return validateForm()">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id_rule" value="<?php echo $currentRule['id_rule'] ?? ''; ?>">

                <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem;">
                    <label>
                        Titre
                        <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($currentRule['titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required maxlength="255">
                    </label>
                    <label>
                        Type
                        <input type="text" id="type" name="type" value="<?php echo htmlspecialchars($currentRule['type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="100">
                    </label>
                    <label style="grid-column: span 2;">
                        Description
                        <textarea id="description" name="description" rows="3" required maxlength="1000"><?php echo htmlspecialchars($currentRule['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>
                    <label>
                        Valeur
                        <input type="text" id="valeur" name="valeur" value="<?php echo htmlspecialchars($currentRule['valeur'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="500">
                    </label>
                    <label>
                        Statut
                        <select name="statut">
                            <option value="" <?php echo empty($currentRule['statut']) ? 'selected' : ''; ?>>Sélectionner</option>
                            <option value="actif" <?php echo ($currentRule['statut'] ?? '') === 'actif' ? 'selected' : ''; ?>>Actif</option>
                            <option value="inactif" <?php echo ($currentRule['statut'] ?? '') === 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                        </select>
                    </label>
                    <label>
                        ID Contrat
                        <input type="text" id="id_contrat" name="id_contrat" value="<?php echo htmlspecialchars($currentRule['id_contrat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" pattern="[0-9]*" title="Nombre uniquement">
                    </label>
                </div>

                <button type="submit"><?php echo isset($currentRule) ? 'Mettre à jour la règle' : 'Créer la règle'; ?></button>
            </form>
        </section>

        <section class="table-container animate-fade-up delay-2">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h2 style="color:white; font-size:1.25rem;">Liste des règles</h2>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Valeur</th>
                        <th>Statut</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rules)): ?>
                        <tr><td colspan="6" style="color:var(--text-muted);">Aucune règle trouvée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rules as $rule): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rule['titre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($rule['type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($rule['valeur'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($rule['statut'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($rule['date_creation'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                                    <a class="btn-action btn-edit" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>?action=edit&id=<?php echo intval($rule['id_rule']); ?>">Modifier</a>
                                    <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="margin:0; display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo intval($rule['id_rule']); ?>">
                                        <button class="btn-action btn-delete" type="submit" onclick="return confirm('Supprimer cette règle ?');">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
