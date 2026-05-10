<?php
// views/frontoffice/freelancer_home.view.php — Interface Freelancer
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Freelancer — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/theme-light.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
</head>
<body class="page-anim">

<nav>
    <div class="logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </div>
    <ul class="nav-links">
        <li><span style="color:var(--text-muted);cursor:default;">Accueil</span></li>
        <li><a href="home.php">Marketplace</a></li>
        <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'client'): ?>
            <li><a href="missions.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'missions.php') ? 'active' : '' ?>">Missions</a></li>
        <?php else: ?>
            <li><a href="freelancer_home.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'freelancer_home.php') ? 'active' : '' ?>">Freelancers</a></li>
        <?php endif; ?>
        <li><a href="profile.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : '' ?>">Mon Profil</a></li>
    </ul>
    <div class="nav-right">
        <button type="button" class="theme-toggle" data-theme-toggle>
            <i class="fa-solid fa-sun" data-theme-icon></i>
            <span data-theme-label>Jour</span>
        </button>
        <div class="nav-avatar">FR</div>
    </div>
</nav>



<div class="marketplace-layout">
    
        <aside class="mkt-sidebar">
        <!-- Card 1 : Profil marketplace -->
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-store"></i></div>
                <div class="mkt-profile-name">Espace Freelancer</div>
                <div class="mkt-profile-sub">FreelaSkill Tunisia</div>
            </div>
            <div class="mkt-profile-stats">
                <div class="mkt-stat">
                    <div class="mkt-stat-val">12</div>
                    <div class="mkt-stat-label">MISSIONS</div>
                </div>
                <div class="mkt-stat">
                    <div class="mkt-stat-val">5</div>
                    <div class="mkt-stat-label">POSTULÉES</div>
                </div>
            </div>
        </div>

        <!-- Card 2 : Navigation + Filtres -->
        <div class="mkt-sidebar-card">
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="freelancer_home.php" class="nav-item active">
                    <i class="fa-solid fa-briefcase"></i> Missions
                </a>
                <a href="freelancer_applications.php" class="nav-item ">
                    <i class="fa-solid fa-paper-plane"></i> Candidatures
                </a>
                <a href="#" id="export-pdf" class="nav-item">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
                <a href="home.php" class="nav-item danger">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </aside>

    <div class="mkt-main">
        <!-- TOPBAR INSIDE MAIN -->
        

<section class="hero-banner">
    <div class="hero-glow"></div>
    <div class="hero-content" style="max-width: 800px; margin: 0 auto; text-align: center;">
        <div class="hero-tag" style="margin: 0 auto 1.5rem;"><i class="fa-solid fa-rocket"></i> Espace Freelancer</div>
        <h1 class="hero-title">Trouvez votre <span>prochaine mission</span></h1>
        <p class="hero-sub" style="margin-left: auto; margin-right: auto;">Explorez les meilleures opportunités publiées par nos clients et postulez en un clic.</p>
        
        <form class="search-container" method="GET" action="freelancer_home.php" style="margin: 2rem auto 0; max-width: 600px;">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" placeholder="Rechercher par titre, compétences..." value="<?= htmlspecialchars($q) ?>">
            </div>
            <button type="submit" class="btn-search">Rechercher</button>
        </form>
    </div>
