<?php
class TherapistMatchDetails {
    private float $specialization_score;
    private float $language_score;
    private float $gender_score;
    private float $availability_score;
    private float $cultural_score;
    private float $total_weighted_score;
    public function __construct(array $data = []) {
        $this->specialization_score = (float) ($data['specialization_score'] ?? 0.0);
        $this->language_score       = (float) ($data['language_score'] ?? 0.0);
        $this->gender_score         = (float) ($data['gender_score'] ?? 0.0);
        $this->availability_score   = (float) ($data['availability_score'] ?? 0.0);
        $this->cultural_score       = (float) ($data['cultural_score'] ?? 0.0);
        $this->total_weighted_score = $this->computeWeightedScore();
    }
    public function computeWeightedScore(): float {
        $this->total_weighted_score = (
            ($this->specialization_score * 0.40) +
            ($this->language_score * 0.20) +
            ($this->availability_score * 0.20) +
            ($this->gender_score * 0.10) +
            ($this->cultural_score * 0.10)
        );
        return $this->total_weighted_score;
    }
    public function getTotalWeightedScore(): float { return $this->total_weighted_score; }
    public function getSpecializationScore(): float { return $this->specialization_score; }
    public function getLanguageScore(): float { return $this->language_score; }
    public function getGenderScore(): float { return $this->gender_score; }
    public function getAvailabilityScore(): float { return $this->availability_score; }
    public function getCulturalScore(): float { return $this->cultural_score; }
}