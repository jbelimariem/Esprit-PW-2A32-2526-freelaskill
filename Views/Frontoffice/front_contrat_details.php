<?php
session_start();
if (!isset($_SESSION['user_role'])) { header('Location: front_rules_role.php'); exit; }
require_once __DIR__ . '/../../controllers/contratController.php';
require_once __DIR__ . '/../../controllers/ruleController.php';
$role = $_SESSION['user_role'];
$isClient = ($role === 'client');
$roleName = $isClient ? 'Client' : 'Freelancer';
$activePage = 'contrats';

if (!isset($_GET['id'])) { header('Location: front_contrat_list.php'); exit; }
$id = intval($_GET['id']);
$contrat = getContratById($id);
if (!$contrat) die("Contrat introuvable.");
$rules = getRulesByContratId($id);

$successMessage = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signature_data'])) {
    $sig = $_POST['signature_data'];
    if (strpos($sig, 'data:image/') === 0) {
        if (updateSignature($id, $role, $sig)) {
            $successMessage = "Signature enregistrée avec succès.";
            $contrat = getContratById($id);
        } else { $errors[] = "Erreur lors de l'enregistrement de la signature."; }
    } else { $errors[] = "Format de signature invalide."; }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Contrat · FreelaSkill</title>
    <link rel="stylesheet" href="css/front.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        window.API_BASE = '/Esprit-PW-2A32-2526-TalentBridge-job/controllers/apiController.php';
    </script>
    <script src="css/front.js" defer></script>
    <script src="../assets/api.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<main class="admin-main">

    <div class="admin-topbar animate-in">
        <div>
            <div class="admin-breadcrumb">
                <i class="fa-solid fa-house"></i>
                <span class="sep">/</span>
                <a href="front_contrat_index.php">Contrats</a>
                <span class="sep">/</span>
                <a href="front_contrat_list.php">Liste</a>
                <span class="sep">/</span>
                <span class="current">Détails</span>
            </div>
            <h1 class="admin-page-title">Détails du <span>Contrat</span></h1>
        </div>
        <div class="topbar-actions">
            <a href="front_contrat_list.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>

            <!-- Traduction du contrat -->
            <div style="display:inline-flex;align-items:center;gap:0.3rem;background:var(--bg-card);border:1px solid var(--border);border-radius:999px;padding:0.3rem 0.5rem;">
                <i class="fa-solid fa-language" style="color:#10B981;font-size:0.85rem;margin-left:0.3rem;"></i>
                <select id="translate-from" style="padding:0.3rem 0.5rem;background:transparent;border:none;color:var(--text-muted);font-size:0.78rem;font-family:inherit;cursor:pointer;outline:none;">
                    <option value="fr">FR</option><option value="en">EN</option><option value="ar">AR</option>
                </select>
                <span style="color:var(--text-muted);font-size:0.72rem;">→</span>
                <select id="translate-to" style="padding:0.3rem 0.5rem;background:transparent;border:none;color:var(--text-muted);font-size:0.78rem;font-family:inherit;cursor:pointer;outline:none;">
                    <option value="en">EN</option><option value="fr">FR</option><option value="ar">AR</option>
                </select>
                <button type="button" id="btn-translate-details" onclick="translateDetails(this)"
                        style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.35rem 0.85rem;background:rgba(16,185,129,0.15);color:#10B981;border:1px solid rgba(16,185,129,0.3);border-radius:999px;font-size:0.8rem;font-weight:600;cursor:pointer;font-family:inherit;">
                    Traduire
                </button>
                <span id="lang-badge" style="display:none;background:rgba(16,185,129,0.2);color:#10B981;padding:0.2rem 0.5rem;border-radius:999px;font-size:0.72rem;font-weight:700;"></span>
            </div>

            <a href="../Backoffice/admin_export_pdf.php?id=<?php echo $id; ?>" class="btn btn-purple" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> Exporter PDF
            </a>
            <div class="admin-badge">
                <i class="fa-solid <?php echo $isClient ? 'fa-user-tie' : 'fa-laptop-code'; ?>"></i>
                <?php echo htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error animate-in">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div><?php foreach ($errors as $e) echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '<br>'; ?></div>
        </div>
    <?php endif; ?>
    <?php if ($successMessage): ?>
        <div class="alert alert-success animate-in">
            <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <!-- Infos générales -->
    <div class="admin-card animate-in delay-1">
        <div class="admin-card-header">
            <div class="admin-card-title"><i class="fa-solid fa-circle-info"></i> Informations générales</div>
            <span class="badge badge-<?php echo htmlspecialchars($contrat['statut'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo ucfirst(str_replace('_', ' ', $contrat['statut'])); ?>
            </span>
        </div>
        <div style="padding:1.5rem;">
            <h2 style="font-size:1.3rem;font-weight:700;color:var(--text-light);margin-bottom:0.75rem;" id="detail-titre"><?php echo htmlspecialchars($contrat['titre'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p style="color:var(--text-muted);line-height:1.7;margin-bottom:1.5rem;" id="detail-description"><?php echo nl2br(htmlspecialchars($contrat['description'], ENT_QUOTES, 'UTF-8')); ?></p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;padding-top:1.25rem;border-top:1px solid var(--border);">
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.3rem;font-weight:600;">Budget</div>
                    <div style="font-size:1.1rem;font-weight:700;color:var(--tech-blue);"><?php echo htmlspecialchars($contrat['budget'], ENT_QUOTES, 'UTF-8'); ?> DT</div>
                </div>
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.3rem;font-weight:600;">Délai</div>
                    <div style="font-size:1.1rem;font-weight:700;color:var(--text-light);"><?php echo htmlspecialchars($contrat['delai'], ENT_QUOTES, 'UTF-8'); ?> jours</div>
                </div>
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.3rem;font-weight:600;">Freelancer</div>
                    <div style="font-size:0.9rem;font-weight:600;color:var(--text-light);"><?php echo htmlspecialchars($contrat['freelance_info'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.3rem;font-weight:600;">Créé le</div>
                    <div style="font-size:0.9rem;color:var(--text-light);"><?php echo date('d/m/Y', strtotime($contrat['date_creation'])); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Règles associées -->
    <?php if (!empty($rules)): ?>
    <div class="admin-card animate-in delay-2">
        <div class="admin-card-header">
            <div class="admin-card-title"><i class="fa-solid fa-gavel"></i> Règles associées</div>
            <span style="font-size:0.8rem;color:var(--text-muted);"><?php echo count($rules); ?> règle<?php echo count($rules) > 1 ? 's' : ''; ?></span>
        </div>
        <div style="padding:0 1.5rem;">
            <?php foreach ($rules as $r): ?>
                <div style="padding:1rem 0;border-bottom:1px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.4rem;flex-wrap:wrap;">
                        <span style="font-weight:600;color:var(--text-light);"><?php echo htmlspecialchars($r['titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span style="background:rgba(37,99,235,0.1);color:var(--tech-blue);padding:0.15rem 0.6rem;border-radius:999px;font-size:0.75rem;font-weight:500;"><?php echo htmlspecialchars($r['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if (!empty($r['valeur'])): ?>
                            <span style="color:var(--text-muted);font-size:0.82rem;">Valeur : <?php echo htmlspecialchars($r['valeur'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($r['description'])): ?>
                        <p class="rule-desc-text" style="color:var(--text-muted);font-size:0.88rem;line-height:1.6;"><?php echo htmlspecialchars($r['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Signatures -->
    <div class="admin-card animate-in delay-3">
        <div class="admin-card-header">
            <div class="admin-card-title"><i class="fa-solid fa-signature"></i> Signatures</div>
        </div>
        <div style="padding:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:2rem;">

            <div>
                <div style="font-size:0.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.75rem;">Signature Client</div>
                <?php if (!empty($contrat['signature_client'])): ?>
                    <div style="background:white;border-radius:10px;padding:0.5rem;overflow:hidden;">
                        <img src="<?php echo htmlspecialchars($contrat['signature_client']); ?>" style="max-width:100%;max-height:120px;display:block;margin:0 auto;">
                    </div>
                <?php elseif ($isClient): ?>
                    <form method="POST" id="signatureFormClient">
                        <div style="border:1px dashed var(--border);border-radius:10px;padding:0.5rem;background:white;position:relative;">
                            <canvas id="signature-pad-client" style="width:100%;height:130px;display:block;border-radius:6px;"></canvas>
                            <button type="button" onclick="signaturePadClient && signaturePadClient.clear()"
                                    style="position:absolute;top:8px;right:8px;background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2);padding:0.2rem 0.5rem;border-radius:4px;font-size:0.72rem;cursor:pointer;">
                                <i class="fa-solid fa-eraser"></i>
                            </button>
                        </div>
                        <input type="hidden" name="signature_data" id="signature_data_client">
                        <button type="button" onclick="saveSignature(signaturePadClient,'signature_data_client','signatureFormClient')"
                                class="btn btn-primary" style="width:100%;margin-top:0.75rem;">
                            <i class="fa-solid fa-pen-nib"></i> Signer en tant que Client
                        </button>
                    </form>
                <?php else: ?>
                    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:10px;height:100px;display:flex;align-items:center;justify-content:center;">
                        <span style="color:var(--text-muted);font-style:italic;font-size:0.88rem;">En attente du client</span>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <div style="font-size:0.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.75rem;">Signature Freelancer</div>
                <?php if (!empty($contrat['signature_freelance'])): ?>
                    <div style="background:white;border-radius:10px;padding:0.5rem;overflow:hidden;">
                        <img src="<?php echo htmlspecialchars($contrat['signature_freelance']); ?>" style="max-width:100%;max-height:120px;display:block;margin:0 auto;">
                    </div>
                <?php elseif (!$isClient): ?>
                    <form method="POST" id="signatureFormFreelance">
                        <div style="border:1px dashed var(--border);border-radius:10px;padding:0.5rem;background:white;position:relative;">
                            <canvas id="signature-pad-freelance" style="width:100%;height:130px;display:block;border-radius:6px;"></canvas>
                            <button type="button" onclick="signaturePadFreelance && signaturePadFreelance.clear()"
                                    style="position:absolute;top:8px;right:8px;background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2);padding:0.2rem 0.5rem;border-radius:4px;font-size:0.72rem;cursor:pointer;">
                                <i class="fa-solid fa-eraser"></i>
                            </button>
                        </div>
                        <input type="hidden" name="signature_data" id="signature_data_freelance">
                        <button type="button" onclick="saveSignature(signaturePadFreelance,'signature_data_freelance','signatureFormFreelance')"
                                class="btn btn-primary" style="width:100%;margin-top:0.75rem;">
                            <i class="fa-solid fa-pen-nib"></i> Signer en tant que Freelancer
                        </button>
                    </form>
                <?php else: ?>
                    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:10px;height:100px;display:flex;align-items:center;justify-content:center;">
                        <span style="color:var(--text-muted);font-style:italic;font-size:0.88rem;">En attente du freelancer</span>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</main>

<script>
let signaturePadClient = null, signaturePadFreelance = null;
window.addEventListener('load', function () {
    const setup = (id) => {
        const c = document.getElementById(id);
        if (!c) return null;
        const r = Math.max(window.devicePixelRatio || 1, 1);
        c.width = c.offsetWidth * r; c.height = c.offsetHeight * r;
        c.getContext('2d').scale(r, r);
        return new SignaturePad(c, { backgroundColor: 'rgba(255,255,255,1)', penColor: 'rgb(0,0,0)' });
    };
    signaturePadClient    = setup('signature-pad-client');
    signaturePadFreelance = setup('signature-pad-freelance');
});
function saveSignature(pad, inputId, formId) {
    if (!pad || pad.isEmpty()) { alert('Veuillez dessiner votre signature.'); return; }
    document.getElementById(inputId).value = pad.toDataURL();
    document.getElementById(formId).submit();
}

// ── Traduction de la page de détails ──────────────────────────────────
async function translateDetails(btn) {
    const from = document.getElementById('translate-from')?.value || 'fr';
    const to   = document.getElementById('translate-to')?.value   || 'en';

    if (from === to) { showApiToast('Choisissez deux langues différentes.', 'error'); return; }

    showApiLoader(btn, 'Traduction...');

    // Supprimer l'ancien panneau de traduction s'il existe
    const oldPanel = document.getElementById('translation-panel');
    if (oldPanel) oldPanel.remove();

    const langNames = { fr: 'Français', en: 'English', ar: 'العربية' };

    // Collecter tous les éléments à traduire
    const targets = [
        { el: document.getElementById('detail-titre'),       label: 'Titre' },
        { el: document.getElementById('detail-description'), label: 'Description' },
        ...Array.from(document.querySelectorAll('.rule-desc-text')).map((el, i) => ({
            el, label: `Règle ${i + 1}`
        })),
    ].filter(t => t.el && t.el.innerText.trim().length > 2);

    if (targets.length === 0) {
        hideApiLoader(btn);
        showApiToast('Aucun texte à traduire.', 'error');
        return;
    }

    const results = [];

    for (const target of targets) {
        const text = target.el.innerText.trim();
        const result = await apiPost('translate', { text, from, to });
        if (result.success && result.translated) {
            results.push({ label: target.label, original: text, translated: result.translated });
        }
    }

    hideApiLoader(btn);

    if (results.length === 0) {
        showApiToast('Traduction échouée. Vérifiez la langue source.', 'error');
        return;
    }

    // Créer un panneau de traduction visible sous les infos générales
    const panel = document.createElement('div');
    panel.id = 'translation-panel';
    panel.style.cssText = `
        background: rgba(37,99,235,0.06);
        border: 1px solid rgba(37,99,235,0.25);
        border-radius: 16px;
        padding: 1.5rem;
        margin-top: 1.25rem;
        animation: fadeIn 0.4s ease;
    `;

    const langFlag = { fr: '🇫🇷', en: '🇬🇧', ar: '🇹🇳' };

    panel.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid rgba(37,99,235,0.2);">
            <div style="font-size:0.82rem;font-weight:700;color:#60A5FA;text-transform:uppercase;letter-spacing:0.8px;">
                <i class="fa-solid fa-language"></i>
                Traduction ${langFlag[from] || ''} ${langNames[from] || from} → ${langFlag[to] || ''} ${langNames[to] || to}
            </div>
            <button type="button" onclick="document.getElementById('translation-panel').remove()"
                    style="background:transparent;border:none;color:#475569;cursor:pointer;font-size:1rem;padding:0.2rem 0.5rem;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        ${results.map(r => `
            <div style="margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid rgba(255,255,255,0.05);">
                <div style="font-size:0.72rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.5rem;">
                    ${r.label}
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div style="background:rgba(255,255,255,0.02);border-radius:8px;padding:0.75rem;font-size:0.88rem;color:#64748B;line-height:1.6;">
                        <div style="font-size:0.68rem;color:#475569;margin-bottom:0.3rem;font-weight:600;">${langNames[from] || from}</div>
                        ${r.original.substring(0, 300)}${r.original.length > 300 ? '...' : ''}
                    </div>
                    <div style="background:rgba(37,99,235,0.06);border:1px solid rgba(37,99,235,0.15);border-radius:8px;padding:0.75rem;font-size:0.88rem;color:#CBD5E1;line-height:1.6;">
                        <div style="font-size:0.68rem;color:#60A5FA;margin-bottom:0.3rem;font-weight:600;">${langNames[to] || to}</div>
                        ${r.translated}
                    </div>
                </div>
            </div>
        `).join('')}
        <div style="display:flex;gap:0.75rem;margin-top:0.5rem;">
            <button type="button" onclick="applyAllTranslations(${JSON.stringify(results).replace(/"/g, '&quot;')})"
                    style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1.1rem;background:rgba(37,99,235,0.15);color:#60A5FA;border:1px solid rgba(37,99,235,0.3);border-radius:999px;font-size:0.82rem;font-weight:600;cursor:pointer;font-family:inherit;">
                <i class="fa-solid fa-check"></i> Appliquer sur la page
            </button>
            <button type="button" onclick="document.getElementById('translation-panel').remove()"
                    style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1.1rem;background:transparent;color:#475569;border:1px solid rgba(255,255,255,0.1);border-radius:999px;font-size:0.82rem;cursor:pointer;font-family:inherit;">
                Fermer
            </button>
        </div>
    `;

    // Insérer après la première card (infos générales)
    const firstCard = document.querySelector('.admin-card');
    if (firstCard) {
        firstCard.parentNode.insertBefore(panel, firstCard.nextSibling);
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        document.querySelector('main')?.appendChild(panel);
    }

    showApiToast(`${results.length} élément(s) traduit(s) en ${to.toUpperCase()} ✓`, 'success');

    // Mettre à jour le badge de langue
    const badge = document.getElementById('lang-badge');
    if (badge) { badge.textContent = to.toUpperCase(); badge.style.display = 'inline-block'; }
}

function applyAllTranslations(results) {
    const targets = [
        document.getElementById('detail-titre'),
        document.getElementById('detail-description'),
        ...Array.from(document.querySelectorAll('.rule-desc-text')),
    ].filter(Boolean);

    results.forEach((r, i) => {
        if (targets[i]) {
            targets[i].innerText = r.translated;
            targets[i].style.borderLeft = '3px solid rgba(37,99,235,0.5)';
            targets[i].style.paddingLeft = '0.5rem';
        }
    });

    document.getElementById('translation-panel')?.remove();
    showApiToast('Traduction appliquée sur la page ✓', 'success');
}
</script>
</body>
</html>
