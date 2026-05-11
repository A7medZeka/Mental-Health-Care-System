<?php
require_once __DIR__ . '/../Interfaces/IStateMachine.php';
require_once __DIR__ . '/Therapist.php';

class ForumPost implements IStateMachine {
    private int $post_id;
    private int $parent_post_id;
    private int $user_id;
    private string $author_pseudonym;
    private string $category;
    private string $content;
    private int $like_count;
    private int $smile_count;
    private bool $is_flagged;
    private string $status;
    private $created_at;
    private array $replies = [];
    private ?Therapist $assigned_therapist;

    public function __construct(array $data, ?Therapist $therapist = null) {
        $this->post_id = (int) ($data['post_id'] ?? 0);
        $this->parent_post_id = (int) ($data['parent_post_id'] ?? 0);
        $this->user_id = (int) ($data['user_id'] ?? 0);
        $this->author_pseudonym = $data['author_pseudonym'] ?? 'Anonymous';
        $this->category = $data['category'] ?? 'General';
        $this->content = $data['content'] ?? '';
        $this->status  = $data['status'] ?? 'Published';
        $this->like_count = (int) ($data['like_count'] ?? 0);
        $this->smile_count = (int) ($data['smile_count'] ?? 0);
        $this->is_flagged = (bool) ($data['is_flagged'] ?? false);
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');

        $this->assigned_therapist = $therapist;
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
    public function getAssignedTherapist(): ?Therapist { return $this->assigned_therapist; }
    public function assignTherapist(Therapist $therapist): void { $this->assigned_therapist = $therapist; }
    public function addReply(ForumPost $reply): void { $this->replies[] = $reply; }
    public function getReplies(): array { return $this->replies; }
    public function getPostId(): int { return $this->post_id; }
    public function getParentPostId(): int { return $this->parent_post_id; }
    public function getUserId(): int { return $this->user_id; }
    public function getAuthorPseudonym(): string { return $this->author_pseudonym; }
    public function getCategory(): string { return $this->category; }
    public function getContent(): string { return $this->content; }
    public function getLikeCount(): int { return $this->like_count; }
    public function getSmileCount(): int { return $this->smile_count; }
    public function isFlagged(): bool { return $this->is_flagged; }
    public function getState(): string { return $this->status; }
    public function getCreatedAt() { return $this->created_at; }
}