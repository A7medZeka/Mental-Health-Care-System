<?php

/**
 * Interface for therapist patient insight operations
 * Implements ISP by focusing only on patient insight-related operations
 */
interface TherapistPatientInsightInterface {
    
    /**
     * Get patient mood entries for therapist review
     * @param int $patient_id
     * @param int $limit
     * @return array
     */
    public function getPatientMoodEntries($patient_id, $limit = 10);
}
