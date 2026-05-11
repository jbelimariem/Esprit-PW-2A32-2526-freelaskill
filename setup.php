<?php
/**
 * setup.php — Script d'installation automatique
 * Lance ce script UNE SEULE FOIS après git clone / git pull
 * http://localhost/projet2222/setup.php
 */

$secretsPath = __DIR__ . '/secrets.php';
$examplePath = __DIR__ . '/secrets.example.php';

// Déjà configuré ?
if (file_exists($secretsPath)) {
    echo "<p style='color:green;font-family:monospace;'>✅ secrets.php existe déjà — Groq est configuré.</p>";
    echo "<p><a href='views/frontoffice/home.php'>→ Aller au site</a></p>";
    exit;
}

// Copier le fichier example
if (!file_exists($examplePath)) {
    die("<p style='color:red;'>❌ secrets.example.php introuvable !</p>");
}

$content = file_get_contents($examplePath);
// Remplacer le commentaire du header
$content = str_replace(
    '// secrets.example.php — Modèle de configuration',
    '// secrets.php — Configuration locale (NE PAS COMMITTER CE FICHIER)',
    $content
);

file_put_contents($secretsPath, $content);

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
<meta charset='UTF-8'>
<title>Setup FreelaSkill</title>
<style>
  body { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 2rem; }
  .ok { color: #10b981; } .err { color: #ef4444; }
  a { color: #3b82f6; }
  .box { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 1.5rem; max-width: 500px; }
</style>
</head>
<body>
<div class='box'>
  <h2>⚙️ Setup FreelaSkill</h2>
  <p class='ok'>✅ secrets.php créé avec succès !</p>
  <p class='ok'>✅ Groq API configurée automatiquement</p>
  <br>
  <p>Tu peux maintenant tester :</p>
  <ul>
    <li><a href='views/frontoffice/home.php'>🏠 FrontOffice (User)</a></li>
    <li><a href='views/backoffice/dashboard.php'>🛡️ BackOffice (Admin)</a></li>
  </ul>
</div>
</body>
</html>";
