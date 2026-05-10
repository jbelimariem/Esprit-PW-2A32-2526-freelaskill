/**
 * FreelaSkill — Air Signature v3 (Pinch Detection)
 * Détection par pincement pouce-index :
 *   - Pouce + Index proches  → stylo posé → trace
 *   - Pouce + Index écartés  → stylo levé → pause
 * Point de dessin = milieu entre pouce et index
 */

// ── État global ───────────────────────────────────────────────────────────
let camStream     = null;
let handsModel    = null;
let animFrameId   = null;
let isDrawing     = false;
let lastX = null, lastY = null;
let sigCanvas     = null;
let sigCtx        = null;
let targetInputId = null;
let strokeCount   = 0;
let smoothX = null, smoothY = null;

// ── Paramètres ────────────────────────────────────────────────────────────
const PINCH_THRESHOLD = 0.06;  // distance normalisée pouce-index pour activer le stylo
const SMOOTHING       = 0.4;   // lissage du mouvement (0=brut, 1=très lisse)
const LINE_WIDTH      = 3.5;   // épaisseur du trait
const MIN_MOVE        = 1.5;   // mouvement minimum en px pour tracer
const MAX_JUMP        = 60;    // saut maximum en px (anti-glitch)

// ── Ouvrir le modal ───────────────────────────────────────────────────────
async function openCameraSignature(inputId) {
    targetInputId = inputId;
    strokeCount   = 0;
    lastX = null; lastY = null;
    smoothX = null; smoothY = null;
    isDrawing = false;

    if (!document.getElementById('cam-sig-modal')) createCameraModal();

    const modal = document.getElementById('cam-sig-modal');
    modal.style.display = 'flex';

    sigCanvas = document.getElementById('cam-sig-canvas');
    sigCtx    = sigCanvas.getContext('2d');
    clearCamCanvas();

    try {
        camStream = await navigator.mediaDevices.getUserMedia({
            video: {
                width:      { ideal: 640 },
                height:     { ideal: 480 },
                frameRate:  { ideal: 60, min: 30 },
                facingMode: 'user'
            }
        });
        const video = document.getElementById('cam-sig-video');
        video.srcObject = camStream;
        await video.play();

        video.addEventListener('loadedmetadata', () => {
            sigCanvas.width  = video.videoWidth  || 640;
            sigCanvas.height = video.videoHeight || 480;
            clearCamCanvas();
        }, { once: true });

        await loadMediaPipe();

    } catch (err) {
        showCamError('Caméra inaccessible : ' + err.message);
    }
}

