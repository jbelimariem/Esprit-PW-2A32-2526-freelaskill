/* ═══════════════════════════════════════════════════
   admin_messagerie.js  —  FreelaSkill Backoffice
   Module 5 : Messagerie / Collaboration
   Contrôles de saisie + modération admin
   ═══════════════════════════════════════════════════ */

'use strict';

/* ══════════════════════════════════════════════════
   DOM REFS
   ══════════════════════════════════════════════════ */
const adminSearch   = document.getElementById('adminSearch');
const adminClear    = document.getElementById('adminClear');
const statusFilter  = document.getElementById('statusFilter');
const adminError    = document.getElementById('adminError');
const adminErrorMsg = document.getElementById('adminErrorMsg');
const convRows      = document.querySelectorAll('#convTableBody tr');
const tableEmpty    = document.getElementById('tableEmpty');

const filesSearch   = document.getElementById('filesSearch');
const fileRows      = document.querySelectorAll('#filesTableBody tr');

const activityLog   = document.getElementById('activityLog');

/* Modal */
const modalOverlay  = document.getElementById('modalOverlay');
const modalIcon     = document.getElementById('modalIcon');
const modalTitle    = document.getElementById('modalTitle');
const modalDesc     = document.getElementById('modalDesc');
const modalCancel   = document.getElementById('modalCancel');
const modalConfirm  = document.getElementById('modalConfirm');

/* ══════════════════════════════════════════════════
   VALIDATION DE SAISIE (commune aux deux champs)
   ══════════════════════════════════════════════════ */
const RULES = {
    maxLength       : 100,
    forbiddenPattern: /<|>|javascript:/i,
    forbiddenMsg    : 'Caractères non autorisés.',
    tooLongMsg      : 'La recherche dépasse 100 caractères.',
};

/**
 * @param {string} value
 * @returns {{ valid:boolean, message:string }}
 */
function validateInput(value) {
    if (value.length > RULES.maxLength)
        return { valid:false, message:RULES.tooLongMsg };
    if (RULES.forbiddenPattern.test(value))
        return { valid:false, message:RULES.forbiddenMsg };
    return { valid:true, message:'' };
}

function showError(msg) {
    adminError.hidden = false;
    adminErrorMsg.textContent = msg;
    adminSearch.classList.add('input-error');
}
function clearError() {
    adminError.hidden = true;
    adminErrorMsg.textContent = '';
    adminSearch.classList.remove('input-error');
}

/* ══════════════════════════════════════════════════
   FILTRE TABLE CONVERSATIONS
   ══════════════════════════════════════════════════ */
let currentStatus = 'all';
let currentQuery  = '';

function filterConvTable() {
    const query = currentQuery.toLowerCase().trim();
    let visible = 0;

    convRows.forEach(row => {
        const status = row.dataset.status || '';
        const search = (row.dataset.search || '').toLowerCase();

        const matchStatus = currentStatus === 'all' || status === currentStatus;
        const matchSearch = !query || search.includes(query);

        row.style.display = (matchStatus && matchSearch) ? '' : 'none';
        if (matchStatus && matchSearch) visible++;
    });

    tableEmpty.hidden = visible > 0;
}

/* ── Search field ── */
adminSearch.addEventListener('input', () => {
    const val = adminSearch.value;
    adminClear.classList.toggle('visible', val.length > 0);

    const { valid, message } = validateInput(val);
    if (!valid) { showError(message); return; }
    clearError();

    currentQuery = val;
    filterConvTable();
});

adminSearch.addEventListener('paste', (e) => {
    const pasted = (e.clipboardData || window.clipboardData).getData('text');
    const { valid, message } = validateInput(pasted);
    if (!valid) { e.preventDefault(); showError(message); }
});

adminSearch.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        adminSearch.value = '';
        currentQuery = '';
        adminClear.classList.remove('visible');
        clearError();
        filterConvTable();
    }
});

adminClear.addEventListener('click', () => {
    adminSearch.value = '';
    currentQuery = '';
    adminClear.classList.remove('visible');
    clearError();
    filterConvTable();
    adminSearch.focus();
});

/* ── Status filter ── */
statusFilter.addEventListener('change', () => {
    currentStatus = statusFilter.value;
    filterConvTable();
});

/* ══════════════════════════════════════════════════
   FILTRE TABLE FICHIERS
   ══════════════════════════════════════════════════ */
