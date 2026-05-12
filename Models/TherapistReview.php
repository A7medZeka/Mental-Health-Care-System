<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../Core/SingletonDatabase.php';

class TherapistReview {
    private int $review_id;
    private int $therapist_id;
    private int $patient_id;
    private ?int $appointment_id;
    private float $rating;
    private string $review_text;
    private string $created_at;
    private bool $is_verified;
    private bool $is_moderated;
    private string $moderation_status;
    private int $helpful_count;
    private PDO $conn;

    public function __construct() {
        $this->conn = SingletonDatabase::getInstance()->getConnection();
    }

    public function createReview(array $data): array {
        // Validate rating
        if (!isset($data['rating']) || $data['rating'] < 1.0 || $data['rating'] > 5.0) {
            return ['success' => false, 'message' => 'Rating must be between 1.0 and 5.0'];
        }

        // Check if patient already reviewed this therapist
        $checkStmt = $this->conn->prepare(
            "SELECT review_id FROM therapist_reviews 
             WHERE patient_id = ? AND therapist_id = ?"
        );
        $checkStmt->execute([$data['patient_id'], $data['therapist_id']]);
        
        if ($checkStmt->fetch()) {
            return ['success' => false, 'message' => 'You have already reviewed this therapist'];
        }

        // Verify patient had appointment with therapist (optional but recommended)
        if (!empty($data['appointment_id'])) {
            $apptCheck = $this->conn->prepare(
                "SELECT appointment_id FROM appointments 
                 WHERE appointment_id = ? AND patient_id = ? AND therapist_id = ? 
                 AND status = 'Completed'"
            );
            $apptCheck->execute([$data['appointment_id'], $data['patient_id'], $data['therapist_id']]);
            
            if (!$apptCheck->fetch()) {
                return ['success' => false, 'message' => 'Invalid appointment for review'];
            }
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO therapist_reviews 
             (therapist_id, patient_id, appointment_id, rating, review_text, is_verified, is_moderated, moderation_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')"
        );

        $success = $stmt->execute([
            $data['therapist_id'],
            $data['patient_id'],
            $data['appointment_id'] ?? null,
            $data['rating'],
            $data['review_text'] ?? '',
            $data['is_verified'] ?? false,
            $data['is_moderated'] ?? false
        ]);

        if ($success) {
            $this->updateTherapistAverageRating($data['therapist_id']);
            return [
                'success' => true, 
                'message' => 'Review submitted successfully and pending moderation',
                'review_id' => $this->conn->lastInsertId()
            ];
        }

        return ['success' => false, 'message' => 'Failed to submit review'];
    }

    public function getTherapistReviews(int $therapistId, int $limit = 10, int $offset = 0): array {
        $stmt = $this->conn->prepare(
            "SELECT tr.*, u.first_name, u.last_name,
                    DATE_FORMAT(tr.created_at, '%M %d, %Y') as formatted_date
             FROM therapist_reviews tr
             JOIN users u ON u.user_id = tr.patient_id
             WHERE tr.therapist_id = ? AND tr.moderation_status = 'Approved'
             ORDER BY tr.created_at DESC
             LIMIT ? OFFSET ?"
        );
        
        $stmt->execute([$therapistId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTherapistRatingStats(int $therapistId): array {
        $stmt = $this->conn->prepare(
            "SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
             FROM therapist_reviews 
             WHERE therapist_id = ? AND moderation_status = 'Approved'"
        );
        
        $stmt->execute([$therapistId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($stats && $stats['total_reviews'] > 0) {
            $stats['average_rating'] = round((float)$stats['average_rating'], 1);
            
            // Calculate percentages
            $total = (int)$stats['total_reviews'];
            $stats['five_star_pct'] = round(($stats['five_star'] / $total) * 100);
            $stats['four_star_pct'] = round(($stats['four_star'] / $total) * 100);
            $stats['three_star_pct'] = round(($stats['three_star'] / $total) * 100);
            $stats['two_star_pct'] = round(($stats['two_star'] / $total) * 100);
            $stats['one_star_pct'] = round(($stats['one_star'] / $total) * 100);
        }
        
        return $stats ?: [
            'total_reviews' => 0,
            'average_rating' => 0.0,
            'five_star' => 0, 'four_star' => 0, 'three_star' => 0,
            'two_star' => 0, 'one_star' => 0,
            'five_star_pct' => 0, 'four_star_pct' => 0, 'three_star_pct' => 0,
            'two_star_pct' => 0, 'one_star_pct' => 0
        ];
    }

    public function canPatientReviewTherapist(int $patientId, int $therapistId): bool {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM therapist_reviews 
             WHERE patient_id = ? AND therapist_id = ?"
        );
        $stmt->execute([$patientId, $therapistId]);
        
        if ($stmt->fetchColumn() > 0) {
            return false; // Already reviewed
        }

        // Check if patient is matched with therapist
        $matchStmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM therapist_matches 
             WHERE patient_id = ? AND therapist_id = ? AND status = 'Accepted'"
        );
        $matchStmt->execute([$patientId, $therapistId]);
        
        return $matchStmt->fetchColumn() > 0;
    }

    public function getPatientReviewForTherapist(int $patientId, int $therapistId): ?array {
        $stmt = $this->conn->prepare(
            "SELECT tr.*, a.appointment_date
             FROM therapist_reviews tr
             LEFT JOIN appointments a ON a.appointment_id = tr.appointment_id
             WHERE tr.patient_id = ? AND tr.therapist_id = ?"
        );
        $stmt->execute([$patientId, $therapistId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function markReviewHelpful(int $reviewId): bool {
        $stmt = $this->conn->prepare(
            "UPDATE therapist_reviews SET helpful_count = helpful_count + 1 
             WHERE review_id = ?"
        );
        return $stmt->execute([$reviewId]);
    }

    public function updateReviewModeration(int $reviewId, string $status): bool {
        $validStatuses = ['Approved', 'Rejected', 'Pending'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $stmt = $this->conn->prepare(
            "UPDATE therapist_reviews 
             SET moderation_status = ?, is_moderated = TRUE 
             WHERE review_id = ?"
        );
        $success = $stmt->execute([$status, $reviewId]);
        
        if ($success && $status === 'Approved') {
            // Update therapist average rating
            $reviewStmt = $this->conn->prepare(
                "SELECT therapist_id FROM therapist_reviews WHERE review_id = ?"
            );
            $reviewStmt->execute([$reviewId]);
            $review = $reviewStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($review) {
                $this->updateTherapistAverageRating($review['therapist_id']);
            }
        }
        
        return $success;
    }

    private function updateTherapistAverageRating(int $therapistId): void {
        $stmt = $this->conn->prepare(
            "SELECT AVG(rating) as avg_rating FROM therapist_reviews 
             WHERE therapist_id = ? AND moderation_status = 'Approved'"
        );
        $stmt->execute([$therapistId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $avgRating = $result ? round((float)$result['avg_rating'], 1) : 0.0;
        
        $updateStmt = $this->conn->prepare(
            "UPDATE therapists SET rating = ? WHERE therapist_id = ?"
        );
        $updateStmt->execute([$avgRating, $therapistId]);
    }

    public function getPendingReviews(): array {
        $stmt = $this->conn->prepare(
            "SELECT tr.*, u.first_name, u.last_name, t.first_name as therapist_first, t.last_name as therapist_last
             FROM therapist_reviews tr
             JOIN users u ON u.user_id = tr.patient_id
             JOIN users t ON t.user_id = tr.therapist_id
             WHERE tr.moderation_status = 'Pending'
             ORDER BY tr.created_at ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
