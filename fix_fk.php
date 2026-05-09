<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = config::getConnexion();
    echo "Fixing foreign key for commande_produit...\n";
    
    // Attempt to drop the existing FK, catching the constraint error if the name differs
    // Normally it's commande_produit_ibfk_1
    $pdo->exec("ALTER TABLE commande_produit DROP FOREIGN KEY commande_produit_ibfk_1");
    echo "Dropped old constraint.\n";
    
} catch(Exception $e) {
    echo "Notice: Could not drop constraint (maybe already dropped or different name). " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE commande_produit 
        ADD CONSTRAINT commande_produit_ibfk_1 
        FOREIGN KEY (idCommande) REFERENCES commande(idCommande) ON DELETE CASCADE");
    echo "Successfully added ON DELETE CASCADE!\n";
} catch(Exception $e) {
    echo "Error adding constraint: " . $e->getMessage() . "\n";
}