// ── Créer le modal ────────────────────────────────────────────────────────
function createCameraModal() {
    const modal = document.createElement('div');
    modal.id = 'cam-sig-modal';
    modal.style.cssText = `
        display:none;position:fixed;inset:0;
        background:rgba(0,0,0,0.88);z-index:9999;
        align-items:center;justify-content:center;
        backdrop-filter:blur(10px);
    `;
    modal.innerHTML = `
        <div style="background:#0f172a;border:1px solid rgba(37,99,235,0.35);border-radius:24px;padding:1.25rem;max-width:680px;width:96vw;max-height:96vh;overflow-y:auto;box-shadow:0 30px 80px rgba(0,0,0,0.7);display:flex;flex-direction:column;gap:0.75rem;">

            <!-- Header -->
            <div style="display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <div>
                    <div style="font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:700;color:#F1F5F9;">
                        ✌️ Air Signature — Pincement
                    </div>
                    <div style="font-size:0.72rem;color:#64748B;margin-top:0.15rem;">
                        <span style="color:#60A5FA;font-weight:600;">Pincez</span> pouce + index pour tracer &nbsp;·&nbsp;
                        <span style="color:#94A3B8;">Écartez</span> pour lever le stylo
                    </div>
                </div>
                <button onclick="closeCameraSignature()" style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#F87171;width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:0.95rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">✕</button>
            </div>

            <!-- Guide visuel compact -->
            <div style="display:flex;gap:0.5rem;flex-shrink:0;">
                <div style="flex:1;background:rgba(37,99,235,0.08);border:1px solid rgba(37,99,235,0.2);border-radius:8px;padding:0.45rem 0.6rem;display:flex;align-items:center;gap:0.4rem;">
                    <span style="font-size:1.1rem;">🤌</span>
                    <div>
                        <div style="font-size:0.7rem;font-weight:700;color:#60A5FA;">Pincez</div>
                        <div style="font-size:0.65rem;color:#64748B;">Stylo posé → trace</div>
                    </div>
                </div>
                <div style="flex:1;background:rgba(100,116,139,0.08);border:1px solid rgba(100,116,139,0.2);border-radius:8px;padding:0.45rem 0.6rem;display:flex;align-items:center;gap:0.4rem;">
                    <span style="font-size:1.1rem;">✋</span>
                    <div>
                        <div style="font-size:0.7rem;font-weight:700;color:#94A3B8;">Écartez</div>
                        <div style="font-size:0.65rem;color:#64748B;">Stylo levé → pause</div>
                    </div>
                </div>
                <div style="flex:1;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:8px;padding:0.45rem 0.6rem;display:flex;align-items:center;gap:0.4rem;">
                    <span style="font-size:1.1rem;">👆</span>
                    <div>
                        <div style="font-size:0.7rem;font-weight:700;color:#34D399;">Point</div>
                        <div style="font-size:0.65rem;color:#64748B;">Milieu pouce-index</div>
                    </div>
                </div>
            </div>

            <!-- Zone vidéo — hauteur réduite pour tout voir -->
            <div style="position:relative;border-radius:12px;overflow:hidden;background:#000;line-height:0;flex-shrink:0;">
                <video id="cam-sig-video" style="width:100%;max-height:300px;object-fit:cover;display:block;transform:scaleX(-1);" playsinline muted></video>
                <canvas id="cam-sig-canvas" style="position:absolute;inset:0;width:100%;height:100%;transform:scaleX(-1);"></canvas>

                <!-- Point de suivi -->
                <div id="cam-finger-dot" style="position:fixed;width:16px;height:16px;border-radius:50%;background:rgba(37,99,235,0.85);border:2px solid white;box-shadow:0 0 12px rgba(37,99,235,1);pointer-events:none;display:none;transform:translate(-50%,-50%);transition:background 0.08s,box-shadow 0.08s,width 0.08s,height 0.08s;z-index:10000;"></div>

                <!-- Barre pincement -->
                <div id="cam-pinch-bar-container" style="position:absolute;top:8px;right:8px;width:10px;height:70px;background:rgba(0,0,0,0.5);border-radius:5px;overflow:hidden;display:none;">
                    <div id="cam-pinch-bar" style="position:absolute;bottom:0;width:100%;background:#2563EB;border-radius:5px;transition:height 0.05s;height:0%;"></div>
                </div>

                <!-- Status -->
                <div id="cam-sig-status" style="position:absolute;top:8px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.65);color:white;padding:0.3rem 0.85rem;border-radius:999px;font-size:0.72rem;backdrop-filter:blur(4px);white-space:nowrap;">
                    <i class="fa-solid fa-spinner fa-spin"></i> Chargement IA...
                </div>

                <!-- Indicateur stylo -->
                <div style="position:absolute;bottom:8px;left:8px;background:rgba(0,0,0,0.65);padding:0.25rem 0.6rem;border-radius:999px;font-size:0.68rem;color:white;display:flex;align-items:center;gap:0.35rem;backdrop-filter:blur(4px);">
                    <span id="cam-pen-dot" style="width:8px;height:8px;border-radius:50%;background:#475569;display:inline-block;transition:all 0.1s;"></span>
                    <span id="cam-pen-text">En attente...</span>
                </div>

                <!-- Distance pincement -->
                <div id="cam-pinch-dist" style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,0.65);padding:0.25rem 0.6rem;border-radius:999px;font-size:0.68rem;color:#94A3B8;backdrop-filter:blur(4px);">
                    <span id="cam-pinch-value">—</span>
                </div>
            </div>

            <!-- Aperçu compact -->
            <div style="flex-shrink:0;">
                <div style="font-size:0.68rem;color:#64748B;text-transform:uppercase;letter-spacing:0.8px;font-weight:600;margin-bottom:0.35rem;">Aperçu signature</div>
                <div style="background:white;border-radius:8px;height:60px;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid rgba(255,255,255,0.08);">
                    <canvas id="cam-sig-preview" style="max-width:100%;max-height:100%;display:none;"></canvas>
                    <span id="cam-preview-placeholder" style="color:#94A3B8;font-size:0.75rem;font-style:italic;">Pincez pour commencer...</span>
                </div>
            </div>

            <!-- Boutons — toujours visibles -->
            <div style="display:flex;gap:0.5rem;flex-shrink:0;">
                <button onclick="clearCamCanvas()" style="flex:1;padding:0.6rem;background:rgba(239,68,68,0.1);color:#F87171;border:1px solid rgba(239,68,68,0.2);border-radius:999px;font-size:0.8rem;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:0.4rem;">
                    <i class="fa-solid fa-eraser"></i> Effacer
                </button>
                <button onclick="saveCameraSignature()" style="flex:2;padding:0.6rem;background:linear-gradient(135deg,#2563EB,#1D4ED8);color:white;border:none;border-radius:999px;font-size:0.82rem;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:0.4rem;box-shadow:0 4px 14px rgba(37,99,235,0.35);">
                    <i class="fa-solid fa-check"></i> Utiliser cette signature
                </button>
            </div>

            <div id="cam-sig-error" style="display:none;background:rgba(239,68,68,0.1);color:#F87171;border:1px solid rgba(239,68,68,0.2);border-radius:8px;padding:0.6rem;font-size:0.78rem;flex-shrink:0;"></div>
        </div>
    `;
    document.body.appendChild(modal);
    modal.addEventListener('click', e => { if (e.target === modal) closeCameraSignature(); });
}

