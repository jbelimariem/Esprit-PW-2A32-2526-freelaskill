<?php
// views/frontoffice/translate_widget.php
// Drop-in floating "Translate to English" button powered by Groq AI.
// Usage: include __DIR__ . '/translate_widget.php';

if (!defined('FREELASKILL_TRANSLATE_WIDGET')) {
    define('FREELASKILL_TRANSLATE_WIDGET', true);

    $trlAssetBase = isset($trlAssetBase) ? trim((string) $trlAssetBase) : '../assets';
    $trlEndpoint  = isset($trlEndpoint)  ? trim((string) $trlEndpoint)  : 'translate_api.php';
?>
<div id="fs-translate-widget"
     class="fs-trl-widget"
     data-translate-endpoint="<?php echo htmlspecialchars($trlEndpoint); ?>">

    <!-- Main floating button -->
    <button id="fs-trl-btn"
            class="fs-trl-btn"
            type="button"
            data-translate-btn
            aria-label="Translate page to English">

        <!-- Animated halo ring -->
        <span class="fs-trl-halo"></span>

        <!-- SVG progress ring (shown while loading) -->
        <svg class="fs-trl-progress" viewBox="0 0 48 48" aria-hidden="true">
            <circle class="fs-trl-ring-bg" cx="24" cy="24" r="20"/>
            <circle class="fs-trl-ring-fg" cx="24" cy="24" r="20"/>
        </svg>

        <!-- Icon -->
        <i class="fa-solid fa-earth-americas" data-translate-icon></i>

        <!-- Label -->
        <span class="fs-trl-label" data-translate-label>EN</span>
    </button>

    <!-- Status badge (floats above button) -->
    <div class="fs-trl-status" data-translate-status></div>

    <!-- Tooltip -->
    <div class="fs-trl-tooltip" aria-hidden="true">
        <strong>AI Translate</strong>
        <span>Powered by Groq</span>
    </div>
</div>

<script src="<?php echo htmlspecialchars($trlAssetBase); ?>/translate.js" defer></script>
<?php
}
?>
