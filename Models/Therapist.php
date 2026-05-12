<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Observers/TherapistObserver.php';
require_once __DIR__ . '/ModerationLog.php';
require_once __DIR__ . '/TherapistMatch.php';
require_once __DIR__ . '/Appointment.php';
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
    private string $cultural;
    private TherapistObserver $observer;
    private array $moderation_logs = [];
    private array $therapist_matches = [];
    private array $appointments = [];

    public function __construct(array $data = []) {
        parent::__construct();
        $this->cultural = '';
        $this->gender = '';
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
            $this->cultural = $data['cultural'] ?? ($data['Cultural'] ?? '');
            $this->gender = $data['gender'] ?? '';
        }
        $this->observer = new TherapistObserver($this->therapist_id);
    }
    public function addAppointment(Appointment $appointment): void {
        $this->appointments[] = $appointment;
    }

    public function getAppointments(): array {
        return $this->appointments;
    }
    public function getTherapistId(): int { return $this->therapist_id; }
    public function getSpecialization(): string { return $this->specialization; }
    public function getLanguages(): string { return $this->languages; }
    public function getExperienceYears(): int { return $this->experience_years; }
    public function getRating(): float { return $this->rating; }
    public function getAvailabilitySchedule(): string { return $this->availability_schedule; }
    public function getCultural(): string { return $this->cultural; }
    public function getGender(): string { return (string)($this->gender ?? ''); }
    public function addModerationLog(ModerationLog $log): void { $this->moderation_logs[] = $log; }
    public function getModerationLogs(): array { return $this->moderation_logs; }
    public function addTherapistMatch(TherapistMatch $match): void { $this->therapist_matches[] = $match; }
    public function getTherapistMatches(): array { return $this->therapist_matches; }

    public function getReviews(int $limit = 10, int $offset = 0): array
    {
        require_once __DIR__ . '/TherapistReview.php';
        $reviewModel = new TherapistReview();
        return $reviewModel->getTherapistReviews($this->therapist_id, $limit, $offset);
    }

    public function getRatingStats(): array
    {
        require_once __DIR__ . '/TherapistReview.php';
        $reviewModel = new TherapistReview();
        return $reviewModel->getTherapistRatingStats($this->therapist_id);
    }

    public function getAverageRating(): float
    {
        return $this->rating;
    }

    public function updateRating(): void
    {
        require_once __DIR__ . '/TherapistReview.php';
        $reviewModel = new TherapistReview();
        $stats = $reviewModel->getTherapistRatingStats($this->therapist_id);
        $this->rating = $stats['average_rating'];
    }

    public function loadTherapistData(int $therapistId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT t.*, u.first_name, u.last_name, u.email, u.date_of_birth, u.gender
             FROM therapists t
             JOIN users u ON u.user_id = t.therapist_id
             WHERE t.therapist_id = ?"
        );
        $stmt->execute([$therapistId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $this->therapist_id = (int) $data['therapist_id'];
            $this->specialization = $data['specialization'] ?? '';
            $this->languages = $data['languages'] ?? '';
            $this->license_status = $data['license_status'] ?? '';
            $this->license_expiry_date = $data['license_expiry_date'] ?? null;
            $this->experience_years = (int) ($data['experience_years'] ?? 0);
            $this->rating = (float) ($data['rating'] ?? 0.0);
            $this->hourly_rate = (float) ($data['hourly_rate'] ?? 0.0);
            $this->availability_schedule = $data['availability_schedule'] ?? '';
            $this->is_verified = (bool) ($data['is_verified'] ?? false);
            $this->cultural = $data['cultural'] ?? '';
            $this->gender = $data['gender'] ?? '';
            $this->first_name = $data['first_name'] ?? '';
            $this->last_name = $data['last_name'] ?? '';
            $this->email = $data['email'] ?? '';
            return true;
        }
        return false;
    }
}
