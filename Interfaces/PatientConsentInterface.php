<?php

/**
 * PatientConsentInterface — ISP-compliant contract for legal consent operations.
 *
 * SOLID: I – Interface Segregation.
 */
interface PatientConsentInterface
{
    /** Get all consent records for a patient. */
    public function getConsents(int $patientId): array;

    /** Record a signed consent document. */
    public function signConsent(int $patientId, string $documentName, string $version): array;
}
