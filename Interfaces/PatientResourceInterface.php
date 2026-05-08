<?php

/**
 * PatientResourceInterface — ISP-compliant contract for wellness resources.
 *
 * SOLID: I – Interface Segregation.
 */
interface PatientResourceInterface
{
    /** Get all accessible resources for a patient. */
    public function getResources(int $patientId): array;

    /** Log resource usage for analytics. */
    public function logResourceUsage(int $patientId, int $resourceId, int $durationMinutes): bool;
}
