<?php

/**
 * Interface for patient-specific operations
 * Implements ISP by focusing only on patient-related operations
 */
interface PatientRepositoryInterface {
    
    /**
     * Get assigned therapist for a patient
     * @param int $patient_id
     * @return array|null
     */
    public function getMyTherapist($patient_id);
    
    /**
     * Get all appointments for a patient
     * @param int $patient_id
     * @return array
     */
    public function getMyAppointments($patient_id);
    
    /**
     * Get upcoming appointments for a patient
     * @param int $patient_id
     * @return array
     */
    public function getUpcomingAppointments($patient_id);
    
    /**
     * Get past appointments for a patient
     * @param int $patient_id
     * @return array
     */
    public function getPastAppointments($patient_id);
    
    /**
     * Book an appointment
     * @param int $patient_id
     * @param int $therapist_id
     * @param string $session_date
     * @param string $session_type
     * @return bool
     */
    public function bookAppointment($patient_id, $therapist_id, $session_date, $session_type);
    
    /**
     * Cancel an appointment
     * @param int $session_id
     * @param int $patient_id
     * @return bool
     */
    public function cancelAppointment($session_id, $patient_id);
}
