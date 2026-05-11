<?php
/**
 * Contrôleur des versions de contrat.
 * Gère l'affichage de l'historique et la comparaison de versions.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/ContratVersion.php';
require_once __DIR__ . '/../Models/ContratVersionRepository.php';
require_once __DIR__ . '/../Models/Contrat.php';

// Pour getContratById
require_once __DIR__ . '/contratController.php';

$pdo         = config::getConnexion();
$versionRepo = new ContratVersionRepository($pdo);

$action    = $_GET['action'] ?? 'history';
$idContrat = isset($_GET['id_contrat']) ? intval($_GET['id_contrat']) : null;
$idVersion = isset($_GET['id_version']) ? intval($_GET['id_version']) : null;

switch ($action) {

    case 'history':
        // Liste toutes les versions d'un contrat
        if (!$idContrat) { die('id_contrat requis.'); }
        $versions = $versionRepo->findByContrat($idContrat);
        $contrat  = getContratById($idContrat);
        break;

    case 'diff':
        // Compare deux versions côte à côte
        if (!$idContrat) { die('id_contrat requis.'); }
        $versions = $versionRepo->findLastTwo($idContrat);
        $contrat  = getContratById($idContrat);
        break;

    case 'compare':
        // Compare une version spécifique avec la version actuelle
        if (!$idVersion || !$idContrat) { die('Paramètres requis.'); }
        $oldVersion = $versionRepo->findById($idVersion);
        $contrat    = getContratById($idContrat);
        $versions   = $versionRepo->findByContrat($idContrat);
        break;
}
