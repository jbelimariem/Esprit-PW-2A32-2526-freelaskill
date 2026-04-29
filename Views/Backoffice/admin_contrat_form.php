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
        $errors['general'] = "Contrat introuvable.";
    }
}

$availableRules = getAvailableRulesForContrat($isEdit ? $id : null);
$selectedRuleIds = [];
if ($isEdit) {
    foreach ($availableRules as $r) {
        if ($r['id_contrat'] == $id) {
            $selectedRuleIds[] = $r['id_rule'];
        }
    }
}

// Repopulate form on error
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors)) {
    $currentContrat = [
        'titre' => $_POST['titre'] ?? '',
        'description' => $_POST['description'] ?? '',
        'budget' => $_POST['budget'] ?? '',
        'delai' => $_POST['delai'] ?? '',
        'statut' => $_POST['statut'] ?? 'brouillon',
        'id_contrat' => $_POST['id_contrat'] ?? null
    ];
    $selectedRuleIds = $_POST['selected_rules'] ?? [];
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
        body { display: flex; min-height: 100vh; background: var(--bg-main, #03060E); overflow-x: hidden; font-family: 'Inter', sans-serif; transition: background 0.3s, color 0.3s; color: var(--text-main, white); }
        .sidebar { width: 280px; background: var(--bg-sidebar, rgba(17, 24, 39, 0.4)); border-right: 1px solid var(--border-color, rgba(255,255,255,0.05)); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: background 0.3s; }
        .main-panel { margin-left: 280px; flex: 1; padding: 2rem 3rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition, 0.3s); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: var(--nav-hover, rgba(37,99,235,0.1)); color: var(--text-main, white); border-right: 4px solid var(--tech-blue); }
        .form-card { background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 24px; padding: 3rem; margin-bottom: 2rem; max-width: 800px; transition: background 0.3s; }
        .form-card input, .form-card textarea, .form-card select { width: 100%; background: var(--input-bg, rgba(255,255,255,0.05)); border: 1px solid var(--border-color, rgba(255,255,255,0.1)); color: var(--text-main, white); padding: 1rem 1.25rem; border-radius: 0.85rem; margin-top: 0.8rem; font-size: 0.95rem; transition: border-color 0.3s; }
        .form-card input:focus, .form-card textarea:focus, .form-card select:focus { outline: none; border-color: var(--tech-blue); }
        .form-card label { color: var(--text-muted); font-size: 0.95rem; display: block; margin-bottom: 1.5rem; font-weight: 500; }
        .form-card button { margin-top: 2rem; background: var(--tech-blue); border: none; color: white; padding: 1rem 2rem; border-radius: 999px; cursor: pointer; font-size: 1rem; font-weight: 600; transition: transform 0.3s, background 0.3s; width: 100%; }
        .form-card button:hover { background: #1D4ED8; transform: translateY(-2px); }
        .alert { padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; max-width: 800px; }
        .alert-error { background: #E74C3C; color: white; font-weight: 500; border: none; }
        .btn-back { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); color: white; border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; border: 1px solid rgba(255,255,255,0.1); }
        .btn-back:hover { background: rgba(255,255,255,0.1); }
        .error-message { color: #E74C3C; font-size: 0.85rem; margin-top: 0.4rem; display: block; }
        .form-card input.has-error, .form-card textarea.has-error, .form-card select.has-error { border-color: #E74C3C; }

        /* Light Mode Variables */
        body.light-mode {
            --bg-main: #f8fafc;
            --bg-sidebar: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --card-bg: #ffffff;
            --input-bg: #f1f5f9;
            --nav-hover: #f1f5f9;
        }

        .theme-toggle {
            cursor: pointer;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--text-muted);
            transition: var(--transition, 0.3s);
            font-size: 0.95rem;
            font-weight: 500;
        }
        .theme-toggle:hover {
            background: var(--nav-hover, rgba(255,255,255,0.03));
            color: var(--text-main);
        }
    </style>
    <script>
        function toggleTheme() {
            document.body.classList.toggle('light-mode');
            const isLight = document.body.classList.contains('light-mode');
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
            const icon = document.getElementById('theme-icon');
            icon.className = isLight ? 'fa-solid fa-moon w-5' : 'fa-solid fa-sun w-5';
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('theme') === 'light') {
                document.body.classList.add('light-mode');
                document.getElementById('theme-icon').className = 'fa-solid fa-moon w-5';
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

        <div style="margin-top: auto;">
            <div class="theme-toggle" onclick="toggleTheme()">
                <i id="theme-icon" class="fa-solid fa-sun w-5"></i> Changer le thème
            </div>
        </div>
    </aside>

    <main class="main-panel">
        <div class="hero-glow-bg-2" style="top: 10%; right: 0; opacity: 0.5;"></div>

        <a href="admin_contrat_list.php" class="btn-back animate-fade-up"><i class="fa-solid fa-arrow-left"></i> Retour à la liste</a>

        <header style="margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h1 style="font-size: 2.5rem; color: var(--text-main, white); margin-bottom: 0.5rem;"><?php echo $isEdit ? 'Modifier le' : 'Nouveau'; ?> <span style="color: var(--tech-blue)">Contrat</span></h1>
        </header>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error animate-fade-up">
                Vous n'avez pas rempli le formulaire correctement
                <?php if (!empty($errors['general'])): ?>
                    <div style="font-size: 0.85rem; margin-top: 0.5rem; opacity: 0.9;"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <section class="form-card animate-fade-up delay-2">
            <form id="contratForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" novalidate>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="redirect_to" value="admin_contrat_list.php">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id_contrat" value="<?php echo intval($currentContrat['id_contrat']); ?>">
                <?php endif; ?>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 0 1.5rem;">
                    <label style="grid-column: span 2;">
                        Titre du contrat *
                        <input type="text" id="titre" name="titre" class="<?php echo isset($errors['titre']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentContrat['titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex: Développement du site e-commerce">
                        <?php if (isset($errors['titre'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>

                    <label style="grid-column: span 2;">
                        Description détaillée *
                        <textarea id="description" name="description" rows="5" class="<?php echo isset($errors['description']) ? 'has-error' : ''; ?>" placeholder="Décrivez les attentes et livrables..."><?php echo htmlspecialchars($currentContrat['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>

                    <label>
                        Budget (DT) *
                        <input type="text" id="budget" name="budget" class="<?php echo isset($errors['budget']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentContrat['budget'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex: 1500.00">
                        <?php if (isset($errors['budget'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['budget'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>

                    <label>
                        Délai (Jours) *
                        <input type="text" id="delai" name="delai" class="<?php echo isset($errors['delai']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentContrat['delai'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex: 30">
                        <?php if (isset($errors['delai'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['delai'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
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

                    <div style="grid-column: span 2; margin-bottom: 1.5rem; margin-top: 1rem;">
                        <span style="color: var(--text-muted); font-size: 0.95rem; display: block; margin-bottom: 0.8rem; font-weight: 500;">Sélectionner des règles existantes (Optionnel)</span>
                        <div style="background: var(--input-bg, rgba(255,255,255,0.05)); border: 1px solid var(--border-color, rgba(255,255,255,0.1)); border-radius: 0.85rem; padding: 1rem; max-height: 200px; overflow-y: auto;">
                            <?php if (empty($availableRules)): ?>
                                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">Aucune règle disponible.</p>
                            <?php else: ?>
                                <?php foreach ($availableRules as $rule): ?>
                                    <?php $checked = in_array($rule['id_rule'], $selectedRuleIds) ? 'checked' : ''; ?>
                                    <label style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 0.8rem; cursor: pointer; font-weight: normal; color: var(--text-main, white);">
                                        <input type="checkbox" name="selected_rules[]" value="<?php echo $rule['id_rule']; ?>" <?php echo $checked; ?> style="width: 1.2rem; height: 1.2rem; margin: 0; cursor: pointer;">
                                        <?php echo htmlspecialchars($rule['titre'], ENT_QUOTES, 'UTF-8'); ?> <span style="color: var(--text-muted); font-size: 0.85rem;">(Type: <?php echo htmlspecialchars($rule['type'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 0.5rem;">
                            Cochez les cases pour sélectionner plusieurs règles. Ces règles seront définitivement liées à ce contrat.
                        </span>
                    </div>
                </div>

                <button type="submit">
                    <?php echo $isEdit ? '<i class="fa-solid fa-save"></i> Mettre à jour le contrat' : '<i class="fa-solid fa-paper-plane"></i> Créer le contrat'; ?>
                </button>
            </form>
        </section>
    </main>
</body>
</html>
