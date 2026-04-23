<?php
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../config.php';

echo "=== DIAGNOSTIC COMMANDE ===\n\n";

try {
    $pdo = config::getConnexion();
    echo "✅ Connexion BD OK (freelaskill)\n\n";

    // 1. Lister les tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables existantes:\n";
    foreach ($tables as $t) echo "  - $t\n";
    echo "\n";

    // 2. Créer la table commande si elle n'existe pas
    $pdo->exec("CREATE TABLE IF NOT EXISTS commande (
        idCommande INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL DEFAULT 1,
        date_commande DATE NOT NULL,
        statut VARCHAR(50) NOT NULL DEFAULT 'en_attente',
        adresse_livraison TEXT,
        montant_total DECIMAL(10,2) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ Table 'commande' prête\n";

    // 3. Créer la table commande_produit si elle n'existe pas
    $pdo->exec("CREATE TABLE IF NOT EXISTS commande_produit (
        id INT AUTO_INCREMENT PRIMARY KEY,
        idCommande INT NOT NULL,
        idProduit INT NOT NULL,
        quantite INT NOT NULL DEFAULT 1,
        prix_unitaire DECIMAL(10,2) NOT NULL DEFAULT 0,
        FOREIGN KEY (idCommande) REFERENCES commande(idCommande) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ Table 'commande_produit' prête\n\n";

    // 4. Tester un insert de commande
    $stmt = $pdo->prepare("INSERT INTO commande (user_id, date_commande, statut, adresse_livraison, montant_total) VALUES (1, CURDATE(), 'en_attente', 'Test Adresse', 99.00)");
    $stmt->execute();
    $testId = $pdo->lastInsertId();
    echo "✅ INSERT commande OK → ID: $testId\n";

    // 5. Chercher un produit existant
    $prod = $pdo->query("SELECT idProduit, nom FROM produit LIMIT 1")->fetch();
    if ($prod) {
        echo "✅ Produit trouvé: [{$prod['idProduit']}] {$prod['nom']}\n";
        $stmt2 = $pdo->prepare("INSERT INTO commande_produit (idCommande, idProduit, quantite, prix_unitaire) VALUES (?, ?, 1, 50.00)");
        $stmt2->execute([$testId, $prod['idProduit']]);
        echo "✅ INSERT commande_produit OK\n\n";
    } else {
        echo "⚠️ Aucun produit dans la table produit — les lignes commande_produit ne seront pas créées\n\n";
    }

    // 6. Supprimer la commande de test
    $pdo->exec("DELETE FROM commande WHERE idCommande = $testId");
    echo "✅ Commande test supprimée\n\n";

    // 7. Vérifier la structure de commande
    echo "Structure table commande:\n";
    $cols = $pdo->query("DESCRIBE commande")->fetchAll();
    foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";
    echo "\nStructure table commande_produit:\n";
    $cols2 = $pdo->query("DESCRIBE commande_produit")->fetchAll();
    foreach ($cols2 as $c) echo "  {$c['Field']} ({$c['Type']})\n";

    echo "\n=== TOUT EST OK — L'API devrait fonctionner ===\n";

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
