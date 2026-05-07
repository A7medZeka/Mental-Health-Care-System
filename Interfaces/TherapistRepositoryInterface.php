<?php

/**
 * Interface for therapist-specific operations
 * Implements ISP by focusing only on therapist-related operations
 */
interface TherapistRepositoryInterface {
    
    /**
     * Get all patients assigned to therapist
     * @param int $therapist_id
     * @return array
     */
    public function getMyPatients($therapist_id);
    
    /**
     * Get sessions for a specific patient
     * @param int $patient_id
     * @return array
     */
    public function getPatientSessions($patient_id);
    
    /**
     * Create a new session
     * @param int $patient_id
     * @param int $therapist_id
     * @param string $session_date
     * @param string $session_type
     * @return bool
     */
    public function createSession($patient_id, $therapist_id, $session_date, $session_type);
    
    /**
     * Update session notes and mark as completed
     * @param int $session_id
     * @param string $notes
     * @return bool
     */
    public function updateSessionNotes($session_id, $notes);
    
    /**
     * Get therapist's upcoming schedule
     * @param int $therapist_id
     * @return array
     */
    public function getTherapistSchedule($therapist_id);
    
    /**
     * Get therapist statistics
     * @param int $therapist_id
     * @return array
     */
    public function getTherapistStats($therapist_id);
}
