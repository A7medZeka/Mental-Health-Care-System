<?php

/**
 * PatientPaymentInterface — ISP-compliant contract for payment & insurance.
 *
 * SOLID: I – Interface Segregation.
 */
interface PatientPaymentInterface
{
    /** Get all invoices / payments for a patient. */
    public function getPayments(int $patientId): array;

    /** Save a payment card. Returns ['success'=>bool,'message'=>string]. */
    public function saveCard(int $patientId, array $cardData): array;

    /** Get insurance information for a patient. */
    public function getInsurance(int $patientId): ?array;

    /** Save/update insurance details. */
    public function saveInsurance(int $patientId, array $data): array;

    /** Submit a billing dispute. */
    public function submitDispute(int $patientId, int $appointmentId, string $reason, string $description): array;
}
