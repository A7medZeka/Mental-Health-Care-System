<?php
// Controllers/SessionController.php

require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Models/Appointment.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Session.php'; // استدعاء الموديل للإدارة

class SessionController {
    private SingletonDatabase $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance();
    }

    /**
     * +transitionState() : Boolean
     * تحقيق علاقة "manages": الكنترولر يتحكم في حالة كائن الجلسة
     */
    public function transitionState(int $sessionId, string $newState): bool {
        // 1. جلب بيانات الجلسة من الداتابيز
        $sql = "SELECT * FROM sessions WHERE session_id = ?";
        $stmt = $this->db->execute($sql, [$sessionId]);
        $data = $stmt->fetch();

        if (!$data) return false;

        // 2. إنشاء كائن الجلسة (Manages instance)
        // لاحظ إننا بنمرر الداتا بس عشان نحافظ على علاقة الـ contains therapist القديمة
        $session = new Session($data);

        // 3. استخدام الـ Logic بتاع الـ State Machine
        if ($session->transition($newState)) {
            // 4. تحديث الداتابيز بالحالة الجديدة (updates status)
            $updateSql = "UPDATE sessions SET session_state = ? WHERE session_id = ?";
            $this->db->execute($updateSql, [$session->getState(), $sessionId]);
            return true;
        }

        return false;
    }

    /**
     * +endSession() : void
     */
    public function endSession(int $sessionId): void {
        // إدارة إنهاء الجلسة وإصدار الفاتورة أوتوماتيكياً
        if ($this->transitionState($sessionId, 'Completed')) {
            $this->generateInvoice($sessionId); // Trigger billing
        }
    }

    /**
     * +admitPatient() : void
     */
    public function admitPatient(int $sessionId, int $patientId): void {
        // تغيير الحالة لـ Live عند دخول المريض
        $this->transitionState($sessionId, 'Live');
    }

    // ==========================================================
    // علاقة "triggers invoice"
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