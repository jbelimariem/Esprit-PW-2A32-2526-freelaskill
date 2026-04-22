<?php
// Models/FreelancerDetail.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/JobOffer.php';

class FreelancerDetail {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function getJob($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM offres_emploi WHERE id = ? AND statut = 'approved'");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new JobOffer($row) : null;
    }
}
