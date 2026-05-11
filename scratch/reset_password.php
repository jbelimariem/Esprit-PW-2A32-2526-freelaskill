<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=freelaskill', 'root', '');
    $email = 'mariam@gmail.com';
    $password = 'password123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ?, status = 'active' WHERE email = ?");
    $stmt->execute([$hash, $email]);
    
    if ($stmt->rowCount() > 0) {
        echo "Success: Password for $email has been set to '$password' and status set to 'active'.";
    } else {
        echo "Error: User $email not found or no change made.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