filesSearch.addEventListener('input', () => {
    const { valid } = validateInput(filesSearch.value);
    if (!valid) return;

    const q = filesSearch.value.toLowerCase().trim();
    fileRows.forEach(row => {
        const s = (row.dataset.fsearch || '').toLowerCase();
        row.style.display = (!q || s.includes(q)) ? '' : 'none';
    });
});

filesSearch.addEventListener('paste', (e) => {
    const pasted = (e.clipboardData || window.clipboardData).getData('text');
    if (RULES.forbiddenPattern.test(pasted)) { e.preventDefault(); }
});

/* ══════════════════════════════════════════════════
   MODAL UTILITAIRE
   ══════════════════════════════════════════════════ */
let _onConfirm = null;

/**
 * Ouvre la modale de confirmation.
 * @param {object} opts - { icon, title, desc, confirmLabel, confirmClass, onConfirm }
 */
function openModal({ icon, title, desc, confirmLabel = 'Confirmer', confirmClass = '', onConfirm }) {
    modalIcon.innerHTML    = icon;
    modalTitle.textContent = title;
    modalDesc.textContent  = desc;
    modalConfirm.textContent = confirmLabel;
    modalConfirm.className = 'modal-btn modal-confirm ' + confirmClass;
    _onConfirm = onConfirm;
    modalOverlay.hidden = false;
}

function closeModal() {
    modalOverlay.hidden = true;
    _onConfirm = null;
}

modalCancel.addEventListener('click', closeModal);
modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay) closeModal();
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});
modalConfirm.addEventListener('click', () => {
    if (typeof _onConfirm === 'function') _onConfirm();
    closeModal();
});

/* ══════════════════════════════════════════════════
   JOURNAL D'ACTIVITÉ
   ══════════════════════════════════════════════════ */
/**
 * Ajoute une entrée au journal.
 * @param {'Supprimé'|'Archivé'|'Approuvé'|'Signalé'|'Banni'} type
 * @param {string} desc
 */
function addLog(type, desc) {
    const classMap = {
        'Supprimé': 'badge-delete',
        'Archivé' : 'badge-archive',
        'Approuvé': 'badge-ok',
        'Signalé' : 'badge-flag',
        'Banni'   : 'badge-delete',
    };
    const now  = new Date();
    const time = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
    const cls  = classMap[type] || 'badge-ok';

    const entry = document.createElement('div');
    entry.className = 'log-entry';
    entry.innerHTML = `
        <span class="log-time">${time}</span>
        <span class="log-badge ${cls}">${type}</span>
        <span class="log-desc">${desc}</span>`;

    activityLog.insertBefore(entry, activityLog.firstChild);
}

/* ══════════════════════════════════════════════════
   ACTIONS — TABLE CONVERSATIONS
   ══════════════════════════════════════════════════ */
document.getElementById('convTableBody').addEventListener('click', (e) => {
    const btn = e.target.closest('.action-btn');
    if (!btn) return;

    const id  = btn.dataset.id;
    const row = btn.closest('tr');

    /* ── Voir ── */
    if (btn.classList.contains('btn-view')) {
        openModal({
            icon         : '<i class="fa-solid fa-eye" style="color:var(--tech-blue);"></i>',
            title        : `Détails Conv. #${id}`,
            desc         : 'Fonctionnalité : ouvre la conversation en lecture seule dans un onglet admin dédié.',
            confirmLabel : 'Ouvrir',
            confirmClass : 'blue',
            onConfirm    : () => { /* navigation vers vue détail */ }
        });
        return;
    }

    /* ── Archiver ── */
    if (btn.classList.contains('btn-archive')) {
        openModal({
            icon         : '<i class="fa-solid fa-box-archive" style="color:#94A3B8;font-size:2.2rem;"></i>',
            title        : 'Archiver la conversation ?',
            desc         : `La conversation #${id} sera archivée. Elle restera consultable en lecture seule.`,
            confirmLabel : 'Archiver',
            confirmClass : '',
            onConfirm    : () => {
                const badge = row.querySelector('.status-badge');
                if (badge) {
                    badge.className = 'status-badge badge-archived';
                    badge.innerHTML = '<i class="fa-solid fa-box-archive"></i> Archivée';
                }
                row.dataset.status = 'archived';
                addLog('Archivé', `Conv. #${id} archivée par Admin`);
                filterConvTable();
            }
        });
        return;
    }

    /* ── Supprimer ── */
    if (btn.classList.contains('btn-delete')) {
        openModal({
            icon         : '<i class="fa-solid fa-trash" style="color:var(--tunisian-red);font-size:2.2rem;"></i>',
            title        : 'Supprimer définitivement ?',
            desc         : `La conversation #${id} et tous ses messages seront supprimés. Cette action est irréversible.`,
            confirmLabel : 'Supprimer',
            confirmClass : '',
            onConfirm    : () => {
                row.style.transition = 'opacity .3s';
                row.style.opacity    = '0';
                setTimeout(() => {
                    row.remove();
                    addLog('Supprimé', `Conv. #${id} supprimée par Admin`);
                    filterConvTable();
                }, 320);
            }
        });
    }
});

