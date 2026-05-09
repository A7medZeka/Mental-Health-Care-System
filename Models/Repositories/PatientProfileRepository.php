<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * PatientRepository — consolidates patient-specific DB operations.
 */
class PatientProfileRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    /**
     * Get full profile record for a patient.
     */
    public function getProfile(int $patientId): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, p.level_of_care, p.triage_status
             FROM users u
             LEFT JOIN patients p ON u.user_id = p.patient_id
             WHERE u.user_id = ? AND u.role = 'Patient'
             LIMIT 1"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Update profile fields (first_name, last_name, phone, city, gender).
     */
    public function updateProfile(int $patientId, array $data): array {
        $allowed = ['first_name', 'last_name', 'phone_number', 'city', 'gender'];
        $sets = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true) && $value !== '') {
                $sets[] = "{$key} = ?";
                $params[] = $value;
            }
        }

        if (empty($sets)) {
            return ['success' => false, 'message' => 'No valid fields to update.'];
        }

        $params[] = $patientId;
        $sql = "UPDATE users SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute($params);

        return ['success' => $ok, 'message' => $ok ? 'Profile updated.' : 'Update failed.'];
    }

    /**
     * Update level of care in patients table.
     */
    public function updateLevelOfCare(int $patientId, string $level): bool {
        $stmt = $this->db->prepare(
            "UPDATE patients SET level_of_care = ? WHERE patient_id = ?"
        );
        return $stmt->execute([$level, $patientId]);
    }

    /**
     * Update triage status.
     */
    public function updateTriageStatus(int $patientId, string $status): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET status = ?, updated_at = NOW() WHERE user_id = ?"
        );
        return $stmt->execute([$status, $patientId]);
    }

    /**
     * Update consent status tracking field.
     */
    public function updateConsentStatus(int $patientId, string $status, string $timestamp): bool {
        $stmt = $this->db->prepare(
            "UPDATE patients SET consent_status = ?, consent_timestamp = ? WHERE patient_id = ?"
        );
        return $stmt->execute([$status, $timestamp, $patientId]);
    }

    /**
     * Update preferences (pref_language, pref_therapist_gender, etc.).
     */
    public function updatePreferences(int $patientId, array $prefs): array {
        $allowed = ['pref_language', 'pref_therapist_gender', 'pref_cultural_background', 'pref_specialization'];
        $sets = [];
        $params = [];

        foreach ($prefs as $key => $value) {
            if (in_array($key, $allowed, true) && $value !== '') {
                $sets[] = "{$key} = ?";
                $params[] = $value;
            }
        }

        if (empty($sets)) {
            return ['success' => false, 'message' => 'No valid preferences.'];
        }

        $params[] = $patientId;
        $sql = "UPDATE patients SET " . implode(', ', $sets) . " WHERE patient_id = ?";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute($params);

        return ['success' => $ok, 'message' => $ok ? 'Preferences updated.' : 'Update failed.'];
    }
}
