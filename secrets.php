<?php
// secrets.php — Clés API et informations sensibles
// ⚠️ Ce fichier est exclu du Git (.gitignore) — ne JAMAIS le committer
// ──────────────────────────────────────────────────────────────────────────
// Pour configurer votre environnement, copiez secrets.example.php vers secrets.php
// et remplissez vos propres clés.
// ──────────────────────────────────────────────────────────────────────────

// ── AI APIs ───────────────────────────────────────────────────────────────
define('GEMINI_API_KEY',  'AIzaSyAb0hQ3YO3aFWxde4yywFXElsmt4A6zxOA');
define('GROQ_API_KEY',    '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');

// ── Stripe ────────────────────────────────────────────────────────────────
define('STRIPE_PUBLIC_KEY', 'pk_test_51TSfkU90r15ENUzNsAFBq4RQqWKfpiWd0bsa7W2Ss9DWq7cutvBba7wlCFuA6kqjYYJvlsLoqTrj6JW5SozN9Adf009vjh6sph');
define('STRIPE_SECRET_KEY', 'sk_test_51TSfkU90r15ENUzNGkNPM6UqFMEKkse26iuYEcAnGfxH3klenuCuRF1fxxmkM2rtKc5RZ8NymQZs0MrFebXIFTZ900m0eRxoP6');

// ── Cloudinary ────────────────────────────────────────────────────────────
define('CLOUDINARY_URL', 'cloudinary://962455564499621:6KCUgBqDWEQCJtuolijK69-jlck@dsimea1nb');

// ── Gmail SMTP ────────────────────────────────────────────────────────────
define('GMAIL_USER', 'acuityacuity23@gmail.com');
define('GMAIL_PASS', 'xrds olbu erwg irzn');
