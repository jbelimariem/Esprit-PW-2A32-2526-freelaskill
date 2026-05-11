<?php
/**
 * Fix UTF-8 encoding corruption in frontoffice PHP files
 * Run: http://localhost/freelaskill/controllers/fix_encoding.php
 * Delete this file after running.
 */

$dirs = [
    __DIR__ . '/../Views/Frontoffice/',
    __DIR__ . '/../Views/Backoffice/',
];

$map = [
    // Latin-1 double-encoded sequences
    "\xC3\xA9" => "\xC3\xA9", // already correct é - skip
];

// The real fix: files were saved as Latin-1 but read as UTF-8
// Corrupted: Ã© = é (UTF-8 bytes C3 A9 read as Latin-1 then re-encoded)
$badToGood = [
    'Ã©' => 'é',  'Ã¨' => 'è',  'Ã ' => 'à',  'Ã¢' => 'â',
    'Ã®' => 'î',  'Ã´' => 'ô',  'Ã»' => 'û',  'Ã§' => 'ç',
    'Ã¯' => 'ï',  'Ã«' => 'ë',  'Ã¹' => 'ù',  'Ã‰' => 'É',
    'Ã€' => 'À',  'Ã‡' => 'Ç',  'Ã¼' => 'ü',  'Ã±' => 'ñ',
    'Ã¶' => 'ö',  'Ã¤' => 'ä',  'Ã¡' => 'á',  'Ã³' => 'ó',
    'Ã­' => 'í',  'Ãº' => 'ú',  'Ã‹' => 'Ë',  'Ã¦' => 'æ',
    'Å"'  => 'œ',  'Ã¾' => 'þ',  'Ã½' => 'ý',  'Ã¿' => 'ÿ',
    // Punctuation
    'â€"' => '—',  'â€™' => "'",  'â€œ' => '"',  'â€' => '"',
    'â€¦' => '…',  'â†'' => '→',  'Â·'  => '·',  'Â«'  => '«',
    'Â»'  => '»',  'Â '  => ' ',
    // Emoji flags
    "\xF0\x9F\x87\xAB\xF0\x9F\x87\xB7" => '🇫🇷',
    "\xF0\x9F\x87\xAC\xF0\x9F\x87\xA7" => '🇬🇧',
    "\xF0\x9F\x87\xB9\xF0\x9F\x87\xB3" => '🇹🇳',
    'ðŸ‡«ðŸ‡·' => '🇫🇷',
    'ðŸ‡¬ðŸ‡§' => '🇬🇧',
    'ðŸ‡¹ðŸ‡³' => '🇹🇳',
];

$fixed = 0;
$ok    = 0;
$errors = [];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $files = glob($dir . '*.php');
    if (!$files) continue;

    foreach ($files as $file) {
        $original = file_get_contents($file);
        $content  = $original;

        foreach ($badToGood as $bad => $good) {
            $content = str_replace($bad, $good, $content);
        }

        if ($content !== $original) {
            if (file_put_contents($file, $content) !== false) {
                echo "<span style='color:green'>✅ Fixed: " . basename($file) . "</span><br>\n";
                $fixed++;
            } else {
                echo "<span style='color:red'>❌ Error writing: " . basename($file) . "</span><br>\n";
                $errors[] = $file;
            }
        } else {
            echo "<span style='color:#888'>✓ OK: " . basename($file) . "</span><br>\n";
            $ok++;
        }
    }
}

echo "<br><strong style='color:white'>Done — $fixed file(s) fixed, $ok already clean.</strong><br>\n";
if (!empty($errors)) {
    echo "<span style='color:red'>Errors: " . implode(', ', array_map('basename', $errors)) . "</span><br>\n";
}
echo "<br><em style='color:#666'>Delete this file after running.</em>\n";

// Auto-delete this script after running
@unlink(__FILE__);
echo "<br><span style='color:#60A5FA'>✓ This fix script has been automatically deleted.</span>\n";
