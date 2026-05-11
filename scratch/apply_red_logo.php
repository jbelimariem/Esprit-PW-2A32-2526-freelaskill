<?php
$frontFiles = [
    'front_contrat_list.php',
    'front_contrat_details.php',
    'front_contrat_form.php',
    'front_rules_list.php',
    'front_rules_form.php',
    'front_contrat_index.php',
    'front_rules_index.php',
];
$baseFront = "c:\\xampp\\htdocs\\freelaskill\\Views\\Frontoffice\\";

$adminFiles = [
    'admin_contrat.php',
    'admin_contrat_form.php',
    'admin_contrat_list.php',
    'admin_export_pdf.php',
    'admin_rules_form.php',
    'admin_rules_list.php',
    'admin_approbations.html',
    'admin_archivage.html',
    'admin_dashboard.html',
    'admin_litiges.html'
];
$baseAdmin = "c:\\xampp\\htdocs\\freelaskill\\Views\\Backoffice\\";

$blueLogo = <<<HTML
            <i class="fa-solid fa-shapes text-tech-blue" style="color: var(--tech-blue)"></i>
            Freela<span>Skill</span>
HTML;
$blueLogo2 = <<<HTML
            <i class="fa-solid fa-shapes" style="color: var(--tech-blue); font-size: 1.5rem;"></i>
            <div style="font-weight: 700; font-size: 1.25rem; color: white;">Freela<span style="color: var(--tech-blue);">Skill</span></div>
HTML;
$redLogoAdmin = <<<HTML
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L18 10H6L12 2Z" fill="#EF4444" />
                <circle cx="6" cy="18" r="4" fill="#EF4444" />
                <rect x="14" y="14" width="8" height="8" rx="1.5" fill="#EF4444" />
            </svg>
            <div style="font-weight: 700; font-size: 1.25rem; color: white;">Freela<span style="color: var(--tech-blue);">Skill</span></div>
HTML;
$redLogoFront = <<<HTML
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L18 10H6L12 2Z" fill="#EF4444" />
                <circle cx="6" cy="18" r="4" fill="#EF4444" />
                <rect x="14" y="14" width="8" height="8" rx="1.5" fill="#EF4444" />
            </svg>
            <div style="font-weight: 700; font-size: 1.25rem; color: white; display: inline-block;">Freela<span style="color: var(--tech-blue);">Skill</span></div>
HTML;

foreach ($frontFiles as $f) {
    if (!file_exists($baseFront . $f)) continue;
    $c = file_get_contents($baseFront . $f);
    $c = str_replace($blueLogo2, $redLogoFront, $c);
    file_put_contents($baseFront . $f, $c);
    echo "Updated Front Logo: $f\n";
}

foreach ($adminFiles as $f) {
    if (!file_exists($baseAdmin . $f)) continue;
    $c = file_get_contents($baseAdmin . $f);
    $c = str_replace($blueLogo, $redLogoAdmin, $c);
    file_put_contents($baseAdmin . $f, $c);
    echo "Updated Back Logo: $f\n";
}

echo "Done.\n";
?>
