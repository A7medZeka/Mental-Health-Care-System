<?php
// Models/TherapistMatch.php
require_once __DIR__ . '/TherapistMatchDetails.php';
class TherapistMatch {
    private int $match_id;
    private int $patient_id;
    private int $therapist_id;
    private float $match_score;
    private string $status;
    private ?TherapistMatchDetails $match_details;
    public function __construct(array $data = [], ?TherapistMatchDetails $details = null) {
        $this->match_id     = (int) ($data['match_id'] ?? 0);
        $this->patient_id   = (int) ($data['patient_id'] ?? 0);
        $this->therapist_id = (int) ($data['therapist_id'] ?? 0);
        $this->status       = $data['status'] ?? 'Pending';
        $this->match_details = $details;
        $this->match_score = $details ? $details->computeWeightedScore() : (float) ($data['match_score'] ?? 0.0);
    }

    /**
     * دالة لإضافة أو تحديث تفاصيل التوافق بعد إنشاء الكائن
     */
    public function setMatchDetails(TherapistMatchDetails $details): void {
        $this->match_details = $details;
        // تحديث الـ Score الأساسي بناءً على التفاصيل الدقيقة
        $this->match_score = $details->computeWeightedScore();
    }

    // Getters
    public function getMatchDetails(): ?TherapistMatchDetails { return $this->match_details; }
    public function getMatchId(): int { return $this->match_id; }
    public function getPatientId(): int { return $this->patient_id; }
    public function getTherapistId(): int { return $this->therapist_id; }
    public function getMatchScore(): float { return $this->match_score; }
    public function getStatus(): string { return $this->status; }
}