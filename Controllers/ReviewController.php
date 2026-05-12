<?php
require_once __DIR__ . '/../Models/Patient.php';
require_once __DIR__ . '/../Models/TherapistReview.php';

class ReviewController {
    private PatientReviewManager $reviewManager;
    private TherapistReview $reviewModel;

    public function __construct() {
        session_start();
        $this->reviewManager = new PatientReviewManager();
        $this->reviewModel = new TherapistReview();
    }

    public function handleSubmitReview(): void {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $patientId = $_SESSION['user_id'] ?? null;
        if (!$patientId || $_SESSION['role'] !== 'Patient') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            return;
        }

        $result = $this->reviewManager->submitTherapistReview($data);
        echo json_encode($result);
    }

    public function handleGetTherapistReviews(): void {
        header('Content-Type: application/json');
        
        $therapistId = $_GET['therapist_id'] ?? null;
        if (!$therapistId) {
            echo json_encode(['success' => false, 'message' => 'Therapist ID required']);
            return;
        }

        $limit = (int)($_GET['limit'] ?? 10);
        $offset = (int)($_GET['offset'] ?? 0);

        $reviews = $this->reviewModel->getTherapistReviews($therapistId, $limit, $offset);
        $stats = $this->reviewModel->getTherapistRatingStats($therapistId);

        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'stats' => $stats
        ]);
    }

    public function handleGetMyReviews(): void {
        header('Content-Type: application/json');
        
        $patientId = $_SESSION['user_id'] ?? null;
        if (!$patientId || $_SESSION['role'] !== 'Patient') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $reviews = $this->reviewManager->getMyReviews($patientId);
        echo json_encode(['success' => true, 'reviews' => $reviews]);
    }

    public function handleCanReview(): void {
        header('Content-Type: application/json');
        
        $patientId = $_SESSION['user_id'] ?? null;
        $therapistId = $_GET['therapist_id'] ?? null;
        
        if (!$patientId || !$therapistId) {
            echo json_encode(['success' => false, 'message' => 'Patient ID and Therapist ID required']);
            return;
        }

        $canReview = $this->reviewManager->canReviewTherapist($patientId, $therapistId);
        $existingReview = $this->reviewManager->getMyReviewForTherapist($patientId, $therapistId);

        echo json_encode([
            'success' => true,
            'can_review' => $canReview,
            'existing_review' => $existingReview
        ]);
    }

    public function handleMarkHelpful(): void {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $reviewId = $data['review_id'] ?? null;

        if (!$reviewId) {
            echo json_encode(['success' => false, 'message' => 'Review ID required']);
            return;
        }

        $success = $this->reviewManager->markReviewHelpful($reviewId);
        echo json_encode(['success' => $success]);
    }

    public function handleGetPendingReviews(): void {
        header('Content-Type: application/json');
        
        $role = $_SESSION['role'] ?? null;
        if (!in_array($role, ['Admin', 'Moderator'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $pendingReviews = $this->reviewModel->getPendingReviews();
        echo json_encode(['success' => true, 'reviews' => $pendingReviews]);
    }

    public function handleModerateReview(): void {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $role = $_SESSION['role'] ?? null;
        if (!in_array($role, ['Admin', 'Moderator'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $reviewId = $data['review_id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$reviewId || !$status) {
            echo json_encode(['success' => false, 'message' => 'Review ID and status required']);
            return;
        }

        $success = $this->reviewModel->updateReviewModeration($reviewId, $status);
        echo json_encode(['success' => $success]);
    }

    public function getReviewDataForPatient(int $patientId): array {
        $myReviews = $this->reviewManager->getMyReviews($patientId);
        $therapistId = $_GET['therapist_id'] ?? null;
        
        $data = [
            'my_reviews' => $myReviews
        ];

        if ($therapistId) {
            $canReview = $this->reviewManager->canReviewTherapist($patientId, $therapistId);
            $existingReview = $this->reviewManager->getMyReviewForTherapist($patientId, $therapistId);
            $therapistReviews = $this->reviewManager->getTherapistReviewsForPatient($therapistId);
            $therapistStats = $this->reviewManager->getTherapistRatingStats($therapistId);

            $data['can_review'] = $canReview;
            $data['existing_review'] = $existingReview;
            $data['therapist_reviews'] = $therapistReviews;
            $data['therapist_stats'] = $therapistStats;
        }

        return $data;
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'review') {
    $controller = new ReviewController();
    
    $action = $_GET['subaction'] ?? '';
    
    switch ($action) {
        case 'submit':
            $controller->handleSubmitReview();
            break;
        case 'get_therapist_reviews':
            $controller->handleGetTherapistReviews();
            break;
        case 'get_my_reviews':
            $controller->handleGetMyReviews();
            break;
        case 'can_review':
            $controller->handleCanReview();
            break;
        case 'mark_helpful':
            $controller->handleMarkHelpful();
            break;
        case 'get_pending':
            $controller->handleGetPendingReviews();
            break;
        case 'moderate':
            $controller->handleModerateReview();
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}
