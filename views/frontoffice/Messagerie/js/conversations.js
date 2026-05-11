/* ═══════════════════════════════════════════════════
   conversations.js  —  FreelaSkill Frontoffice
   Module 5 : Messagerie / Collaboration
   Contrôles de saisie + filtre + recherche
   ═══════════════════════════════════════════════════ */

'use strict';

/* ── DOM refs ── */
const searchInput  = document.getElementById('searchInput');
const clearBtn     = document.getElementById('clearSearch');
const filterTabs   = document.querySelectorAll('.ftab');
const convList     = document.getElementById('convList');
const convCards    = document.querySelectorAll('.conv-card');
const emptyState   = document.getElementById('emptyState');
const errorBanner  = document.getElementById('errorBanner');
const errorMsg     = document.getElementById('errorMsg');
const unreadCount  = document.getElementById('unreadCount');

/* ── State ── */
let currentFilter = 'all';
let currentQuery  = '';

/* ══════════════════════════════════════════════════
   VALIDATION DE SAISIE
   ══════════════════════════════════════════════════ */

/** Règles de validation pour le champ de recherche */
const VALIDATION = {
    maxLength : 100,
    minLength : 0,          // vide autorisé (réinitialise la recherche)
    /** Caractères interdits : balises HTML / scripts */
    forbiddenPattern: /<|>|script|javascript/i,
    /** Message d'erreur associé */
    forbiddenMsg: 'Caractères non autorisés dans la recherche.'
};

/**
 * Valide la valeur saisie dans le champ de recherche.
 * @param {string} value
 * @returns {{ valid: boolean, message: string }}
 */
function validateSearch(value) {
    if (value.length > VALIDATION.maxLength) {
        return { valid: false, message: `La recherche ne peut pas dépasser ${VALIDATION.maxLength} caractères.` };
    }
    if (VALIDATION.forbiddenPattern.test(value)) {
        return { valid: false, message: VALIDATION.forbiddenMsg };
    }
    return { valid: true, message: '' };
}

/** Affiche ou masque la bannière d'erreur */
function showError(message) {
    errorBanner.hidden = false;
    errorMsg.textContent = message;
    searchInput.classList.add('input-error');
    searchInput.setAttribute('aria-invalid', 'true');
}

function clearError() {
    errorBanner.hidden = true;
    errorMsg.textContent = '';
    searchInput.classList.remove('input-error');
    searchInput.removeAttribute('aria-invalid');
}

/* ══════════════════════════════════════════════════
   FILTRE + RECHERCHE
   ══════════════════════════════════════════════════ */

/**
 * Applique le filtre actif ET la recherche textuelle aux cartes.
 * Affiche l'état vide si aucun résultat.
 */
function applyFilters() {
    const query = currentQuery.toLowerCase().trim();
    let visibleCount = 0;

    convCards.forEach(card => {
        const cardFilters = card.dataset.filter || '';
        const name        = (card.dataset.name    || '').toLowerCase();
        const preview     = (card.dataset.preview || '').toLowerCase();

        /* ── Filtre par onglet ── */
        let matchFilter = false;
        if (currentFilter === 'all') {
            matchFilter = true;
        } else if (currentFilter === 'unread') {
            matchFilter = card.classList.contains('unread');
        } else {
            matchFilter = cardFilters.split(' ').includes(currentFilter);
        }

        /* ── Filtre par recherche textuelle ── */
        const matchSearch = !query || name.includes(query) || preview.includes(query);

        const visible = matchFilter && matchSearch;
        card.style.display = visible ? 'flex' : 'none';
        if (visible) visibleCount++;
    });

    emptyState.hidden = visibleCount > 0;
}

/* ══════════════════════════════════════════════════
   ÉVÉNEMENTS
   ══════════════════════════════════════════════════ */

/* Recherche en temps réel */
searchInput.addEventListener('input', () => {
    const value = searchInput.value;

    /* Bouton effacer */
    clearBtn.classList.toggle('visible', value.length > 0);

    /* Validation */
    const { valid, message } = validateSearch(value);
    if (!valid) {
        showError(message);
        /* On n'applique pas le filtre si saisie invalide */
        return;
    }
    clearError();

    currentQuery = value;
    applyFilters();
});

/* Effacer la recherche */
clearBtn.addEventListener('click', () => {
    searchInput.value = '';
    currentQuery      = '';
    clearBtn.classList.remove('visible');
    clearError();
    applyFilters();
    searchInput.focus();
});

/* Blocage collé de contenu potentiellement dangereux */
searchInput.addEventListener('paste', (e) => {
    const pasted = (e.clipboardData || window.clipboardData).getData('text');
    const { valid, message } = validateSearch(pasted);
    if (!valid) {
        e.preventDefault();
        showError(message);
    }
});

/* Touche Échap pour réinitialiser */
searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        searchInput.value = '';
        currentQuery      = '';
        clearBtn.classList.remove('visible');
        clearError();
        applyFilters();
    }
});

/* Filtre par onglets */
filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        filterTabs.forEach(t => {
            t.classList.remove('active');
            t.setAttribute('aria-selected', 'false');
        });
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');
        currentFilter = tab.dataset.filter;
        applyFilters();
    });
});

/* ══════════════════════════════════════════════════
   COMPTEUR NON-LUS (dynamique)
   ══════════════════════════════════════════════════ */
function refreshUnreadCount() {
    const count = document.querySelectorAll('.conv-card.unread').length;
    if (unreadCount) unreadCount.textContent = count;
}

/* ── Init ── */
refreshUnreadCount();
applyFilters();
