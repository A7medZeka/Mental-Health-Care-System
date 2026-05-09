<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Observers/TherapistObserver.php';
require_once __DIR__ . '/ModerationLog.php';
require_once __DIR__ . '/TherapistMatch.php';
require_once __DIR__ . '/Appointment.php'; // ربط كلاس المواعيد الجديد

class Therapist extends User {
    private int $therapist_id;
    private string $specialization;
    private string $languages;
    private string $license_status;
    private $license_expiry_date;
    private int $experience_years;
    private float $rating;
    private float $hourly_rate;
    private string $availability_schedule;
    private bool $is_verified;

    // العلاقات القديمة المحفوظة (متبوظش القديم)
    private TherapistObserver $observer;
    private array $moderation_logs = [];
    private array $therapist_matches = [];

    // ==========================================================
    // تحقيق علاقة الـ 0..* (accepts) الموضحة في الرسمة
    // ==========================================================
    private array $appointments = [];

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->therapist_id = (int) ($data['therapist_id'] ?? 0);
            $this->specialization = $data['specialization'] ?? '';
            $this->languages = $data['languages'] ?? '';
            $this->license_status = $data['license_status'] ?? '';
            $this->license_expiry_date = $data['license_expiry_date'] ?? null;
            $this->experience_years = (int) ($data['experience_years'] ?? 0);
            $this->rating = (float) ($data['rating'] ?? 0.0);
            $this->hourly_rate = (float) ($data['hourly_rate'] ?? 0.0);
            $this->availability_schedule = $data['availability_schedule'] ?? '';
            $this->is_verified = (bool) ($data['is_verified'] ?? false);
        }
        $this->observer = new TherapistObserver($this->therapist_id);
    }

    // دوال المواعيد الجديدة (الربط الجديد)
    public function addAppointment(Appointment $appointment): void {
        $this->appointments[] = $appointment;
    }

    public function getAppointments(): array {
        return $this->appointments;
    }

    // الحفاظ على الـ Getters القديمة
    public function getTherapistId(): int { return $this->therapist_id; }
    public function getSpecialization(): string { return $this->specialization; }
    public function getLanguages(): string { return $this->languages; }
    public function getExperienceYears(): int { return $this->experience_years; }
    public function getRating(): float { return $this->rating; }

    // الحفاظ على دوال العلاقات السابقة
    public function addModerationLog(ModerationLog $log): void { $this->moderation_logs[] = $log; }
    public function getModerationLogs(): array { return $this->moderation_logs; }
    public function addTherapistMatch(TherapistMatch $match): void { $this->therapist_matches[] = $match; }
    public function getTherapistMatches(): array { return $this->therapist_matches; }
}