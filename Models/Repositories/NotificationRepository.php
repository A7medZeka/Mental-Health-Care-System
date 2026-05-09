<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * NotificationRepository — notification queries.
 */
class NotificationRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function getNotifications(int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function markAllRead(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        return $stmt->execute([$userId]);
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);
    }

    public function save(int $userId, string $message, string $type): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, message, type, is_read, created_at) VALUES (?, ?, ?, 0, NOW())"
        );
        return $stmt->execute([$userId, $message, $type]);
    }
}
