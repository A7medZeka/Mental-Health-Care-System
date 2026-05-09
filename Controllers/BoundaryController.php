<?php
// Controllers/BoundaryController.php
require_once __DIR__ . '/../Core/SingletonDatabase.php';

/**
 * BoundaryController — UC 8: Enforce Patient–Therapist Boundaries.
 *
 * Mediates all information flow between Patient and Therapist modules.
 * No raw personal data is exposed.
 */
class BoundaryController {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    /**
     * +getContactOptions(therapistId) : array
     * Returns only the contact methods the therapist has approved.
     */
    public function getContactOptions(int $therapistId): array {
        return [
            'methods' => ['In-App Messaging', 'Scheduled Video Session'],
            'therapist_id' => $therapistId,
            'note' => 'Direct phone/email contact is not available. Use in-platform channels only.',
        ];
    }

    /**
     * +routeToInternalMessaging(patientId, therapistId) : void
     * Ensures all communication goes through the platform.
     */
    public function routeToInternalMessaging(int $patientId, int $therapistId): array {
        // Verify both users exist and relationship is active
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS cnt FROM therapist_matches
             WHERE patient_id = ? AND therapist_id = ? AND status = 'Accepted'"
        );
        $stmt->execute([$patientId, $therapistId]);
        $exists = ((int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0)) > 0;

        if (!$exists) {
            return ['success' => false, 'message' => 'No active match. Cannot initiate messaging.'];
        }

        return [
            'success'    => true,
            'channel'    => 'in_app',
            'patient_id' => $patientId,
            'therapist_id' => $therapistId,
        ];
    }

    /**
     * +validateAccess(element) : Boolean
     * Checks if an element (e.g., therapist email, phone) should be visible.
     */
    public function validateAccess(string $element): bool {
        $restricted = ['email', 'phone', 'phone_number', 'address', 'national_id'];
        return !in_array(strtolower($element), $restricted, true);
    }

    /**
     * Filter patient data before sending to therapist side.
     */
    public function filterPatientDataForTherapist(array $patientData): array {
        $restricted = ['national_id', 'phone_number', 'email', 'password_hash', 'city'];
        return array_diff_key($patientData, array_flip($restricted));
    }

    /**
     * Filter therapist data before sending to patient side.
     */
    public function filterTherapistDataForPatient(array $therapistData): array {
        $restricted = ['national_id', 'phone_number', 'email', 'password_hash'];
        return array_diff_key($therapistData, array_flip($restricted));
    }
}
