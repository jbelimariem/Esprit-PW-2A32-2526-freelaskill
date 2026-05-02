<?php
/**
 * Contrôleur des notifications — endpoints AJAX.
 * Actions : get_unread, mark_read, mark_all_read, get_all
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Models/NotificationRepository.php';

header('Content-Type: application/json; charset=utf-8');

$pdo    = config::getConnexion();
$repo   = new NotificationRepository($pdo);
$action = $_GET['action'] ?? $_POST['action'] ?? 'get_unread';

switch ($action) {

    case 'get_unread':
        $notifications = $repo->findUnread();
        $count         = $repo->countUnread();
        echo json_encode([
            'success' => true,
            'count'   => $count,
            'items'   => array_map(fn(Notification $n) => [
                'id'             => $n->getIdNotification(),
                'id_contrat'     => $n->getIdContrat(),
                'titre_contrat'  => $n->getTitreContrat(),
                'ancien_statut'  => $n->getAncienStatut(),
                'nouveau_statut' => $n->getNouveauStatut(),
                'message'        => $n->getMessage(),
                'icon'           => $n->getIconNouveauStatut(),
                'color'          => $n->getColorNouveauStatut(),
                'date'           => $n->getDateCreation(),
                'date_relative'  => timeAgo($n->getDateCreation()),
            ], $notifications),
        ]);
        break;

    case 'get_all':
        $notifications = $repo->findAll(50);
        echo json_encode([
            'success' => true,
            'items'   => array_map(fn(Notification $n) => [
                'id'             => $n->getIdNotification(),
                'id_contrat'     => $n->getIdContrat(),
                'titre_contrat'  => $n->getTitreContrat(),
                'ancien_statut'  => $n->getAncienStatut(),
                'nouveau_statut' => $n->getNouveauStatut(),
                'message'        => $n->getMessage(),
                'lu'             => $n->isLu(),
                'icon'           => $n->getIconNouveauStatut(),
                'color'          => $n->getColorNouveauStatut(),
                'date_relative'  => timeAgo($n->getDateCreation()),
            ], $notifications),
        ]);
        break;

    case 'mark_read':
        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id > 0) {
            $repo->markAsRead($id);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ID requis.']);
        }
        break;

    case 'mark_all_read':
        $repo->markAllAsRead();
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Action inconnue.']);
}

// ── Helper : temps relatif ────────────────────────────────────────────
function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'À l\'instant';
    if ($diff < 3600)   return floor($diff / 60) . ' min';
    if ($diff < 86400)  return floor($diff / 3600) . 'h';
    if ($diff < 604800) return floor($diff / 86400) . 'j';
    return date('d/m/Y', strtotime($datetime));
}
