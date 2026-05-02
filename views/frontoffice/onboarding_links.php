<?php
// views/frontoffice/onboarding_links.php
require_once __DIR__ . '/../../controllers/ProfileController.php';

(new ProfileController())->executeOnboardingLinksPage();
