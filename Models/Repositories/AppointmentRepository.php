<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * AppointmentRepository — appointment queries for Patient side.
 */
class AppointmentRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function getUpcoming(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.first_name AS therapist_first, u.last_name AS therapist_last
             FROM appointments a
             JOIN users u ON u.user_id = a.therapist_id
             WHERE a.patient_id = ? AND a.appointment_date >= NOW()
               AND a.status IN ('Scheduled','Confirmed')
             ORDER BY a.appointment_date ASC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getPast(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.first_name AS therapist_first, u.last_name AS therapist_last
             FROM appointments a
             JOIN users u ON u.user_id = a.therapist_id
             WHERE a.patient_id = ? AND (a.appointment_date < NOW() OR a.status IN ('Completed','Cancelled'))
             ORDER BY a.appointment_date DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createAppointment(int $patientId, int $therapistId, string $date, string $type): array {
        try {
            // Check for conflicts
            if ($this->hasConflict($therapistId, $date)) {
                return ['success' => false, 'message' => 'Time slot conflict detected.'];
            }

            $stmt = $this->db->prepare(
                "INSERT INTO appointments (patient_id, therapist_id, appointment_date, session_type, status)
                 VALUES (?, ?, ?, ?, 'Scheduled')"
            );
            $ok = $stmt->execute([$patientId, $therapistId, $date, $type]);
            if ($ok) {
                $appointmentId = $this->db->lastInsertId();
                $stmt2 = $this->db->prepare("INSERT INTO sessions (appointment_id, session_state) VALUES (?, 'Scheduled')");
                $stmt2->execute([$appointmentId]);
            }
            return ['success' => $ok, 'message' => $ok ? 'Appointment booked.' : 'Booking failed.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelAppointment(int $appointmentId, int $patientId): array {
        $stmt = $this->db->prepare(
            "UPDATE appointments SET status = 'Cancelled'
             WHERE appointment_id = ? AND patient_id = ? AND status IN ('Scheduled','Confirmed')"
        );
        $ok = $stmt->execute([$appointmentId, $patientId]);
        $affected = $stmt->rowCount();
        return [
            'success' => $affected > 0,
            'message' => $affected > 0 ? 'Appointment cancelled.' : 'Cannot cancel this appointment.'
        ];
    }

    public function getTherapistAvailability(int $therapistId): array {
        $stmt = $this->db->prepare(
            "SELECT availability_schedule FROM therapists WHERE therapist_id = ?"
        );
        $stmt->execute([$therapistId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? [$row['availability_schedule']] : [];
    }

    public function hasConflict(int $therapistId, string $date): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS cnt FROM appointments
             WHERE therapist_id = ? AND appointment_date = ? AND status IN ('Scheduled','Confirmed')"
        );
        $stmt->execute([$therapistId, $date]);
        return ((int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0)) > 0;
    }

    public function getMyTherapist(int $patientId): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.user_id, u.first_name, u.last_name, u.email, t.specialization, t.experience_years, t.rating
             FROM therapist_matches tm
             JOIN users u ON u.user_id = tm.therapist_id
             JOIN therapists t ON t.therapist_id = tm.therapist_id
             WHERE tm.patient_id = ? AND tm.status = 'Accepted'
             LIMIT 1"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAvailableTherapists(): array {
        $stmt = $this->db->prepare(
            "SELECT u.user_id, u.first_name, u.last_name, t.specialization, t.experience_years, t.rating, t.availability_schedule
             FROM users u
             JOIN therapists t ON t.therapist_id = u.user_id
             WHERE u.role = 'Therapist' AND u.status = 'Active' AND t.is_verified = 1
             ORDER BY t.rating DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
