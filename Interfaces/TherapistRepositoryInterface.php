<?php
interface TherapistRepositoryInterface {
    public function getMyPatients($therapist_id);
    public function getTherapistSchedule($therapist_id);
    public function getTherapistStats($therapist_id);
    public function createSession($patient_id, $therapist_id, $session_date, $session_type);
}