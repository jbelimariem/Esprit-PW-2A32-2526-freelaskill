<?php
// controllers/CVController.php
require_once __DIR__ . '/../Models/ClientFreelancers.php';

class CVController {
    public function execute($id) {
        $model = new ClientFreelancers();
        $freelancer = $model->getFreelancerById($id);
        
        if (!$freelancer) {
            echo "Freelancer introuvable.";
            exit;
        }

        // Simuler des expériences pour rendre le CV plus réaliste, car la DB n'en a pas
        $experiences = [
            ['title' => 'Développeur Senior', 'company' => 'Tech Solutions Inc.', 'duration' => '2021 - Présent', 'desc' => 'Développement et maintenance d\'applications web full-stack, optimisation des performances.'],
            ['title' => 'Développeur Junior', 'company' => 'Web Agency', 'duration' => '2019 - 2021', 'desc' => 'Création de sites vitrines et e-commerce, intégration HTML/CSS et PHP.'],
        ];

        include __DIR__ . '/../views/frontoffice/cv.view.php';
    }
}
