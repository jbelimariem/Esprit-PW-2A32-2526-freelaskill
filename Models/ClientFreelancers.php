<?php
// Models/ClientFreelancers.php
require_once __DIR__ . '/../config.php';

class ClientFreelancers {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

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

    public function getFreelancerById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'freelancer'");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
