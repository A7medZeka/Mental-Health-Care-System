<?php
/**
 * Insurance — Patient entity (UC 4).
 */
class Insurance {

    private int    $insuranceId;
    private int    $patientId;
    private string $providerName;
    private string $policyNumber;
    private string $planType;
    private string $coverage;
    private string $expiryDate;
    private string $eligibilityStatus;

    public function __construct(array $data = []) {
        $this->insuranceId       = (int)($data['insurance_id'] ?? 0);
        $this->patientId         = (int)($data['patient_id'] ?? 0);
        $this->providerName      = $data['provider_name'] ?? '';
        $this->policyNumber      = $data['policy_number'] ?? '';
        $this->planType          = $data['plan_type'] ?? '';
        $this->coverage          = $data['coverage'] ?? '';
        $this->expiryDate        = $data['expiry_date'] ?? '';
        $this->eligibilityStatus = $data['eligibility_status'] ?? 'Unknown';
    }

    // ── Getters ──────────────────────────────────────────────────────────
    public function getInsuranceId(): int        { return $this->insuranceId; }
    public function getPatientId(): int          { return $this->patientId; }
    public function getProviderName(): string    { return $this->providerName; }
    public function getPolicyNumber(): string    { return $this->policyNumber; }
    public function getPlanType(): string        { return $this->planType; }
    public function getCoverage(): string        { return $this->coverage; }
    public function getExpiryDate(): string      { return $this->expiryDate; }
    public function getEligibilityStatus(): string { return $this->eligibilityStatus; }

    // ── Setters (controlled mutation allowed per CD3) ─────────────────────
    public function setEligibilityStatus(string $status): void {
        $valid = ['Eligible', 'Not Eligible', 'Unknown', 'PendingReview'];
        if (!in_array($status, $valid, true)) {
            throw new \InvalidArgumentException("Invalid eligibility status: {$status}");
        }
        $this->eligibilityStatus = $status;
    }
}
