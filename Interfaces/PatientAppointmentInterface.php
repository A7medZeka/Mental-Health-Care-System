<?php

/**
 * PatientAppointmentInterface — ISP-compliant contract for appointment management.
 *
 * SOLID: I – Interface Segregation (only appointment concerns here).
 */
interface PatientAppointmentInterface
{
    /** Book a new appointment. Returns ['success'=>bool,'message'=>string]. */
    public function bookAppointment(int $patientId, int $therapistId, string $date, string $sessionType): array;

    /** Cancel an appointment owned by this patient. */
    public function cancelAppointment(int $sessionId, int $patientId): array;

    /** Fetch all upcoming (future + Scheduled) appointments for a patient. */
    public function getUpcomingAppointments(int $patientId): array;

    /** Fetch all past / completed appointments for a patient. */
    public function getPastAppointments(int $patientId): array;
}
