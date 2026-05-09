<?php
/**
 * Notification — entity (UC 14, 25).
 */
class Notification {

    private int    $notificationId;
    private int    $userId;
    private string $message;
    private string $type;
    private bool   $isRead;
    private string $createdAt;

    public function __construct(array $data = []) {
        $this->notificationId = (int)($data['notification_id'] ?? $data['id'] ?? 0);
        $this->userId         = (int)($data['user_id'] ?? 0);
        $this->message        = $data['message'] ?? '';
        $this->type           = $data['type'] ?? 'General';
        $this->isRead         = (bool)($data['is_read'] ?? false);
        $this->createdAt      = $data['created_at'] ?? date('Y-m-d H:i:s');
    }

    public function getNotificationId(): int { return $this->notificationId; }
    public function getUserId(): int         { return $this->userId; }
    public function getMessage(): string     { return $this->message; }
    public function getType(): string        { return $this->type; }
    public function isRead(): bool           { return $this->isRead; }
    public function getCreatedAt(): string   { return $this->createdAt; }
}
