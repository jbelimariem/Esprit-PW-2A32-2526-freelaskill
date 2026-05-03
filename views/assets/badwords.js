/**
 * views/assets/badwords.js
 * Réutilisable — Détecteur de contenu inapproprié via Groq.
 *
 * Usage :
 *   BadWordsGuard.watch('bio', 'badwords_api.php', { delay: 900 });
 *
 * Le widget affiche un badge sous le textarea avec le statut de modération
 * en temps réel (debounced). Il bloque aussi la soumission du formulaire
 * parent si du contenu inapproprié est détecté.
 */

const BadWordsGuard = (() => {

    const SEVERITY_CONFIG = {
        checking : { color: '#7c3aed', bg: 'rgba(139,92,246,0.1)', border: 'rgba(139,92,246,0.3)', icon: 'fa-spinner fa-spin', label: 'Analyse en cours…' },
        clean    : { color: '#10b981', bg: 'rgba(16,185,129,0.1)',  border: 'rgba(16,185,129,0.3)',  icon: 'fa-circle-check',  label: 'Contenu approprié ✓' },
        low      : { color: '#f59e0b', bg: 'rgba(245,158,11,0.1)', border: 'rgba(245,158,11,0.35)', icon: 'fa-triangle-exclamation', label: 'Langage déconseillé' },
        medium   : { color: '#f97316', bg: 'rgba(249,115,22,0.1)', border: 'rgba(249,115,22,0.35)', icon: 'fa-circle-exclamation', label: 'Contenu inapproprié' },
        high     : { color: '#ef4444', bg: 'rgba(239,68,68,0.1)',  border: 'rgba(239,68,68,0.35)', icon: 'fa-ban', label: 'Contenu interdit' },
    };

    // Track active guards: fieldId → { badge, timer, dirty, blocked }
    const _guards = {};

    function _createBadge(fieldId) {
        const existing = document.getElementById('bwg-badge-' + fieldId);
        if (existing) return existing;

        const badge = document.createElement('div');
        badge.id = 'bwg-badge-' + fieldId;
        badge.style.cssText = [
            'display:none',
            'align-items:center',
            'gap:0.45rem',
            'margin-top:0.5rem',
            'padding:0.42rem 0.85rem',
            'border-radius:999px',
            'font-size:0.78rem',
            'font-weight:600',
            'border:1px solid transparent',
            'transition:all 0.3s ease',
            'width:fit-content',
        ].join(';');
        return badge;
    }

    function _showBadge(badge, status, reason) {
        const cfg = SEVERITY_CONFIG[status] || SEVERITY_CONFIG.clean;
        badge.style.display      = 'flex';
        badge.style.color        = cfg.color;
        badge.style.background   = cfg.bg;
        badge.style.borderColor  = cfg.border;
        const labelText = reason ? cfg.label + ' — ' + reason : cfg.label;
        badge.innerHTML = `<i class="fa-solid ${cfg.icon}" style="font-size:0.72rem;"></i><span>${labelText}</span>`;
    }

    function _hideBadge(badge) {
        badge.style.display = 'none';
    }

    function _setBlocked(guard, blocked) {
        guard.blocked = blocked;
        // Find the submit button(s) of the parent form
        const field = guard.field;
        if (!field) return;
        const form = field.closest('form');
        if (!form) return;
        form.querySelectorAll('[type="submit"]').forEach(btn => {
            if (blocked) {
                btn.dataset.bwgDisabled = '1';
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.title = 'Veuillez corriger le contenu avant de soumettre.';
            } else {
                if (btn.dataset.bwgDisabled) {
                    btn.disabled = false;
                    btn.style.opacity = '';
                    btn.title = '';
                    delete btn.dataset.bwgDisabled;
                }
            }
        });
    }

    async function _analyze(fieldId, apiUrl) {
        const guard = _guards[fieldId];
        if (!guard) return;

        const text = guard.field.value.trim();

        if (text.length < 4) {
            _hideBadge(guard.badge);
            _setBlocked(guard, false);
            return;
        }

        _showBadge(guard.badge, 'checking');
        _setBlocked(guard, false);

        try {
            const res  = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text, field: fieldId }),
            });
            const data = await res.json();

            if (data.clean) {
                _showBadge(guard.badge, 'clean');
                _setBlocked(guard, false);
            } else {
                const sev = data.severity || 'medium';
                _showBadge(guard.badge, sev, data.reason || '');
                // Block submit only for medium/high severity
                _setBlocked(guard, sev === 'medium' || sev === 'high');
            }
        } catch (_) {
            // Network error → fail-open
            _hideBadge(guard.badge);
            _setBlocked(guard, false);
        }
    }

    /**
     * Watch a textarea/input for inappropriate content.
     * @param {string} fieldId   — The id of the <textarea> or <input>
     * @param {string} apiUrl    — Path to badwords_api.php
     * @param {object} options   — { delay: ms (default 900), blockOnHigh: bool }
     */
    function watch(fieldId, apiUrl, options = {}) {
        const delay = options.delay ?? 900;
        const field = document.getElementById(fieldId);
        if (!field) return;

        const badge = _createBadge(fieldId);

        // Insert badge right after the field (or its wrapper)
        const insertAfter = field.closest('.input-wrap') || field;
        insertAfter.parentNode.insertBefore(badge, insertAfter.nextSibling);

        _guards[fieldId] = { field, badge, timer: null, blocked: false };
        const guard = _guards[fieldId];

        function onInput() {
            clearTimeout(guard.timer);
            const txt = field.value.trim();
            if (txt.length < 4) { _hideBadge(badge); _setBlocked(guard, false); return; }
            _showBadge(badge, 'checking');
            guard.timer = setTimeout(() => _analyze(fieldId, apiUrl), delay);
        }

        field.addEventListener('input',  onInput);
        field.addEventListener('change', onInput);

        // Analyze existing value on load (if any)
        if (field.value.trim().length >= 4) {
            _showBadge(badge, 'checking');
            setTimeout(() => _analyze(fieldId, apiUrl), 300);
        }
    }

    /** Manually trigger analysis on a field */
    function check(fieldId, apiUrl) {
        return _analyze(fieldId, apiUrl);
    }

    /** Returns true if the field is currently blocked */
    function isBlocked(fieldId) {
        return _guards[fieldId]?.blocked ?? false;
    }

    return { watch, check, isBlocked };

})();
