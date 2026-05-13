<?php
// Views/Frontoffice/chat.php

if (!isset($id_conversation) || !isset($messages)) {
    die("Erreur: Données manquantes. <a href='messagerie_index.php?page=conversations'>Retour à la messagerie</a>");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── FreelaSkill dark navy theme ── */
        :root {
            --bg:         #060a18;
            --bg-card:    #0e1629;
            --bg-input:   #0a1020;
            --blue:       #2563eb;
            --blue-dark:  #1d4ed8;
            --blue-glow:  rgba(37,99,235,.25);
            --purple:     #6d28d9;
            --green:      #22c55e;
            --red:        #e70013;
            --border:     rgba(255,255,255,.07);
            --border-b:   rgba(37,99,235,.4);
            --muted:      #8b9cb8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Space Grotesk', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(ellipse 70% 60% at 75% 30%, rgba(37,99,235,0.08) 0%, transparent 70%),
                radial-gradient(ellipse 55% 50% at 15% 80%, rgba(124,58,237,0.06) 0%, transparent 65%),
                var(--bg);
            color: #fff;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Navbar ── */
        .logo {
            display: flex; align-items: center;
            gap: 0.5rem; text-decoration: none;
        }
        .logo i { color: var(--red); font-size: 1.3rem; }
        .logo .logo-freela { font-size: 1.5rem; font-weight: bold; color: white; }
        .logo .logo-skill  { font-size: 1.5rem; font-weight: bold; color: var(--blue); }

        .navbar {
            background: rgba(8,12,28,0.97);
            backdrop-filter: blur(20px);
            padding: 0.85rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,.06);
            position: sticky; top: 0; z-index: 100;
        }
        .container {
            max-width: 1300px; margin: 0 auto;
            display: flex; justify-content: space-between; align-items: center;
        }
        .nav-avatar {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(255,255,255,.05);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--muted); font-size: 14px;
            transition: all .2s;
        }
        .nav-avatar:hover {
            background: rgba(37,99,235,.15);
            color: var(--blue);
            border-color: var(--border-b);
        }

        /* ── Chat header ── */
        .chat-header {
            background: var(--bg-card);
            padding: 14px 20px;
            display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid var(--border);
        }
        .back-btn {
            background: rgba(255,255,255,.05);
            border: 1px solid var(--border);
            color: var(--muted); font-size: 16px; cursor: pointer;
            width: 34px; height: 34px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            transition: all .2s;
        }
        .back-btn:hover {
            background: rgba(37,99,235,.15);
            color: var(--blue); border-color: var(--border-b);
        }
        .chat-avatar {
            width: 42px; height: 42px; border-radius: 12px;
            background: linear-gradient(135deg, var(--blue), var(--purple));
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1rem;
        }
        .chat-info { flex: 1; }
        .chat-name   { font-weight: 600; font-size: .97rem; }
        .chat-status {
            font-size: 12px; color: var(--green);
            display: flex; align-items: center; gap: 5px;
            margin-top: 2px;
        }
        .chat-status::before {
            content: ''; width: 7px; height: 7px;
            border-radius: 50%; background: var(--green);
            display: inline-block;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%,100% { opacity:1; } 50% { opacity:.3; }
        }

        /* ── Messages ── */
        .messages-area {
            flex: 1; overflow-y: auto;
            padding: 20px 24px;
            display: flex; flex-direction: column; gap: 10px;
            scroll-behavior: smooth;
        }
        .messages-area::-webkit-scrollbar { width: 4px; }
        .messages-area::-webkit-scrollbar-thumb {
            background: rgba(37,99,235,.3); border-radius: 2px;
        }

        .message-row { display: flex; width: 100%; }
        .message-row.sent     { justify-content: flex-end; }
        .message-row.received { justify-content: flex-start; }
        .message-wrapper {
            display: flex; align-items: flex-end; gap: 8px;
            max-width: 75%;
        }
        .sent .message-wrapper { flex-direction: row-reverse; }

        .msg-avatar {
            width: 30px; height: 30px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; flex-shrink: 0;
            color: white;
        }

        .message-bubble {
            padding: 10px 14px;
            border-radius: 14px;
            font-size: 14px; line-height: 1.5;
            word-wrap: break-word;
            animation: msgIn .18s ease;
        }
        @keyframes msgIn {
            from { opacity:0; transform:translateY(5px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .sent .message-bubble {
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            color: white;
            border-bottom-right-radius: 4px;
            box-shadow: 0 4px 15px var(--blue-glow);
            border: 1px solid rgba(37,99,235,.5);
        }
        .received .message-bubble {
            background: var(--bg-card);
            color: rgba(255,255,255,.88);
            border-bottom-left-radius: 4px;
            border: 1px solid var(--border);
        }
        .message-time {
            font-size: 10px; color: var(--muted);
            margin-top: 4px; text-align: right;
        }

        .date-separator { text-align: center; margin: 12px 0; }
        .date-separator span {
            background: var(--bg-card);
            border: 1px solid var(--border);
            padding: 4px 14px; border-radius: 20px;
            font-size: 11px; color: var(--muted);
        }
        .empty-messages {
            text-align: center; padding: 40px; color: var(--muted);
        }

        /* ── Input zone ── */
        .input-zone {
            padding: 14px 20px 18px;
            background: var(--bg-card);
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }
        .input-row {
            display: flex; align-items: center; gap: 8px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 10px 14px;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-row.focused {
            border-color: var(--border-b);
            box-shadow: 0 0 0 3px var(--blue-glow);
        }
        .input-row.error {
            border-color: var(--red) !important;
            box-shadow: 0 0 0 3px rgba(231,0,19,0.12) !important;
        }

        #msgInput {
            flex: 1; background: transparent;
            border: none; outline: none;
            color: white; font-size: 14px;
            font-family: inherit; padding: 2px 0;
            resize: none;
        }
        #msgInput::placeholder { color: var(--muted); }

        .send-btn {
            background: var(--blue); border: none; color: white;
            width: 34px; height: 34px; border-radius: 10px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: background .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 12px var(--blue-glow);
        }
        .send-btn:hover  { background: var(--blue-dark); transform: scale(1.08); box-shadow: 0 6px 18px var(--blue-glow); }
        .send-btn:active { transform: scale(.95); }
        .send-btn:disabled { background: rgba(255,255,255,.08); box-shadow: none; cursor: not-allowed; transform: none; }

        .char-counter {
            font-size: 10px; color: var(--muted);
            text-align: right; margin-top: 5px;
        }
        .char-counter.warn { color: var(--red); }

        /* ── Validation message ── */
        .validation-msg {
            display: none;
            align-items: center; gap: 7px;
            margin-top: 8px; padding: 7px 13px;
            background: rgba(231,0,19,.10);
            border: 1px solid rgba(231,0,19,.30);
            border-radius: 10px;
            color: #ff4d4d; font-size: 12px; font-weight: 500;
            animation: valFadeIn .18s ease;
        }
        .validation-msg.show { display: flex; }
        .validation-msg i    { font-size: 13px; flex-shrink: 0; }
        @keyframes valFadeIn {
            from { opacity:0; transform:translateY(-4px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* ── Edit / dropdown ── */
        .edit-textarea {
            width: 100%; background: rgba(255,255,255,.05);
            border: 1px solid var(--border-b); border-radius: 12px;
            padding: 8px; color: white;
            font-family: inherit; font-size: 14px;
            resize: none; outline: none;
        }
        .edit-actions { display: flex; gap: 8px; margin-top: 8px; justify-content: flex-end; }
        .edit-actions button { padding: 6px 14px; border-radius: 8px; border: none; cursor: pointer; font-size: 12px; }
        .save-edit   { background: var(--blue); color: white; }
        .cancel-edit { background: rgba(255,255,255,.08); color: var(--muted); }

        .dropdown {
            position: fixed;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,.5);
            z-index: 1000; min-width: 160px; overflow: hidden;
        }
        .dropdown button {
            width: 100%; padding: 10px 16px;
            background: none; border: none; color: white;
            text-align: left; cursor: pointer;
            display: flex; align-items: center; gap: 10px;
            font-size: 14px; transition: background .15s;
        }
        .dropdown button:hover { background: rgba(255,255,255,.06); }
        .dropdown .delete { color: #ff3b30; }
        .dropdown .edit   { color: var(--blue); }
        .dropdown .report { color: #f59e0b; }

        @media (max-width: 640px) {
            .message-wrapper { max-width: 88%; }
            .chat-header { padding: 10px 14px; }
            .messages-area { padding: 14px; }
            .input-zone { padding: 10px 14px 14px; }
        }
    </style>
</head>
<body>

<?php
$activePage = 'messagerie';
$isClient   = (($_SESSION['user_role'] ?? '') === 'client');
$roleName   = trim(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?: 'Utilisateur';
?>

<nav class="navbar" style="display:flex; justify-content:space-between; align-items:center;">
    <a href="/Esprit-PW-2A32-2526-TalentBridge-job/views/frontoffice/home.php" class="logo"><i class="fa-solid fa-shapes"></i> <span class="logo-freela">Freela</span><span class="logo-skill">Skill</span></a>
    
    <div style="display:flex; gap:2rem; align-items:center; list-style:none;">
        <span style="color:var(--muted);cursor:default; font-size:0.9rem;">Accueil</span>
        <a href="/Esprit-PW-2A32-2526-TalentBridge-job/views/frontoffice/home.php" style="color:var(--muted); text-decoration:none; font-size:0.9rem; font-weight:500;">Marketplace</a>
        <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'client'): ?>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/views/frontoffice/missions.php" style="color:var(--muted); text-decoration:none; font-size:0.9rem; font-weight:500;">Missions</a>
        <?php else: ?>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/views/frontoffice/freelancer_home.php" style="color:var(--muted); text-decoration:none; font-size:0.9rem; font-weight:500;">Freelancers</a>
        <?php endif; ?>
        <a href="messagerie_index.php?page=conversations" style="color:white; text-decoration:none; font-size:0.9rem; position:relative; font-weight:500;">
            Messagerie
            <span style="position:absolute; bottom:-4px; left:0; right:0; height:2px; background:var(--blue); border-radius:2px;"></span>
        </a>
        <a href="/Esprit-PW-2A32-2526-TalentBridge-job/views/frontoffice/profile.php" style="color:var(--muted); text-decoration:none; font-size:0.9rem; font-weight:500;">Mon Profil</a>
    </div>

    <div class="nav-right" style="display:flex; gap:1rem;">
        <div class="nav-avatar" title="Mon profil">
            <?= strtoupper(substr((string)($_SESSION['user_id'] ?? '?'), 0, 2)) ?>
        </div>
    </div>
</nav>

<div class="chat-header">
    <button class="back-btn" onclick="window.location.href='messagerie_index.php?page=conversations'">
        <i class="fa-solid fa-arrow-left"></i>
    </button>
    <?php 
    $initial = strtoupper(substr($convName ?? 'C', 0, 1));
    $colors = ['#f44336','#e91e63','#9c27b0','#673ab7','#3f51b5','#2196f3','#03a9f4','#00bcd4','#009688','#4caf50','#8bc34a','#cddc39','#ffc107','#ff9800','#ff5722'];
    $bgColor = $colors[ord($initial) % count($colors)];
    ?>
    <div class="chat-avatar" style="background: <?= $bgColor ?>; color: white; border: none; font-weight: bold;">
        <?= $initial ?>
    </div>
    <div class="chat-info">
        <div class="chat-name">Conversation #<?php echo $id_conversation; ?></div>
        <div class="chat-status">
            <i class="fa-solid fa-circle" style="font-size:8px;"></i> En ligne
        </div>
    </div>
</div>

<div class="messages-area" id="messagesArea">
    <?php if (empty($messages)): ?>
        <div class="empty-messages">
            <i class="fa-regular fa-comment-dots" style="font-size:40px;margin-bottom:12px;opacity:.3;"></i>
            <p>Aucun message</p>
            <p style="font-size:.8rem;">Soyez le premier à envoyer un message !</p>
        </div>
    <?php else: ?>
        <?php
        $current_date = '';
        foreach ($messages as $message):
            if ($message['statut'] === 'deleted') continue;
            $message_date = date('d/m/Y', strtotime($message['date_envoi']));
            $is_sent      = ($message['id_expediteur'] ?? 0) == $id_user;
            $seconds_ago  = $message['seconds_ago'] ?? 999;
            $can_edit     = $is_sent && $seconds_ago <= 60;
            $seconds_left = $can_edit ? 60 - $seconds_ago : 0;
        ?>
            <?php if ($current_date != $message_date):
                $current_date = $message_date; ?>
                <div class="date-separator"><span><?php echo $message_date; ?></span></div>
            <?php endif; ?>

            <div class="message-row <?php echo $is_sent ? 'sent' : 'received'; ?>"
                 data-message-id="<?php echo $message['id_message']; ?>"
                 data-seconds-left="<?php echo $seconds_left; ?>">
                <div class="message-wrapper">
                    <div class="message-bubble" id="bubble-<?php echo $message['id_message']; ?>">
                        <?php echo nl2br(htmlspecialchars($message['contenu'])); ?>
                        <div class="message-time">
                            <?php echo date('H:i', strtotime($message['date_envoi'])); ?>
                            <?php if ($can_edit && $seconds_left > 0): ?>
                                <span class="edit-timer">
                                    <i class="fa-regular fa-clock"></i> modifiable (<?php echo $seconds_left; ?>s)
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($is_sent): ?>
                        <button class="menu-btn" onclick="showMenu(event, <?php echo $message['id_message']; ?>, <?php echo $can_edit ? 'true' : 'false'; ?>, '<?php echo addslashes($message['contenu']); ?>')">
                            <i class="fa-solid fa-ellipsis-h"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ── Input zone ── -->
<div class="input-zone">
    <div class="input-row" id="inputRow">
        <!-- NO required attribute — validation is 100% JS -->
        <textarea id="msgInput" rows="1" placeholder="Aa"></textarea>
        <!-- type="button" prevents any form/HTML5 behaviour -->
        <button type="button" class="send-btn" id="sendBtn">
            <i class="fa-regular fa-paper-plane"></i>
        </button>
    </div>
    <div class="char-counter" id="charCounter">0 / 150</div>

    <!-- Custom validation message — shown by JS, never by browser -->
    <div class="validation-msg" id="validationMsg">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span id="validationText"></span>
    </div>
</div>

<script>
/* ═══════════════════════════════════════════════
   chat.js — inline, pure JS validation
   NO alert(), NO HTML5 required popup
   ═══════════════════════════════════════════════ */
'use strict';

const CONVERSATION_ID = <?php echo json_encode($id_conversation); ?>;
const MAX_LENGTH      = 150;
const WARN_AT         = 130;

/* ── DOM refs ── */
const msgInput      = document.getElementById('msgInput');
const sendBtn       = document.getElementById('sendBtn');
const inputRow      = document.getElementById('inputRow');
const charCounter   = document.getElementById('charCounter');
const messagesArea  = document.getElementById('messagesArea');
const validationMsg = document.getElementById('validationMsg');
const validationTxt = document.getElementById('validationText');

/* ── Forbidden pattern ── */
const FORBIDDEN = /<|>|javascript:/i;

let errorTimer = null;
let activeDropdown = null;

/* ════════════════════════════════════════════════
   VALIDATION MESSAGE  (under the input)
   ════════════════════════════════════════════════ */
function showError(message) {
    clearTimeout(errorTimer);

    validationTxt.textContent = message;
    validationMsg.classList.add('show');   // makes display:flex
    inputRow.classList.add('error');       // red border

    /* auto-hide after 3 s */
    errorTimer = setTimeout(hideError, 3000);
}

function hideError() {
    validationMsg.classList.remove('show');
    inputRow.classList.remove('error');
    clearTimeout(errorTimer);
}

/* ════════════════════════════════════════════════
   VALIDATION RULES
   ════════════════════════════════════════════════ */
function validate(text) {
    if (text.trim().length === 0)    return 'Veuillez écrire un message.';
    if (text.length > MAX_LENGTH)    return `Message trop long (max ${MAX_LENGTH} caractères).`;
    if (FORBIDDEN.test(text.trim())) return 'Caractères non autorisés.';
    return null; // null = valid
}

/* ════════════════════════════════════════════════
   CHAR COUNTER
   ════════════════════════════════════════════════ */
function updateCounter() {
    const len = msgInput.value.length;

    /* Hard cap — silently truncate */
    if (len > MAX_LENGTH) {
        msgInput.value = msgInput.value.substring(0, MAX_LENGTH);
    }

    const cur = msgInput.value.length;
    charCounter.textContent = cur + ' / ' + MAX_LENGTH;
    charCounter.style.color =
        cur >= MAX_LENGTH ? '#ff3b30' :
        cur >= WARN_AT    ? '#f59e0b' : '#666';
}

/* ════════════════════════════════════════════════
   AUTO-RESIZE
   ════════════════════════════════════════════════ */
function autoResize() {
    msgInput.style.height = 'auto';
    msgInput.style.height = Math.min(msgInput.scrollHeight, 100) + 'px';
}

/* ════════════════════════════════════════════════
   SCROLL
   ════════════════════════════════════════════════ */
function scrollToBottom() {
    messagesArea.scrollTop = messagesArea.scrollHeight;
}

/* ════════════════════════════════════════════════
   APPEND OPTIMISTIC BUBBLE (no reload on success)
   ════════════════════════════════════════════════ */
function appendSentBubble(text) {
    const now  = new Date();
    const time = now.getHours().toString().padStart(2,'0') + ':' +
                 now.getMinutes().toString().padStart(2,'0');
    const safe = text
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/\n/g,'<br>');

    const row = document.createElement('div');
    row.className = 'message-row sent';
    row.innerHTML = `
        <div class="message-wrapper">
            <div class="message-bubble">
                ${safe}
                <div class="message-time">${time}</div>
            </div>
        </div>`;
    messagesArea.appendChild(row);
    scrollToBottom();
}

/* ════════════════════════════════════════════════
   SEND MESSAGE
   ════════════════════════════════════════════════ */
function sendMessage() {
    const text  = msgInput.value;
    const error = validate(text);

    hideError();

    if (error) {
        showError(error);      /* styled inline message — NO alert/popup */
        msgInput.focus();
        return;
    }

    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    fetch('messagerie_index.php?page=chat&action=send', {
        method : 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body   : 'id_conversation=' + CONVERSATION_ID +
                 '&contenu=' + encodeURIComponent(text.trim())
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            appendSentBubble(text.trim());
            msgInput.value = '';
            autoResize();
            updateCounter();
            msgInput.focus();
        } else {
            showError(data.error || 'Erreur lors de l\'envoi.');
        }
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fa-regular fa-paper-plane"></i>';
    })
    .catch(() => {
        showError('Erreur réseau. Veuillez réessayer.');
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fa-regular fa-paper-plane"></i>';
    });
}

/* ════════════════════════════════════════════════
   EVENTS
   ════════════════════════════════════════════════ */
msgInput.addEventListener('focus',  () => { inputRow.classList.add('focused'); hideError(); });
msgInput.addEventListener('blur',   () => inputRow.classList.remove('focused'));
msgInput.addEventListener('input',  () => { hideError(); autoResize(); updateCounter(); });
msgInput.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});
/* Block forbidden paste silently with message */
msgInput.addEventListener('paste', e => {
    const pasted = (e.clipboardData || window.clipboardData).getData('text');
    if (FORBIDDEN.test(pasted)) {
        e.preventDefault();
        showError('Caractères non autorisés.');
    }
});
sendBtn.addEventListener('click', sendMessage);

/* ════════════════════════════════════════════════
   EDIT / DELETE / REPORT / DROPDOWN  (unchanged)
   ════════════════════════════════════════════════ */
function deleteMessage(messageId) {
    if (confirm('Supprimer ce message ?')) {
        fetch('messagerie_index.php?page=chat&action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_message=' + messageId
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
    }
}

function reportMessage(messageId) {
    if (confirm('Signaler ce message ?')) {
        fetch('messagerie_index.php?page=chat&action=report', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_message=' + messageId
        }).then(r => r.json()).then(data => { if (data.success) alert('Signalé'); });
    }
}

function editMessage(messageId, currentContent) {
    const bubble = document.getElementById('bubble-' + messageId);
    if (!bubble) return;
    if (activeDropdown) { activeDropdown.remove(); activeDropdown = null; }
    bubble.innerHTML = `
        <textarea class="edit-textarea" rows="2" maxlength="150">${currentContent}</textarea>
        <div class="edit-actions">
            <button class="save-edit"   onclick="saveEdit(${messageId}, this)">✓ Modifier</button>
            <button class="cancel-edit" onclick="location.reload()">✗ Annuler</button>
        </div>`;
    bubble.querySelector('.edit-textarea').focus();
}

function saveEdit(messageId, btn) {
    const bubble     = btn.closest('.message-bubble');
    const newContent = bubble.querySelector('.edit-textarea').value.trim();
    if (!newContent || newContent.length > 150) return;
    fetch('messagerie_index.php?page=chat&action=edit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_message=' + messageId + '&contenu=' + encodeURIComponent(newContent)
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
}

function showMenu(event, messageId, canEdit, currentContent) {
    event.stopPropagation();
    if (activeDropdown) activeDropdown.remove();
    const btn  = event.currentTarget;
    const rect = btn.getBoundingClientRect();
    const dropdown = document.createElement('div');
    dropdown.className = 'dropdown';
    dropdown.style.top   = (rect.bottom + 5) + 'px';
    dropdown.style.right = (window.innerWidth - rect.right) + 'px';
    dropdown.innerHTML = `
        ${canEdit ? `<button onclick="editMessage(${messageId},'${currentContent.replace(/'/g,"\\'")}');this.closest('.dropdown').remove();" class="edit"><i class="fa-regular fa-pen-to-square"></i> Modifier</button>` : ''}
        <button onclick="reportMessage(${messageId});this.closest('.dropdown').remove();" class="report"><i class="fa-solid fa-flag"></i> Signaler</button>
        <button onclick="deleteMessage(${messageId});this.closest('.dropdown').remove();" class="delete"><i class="fa-regular fa-trash-can"></i> Supprimer</button>`;
    document.body.appendChild(dropdown);
    activeDropdown = dropdown;
}

document.addEventListener('click', () => {
    if (activeDropdown) { activeDropdown.remove(); activeDropdown = null; }
});

/* ════════════════════════════════════════════════
   HIGHLIGHT  (from URL ?highlight=id)
   ════════════════════════════════════════════════ */
function highlightMessage() {
    const id = new URLSearchParams(window.location.search).get('highlight');
    if (!id) return;
    const el = document.querySelector(`.message-row[data-message-id="${id}"] .message-bubble`);
    if (!el) return;
    el.style.background = '#e70013';
    el.style.color      = 'white';
    setTimeout(() => { el.style.background = ''; el.style.color = ''; }, 3000);
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/* ── Init ── */
scrollToBottom();
updateCounter();
highlightMessage();
</script>

</body>
</html>