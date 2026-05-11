<?php
// views/frontoffice/chatbot_widget.php

if (!defined('FREELASKILL_CHATBOT_WIDGET')) {
    define('FREELASKILL_CHATBOT_WIDGET', true);

    $chatbotAssetBase = isset($chatbotAssetBase) ? trim((string) $chatbotAssetBase) : '../assets';
    $chatbotEndpoint = isset($chatbotEndpoint) ? trim((string) $chatbotEndpoint) : 'chatbot_api.php';
    $chatbotStorageKey = isset($chatbotStorageKey) ? trim((string) $chatbotStorageKey) : 'freelaskill-chat-history';
    $chatbotKicker = isset($chatbotKicker) ? trim((string) $chatbotKicker) : 'FreelaSkill AI';
    $chatbotTitle = isset($chatbotTitle) ? trim((string) $chatbotTitle) : "Besoin d'aide ?";
    $chatbotIntro = isset($chatbotIntro) ? trim((string) $chatbotIntro) : "Salut, je suis l'assistant FreelaSkill. Je peux t'aider a choisir un metier avance, ameliorer ton profil ou comprendre la plateforme.";
    $chatbotSuggestions = isset($chatbotSuggestions) && is_array($chatbotSuggestions)
        ? $chatbotSuggestions
        : [
            'Quels metiers avances je peux apprendre ?' => 'Metiers avances',
            'Comment ameliorer mon profil freelance ?' => 'Profil freelance',
            'Comment recruter un bon talent ?' => 'Recruter',
        ];
?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($chatbotAssetBase); ?>/chatbot.css">

<div class="fs-chatbot"
     data-chatbot
     data-chatbot-endpoint="<?php echo htmlspecialchars($chatbotEndpoint); ?>"
     data-chatbot-storage-key="<?php echo htmlspecialchars($chatbotStorageKey); ?>">
    <button class="fs-chatbot-toggle" type="button" data-chatbot-toggle aria-label="Ouvrir assistant">
        <span class="fs-chatbot-toggle__halo"></span>
        <i class="fa-solid fa-comments"></i>
        <span>Assistant</span>
    </button>

    <section class="fs-chatbot-panel" data-chatbot-panel aria-hidden="true">
        <header class="fs-chatbot-header">
            <div>
                <p class="fs-chatbot-kicker"><?php echo htmlspecialchars($chatbotKicker); ?></p>
                <h2><?php echo htmlspecialchars($chatbotTitle); ?></h2>
            </div>
            <button class="fs-chatbot-close" type="button" data-chatbot-close aria-label="Fermer assistant">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </header>

        <div class="fs-chatbot-body">
            <div class="fs-chatbot-messages" data-chatbot-messages>
                <article class="fs-chatbot-message fs-chatbot-message--assistant" data-chatbot-intro>
                    <?php echo htmlspecialchars($chatbotIntro); ?>
                </article>
            </div>

            <div class="fs-chatbot-suggestions" data-chatbot-suggestions>
                <?php foreach ($chatbotSuggestions as $prompt => $label): ?>
                    <button type="button" data-chatbot-suggestion="<?php echo htmlspecialchars($prompt); ?>"><?php echo htmlspecialchars($label); ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <form class="fs-chatbot-form" data-chatbot-form>
            <label class="fs-chatbot-input-wrap">
                <span class="sr-only">Message</span>
                <input type="text" data-chatbot-input maxlength="1200" placeholder="Pose ta question..." autocomplete="off">
            </label>
            <button type="submit" class="fs-chatbot-send" data-chatbot-send aria-label="Envoyer">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
    </section>
</div>

<script src="<?php echo htmlspecialchars($chatbotAssetBase); ?>/chatbot.js" defer></script>
<?php
}
?>
