<?php
$dir = __DIR__ . '/Views/Backoffice';
$files = glob($dir . '/*.php');
$count = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Check if the link already exists
    if (strpos($content, 'admin_contrat.php') !== false) {
        continue;
    }
    
    // Find the mes_achats.php link
    $pattern = '/(<a href="mes_achats\.php"\s+class="submenu-item">.*?<\/a>)/is';
    
    // Replacement string
    $replacement = "$1\n                <a href=\"admin_contrat.php\" class=\"submenu-item\"><i class=\"fa-solid fa-file-signature\"></i> Gestion Contrats</a>";
    
    $newContent = preg_replace($pattern, $replacement, $content);
    
    if ($newContent !== null && $newContent !== $content) {
        file_put_contents($file, $newContent);
        $count++;
        echo "Updated: " . basename($file) . "\n";
    }
}

echo "Total updated: $count\n";
