<?php
// controllers/NotificationController.php

require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../config.php';

class NotificationController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function createData($user_id, $message, $type = 'info') {
        $sql = "INSERT INTO notification (user_id, message, type) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id, $message, $type]);
    }

    public function getByUser($user_id) {
        $sql = "SELECT * FROM notification WHERE user_id = ? ORDER BY date_notif DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUserPaginated($user_id, $limit, $offset) {
        $sql = "SELECT * FROM notification WHERE user_id = :user_id ORDER BY date_notif DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount($user_id) {
        $sql = "SELECT COUNT(*) FROM notification WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function getUnreadCount($user_id) {
        $sql = "SELECT COUNT(*) FROM notification WHERE user_id = ? AND is_read = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function markAsRead($idNotification, $user_id = null) {
        $sql = "UPDATE notification SET is_read = 1 WHERE idNotification = ?";
        $params = [$idNotification];
        if ($user_id !== null) {
            $sql .= " AND user_id = ?";
            $params[] = $user_id;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function markAllAsRead($user_id) {
        $sql = "UPDATE notification SET is_read = 1 WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
    }

    public function deleteData($idNotification) {
        $sql = "DELETE FROM notification WHERE idNotification = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idNotification]);
    }
}

// ── API ROUTING POUR LES NOTIFICATIONS AJAX ───────────────────────────
if (isset($_GET['action']) && basename($_SERVER['PHP_SELF']) === 'notificationController.php') {
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/../Models/NotificationRepository.php';
    $pdo = config::getConnexion();
    $repo = new NotificationRepository($pdo);
    
    $action = $_GET['action'];
    
    if ($action === 'get_unread') {
        $unread = $repo->findUnread();
        $count  = $repo->countUnread();
        
        $items = [];
        foreach ($unread as $n) {
            $items[] = [
                'id'            => $n->getIdNotification(),
                'titre_contrat' => $n->getTitreContrat(),
                'message'       => $n->getMessage(),
                'date_relative' => date('d/m H:i', strtotime($n->getDateCreation())),
                'icon'          => $n->getIconNouveauStatut(),
                'color'         => $n->getColorNouveauStatut()
            ];
        }
        
        echo json_encode(['success' => true, 'count' => $count, 'items' => $items]);
        exit;
    }
    
    if ($action === 'mark_read') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $repo->markAsRead($id);
        }
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($action === 'mark_all_read') {
        $repo->markAllAsRead();
        echo json_encode(['success' => true]);
        exit;
    }
}
