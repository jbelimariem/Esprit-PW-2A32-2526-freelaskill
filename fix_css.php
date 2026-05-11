<?php
$adminFile = __DIR__ . '/Views/Backoffice/css/admin.css';
$frontFile = __DIR__ . '/Views/Frontoffice/css/front.css';

$css = file_get_contents($adminFile);

// Replace root variables
$css = preg_replace('/:root\s*\{.*?\}/s', ':root {
    --primary: #2563EB;
    --success: #10B981;
    --danger: #EF4444;
    --warning: #F59E0B;
    --bg-main: #080d1a;
    --bg-white: rgba(255,255,255,0.02);
    --text-dark: #F1F5F9;
    --text-light: #94A3B8;
    --border: rgba(255,255,255,0.05);
    
    --sidebar-width: 240px;
    --topbar-height: 64px;
    --shadow: 0 4px 6px -1px rgba(0,0,0,0.2);
    --transition: all 0.3s ease;
}', $css);

// Remove the background: #F8FAFC; from th { ... }
$css = str_replace('background: #F8FAFC;', 'background: transparent;', $css);

$frontLines = file($frontFile);
$appendedCss = "\n/* --- Appended from front.css --- */\n" . implode("", array_slice($frontLines, 257, 574));

// Prepend the dashboard-specific classes before appendedCss
$dashboardClasses = "
.glow-orb { position: absolute; border-radius: 50%; filter: blur(100px); opacity: 0.15; z-index: -1; pointer-events: none; }
.admin-main { margin-left: var(--sidebar-width); padding: 2.5rem; position: relative; z-index: 1; flex: 1; }
.admin-topbar { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; }
.admin-breadcrumb { color: var(--text-light); font-size: 0.85rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem; }
.admin-page-title { font-size: 2rem; font-weight: 700; color: var(--text-dark); }
.admin-page-title span { color: var(--primary); }
.topbar-actions { display: flex; align-items: center; gap: 1rem; }
.admin-badge { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 0.5rem 1rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
.menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.menu-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 1rem; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; transition: var(--transition); text-decoration: none; position: relative; overflow: hidden; }
.menu-card:hover { transform: translateY(-5px); background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.1); box-shadow: 0 10px 30px var(--card-glow, rgba(0,0,0,0.2)); }
.menu-card-icon { width: 50px; height: 50px; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.menu-card-title { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); margin-bottom: 0.5rem; }
.menu-card-desc { font-size: 0.85rem; color: var(--text-light); line-height: 1.5; }
.menu-card-arrow { margin-top: auto; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
.animate-in { animation: fadeIn 0.5s ease-out forwards; }
.delay-1 { animation-delay: 0.1s; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
";

file_put_contents($adminFile, $css . "\n" . $dashboardClasses . "\n" . $appendedCss);

echo "CSS fixed!\n";
