<?php
// secrets.example.php — Modèle de configuration
// COPIEZ CE FICHIER VERS secrets.php ET REMPLISSEZ VOS CLÉS

// ── AI APIs ───────────────────────────────────────────────────────────────
define('GEMINI_API_KEY',  'VOTRE_CLE_ICI');
define('GROQ_API_KEY',    'VOTRE_CLE_ICI');

// ── Stripe ────────────────────────────────────────────────────────────────
define('STRIPE_PUBLIC_KEY', 'VOTRE_CLE_PUBLIQUE_TEST_ICI');
define('STRIPE_SECRET_KEY', 'VOTRE_CLE_SECRETE_TEST_ICI');

// ── Cloudinary ────────────────────────────────────────────────────────────
define('CLOUDINARY_URL', 'VOTRE_URL_CLOUDINARY_ICI');

// ── Gmail SMTP ────────────────────────────────────────────────────────────
define('GMAIL_USER', 'votre-email@gmail.com');
define('GMAIL_PASS', 'votre-mot-de-passe-application');
