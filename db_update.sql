CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    bio TEXT,
    avatar VARCHAR(255),
    status VARCHAR(50) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    github_url VARCHAR(255),
    linkedin_url VARCHAR(255),
    cv_url VARCHAR(255),
    portfolio_url VARCHAR(255),
    face_descriptor TEXT
);

INSERT INTO users (nom, prenom, email, password, role, bio, github_url, linkedin_url, cv_url, portfolio_url) VALUES
('Dupont', 'Alexandre', 'alexandre.d@example.com', 'password123', 'freelancer', 'Developpeur Fullstack PHP React', 'https://github.com/alexdupont', 'https://linkedin.com/in/alexdupont', 'https://example.com/cv/alex.pdf', 'https://alexdupont.dev'),
('Martin', 'Sophie', 'sophie.m@example.com', 'password123', 'freelancer', 'UI/UX Designer experimentee', 'https://github.com/sophiem', 'https://linkedin.com/in/sophiemartin', 'https://example.com/cv/sophie.pdf', 'https://sophiemartin.design'),
('Bernard', 'Thomas', 'thomas.b@example.com', 'password123', 'freelancer', 'Expert DevOps et Cloud AWS', 'https://github.com/tbernard', 'https://linkedin.com/in/tbernard', 'https://example.com/cv/thomas.pdf', ''),
('Dubois', 'Julie', 'julie.d@example.com', 'password123', 'freelancer', 'Developpeuse Mobile iOS et Android', 'https://github.com/juliedubois', 'https://linkedin.com/in/juliedubois', 'https://example.com/cv/julie.pdf', 'https://juliedubois.app');
