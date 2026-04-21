-- ============================================================
--  FreelaSkill — Module User
--  Table: users
--  Compatible with config.php (MySQL / MariaDB)
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `nom`        VARCHAR(80)  NOT NULL,
    `prenom`     VARCHAR(80)  NOT NULL,
    `email`      VARCHAR(150) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('freelancer', 'client') NOT NULL DEFAULT 'client',
    `bio`        TEXT         NULL,
    `avatar`     VARCHAR(255) NULL DEFAULT '',
    `status`     ENUM('active', 'banned', 'pending') NOT NULL DEFAULT 'active',
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  Données de test (optionnel)
-- ============================================================
INSERT INTO `users` (`nom`, `prenom`, `email`, `password`, `role`, `bio`, `status`) VALUES
('Ben Salah',  'Amine',  'amine@test.com',  '$2y$10$YourHashHere1...', 'freelancer', 'Développeur Full-Stack passionné.', 'active'),
('Chaabane',   'Leila',  'leila@test.com',  '$2y$10$YourHashHere2...', 'client',     'CEO chez TechStart Tunisia.',       'active'),
('Gharbi',     'Mehdi',  'mehdi@test.com',  '$2y$10$YourHashHere3...', 'freelancer', 'Designer UI/UX & motion.',          'active'),
('Mansouri',   'Sana',   'sana@test.com',   '$2y$10$YourHashHere4...', 'client',     '',                                  'banned');
-- Note: passwords are hashed via password_hash() — these are placeholders.
-- Use the register form to create real accounts.
