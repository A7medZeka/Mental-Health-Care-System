<?php
/**
 * WellnessResource — entity (UC 23).
 */
class WellnessResource {

    private int    $resourceId;
    private string $title;
    private string $description;
    private string $category;
    private string $contentUrl;
    private ?int   $suggestedMoodScore;

    public function __construct(array $data = []) {
        $this->resourceId         = (int)($data['resource_id'] ?? 0);
        $this->title              = $data['title'] ?? '';
        $this->description        = $data['description'] ?? '';
        $this->category           = $data['category'] ?? 'General';
        $this->contentUrl         = $data['content_url'] ?? '';
        $this->suggestedMoodScore = isset($data['suggested_mood_score']) ? (int)$data['suggested_mood_score'] : null;
    }

    public function getResourceId(): int          { return $this->resourceId; }
    public function getTitle(): string            { return $this->title; }
    public function getDescription(): string      { return $this->description; }
    public function getCategory(): string         { return $this->category; }
    public function getContentUrl(): string       { return $this->contentUrl; }
    public function getSuggestedMoodScore(): ?int { return $this->suggestedMoodScore; }
}
