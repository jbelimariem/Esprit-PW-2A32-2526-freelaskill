<?php
require_once __DIR__ . '/../config.php';
$pdo = config::getConnexion();

// Migration 1 : Colonnes de signature
try {
    $pdo->exec('ALTER TABLE contrat ADD COLUMN freelance_info VARCHAR(255) DEFAULT NULL, ADD COLUMN signature_client VARCHAR(255) DEFAULT NULL, ADD COLUMN signature_freelance VARCHAR(255) DEFAULT NULL;');
    echo "Migration 1 : Colonnes de signature ajoutées.<br>";
} catch (PDOException $e) {
    echo "Migration 1 (déjà appliquée) : " . $e->getMessage() . "<br>";
}

// Migration 2 : titre_contrat dans rules
try {
    $pdo->exec('ALTER TABLE rules ADD COLUMN titre_contrat VARCHAR(255) DEFAULT NULL;');
    echo "Migration 2a : Colonne titre_contrat ajoutée.<br>";
} catch (PDOException $e) {
    echo "Migration 2a (déjà appliquée) : " . $e->getMessage() . "<br>";
}
try {
    $pdo->exec('UPDATE rules r LEFT JOIN contrat c ON r.id_contrat = c.id_contrat SET r.titre_contrat = c.titre WHERE r.id_contrat IS NOT NULL;');
    echo "Migration 2b : Données migrées.<br>";
} catch (PDOException $e) {
    echo "Migration 2b : " . $e->getMessage() . "<br>";
}
try {
    $pdo->exec('ALTER TABLE rules DROP FOREIGN KEY rules_ibfk_1;');
    echo "Migration 2c : FK supprimée.<br>";
} catch (PDOException $e) {
    echo "Migration 2c (pas de FK) : " . $e->getMessage() . "<br>";
}
try {
    $pdo->exec('ALTER TABLE rules DROP COLUMN id_contrat;');
    echo "Migration 2d : Colonne id_contrat supprimée.<br>";
} catch (PDOException $e) {
    echo "Migration 2d (déjà supprimée) : " . $e->getMessage() . "<br>";
}

// ── Migration 3 : Table notifications ────────────────────────────────
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id_notification INT AUTO_INCREMENT PRIMARY KEY,
            id_contrat      INT NOT NULL,
            titre_contrat   VARCHAR(255) NOT NULL,
            ancien_statut   VARCHAR(50)  NOT NULL,
            nouveau_statut  VARCHAR(50)  NOT NULL,
            message         TEXT         NOT NULL,
            lu              TINYINT(1)   NOT NULL DEFAULT 0,
            date_creation   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_lu (lu),
            INDEX idx_contrat (id_contrat)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Migration 3 : Table notifications créée.<br>";
} catch (PDOException $e) {
    echo "Migration 3 (déjà créée) : " . $e->getMessage() . "<br>";
}

// ── Migration 4 : Table contrat_versions ─────────────────────────────
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contrat_versions (
            id_version      INT AUTO_INCREMENT PRIMARY KEY,
            id_contrat      INT          NOT NULL,
            version_number  INT          NOT NULL DEFAULT 1,
            titre           VARCHAR(255) NOT NULL,
            description     TEXT,
            budget          DECIMAL(10,2),
            delai           INT,
            statut          VARCHAR(50),
            freelance_info  VARCHAR(255),
            modifie_par     VARCHAR(100) DEFAULT 'admin',
            date_version    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_contrat_version (id_contrat, version_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Migration 4 : Table contrat_versions créée.<br>";
} catch (PDOException $e) {
    echo "Migration 4 (déjà créée) : " . $e->getMessage() . "<br>";
}

// ── Migration 5 : Escrow — statut_paiement dans contrat ──────────────
try {
    $pdo->exec("ALTER TABLE contrat ADD COLUMN statut_paiement ENUM('en_attente','bloque','libere','rembourse') NOT NULL DEFAULT 'en_attente' AFTER statut;");
    echo "Migration 5a : Colonne statut_paiement ajoutée.<br>";
} catch (PDOException $e) {
    echo "Migration 5a (déjà appliquée) : " . $e->getMessage() . "<br>";
}

// ── Migration 6 : Table escrow_transactions ───────────────────────────
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS escrow_transactions (
            id_transaction  INT AUTO_INCREMENT PRIMARY KEY,
            id_contrat      INT            NOT NULL,
            montant         DECIMAL(10,2)  NOT NULL,
            type_action     ENUM('depot','blocage','liberation','remboursement') NOT NULL,
            statut_avant    VARCHAR(50)    NOT NULL,
            statut_apres    VARCHAR(50)    NOT NULL,
            commentaire     TEXT           DEFAULT NULL,
            effectue_par    VARCHAR(100)   DEFAULT 'client',
            date_action     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_contrat (id_contrat)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Migration 6 : Table escrow_transactions créée.<br>";
} catch (PDOException $e) {
    echo "Migration 6 (déjà créée) : " . $e->getMessage() . "<br>";
}

// ── Migration 7 : Tables email (config + logs) ───────────────────────
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS email_config (
            id      INT AUTO_INCREMENT PRIMARY KEY,
            cle     VARCHAR(50)  NOT NULL UNIQUE,
            valeur  TEXT         NOT NULL,
            updated_at DATETIME  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    // Valeurs par défaut
    $pdo->exec("INSERT IGNORE INTO email_config (cle, valeur) VALUES
        ('smtp_user',     'freelaskill.notifications@gmail.com'),
        ('smtp_password', ''),
        ('from_name',     'FreelaSkill')
    ");
    echo "Migration 7a : Table email_config créée.<br>";
} catch (PDOException $e) {
    echo "Migration 7a (déjà créée) : " . $e->getMessage() . "<br>";
}

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS email_logs (
            id_log      INT AUTO_INCREMENT PRIMARY KEY,
            id_contrat  INT          NOT NULL,
            type_email  VARCHAR(50)  NOT NULL,
            to_email    VARCHAR(255) NOT NULL,
            subject     VARCHAR(500) NOT NULL,
            statut      ENUM('envoye','echec','simule') NOT NULL DEFAULT 'simule',
            date_envoi  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_contrat (id_contrat),
            INDEX idx_type (type_email),
            INDEX idx_date (date_envoi)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Migration 7b : Table email_logs créée.<br>";
} catch (PDOException $e) {
    echo "Migration 7b (déjà créée) : " . $e->getMessage() . "<br>";
}

echo "<br><strong>✅ Toutes les migrations terminées.</strong>";
