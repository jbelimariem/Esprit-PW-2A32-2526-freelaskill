<?php
require_once 'controllers/config.php';
$pdo = config::getConnexion();
$password = password_hash('123456', PASSWORD_DEFAULT);

$emails = ['malekderbel000@gmail.com', 'malekderbel444@gmail.com', 'alexandre.d@example.com'];

foreach ($emails as $email) {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$password, $email]);
    echo "Password reset for $email\n";
}
?>
