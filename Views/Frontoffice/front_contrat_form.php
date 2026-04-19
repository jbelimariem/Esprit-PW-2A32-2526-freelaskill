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
        $errors[] = "Contrat introuvable.";
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
        body { background: #050812; font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; overflow-x: hidden; }
        .sidebar { width: 280px; background: rgba(17, 24, 39, 0.4); border-right: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .main-panel { margin-left: 280px; flex: 1; padding: 3rem 4rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(37,99,235,0.1); color: white; border-right: 4px solid var(--tech-blue); }
        .btn-back { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); color: white; border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; border: 1px solid rgba(255,255,255,0.1); }
        .btn-back:hover { background: rgba(255,255,255,0.1); }
        .breadcrumb { display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; }
        .breadcrumb a { color: var(--tech-blue); text-decoration: none; transition: color 0.3s; }
        .breadcrumb a:hover { color: #60A5FA; }
        .breadcrumb i { font-size: 0.8rem; }
        
        .form-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 24px; padding: 3rem; margin-bottom: 2rem; max-width: 800px; }
        .form-card input, .form-card textarea, .form-card select { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 1rem 1.25rem; border-radius: 0.85rem; margin-top: 0.8rem; font-size: 0.95rem; transition: border-color 0.3s; }
        .form-card input:focus, .form-card textarea:focus, .form-card select:focus { outline: none; border-color: var(--tech-blue); }
        .form-card label { color: var(--text-muted); font-size: 0.95rem; display: block; margin-bottom: 1.5rem; font-weight: 500; }
        .form-card button { margin-top: 2rem; background: var(--tech-blue); border: none; color: white; padding: 1rem 2rem; border-radius: 999px; cursor: pointer; font-size: 1rem; font-weight: 600; transition: transform 0.3s, background 0.3s; width: 100%; }
        .form-card button:hover { background: #1D4ED8; transform: translateY(-2px); }
        .alert { padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; max-width: 800px; }
        .alert-error { background: rgba(248,113,113,0.15); color: #fecaca; }
    </style>
    <script>
        function validateForm(event) {
            const titre = document.getElementById('titre').value.trim();
            const description = document.getElementById('description').value.trim();
            const budget = document.getElementById('budget').value.trim();
            const delai = document.getElementById('delai').value.trim();
            
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
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; letter-spacing: 1px;">Espace <?php echo $roleName; ?></p>
        </div>
        
        <?php if ($isClient): ?>
            <a href="client_dashboard.html" class="nav-item"><i class="fa-solid fa-chart-pie w-5"></i> Tableau de bord</a>
            <a href="publish_job.html" class="nav-item"><i class="fa-solid fa-plus-circle w-5"></i> Lancer un Projet</a>
        <?php else: ?>
            <a href="freelancer_jobs.html" class="nav-item"><i class="fa-solid fa-compass w-5"></i> Explorer Missions</a>
        <?php endif; ?>
        
        <a href="front_rules_index.php" class="nav-item"><i class="fa-solid fa-gavel w-5"></i> Mes Règles</a>
        <a href="front_contrat_index.php" class="nav-item active"><i class="fa-solid fa-file-contract w-5"></i> Mes Contrats</a>

        <div style="margin-top: auto; padding: 2rem;">
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

        <div id="js-errors" class="alert alert-error" style="display: none;"></div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error animate-fade-up">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="form-card animate-fade-up delay-2">
            <form id="contratForm" method="post" action="front_contrat_list.php">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="redirect_to" value="front_contrat_list.php">
                <input type="hidden" name="statut" value="<?php echo htmlspecialchars($currentContrat['statut'] ?? 'brouillon', ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id_contrat" value="<?php echo intval($currentContrat['id_contrat']); ?>">
                <?php endif; ?>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 0 1.5rem;">
                    <label style="grid-column: span 2;">
                        Titre du contrat *
                        <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($currentContrat['titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required maxlength="255" placeholder="Ex: Contrat de prestation de services IT">
                    </label>

                    <label style="grid-column: span 2;">
                        Description détaillée *
                        <textarea id="description" name="description" rows="6" required placeholder="Détaillez les conditions, livrables et attentes du contrat..."><?php echo htmlspecialchars($currentContrat['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>

                    <label>
                        Budget (DT) *
                        <input type="text" id="budget" name="budget" value="<?php echo htmlspecialchars($currentContrat['budget'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required placeholder="Ex: 2500.00">
                    </label>

                    <label>
                        Délai de réalisation (Jours) *
                        <input type="text" id="delai" name="delai" value="<?php echo htmlspecialchars($currentContrat['delai'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required placeholder="Ex: 45">
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
