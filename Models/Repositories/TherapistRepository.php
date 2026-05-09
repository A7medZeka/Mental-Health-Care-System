<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../../Interfaces/TherapistRepositoryInterface.php';
require_once __DIR__ . '/../../Interfaces/TherapistPatientInsightInterface.php';
class TherapistRepository implements TherapistRepositoryInterface, TherapistPatientInsightInterface {
    private $db;
    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function getMyPatients($therapist_id) {
        // نربط جدول المستخدمين بجدول التوافق لنجد المرضى التابعين لهذا المعالج
        $stmt = $this->db->prepare("
        SELECT u.* FROM users u 
        JOIN therapist_matches tm ON u.user_id = tm.patient_id 
        WHERE tm.therapist_id = ? 
          AND tm.status = 'Accepted' 
          AND u.role = 'Patient' 
        ORDER BY u.last_name, u.first_name
    ");
        $stmt->execute([$therapist_id]);
        return $stmt->fetchAll();
    }

    public function getPatientSessions($patient_id) {
        $stmt = $this->db->prepare("
        SELECT s.*, a.appointment_date 
        FROM sessions s 
        JOIN appointments a ON s.appointment_id = a.appointment_id 
        WHERE a.patient_id = ? 
        ORDER BY a.appointment_date DESC
    ");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll();
    }

    public function createSession($patient_id, $therapist_id, $session_date, $session_type) {
        $stmt = $this->db->prepare("INSERT INTO sessions (patient_id, therapist_id, session_date, session_type, status) VALUES (?, ?, ?, ?, 'Scheduled')");
        return $stmt->execute([$patient_id, $therapist_id, $session_date, $session_type]);
    }

    public function updateSessionNotes($session_id, $notes) {
        $stmt = $this->db->prepare("UPDATE sessions SET notes = ?, status = 'Completed' WHERE session_id = ?");
        return $stmt->execute([$notes, $session_id]);
    }

    public function getTherapistSchedule($therapist_id) {
        // ربط جدول الجلسات بالمواعيد للوصول لبيانات المعالج وتاريخ الموعد
        $stmt = $this->db->prepare("
        SELECT s.*, a.appointment_date 
        FROM sessions s 
        JOIN appointments a ON s.appointment_id = a.appointment_id 
        WHERE a.therapist_id = ? 
          AND a.appointment_date >= CURDATE() 
        ORDER BY a.appointment_date ASC
    ");

        $stmt->execute([$therapist_id]);
        return $stmt->fetchAll();
    }

    public function getTherapistStats($therapist_id) {
        // استخدمنا JOIN لربط الجلسات بالمواعيد عشان نوصل للـ therapist_id
        // وكمان استخدمنا session_state بدل status زي ما صلحنا المرة اللي فاتت
        $stmt = $this->db->prepare("SELECT 
        COUNT(*) as total_sessions,
        COUNT(CASE WHEN s.session_state = 'Completed' THEN 1 END) as completed_sessions,
        COUNT(CASE WHEN s.session_state = 'Scheduled' THEN 1 END) as upcoming_sessions
        FROM sessions s
        JOIN appointments a ON s.appointment_id = a.appointment_id
        WHERE a.therapist_id = ?");

        $stmt->execute([$therapist_id]);
        return $stmt->fetch();
    }

    public function getPatientMoodEntries($patient_id, $limit = 10) {
        $stmt = $this->db->prepare("SELECT * FROM mood_entries WHERE patient_id = ? ORDER BY entry_date DESC LIMIT ?");
        $stmt->execute([$patient_id, $limit]);
        return $stmt->fetchAll();
    }
}