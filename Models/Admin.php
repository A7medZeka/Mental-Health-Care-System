<?php
require_once __DIR__ . '/User.php';

class Admin extends User {

    public function __construct() {
        parent::__construct();
    }


    public function getTotalPatients() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'Patient'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getAllPatients() {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE role = 'Patient' ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAuditLogsCount() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM audit_logs");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }


    public function getAllTherapists() {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE role = 'Therapist' ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function getPendingTherapistsCount(): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM pending_therapists WHERE status = 'Pending'");
        $stmt->execute();
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public function getPendingTherapistsList(): array {
        $stmt = $this->conn->prepare("
            SELECT  id, first_name, last_name, specialization,
                    license_status, city, status, submitted_at,
                    credential_file_path, email, phone_number,
                    years_of_experience, availability_schedule,
                    national_id, gender, date_of_birth, username
            FROM    pending_therapists
            WHERE   status = 'Pending'
            ORDER BY submitted_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPendingTherapistById(int $id): ?array {
        $stmt = $this->conn->prepare("
            SELECT  id, first_name, last_name, username, email,
                    national_id, phone_number, date_of_birth, gender, city,
                    specialization, license_status, years_of_experience,
                    availability_schedule, credential_file_path,
                    status, submitted_at
            FROM    pending_therapists
            WHERE   id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function approveTherapist(int $pending_id): bool {
        $pending = $this->getPendingTherapistById($pending_id);
        if (!$pending) return false;

        $pwStmt = $this->conn->prepare("SELECT password_hash FROM pending_therapists WHERE id = ?");
        $pwStmt->execute([$pending_id]);
        $password_hash = $pwStmt->fetchColumn();

        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO users
                    (first_name, last_name, username, email, password_hash,
                     national_id, phone_number, date_of_birth, gender, city,
                     role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Therapist', 'Active')
            ");
            $stmt->execute([
                $pending['first_name'],
                $pending['last_name'],
                $pending['username'],
                $pending['email'],
                $password_hash,
                $pending['national_id'],
                $pending['phone_number'],
                $pending['date_of_birth'],
                $pending['gender'],
                $pending['city'],
            ]);
            $new_user_id = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare("
                INSERT INTO therapists
                    (therapist_id, specialization, experience_years,
                     availability_schedule, credential_file_path, is_verified)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $new_user_id,
                $pending['specialization'],
                $pending['years_of_experience'],
                $pending['availability_schedule'],
                $pending['credential_file_path'],
            ]);

            $this->conn->prepare("
                UPDATE pending_therapists SET status = 'Approved' WHERE id = ?
            ")->execute([$pending_id]);

            $this->conn->commit();
            return true;

        } catch (Throwable $e) {
            $this->conn->rollBack();
            error_log('[Admin::approveTherapist] ' . $e->getMessage());
            return false;
        }
    }

    public function rejectTherapist(int $pending_id): bool {
        $stmt = $this->conn->prepare("
            UPDATE pending_therapists SET status = 'Rejected' WHERE id = ?
        ");
        $stmt->execute([$pending_id]);
        return $stmt->rowCount() > 0;
    }


    public function getActiveTherapists(): array {
        $stmt = $this->conn->prepare("
            SELECT  t.therapist_id,
                    u.first_name, u.last_name, u.email,
                    t.specialization, t.license_expiry_date,
                    t.is_verified,   t.credential_file_path,
                    t.experience_years, t.availability_schedule
            FROM    therapists t
            JOIN    users u ON u.user_id = t.therapist_id
            WHERE   u.role   = 'Therapist'
              AND   u.status = 'Active'
            ORDER BY t.license_expiry_date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function renewTherapistLicense(int $therapist_id, string $new_expiry, ?string $credential_path = null): bool {
        if ($credential_path !== null) {
            $stmt = $this->conn->prepare("
                UPDATE therapists
                SET    license_expiry_date  = ?,
                       credential_file_path = ?,
                       is_verified          = 1
                WHERE  therapist_id = ?
            ");
            $stmt->execute([$new_expiry, $credential_path, $therapist_id]);
        } else {
            $stmt = $this->conn->prepare("
                UPDATE therapists
                SET    license_expiry_date = ?,
                       is_verified         = 1
                WHERE  therapist_id = ?
            ");
            $stmt->execute([$new_expiry, $therapist_id]);
        }
        return $stmt->rowCount() > 0;
    }

    public function removeTherapist(int $therapist_id): bool {
        $this->conn->beginTransaction();
        try {
            $this->conn->prepare("
                DELETE FROM therapists WHERE therapist_id = ?
            ")->execute([$therapist_id]);

            $this->conn->prepare("
                UPDATE users SET status = 'Inactive' WHERE user_id = ?
            ")->execute([$therapist_id]);

            $this->conn->commit();
            return true;

        } catch (Throwable $e) {
            $this->conn->rollBack();
            error_log('[Admin::removeTherapist] ' . $e->getMessage());
            return false;
        }
    }

 
    public function getVerifiedTherapistList(): array {
        $stmt = $this->conn->prepare("
            SELECT  t.therapist_id,
                    u.username AS therapist_name
            FROM    therapists t
            JOIN    users u ON u.user_id = t.therapist_id
            WHERE   t.is_verified = 1
            ORDER BY u.username
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getTherapistRankingStat(int $therapist_id): array {
        $stmt = $this->conn->prepare("
            SELECT ROUND(AVG(rating), 1) AS avg_rating,
                   COUNT(*)              AS cnt
            FROM   therapist_reviews
            WHERE  therapist_id = ?
        ");
        $stmt->execute([$therapist_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['avg_rating' => 0, 'cnt' => 0];
    }

    
    public function getTherapistPerformanceDetail(int $therapist_id): ?array {
        // Name
        $stmtName = $this->conn->prepare("
            SELECT u.username AS therapist_name
            FROM   therapists t
            JOIN   users u ON u.user_id = t.therapist_id
            WHERE  t.therapist_id = ?
              AND  t.is_verified  = 1
        ");
        $stmtName->execute([$therapist_id]);
        $nameData = $stmtName->fetch(PDO::FETCH_ASSOC);

        if (!$nameData) return null;

        $stmtRev = $this->conn->prepare("
            SELECT COUNT(review_id)      AS total_reviews,
                   ROUND(AVG(rating), 1) AS avg_rating
            FROM   therapist_reviews
            WHERE  therapist_id = ?
        ");
        $stmtRev->execute([$therapist_id]);
        $revData = $stmtRev->fetch(PDO::FETCH_ASSOC);

        return [
            'therapist_id'   => $therapist_id,
            'therapist_name' => $nameData['therapist_name'],
            'total_reviews'  => (int)($revData['total_reviews'] ?? 0),
            'avg_rating'     => (float)($revData['avg_rating']  ?? 0),
            'total_sessions' => 0,   // extend when sessions table link is known
            'no_show_rate'   => 0,
        ];
    }


    public function getTherapistRatingBreakdown(int $therapist_id): array {
        $stmt = $this->conn->prepare("
            SELECT rating, COUNT(*) AS count
            FROM   therapist_reviews
            WHERE  therapist_id = ?
            GROUP BY rating
            ORDER BY rating DESC
        ");
        $stmt->execute([$therapist_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getTherapistRecentFeedback(int $therapist_id): array {
        $stmt = $this->conn->prepare("
            SELECT rating, comment, created_at
            FROM   therapist_reviews
            WHERE  therapist_id = ?
              AND  comment IS NOT NULL
              AND  comment <> ''
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$therapist_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}