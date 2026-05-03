<?php
// views/frontoffice/add_job.view.php — Template: Ajouter une offre
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une offre — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; margin-bottom: .5rem; color: #94A3B8; font-size: .9rem; font-weight: 500; }
        .form-label span { color: var(--tunisian-red); margin-left: 2px; }
        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.85rem 1.1rem;
            color: white;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.95rem;
            outline: none;
            transition: var(--transition);
        }
        .form-input:focus { background: rgba(59,130,246,0.05); border-color: var(--tech-blue); box-shadow: 0 0 0 4px rgba(59,130,246,0.12); }
        .form-input.error { border-color: rgba(239,68,68,0.5); background: rgba(239,68,68,0.03); }
        .error-msg { color: var(--tunisian-red); font-size: .8rem; margin-top: .4rem; display: flex; align-items: center; gap: .3rem; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        textarea.form-input { resize: vertical; min-height: 140px; }
        select.form-input { cursor: pointer; }
        select.form-input option { background: #0f172a; }
    </style>
</head>
<body class="page-anim">

<nav style="position: sticky; top: 0; width: 100%; z-index: 100; padding: 0 2rem;">
            <div class="logo"><i class="fa-solid fa-shapes"></i> Freela<span>Skill</span></div>
            <ul class="nav-links">
                <li><a href="home.php">Accueil</a></li>
                <li><a href="home.php" class="active">Client</a></li>
                <li><a href="freelancer_home.php">Freelancers</a></li>
            </ul>
            <div class="nav-right">
                <div class="nav-avatar">CL</div>
            </div>
        </nav>


<div class="marketplace-layout">

        <aside class="mkt-sidebar">
        <!-- Card 1 : Profil marketplace -->
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-building"></i></div>
                <div class="mkt-profile-name">Espace Client</div>
                <div class="mkt-profile-sub">FreelaSkill Tunisia</div>
            </div>
            <div class="mkt-profile-stats">
                <div class="mkt-stat">
                    <div class="mkt-stat-val"><?= config::getConnexion()->query("SELECT COUNT(*) FROM offres_emploi")->fetchColumn() ?></div>
                    <div class="mkt-stat-label">OFFRES</div>
                </div>
                <div class="mkt-stat">
                    <div class="mkt-stat-val"><?= config::getConnexion()->query("SELECT COUNT(*) FROM job_applications")->fetchColumn() ?></div>
                    <div class="mkt-stat-label">CANDIDATS</div>
                </div>
            </div>
        </div>

        <!-- Card 2 : Navigation + Filtres -->
        <div class="mkt-sidebar-card">
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="home.php" class="nav-item ">
                    <i class="fa-solid fa-list-ul"></i> Mes Offres
                </a>
                <a href="add_job.php" class="nav-item active">
                    <i class="fa-solid fa-plus-circle"></i> Nouveau Offre
                </a>
                <a href="client_freelancers.php" class="nav-item ">
                    <i class="fa-solid fa-users"></i> Freelancers
                </a>
                <a href="#" id="download-pdf-home" class="nav-item">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>
    </aside>


    <div class="mkt-main">
        <!-- TOPBAR INSIDE MAIN -->
        

<section class="hero-banner">
    <div class="hero-glow"></div>
    <div class="hero-content">
        <div class="hero-tag"><i class="fa-solid fa-plus"></i> Nouvelle offre</div>
        <h1 class="hero-title">Publiez votre offre <span>en quelques minutes</span></h1>
    </div>
</section>

<div class="page-body" style="padding: 2rem 1rem; display: block;">
    <div style="max-width:1200px; margin:0 auto; width:100%;">

        <div class="product-card" style="opacity:1; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
            <div class="card-body" style="padding:3rem;">
                <form id="add-form" action="add_job.php" method="POST" novalidate>
                    <div class="form-group">
                        <label class="form-label" for="titre">Titre de l'offre <span>*</span></label>
                        <?php if (isset($errors['titre'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['titre'] ?></div><?php endif; ?>
                        <input id="titre" name="titre" type="text" class="form-input <?= isset($errors['titre']) ? 'error' : '' ?>" placeholder="Ex. Développeur React.js" value="<?= htmlspecialchars($data['titre'] ?? '') ?>">
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="budget">Budget (DT) <span>*</span></label>
                            <?php if (isset($errors['budget'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['budget'] ?></div><?php endif; ?>
                            <input id="budget" name="budget" type="text" class="form-input <?= isset($errors['budget']) ? 'error' : '' ?>" placeholder="Ex. 1500" value="<?= htmlspecialchars($data['budget'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="delai">Délai <span>*</span></label>
                            <?php if (isset($errors['delai'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['delai'] ?></div><?php endif; ?>
                            <input id="delai" name="delai" type="text" class="form-input <?= isset($errors['delai']) ? 'error' : '' ?>" placeholder="Ex. 15 jours" value="<?= htmlspecialchars($data['delai'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="competences">Compétences <span>*</span></label>
                        <?php if (isset($errors['competences'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['competences'] ?></div><?php endif; ?>
                        <input id="competences" name="competences" type="text" class="form-input <?= isset($errors['competences']) ? 'error' : '' ?>" placeholder="Ex. React.js, PHP" value="<?= htmlspecialchars($data['competences'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Description <span>*</span></label>
                        <?php if (isset($errors['description'])): ?><div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['description'] ?></div><?php endif; ?>
                        <textarea id="description" name="description" rows="8" class="form-input <?= isset($errors['description']) ? 'error' : '' ?>"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                    </div>

                    <div style="display:flex; gap:1rem; border-top:1px solid var(--border); padding-top:2rem; margin-top:1rem;">
                        <button type="submit" class="btn-cart" style="width:auto; padding:1rem 3rem;">Publier l'offre</button>
                        <a href="home.php" class="btn-cart" style="width:auto; padding:1rem 2rem; background:rgba(255,255,255,0.05); color:white; text-decoration:none; display:flex; align-items:center; justify-content:center; border:1px solid var(--border);">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
</div>
<!-- ═══════════════════════════════════
     AI ASSISTANT WIDGET FOR JOB CREATION
═══════════════════════════════════ -->
<div class="ai-fab" id="ai-fab" title="Assistant de création d'offre">
    <i class="fa-solid fa-wand-magic-sparkles"></i>
</div>

<div class="ai-chat-panel" id="ai-chat-panel">
    <div class="ai-chat-header">
        <div class="ai-avatar"><i class="fa-solid fa-robot"></i></div>
        <div class="ai-header-info">
            <div class="ai-title">Assistant Création</div>
            <div class="ai-status">Prêt à vous aider</div>
        </div>
        <button class="ai-close" id="ai-close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="ai-chat-body" id="ai-chat-body">
        <div class="ai-msg bot">
            <div class="ai-bubble">
                Bonjour ! Je peux vous aider à rédiger votre offre d'emploi. Décrivez-moi brièvement le profil que vous recherchez ou votre projet !
            </div>
        </div>
    </div>
    <div class="ai-chat-input-area">
        <form id="ai-form" style="display:flex; width:100%; gap:8px;">
            <input type="text" id="ai-input" placeholder="Tapez votre message..." autocomplete="off">
            <button type="submit" id="ai-send"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>
</div>

<style>
/* AI Fab Button */
.ai-fab { position:fixed; bottom:2rem; right:2rem; width:60px; height:60px; border-radius:50%; background:linear-gradient(135deg, #8b5cf6, #3b82f6); color:white; display:flex; align-items:center; justify-content:center; font-size:1.8rem; cursor:pointer; box-shadow:0 10px 25px rgba(139,92,246,0.5); z-index:9999; transition:transform 0.3s, box-shadow 0.3s; }
.ai-fab:hover { transform:scale(1.1) translateY(-5px); box-shadow:0 15px 35px rgba(139,92,246,0.6); }

/* Chat Panel */
.ai-chat-panel { position:fixed; bottom:6rem; right:2rem; width:380px; height:550px; max-height:80vh; background:linear-gradient(160deg, #0f172a 0%, #1e293b 100%); border:1px solid rgba(255,255,255,0.1); border-radius:24px; display:flex; flex-direction:column; box-shadow:0 30px 60px rgba(0,0,0,0.8); z-index:9998; opacity:0; pointer-events:none; transform:translateY(20px) scale(0.95); transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1); overflow:hidden; }
.ai-chat-panel.active { opacity:1; pointer-events:auto; transform:none; }

.ai-chat-header { display:flex; align-items:center; padding:1.2rem 1.5rem; background:rgba(255,255,255,0.03); border-bottom:1px solid rgba(255,255,255,0.08); }
.ai-avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg, #8b5cf6, #3b82f6); display:flex; align-items:center; justify-content:center; font-size:1.2rem; color:white; margin-right:12px; }
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
.ai-msg.bot .ai-bubble { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#e2e8f0; border-bottom-left-radius:4px; }
.ai-msg.user .ai-bubble { background:linear-gradient(135deg, #8b5cf6, #3b82f6); color:white; border-bottom-right-radius:4px; }

.ai-chat-input-area { padding:1rem 1.5rem; border-top:1px solid rgba(255,255,255,0.08); background:rgba(0,0,0,0.2); }
#ai-input { flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:20px; padding:10px 15px; color:white; outline:none; font-family:inherit; transition:border-color 0.2s; }
#ai-input:focus { border-color:#8b5cf6; }
#ai-send { background:#8b5cf6; border:none; width:40px; height:40px; border-radius:50%; color:white; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s; }
#ai-send:hover { background:#7c3aed; }

/* Typing indicator */
.typing-dots { display:flex; gap:4px; padding:5px 0; }
.typing-dots span { width:6px; height:6px; background:var(--text-muted); border-radius:50%; animation:typing 1.4s infinite ease-in-out; }
.typing-dots span:nth-child(1) { animation-delay:0s; }
.typing-dots span:nth-child(2) { animation-delay:0.2s; }
.typing-dots span:nth-child(3) { animation-delay:0.4s; }
@keyframes typing { 0%, 100% { transform:scale(1); opacity:0.5; } 50% { transform:scale(1.2); opacity:1; background:white; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fab = document.getElementById('ai-fab');
    const panel = document.getElementById('ai-chat-panel');
    const closeBtn = document.getElementById('ai-close');
    const form = document.getElementById('ai-form');
    const input = document.getElementById('ai-input');
    const body = document.getElementById('ai-chat-body');

    let chatHistory = [];
    chatHistory.push({ role: 'bot', text: "Bonjour ! Je peux vous aider à rédiger votre offre d'emploi. Décrivez-moi brièvement le profil que vous recherchez ou votre projet !" });

    fab.addEventListener('click', () => {
        panel.classList.toggle('active');
        if(panel.classList.contains('active')) input.focus();
    });

    closeBtn.addEventListener('click', () => panel.classList.remove('active'));

    function parseMarkdown(text) {
        let html = text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\n/g, '<br>');
        return html;
    }

    function appendMsg(text, type) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-msg ${type}`;
        msgDiv.innerHTML = `<div class="ai-bubble">${parseMarkdown(text)}</div>`;
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

    async function sendToAi() {
        const typing = appendTyping();
        try {
            const res = await fetch('api_generate_job.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ history: chatHistory })
            });
            const data = await res.json();
            typing.remove();

            if (data.status === 'success') {
                const aiResponse = data.data;
                appendMsg(aiResponse.message, 'bot');
                chatHistory.push({ role: 'bot', text: aiResponse.message });
                
                // Si l'IA a toutes les informations
                if (aiResponse.is_complete && aiResponse.job_data) {
                    // Remplir le formulaire classique
                    const f_titre = document.getElementById('titre');
                    const f_budget = document.getElementById('budget');
                    const f_delai = document.getElementById('delai');
                    const f_comp = document.getElementById('competences');
                    const f_desc = document.getElementById('description');

                    if (f_titre) f_titre.value = aiResponse.job_data.titre || '';
                    if (f_budget) f_budget.value = aiResponse.job_data.budget || '';
                    if (f_delai) f_delai.value = aiResponse.job_data.delai || '';
                    if (f_comp) f_comp.value = aiResponse.job_data.competences || '';
                    if (f_desc) f_desc.value = aiResponse.job_data.description || '';

                    // Effet visuel
                    [f_titre, f_budget, f_delai, f_comp, f_desc].forEach(el => {
                        if (el) {
                            el.style.borderColor = '#10b981';
                            el.style.backgroundColor = 'rgba(16,185,129,0.05)';
                            setTimeout(() => { 
                                el.style.borderColor = 'var(--border)'; 
                                el.style.backgroundColor = 'rgba(255,255,255,0.03)';
                            }, 3000);
                        }
                    });

                    // Message final dans le chat
                    appendMsg("J'ai rempli le formulaire pour vous ! Vous pouvez fermer cette fenêtre, vérifier les informations et publier l'offre.", 'bot');
                    chatHistory.push({ role: 'bot', text: "J'ai rempli le formulaire."});

                    // Fermer la fenêtre de chat après un délai pour laisser le temps de lire
                    setTimeout(() => {
                        panel.classList.remove('active');
                    }, 4000);
                }
            } else {
                appendMsg("❌ Erreur : " + data.message, 'bot');
            }
        } catch (err) {
            typing.remove();
            appendMsg("❌ Oups, impossible de joindre l'IA.", 'bot');
        }
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const val = input.value.trim();
        if (!val) return;

        appendMsg(val, 'user');
        chatHistory.push({ role: 'user', text: val });
        input.value = '';
        
        sendToAi();
    });
});
</script>
</body>
</html>
