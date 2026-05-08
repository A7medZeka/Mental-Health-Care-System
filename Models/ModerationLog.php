<?php
class ModerationLog
{
    private int $log_id;
    private int $post_id;
    private int $moderator_id;
    private string $action_taken;
    private string $note;
    private $created_at;

    public function __construct(array $data = [])
    {
        $this->log_id = $data['log_id'] ?? 0;
        $this->post_id = $data['post_id'] ?? 0;
        $this->moderator_id = $data['moderator_id'] ?? 0;
        $this->action_taken = $data['action_taken'] ?? '';
        $this->note = $data['note'] ?? '';
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
    }
    public function getLogId(): int { return $this->log_id; }
    public function getPostId(): int { return $this->post_id; }
    public function getModeratorId(): int { return $this->moderator_id; }
    public function getActionTaken(): string { return $this->action_taken; }
    public function getNote(): string { return $this->note; }
}