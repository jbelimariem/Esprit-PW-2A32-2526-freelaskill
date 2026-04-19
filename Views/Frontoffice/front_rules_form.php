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
        .form-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: var(--radius-lg); padding: 2.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 1rem; border-radius: 0.85rem; font-family: inherit; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: var(--tech-blue); background: rgba(255,255,255,0.08); }
        .alert { padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; }
        .alert-error { background: rgba(248,113,113,0.15); color: #fecaca; }
        .btn-back { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); color: white; border-radius: 999px; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; border: 1px solid rgba(255,255,255,0.1); }
        .btn-back:hover { background: rgba(255,255,255,0.1); }
        .breadcrumb { display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; }
        .breadcrumb a { color: var(--tech-blue); text-decoration: none; transition: color 0.3s; }
        .breadcrumb a:hover { color: #60A5FA; }
        .breadcrumb i { font-size: 0.8rem; }
        
        /* SIDEBAR STYLES */
        body { background: #050812; font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; overflow-x: hidden; }
        .sidebar { width: 280px; background: rgba(17, 24, 39, 0.4); border-right: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px); flex-shrink: 0; padding: 2rem 0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .main-panel { margin-left: 280px; flex: 1; padding: 3rem 4rem; position: relative; }
        .nav-item { padding: 1rem 2rem; color: var(--text-muted); display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: var(--transition); font-size: 0.95rem; font-weight: 500; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(37,99,235,0.1); color: white; border-right: 4px solid var(--tech-blue); }
    </style>
    <script>
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
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; letter-spacing: 1px;">Espace <?php echo $roleName; ?></p>
        </div>
        
        <?php if ($isClient): ?>
            <a href="client_dashboard.html" class="nav-item"><i class="fa-solid fa-chart-pie w-5"></i> Tableau de bord</a>
            <a href="publish_job.html" class="nav-item"><i class="fa-solid fa-plus-circle w-5"></i> Lancer un Projet</a>
        <?php else: ?>
            <a href="freelancer_jobs.html" class="nav-item"><i class="fa-solid fa-compass w-5"></i> Explorer Missions</a>
        <?php endif; ?>
        
        <a href="front_rules_index.php" class="nav-item active"><i class="fa-solid fa-gavel w-5"></i> Mes Règles</a>

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
            <a href="front_rules_index.php">Gestion des règles</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span style="color: white;"><?php echo isset($currentRule) ? 'Modifier' : 'Créer'; ?></span>
        </div>

        <a href="front_rules_index.php" class="btn-back animate-fade-up delay-1"><i class="fa-solid fa-arrow-left"></i> Retour au menu</a>

        <div style="margin-bottom: 3rem;" class="animate-fade-up delay-1">
            <h1 style="font-size: 2.5rem; color: white; margin-bottom: 0.5rem;"><?php echo isset($currentRule) ? 'Modifier' : 'Nouvelle'; ?> <span style="color: var(--tech-blue)">Règle</span></h1>
            <p style="color: var(--text-muted);">Veuillez remplir les informations de la règle.</p>
        </div>

        <div id="js-errors" class="alert alert-error" style="display: none;"></div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="form-card animate-fade-up delay-2">
            <form id="ruleForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="redirect_to" value="front_rules_list.php">
                <input type="hidden" name="id_rule" value="<?php echo $currentRule['id_rule'] ?? ''; ?>">
                <!-- Le statut est masqué et par défaut actif, ou conserve l'ancien s'il existe -->
                <input type="hidden" name="statut" value="<?php echo htmlspecialchars($currentRule['statut'] ?? 'actif', ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-group">
                    <label>Titre de la règle *</label>
                    <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($currentRule['titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>Type de clause *</label>
                        <input type="text" id="type" name="type" value="<?php echo htmlspecialchars($currentRule['type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Valeur (Nombre uniquement)</label>
                        <input type="text" id="valeur" name="valeur" value="<?php echo htmlspecialchars($currentRule['valeur'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" pattern="[0-9]*" title="Nombre uniquement">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description détaillée (Unique) *</label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($currentRule['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="form-group">
                    <label>ID du Contrat associé (Nombre uniquement)</label>
                    <input type="text" id="id_contrat" name="id_contrat" value="<?php echo htmlspecialchars($currentRule['id_contrat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" pattern="[0-9]*" title="Nombre uniquement">
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
