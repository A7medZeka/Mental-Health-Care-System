<?php
/**
 * IntakeForm — «Immutable» entity (Section 3.2).
 *
 * Set all fields in constructor. No setters. All fields effectively final.
 * version increments by creating a NEW object, never modifying the old one.
 */
class IntakeForm {

    private int    $formId;
    private int    $patientId;
    private string $responses;   // JSON string
    private float  $totalScore;
    private string $submissionDate;

    public function __construct(
        int     $formId,
        int     $patientId,
        string  $responses,
        float   $totalScore,
        ?string $submissionDate = null
    ) {
        if ($patientId <= 0) {
            throw new \InvalidArgumentException('IntakeForm: patientId is required.');
        }
        if (empty($responses)) {
            throw new \InvalidArgumentException('IntakeForm: responses cannot be empty.');
        }

        $this->formId         = $formId;
        $this->patientId      = $patientId;
        $this->responses      = $responses;
        $this->totalScore     = $totalScore;
        $this->submissionDate = $submissionDate ?? date('Y-m-d H:i:s');
    }

    // ── Getters only ─────────────────────────────────────────────────────
    public function getFormId(): int        { return $this->formId; }
    public function getPatientId(): int     { return $this->patientId; }
    public function getResponses(): string  { return $this->responses; }
    public function getTotalScore(): float  { return $this->totalScore; }
    public function getSubmissionDate(): string { return $this->submissionDate; }

    /**
     * Decode responses JSON to array.
     */
    public function getDecodedResponses(): array {
        $decoded = json_decode($this->responses, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Factory — create from database row.
     */
    public static function fromDatabase(array $data): self {
        return new self(
            (int)($data['form_id'] ?? $data['intake_id'] ?? 0),
            (int)($data['patient_id'] ?? 0),
            $data['respones'] ?? $data['responses'] ?? '{}',
            (float)($data['total_score'] ?? 0.0),
            $data['submission_date'] ?? null
        );
    }

    // ── Immutability enforcement ─────────────────────────────────────────
    public function __set($name, $value) {
        throw new \Exception("IntakeForm is immutable — cannot modify property '{$name}'.");
    }

    private function __clone() {}
}
