<?php

class MailRecipientHelper
{
    public static function getUserEmail(PDO $pdo, $userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return null;
        }

        $lookups = [
            ['users', 'id'],
            ['user', 'idUser'],
            ['user', 'id'],
        ];

        foreach ($lookups as [$table, $column]) {
            try {
                $stmt = $pdo->prepare("SELECT email FROM `$table` WHERE `$column` = ? LIMIT 1");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && !empty($user['email']) && filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                    return $user['email'];
                }
            } catch (Exception $e) {
                // Some project versions use `users`, others use `user`.
            }
        }

        return null;
    }

    public static function groupSellerItemsByEmail(PDO $pdo, array $items, $buyerId = null)
    {
        $buyerId = $buyerId !== null ? (int) $buyerId : null;
        $sellerItems = [];

        foreach ($items as $item) {
            $sellerId = (int) ($item['owner_id'] ?? $item['user_id'] ?? 0);

            if ($sellerId <= 0 || ($buyerId !== null && $sellerId === $buyerId)) {
                continue;
            }

            $sellerEmail = self::getUserEmail($pdo, $sellerId);
            if (!$sellerEmail) {
                continue;
            }

            if (!isset($sellerItems[$sellerEmail])) {
                $sellerItems[$sellerEmail] = [];
            }

            $sellerItems[$sellerEmail][] = $item;
        }

        return $sellerItems;
    }
}
