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
        body { display: flex; min-height: 100vh; background: var(--bg-main, #03060E); overflow-x: hidden; font-family: 'Inter', sans-serif; transition: background 0.3s, color 0.3s; color: var(--text-main, white); }
        .sidebar { width: 280px; background: var(--bg-sidebar, rgba(17, 24, 39, 0.4)); border-right: 1px solid var(--border-color, rgba(255,255,255,0.05)); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: background 0.3s; }
        .main-panel { margin-left: 280px; flex: 1; padding: 2rem 3rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition, 0.3s); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: var(--nav-hover, rgba(37,99,235,0.1)); color: var(--text-main, white); border-right: 4px solid var(--tech-blue); }
        .form-card { background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 24px; padding: 2rem; margin-bottom: 2rem; transition: background 0.3s; }
        .form-card input, .form-card textarea, .form-card select { width: 100%; background: var(--input-bg, rgba(255,255,255,0.05)); border: 1px solid var(--border-color, rgba(255,255,255,0.1)); color: var(--text-main, white); padding: 0.9rem 1rem; border-radius: 0.85rem; margin-top: 0.8rem; font-family: inherit; transition: border-color 0.3s; }
        .form-card input:focus, .form-card textarea:focus, .form-card select:focus { outline: none; border-color: var(--tech-blue); background: var(--nav-hover, rgba(255,255,255,0.08)); }
        .form-card label { color: var(--text-muted); font-size: 0.9rem; }
        .form-card button { margin-top: 1rem; background: var(--tech-blue); border: none; color: white; padding: 0.9rem 1.4rem; border-radius: 999px; cursor: pointer; transition: 0.3s; }
        .form-card button:hover { opacity: 0.95; }
        .btn-back { display: inline-block; padding: 0.5rem 1rem; background: rgba(255,255,255,0.1); color: white; border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; }
        .btn-back:hover { background: rgba(255,255,255,0.2); }
        .alert { padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-error { background: #E74C3C; color: white; font-weight: 500; border: none; }
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
        <a href="admin_rules_list.php" class="nav-item active"><i class="fa-solid fa-gavel w-5"></i> Gestion des règles</a>
        <a href="admin_contrat.php" class="nav-item"><i class="fa-solid fa-file-contract w-5"></i> Gestion des contrats</a>

        <div style="margin-top: auto;">
            <div class="theme-toggle" onclick="toggleTheme()">
                <i id="theme-icon" class="fa-solid fa-sun w-5"></i> Changer le thème
            </div>
        </div>
    </aside>

    <main class="main-panel">
        <div class="hero-glow-bg-2" style="top: 0; right: 0; opacity: 0.5;"></div>

        <a href="admin_rules_list.php" class="btn-back animate-fade-up"><i class="fa-solid fa-arrow-left"></i> Retour à la liste</a>

        <header style="margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h1 style="font-family: 'Space Grotesk'; font-size: 2rem; color: var(--text-main, white);"><?php echo isset($currentRule) ? 'Modifier' : 'Ajouter'; ?> une <span style="color: var(--tech-blue)">règle</span></h1>
        </header>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                Vous n'avez pas rempli le formulaire correctement
                <?php if (!empty($errors['general'])): ?>
                    <div style="font-size: 0.85rem; margin-top: 0.5rem; opacity: 0.9;"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <section class="form-card animate-fade-up delay-2">
            <form id="ruleForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" novalidate>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="redirect_to" value="admin_rules_list.php">
                <input type="hidden" name="id_rule" value="<?php echo $currentRule['id_rule'] ?? ''; ?>">
                <!-- Le statut est masqué et par défaut actif, ou conserve l'ancien s'il existe -->
                <input type="hidden" name="statut" value="<?php echo htmlspecialchars($currentRule['statut'] ?? 'actif', ENT_QUOTES, 'UTF-8'); ?>">

                <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem;">
                    <label>
                        Titre de la règle *
                        <input type="text" id="titre" name="titre" class="<?php echo isset($errors['titre']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentRule['titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (isset($errors['titre'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>
                    <label>
                        Type de clause *
                        <input type="text" id="type" name="type" class="<?php echo isset($errors['type']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentRule['type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (isset($errors['type'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>
                    <label style="grid-column: span 2;">
                        Description détaillée (Unique) *
                        <textarea id="description" name="description" rows="3" class="<?php echo isset($errors['description']) ? 'has-error' : ''; ?>"><?php echo htmlspecialchars($currentRule['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>
                    <label>
                        Valeur (Nombre uniquement)
                        <input type="text" id="valeur" name="valeur" class="<?php echo isset($errors['valeur']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentRule['valeur'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="Nombre uniquement">
                        <?php if (isset($errors['valeur'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['valeur'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>
                    <label>
                        ID Contrat (Nombre uniquement)
                        <input type="text" id="id_contrat" name="id_contrat" class="<?php echo isset($errors['id_contrat']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentRule['id_contrat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="Nombre uniquement">
                        <?php if (isset($errors['id_contrat'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['id_contrat'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>
                </div>

                <button type="submit"><?php echo isset($currentRule) ? 'Enregistrer les modifications' : 'Créer la règle'; ?></button>
            </form>
        </section>
    </main>
</body>
</html>
