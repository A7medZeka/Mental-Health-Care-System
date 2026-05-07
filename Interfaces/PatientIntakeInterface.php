<?php

/**
 * Interface for patient intake form operations
 * Implements ISP by focusing only on intake-related operations
 */
interface PatientIntakeInterface {
    
    /**
     * Get intake form status for patient
     * @param int $patient_id
     * @return array|null
     */
    public function getIntakeFormStatus($patient_id);
    
    /**
     * Submit intake form for patient
     * @param int $patient_id
     * @param array $form_data
     * @return bool
     */
    public function submitIntakeForm($patient_id, $form_data);
}
