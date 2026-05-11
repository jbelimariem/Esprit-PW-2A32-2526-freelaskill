<?php
$frontCssPath = __DIR__ . '/Views/Frontoffice/css/front.css';
$adminCssPath = __DIR__ . '/Views/Backoffice/css/admin.css';

$frontLines = file($frontCssPath);

// We want to extract lines 257 to 830 (0-indexed: 256 to 829).
$linesToAppend = array_slice($frontLines, 257, 574);

$contentToAppend = "\n/* --- Appended from front.css (Forms, Rules, Buttons, Tables) --- */\n" . implode("", $linesToAppend);

file_put_contents($adminCssPath, $contentToAppend, FILE_APPEND);

echo "CSS appended successfully.";
