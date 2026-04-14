-- ============================================================
-- FreelaSkill — Module Job Offers
-- Base de données : freelaskill
-- ============================================================

-- Créer la table job_offer si elle n'existe pas
CREATE TABLE IF NOT EXISTS `job_offer` (
    `id`            INT          AUTO_INCREMENT PRIMARY KEY,
    `titre`         VARCHAR(255) NOT NULL,
    `description`   TEXT         NOT NULL,
    `competences`   VARCHAR(500) NOT NULL,
    `budget`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `delai`         VARCHAR(100) NOT NULL,
    `statut`        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `client_id`     INT          NOT NULL DEFAULT 1,
    `date_creation` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Données de démonstration (optionnel)
-- ============================================================

INSERT INTO `job_offer` (`titre`, `description`, `competences`, `budget`, `delai`, `statut`, `client_id`) VALUES
('Développeur React.js pour dashboard analytics',
 'Nous cherchons un développeur React.js expérimenté pour créer un tableau de bord analytics complet avec des graphiques interactifs, des filtres dynamiques et une interface responsive. Le projet inclut l\'intégration d\'une API REST existante.',
 'React.js, TypeScript, Chart.js, REST API, CSS Modules',
 2500.00, '2 mois', 'pending', 1),

('Designer UI/UX pour application mobile (iOS/Android)',
 'Studio créatif cherche un designer UI/UX talentueux pour repenser l\'expérience utilisateur de notre application mobile. Vous devrez livrer des wireframes, maquettes haute-fidélité et un design system complet.',
 'Figma, Adobe XD, UI/UX, Design System, Prototypage',
 1800.00, '6 semaines', 'approved', 1),

('Expert SEO & Content Marketing',
 'PME e-commerce tunisienne recherche un expert SEO pour optimiser la visibilité de son site e-commerce. Mission : audit SEO complet, stratégie de contenu, optimisation on-page et reporting mensuel.',
 'SEO, Google Analytics, Semrush, Content Marketing, WordPress',
 900.00, '3 mois', 'approved', 1),

('Développeur PHP/Laravel pour plateforme B2B',
 'Startup fintech recherche un développeur PHP Laravel senior pour développer une plateforme de facturation B2B avec intégration de paiement, gestion des abonnements et portail client.',
 'PHP, Laravel, MySQL, Redis, Stripe API',
 4500.00, '4 mois', 'pending', 1),

('Data Scientist pour modèle de prédiction',
 'Entreprise de logistique cherche un data scientist pour construire un modèle de prédiction de la demande basé sur l\'historique de commandes et les variables saisonnières.',
 'Python, Machine Learning, Pandas, Scikit-learn, Jupyter',
 3200.00, '2 mois', 'rejected', 1);
