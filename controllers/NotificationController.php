<?php
// controllers/NotificationController.php

require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/config.php';

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
        $sql = "SELECT * FROM notification WHERE user_id = ? ORDER BY date_notif DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        // Comme bindValue peut être chiant avec execute([$id]), je refais proprement :
        $sql = "SELECT * FROM notification WHERE user_id = $user_id ORDER BY date_notif DESC LIMIT $limit OFFSET $offset";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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

    public function markAsRead($idNotification) {
        $sql = "UPDATE notification SET is_read = 1 WHERE idNotification = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idNotification]);
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
