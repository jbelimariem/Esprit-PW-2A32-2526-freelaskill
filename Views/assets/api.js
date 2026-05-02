/**
 * FreelaSkill — API Client JS
 * Gère tous les appels aux services API externes depuis le frontend.
 * Utilisé dans les formulaires contrat et règle (front + back).
 */

// Chemin vers apiController — défini par PHP dans chaque page, sinon auto-détection
if (typeof window.API_BASE === 'undefined') {
    (function() {
        const path = window.location.pathname;
        const parts = path.split('/');
        const viewsIdx = parts.findIndex(p => p === 'Views');
        if (viewsIdx > 0) {
            window.API_BASE = parts.slice(0, viewsIdx).join('/') + '/controllers/apiController.php';
        } else if (path.includes('/Frontoffice/') || path.includes('/Backoffice/')) {
            window.API_BASE = '../../controllers/apiController.php';
        } else {
            window.API_BASE = '../controllers/apiController.php';
        }
    })();
}

const API_BASE = window.API_BASE;

// ── Utilitaires ──────────────────────────────────────────────────────

function showApiLoader(btnEl, text = 'Chargement...') {
    if (!btnEl) return;
    btnEl.dataset.originalHtml = btnEl.innerHTML;
    btnEl.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${text}`;
    btnEl.disabled = true;
}

function hideApiLoader(btnEl) {
    if (!btnEl || !btnEl.dataset.originalHtml) return;
    btnEl.innerHTML = btnEl.dataset.originalHtml;
    btnEl.disabled = false;
}

function showApiToast(message, type = 'success') {
    const existing = document.getElementById('api-toast');
    if (existing) existing.remove();

    const colors = {
        success: { bg: 'rgba(16,185,129,0.15)', border: 'rgba(16,185,129,0.3)', color: '#34D399' },
        error:   { bg: 'rgba(239,68,68,0.15)',  border: 'rgba(239,68,68,0.3)',  color: '#F87171' },
        info:    { bg: 'rgba(37,99,235,0.15)',   border: 'rgba(37,99,235,0.3)',  color: '#60A5FA' },
    };
    const c = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.id = 'api-toast';
    toast.style.cssText = `
        position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
        background: ${c.bg}; border: 1px solid ${c.border}; color: ${c.color};
        padding: 0.85rem 1.25rem; border-radius: 12px;
        font-size: 0.88rem; font-weight: 500; font-family: 'Space Grotesk', sans-serif;
        display: flex; align-items: center; gap: 0.6rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        animation: slideInRight 0.3s ease;
        max-width: 380px;
    `;
    const icon = type === 'success' ? 'fa-circle-check' : type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-info';
    toast.innerHTML = `<i class="fa-solid ${icon}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => { if (toast.parentNode) toast.remove(); }, 4000);
}

