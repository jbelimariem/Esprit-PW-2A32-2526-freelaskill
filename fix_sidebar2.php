<?php
$dir = __DIR__ . '/Views/Backoffice';
$files = glob($dir . '/*.php');
$count = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Remove the submenu link I added earlier
    $content = preg_replace('/^[ \t]*<a href="admin_contrat\.php"\s+class="submenu-item">.*?<\/a>\r?\n?/m', '', $content);
    
    // Add it as a main menu item after Freelancers, if not already there
    if (strpos($content, '<a href="admin_contrat.php" class="nav-item"') === false && strpos($content, 'admin_freelancers.php') !== false) {
        
        $pattern = '/(<a href="admin_freelancers\.php"[^>]*>.*?<\/a>)/i';
        
        $replacement = "$1\n        <a href=\"admin_contrat.php\" class=\"nav-item\" style=\"text-decoration:none;\"><i class=\"fa-solid fa-file-signature\"></i> Contrats</a>";
        
        $content = preg_replace($pattern, $replacement, $content, 1);
    }
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        $count++;
        echo "Updated: " . basename($file) . "\n";
    }
}
echo "Total updated: $count\n";
