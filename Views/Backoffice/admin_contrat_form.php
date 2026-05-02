<?php
$activePage = 'contrats';
require_once __DIR__ . '/../../controllers/contratController.php';

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
if ($isEdit && !empty($currentContrat)) {
    foreach ($availableRules as $r) {
        if (!empty($r['titre_contrat']) && $r['titre_contrat'] === $currentContrat['titre']) {
            $selectedRuleIds[] = $r['id_rule'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors)) {
    $currentContrat = [
        'titre'              => $_POST['titre'] ?? '',
        'description'        => $_POST['description'] ?? '',
        'budget'             => $_POST['budget'] ?? '',
        'delai'              => $_POST['delai'] ?? '',
        'statut'             => $_POST['statut'] ?? 'brouillon',
        'freelance_info'     => $_POST['freelance_info'] ?? '',
        'signature_client'   => $_POST['signature_client'] ?? '',
        'signature_freelance'=> $_POST['signature_freelance'] ?? '',
        'id_contrat'         => $_POST['id_contrat'] ?? null,
    ];
    $selectedRuleIds = $_POST['selected_rules'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — <?php echo $isEdit ? 'Modifier' : 'Créer'; ?> un Contrat · FreelaSkill</title>
    <link rel="stylesheet" href="css/admin.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <script>
        window.API_BASE = '/Esprit-PW-2A32-2526-TalentBridge-job/controllers/apiController.php';
    </script>
    <script src="css/admin.js" defer></script>
    <script src="../assets/api.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="glow-orb" style="width:400px;height:400px;background:#2563EB;top:-100px;right:-100px;"></div>

<main class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar animate-in">
        <div>
            <div class="admin-breadcrumb">
                <i class="fa-solid fa-house"></i>
                <span class="sep">/</span>
                <a href="admin_contrat.php">Contrats</a>
                <span class="sep">/</span>
                <a href="admin_contrat_list.php">Liste</a>
                <span class="sep">/</span>
                <span class="current"><?php echo $isEdit ? 'Modifier' : 'Créer'; ?></span>
            </div>
            <h1 class="admin-page-title"><?php echo $isEdit ? 'Modifier le' : 'Nouveau'; ?> <span>Contrat</span></h1>
        </div>
        <div class="topbar-actions">
            <a href="admin_contrat_list.php" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour à la liste
            </a>
            <div class="admin-badge"><i class="fa-solid fa-user-shield"></i> SuperAdmin</div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error animate-in">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                Formulaire incomplet.
                <?php if (!empty($errors['general'])): ?>
                    <div style="font-size:0.85rem;margin-top:0.3rem;opacity:0.85;"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <form id="contratForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" novalidate>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="redirect_to" value="admin_contrat_list.php">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id_contrat" value="<?php echo intval($currentContrat['id_contrat']); ?>">
        <?php endif; ?>

        <!-- Section 1 : Informations générales -->
        <div class="form-section animate-in delay-1">
            <div class="form-section-title"><i class="fa-solid fa-circle-info"></i> Informations générales</div>

            <!-- Barre d'outils API -->
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1.5rem;padding:0.85rem 1rem;background:rgba(37,99,235,0.05);border:1px solid rgba(37,99,235,0.15);border-radius:12px;align-items:center;">
                <span style="font-size:0.75rem;font-weight:700;color:#60A5FA;text-transform:uppercase;letter-spacing:0.8px;margin-right:0.5rem;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Outils IA
                </span>
                <select id="lang-select" style="padding:0.4rem 0.75rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:999px;color:#94A3B8;font-size:0.8rem;font-family:inherit;cursor:pointer;outline:none;">
                    <option value="fr">🇫🇷 FR</option>
                    <option value="en">🇬🇧 EN</option>
                    <option value="ar">🇹🇳 AR</option>
                </select>
                <button type="button" id="btn-generate-desc" onclick="generateDescription(this)"
                        style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.4rem 0.9rem;background:rgba(168,85,247,0.12);color:#A855F7;border:1px solid rgba(168,85,247,0.25);border-radius:999px;font-size:0.8rem;font-weight:600;cursor:pointer;font-family:inherit;">
                    <i class="fa-solid fa-robot"></i> Générer description
                </button>
                <button type="button" id="btn-suggest-rules" onclick="suggestRules(this)"
                        style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.4rem 0.9rem;background:rgba(245,158,11,0.12);color:#F59E0B;border:1px solid rgba(245,158,11,0.25);border-radius:999px;font-size:0.8rem;font-weight:600;cursor:pointer;font-family:inherit;">
                    <i class="fa-solid fa-lightbulb"></i> Suggérer règles
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
                            onclick="translateContratFields(document.getElementById('translate-from').value,document.getElementById('translate-to').value,this)"
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
                <div class="form-group full">
                    <label class="form-label">Titre du contrat <span class="required">*</span></label>
                    <input type="text" id="titre" name="titre" class="form-input <?php echo isset($errors['titre']) ? 'has-error' : ''; ?>"
                           value="<?php echo htmlspecialchars($currentContrat['titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Ex: Développement du site e-commerce">
                    <?php if (isset($errors['titre'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group full">
                    <label class="form-label">Description détaillée <span class="required">*</span></label>
                    <textarea id="description" name="description" rows="5" class="form-textarea <?php echo isset($errors['description']) ? 'has-error' : ''; ?>"
                              placeholder="Décrivez les attentes, livrables et conditions..."><?php echo htmlspecialchars($currentContrat['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Budget (DT) <span class="required">*</span></label>
                    <input type="text" id="budget" name="budget" class="form-input <?php echo isset($errors['budget']) ? 'has-error' : ''; ?>"
                           value="<?php echo htmlspecialchars($currentContrat['budget'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Ex: 1500.00">
                    <?php if (isset($errors['budget'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['budget'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Délai (jours) <span class="required">*</span></label>
                    <input type="text" id="delai" name="delai" class="form-input <?php echo isset($errors['delai']) ? 'has-error' : ''; ?>"
                           value="<?php echo htmlspecialchars($currentContrat['delai'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Ex: 30">
                    <?php if (isset($errors['delai'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['delai'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <select id="statut" name="statut" class="form-select">
                        <?php
                        $statuts = ['brouillon','en_attente','actif','termine','annule'];
                        $cur = $currentContrat['statut'] ?? 'brouillon';
                        foreach ($statuts as $s) {
                            $sel = $s === $cur ? 'selected' : '';
                            echo "<option value=\"$s\" $sel>" . ucfirst(str_replace('_', ' ', $s)) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Freelancer (Nom & Poste) <span class="required">*</span></label>
                    <input type="text" id="freelance_info" name="freelance_info" class="form-input <?php echo isset($errors['freelance_info']) ? 'has-error' : ''; ?>"
                           value="<?php echo htmlspecialchars($currentContrat['freelance_info'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Ex: John Doe - Développeur Web">
                    <?php if (isset($errors['freelance_info'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['freelance_info'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Section 2 : Règles associées avec pagination -->
        <div class="form-section animate-in delay-2">
            <div class="form-section-title"><i class="fa-solid fa-gavel"></i> Règles associées <span style="font-weight:400;font-size:0.8rem;color:var(--text-muted);">(optionnel)</span></div>

            <div class="rules-picker">
                <!-- Barre de recherche -->
                <div class="rules-picker-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="ruleSearch" placeholder="Rechercher une règle..." oninput="filterRules()">
                </div>

                <!-- Liste paginée -->
                <div class="rules-picker-list" id="rulesList">
                    <?php if (empty($availableRules)): ?>
                        <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:0.88rem;">
                            <i class="fa-solid fa-inbox" style="display:block;font-size:1.5rem;margin-bottom:0.5rem;opacity:0.4;"></i>
                            Aucune règle disponible.
                        </div>
                    <?php else: ?>
                        <?php foreach ($availableRules as $rule): ?>
                            <label class="rule-pick-item" data-title="<?php echo strtolower(htmlspecialchars($rule['titre'], ENT_QUOTES, 'UTF-8')); ?>" data-type="<?php echo strtolower(htmlspecialchars($rule['type'], ENT_QUOTES, 'UTF-8')); ?>">
                                <input type="checkbox" name="selected_rules[]" value="<?php echo $rule['id_rule']; ?>"
                                       <?php echo in_array($rule['id_rule'], $selectedRuleIds) ? 'checked' : ''; ?>>
                                <div>
                                    <div class="rule-pick-title"><?php echo htmlspecialchars($rule['titre'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="rule-pick-meta">
                                        <span style="background:rgba(37,99,235,0.1);color:var(--tech-blue);padding:0.1rem 0.5rem;border-radius:999px;font-size:0.72rem;font-weight:500;">
                                            <?php echo htmlspecialchars($rule['type'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <?php if (!empty($rule['valeur'])): ?>
                                            &nbsp;· Valeur : <?php echo htmlspecialchars($rule['valeur'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Footer : compteur + pagination -->
                <div class="rules-picker-footer">
                    <span id="rulesCount">
                        <span id="selectedCount">0</span> sélectionnée(s) · <span id="visibleCount"><?php echo count($availableRules); ?></span> règle(s)
                    </span>
                    <div class="pagination" id="rulesPagination" style="margin-top:0;"></div>
                </div>
            </div>
        </div>

        <!-- Section 3 : Signatures -->
        <div class="form-section animate-in delay-3">
            <div class="form-section-title"><i class="fa-solid fa-signature"></i> Signatures</div>
            <div class="form-grid">
                <!-- Signature Client -->
                <div class="form-group">
                    <label class="form-label">Signature Client <span class="required">*</span></label>
                    <div style="border:1px dashed var(--border);border-radius:var(--radius-md);padding:0.5rem;background:white;position:relative;">
                        <canvas id="sig-client" style="width:100%;height:140px;display:block;border-radius:6px;"></canvas>
                        <button type="button" onclick="padClient && padClient.clear()"
                                style="position:absolute;top:8px;right:8px;background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2);padding:0.2rem 0.5rem;border-radius:4px;font-size:0.72rem;cursor:pointer;">
                            <i class="fa-solid fa-eraser"></i> Effacer
                        </button>
                    </div>
                    <input type="hidden" id="signature_client" name="signature_client"
                           value="<?php echo htmlspecialchars($currentContrat['signature_client'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if (isset($errors['signature_client'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['signature_client'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Signature Freelancer -->
                <div class="form-group">
                    <label class="form-label">Signature Freelancer <span class="required">*</span></label>
                    <div style="border:1px dashed var(--border);border-radius:var(--radius-md);padding:0.5rem;background:white;position:relative;">
                        <canvas id="sig-freelance" style="width:100%;height:140px;display:block;border-radius:6px;"></canvas>
                        <button type="button" onclick="padFreelance && padFreelance.clear()"
                                style="position:absolute;top:8px;right:8px;background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2);padding:0.2rem 0.5rem;border-radius:4px;font-size:0.72rem;cursor:pointer;">
                            <i class="fa-solid fa-eraser"></i> Effacer
                        </button>
                    </div>
                    <input type="hidden" id="signature_freelance" name="signature_freelance"
                           value="<?php echo htmlspecialchars($currentContrat['signature_freelance'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if (isset($errors['signature_freelance'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['signature_freelance'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions animate-in delay-3">
            <a href="admin_contrat_list.php" class="btn btn-secondary" style="padding:0.9rem 2rem;">Annuler</a>
            <button type="submit" class="btn-submit">
                <?php echo $isEdit
                    ? '<i class="fa-solid fa-save"></i> Enregistrer les modifications'
                    : '<i class="fa-solid fa-paper-plane"></i> Créer le contrat'; ?>
            </button>
        </div>
    </form>

</main>

<script>
let padClient = null, padFreelance = null;

window.addEventListener('load', function () {
    const setup = (canvasId, hiddenId) => {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        const pad = new SignaturePad(canvas, { backgroundColor: 'white' });
        const existing = document.getElementById(hiddenId)?.value;
        if (existing && existing.startsWith('data:image')) pad.fromDataURL(existing);
        return pad;
    };
    padClient    = setup('sig-client',    'signature_client');
    padFreelance = setup('sig-freelance', 'signature_freelance');
    initRulesPagination();
    updateSelectedCount();
});

document.getElementById('contratForm').addEventListener('submit', function () {
    if (padClient   && !padClient.isEmpty())    document.getElementById('signature_client').value    = padClient.toDataURL();
    if (padFreelance && !padFreelance.isEmpty()) document.getElementById('signature_freelance').value = padFreelance.toDataURL();
});

// ── RULES PAGINATION ──
const RULES_PER_PAGE = 5;
let currentPage = 1;
let filteredItems = [];

function getAllRuleItems() {
    return Array.from(document.querySelectorAll('#rulesList .rule-pick-item'));
}

function filterRules() {
    const q = document.getElementById('ruleSearch').value.toLowerCase().trim();
    filteredItems = getAllRuleItems().filter(item => {
        return (item.dataset.title || '').includes(q) || (item.dataset.type || '').includes(q);
    });
    currentPage = 1;
    renderPage();
}

function renderPage() {
    getAllRuleItems().forEach(item => item.style.display = 'none');
    const start = (currentPage - 1) * RULES_PER_PAGE;
    filteredItems.slice(start, start + RULES_PER_PAGE).forEach(item => item.style.display = 'flex');
    document.getElementById('visibleCount').textContent = filteredItems.length;
    updateSelectedCount();
    renderPagination();
}

function renderPagination() {
    const total = Math.ceil(filteredItems.length / RULES_PER_PAGE);
    const container = document.getElementById('rulesPagination');
    if (!container) return;
    if (total <= 1) { container.innerHTML = ''; return; }

    let html = `<button type="button" class="pag-btn ${currentPage===1?'disabled':''}" onclick="goToPage(${currentPage-1})"><i class="fa-solid fa-chevron-left"></i></button>`;
    for (let i = 1; i <= total; i++) {
        if (total > 7 && i > 2 && i < total-1 && Math.abs(i - currentPage) > 1) {
            if (i === 3 || i === total-2) html += `<span class="pag-btn disabled">…</span>`;
            continue;
        }
        html += `<button type="button" class="pag-btn ${i===currentPage?'active':''}" onclick="goToPage(${i})">${i}</button>`;
    }
    html += `<button type="button" class="pag-btn ${currentPage===total?'disabled':''}" onclick="goToPage(${currentPage+1})"><i class="fa-solid fa-chevron-right"></i></button>`;
    container.innerHTML = html;
}

function goToPage(page) {
    const total = Math.ceil(filteredItems.length / RULES_PER_PAGE);
    if (page < 1 || page > total) return;
    currentPage = page;
    renderPage();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('#rulesList input[type="checkbox"]:checked').length;
    const el = document.getElementById('selectedCount');
    if (el) el.textContent = checked;
}

document.addEventListener('change', e => { if (e.target.closest('#rulesList')) updateSelectedCount(); });

function initRulesPagination() {
    filteredItems = getAllRuleItems();
    renderPage();
}

document.addEventListener('DOMContentLoaded', () => {
    setupContentCheck('contratForm', ['titre', 'description', 'freelance_info']);
});
</script>
</body>
</html>