async function apiPost(action, data) {
    try {
        const res = await fetch(`${API_BASE}?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        return await res.json();
    } catch (err) {
        return { success: false, error: 'Erreur réseau : ' + err.message };
    }
}

// ── 1. TRADUCTION ────────────────────────────────────────────────────

/**
 * Traduit un champ texte et affiche le résultat dans un élément cible.
 * @param {string} sourceId   ID du champ source
 * @param {string} targetId   ID du champ ou div cible
 * @param {string} from       Code langue source
 * @param {string} to         Code langue cible
 * @param {HTMLElement} btn   Bouton déclencheur
 */
async function translateField(sourceId, targetId, from, to, btn) {
    const source = document.getElementById(sourceId);
    const target = document.getElementById(targetId);
    if (!source || !target) return;

    const text = source.value || source.textContent;
    if (!text.trim()) { showApiToast('Aucun texte à traduire.', 'error'); return; }

    showApiLoader(btn, 'Traduction...');
    const result = await apiPost('translate', { text, from, to });
    hideApiLoader(btn);

    if (result.success) {
        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
            target.value = result.translated;
        } else {
            target.textContent = result.translated;
        }
        showApiToast(`Traduit en ${to.toUpperCase()} ✓`, 'success');
    } else {
        showApiToast(result.error || 'Erreur de traduction.', 'error');
    }
}

/**
 * Traduit tous les champs d'un contrat (titre + description).
 */
async function translateContratFields(from, to, btn) {
    const titreEl = document.getElementById('titre');
    const descEl  = document.getElementById('description');

    if (!titreEl && !descEl) { showApiToast('Champs introuvables.', 'error'); return; }

    const contrat = {
        titre:       titreEl?.value || '',
        description: descEl?.value  || '',
    };

    if (!contrat.titre && !contrat.description) {
        showApiToast('Remplissez d\'abord le titre ou la description.', 'error');
        return;
    }

    showApiLoader(btn, 'Traduction...');
    const result = await apiPost('translate_contrat', { contrat, from, to });
    hideApiLoader(btn);

    if (result.success) {
        const c = result.contrat;
        if (c.titre_translated && titreEl) {
            showTranslationPreview('titre', c.titre_translated, to);
        }
        if (c.description_translated && descEl) {
            showTranslationPreview('description', c.description_translated, to);
        }
        showApiToast('Traduction terminée ✓', 'success');
    } else {
        showApiToast(result.error || 'Erreur de traduction.', 'error');
    }
}

function showTranslationPreview(fieldId, translatedText, lang) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    const existingPreview = document.getElementById(`preview-${fieldId}`);
    if (existingPreview) existingPreview.remove();

    const langNames = { fr: 'Français', en: 'English', ar: 'العربية' };
    const preview = document.createElement('div');
    preview.id = `preview-${fieldId}`;
    preview.style.cssText = `
        margin-top: 0.5rem;
        background: rgba(37,99,235,0.08);
        border: 1px solid rgba(37,99,235,0.2);
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        color: #94A3B8;
        position: relative;
    `;
    preview.innerHTML = `
        <div style="font-size:0.72rem;font-weight:600;color:#60A5FA;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.4rem;">
            <i class="fa-solid fa-language"></i> Traduction ${langNames[lang] || lang}
        </div>
        <div style="color:#CBD5E1;line-height:1.5;">${translatedText}</div>
        <button type="button" onclick="applyTranslation('${fieldId}', this.parentElement)"
                style="margin-top:0.5rem;background:rgba(37,99,235,0.15);color:#60A5FA;border:1px solid rgba(37,99,235,0.3);padding:0.3rem 0.75rem;border-radius:999px;font-size:0.78rem;cursor:pointer;font-family:inherit;">
            <i class="fa-solid fa-check"></i> Appliquer
        </button>
        <button type="button" onclick="this.parentElement.remove()"
                style="margin-top:0.5rem;margin-left:0.4rem;background:transparent;color:#475569;border:1px solid rgba(255,255,255,0.1);padding:0.3rem 0.75rem;border-radius:999px;font-size:0.78rem;cursor:pointer;font-family:inherit;">
            Ignorer
        </button>
    `;
    field.parentNode.insertBefore(preview, field.nextSibling);
}

function applyTranslation(fieldId, previewEl) {
    const field = document.getElementById(fieldId);
    const textEl = previewEl.querySelector('div[style*="color:#CBD5E1"]');
    if (field && textEl) {
        if (field.tagName === 'TEXTAREA' || field.tagName === 'INPUT') {
            field.value = textEl.textContent;
        }
        showApiToast('Traduction appliquée ✓', 'success');
    }
    previewEl.remove();
}

// ── 2. GÉNÉRATION IA (Gemini) ─────────────────────────────────────────

async function generateDescription(btn) {
    const titre      = document.getElementById('titre')?.value?.trim();
    const freelancer = document.getElementById('freelance_info')?.value?.trim() || '';
    const budget     = parseFloat(document.getElementById('budget')?.value) || 0;
    const delai      = parseInt(document.getElementById('delai')?.value)    || 0;
    const lang       = document.getElementById('lang-select')?.value || 'fr';

    if (!titre) {
        showApiToast('Remplissez d\'abord le titre du contrat.', 'error');
        return;
    }

    showApiLoader(btn, 'Génération IA...');
    const result = await apiPost('generate_description', { titre, freelancer, budget, delai, lang });
    hideApiLoader(btn);

    if (result.success) {
        const descEl = document.getElementById('description');
        if (descEl) {
            descEl.value = result.text;
            descEl.style.borderColor = 'rgba(37,99,235,0.5)';
            setTimeout(() => { descEl.style.borderColor = ''; }, 2000);
        }
        const source = result.source === 'local' ? 'Description générée (modèle local) ✓' : 'Description générée par IA ✓';
        showApiToast(source, 'success');
    } else {
        showApiToast(result.error || 'Erreur Gemini.', 'error');
    }
}

async function suggestRules(btn) {
    const titre = document.getElementById('titre')?.value?.trim()
               || document.getElementById('titre_contrat')?.value?.trim();
    const lang  = document.getElementById('lang-select')?.value || 'fr';

    if (!titre) {
        showApiToast('Remplissez d\'abord le titre du contrat.', 'error');
        return;
    }

    showApiLoader(btn, 'Génération IA...');
    const result = await apiPost('suggest_rules', { titre_contrat: titre, lang });
    hideApiLoader(btn);

    if (result.success && result.rules) {
        displaySuggestedRules(result.rules);
        const source = result.source === 'local' ? `${result.rules.length} règles suggérées (modèle local) ✓` : `${result.rules.length} règles suggérées ✓`;
        showApiToast(source, 'success');
    } else {
        showApiToast(result.error || 'Erreur Gemini.', 'error');
    }
}

function displaySuggestedRules(rules) {
    const existing = document.getElementById('suggested-rules-panel');
    if (existing) existing.remove();

    const panel = document.createElement('div');
    panel.id = 'suggested-rules-panel';
    panel.style.cssText = `
        background: rgba(37,99,235,0.06);
        border: 1px solid rgba(37,99,235,0.2);
        border-radius: 14px;
        padding: 1.25rem;
        margin-top: 1rem;
    `;

    panel.innerHTML = `
        <div style="font-size:0.82rem;font-weight:700;color:#60A5FA;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:1rem;">
            <i class="fa-solid fa-robot"></i> Règles suggérées par l'IA
        </div>
        ${rules.map((r, i) => `
            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:0.85rem 1rem;margin-bottom:0.6rem;display:flex;align-items:flex-start;gap:0.75rem;">
                <input type="checkbox" id="suggested-rule-${i}" checked
                       style="width:1rem;height:1rem;accent-color:#2563EB;cursor:pointer;flex-shrink:0;margin-top:2px;">
                <div style="flex:1;">
                    <div style="font-weight:600;color:#F1F5F9;font-size:0.88rem;">${r.titre}</div>
                    <div style="font-size:0.78rem;color:#94A3B8;margin-top:0.2rem;">
                        <span style="background:rgba(37,99,235,0.1);color:#60A5FA;padding:0.1rem 0.5rem;border-radius:999px;font-size:0.72rem;font-weight:500;">${r.type}</span>
                        ${r.valeur ? `&nbsp;· Valeur : ${r.valeur}` : ''}
                    </div>
                    <div style="font-size:0.82rem;color:#64748B;margin-top:0.35rem;line-height:1.5;">${r.description}</div>
                </div>
            </div>
        `).join('')}
        <button type="button" onclick="applySuggestedRules(${JSON.stringify(rules).replace(/"/g, '&quot;')})"
                style="margin-top:0.5rem;background:var(--tech-blue,#2563EB);color:white;border:none;padding:0.6rem 1.25rem;border-radius:999px;font-size:0.85rem;font-weight:600;cursor:pointer;font-family:inherit;">
            <i class="fa-solid fa-plus"></i> Ajouter les règles sélectionnées
        </button>
        <button type="button" onclick="document.getElementById('suggested-rules-panel').remove()"
                style="margin-left:0.5rem;background:transparent;color:#475569;border:1px solid rgba(255,255,255,0.1);padding:0.6rem 1.25rem;border-radius:999px;font-size:0.85rem;cursor:pointer;font-family:inherit;">
            Ignorer
        </button>
    `;

    // Insérer après le bouton suggest
    const suggestBtn = document.getElementById('btn-suggest-rules');
    if (suggestBtn) {
        suggestBtn.parentNode.insertBefore(panel, suggestBtn.nextSibling);
    } else {
        document.querySelector('form')?.appendChild(panel);
    }
}

function applySuggestedRules(rules) {
    const selected = rules.filter((_, i) => {
        const cb = document.getElementById(`suggested-rule-${i}`);
        return cb && cb.checked;
    });

    if (selected.length === 0) {
        showApiToast('Sélectionnez au moins une règle.', 'error');
        return;
    }

    // Stocker les règles suggérées pour soumission
    const existing = document.getElementById('suggested-rules-data');
    if (existing) existing.remove();

    const input = document.createElement('input');
    input.type  = 'hidden';
    input.id    = 'suggested-rules-data';
    input.name  = 'suggested_rules_json';
    input.value = JSON.stringify(selected);
    document.querySelector('form')?.appendChild(input);

    document.getElementById('suggested-rules-panel')?.remove();
    showApiToast(`${selected.length} règle(s) prête(s) à être ajoutée(s) ✓`, 'success');
}

// ── 3. VÉRIFICATION CONTENU (Bad Words) ──────────────────────────────

async function checkContent(fields, onSuccess) {
    const result = await apiPost('check_content', { fields });

    if (!result.success) return true; // En cas d'erreur API, on laisse passer

    if (!result.is_clean) {
        // Afficher les erreurs sur les champs concernés
        Object.entries(result.errors || {}).forEach(([fieldName, msg]) => {
            const field = document.getElementById(fieldName)
                       || document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.style.borderColor = '#EF4444';
                field.style.boxShadow   = '0 0 0 3px rgba(239,68,68,0.15)';

                const existing = field.parentNode.querySelector('.badwords-error');
                if (existing) existing.remove();

                const err = document.createElement('span');
                err.className = 'badwords-error';
                err.style.cssText = 'font-size:0.8rem;color:#F87171;font-weight:500;display:block;margin-top:0.3rem;';
                err.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${msg}`;
                field.parentNode.appendChild(err);
            }
        });
        showApiToast('Contenu inapproprié détecté. Veuillez corriger.', 'error');
        return false;
    }

    if (onSuccess) onSuccess();
    return true;
}

