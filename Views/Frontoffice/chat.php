<?php
// Views/Frontoffice/chat.php

if (!isset($id_conversation) || !isset($messages)) {
    die("Erreur: Données manquantes. <a href='index.php?page=conversations'>Retour à la messagerie</a>");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(ellipse 70% 60% at 75% 30%, rgba(37,99,235,0.13) 0%, transparent 70%),
                radial-gradient(ellipse 55% 50% at 20% 20%, rgba(124,58,237,0.10) 0%, transparent 65%),
                #05081a;
            color: #fff;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .logo i { color: #e70013; font-size: 1.3rem; }
        .logo .logo-freela { font-size: 1.5rem; font-weight: bold; color: white; }
        .logo .logo-skill  { font-size: 1.5rem; font-weight: bold; color: #2563eb; }

        .navbar {
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(37,99,235,0.2);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
        }

        /* ── Chat header ── */
        .chat-header {
            background: #1a1a1a;
            padding: 12px 16px;
            display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid #2a2a2a;
        }
        .back-btn {
            background: none; border: none;
            color: #2563eb; font-size: 20px; cursor: pointer;
        }
        .chat-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            display: flex; align-items: center; justify-content: center;
            font-weight: bold;
        }
        .chat-info { flex: 1; }
        .chat-name   { font-weight: 600; }
        .chat-status { font-size: 12px; color: #22c55e; }

        /* ── Messages ── */
        .messages-area {
            flex: 1; overflow-y: auto;
            padding: 16px;
            display: flex; flex-direction: column; gap: 8px;
        }
        .message-row { display: flex; width: 100%; }
        .message-row.sent     { justify-content: flex-end; }
        .message-row.received { justify-content: flex-start; }
        .message-wrapper {
            display: flex; align-items: flex-end; gap: 6px;
            max-width: 75%;
        }
        .sent .message-wrapper { flex-direction: row-reverse; }
        .message-bubble {
            padding: 8px 12px;
            border-radius: 18px;
            font-size: 14px; line-height: 1.4;
            word-wrap: break-word;
        }
        .sent .message-bubble {
            background: #2563eb; color: white;
            border-bottom-right-radius: 4px;
        }
        .received .message-bubble {
            background: #1f1f1f; color: #e0e0e0;
            border-bottom-left-radius: 4px;
        }
        .message-time {
            font-size: 10px; color: #888;
            margin-top: 4px; text-align: right;
        }
        .date-separator { text-align: center; margin: 16px 0; }
        .date-separator span {
            background: #1a1a1a;
            padding: 4px 12px; border-radius: 20px;
            font-size: 11px; color: #888;
        }
        .empty-messages {
            text-align: center; padding: 40px; color: #666;
        }

        /* ── Input zone ── */
        .input-zone {
            padding: 12px 16px;
            background: #1a1a1a;
            border-top: 1px solid #2a2a2a;
            flex-shrink: 0;
        }
        .input-row {
            display: flex; align-items: center; gap: 8px;
            background: #0a0f1e;
            border: 1px solid #2a2a2a;
            border-radius: 24px;
            padding: 8px 12px;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-row.focused { border-color: #2563eb; }

        /* ── RED BORDER when validation error ── */
        .input-row.error {
            border-color: #e70013 !important;
            box-shadow: 0 0 0 3px rgba(231,0,19,0.12) !important;
        }

        /* ── YELLOW BORDER when bad word warning ── */
        .input-row.warning {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245,158,11,0.12) !important;
        }

        #msgInput {
            flex: 1; background: transparent;
            border: none; outline: none;
            color: white; font-size: 14px;
            font-family: inherit; padding: 4px 0;
            resize: none;
        }
        #msgInput::placeholder { color: #666; }

        .send-btn {
            background: #2563eb; border: none; color: white;
            width: 32px; height: 32px; border-radius: 50%;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: background .2s, transform .15s;
        }
        .send-btn:hover  { background: #1d4ed8; transform: scale(1.08); }
        .send-btn:active { transform: scale(.95); }
        .send-btn:disabled { background: #333; cursor: not-allowed; transform: none; }

        .char-counter {
            font-size: 10px; color: #666;
            text-align: right; margin-top: 4px;
        }

        /* ══════════════════════════════════════════════
           CUSTOM VALIDATION MESSAGE  ← THE KEY PART
           Appears UNDER the input, NO native popup
           ══════════════════════════════════════════════ */
        .validation-msg {
            display: none;                      /* hidden by default */
            align-items: center;
            gap: 7px;
            margin-top: 8px;
            padding: 7px 13px;
            background: rgba(231, 0, 19, 0.10);
            border: 1px solid rgba(231, 0, 19, 0.30);
            border-radius: 10px;
            color: #ff4d4d;
            font-size: 12px;
            font-weight: 500;
            animation: valFadeIn .18s ease;
        }
        .validation-msg.show { display: flex; }  /* JS adds .show */
        .validation-msg i    { font-size: 13px; flex-shrink: 0; }

        /* ── WARNING MESSAGE (yellow) for bad words ── */
        .validation-msg.warning {
            background: rgba(245, 158, 11, 0.10);
            border: 1px solid rgba(245, 158, 11, 0.30);
            color: #fbbf24;
        }

        @keyframes valFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Edit / dropdown styles (unchanged) ── */
        .edit-textarea {
            width: 100%; background: #2a2a2a;
            border: 1px solid #2563eb; border-radius: 12px;
            padding: 8px; color: white;
            font-family: inherit; font-size: 14px;
            resize: none; outline: none;
        }
        .edit-actions { display: flex; gap: 8px; margin-top: 8px; justify-content: flex-end; }
        .edit-actions button { padding: 6px 12px; border-radius: 8px; border: none; cursor: pointer; font-size: 12px; }
        .save-edit   { background: #2563eb; color: white; }
        .cancel-edit { background: #2a2a2a; color: #888; }
        .dropdown {
            position: fixed; background: #1f1f1f;
            border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.3);
            z-index: 1000; min-width: 160px; overflow: hidden;
        }
        .dropdown button {
            width: 100%; padding: 10px 16px;
            background: none; border: none; color: white;
            text-align: left; cursor: pointer;
            display: flex; align-items: center; gap: 10px;
            font-size: 14px;
        }
        .dropdown button:hover { background: #2a2a2a; }
        .dropdown .delete { color: #ff3b30; }
        .dropdown .edit   { color: #2563eb; }
        .dropdown .report { color: #f59e0b; }

        @media (max-width: 640px) {
            .message-wrapper { max-width: 85%; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="index.php?page=home" class="logo">
            <i class="fa-solid fa-shapes"></i>
            <span class="logo-freela">Freela</span>
            <span class="logo-skill">Skill</span>
        </a>
        <div class="nav-avatar"><i class="fa-regular fa-user"></i></div>
    </div>
</nav>

<div class="chat-header">
    <button class="back-btn" onclick="window.location.href='index.php?page=conversations'">
        <i class="fa-solid fa-arrow-left"></i>
    </button>
    <div class="chat-avatar">
        <?php echo $id_conversation ? substr(md5($id_conversation), 0, 2) : '?'; ?>
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
let warningTimer = null;
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
   WARNING MESSAGE (for bad words)
   ════════════════════════════════════════════════ */
function showWarning(message) {
    clearTimeout(warningTimer);

    validationTxt.textContent = message;
    validationMsg.classList.add('warning');  // add warning class for yellow color
    validationMsg.classList.add('show');     // makes display:flex
    inputRow.classList.add('warning');       // yellow border

    /* auto-hide after 4 s */
    warningTimer = setTimeout(hideWarning, 4000);
}

function hideWarning() {
    validationMsg.classList.remove('warning');
    validationMsg.classList.remove('show');
    inputRow.classList.remove('warning');
    clearTimeout(warningTimer);
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

    fetch('index.php?page=chat&action=send', {
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
        } else if (data.warning) {
            const warningText = data.found_words && data.found_words.length > 0
                ? `${data.warning}`
                : data.warning;
            showWarning(warningText);
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
        fetch('index.php?page=chat&action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_message=' + messageId
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
    }
}

function reportMessage(messageId) {
    if (confirm('Signaler ce message ?')) {
        fetch('index.php?page=chat&action=report', {
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
    fetch('index.php?page=chat&action=edit', {
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
