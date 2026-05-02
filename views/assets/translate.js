/**
 * FreelaSkill — AI Page Translator
 * Collects visible text nodes → sends to Groq API → swaps with a smooth fade.
 */
(function () {
  'use strict';

  /* ── CONFIG ─────────────────────────────────────────────── */
  const BATCH_SIZE   = 60;   // texts per API call
  const ENDPOINT     = 'translate_api.php';   // relative; overridden via data-attr
  const SKIP_TAGS    = new Set([
    'SCRIPT', 'STYLE', 'NOSCRIPT', 'IFRAME', 'OBJECT',
    'CODE', 'PRE', 'KBD', 'SAMP', 'VAR',
    'INPUT', 'TEXTAREA', 'SELECT', 'OPTION',
    'svg', 'math',
  ]);
  const SKIP_ATTRS   = ['data-no-translate', 'data-chatbot', 'data-theme-label', 'data-theme-icon'];
  const MIN_LEN      = 2;    // skip very short strings
  const STORAGE_KEY  = 'fs-page-translated';

  /* ── STATE ───────────────────────────────────────────────── */
  let translated     = false;
  let originalTexts  = [];   // [{node, text}]
  let translatedTexts= [];   // string[]
  let busy           = false;

  /* ── WIDGET ROOT (set after DOM ready) ──────────────────── */
  let widget, btn, btnIcon, btnLabel, progressRing, progressText, statusMsg;

  /* ── HELPERS ─────────────────────────────────────────────── */

  function shouldSkipNode(node) {
    let el = node.parentElement;
    while (el) {
      if (SKIP_TAGS.has(el.tagName)) return true;
      for (const attr of SKIP_ATTRS) {
        if (el.hasAttribute(attr)) return true;
      }
      // skip the translate widget itself
      if (el.id === 'fs-translate-widget') return true;
      el = el.parentElement;
    }
    return false;
  }

  function collectTextNodes(root) {
    const walker = document.createTreeWalker(
      root,
      NodeFilter.SHOW_TEXT,
      {
        acceptNode(node) {
          const text = node.textContent;
          if (!text || text.trim().length < MIN_LEN) return NodeFilter.FILTER_REJECT;
          if (shouldSkipNode(node))                  return NodeFilter.FILTER_REJECT;
          return NodeFilter.FILTER_ACCEPT;
        },
      }
    );
    const nodes = [];
    let n;
    while ((n = walker.nextNode())) nodes.push(n);
    return nodes;
  }

  function chunk(arr, size) {
    const out = [];
    for (let i = 0; i < arr.length; i += size) out.push(arr.slice(i, i + size));
    return out;
  }

  async function fetchTranslations(texts, endpoint) {
    const resp = await fetch(endpoint, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ texts }),
    });
    if (!resp.ok) {
      const err = await resp.json().catch(() => ({}));
      throw new Error(err.error || `HTTP ${resp.status}`);
    }
    const data = await resp.json();
    if (!Array.isArray(data.translations)) throw new Error('Unexpected response format');
    return data.translations;
  }

  /* ── FADE SWAP ───────────────────────────────────────────── */
  function applyFadeOut(nodes) {
    // Group by parent element and fade parents
    const parents = new Set(nodes.map(n => n.parentElement).filter(Boolean));
    parents.forEach(el => {
      el.style.transition = 'opacity 0.25s ease';
      el.style.opacity    = '0';
    });
    return new Promise(r => setTimeout(r, 270));
  }

  function applyFadeIn(nodes) {
    const parents = new Set(nodes.map(n => n.parentElement).filter(Boolean));
    parents.forEach(el => {
      el.style.opacity = '1';
    });
  }

  /* ── CORE TRANSLATE ──────────────────────────────────────── */
  async function translatePage() {
    if (busy) return;
    busy = true;

    const endpoint = widget.dataset.translateEndpoint || ENDPOINT;

    setUIState('loading', 0);
    statusMsg.textContent = 'Collecting text…';

    // Collect
    const textNodes = collectTextNodes(document.body);
    if (textNodes.length === 0) {
      setUIState('idle');
      busy = false;
      return;
    }

    // Save originals (only first time)
    if (originalTexts.length === 0) {
      originalTexts = textNodes.map(n => ({ node: n, text: n.textContent }));
    }

    const texts  = originalTexts.map(o => o.text);
    const batches = chunk(texts, BATCH_SIZE);
    let allTranslations = [];
    let done = 0;

    try {
      for (const batch of batches) {
        const results = await fetchTranslations(batch, endpoint);
        allTranslations = allTranslations.concat(results);
        done += batch.length;
        const pct = Math.round((done / texts.length) * 100);
        setUIState('loading', pct);
        statusMsg.textContent = `Translating… ${pct}%`;
      }
    } catch (err) {
      console.error('[Translator]', err);
      showToast('Translation failed: ' + err.message, 'error');
      setUIState('idle');
      busy = false;
      return;
    }

    translatedTexts = allTranslations;

    // Animate swap
    await applyFadeOut(originalTexts.map(o => o.node));
    originalTexts.forEach((o, i) => {
      if (translatedTexts[i] !== undefined) {
        o.node.textContent = translatedTexts[i];
      }
    });
    applyFadeIn(originalTexts.map(o => o.node));

    translated = true;
    sessionStorage.setItem(STORAGE_KEY, '1');
    setUIState('translated');
    showToast('Page translated to English ✓', 'success');
    busy = false;
  }

  async function restorePage() {
    if (busy || !originalTexts.length) return;
    busy = true;

    setUIState('loading', 50);
    statusMsg.textContent = 'Restoring…';

    await applyFadeOut(originalTexts.map(o => o.node));
    originalTexts.forEach(o => {
      o.node.textContent = o.text;
    });
    applyFadeIn(originalTexts.map(o => o.node));

    translated = false;
    sessionStorage.removeItem(STORAGE_KEY);
    setUIState('idle');
    showToast('Original language restored', 'info');
    busy = false;
  }

  /* ── TOAST ───────────────────────────────────────────────── */
  function showToast(msg, type = 'info') {
    const colors = {
      success: { bg: 'rgba(16,185,129,0.15)', border: 'rgba(16,185,129,0.35)', text: '#34d399' },
      error:   { bg: 'rgba(239,68,68,0.15)',  border: 'rgba(239,68,68,0.35)',  text: '#fca5a5' },
      info:    { bg: 'rgba(59,130,246,0.15)',  border: 'rgba(59,130,246,0.35)', text: '#93c5fd' },
    };
    const c = colors[type] || colors.info;
    const toast = document.createElement('div');
    toast.setAttribute('role', 'status');
    toast.style.cssText = `
      position:fixed; bottom:5rem; left:50%; transform:translateX(-50%) translateY(20px);
      background:${c.bg}; border:1px solid ${c.border}; color:${c.text};
      padding:0.65rem 1.25rem; border-radius:9999px; font-size:0.85rem;
      font-family:'Space Grotesk',sans-serif; font-weight:600;
      backdrop-filter:blur(16px); z-index:9999;
      opacity:0; transition:all 0.35s cubic-bezier(0.4,0,0.2,1);
      white-space:nowrap; box-shadow:0 8px 32px rgba(0,0,0,0.25);
      pointer-events:none;
    `;
    toast.textContent = msg;
    document.body.appendChild(toast);
    requestAnimationFrame(() => {
      toast.style.opacity    = '1';
      toast.style.transform  = 'translateX(-50%) translateY(0)';
    });
    setTimeout(() => {
      toast.style.opacity   = '0';
      toast.style.transform = 'translateX(-50%) translateY(20px)';
      setTimeout(() => toast.remove(), 400);
    }, 3200);
  }

  /* ── UI STATE ────────────────────────────────────────────── */
  function setUIState(state, pct = 0) {
    if (!btn) return;
    const circle = progressRing?.querySelector('.fs-trl-ring-fg');

    btn.disabled = (state === 'loading');

    if (state === 'loading') {
      btnIcon.className    = 'fa-solid fa-circle-notch fa-spin';
      btnLabel.textContent = 'Translating…';
      if (circle) {
        const r = 20, c = 2 * Math.PI * r;
        circle.style.strokeDasharray  = c;
        circle.style.strokeDashoffset = c - (pct / 100) * c;
      }
      if (progressText) progressText.textContent = pct + '%';
    } else if (state === 'translated') {
      btnIcon.className    = 'fa-solid fa-rotate-left';
      btnLabel.textContent = 'Restore';
      widget.classList.add('fs-trl--active');
      statusMsg.textContent = 'English mode on';
    } else {
      btnIcon.className    = 'fa-solid fa-earth-americas';
      btnLabel.textContent = 'Translate';
      widget.classList.remove('fs-trl--active');
      statusMsg.textContent = '';
    }
  }

  /* ── INIT WIDGET ─────────────────────────────────────────── */
  function initWidget() {
    widget      = document.getElementById('fs-translate-widget');
    if (!widget) return;

    btn         = widget.querySelector('[data-translate-btn]');
    btnIcon     = widget.querySelector('[data-translate-icon]');
    btnLabel    = widget.querySelector('[data-translate-label]');
    progressRing= widget.querySelector('.fs-trl-progress');
    progressText= widget.querySelector('.fs-trl-pct');
    statusMsg   = widget.querySelector('[data-translate-status]');

    btn.addEventListener('click', () => {
      translated ? restorePage() : translatePage();
    });

    // Restore translated state within the same session
    if (sessionStorage.getItem(STORAGE_KEY) === '1') {
      // Already translated in this session — re-translate silently
      translatePage();
    }
  }

  document.addEventListener('DOMContentLoaded', initWidget);
})();
