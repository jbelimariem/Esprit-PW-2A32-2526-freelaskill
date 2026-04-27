<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header('Location: front_rules_index.php');
    exit;
}
$role = $_SESSION['user_role'];
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';
$roleIcon = $isClient ? 'fa-user-tie' : 'fa-laptop-code';

require_once __DIR__ . '/../../controllers/ruleController.php';
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace <?php echo $roleName; ?> - <?php echo isset($currentRule) ? 'Modifier' : 'Nouvelle'; ?> Règle</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: var(--bg-main, #050812); font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; overflow-x: hidden; transition: background 0.3s, color 0.3s; color: var(--text-main, white); }
        .sidebar { width: 280px; background: var(--bg-sidebar, rgba(17, 24, 39, 0.4)); border-right: 1px solid var(--border-color, rgba(255,255,255,0.05)); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: background 0.3s; }
        .main-panel { margin-left: 280px; flex: 1; padding: 3rem 4rem; position: relative; }
        
        .form-card { background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: var(--radius-lg, 24px); padding: 2.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; background: var(--input-bg, rgba(255,255,255,0.05)); border: 1px solid var(--border-color, rgba(255,255,255,0.1)); color: var(--text-main, white); padding: 1rem; border-radius: 0.85rem; font-family: inherit; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: var(--tech-blue); background: var(--nav-hover, rgba(255,255,255,0.08)); }
        .alert { padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-error { background: #E74C3C; color: white; font-weight: 500; border: none; }
        .field-error { margin-top: 0.45rem; color: #fca5a5; font-size: 0.8rem; display: block; }
        .form-group input.has-error, .form-group textarea.has-error, .form-group select.has-error { border-color: #E74C3C; }
        .btn-back { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: var(--btn-back-bg, rgba(255,255,255,0.05)); color: var(--text-main, white); border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; border: 1px solid var(--border-color, rgba(255,255,255,0.1)); }
        .btn-back:hover { background: var(--btn-back-hover, rgba(255,255,255,0.1)); }
        .breadcrumb { display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; }
        .breadcrumb a { color: var(--tech-blue); text-decoration: none; transition: color 0.3s; }
        .breadcrumb a:hover { color: #60A5FA; }
        .breadcrumb i { font-size: 0.8rem; }
        
        /* SIDEBAR NAV */
        .side-nav { padding: 0.75rem; display: flex; flex-direction: column; gap: 0.25rem; }
        .side-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm, 8px);
            color: var(--text-muted, #9ca3af);
            font-size: 0.88rem;
            transition: var(--transition, 0.3s);
            text-decoration: none;
        }
        .side-nav a:hover, .side-nav a.active { color: var(--text-main, white); background: var(--nav-hover, rgba(255,255,255,0.03)); }
        .side-nav a.active { border-left: 3px solid var(--tech-blue, #3b82f6); }
        .side-nav a.danger { color: var(--tunisian-red, #e3000f); }
        .side-nav a.danger:hover { background: rgba(239,68,68,0.1); }
        .nav-avatar.has-image {
            padding: 0;
            overflow: hidden;
            background: rgba(15,23,42,0.95);
        }
        .nav-avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 50%;
        }

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
            --btn-back-bg: #e2e8f0;
            --btn-back-hover: #cbd5e1;
        }

        .theme-toggle {
            cursor: pointer;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-muted);
            border-radius: var(--radius-sm, 8px);
            transition: var(--transition, 0.3s);
            margin: 0 0.75rem;
            font-size: 0.88rem;
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
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; letter-spacing: 1px;">Espace <?php echo $roleName; ?></p>
        </div>
        
        <nav class="side-nav">
            <?php if ($isClient): ?>
                <a href="client_dashboard.html"><i class="fa-solid fa-chart-pie w-5"></i> Tableau de bord</a>
                <a href="publish_job.html"><i class="fa-solid fa-plus-circle w-5"></i> Lancer un Projet</a>
            <?php else: ?>
                <a href="freelancer_jobs.html"><i class="fa-solid fa-compass w-5"></i> Explorer Missions</a>
            <?php endif; ?>
            
            <a href="front_rules_index.php" class="active"><i class="fa-solid fa-gavel w-5"></i> Mes Règles</a>
            <a href="front_contrat_index.php"><i class="fa-solid fa-file-contract w-5"></i> Mes Contrats</a>
        </nav>

        <div style="margin-top: auto; padding: 2rem 0;">
            <div class="theme-toggle" onclick="toggleTheme()">
                <i id="theme-icon" class="fa-solid fa-sun w-5"></i> Changer le thème
            </div>
            <a href="front_rules_role.php" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; color: #F87171; text-decoration: none; font-size: 0.85rem; padding: 0.8rem; border: 1px solid rgba(248,113,113,0.3); border-radius: 999px; transition: 0.3s; margin: 0 0.75rem;">
                <i class="fa-solid fa-right-from-bracket"></i> Changer de profil
            </a>
        </div>
    </aside>
    
    <div class="hero-glow-bg-2" style="top: 10%; right: 0; opacity: 0.5;"></div>

    <main class="main-panel">
        
        <div class="breadcrumb animate-fade-up">
            <a href="#"><i class="fa-solid fa-home"></i> Accueil</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Espace <?php echo $roleName; ?></span>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="front_rules_index.php">Gestion des règles</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span style="color: white;"><?php echo isset($currentRule) ? 'Modifier' : 'Créer'; ?></span>
        </div>

        <a href="front_rules_index.php" class="btn-back animate-fade-up delay-1"><i class="fa-solid fa-arrow-left"></i> Retour au menu</a>

        <div style="margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h1 style="font-size: 2.5rem; color: white; margin-bottom: 0.5rem;"><?php echo isset($currentRule) ? 'Modifier' : 'Nouvelle'; ?> <span style="color: var(--tech-blue)">Règle</span></h1>
            <p style="color: var(--text-muted);">Veuillez remplir les informations de la règle.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                Vous n'avez pas rempli le formulaire correctement
                <?php if (!empty($errors['general'])): ?>
                    <div style="font-size: 0.85rem; margin-top: 0.5rem; opacity: 0.9;"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-card animate-fade-up delay-2">
            <form id="ruleForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" novalidate>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="redirect_to" value="front_rules_list.php">
                <input type="hidden" name="id_rule" value="<?php echo $currentRule['id_rule'] ?? ''; ?>">
                <!-- Le statut est masqué et par défaut actif, ou conserve l'ancien s'il existe -->
                <input type="hidden" name="statut" value="<?php echo htmlspecialchars($currentRule['statut'] ?? 'actif', ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-group">
                    <label>Titre de la règle *</label>
                    <input type="text" id="titre" name="titre" class="<?php echo isset($errors['titre']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentRule['titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if (isset($errors['titre'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>Type de clause *</label>
                        <input type="text" id="type" name="type" class="<?php echo isset($errors['type']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentRule['type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (isset($errors['type'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Valeur (Nombre uniquement)</label>
                        <input type="text" id="valeur" name="valeur" class="<?php echo isset($errors['valeur']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentRule['valeur'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="Nombre uniquement">
                        <?php if (isset($errors['valeur'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['valeur'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description détaillée (Unique) *</label>
                    <textarea id="description" name="description" rows="4" class="<?php echo isset($errors['description']) ? 'has-error' : ''; ?>"><?php echo htmlspecialchars($currentRule['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>ID du Contrat associé (Nombre uniquement)</label>
                    <input type="text" id="id_contrat" name="id_contrat" class="<?php echo isset($errors['id_contrat']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentRule['id_contrat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="Nombre uniquement">
                    <?php if (isset($errors['id_contrat'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['id_contrat'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                        <i class="fa-solid fa-save"></i> <?php echo isset($currentRule) ? 'Enregistrer les modifications' : 'Créer la règle'; ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
