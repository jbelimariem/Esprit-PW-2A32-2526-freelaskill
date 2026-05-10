<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=freelaskill', 'root', '');
    $stmt = $pdo->query('SELECT nom, prenom, email, role FROM users LIMIT 10');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
