<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Core/ObserverPattern.php';
require_once __DIR__ . '/../Core/ImmutablePattern.php';
require_once __DIR__ . '/../Interfaces/PatientRepositoryInterface.php';
require_once __DIR__ . '/../Interfaces/PatientWellnessInterface.php';
require_once __DIR__ . '/../Interfaces/PatientJournalInterface.php';
require_once __DIR__ . '/../Interfaces/PatientIntakeInterface.php';
require_once __DIR__ . '/../Interfaces/PatientAppointmentInterface.php';
require_once __DIR__ . '/../Interfaces/PatientProfileInterface.php';
require_once __DIR__ . '/../Interfaces/PatientPaymentInterface.php';
require_once __DIR__ . '/../Interfaces/PatientConsentInterface.php';
require_once __DIR__ . '/../Interfaces/PatientResourceInterface.php';


class Patient extends User implements
    PatientProfileInterface,
    PatientIntakeInterface
{
    // new edit i did not put setters cause you have already updatePreferences so you dont need it
    // but if there is a problem try to put setters
    protected $pref_language;
    protected $pref_therapist_gender;
    protected $pref_cultural_background;
    protected $pref_specialization;

    public function getPrefLanguage() {
        return $this->pref_language;
    }

    public function getPrefTherapistGender() {
        return $this->pref_therapist_gender;
    }

    public function getPrefCulturalBackground() {
        return $this->pref_cultural_background;
    }

    public function getPrefSpecialization() {
        return $this->pref_specialization;
    }

    // This method loads the preferences from your 'patients' database table
    public function loadPatientData($patient_id) {
        $stmt = $this->conn->prepare(
            "SELECT pref_language, pref_therapist_gender, pref_cultural_background, pref_specialization
             FROM patients 
             WHERE patient_id = ?"
        );
        $stmt->execute([$patient_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $this->user_id = $patient_id;
            $this->pref_language = $data['pref_language'] ?? '';
            $this->pref_specialization = $data['pref_specialization'] ?? '';
            $this->pref_therapist_gender = $data['pref_therapist_gender'] ?? '';
            $this->pref_cultural_background = $data['pref_cultural_background'] ?? '';
            return true;
        }
        return false;
    }
    private function toImmutable(array $row): ImmutablePatientRecord
    {
        return new ImmutablePatientRecord(
            (int)$row['user_id'],
            $row['username'],
            $row['email'],
            $row['first_name'],
            $row['last_name'],
            $row['status'],
            $row['created_at'],
            isset($row['age'])    ? (int)$row['age']    : null,
            $row['gender']        ?? null,
            $row['phone_number']  ?? null,
            $row['city']          ?? null,
            null,
            null,
            $row['updated_at']    ?? null
        );
    }

    public function getProfile(int $patientId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.*, p.pref_language, p.pref_therapist_gender,
                    p.pref_cultural_background, p.pref_specialization,
                    p.triage_status, p.level_of_care, p.diagnosis,
                    p.general_notes, p.consent_status
             FROM   users u
             LEFT JOIN patients p ON p.patient_id = u.user_id
             WHERE  u.user_id = ? AND u.role = 'Patient'"
        );
        $stmt->execute([$patientId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateProfile(int $patientId, array $data): array
    {
        $allowed = ['first_name','last_name','phone_number','city','gender'];
        $fields  = [];
        $params  = [];

        foreach ($data as $k => $v) {
            if (in_array($k, $allowed, true)) {
                $fields[] = "$k = ?";
                $params[] = $v;
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'message' => 'No updatable fields provided.'];
        }

        $params[] = $patientId;
        $stmt = $this->conn->prepare(
            "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ? AND role = 'Patient'"
        );
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Profile updated successfully.'];
    }

    public function updatePreferences(int $patientId, array $prefs): array
    {
        $allowed = ['pref_language','pref_therapist_gender','pref_cultural_background','pref_specialization'];
        $fields  = [];
        $params  = [];

        foreach ($prefs as $k => $v) {
            if (in_array($k, $allowed, true)) {
                $fields[] = "$k = ?";
                $params[] = $v;
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid preference fields.'];
        }

        // Upsert pattern — patients row may not exist yet
        $check = $this->conn->prepare("SELECT patient_id FROM patients WHERE patient_id = ?");
        $check->execute([$patientId]);
        if (!$check->fetch()) {
            $this->conn->prepare("INSERT INTO patients (patient_id) VALUES (?)")->execute([$patientId]);
        }

        $params[] = $patientId;
        $this->conn->prepare(
            "UPDATE patients SET " . implode(', ', $fields) . " WHERE patient_id = ?"
        )->execute($params);

        return ['success' => true, 'message' => 'Preferences saved.'];
    }


    public function getMyTherapist(int $patient_id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.user_id, u.first_name, u.last_name, u.email,
                    t.specialization, t.languages, t.experience_years,
                    t.rating, t.is_verified, t.availability_schedule
             FROM   therapist_matches tm
             JOIN   users u ON u.user_id = tm.therapist_id
             JOIN   therapists t ON t.therapist_id = tm.therapist_id
             WHERE  tm.patient_id = ? AND tm.status = 'Accepted'
             ORDER  BY tm.match_id DESC
             LIMIT  1"
        );
        $stmt->execute([$patient_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMyAppointments(int $patient_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT a.*, u.first_name, u.last_name
             FROM   appointments a
             JOIN   users u ON u.user_id = a.therapist_id
             WHERE  a.patient_id = ?
             ORDER  BY a.appointment_date DESC"
        );
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getUpcomingAppointments(int $patient_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT a.*, u.first_name, u.last_name,
                    s.session_id, s.session_state, s.meeting_link
             FROM   appointments a
             JOIN   users u ON u.user_id = a.therapist_id
             LEFT JOIN sessions s ON s.appointment_id = a.appointment_id
             WHERE  a.patient_id = ?
               AND  a.appointment_date >= NOW()
               AND  a.status IN ('Scheduled','Confirmed')
             ORDER  BY a.appointment_date ASC"
        );
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getPastAppointments(int $patient_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT a.*, u.first_name, u.last_name,
                    cn.encrypted_content AS session_notes
             FROM   appointments a
             JOIN   users u ON u.user_id = a.therapist_id
             LEFT JOIN sessions s ON s.appointment_id = a.appointment_id
             LEFT JOIN clinical_notes cn ON cn.session_id = s.session_id
             WHERE  a.patient_id = ?
               AND  (a.appointment_date < NOW() OR a.status IN ('Completed','No-Show','Cancelled'))
             ORDER  BY a.appointment_date DESC"
        );
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getIntakeFormStatus(int $patient_id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM intake_forms WHERE patient_id = ? ORDER BY submission_date DESC LIMIT 1"
        );
        $stmt->execute([$patient_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function submitIntakeForm(int $patient_id, $form_data): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO intake_forms (patient_id, respones, submission_date)
             VALUES (?, ?, NOW())"
        );
        return $stmt->execute([$patient_id, json_encode($form_data)]);
    }
}


class PatientAppointmentManager extends Patient implements PatientAppointmentInterface
{
    private PatientStatusManager $statusManager;

    public function __construct()
    {
        parent::__construct();
        $this->statusManager = new PatientStatusManager();
        $this->statusManager->attach(new PatientStatusDatabaseLogger());
        $this->statusManager->attach(new PatientStatusEmailNotifier());
        $this->statusManager->attach(new PatientStatusAuditLogger());
    }

    public function bookAppointment(int $patientId, int $therapistId, string $date, string $sessionType): array
    {
        $matchCheck = $this->conn->prepare(
            "SELECT match_id FROM therapist_matches
             WHERE patient_id = ? AND therapist_id = ? AND status = 'Accepted'"
        );
        $matchCheck->execute([$patientId, $therapistId]);
        if (!$matchCheck->fetch()) {
            return ['success' => false, 'message' => 'This therapist is not your assigned match.'];
        }

        $dupCheck = $this->conn->prepare(
            "SELECT appointment_id FROM appointments
             WHERE patient_id = ? AND therapist_id = ?
               AND appointment_date = ? AND status NOT IN ('Cancelled')"
        );
        $dupCheck->execute([$patientId, $therapistId, $date]);
        if ($dupCheck->fetch()) {
            return ['success' => false, 'message' => 'You already have an appointment at this time.'];
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO appointments (patient_id, therapist_id, appointment_date, session_type, status)
             VALUES (?, ?, ?, ?, 'Scheduled')"
        );
        $stmt->execute([$patientId, $therapistId, $date, $sessionType]);

        return ['success' => true, 'message' => 'Appointment booked successfully.', 'appointment_id' => $this->conn->lastInsertId()];
    }

    public function cancelAppointment(int $sessionId, int $patientId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM appointments WHERE appointment_id = ? AND patient_id = ?"
        );
        $stmt->execute([$sessionId, $patientId]);
        $appt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appt) {
            return ['success' => false, 'message' => 'Appointment not found.'];
        }
        if ($appt['status'] === 'Cancelled') {
            return ['success' => false, 'message' => 'Already cancelled.'];
        }

        $update = $this->conn->prepare(
            "UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ? AND patient_id = ?"
        );
        $update->execute([$sessionId, $patientId]);

        return ['success' => true, 'message' => 'Appointment cancelled.'];
    }

    public function getUpcomingAppointments(int $patient_id): array
    {
        return parent::getUpcomingAppointments($patient_id);
    }

    public function getPastAppointments(int $patientId): array
    {
        return parent::getPastAppointments($patientId);
    }

    public function getAvailableTherapists(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.user_id, u.first_name, u.last_name,
                    t.specialization, t.languages, t.rating,
                    t.availability_schedule, t.hourly_rate
             FROM   therapists t
             JOIN   users u ON u.user_id = t.therapist_id
             WHERE  t.is_verified = 1 AND u.status = 'Active'
             ORDER  BY t.rating DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getMyTherapist(int $patientId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT u.user_id, u.first_name, u.last_name, u.email,
                   t.specialization, t.languages, t.rating
            FROM therapist_matches tm
            JOIN users u ON u.user_id = tm.therapist_id
            JOIN therapists t ON t.therapist_id = tm.therapist_id
            WHERE tm.patient_id = ? AND tm.status = 'Accepted'
            LIMIT 1
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}


class PatientWellnessManager extends Patient implements PatientWellnessInterface
{
    public function logMood($patient_id, $mood_score, $mood_label, $notes = '')
    {
        $check = $this->conn->prepare(
            "SELECT entry_id FROM mood_entries WHERE patient_id = ? AND entry_date = CURDATE()"
        );
        $check->execute([$patient_id]);
        if ($check->fetch()) {
            $stmt = $this->conn->prepare(
                "UPDATE mood_entries SET mood_score = ?, mood_label = ?, note = ?
                 WHERE patient_id = ? AND entry_date = CURDATE()"
            );
            return $stmt->execute([$mood_score, $mood_label, $notes, $patient_id]);
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO mood_entries (patient_id, mood_score, mood_label, note, entry_date)
             VALUES (?, ?, ?, ?, CURDATE())"
        );
        return $stmt->execute([$patient_id, $mood_score, $mood_label, $notes]);
    }

    public function getMoodHistory($patient_id, $days = 30)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM mood_entries
             WHERE  patient_id = ? AND entry_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             ORDER  BY entry_date ASC"
        );
        $stmt->execute([$patient_id, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTodayMood($patient_id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM mood_entries WHERE patient_id = ? AND entry_date = CURDATE()"
        );
        $stmt->execute([$patient_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMyGoals($patient_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM wellness_goals WHERE patient_id = ? ORDER BY goal_id DESC"
        );
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createGoal($patient_id, $goal_title, $target_days, $category)
    {
        $validCats = ['Mindfulness','Exercise','Sleep','Nutrition','Other'];
        if (!in_array($category, $validCats, true)) {
            return false;
        }
        $stmt = $this->conn->prepare(
            "INSERT INTO wellness_goals (patient_id, title, target_value, category, progress, status)
             VALUES (?, ?, ?, ?, 0, 'In-Progress')"
        );
        return $stmt->execute([$patient_id, $goal_title, $target_days, $category]);
    }

    public function updateGoalProgress($goal_id, $progress)
    {
        $progress  = max(0, min(100, (float)$progress));
        $newStatus = $progress >= 100 ? 'Achieved' : 'In-Progress';
        $stmt = $this->conn->prepare(
            "UPDATE wellness_goals SET progress = ?, status = ? WHERE goal_id = ?"
        );
        return $stmt->execute([$progress, $newStatus, $goal_id]);
    }
}

class PatientJournalManager extends Patient implements PatientJournalInterface
{
    public function getJournalEntries($patient_id, $limit = 10)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM journal_entries
             WHERE  patient_id = ?
             ORDER  BY created_at DESC
             LIMIT  ?"
        );
        $stmt->execute([$patient_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createJournalEntry($patient_id, $title, $content, string $privacy = 'Private')
    {
        $validPrivacy = ['Private', 'ShareWithTherapist'];
        if (!in_array($privacy, $validPrivacy, true)) {
            $privacy = 'Private';
        }
        $stmt = $this->conn->prepare(
            "INSERT INTO journal_entries (patient_id, title, content, privacy_level, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );
        return $stmt->execute([$patient_id, $title, $content, $privacy]);
    }

    public function togglePrivacy(int $entryId, int $patientId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT entry_id, privacy_level FROM journal_entries
             WHERE entry_id = ? AND patient_id = ?"
        );
        $stmt->execute([$entryId, $patientId]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$entry) {
            return ['success' => false, 'message' => 'Entry not found.'];
        }

        $newPrivacy = ($entry['privacy_level'] === 'Private') ? 'ShareWithTherapist' : 'Private';
        $this->conn->prepare(
            "UPDATE journal_entries SET privacy_level = ? WHERE entry_id = ?"
        )->execute([$newPrivacy, $entryId]);

        return ['success' => true, 'new_privacy' => $newPrivacy];
    }
}

class PatientPaymentManager extends Patient implements PatientPaymentInterface
{
    public function getPayments(int $patientId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT p.*,
                    a.appointment_date, a.session_type,
                    u.first_name AS therapist_first, u.last_name AS therapist_last
             FROM   payments p
             LEFT JOIN appointments a ON a.appointment_id = p.appointment_id
             LEFT JOIN users u ON u.user_id = a.therapist_id
             WHERE  p.patient_id = ?
             ORDER  BY p.payment_date DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveCard(int $patientId, array $cardData): array
    {
        $required = ['card_number', 'cvv', 'expiry_date', 'cardholder_name', 'amount'];
        foreach ($required as $field) {
            if (empty($cardData[$field])) {
                return ['success' => false, 'message' => "Missing field: $field"];
            }
        }

        $amount = (float) $cardData['amount'];
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero.'];
        }

        $rawCard = preg_replace('/\D/', '', $cardData['card_number']);
        $masked  = 'xxxx-xxxx-xxxx-' . substr($rawCard, -4);

        $invoice = 'INV-' . strtoupper(substr(md5(uniqid($patientId . time(), true)), 0, 10));

        $this->conn->prepare(
            "INSERT INTO payments
                (invoice_number, patient_id, amount, card_number, cvv, expiry_date, cardholder_name, status, payment_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'Paid', NOW())"
        )->execute([
            $invoice,
            $patientId,
            $amount,
            $masked,
            '***',          
            $cardData['expiry_date'],
            $cardData['cardholder_name'],
        ]);

        return [
            'success'  => true,
            'message'  => 'Payment processed successfully.',
            'invoice'  => $invoice,
            'amount'   => $amount,
            'masked'   => $masked,
        ];
    }

    public function getInsurance(int $patientId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM insurance WHERE patient_id = ? ORDER BY insurance_id DESC LIMIT 1"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function saveInsurance(int $patientId, array $data): array
    {
        $allowed = ['provider_name','policy_number','plan_type','coverage','expiry_date','eligibility_status'];
        $existing = $this->getInsurance($patientId);

        if ($existing) {
            $fields = [];
            $params = [];
            foreach ($data as $k => $v) {
                if (in_array($k, $allowed, true)) {
                    $fields[] = "$k = ?";
                    $params[] = $v;
                }
            }
            if (empty($fields)) return ['success' => false, 'message' => 'No valid fields.'];
            $params[] = $existing['insurance_id'];
            $this->conn->prepare(
                "UPDATE insurance SET " . implode(', ', $fields) . " WHERE insurance_id = ?"
            )->execute($params);
        } else {
            $this->conn->prepare(
                "INSERT INTO insurance (patient_id, provider_name, policy_number, plan_type, coverage, expiry_date, eligibility_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            )->execute([
                $patientId,
                $data['provider_name']      ?? '',
                $data['policy_number']       ?? '',
                $data['plan_type']           ?? '',
                $data['coverage']            ?? '',
                $data['expiry_date']         ?? '',
                $data['eligibility_status']  ?? 'Eligible',
            ]);
        }

        return ['success' => true, 'message' => 'Insurance information saved.'];
    }

    public function submitDispute(int $patientId, int $appointmentId, string $reason, string $description): array
    {
        $validReasons = ['incorrect charge','session not received','Technical issue','Other'];
        if (!in_array($reason, $validReasons, true)) {
            return ['success' => false, 'message' => 'Invalid dispute reason.'];
        }

        $appt = $this->conn->prepare(
            "SELECT appointment_id FROM appointments WHERE appointment_id = ? AND patient_id = ?"
        );
        $appt->execute([$appointmentId, $patientId]);
        if (!$appt->fetch()) {
            return ['success' => false, 'message' => 'Appointment not found.'];
        }

        $code = 'D-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $stmt = $this->conn->prepare(
            "INSERT INTO disputes (appointment_id, dispute_code, raised_by_id, reason, description, status, created_at)
             VALUES (?, ?, ?, ?, ?, 'Under Review', NOW())"
        );
        $stmt->execute([$appointmentId, $code, $patientId, $reason, $description]);

        return ['success' => true, 'message' => "Dispute $code submitted.", 'dispute_code' => $code];
    }
}

class PatientConsentManager extends Patient implements PatientConsentInterface
{
    public function getConsents(int $patientId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM consents WHERE patient_id = ? ORDER BY timestamp DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function signConsent(int $patientId, string $documentName, string $version): array
    {
        $existing = $this->conn->prepare(
            "SELECT consent_id FROM consents
             WHERE patient_id = ? AND document_name = ? AND document_version = ?"
        );
        $existing->execute([$patientId, $documentName, $version]);
        if ($existing->fetch()) {
            return ['success' => false, 'message' => 'This document is already signed.'];
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO consents (patient_id, document_name, document_version, signed_date, timestamp)
             VALUES (?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([$patientId, $documentName, $version]);

        return ['success' => true, 'message' => "$documentName (v$version) signed successfully."];
    }
}

class PatientResourceManager extends Patient implements PatientResourceInterface
{
    public function getResources(int $patientId): array
    {
        $moodStmt = $this->conn->prepare(
            "SELECT mood_score FROM mood_entries WHERE patient_id = ? AND entry_date = CURDATE()"
        );
        $moodStmt->execute([$patientId]);
        $mood = $moodStmt->fetch(PDO::FETCH_ASSOC);
        $moodScore = $mood['mood_score'] ?? null;

        $accessStmt = $this->conn->prepare(
            "SELECT wr.*
             FROM wellness_resources wr
             JOIN resource_access_control rac ON rac.resource_id = wr.resource_id
             WHERE rac.patient_id = ? AND rac.is_allowed = 1
             ORDER BY wr.resource_id ASC"
        );
        $accessStmt->execute([$patientId]);
        $controlled = $accessStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (!empty($controlled)) return $controlled;

        $query = "SELECT * FROM wellness_resources ORDER BY resource_id ASC";
        if ($moodScore !== null) {
            $query = "SELECT *, ABS(suggested_mood_score - ?) AS relevance
                      FROM wellness_resources
                      ORDER BY relevance ASC, resource_id ASC";
        }
        $stmt = $this->conn->prepare($query);
        $moodScore !== null ? $stmt->execute([$moodScore]) : $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function logResourceUsage(int $patientId, int $resourceId, int $durationMinutes): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO resource_usage_logs (patient_id, resource_id, duration_minutes, completed_at)
             VALUES (?, ?, ?, NOW())"
        );
        return $stmt->execute([$patientId, $resourceId, $durationMinutes]);
    }

    public function getResourcesByGoalCategories(int $patientId): array
    {
        require_once __DIR__ . '/Repositories/ResourceRepository.php';
        $resourceRepo = new ResourceRepository();
        return $resourceRepo->getResourcesGroupedByCategory($patientId);
    }
}

class PatientReviewManager extends Patient
{
    private TherapistReview $reviewModel;

    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/TherapistReview.php';
        $this->reviewModel = new TherapistReview();
    }

    public function submitTherapistReview(array $reviewData): array
    {
        $reviewData['patient_id'] = $reviewData['patient_id'] ?? $_SESSION['user_id'] ?? null;
        
        if (!$reviewData['patient_id']) {
            return ['success' => false, 'message' => 'Patient ID required'];
        }

        // Validate required fields
        $required = ['therapist_id', 'rating'];
        foreach ($required as $field) {
            if (empty($reviewData[$field])) {
                return ['success' => false, "message" => "Missing required field: $field"];
            }
        }

        // Check if patient can review this therapist
        if (!$this->reviewModel->canPatientReviewTherapist($reviewData['patient_id'], $reviewData['therapist_id'])) {
            return ['success' => false, 'message' => 'You cannot review this therapist. You must have a completed appointment and have not reviewed before.'];
        }

        return $this->reviewModel->createReview($reviewData);
    }

    public function getMyReviews(int $patientId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT tr.*, u.first_name as therapist_first, u.last_name as therapist_last,
                    DATE_FORMAT(tr.created_at, '%M %d, %Y') as formatted_date
             FROM therapist_reviews tr
             JOIN users u ON u.user_id = tr.therapist_id
             WHERE tr.patient_id = ?
             ORDER BY tr.created_at DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTherapistReviewsForPatient(int $therapistId, int $limit = 10): array
    {
        return $this->reviewModel->getTherapistReviews($therapistId, $limit);
    }

    public function getTherapistRatingStats(int $therapistId): array
    {
        return $this->reviewModel->getTherapistRatingStats($therapistId);
    }

    public function canReviewTherapist(int $patientId, int $therapistId): bool
    {
        return $this->reviewModel->canPatientReviewTherapist($patientId, $therapistId);
    }

    public function getMyReviewForTherapist(int $patientId, int $therapistId): ?array
    {
        return $this->reviewModel->getPatientReviewForTherapist($patientId, $therapistId);
    }

    public function markReviewHelpful(int $reviewId): bool
    {
        return $this->reviewModel->markReviewHelpful($reviewId);
    }
}

class PatientNotificationManager extends Patient
{
    public function getNotifications(int $patientId, int $limit = 20): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM notifications
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$patientId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function markAllRead(int $patientId): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0"
        );
        return $stmt->execute([$patientId]);
    }

    public function getUnreadCount(int $patientId): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = 0"
        );
        $stmt->execute([$patientId]);
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);
    }
}
