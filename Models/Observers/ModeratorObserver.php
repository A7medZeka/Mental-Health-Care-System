<?php
require_once __DIR__ . '/../../Interfaces/Observer/IObserver.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

use Interfaces\Observer\IObserver;

class ModeratorObserver implements IObserver {

    private int $moderator_id;
    private $db;
    public function __construct(int $moderator_id) {
        $this->moderator_id = $moderator_id;
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }
    public function update(string $event, array $data): void {
        if ($event === 'POST_FLAGGED') {
            $this->handlePostFlagged($data);
        } elseif ($event === 'CRISIS_KEYWORD') {
            $this->handleCrisisKeyword($data);
        }
    }
    public function handlePostFlagged(array $data): void {
        $postId = $data['post_id'];
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'Alert')");
        $stmt->execute([$this->moderator_id, "New post flagged for review: #$postId"]);
    }
    public function handleCrisisKeyword(array $data): void {
        $text = $data['content'] ?? 'Unknown content';
        $stmt = $this->db->prepare("INSERT INTO audit_logs (action, severity, description) VALUES ('System Alert', 'Critical', ?)");
        $stmt->execute(["Crisis keyword detected. Content: $text"]);
    }
}