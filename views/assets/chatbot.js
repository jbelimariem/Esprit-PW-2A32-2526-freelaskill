(function () {
    const root = document.querySelector('[data-chatbot]');

    if (!root || root.dataset.ready === 'true') {
        return;
    }

    root.dataset.ready = 'true';

    const endpoint = root.dataset.chatbotEndpoint || 'chatbot_api.php';
    const toggleButton = root.querySelector('[data-chatbot-toggle]');
    const closeButton = root.querySelector('[data-chatbot-close]');
    const panel = root.querySelector('[data-chatbot-panel]');
    const form = root.querySelector('[data-chatbot-form]');
    const input = root.querySelector('[data-chatbot-input]');
    const sendButton = root.querySelector('[data-chatbot-send]');
    const messagesEl = root.querySelector('[data-chatbot-messages]');
    const suggestionsEl = root.querySelector('[data-chatbot-suggestions]');
    const storageKey = root.dataset.chatbotStorageKey || 'freelaskill-chat-history';
    let conversation = readHistory();
    let isSending = false;

    if (conversation.length > 0) {
        messagesEl.innerHTML = '';
        conversation.forEach((item) => appendMessage(item.role, item.content, false));
        hideSuggestions();
    }

    function openChat() {
        root.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        window.setTimeout(() => input && input.focus(), 80);
        scrollToBottom();
    }

    function closeChat() {
        root.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
    }

    function appendMessage(role, content, shouldScroll) {
        const message = document.createElement('article');
        message.className = 'fs-chatbot-message fs-chatbot-message--' + role;
        message.textContent = content;
        messagesEl.appendChild(message);

        if (shouldScroll !== false) {
            scrollToBottom();
        }

        return message;
    }

    function appendTyping() {
        const typing = appendMessage('assistant', 'Assistant ecrit...', true);
        typing.classList.add('is-typing');
        return typing;
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function hideSuggestions() {
        if (suggestionsEl) {
            suggestionsEl.style.display = 'none';
        }
    }

    function setSending(value) {
        isSending = value;
        sendButton.disabled = value;
        input.disabled = value;
    }

    function readHistory() {
        try {
            const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');

            if (!Array.isArray(saved)) {
                return [];
            }

            return saved
                .filter((item) => item && ['user', 'assistant'].includes(item.role) && typeof item.content === 'string')
                .slice(-12);
        } catch (error) {
            return [];
        }
    }

    function saveHistory() {
        try {
            localStorage.setItem(storageKey, JSON.stringify(conversation.slice(-12)));
        } catch (error) {
            // Ignore storage errors. The chat still works for this page view.
        }
    }

    async function sendMessage(message) {
        if (isSending || message.trim() === '') {
            return;
        }

        const cleanMessage = message.trim();
        const previousHistory = conversation.slice(-10);

        hideSuggestions();
        appendMessage('user', cleanMessage, true);
        conversation.push({
            role: 'user',
            content: cleanMessage
        });
        saveHistory();
        input.value = '';
        setSending(true);

        const typing = appendTyping();

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: cleanMessage,
                    history: previousHistory
                })
            });

            const data = await response.json();

            typing.remove();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erreur API.');
            }

            const reply = data.reply || 'Je suis la, mais je n ai pas encore de reponse.';
            appendMessage('assistant', reply, true);
            conversation.push({
                role: 'assistant',
                content: reply
            });
            saveHistory();
        } catch (error) {
            typing.remove();
            appendMessage('assistant', 'Impossible de contacter le chatbot pour le moment. Reessaie dans un instant.', true);
        } finally {
            setSending(false);
            input.focus();
        }
    }

    toggleButton.addEventListener('click', () => {
        if (root.classList.contains('is-open')) {
            closeChat();
        } else {
            openChat();
        }
    });

    closeButton.addEventListener('click', closeChat);

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        sendMessage(input.value);
    });

    if (suggestionsEl) {
        suggestionsEl.addEventListener('click', (event) => {
            const button = event.target.closest('[data-chatbot-suggestion]');

            if (!button) {
                return;
            }

            openChat();
            sendMessage(button.dataset.chatbotSuggestion || button.textContent);
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && root.classList.contains('is-open')) {
            closeChat();
        }
    });
})();
