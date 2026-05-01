<?php
require_once __DIR__ . '/../config.php';
$pdo = config::getConnexion();

// Migration 1 : Ajout des colonnes de signature sur la table contrat
try {
    $pdo->exec('ALTER TABLE contrat ADD COLUMN freelance_info VARCHAR(255) DEFAULT NULL, ADD COLUMN signature_client VARCHAR(255) DEFAULT NULL, ADD COLUMN signature_freelance VARCHAR(255) DEFAULT NULL;');
    echo "Migration 1 : Colonnes de signature ajoutées.<br>";
} catch (PDOException $e) {
    echo "Migration 1 (déjà appliquée ou erreur) : " . $e->getMessage() . "<br>";
}

// Migration 2 : Remplacement de id_contrat par titre_contrat dans la table rules
try {
    // Ajouter la nouvelle colonne titre_contrat
    $pdo->exec('ALTER TABLE rules ADD COLUMN titre_contrat VARCHAR(255) DEFAULT NULL;');
    echo "Migration 2a : Colonne titre_contrat ajoutée.<br>";
} catch (PDOException $e) {
    echo "Migration 2a (déjà appliquée ou erreur) : " . $e->getMessage() . "<br>";
}

try {
    // Copier les titres de contrat existants dans la nouvelle colonne
    $pdo->exec('UPDATE rules r LEFT JOIN contrat c ON r.id_contrat = c.id_contrat SET r.titre_contrat = c.titre WHERE r.id_contrat IS NOT NULL;');
    echo "Migration 2b : Données migrées de id_contrat vers titre_contrat.<br>";
} catch (PDOException $e) {
    echo "Migration 2b (erreur) : " . $e->getMessage() . "<br>";
}

try {
    // Supprimer la clé étrangère si elle existe (nom peut varier selon le serveur)
    $pdo->exec('ALTER TABLE rules DROP FOREIGN KEY rules_ibfk_1;');
    echo "Migration 2c : Clé étrangère supprimée.<br>";
} catch (PDOException $e) {
    echo "Migration 2c (pas de FK ou déjà supprimée) : " . $e->getMessage() . "<br>";
}

try {
    // Supprimer l'ancienne colonne id_contrat
    $pdo->exec('ALTER TABLE rules DROP COLUMN id_contrat;');
    echo "Migration 2d : Colonne id_contrat supprimée.<br>";
} catch (PDOException $e) {
    echo "Migration 2d (déjà supprimée ou erreur) : " . $e->getMessage() . "<br>";
}

echo "<br><strong>Migration terminée.</strong>";
