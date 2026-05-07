<?php

/**
 * Interface for therapist license management operations
 * Implements ISP by focusing only on license-related operations
 */
interface AdminTherapistLicenseManagerInterface {
    
    /**
     * Renew therapist license with optional credential update
     * @param int $therapist_id
     * @param string $new_expiry
     * @param string|null $credential_path
     * @return bool
     */
    public function renewTherapistLicense(int $therapist_id, string $new_expiry, ?string $credential_path = null): bool;
    
    /**
     * Check if therapist is verified
     * @param int $therapist_id
     * @return bool
     */
    public function isTherapistVerified(int $therapist_id): bool;
    
    /**
     * Get therapists with licenses expiring within specified days
     * @param int $days
     * @return array
     */
    public function getExpiringLicenses(int $days = 30): array;
}
