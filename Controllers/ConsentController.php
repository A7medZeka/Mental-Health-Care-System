<?php
// Controllers/ConsentController.php
require_once __DIR__ . '/../Models/Repositories/ConsentRepository.php';
require_once __DIR__ . '/../Models/Repositories/PatientProfileRepository.php';

/**
 * ConsentController — UC 7: Capture Legal Consents.
 *
 * SD flow:
 *   Patient → ConsentController.recordConsent(patientId, docName, version)
 *     → ConsentRepository.signConsent()
 *     → PatientRepository.updateConsentStatus()
 *     → return confirmation
 *
 *   System → ConsentController.checkConsentStatus(patientId)
 *     → [alt] not signed → blockAccess("Consent required")
 */
class ConsentController {

    private ConsentRepository        $consentRepo;
    private PatientProfileRepository $patientRepo;

    public function __construct(ConsentRepository $consentRepo, PatientProfileRepository $patientRepo) {
        $this->consentRepo = $consentRepo;
        $this->patientRepo = $patientRepo;
    }

    /**
     * +recordConsent(patientId, docName, version) : void
     */
    public function recordConsent(int $patientId, string $docName, string $version): array {
        $result = $this->consentRepo->signConsent($patientId, $docName, $version);

        if ($result['success']) {
            $this->patientRepo->updateConsentStatus($patientId, 'Signed', date('Y-m-d H:i:s'));
        }

        return $result;
    }

    /**
     * +checkConsentStatus(patientId) : Boolean
     */
    public function checkConsentStatus(int $patientId): bool {
        return $this->consentRepo->checkConsentStatus($patientId);
    }

    /**
     * +promptReSign(patientId) : void
     * Called when document version changes.
     */
    public function promptReSign(int $patientId): void {
        $this->patientRepo->updateConsentStatus($patientId, 'Pending', date('Y-m-d H:i:s'));
    }

    /**
     * +blockAccess(message) : void
     * Returns error response when consent is missing.
     */
    public function blockAccess(string $message = 'Consent required before proceeding.'): array {
        return ['success' => false, 'message' => $message, 'blocked' => true];
    }
}
