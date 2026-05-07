<?php

/**
 * Interface for patient management operations
 * Implements ISP by focusing only on patient-related admin operations
 */
interface AdminPatientManagerInterface {
    
    /**
     * Get total count of all patients
     * @return int
     */
    public function getTotalPatients(): int;
    
    /**
     * Retrieve all patients
     * @return array
     */
    public function getAllPatients(): array;
    
    /**
     * Get specific patient by ID
     * @param int $patient_id
     * @return array|null
     */
    public function getPatientById(int $patient_id): ?array;
    
    /**
     * Get all active patients
     * @return array
     */
    public function getActivePatients(): array;
    
    /**
     * Update patient status with validation
     * @param int $patientId
     * @param string $currentStatus
     * @param string $newStatus
     * @return array ['success' => bool, 'message' => string]
     */
    public function updatePatientStatus(int $patientId, string $currentStatus, string $newStatus): array;
    
    /**
     * Upload and validate intake form for patient
     * @param int $patientId
     * @param array $file
     * @param int $uploadedBy
     * @return array ['success' => bool, 'message' => string]
     */
    public function uploadIntakeForm(int $patientId, array $file, int $uploadedBy): array;
}