/**
 * Intercepte la soumission d'un formulaire pour vérifier le contenu.
 * @param {string} formId     ID du formulaire
 * @param {string[]} fieldIds IDs des champs à vérifier
 */
function setupContentCheck(formId, fieldIds) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Nettoyer les erreurs précédentes
        document.querySelectorAll('.badwords-error').forEach(el => el.remove());
        fieldIds.forEach(id => {
            const f = document.getElementById(id) || document.querySelector(`[name="${id}"]`);
            if (f) { f.style.borderColor = ''; f.style.boxShadow = ''; }
        });

        const fields = {};
        fieldIds.forEach(id => {
            const el = document.getElementById(id) || document.querySelector(`[name="${id}"]`);
            if (el && el.value && el.value.trim()) fields[id] = el.value.trim();
        });

        // Si pas de champs à vérifier, soumettre directement
        if (Object.keys(fields).length === 0) { form.submit(); return; }

        try {
            // Timeout de 3 secondes — si l'API ne répond pas, on soumet quand même
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 3000);

            const res = await fetch(`${API_BASE}?action=check_content`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ fields }),
                signal: controller.signal,
            });
            clearTimeout(timeout);

            const result = await res.json();

            if (result.success && !result.is_clean && result.errors && Object.keys(result.errors).length > 0) {
                // Afficher les erreurs sur les champs concernés
                Object.entries(result.errors).forEach(([fieldName, msg]) => {
                    const field = document.getElementById(fieldName)
                               || document.querySelector(`[name="${fieldName}"]`);
                    if (field) {
                        field.style.borderColor = '#EF4444';
                        field.style.boxShadow   = '0 0 0 3px rgba(239,68,68,0.15)';
                        const existing = field.parentNode.querySelector('.badwords-error');
                        if (existing) existing.remove();
                        const err = document.createElement('span');
                        err.className = 'badwords-error';
                        err.style.cssText = 'font-size:0.8rem;color:#F87171;font-weight:500;display:block;margin-top:0.3rem;';
                        err.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${msg}`;
                        field.parentNode.appendChild(err);
                    }
                });
                showApiToast('Contenu inapproprié détecté. Veuillez corriger.', 'error');
                return; // Bloquer la soumission
            }

            // Contenu propre ou API indisponible → soumettre
            form.submit();

        } catch (err) {
            // Timeout ou erreur réseau → soumettre quand même sans bloquer
            form.submit();
        }
    });
}

// ── 4. OCR (Tesseract.js) ─────────────────────────────────────────────

/**
 * Lit le texte d'une image via Tesseract.js et remplit les champs du formulaire.
 * Tesseract.js est chargé depuis CDN — pas besoin de clé API.
 */
async function processOcrImage(file, targetFields, btn) {
    if (!file) { showApiToast('Sélectionnez une image.', 'error'); return; }

    showApiLoader(btn, 'Lecture OCR...');

    try {
        // Convertir le fichier en base64
        const base64 = await fileToBase64(file);
        const lang   = document.getElementById('lang-select')?.value || 'fr';

        // Essayer d'abord Gemini Vision (plus précis pour l'écriture manuscrite)
        const geminiResult = await apiPost('ocr_smart', { image: base64, lang });

        if (geminiResult.success && geminiResult.extracted) {
            hideApiLoader(btn);
            fillFieldsFromExtracted(geminiResult.extracted, targetFields);
            showApiToast('OCR intelligent ✓ (Gemini Vision)', 'success');
            return;
        }

        // Fallback : Tesseract.js
        if (!window.Tesseract) {
            hideApiLoader(btn);
            showApiToast('OCR non disponible. Installez Tesseract.js.', 'error');
            return;
        }

        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> OCR Tesseract...`;

        const result = await Tesseract.recognize(file, 'fra+eng', {
            logger: m => {
                if (m.status === 'recognizing text' && btn) {
                    const pct = Math.round(m.progress * 100);
                    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> OCR ${pct}%`;
                }
            }
        });

        const text = result.data.text.trim();
        hideApiLoader(btn);

        if (!text || text.length < 5) {
            showApiToast('Aucun texte détecté. Essayez une image plus nette.', 'error');
            return;
        }

        fillFieldsFromOcr(text, targetFields);
        showApiToast('Texte extrait (Tesseract) ✓', 'success');

    } catch (err) {
        hideApiLoader(btn);
        showApiToast('Erreur OCR : ' + err.message, 'error');
    }
}

/**
 * Convertit un File en base64 data URL.
 */
function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload  = e => resolve(e.target.result);
        reader.onerror = e => reject(e);
        reader.readAsDataURL(file);
    });
}

/**
 * Remplit les champs depuis les données extraites par Gemini Vision.
 */
function fillFieldsFromExtracted(extracted, targetFields) {
    const map = {
        titre:          targetFields.titre          || 'titre',
        description:    targetFields.description    || 'description',
        budget:         'budget',
        delai:          'delai',
        freelance_info: 'freelance_info',
    };

    const filled = [];

    Object.entries(map).forEach(([key, fieldId]) => {
        const value = extracted[key];
        if (value && value.toString().trim()) {
            const el = document.getElementById(fieldId);
            if (el) {
                el.value = value.toString().trim();
                el.style.borderColor = 'rgba(16,185,129,0.5)';
                setTimeout(() => { el.style.borderColor = ''; }, 2000);
                filled.push(key);
            }
        }
    });

    // Afficher un résumé des champs remplis
    showOcrPreview(
        JSON.stringify(extracted, null, 2),
        extracted
    );
}

function fillFieldsFromOcr(text, targetFields) {
    const lines    = text.split('\n').map(l => l.trim()).filter(l => l.length > 1);
    const fullText = lines.join('\n');

    // ── Extraction intelligente ──────────────────────────────────────

    // 1. TITRE — première ligne significative (pas un nombre, pas trop courte)
    let titre = '';
    for (const line of lines) {
        if (line.length >= 4 && line.length <= 100 && !/^\d+$/.test(line)) {
            titre = line.replace(/[*_#]/g, '').trim();
            break;
        }
    }

    // 2. BUDGET — nombre après "budget", "Budget", ou suivi de DT/€/$
    let budget = '';
    const budgetPatterns = [
        /budget\s*[:\-=]?\s*(\d[\d\s,\.]*)\s*(dt|dinar|tnd|€|\$|eur)?/i,
        /(\d{2,7}(?:[,\.]\d{1,2})?)\s*(dt|dinar|tnd|€|\$)/i,
        /montant\s*[:\-=]?\s*(\d[\d\s,\.]*)/i,
        /prix\s*[:\-=]?\s*(\d[\d\s,\.]*)/i,
    ];
    for (const p of budgetPatterns) {
        const m = fullText.match(p);
        if (m) { budget = m[1].replace(/\s/g, '').replace(',', '.'); break; }
    }

    // 3. DÉLAI — nombre de jours
    let delai = '';
    const delaiPatterns = [
        /d[eé]lai\s*[:\-=]?\s*(\d+)\s*(jours?|days?|semaines?|mois)?/i,
        /(\d+)\s*(jours?|days?)/i,
        /dur[eé]e\s*[:\-=]?\s*(\d+)/i,
        /livraison\s*[:\-=]?\s*(\d+)/i,
    ];
    for (const p of delaiPatterns) {
        const m = fullText.match(p);
        if (m) { delai = m[1]; break; }
    }

    // 4. FREELANCER — nom après mots-clés
    let freelancer = '';
    const freelancerPatterns = [
        /(?:freelancer|prestataire|d[eé]veloppeur|designer|consultant|avec|par|réalisé par)\s*[:\-]?\s*([A-ZÀ-Ü][a-zà-ü]+(?:\s+[A-ZÀ-Ü][a-zà-ü]+){0,3})/i,
        /(?:nom|name)\s*[:\-]?\s*([A-ZÀ-Ü][a-zà-ü]+(?:\s+[A-ZÀ-Ü][a-zà-ü]+){0,2})/i,
    ];
    for (const p of freelancerPatterns) {
        const m = fullText.match(p);
        if (m) { freelancer = m[1].trim(); break; }
    }

    // 5. DESCRIPTION — lignes pertinentes (exclure titre, budget, délai, freelancer)
    const skipPatterns = [
        /budget|montant|prix/i,
        /d[eé]lai|livraison|dur[eé]e/i,
        /freelancer|prestataire|d[eé]veloppeur/i,
        /^\d+[\.,]?\d*\s*(dt|€|\$|dinar)?$/i,  // lignes avec juste un nombre
        /^[*#_\-=]+$/,  // séparateurs
    ];

    const descLines = lines.slice(1).filter(line => {
        if (line === titre) return false;
        if (line.length < 5) return false;
        return !skipPatterns.some(p => p.test(line));
    });

    const description = descLines.slice(0, 10).join('\n').trim(); // max 10 lignes

    // ── Remplir les champs avec highlight ────────────────────────────

    const fieldsToFill = [
        { id: targetFields.titre || 'titre',               value: titre,       label: 'Titre' },
        { id: targetFields.description || 'description',   value: description, label: 'Description' },
        { id: 'budget',                                     value: budget,      label: 'Budget' },
        { id: 'delai',                                      value: delai,       label: 'Délai' },
        { id: 'freelance_info',                             value: freelancer,  label: 'Freelancer' },
    ];

    const filled = [];
    fieldsToFill.forEach(({ id, value, label }) => {
        if (!value) return;
        const el = document.getElementById(id);
        if (!el) return;
        // Ne pas écraser un champ déjà rempli (sauf titre et description)
        if (el.value && id !== 'titre' && id !== 'description') return;
        el.value = value.toString().trim().substring(0, id === 'titre' ? 255 : 2000);
        el.style.borderColor = 'rgba(16,185,129,0.6)';
        el.style.boxShadow   = '0 0 0 3px rgba(16,185,129,0.15)';
        setTimeout(() => { el.style.borderColor = ''; el.style.boxShadow = ''; }, 3000);
        filled.push(label);
    });

    // Afficher le résumé
    showOcrPreview(text, { titre, budget, delai, freelancer });

    if (filled.length > 0) {
        showApiToast(`Champs remplis : ${filled.join(', ')} ✓`, 'success');
    } else {
        showApiToast('Texte extrait mais aucun champ reconnu. Vérifiez le texte brut.', 'info');
    }
}

function showOcrPreview(text, extracted = {}) {
    const existing = document.getElementById('ocr-preview');
    if (existing) existing.remove();

    const panel = document.createElement('div');
    panel.id = 'ocr-preview';
    panel.style.cssText = `
        background: rgba(16,185,129,0.06);
        border: 1px solid rgba(16,185,129,0.2);
        border-radius: 12px;
        padding: 1rem;
        margin-top: 0.75rem;
        font-size: 0.82rem;
        color: #94A3B8;
    `;

    const extractedHtml = Object.entries(extracted)
        .filter(([, v]) => v)
        .map(([k, v]) => `<span style="background:rgba(16,185,129,0.1);color:#34D399;padding:0.15rem 0.5rem;border-radius:4px;margin-right:0.4rem;font-size:0.75rem;"><b>${k}</b>: ${v.substring(0,40)}</span>`)
        .join('');

    panel.innerHTML = `
        <div style="font-size:0.72rem;font-weight:700;color:#34D399;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.5rem;">
            <i class="fa-solid fa-file-image"></i> OCR — Champs extraits
        </div>
        ${extractedHtml ? `<div style="margin-bottom:0.6rem;flex-wrap:wrap;display:flex;gap:0.3rem;">${extractedHtml}</div>` : ''}
        <details style="cursor:pointer;">
            <summary style="font-size:0.75rem;color:#64748B;margin-bottom:0.4rem;">Voir le texte brut</summary>
            <pre style="white-space:pre-wrap;font-family:inherit;color:#CBD5E1;line-height:1.5;font-size:0.78rem;max-height:120px;overflow-y:auto;">${text}</pre>
        </details>
        <button type="button" onclick="document.getElementById('ocr-preview').remove()"
                style="margin-top:0.5rem;background:transparent;color:#475569;border:1px solid rgba(255,255,255,0.1);padding:0.3rem 0.75rem;border-radius:999px;font-size:0.75rem;cursor:pointer;font-family:inherit;">
            Fermer
        </button>
    `;

    const ocrZone = document.getElementById('ocr-zone');
    if (ocrZone) ocrZone.appendChild(panel);
    else document.querySelector('form')?.appendChild(panel);
}

// ── Animation CSS ─────────────────────────────────────────────────────
const style = document.createElement('style');
style.textContent = `
@keyframes slideInRight {
    from { transform: translateX(100px); opacity: 0; }
    to   { transform: translateX(0);     opacity: 1; }
}
`;
document.head.appendChild(style);
