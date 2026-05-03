<?php
// Models/ClientFreelancers.php
require_once __DIR__ . '/../config.php';

class ClientFreelancers {
    private $pdo;

    // Initialise la connexion PDO à la base de données
    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Retourne la liste de tous les freelancers (users avec role='freelancer'), filtrés par nom/prénom/email si recherche active
    public function getAllFreelancers($search = '') {
        if (!empty($search)) {
            $stmt = $this->pdo->prepare("SELECT id, nom, prenom, email, linkedin_url, cv_url FROM users WHERE role = 'freelancer' AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?) ORDER BY nom ASC");
            $likeSearch = "%$search%";
            $stmt->execute([$likeSearch, $likeSearch, $likeSearch]);
            return $stmt->fetchAll();
        } else {
            $stmt = $this->pdo->query("SELECT id, nom, prenom, email, linkedin_url, cv_url FROM users WHERE role = 'freelancer' ORDER BY nom ASC");
            return $stmt->fetchAll();
        }
    }

    // Retourne un seul freelancer par son ID, ou false si introuvable
    public function getFreelancerById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'freelancer'");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
