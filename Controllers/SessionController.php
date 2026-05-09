<?php
// Controllers/SessionController.php

/**
 * SessionController — UC 13: Manage Session Lifecycle.
 *
 * SD flow:
 *   Patient checks in → startSession(sessionId)
 *     → transitionState(sessionId, "CheckedIn")
 *   Therapist admits   → admitPatient(sessionId, patientId)
 *     → transitionState(sessionId, "Live")
 *   Therapist ends     → endSession(sessionId)
 *     → transitionState(sessionId, "Completed")
 *     → generateInvoice(sessionId)
 *   System timeout     → detectNoShow(sessionId)
 *     → transitionState(sessionId, "NoShow")
 *     → logNoShow audit entry
 */

require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Models/Appointment.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Session.php';

class SessionController {
    private SingletonDatabase $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance();
    }

    /**
     * +transitionState(sessionId, newState) : Boolean
     * Core state machine gate — delegates to Session model.
     */
    public function transitionState(int $sessionId, string $newState): bool {
        // SD Step 1: fetch session data from DB
        $sql = "SELECT * FROM sessions WHERE session_id = ?";
        $stmt = $this->db->execute($sql, [$sessionId]);
        $data = $stmt->fetch();

        if (!$data) return false;

        // SD Step 2: create Session object (manages instance)
        $session = new Session($data);

        // SD Step 3: delegate to Session::transition() state machine
        if ($session->transition($newState)) {
            // SD Step 4: persist new state to DB
            $updateSql = "UPDATE sessions SET session_state = ? WHERE session_id = ?";
            $this->db->execute($updateSql, [$session->getState(), $sessionId]);
            return true;
        }

        return false;
    }

    /**
     * +startSession(sessionId) : void
     * UC 13 SD: Patient checks in — Scheduled → CheckedIn.
     */
    public function startSession(int $sessionId): void {
        $this->transitionState($sessionId, 'CheckedIn');
    }

    /**
     * +admitPatient(sessionId, patientId) : void
     * UC 13 SD: Therapist admits patient — CheckedIn → Live.
     */
    public function admitPatient(int $sessionId, int $patientId): void {
        $this->transitionState($sessionId, 'Live');
    }

    /**
     * +endSession(sessionId) : void
     * UC 13 SD: Live → Completed, then trigger billing.
     */
    public function endSession(int $sessionId): void {
        if ($this->transitionState($sessionId, 'Completed')) {
            $this->generateInvoice($sessionId);
        }
    }

    /**
     * +detectNoShow(sessionId) : void
     * UC 13 SD Alt path: Scheduled → NoShow when patient doesn't check in.
     */
    public function detectNoShow(int $sessionId): void {
        if ($this->transitionState($sessionId, 'NoShow')) {
            // Log no-show audit entry
            $logSql = "INSERT INTO audit_logs (action, severity, description, created_at)
                       VALUES (?, ?, ?, NOW())";
            $this->db->execute($logSql, [
                'PATIENT_NO_SHOW',
                'Warning',
                json_encode(['session_id' => $sessionId])
            ]);
        }
    }

    // ==========================================================
    // Billing — triggers invoice (existing logic preserved)
    // ==========================================================
    public function generateInvoice(int $sessionId): void {
        $sql = "SELECT patient_id FROM appointments WHERE appointment_id = (SELECT appointment_id FROM sessions WHERE session_id = ?)";
        $stmt = $this->db->execute($sql, [$sessionId]);
        $data = $stmt->fetch();

        if ($data) {
            $payment = new Payment([
                'appointment_id' => $sessionId,
                'patient_id'     => $data['patient_id'],
                'invoice_number' => 'INV-' . time(),
                'amount'         => 200.00,
                'status'         => 'Unpaid'
            ]);

            $saveSql = "INSERT INTO payments (appointment_id, patient_id, invoice_number, amount, status) VALUES (?, ?, ?, ?, ?)";
            $this->db->execute($saveSql, [
                $payment->getAppointmentId(),
                $data['patient_id'],
                $payment->getInvoiceNumber(),
                $payment->getAmount(),
                $payment->getStatus()
            ]);
        }
    }
}