/**
 * FreelaSkill — AI Page Translator (multi-language)
 * Cycles: Original → English → Français → العربية → Original
 */
(function () {
  'use strict';

  /* ── CONFIG ─────────────────────────────────────────────── */
  const BATCH_SIZE  = 30;
  const ENDPOINT    = 'translate_api.php';
  const SKIP_TAGS   = new Set([
    'SCRIPT', 'STYLE', 'NOSCRIPT', 'IFRAME', 'OBJECT',
    'CODE', 'PRE', 'KBD', 'SAMP', 'VAR',
    'INPUT', 'TEXTAREA', 'SELECT', 'OPTION',
    'svg', 'math',
  ]);
  const SKIP_ATTRS  = ['data-no-translate', 'data-chatbot', 'data-theme-label', 'data-theme-icon'];
  const MIN_LEN     = 2;
  const STORAGE_KEY = 'fs-page-lang';

  const LANG_CYCLE = [
    { code: 'en', label: 'EN', name: 'English',  dir: 'ltr', icon: 'fa-solid fa-earth-americas' },
    { code: 'ar', label: 'AR', name: 'العربية',  dir: 'rtl', icon: 'fa-solid fa-earth-africa'   },
  ];

  /* ── STATE ───────────────────────────────────────────────── */
  let currentLangIdx = -1;   // -1 = original
  let originalTexts  = [];   // [{node, text}]
  let cache          = {};   // { en: [], fr: [], ar: [] }
  let busy           = false;
  let originalDir    = '';

  /* ── WIDGET REFS ─────────────────────────────────────────── */
  let widget, btn, btnIcon, btnLabel, progressRing, statusMsg;

  /* ── HELPERS ─────────────────────────────────────────────── */
  function shouldSkipNode(node) {
    let el = node.parentElement;
    while (el) {
      if (SKIP_TAGS.has(el.tagName)) return true;
      for (const attr of SKIP_ATTRS) if (el.hasAttribute(attr)) return true;
      if (el.id === 'fs-translate-widget') return true;
      el = el.parentElement;
    }
    return false;
  }

  function collectTextNodes(root) {
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode(node) {
        const t = node.textContent;
        if (!t || t.trim().length < MIN_LEN) return NodeFilter.FILTER_REJECT;
        if (shouldSkipNode(node))            return NodeFilter.FILTER_REJECT;
        return NodeFilter.FILTER_ACCEPT;
      },
    });
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

  async function fetchTranslations(texts, langCode, endpoint, retries = 2) {
    for (let attempt = 0; attempt <= retries; attempt++) {
      try {
        const resp = await fetch(endpoint, {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ texts, target_lang: langCode }),
        });
        if (!resp.ok) {
          const err = await resp.json().catch(() => ({}));
          throw new Error(err.error || `HTTP ${resp.status}`);
        }
        const data = await resp.json();
        if (!Array.isArray(data.translations)) throw new Error('Unexpected response format');
        return data.translations;
      } catch (err) {
        if (attempt === retries || !err.message.toLowerCase().includes('limit')) {
          throw err;
        }
        // Wait and retry with exponential backoff if rate limited
        await new Promise(r => setTimeout(r, 1500 * (attempt + 1)));
      }
    }
  }

  /* ── FADE HELPERS ────────────────────────────────────────── */
  function fadeOut(nodes) {
    const parents = new Set(nodes.map(n => n.parentElement).filter(Boolean));
    parents.forEach(el => {
      el.style.transition = 'opacity 0.25s ease';
      el.style.opacity    = '0';
    });
    return new Promise(r => setTimeout(r, 270));
  }

  function fadeIn(nodes) {
    const parents = new Set(nodes.map(n => n.parentElement).filter(Boolean));
    parents.forEach(el => { el.style.opacity = '1'; });
  }

  function applyTexts(texts) {
    originalTexts.forEach((o, i) => {
      if (texts[i] !== undefined) o.node.textContent = texts[i];
    });
  }

  /* ── TRANSLATE TO LANG ───────────────────────────────────── */
  async function translateTo(langDef) {
    const endpoint = widget.dataset.translateEndpoint || ENDPOINT;

    // Collect originals once
    if (originalTexts.length === 0) {
      const nodes = collectTextNodes(document.body);
      originalTexts = nodes.map(n => ({ node: n, text: n.textContent }));
    }

    // Use cache if available
    if (cache[langDef.code]) {
      await fadeOut(originalTexts.map(o => o.node));
      applyTexts(cache[langDef.code]);
      fadeIn(originalTexts.map(o => o.node));
      return;
    }

    // Fetch in batches from original texts
    const texts   = originalTexts.map(o => o.text);
    const batches = chunk(texts, BATCH_SIZE);
    let all  = [];
    let done = 0;

    setUIState('loading', 0, langDef);
    statusMsg.textContent = 'Collecting text…';

    for (const batch of batches) {
      const results = await fetchTranslations(batch, langDef.code, endpoint);
      all  = all.concat(results);
      done += batch.length;
      const pct = Math.round((done / texts.length) * 100);
      setUIState('loading', pct, langDef);
      statusMsg.textContent = `Translating… ${pct}%`;
      
      // Delay between batches to respect rate limits
      if (done < texts.length) {
        await new Promise(r => setTimeout(r, 600));
      }
    }

    // Align counts
    while (all.length < texts.length) all.push(texts[all.length]);
    all = all.slice(0, texts.length);

    cache[langDef.code] = all;

    await fadeOut(originalTexts.map(o => o.node));
    applyTexts(all);
    fadeIn(originalTexts.map(o => o.node));
  }

  /* ── RESTORE ORIGINAL ────────────────────────────────────── */
  async function restoreOriginal() {
    await fadeOut(originalTexts.map(o => o.node));
    originalTexts.forEach(o => { o.node.textContent = o.text; });
    fadeIn(originalTexts.map(o => o.node));
    document.body.dir                  = originalDir;
    document.documentElement.dir       = originalDir;
  }

  /* ── CLICK HANDLER (cycle) ───────────────────────────────── */
  async function handleClick() {
    if (busy) return;
    busy = true;

    // Cycle: 0=EN, 1=FR, 2=AR, 3=restore → back to -1
    const nextIdx = currentLangIdx + 1;

    if (nextIdx >= LANG_CYCLE.length) {
      // Restore original
      setUIState('loading', 50, null);
      statusMsg.textContent = 'Restoring…';
      try {
        await restoreOriginal();
        currentLangIdx = -1;
        setUIState('idle');
        sessionStorage.removeItem(STORAGE_KEY);
        showToast('Original language restored', 'info');
      } catch (e) {
        showToast('Restore failed', 'error');
        setUIState('idle');
      }
    } else {
      const langDef = LANG_CYCLE[nextIdx];
      try {
        await translateTo(langDef);
        document.body.dir            = langDef.dir;
        document.documentElement.dir = langDef.dir;
        currentLangIdx = nextIdx;
        setUIState('translated', 0, langDef);
        sessionStorage.setItem(STORAGE_KEY, langDef.code);
        showToast(`Page translated to ${langDef.name} ✓`, 'success');
      } catch (err) {
        console.error('[Translator]', err);
        showToast(err.message || 'Translation failed. Please try again.', 'error');
        // Revert UI to wherever we were
        if (currentLangIdx === -1) {
          setUIState('idle');
        } else {
          setUIState('translated', 0, LANG_CYCLE[currentLangIdx]);
        }
      }
    }

    busy = false;
  }

  /* ── DIRECT JUMP (session restore) ──────────────────────── */
  async function jumpToLang(code) {
    const idx = LANG_CYCLE.findIndex(l => l.code === code);
    if (idx === -1) return;
    busy = true;
    const langDef = LANG_CYCLE[idx];
    setUIState('loading', 0, langDef);
    try {
      await translateTo(langDef);
      document.body.dir            = langDef.dir;
      document.documentElement.dir = langDef.dir;
      currentLangIdx = idx;
      setUIState('translated', 0, langDef);
    } catch (e) {
      setUIState('idle');
    }
    busy = false;
  }

  /* ── TOAST ───────────────────────────────────────────────── */
  function showToast(msg, type = 'info') {
    const colors = {
      success: { bg: 'rgba(16,185,129,0.15)', border: 'rgba(16,185,129,0.35)', text: '#34d399' },
      error:   { bg: 'rgba(239,68,68,0.15)',  border: 'rgba(239,68,68,0.35)',  text: '#fca5a5' },
      info:    { bg: 'rgba(59,130,246,0.15)', border: 'rgba(59,130,246,0.35)', text: '#93c5fd' },
    };
    const c = colors[type] || colors.info;
    const toast = document.createElement('div');
    toast.setAttribute('role', 'status');
    toast.style.cssText = `
      position:fixed; bottom:5rem; left:50%; transform:translateX(-50%) translateY(20px);
      background:${c.bg}; border:1px solid ${c.border}; color:${c.text};
      padding:0.65rem 1.25rem; border-radius:14px; font-size:0.85rem;
      font-family:'Space Grotesk',sans-serif; font-weight:600;
      backdrop-filter:blur(16px); z-index:9999;
      opacity:0; transition:all 0.35s cubic-bezier(0.4,0,0.2,1);
      width:max-content; max-width:min(520px,calc(100vw - 2rem));
      white-space:normal; text-align:center; line-height:1.45;
      box-shadow:0 8px 32px rgba(0,0,0,0.25); pointer-events:none;
    `;
    toast.textContent = msg;
    document.body.appendChild(toast);
    requestAnimationFrame(() => {
      toast.style.opacity   = '1';
      toast.style.transform = 'translateX(-50%) translateY(0)';
    });
    setTimeout(() => {
      toast.style.opacity   = '0';
      toast.style.transform = 'translateX(-50%) translateY(20px)';
      setTimeout(() => toast.remove(), 400);
    }, 3200);
  }

  /* ── LANGUAGE DOTS ───────────────────────────────────────── */
  function updateDots() {
    const dots = widget.querySelectorAll('.fs-trl-dot');
    dots.forEach((dot, i) => {
      dot.classList.toggle('fs-trl-dot--active', i === currentLangIdx);
    });
  }

  /* ── UI STATE ────────────────────────────────────────────── */
  function setUIState(state, pct = 0, langDef = null) {
    if (!btn) return;
    const circle      = progressRing?.querySelector('.fs-trl-ring-fg');
    const tooltipSpan = widget.querySelector('.fs-trl-tooltip span');

    btn.disabled = (state === 'loading');

    if (state === 'loading') {
      btnIcon.className    = 'fa-solid fa-circle-notch fa-spin';
      btnLabel.textContent = langDef ? langDef.label : '…';
      if (circle) {
        const r = 20, circ = 2 * Math.PI * r;
        circle.style.strokeDasharray  = circ;
        circle.style.strokeDashoffset = circ - (pct / 100) * circ;
      }
    } else if (state === 'translated') {
      const nextIdx = currentLangIdx + 1;
      const next    = nextIdx < LANG_CYCLE.length ? LANG_CYCLE[nextIdx] : null;

      btnIcon.className    = langDef ? langDef.icon : 'fa-solid fa-earth-americas';
      btnLabel.textContent = langDef ? langDef.label : 'EN';
      widget.classList.add('fs-trl--active');
      statusMsg.textContent = langDef ? `${langDef.name} ✓` : '';

      if (tooltipSpan) {
        tooltipSpan.textContent = next ? `Next: ${next.name}` : 'Restore original';
      }
      btn.setAttribute('aria-label',
        next ? `Switch to ${next.name}` : 'Restore original language'
      );
      updateDots();
    } else {
      // idle
      btnIcon.className    = 'fa-solid fa-earth-americas';
      btnLabel.textContent = 'Translate';
      widget.classList.remove('fs-trl--active');
      statusMsg.textContent = '';
      if (tooltipSpan) tooltipSpan.textContent = 'EN · AR';
      btn.setAttribute('aria-label', 'Translate page');
      updateDots();
    }
  }

  /* ── INIT WIDGET ─────────────────────────────────────────── */
  function initWidget() {
    widget       = document.getElementById('fs-translate-widget');
    if (!widget) return;

    btn          = widget.querySelector('[data-translate-btn]');
    btnIcon      = widget.querySelector('[data-translate-icon]');
    btnLabel     = widget.querySelector('[data-translate-label]');
    progressRing = widget.querySelector('.fs-trl-progress');
    statusMsg    = widget.querySelector('[data-translate-status]');

    originalDir  = document.body.dir || document.documentElement.dir || '';

    btn.addEventListener('click', handleClick);

    // Session restore
    const saved = sessionStorage.getItem(STORAGE_KEY);
    if (saved) jumpToLang(saved);
  }

  document.addEventListener('DOMContentLoaded', initWidget);
})();
