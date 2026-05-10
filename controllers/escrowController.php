<?php
/**
 * escrowController.php
 * Gère les actions AJAX du système Escrow (dépôt, libération, remboursement).
 * Appelé via fetch() depuis le front et le back.
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/EscrowService.php';

header('Content-Type: application/json; charset=utf-8');

$pdo     = config::getConnexion();
$escrow  = new EscrowService($pdo);
$action  = $_POST['action'] ?? $_GET['action'] ?? '';
$id      = intval($_POST['id_contrat'] ?? $_GET['id_contrat'] ?? 0);
$role    = $_SESSION['user_role'] ?? 'client';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID contrat manquant.']);
    exit;
}

switch ($action) {

    case 'deposer':
        // Seul le client peut déposer
        $result = $escrow->deposerPaiement($id, $role);
        echo json_encode($result);
        break;

    case 'liberer':
        // Seul le client peut valider et libérer
        $result = $escrow->libererPaiement($id, $role);
        echo json_encode($result);
        break;

    case 'rembourser':
        // Admin ou client peut demander un remboursement
        $commentaire = $_POST['commentaire'] ?? '';
        $result = $escrow->rembourserPaiement($id, $commentaire, $role);
        echo json_encode($result);
        break;

    case 'statut':
        // Retourner le statut actuel
        $statut = $escrow->getStatutPaiement($id);
        echo json_encode([
            'success' => true,
            'statut'  => $statut,
            'label'   => EscrowService::getLabel($statut),
            'color'   => EscrowService::getColor($statut),
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
}
exit;