</section>


    <!-- HORIZONTAL FILTERS INSTEAD OF SIDEBAR FOR CLEANER LOOK -->
    <div style="background:rgba(255,255,255,0.02); border:1px solid var(--border); border-radius:var(--radius-md); padding:1.5rem; margin-bottom:2.5rem; display:flex; align-items:center; justify-content:space-between; gap:2rem;">
        <form method="GET" action="freelancer_home.php" style="display:flex; align-items:center; gap:1.5rem; flex:1;" onsubmit="return validateBudgetSubmit(event)">
            <?php if(!empty($q)): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:0.85rem; color:var(--text-muted); font-weight:600;">Budget:</span>
                <input type="number" id="min_price" name="min_price" placeholder="Min" value="<?= $min > 0 ? $min : '' ?>" style="background:rgba(255,255,255,0.05); border:1px solid var(--border); color:white; padding:8px 12px; border-radius:8px; width:100px; font-size:0.9rem;">
                <span style="color:var(--text-muted);">-</span>
                <input type="number" id="max_price" name="max_price" placeholder="Max" value="<?= $max < 999999 ? $max : '' ?>" style="background:rgba(255,255,255,0.05); border:1px solid var(--border); color:white; padding:8px 12px; border-radius:8px; width:100px; font-size:0.9rem;">
                
                <script>
                function validateBudgetSubmit(e) {
                    const minInput = document.getElementById('min_price');
                    const maxInput = document.getElementById('max_price');
                    minInput.style.borderColor = 'var(--border)';
                    maxInput.style.borderColor = 'var(--border)';
                    
                    let isValid = true;
                    if (minInput.value !== '' && maxInput.value === '') {
                        maxInput.style.borderColor = '#ef4444';
                        isValid = false;
                    } else if (maxInput.value !== '' && minInput.value === '') {
                        minInput.style.borderColor = '#ef4444';
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                        return false;
                    }
                    return true;
                }
                </script>
            </div>
            <button type="submit" class="cart-btn" style="padding:8px 20px;">Filtrer</button>
        </form>
        <div class="result-count" style="font-size:0.9rem; color:var(--text-muted);"><strong><?= count($offres) ?></strong> missions disponibles</div>
    </div>

    <!-- MISSIONS GRID -->
    <div class="job-grid">
        <?php if (empty($offres)): ?>
            <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem;">
                <i class="fa-solid fa-search" style="font-size: 3rem; margin-bottom: 1.5rem; color: var(--border);"></i>
                <h3 style="color:white; margin-bottom:0.5rem;">Aucune mission trouvée</h3>
                <p>Essayez de modifier vos critères de recherche ou vos filtres.</p>
            </div>
        <?php else: ?>
            <?php foreach ($offres as $o): 
                $competencesList = array_slice(explode(',', $o->getCompetences()), 0, 3);
            ?>
            <div class="job-card">
                <div class="job-card-header">
                    <div class="job-icon">💼</div>
                    <div class="job-badge" style="background:rgba(59,130,246,0.1); color:var(--tech-blue); padding:4px 10px; border-radius:var(--radius-full); font-size:0.65rem; font-weight:700; text-transform:uppercase;">Job Offer</div>
                </div>
                <div class="job-card-body">
                    <div class="job-titre"><?= htmlspecialchars($o->getTitre()) ?></div>
                    <div class="job-competences">
                        <?php foreach ($competencesList as $comp): ?>
                        <span style="display:inline-block; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.2); color:var(--tech-blue); padding:2px 8px; border-radius:var(--radius-full); font-size:.7rem; margin-right:4px;"><?= htmlspecialchars(trim($comp)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="job-meta">
                        <span><i class="fa-solid fa-coins"></i> <span class="job-budget"><?= number_format($o->getBudget(), 0, ',', ' ') ?></span> <small>DT</small></span>
                        <span><i class="fa-solid fa-calendar-days"></i> <?= date('d/m/Y', strtotime($o->getDateCreation())) ?></span>
                    </div>
                </div>
                <div class="job-actions">
                    <a href="freelancer_detail.php?id=<?= $o->getId() ?>" class="btn-action btn-view" style="width:100%; flex:none;"><i class="fa-solid fa-eye"></i> Voir Détails & Postuler</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>


<script>
    // PDF Export Logic
    document.getElementById('export-pdf').addEventListener('click', async function(e) {
        e.preventDefault();
        const btn = this;
        const originalContent = btn.innerHTML;
        
        try {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Génération...';
            btn.style.pointerEvents = 'none';

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            doc.setFontSize(22);
            doc.setTextColor(37, 99, 235);
            doc.text("Liste des Missions — FreelaSkill", 14, 20);
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text("Généré le : " + new Date().toLocaleDateString(), 14, 30);

            const data = <?= json_encode(array_map(fn($o) => [$o->getTitre(), $o->getBudget() . " DT", $o->getDelai(), $o->getCompetences()], $offres)) ?>;
            
            doc.autoTable({
                startY: 40,
                head: [['Titre', 'Budget', 'Délai', 'Compétences']],
                body: data,
                theme: 'striped',
                headStyles: { fillStyle: [37, 99, 235] }
            });
            
            const pdfBlob = doc.output('blob');
            const formData = new FormData();
            formData.append('pdf', pdfBlob, 'missions_freelancer.pdf');

            btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up fa-spin"></i> Cloud upload...';
            
            const response = await fetch('../../api/upload_pdf.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.ok) {
                window.open(result.url, '_blank');
                saveAs(pdfBlob, 'missions_freelancer.pdf');
            } else {
                alert("Erreur Cloudinary : " + result.error);
            }
        } catch (err) {
            console.error("Export PDF failed:", err);
            alert("Une erreur est survenue lors de l'export : " + err.message);
        } finally {
            btn.innerHTML = originalContent;
            btn.style.pointerEvents = 'all';
        }
    });
</script>

    </div>
</div>
<!-- ═══════════════════════════════════
     AI ASSISTANT WIDGET
═══════════════════════════════════ -->
<div class="ai-fab" id="ai-fab" title="Assistant IA">
    <i class="fa-solid fa-robot"></i>
</div>

<div class="ai-chat-panel" id="ai-chat-panel">
    <div class="ai-chat-header">
        <div class="ai-avatar"><i class="fa-solid fa-robot"></i></div>
        <div class="ai-header-info">
            <div class="ai-title">Assistant FreelaSkill</div>
            <div class="ai-status">En ligne</div>
        </div>
        <button class="ai-close" id="ai-close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="ai-chat-body" id="ai-chat-body">
        <div class="ai-msg bot">
            <div class="ai-bubble">
                Bonjour ! Je suis l'assistant FreelaSkill. Je peux vous aider à trouver les meilleures missions. Quelles sont vos compétences principales ? (ex: PHP, React, Design)
            </div>
        </div>
    </div>
    <div class="ai-chat-input-area">
        <form id="ai-form" style="display:flex; width:100%; gap:8px; align-items: center;">
            <button type="button" id="ai-mic" class="ai-mic-btn" title="Dicter votre message"><i class="fa-solid fa-microphone"></i></button>
            <button type="button" id="ai-stop-voice" class="ai-mic-btn" title="Arrêter la voix" style="display:none;"><i class="fa-solid fa-volume-xmark"></i></button>
            <input type="text" id="ai-input" placeholder="Tapez votre message..." autocomplete="off">
            <button type="submit" id="ai-send"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>
</div>

<style>
/* AI Fab Button */
.ai-fab { position:fixed; bottom:2rem; right:2rem; width:60px; height:60px; border-radius:50%; background:linear-gradient(135deg, #3b82f6, #8b5cf6); color:white; display:flex; align-items:center; justify-content:center; font-size:1.8rem; cursor:pointer; box-shadow:0 10px 25px rgba(59,130,246,0.5); z-index:9999; transition:transform 0.3s, box-shadow 0.3s; }
.ai-fab:hover { transform:scale(1.1) translateY(-5px); box-shadow:0 15px 35px rgba(59,130,246,0.6); }

/* Chat Panel */
.ai-chat-panel { position:fixed; bottom:6rem; right:2rem; width:380px; height:600px; max-height:80vh; background:linear-gradient(160deg, #0f172a 0%, #1e293b 100%); border:1px solid rgba(255,255,255,0.1); border-radius:24px; display:flex; flex-direction:column; box-shadow:0 30px 60px rgba(0,0,0,0.8); z-index:9998; opacity:0; pointer-events:none; transform:translateY(20px) scale(0.95); transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1); overflow:hidden; }
.ai-chat-panel.active { opacity:1; pointer-events:auto; transform:none; }

.ai-chat-header { display:flex; align-items:center; padding:1.2rem 1.5rem; background:rgba(255,255,255,0.03); border-bottom:1px solid rgba(255,255,255,0.08); }
.ai-avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg, #3b82f6, #8b5cf6); display:flex; align-items:center; justify-content:center; font-size:1.2rem; color:white; margin-right:12px; }
.ai-header-info { flex:1; }
.ai-title { font-weight:700; font-size:1rem; color:white; }
.ai-status { font-size:0.75rem; color:#10b981; display:flex; align-items:center; gap:5px; }
.ai-status::before { content:''; display:block; width:6px; height:6px; background:#10b981; border-radius:50%; box-shadow:0 0 8px #10b981; }
.ai-close { background:none; border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer; transition:color 0.2s; }
.ai-close:hover { color:#ef4444; }

.ai-chat-body { flex:1; padding:1.5rem; overflow-y:auto; display:flex; flex-direction:column; gap:1rem; }
.ai-chat-body::-webkit-scrollbar { width:6px; }
.ai-chat-body::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:10px; }

.ai-msg { display:flex; max-width:85%; }
.ai-msg.bot { align-self:flex-start; }
.ai-msg.user { align-self:flex-end; }

.ai-bubble { padding:1rem 1.2rem; border-radius:18px; font-size:0.9rem; line-height:1.4; }
.ai-msg.bot .ai-bubble { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#e2e8f0; border-bottom-left-radius:4px; position:relative; padding-right: 2.5rem; }
.ai-msg.user .ai-bubble { background:linear-gradient(135deg, #3b82f6, #2563eb); color:white; border-bottom-right-radius:4px; }
.ai-replay-btn { position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:#94a3b8; cursor:pointer; font-size:0.9rem; padding:5px; transition:color 0.2s; display:flex; align-items:center; justify-content:center; }
.ai-replay-btn:hover { color:#3b82f6; }

.ai-chat-input-area { padding:1rem 1.5rem; border-top:1px solid rgba(255,255,255,0.08); background:rgba(0,0,0,0.2); }
#ai-input { flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:20px; padding:10px 15px; color:white; outline:none; font-family:inherit; transition:border-color 0.2s; }
#ai-input:focus { border-color:#3b82f6; }
#ai-send { background:#3b82f6; border:none; width:40px; height:40px; border-radius:50%; color:white; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s; }
#ai-send:hover { background:#2563eb; }
#ai-mic { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); width:40px; height:40px; border-radius:50%; color:white; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; }
#ai-mic:hover { background:rgba(255,255,255,0.1); color:#3b82f6; }
#ai-mic.listening { background:#ef4444; border-color:#ef4444; animation:pulse-red 1.5s infinite; }
@keyframes pulse-red { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

/* Typing indicator */
.typing-dots { display:flex; gap:4px; padding:5px 0; }
.typing-dots span { width:6px; height:6px; background:var(--text-muted); border-radius:50%; animation:typing 1.4s infinite ease-in-out; }
.typing-dots span:nth-child(1) { animation-delay:0s; }
.typing-dots span:nth-child(2) { animation-delay:0.2s; }
.typing-dots span:nth-child(3) { animation-delay:0.4s; }
@keyframes typing { 0%, 100% { transform:scale(1); opacity:0.5; } 50% { transform:scale(1.2); opacity:1; background:white; } }

/* Job Card in Chat */
.ai-job-card { background:rgba(0,0,0,0.3); border:1px solid rgba(59,130,246,0.3); border-radius:12px; padding:1rem; margin-top:10px; width:100%; box-sizing:border-box; }
.ai-job-title { font-weight:700; color:white; font-size:0.95rem; margin-bottom:5px; }
.ai-job-meta { font-size:0.8rem; color:var(--text-muted); margin-bottom:8px; display:flex; gap:10px; }
.ai-job-score { background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); padding:2px 8px; border-radius:10px; font-size:0.75rem; font-weight:600; display:inline-block; margin-bottom:8px;}
.ai-job-btn { display:block; text-align:center; background:rgba(59,130,246,0.1); color:#60a5fa; border:1px solid rgba(59,130,246,0.3); border-radius:8px; padding:8px; text-decoration:none; font-size:0.85rem; font-weight:600; transition:all 0.2s; }
.ai-job-btn:hover { background:rgba(59,130,246,0.2); color:white; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fab = document.getElementById('ai-fab');
    const panel = document.getElementById('ai-chat-panel');
    const closeBtn = document.getElementById('ai-close');
    const form = document.getElementById('ai-form');
    const input = document.getElementById('ai-input');
    const body = document.getElementById('ai-chat-body');
    const micBtn = document.getElementById('ai-mic');
    const stopVoiceBtn = document.getElementById('ai-stop-voice');

    // --- SYNTHÈSE VOCALE ---
    window.speak = function(text) {
        if (!window.speechSynthesis) return;
        window.speechSynthesis.cancel();
        const cleanText = text.replace(/\*\*/g, '').replace(/\[.*?\]\(.*?\)/g, '');
        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.lang = 'fr-FR';
        
        utterance.onstart = () => { stopVoiceBtn.style.display = 'flex'; };
        utterance.onend = () => { stopVoiceBtn.style.display = 'none'; };
        utterance.onerror = () => { stopVoiceBtn.style.display = 'none'; };

        window.speechSynthesis.speak(utterance);
    }

    stopVoiceBtn.addEventListener('click', () => {
        window.speechSynthesis.cancel();
        stopVoiceBtn.style.display = 'none';
    });

    // --- RECONNAISSANCE VOCALE ---
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecognition) {
        const recognition = new SpeechRecognition();
        recognition.lang = 'fr-FR';
        recognition.onstart = () => { micBtn.classList.add('listening'); input.placeholder = "Je vous écoute..."; };
        recognition.onend = () => { micBtn.classList.remove('listening'); input.placeholder = "Tapez votre message..."; };
        recognition.onresult = (event) => { input.value = event.results[0][0].transcript; form.dispatchEvent(new Event('submit')); };
        micBtn.addEventListener('click', () => { if (micBtn.classList.contains('listening')) recognition.stop(); else recognition.start(); });
    } else { micBtn.style.display = 'none'; }


    let chatHistory = [];

    fab.addEventListener('click', () => {
        panel.classList.toggle('active');
        if(panel.classList.contains('active')) input.focus();
    });

    closeBtn.addEventListener('click', () => panel.classList.remove('active'));

    // Fonction basique pour transformer le Markdown (gras, liens, listes) en HTML
    function parseMarkdown(text) {
        let html = text;
        // Echapper le HTML basique d'abord pour éviter les failles XSS (sauf les sauts de ligne)
        html = html.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        // Gras : **texte**
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Liens : [texte](url)
        html = html.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" class="ai-job-btn" style="display:inline-block; margin-top:5px; margin-bottom:5px;">$1</a>');
        // Listes à puces simples (lignes commençant par * ou -)
        html = html.replace(/^[\*\-]\s+(.*)$/gm, '<li style="margin-left: 20px;">$1</li>');
        // Retours à la ligne
        html = html.replace(/\n/g, '<br>');
        return html;
    }

    function appendMsg(text, type, isHtml = false) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-msg ${type}`;
        const replayHtml = type === 'bot' ? `<button class="ai-replay-btn" onclick="speak(this.parentElement.innerText)" title="Réécouter"><i class="fa-solid fa-volume-high"></i></button>` : '';
        msgDiv.innerHTML = `<div class="ai-bubble">${isHtml ? text : text}${replayHtml}</div>`;
        body.appendChild(msgDiv);
        body.scrollTop = body.scrollHeight;
    }

    function appendTyping() {
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-msg bot typing-indicator`;
        msgDiv.innerHTML = `<div class="ai-bubble"><div class="typing-dots"><span></span><span></span><span></span></div></div>`;
        body.appendChild(msgDiv);
        body.scrollTop = body.scrollHeight;
        return msgDiv;
    }

    async function sendToAi(userMessage) {
        const typing = appendTyping();
        try {
            const res = await fetch('ai_assistant.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: userMessage, history: chatHistory })
            });
            const data = await res.json();
            typing.remove();

            if (data.status === 'success') {
                const replyHtml = parseMarkdown(data.reply);
                appendMsg(replyHtml, 'bot', true);
                speak(data.reply); // L'IA parle !
                
                // Ajouter à l'historique pour que l'IA se souvienne de la conversation
                chatHistory.push({ role: 'user', text: userMessage });
                chatHistory.push({ role: 'bot', text: data.reply });
            } else {
                appendMsg("Désolé, une erreur technique est survenue.", 'bot');
            }
        } catch (err) {
            typing.remove();
            appendMsg("Oups, impossible de joindre l'IA.", 'bot');
        }
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const val = input.value.trim();
        if (!val) return;

        appendMsg(val, 'user');
        input.value = '';
        
        sendToAi(val);
    });
});
</script>
<script src="../assets/theme.js"></script>
</body>
</html>
