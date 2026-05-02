<?php
session_start();
if (!isset($_SESSION['user_role'])) { header('Location: front_rules_index.php'); exit; }
$role = $_SESSION['user_role'];
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';
$activePage = 'rules';
require_once __DIR__ . '/../../controllers/ruleController.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace <?php echo $roleName; ?> — <?php echo isset($currentRule) ? 'Modifier' : 'Nouvelle'; ?> Règle · FreelaSkill</title>
    <link rel="stylesheet" href="css/front.css?v=5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        window.API_BASE = '/Esprit-PW-2A32-2526-TalentBridge-job/controllers/apiController.php';
    </script>
    <script src="css/front.js" defer></script>
    <script src="../assets/api.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="glow-orb" style="width:400px;height:400px;background:#A855F7;top:-100px;right:-100px;"></div>

<main class="admin-main" style="padding-top:1.5rem;">

    <div class="admin-topbar animate-in">
        <div>
            <div class="admin-breadcrumb">
                <i class="fa-solid fa-house"></i>
                <span class="sep">/</span>
                <a href="front_rules_index.php">Règles</a>
                <span class="sep">/</span>
                <a href="front_rules_list.php">Liste</a>
                <span class="sep">/</span>
                <span class="current"><?php echo isset($currentRule) ? 'Modifier' : 'Créer'; ?></span>
            </div>
            <h1 class="admin-page-title"><?php echo isset($currentRule) ? 'Modifier la' : 'Nouvelle'; ?> <span>Règle</span></h1>
        </div>
        <div class="topbar-actions">
            <a href="front_rules_list.php" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error animate-in">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>Formulaire incomplet.
                <?php if (!empty($errors['general'])): ?>
                    <div style="font-size:0.85rem;margin-top:0.3rem;opacity:0.85;"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <form id="ruleForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" novalidate>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="redirect_to" value="front_rules_list.php">
        <input type="hidden" name="id_rule" value="<?php echo $currentRule['id_rule'] ?? ''; ?>">
        <input type="hidden" name="statut" value="<?php echo htmlspecialchars($currentRule['statut'] ?? 'actif', ENT_QUOTES, 'UTF-8'); ?>">

        <div class="form-section animate-in delay-1">
            <div class="form-section-title"><i class="fa-solid fa-gavel"></i> Informations de la règle</div>

            <!-- Barre d'outils API -->
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1.5rem;padding:0.85rem 1rem;background:rgba(168,85,247,0.05);border:1px solid rgba(168,85,247,0.15);border-radius:12px;align-items:center;">
                <span style="font-size:0.75rem;font-weight:700;color:#A855F7;text-transform:uppercase;letter-spacing:0.8px;margin-right:0.5rem;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Outils IA
                </span>
                <select id="lang-select" style="padding:0.4rem 0.75rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:999px;color:#94A3B8;font-size:0.8rem;font-family:inherit;cursor:pointer;outline:none;">
                    <option value="fr">🇫🇷 FR</option>
                    <option value="en">🇬🇧 EN</option>
                    <option value="ar">🇹🇳 AR</option>
                </select>
                <button type="button" id="btn-suggest-rules" onclick="suggestRules(this)"
                        style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.4rem 0.9rem;background:rgba(245,158,11,0.12);color:#F59E0B;border:1px solid rgba(245,158,11,0.25);border-radius:999px;font-size:0.8rem;font-weight:600;cursor:pointer;font-family:inherit;">
                    <i class="fa-solid fa-lightbulb"></i> Suggérer des règles
                </button>
                <div style="display:inline-flex;align-items:center;gap:0.3rem;">
                    <select id="translate-from" style="padding:0.4rem 0.6rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:999px;color:#94A3B8;font-size:0.78rem;font-family:inherit;cursor:pointer;outline:none;">
                        <option value="fr">FR</option><option value="en">EN</option><option value="ar">AR</option>
                    </select>
                    <span style="color:#475569;font-size:0.75rem;">→</span>
                    <select id="translate-to" style="padding:0.4rem 0.6rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:999px;color:#94A3B8;font-size:0.78rem;font-family:inherit;cursor:pointer;outline:none;">
                        <option value="en">EN</option><option value="fr">FR</option><option value="ar">AR</option>
                    </select>
                    <button type="button"
                            onclick="(async()=>{const f=document.getElementById('translate-from').value,t=document.getElementById('translate-to').value;await translateField('titre','titre',f,t,null);await translateField('description','description',f,t,this);})()"
                            style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.4rem 0.9rem;background:rgba(16,185,129,0.12);color:#10B981;border:1px solid rgba(16,185,129,0.25);border-radius:999px;font-size:0.8rem;font-weight:600;cursor:pointer;font-family:inherit;">
                        <i class="fa-solid fa-language"></i> Traduire
                    </button>
                </div>
                <label style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.4rem 0.9rem;background:rgba(37,99,235,0.12);color:#60A5FA;border:1px solid rgba(37,99,235,0.25);border-radius:999px;font-size:0.8rem;font-weight:600;cursor:pointer;">
                    <i class="fa-solid fa-camera"></i> OCR
                    <input type="file" accept="image/*" style="display:none;"
                           onchange="processOcrImage(this.files[0],{titre:'titre',description:'description'},this.nextElementSibling)">
                    <span></span>
                </label>
            </div>
            <div id="ocr-zone"></div>

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">Titre <span style="color:#EF4444;">*</span></label>
                    <input type="text" id="titre" name="titre" class="form-input <?php echo isset($errors['titre']) ? 'has-error' : ''; ?>"
                           value="<?php echo htmlspecialchars($currentRule['titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Ex: Clause de confidentialité">
                    <?php if (isset($errors['titre'])): ?><span class="field-error"><?php echo htmlspecialchars($errors['titre'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Type de clause <span style="color:#EF4444;">*</span></label>
                    <input type="text" id="type" name="type" class="form-input <?php echo isset($errors['type']) ? 'has-error' : ''; ?>"
                           value="<?php echo htmlspecialchars($currentRule['type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Ex: NDA, Pénalité, Délai">
                    <?php if (isset($errors['type'])): ?><span class="field-error"><?php echo htmlspecialchars($errors['type'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                </div>

                <div class="form-group full">
                    <label class="form-label">Description détaillée <span style="color:#EF4444;">*</span></label>
                    <textarea id="description" name="description" rows="4" class="form-textarea <?php echo isset($errors['description']) ? 'has-error' : ''; ?>"
                              placeholder="Décrivez précisément la règle et ses conditions..."><?php echo htmlspecialchars($currentRule['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <?php if (isset($errors['description'])): ?><span class="field-error"><?php echo htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Valeur <span style="font-weight:400;color:var(--text-muted);">(numérique)</span></label>
                    <input type="text" id="valeur" name="valeur" class="form-input <?php echo isset($errors['valeur']) ? 'has-error' : ''; ?>"
                           value="<?php echo htmlspecialchars($currentRule['valeur'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Ex: 500">
                    <?php if (isset($errors['valeur'])): ?><span class="field-error"><?php echo htmlspecialchars($errors['valeur'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Titre du contrat associé</label>
                    <input type="text" id="titre_contrat" name="titre_contrat" class="form-input <?php echo isset($errors['titre_contrat']) ? 'has-error' : ''; ?>"
                           value="<?php echo htmlspecialchars($currentRule['titre_contrat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Ex: Contrat de prestation IT">
                    <?php if (isset($errors['titre_contrat'])): ?><span class="field-error"><?php echo htmlspecialchars($errors['titre_contrat'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                </div>

            </div>
        </div>

        <div class="form-actions animate-in delay-2">
            <a href="front_rules_list.php" class="btn btn-secondary" style="padding:0.9rem 2rem;">Annuler</a>
            <button type="submit" class="btn-submit">
                <?php echo isset($currentRule)
                    ? '<i class="fa-solid fa-save"></i> Enregistrer les modifications'
                    : '<i class="fa-solid fa-plus"></i> Créer la règle'; ?>
            </button>
        </div>
    </form>

</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
    setupContentCheck('ruleForm', ['titre', 'description']);
});
</script>
</body>
</html>
