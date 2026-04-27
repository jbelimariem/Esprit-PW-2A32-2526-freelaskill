<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header('Location: front_rules_role.php');
    exit;
}

require_once __DIR__ . '/../../controllers/contratController.php';

$role = $_SESSION['user_role'];
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';

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
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace <?php echo $roleName; ?> - <?php echo $isEdit ? 'Modifier' : 'Nouveau'; ?> Contrat</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: var(--bg-main, #050812); font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; overflow-x: hidden; transition: background 0.3s, color 0.3s; color: var(--text-main, white); }
        .sidebar { width: 280px; background: var(--bg-sidebar, rgba(17, 24, 39, 0.4)); border-right: 1px solid var(--border-color, rgba(255,255,255,0.05)); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: background 0.3s; }
        .main-panel { margin-left: 280px; flex: 1; padding: 3rem 4rem; position: relative; }
        
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

        .btn-back { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: var(--btn-back-bg, rgba(255,255,255,0.05)); color: var(--text-main, white); border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; border: 1px solid var(--border-color, rgba(255,255,255,0.1)); }
        .btn-back:hover { background: var(--btn-back-hover, rgba(255,255,255,0.1)); }
        .breadcrumb { display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; }
        .breadcrumb a { color: var(--tech-blue); text-decoration: none; transition: color 0.3s; }
        .breadcrumb a:hover { color: #60A5FA; }
        .breadcrumb i { font-size: 0.8rem; }
        
        .form-card { background: var(--card-bg, rgba(255,255,255,0.02)); border: 1px solid var(--border-color, rgba(255,255,255,0.05)); border-radius: 24px; padding: 3rem; margin-bottom: 2rem; max-width: 800px; }
        .form-card input, .form-card textarea, .form-card select { width: 100%; background: var(--input-bg, rgba(255,255,255,0.05)); border: 1px solid var(--border-color, rgba(255,255,255,0.1)); color: var(--text-main, white); padding: 1rem 1.25rem; border-radius: 0.85rem; margin-top: 0.8rem; font-size: 0.95rem; transition: border-color 0.3s; }
        .form-card input:focus, .form-card textarea:focus, .form-card select:focus { outline: none; border-color: var(--tech-blue); }
        .form-card label { color: var(--text-muted); font-size: 0.95rem; display: block; margin-bottom: 1.5rem; font-weight: 500; }
        .form-card button { margin-top: 2rem; background: var(--tech-blue); border: none; color: white; padding: 1rem 2rem; border-radius: 999px; cursor: pointer; font-size: 1rem; font-weight: 600; transition: transform 0.3s, background 0.3s; width: 100%; }
        .form-card button:hover { background: #1D4ED8; transform: translateY(-2px); }
        .alert { padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; max-width: 800px; }
        .alert-error { background: #E74C3C; color: white; font-weight: 500; border: none; }
        
        .field-error { margin-top: 0.45rem; color: #fca5a5; font-size: 0.8rem; display: block; }
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
            
            <a href="front_rules_index.php"><i class="fa-solid fa-gavel w-5"></i> Mes Règles</a>
            <a href="front_contrat_index.php" class="active"><i class="fa-solid fa-file-contract w-5"></i> Mes Contrats</a>
        </nav>

        <div style="margin-top: auto; padding: 2rem 0;">
            <div class="theme-toggle" onclick="toggleTheme()">
                <i id="theme-icon" class="fa-solid fa-sun w-5"></i> Changer le thème
            </div>
            <a href="front_rules_role.php" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; color: #F87171; text-decoration: none; font-size: 0.85rem; padding: 0.8rem; border: 1px solid rgba(248,113,113,0.3); border-radius: 999px; transition: 0.3s;">
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
            <a href="front_contrat_index.php">Gestion des contrats</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span style="color: white;"><?php echo $isEdit ? 'Modifier' : 'Créer'; ?></span>
        </div>

        <a href="front_contrat_list.php" class="btn-back animate-fade-up delay-1"><i class="fa-solid fa-arrow-left"></i> Retour à la liste</a>

        <div style="margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h1 style="font-size: 2.5rem; color: white; margin-bottom: 0.5rem;"><?php echo $isEdit ? 'Modifier le' : 'Nouveau'; ?> <span style="color: var(--tech-blue)">Contrat</span></h1>
            <p style="color: var(--text-muted);">Veuillez remplir les informations concernant le contrat.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error animate-fade-up">
                Vous n'avez pas rempli le formulaire correctement
                <?php if (!empty($errors['general'])): ?>
                    <div style="font-size: 0.85rem; margin-top: 0.5rem; opacity: 0.9;"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <section class="form-card animate-fade-up delay-2">
            <form id="contratForm" method="post" action="front_contrat_list.php" novalidate>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="redirect_to" value="front_contrat_list.php">
                <input type="hidden" name="statut" value="<?php echo htmlspecialchars($currentContrat['statut'] ?? 'brouillon', ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id_contrat" value="<?php echo intval($currentContrat['id_contrat']); ?>">
                <?php endif; ?>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 0 1.5rem;">
                    <label style="grid-column: span 2;">
                        Titre du contrat *
                        <input type="text" id="titre" name="titre" class="<?php echo isset($errors['titre']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentContrat['titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex: Contrat de prestation de services IT">
                        <?php if (isset($errors['titre'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>

                    <label style="grid-column: span 2;">
                        Description détaillée *
                        <textarea id="description" name="description" rows="6" class="<?php echo isset($errors['description']) ? 'has-error' : ''; ?>" placeholder="Détaillez les conditions, livrables et attentes du contrat..."><?php echo htmlspecialchars($currentContrat['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>

                    <label>
                        Budget (DT) *
                        <input type="text" id="budget" name="budget" class="<?php echo isset($errors['budget']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentContrat['budget'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex: 2500.00">
                        <?php if (isset($errors['budget'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['budget'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>

                    <label>
                        Délai de réalisation (Jours) *
                        <input type="text" id="delai" name="delai" class="<?php echo isset($errors['delai']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentContrat['delai'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex: 45">
                        <?php if (isset($errors['delai'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['delai'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>
                    
                    <label style="grid-column: span 2;">
                        Freelancer approuvé (Nom & Job) *
                        <input type="text" id="freelance_info" name="freelance_info" class="<?php echo isset($errors['freelance_info']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentContrat['freelance_info'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex: John Doe - Développeur Web">
                        <?php if (isset($errors['freelance_info'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['freelance_info'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>

                    <label>
                        Signature Client *
                        <input type="text" id="signature_client" name="signature_client" class="<?php echo isset($errors['signature_client']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentContrat['signature_client'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tapez votre nom pour signer">
                        <?php if (isset($errors['signature_client'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['signature_client'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>

                    <label>
                        Signature Freelancer *
                        <input type="text" id="signature_freelance" name="signature_freelance" class="<?php echo isset($errors['signature_freelance']) ? 'has-error' : ''; ?>" value="<?php echo htmlspecialchars($currentContrat['signature_freelance'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Attente signature freelancer">
                        <?php if (isset($errors['signature_freelance'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['signature_freelance'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </label>

                    <label style="grid-column: span 2;">
                        Sélectionner des règles existantes (Optionnel)
                        <select name="selected_rules[]" multiple style="height: 120px;">
                            <?php foreach ($availableRules as $rule): ?>
                                <?php $selected = in_array($rule['id_rule'], $selectedRuleIds) ? 'selected' : ''; ?>
                                <option value="<?php echo $rule['id_rule']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($rule['titre'], ENT_QUOTES, 'UTF-8'); ?> 
                                    (Type: <?php echo htmlspecialchars($rule['type'], ENT_QUOTES, 'UTF-8'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 0.5rem;">
                            Maintenez Ctrl (Windows) ou Cmd (Mac) pour sélectionner plusieurs règles. 
                            Note : ces règles seront définitivement liées à ce contrat.
                        </span>
                    </label>
                </div>

                <button type="submit">
                    <?php echo $isEdit ? '<i class="fa-solid fa-save"></i> Enregistrer les modifications' : '<i class="fa-solid fa-paper-plane"></i> Créer le contrat'; ?>
                </button>
            </form>
        </section>
    </main>
</body>
</html>