// ── Charger MediaPipe ─────────────────────────────────────────────────────
async function loadMediaPipe() {
    setStatus('<i class="fa-solid fa-spinner fa-spin"></i> Chargement IA...');

    if (!window.Hands) {
        await loadScript('https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js');
    }

    handsModel = new Hands({
        locateFile: f => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${f}`
    });

    handsModel.setOptions({
        maxNumHands:             1,
        modelComplexity:         0,    // LITE = plus rapide
        minDetectionConfidence:  0.65,
        minTrackingConfidence:   0.55,
    });

    handsModel.onResults(onHandResults);
    setStatus('🤌 Prêt — pincez pouce + index pour signer');

    // Afficher la barre de pincement
    const bar = document.getElementById('cam-pinch-bar-container');
    if (bar) bar.style.display = 'block';

    startDetectionLoop();
}

// ── Boucle de détection ───────────────────────────────────────────────────
function startDetectionLoop() {
    const video = document.getElementById('cam-sig-video');
    if (!video || !handsModel) return;

    async function loop() {
        const modal = document.getElementById('cam-sig-modal');
        if (!modal || modal.style.display === 'none') return;
        if (video.readyState >= 2) {
            try { await handsModel.send({ image: video }); } catch(e) {}
        }
        animFrameId = requestAnimationFrame(loop);
    }
    animFrameId = requestAnimationFrame(loop);
}

// ── Traitement des résultats ──────────────────────────────────────────────
function onHandResults(results) {
    if (!sigCtx || !sigCanvas) return;

    const fingerDot  = document.getElementById('cam-finger-dot');
    const pinchBar   = document.getElementById('cam-pinch-bar');
    const pinchValue = document.getElementById('cam-pinch-value');

    if (!results.multiHandLandmarks || results.multiHandLandmarks.length === 0) {
        isDrawing = false;
        lastX = null; lastY = null;
        smoothX = null; smoothY = null;
        setPenIndicator(false, 'Aucune main détectée');
        if (fingerDot)  fingerDot.style.display = 'none';
        if (pinchBar)   pinchBar.style.height = '0%';
        if (pinchValue) pinchValue.textContent = '—';
        return;
    }

    const lm = results.multiHandLandmarks[0];

    // ── Points clés ──
    const thumbTip = lm[4];   // bout du pouce
    const indexTip = lm[8];   // bout de l'index

    // ── Distance normalisée pouce-index ──
    const dx   = thumbTip.x - indexTip.x;
    const dy   = thumbTip.y - indexTip.y;
    const dist = Math.sqrt(dx * dx + dy * dy); // 0 = pincé, ~0.3 = ouvert

    // ── Point de dessin = milieu entre pouce et index ──
    const midX = (thumbTip.x + indexTip.x) / 2;
    const midY = (thumbTip.y + indexTip.y) / 2;

    // Coordonnées canvas (miroir horizontal)
    const rawX = (1 - midX) * sigCanvas.width;
    const rawY = midY * sigCanvas.height;

    // ── Lissage exponentiel ──
    if (smoothX === null) { smoothX = rawX; smoothY = rawY; }
    smoothX = smoothX * SMOOTHING + rawX * (1 - SMOOTHING);
    smoothY = smoothY * SMOOTHING + rawY * (1 - SMOOTHING);

    const x = smoothX;
    const y = smoothY;

    // ── Afficher la distance de pincement ──
    const pinchPct = Math.max(0, Math.min(100, (1 - dist / 0.25) * 100));
    if (pinchBar)   pinchBar.style.height = pinchPct + '%';
    if (pinchValue) pinchValue.textContent = (dist * 100).toFixed(1) + '%';

    // ── Afficher le point de suivi ──
    const videoEl = document.getElementById('cam-sig-video');
    if (fingerDot && videoEl) {
        const rect = videoEl.getBoundingClientRect();
        // Position miroir
        const dotX = rect.left + (1 - midX) * rect.width;
        const dotY = rect.top  + midY * rect.height;
        fingerDot.style.display = 'block';
        fingerDot.style.left    = dotX + 'px';
        fingerDot.style.top     = dotY + 'px';
    }

    // ── Logique stylo : pincé = posé, écarté = levé ──
    const penDown = dist < PINCH_THRESHOLD;

    if (fingerDot) {
        fingerDot.style.background  = penDown ? 'rgba(37,99,235,0.95)' : 'rgba(100,116,139,0.7)';
        fingerDot.style.boxShadow   = penDown ? '0 0 14px rgba(37,99,235,1), 0 0 4px white' : '0 0 6px rgba(100,116,139,0.5)';
        fingerDot.style.width       = penDown ? '14px' : '18px';
        fingerDot.style.height      = penDown ? '14px' : '18px';
    }

    if (penDown) {
        setPenIndicator(true, `Stylo posé (${(dist * 100).toFixed(1)}%)`);

        if (lastX !== null && lastY !== null) {
            const moveDist = Math.hypot(x - lastX, y - lastY);

            if (moveDist > MIN_MOVE && moveDist < MAX_JUMP) {
                // Tracer avec courbe de Bézier quadratique pour fluidité
                sigCtx.beginPath();
                const cpX = (lastX + x) / 2;
                const cpY = (lastY + y) / 2;
                sigCtx.moveTo(lastX, lastY);
                sigCtx.quadraticCurveTo(lastX, lastY, cpX, cpY);
                sigCtx.strokeStyle = '#1a1a2e';
                sigCtx.lineWidth   = LINE_WIDTH;
                sigCtx.lineCap     = 'round';
                sigCtx.lineJoin    = 'round';
                sigCtx.stroke();

                strokeCount++;
                if (strokeCount % 4 === 0) updatePreview();
            }
        }

        isDrawing = true;
        lastX = x;
        lastY = y;

    } else {
        // Stylo levé
        if (isDrawing) {
            updatePreview();
            isDrawing = false;
        }
        lastX = null;
        lastY = null;
        setPenIndicator(false, `Stylo levé (${(dist * 100).toFixed(1)}%)`);
    }
}

// ── Aperçu ────────────────────────────────────────────────────────────────
function updatePreview() {
    const preview     = document.getElementById('cam-sig-preview');
    const placeholder = document.getElementById('cam-preview-placeholder');
    if (!preview || !sigCanvas) return;
    const pCtx = preview.getContext('2d');
    preview.width  = sigCanvas.width;
    preview.height = sigCanvas.height;
    pCtx.fillStyle = 'white';
    pCtx.fillRect(0, 0, preview.width, preview.height);
    pCtx.drawImage(sigCanvas, 0, 0);
    if (placeholder) placeholder.style.display = 'none';
    preview.style.display = 'block';
}

// ── Effacer ───────────────────────────────────────────────────────────────
function clearCamCanvas() {
    sigCanvas = document.getElementById('cam-sig-canvas');
    if (!sigCanvas) return;
    sigCtx = sigCanvas.getContext('2d');
    sigCtx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
    strokeCount = 0;
    lastX = null; lastY = null;
    smoothX = null; smoothY = null;
    isDrawing = false;
    const preview     = document.getElementById('cam-sig-preview');
    const placeholder = document.getElementById('cam-preview-placeholder');
    if (preview)     preview.style.display = 'none';
    if (placeholder) placeholder.style.display = 'block';
}

// ── Sauvegarder ───────────────────────────────────────────────────────────
function saveCameraSignature() {
    if (!sigCanvas) return;
    if (strokeCount < 3) {
        showCamError('Signature trop courte. Pincez pouce + index et bougez pour signer.');
        return;
    }

    const final = document.createElement('canvas');
    final.width  = sigCanvas.width;
    final.height = sigCanvas.height;
    const fCtx   = final.getContext('2d');
    fCtx.fillStyle = 'white';
    fCtx.fillRect(0, 0, final.width, final.height);
    fCtx.drawImage(sigCanvas, 0, 0);
    const dataUrl = final.toDataURL('image/png');

    const input = document.getElementById(targetInputId);
    if (input) {
        input.value = dataUrl;
        const container = input.closest('.form-group') || input.parentElement;
        if (container) {
            container.querySelector('.cam-sig-result')?.remove();
            const prev = document.createElement('div');
            prev.className = 'cam-sig-result';
            prev.style.cssText = 'margin-top:0.5rem;background:white;border-radius:10px;padding:0.5rem;border:2px solid rgba(37,99,235,0.3);';
            prev.innerHTML = `
                <div style="font-size:0.7rem;color:#2563EB;font-weight:600;margin-bottom:0.3rem;">
                    <i class="fa-solid fa-camera"></i> Signature Air capturée ✓
                </div>
                <img src="${dataUrl}" style="max-width:100%;max-height:90px;display:block;">
            `;
            container.appendChild(prev);
        }
    }

    closeCameraSignature();
    if (typeof showApiToast === 'function') showApiToast('Signature capturée ✓', 'success');
}

// ── Fermer ────────────────────────────────────────────────────────────────
function closeCameraSignature() {
    if (animFrameId) { cancelAnimationFrame(animFrameId); animFrameId = null; }
    if (camStream)   { camStream.getTracks().forEach(t => t.stop()); camStream = null; }
    if (handsModel)  { handsModel.close(); handsModel = null; }
    const modal = document.getElementById('cam-sig-modal');
    if (modal) modal.style.display = 'none';
    const dot = document.getElementById('cam-finger-dot');
    if (dot) dot.style.display = 'none';
    isDrawing = false; lastX = null; lastY = null;
    smoothX = null; smoothY = null;
}

// ── Helpers ───────────────────────────────────────────────────────────────
function setStatus(html) {
    const el = document.getElementById('cam-sig-status');
    if (el) el.innerHTML = html;
}

function setPenIndicator(down, text) {
    const dot = document.getElementById('cam-pen-dot');
    const txt = document.getElementById('cam-pen-text');
    if (!dot || !txt) return;
    dot.style.background = down ? '#2563EB' : '#475569';
    dot.style.boxShadow  = down ? '0 0 8px rgba(37,99,235,0.9)' : 'none';
    txt.textContent      = text || (down ? 'Stylo posé' : 'Stylo levé');
    txt.style.color      = down ? '#60A5FA' : '#94A3B8';
}

function showCamError(msg) {
    const el = document.getElementById('cam-sig-error');
    if (el) { el.textContent = msg; el.style.display = 'block'; }
    setTimeout(() => { if (el) el.style.display = 'none'; }, 4000);
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) { resolve(); return; }
        const s = document.createElement('script');
        s.src = src; s.onload = resolve; s.onerror = reject;
        document.head.appendChild(s);
    });
}
