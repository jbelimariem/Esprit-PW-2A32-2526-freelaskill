<?php
$u = 'Models/User.php';
$c = 'controllers/UserController.php';
$u_cont = file_get_contents($u);

$start = '// ==========================================' . "\r\n" . '    // MÉTHODES DE BASE DE DONNÉES (ACTIVE RECORD)' . "\r\n" . '    // ==========================================';
$end = '    // ArrayAccess Implementation for backward compatibility with views';

$pos1 = strpos($u_cont, $start);
$pos2 = strpos($u_cont, $end);

if ($pos1 === false) {
    // try literal \n
    $start = '// ==========================================' . "\n" . '    // MÉTHODES DE BASE DE DONNÉES (ACTIVE RECORD)' . "\n" . '    // ==========================================';
    $pos1 = strpos($u_cont, $start);
}

if ($pos1 !== false && $pos2 !== false) {
    echo "Found markers!\n";
    $methods = substr($u_cont, $pos1, $pos2 - $pos1);
    
    // remove from User.php
    $new_u = substr($u_cont, 0, $pos1) . substr($u_cont, $pos2);
    // Also remove private $pdo; and $this->pdo = config::getConnexion();
    // Wait, let's keep private $pdo; if we want to mimic precisely produit.php, wait no, let's just leave it there or remove it.
    // The user's screenshot had private $pdo in the model. So leave it.
    
    file_put_contents($u, $new_u);
    echo "Models/User.php updated.\n";
    
    $c_cont = file_get_contents($c);
    
    // Replace $userModel usages
    $c_cont = str_replace('$userModel = new User();', '', $c_cont);
    $c_cont = str_replace('$userModel->', '$this->', $c_cont);
    
    if (strpos($c_cont, 'require_once __DIR__ . \'/../config.php\';') === false) {
        $c_cont = str_replace("require_once __DIR__ . '/../Models/User.php';", "require_once __DIR__ . '/../Models/User.php';\nrequire_once __DIR__ . '/../config.php';", $c_cont);
    }
    
    $ins = "    private \$pdo;\n\n    public function __construct() {\n        \$this->pdo = config::getConnexion();\n    }\n\n";
    
    $class_pos = strpos($c_cont, 'class UserController {');
    $class_pos += strlen("class UserController {");
    
    // We need to add after the { 
    if ($c_cont[$class_pos] == "\r") $class_pos++;
    if ($c_cont[$class_pos] == "\n") $class_pos++;
    
    $new_c = substr($c_cont, 0, $class_pos) . "\n" . $ins . $methods . substr($c_cont, $class_pos);
    
    file_put_contents($c, $new_c);
    echo "controllers/UserController.php updated.\n";
} else {
    echo "Markers not found. pos1: " . var_export($pos1, true) . ", pos2: " . var_export($pos2, true) . "\n";
}
