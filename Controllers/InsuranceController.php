<?php
// Controllers/InsuranceController.php
require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Models/Repositories/InsuranceRepository.php';

/**
 * InsuranceController — UC 4: Verify Insurance Eligibility.
 *
 * SD flow:
 *   Patient → InsuranceController.verifyEligibility(providerInfo, therapistId)
 *     → InsuranceRepository.getAcceptedInsurance(therapistId)
 *     → [alt] match found → "Eligible"
 *     → [alt] no match   → "Not Eligible"
 *     → [alt] ambiguous  → flagForManualReview()
 *     → InsuranceRepository.updateEligibilityStatus()
 *     → return status
 */
class InsuranceController {

    private InsuranceRepository $repo;

    public function __construct(InsuranceRepository $repo) {
        $this->repo = $repo;
    }

    /**
     * +verifyEligibility(providerInfo, therapistId) : String
     */
    public function verifyEligibility(array $providerInfo, int $therapistId): string {
        $patientProvider = $providerInfo['provider_name'] ?? '';
        if (empty($patientProvider)) {
            return 'Unknown';
        }

        $accepted = $this->repo->getAcceptedInsurance($therapistId);

        // Exact match
        foreach ($accepted as $provider) {
            if (strcasecmp($provider, $patientProvider) === 0) {
                return 'Eligible';
            }
        }

        // Partial / ambiguous
        foreach ($accepted as $provider) {
            if (stripos($provider, $patientProvider) !== false || stripos($patientProvider, $provider) !== false) {
                return 'PendingReview';
            }
        }

        return 'Not Eligible';
    }

    /**
     * +updateInsurance(patientId, data) : void
     */
    public function updateInsurance(int $patientId, array $data): array {
        return $this->repo->saveInsurance($patientId, $data);
    }

    /**
     * +flagForManualReview(patientId) : void
     */
    public function flagForManualReview(int $patientId): void {
        $this->repo->updateEligibilityStatus($patientId, 'PendingReview');
    }

    /**
     * Full flow: verify + persist
     */
    public function processVerification(int $patientId, array $providerInfo, int $therapistId): array {
        $status = $this->verifyEligibility($providerInfo, $therapistId);

        $this->repo->updateEligibilityStatus($patientId, $status);

        if ($status === 'PendingReview') {
            $this->flagForManualReview($patientId);
        }

        return ['success' => true, 'eligibility_status' => $status];
    }
}
