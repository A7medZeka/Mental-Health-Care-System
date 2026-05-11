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
        try {
            $this->db->beginTransaction();
            $stmt1 = $this->db->prepare("INSERT INTO appointments (patient_id, therapist_id, appointment_date, session_type, status) VALUES (?, ?, ?, ?, 'Scheduled')");
            $stmt1->execute([$patient_id, $therapist_id, $session_date, $session_type]);
            $appointment_id = $this->db->lastInsertId();
            $stmt2 = $this->db->prepare("INSERT INTO sessions (appointment_id, session_state) VALUES (?, 'Scheduled')");
            $stmt2->execute([$appointment_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    //correction use session_state instead of status
    public function completeSession($session_id) {
        $stmt = $this->db->prepare("UPDATE sessions SET session_state = 'Completed' WHERE session_id = ?");
        return $stmt->execute([$session_id]);
    }
    public function getTherapistSchedule($therapist_id) {
        $stmt = $this->db->prepare("
            SELECT s.*, a.appointment_date, u.first_name, u.last_name
            FROM sessions s 
            JOIN appointments a ON s.appointment_id = a.appointment_id 
            JOIN users u ON a.patient_id = u.user_id
            WHERE a.therapist_id = ? 
              AND a.appointment_date >= CURDATE() 
            ORDER BY a.appointment_date ASC
        ");
        $stmt->execute([$therapist_id]);
        return $stmt->fetchAll();
    }
    public function getTherapistStats($therapist_id) {
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
    public function checkNoShow($therapist_id) {
        $stmt = $this->db->prepare("
        SELECT COUNT(*) as incident_count
        FROM appointments a
        JOIN sessions s ON a.appointment_id = s.appointment_id
        WHERE a.therapist_id = ?
          AND a.appointment_date < (NOW() - INTERVAL 15 MINUTE)
          AND s.session_state = 'Scheduled'
          AND DATE(a.appointment_date) = CURDATE()
    ");
        $stmt->execute([$therapist_id]);
        $result = $stmt->fetch();
        return ($result['incident_count'] > 0);
    }
    public function updateSessionNotes($session_id, $notes) {
        $stmt = $this->db->prepare("UPDATE sessions SET notes = ?, session_state = 'Completed' WHERE session_id = ?");
        return $stmt->execute([$notes, $session_id]);
    }
    public function getMoodSleepCorrelation($patient_id) {
        $stmt = $this->db->prepare("
            SELECT 
                AVG(mood_score) as avg_mood, 
                AVG(sleep_hours) as avg_sleep,
                (AVG(mood_score * sleep_hours) - AVG(mood_score) * AVG(sleep_hours)) / 
                (STDDEV(mood_score) * STDDEV(sleep_hours)) as correlation_coefficient
            FROM mood_entries 
            WHERE patient_id = ?
        ");
        $stmt->execute([$patient_id]);
        return $stmt->fetch();
    }
}