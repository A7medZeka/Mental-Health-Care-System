<?php

/**
 * PatientProfileInterface — ISP-compliant contract for profile management.
 *
 * SOLID: I – Interface Segregation.
 */
interface PatientProfileInterface
{
    /** Return full patient profile as an array. */
    public function getProfile(int $patientId): ?array;

    /** Update allowed profile fields. Returns ['success'=>bool,'message'=>string]. */
    public function updateProfile(int $patientId, array $data): array;

    /** Update matching preferences stored in `patients` table. */
    public function updatePreferences(int $patientId, array $prefs): array;
}