/* ══════════════════════════════════════════════════
   ACTIONS — MESSAGES SIGNALÉS
   ══════════════════════════════════════════════════ */
document.getElementById('flaggedList').addEventListener('click', (e) => {
    const btn  = e.target.closest('.action-btn');
    if (!btn)  return;
    const mid  = btn.dataset.mid;
    const card = btn.closest('.flagged-card');

    /* Ignorer signal */
    if (btn.classList.contains('btn-approve')) {
        openModal({
            icon         : '<i class="fa-solid fa-check-circle" style="color:var(--tech-green);font-size:2.2rem;"></i>',
            title        : 'Ignorer le signalement ?',
            desc         : `Le message #${mid} sera marqué comme valide. Le signal sera supprimé.`,
            confirmLabel : 'Ignorer signal',
            confirmClass : 'green',
            onConfirm    : () => {
                fadeRemove(card);
                addLog('Approuvé', `Signal sur message #${mid} ignoré par Admin`);
            }
        });
        return;
    }

    /* Supprimer message */
    if (btn.classList.contains('btn-delete')) {
        openModal({
            icon         : '<i class="fa-solid fa-trash" style="color:var(--tunisian-red);font-size:2.2rem;"></i>',
            title        : 'Supprimer ce message ?',
            desc         : `Le message #${mid} sera définitivement supprimé de la conversation.`,
            confirmLabel : 'Supprimer',
            confirmClass : '',
            onConfirm    : () => {
                fadeRemove(card);
                addLog('Supprimé', `Message #${mid} supprimé par Admin`);
            }
        });
        return;
    }

    /* Bannir expéditeur */
    if (btn.classList.contains('btn-ban')) {
        openModal({
            icon         : '<i class="fa-solid fa-ban" style="color:#F59E0B;font-size:2.2rem;"></i>',
            title        : 'Bannir l\'expéditeur ?',
            desc         : `L'expéditeur du message #${mid} sera suspendu de la plateforme. Cette action est grave.`,
            confirmLabel : 'Bannir',
            confirmClass : '',
            onConfirm    : () => {
                fadeRemove(card);
                addLog('Banni', `Expéditeur du message #${mid} banni par Admin`);
            }
        });
    }
});

/* ══════════════════════════════════════════════════
   ACTIONS — FICHIERS
   ══════════════════════════════════════════════════ */
document.getElementById('filesTableBody').addEventListener('click', (e) => {
    const btn = e.target.closest('.action-btn');
    if (!btn) return;
    const fid = btn.dataset.fid;
    const row = btn.closest('tr');

    if (btn.classList.contains('btn-approve')) {
        openModal({
            icon         : '<i class="fa-solid fa-file-shield" style="color:var(--tech-green);font-size:2.2rem;"></i>',
            title        : 'Approuver ce fichier ?',
            desc         : `Le fichier #${fid} sera marqué comme sûr et accessible aux participants.`,
            confirmLabel : 'Approuver',
            confirmClass : 'green',
            onConfirm    : () => {
                fadeRemove(row);
                addLog('Approuvé', `Fichier #${fid} approuvé par Admin`);
            }
        });
        return;
    }

    if (btn.classList.contains('btn-delete')) {
        openModal({
            icon         : '<i class="fa-solid fa-trash" style="color:var(--tunisian-red);font-size:2.2rem;"></i>',
            title        : 'Supprimer ce fichier ?',
            desc         : `Le fichier #${fid} sera définitivement supprimé et inaccessible.`,
            confirmLabel : 'Supprimer',
            confirmClass : '',
            onConfirm    : () => {
                fadeRemove(row);
                addLog('Supprimé', `Fichier #${fid} supprimé par Admin`);
            }
        });
    }
});

/* ══════════════════════════════════════════════════
   UTILITAIRES
   ══════════════════════════════════════════════════ */
function fadeRemove(el) {
    if (!el) return;
    el.style.transition = 'opacity .3s, max-height .35s';
    el.style.opacity    = '0';
    setTimeout(() => el.remove(), 380);
}

/* ── Init ── */
filterConvTable();
