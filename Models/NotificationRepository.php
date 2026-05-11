<?php
require_once __DIR__ . '/NotificationContrat.php';

/**
 * Repository OOP pour les notifications — accès PDO uniquement.
 */
class NotificationRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Crée une notification en base. */
    public function create(NotificationContrat $notification): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO notifications (id_contrat, titre_contrat, ancien_statut, nouveau_statut, message, lu, date_creation)
                 VALUES (:id_contrat, :titre_contrat, :ancien_statut, :nouveau_statut, :message, :lu, NOW())'
            );
            return $stmt->execute([
                'id_contrat'     => $notification->getIdContrat(),
                'titre_contrat'  => $notification->getTitreContrat(),
                'ancien_statut'  => $notification->getAncienStatut(),
                'nouveau_statut' => $notification->getNouveauStatut(),
                'message'        => $notification->getMessage(),
                'lu'             => $notification->isLu() ? 1 : 0,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /** Retourne toutes les notifications non lues. */
    public function findUnread(): array
    {
        try {
            $stmt = $this->pdo->query(
                'SELECT * FROM notifications WHERE lu = 0 ORDER BY date_creation DESC LIMIT 50'
            );
            return array_map([NotificationContrat::class, 'fromArray'], $stmt->fetchAll());
        } catch (PDOException $e) {
            return [];
        }
    }

    /** Retourne toutes les notifications (lues + non lues). */
    public function findAll(int $limit = 100): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM notifications ORDER BY date_creation DESC LIMIT :limit'
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return array_map([NotificationContrat::class, 'fromArray'], $stmt->fetchAll());
        } catch (PDOException $e) {
            return [];
        }
    }

    /** Compte les notifications non lues. */
    public function countUnread(): int
    {
        try {
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM notifications WHERE lu = 0');
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /** Marque une notification comme lue. */
    public function markAsRead(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare('UPDATE notifications SET lu = 1 WHERE id_notification = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /** Marque toutes les notifications comme lues. */
    public function markAllAsRead(): bool
    {
        try {
            return $this->pdo->exec('UPDATE notifications SET lu = 1 WHERE lu = 0') !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /** Supprime les notifications plus vieilles que N jours. */
    public function deleteOlderThan(int $days = 30): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM notifications WHERE date_creation < DATE_SUB(NOW(), INTERVAL :days DAY)'
        );
        return $stmt->execute(['days' => $days]);
    }
}
