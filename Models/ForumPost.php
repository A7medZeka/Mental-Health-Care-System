<?php
require_once __DIR__ . '/../Interfaces/IStateMachine.php';
class ForumPost implements IStateMachine {
    private int $post_id;
    private int $parent_post_id;
    private int $user_id;
    private string $author_pseudonym;
    private string $category;
    private string $content;
    private int $like_count = 0;
    private int $smile_count = 0;
    private bool $is_flagged = false;
    private string $status;
    private $created_at;
    public function __construct(array $data) {
        $this->post_id = $data['post_id'] ?? 0;
        $this->parent_post_id = $data['parent_post_id'] ?? 0;
        $this->user_id = $data['user_id'] ?? 0;
        $this->author_pseudonym = $data['author_pseudonym'] ?? 'Anonymous';
        $this->category = $data['category'] ?? 'General';
        $this->content = $data['content'] ?? '';
        $this->status  = $data['status'] ?? 'Published';
        $this->is_flagged = $data['is_flagged'] ?? false;
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
    }
    public function getState(): string {
        return $this->status;
    }
    public function transition(string $newState): bool {
        $allowedTransitions = [
            'Published'    => ['Hidden', 'Flagged'],
            'Flagged'      => ['Under Review', 'Deleted', 'Published'],
            'Under Review' => ['Hidden', 'Deleted', 'Published'],
            'Hidden'       => ['Published', 'Deleted']
        ];
        if (isset($allowedTransitions[$this->status]) &&
            in_array($newState, $allowedTransitions[$this->status])) {
            $this->status = $newState;
            return true;
        }
        return false;
    }
    public function getPostId(): int { return $this->post_id; }
    public function getUserId(): int { return $this->user_id; }
}