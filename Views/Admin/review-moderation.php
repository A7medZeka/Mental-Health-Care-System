<?php
session_start();
require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Models/TherapistReview.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Moderator'])) {
    header('Location: ../Auth/login.php');
    exit();
}

$reviewModel = new TherapistReview();
$pendingReviews = $reviewModel->getPendingReviews();

// Handle moderation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $reviewId = $_POST['review_id'] ?? 0;
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $result = $reviewModel->updateReviewModeration($reviewId, 'Approved');
        $message = $result ? 'Review approved successfully' : 'Error approving review';
        $status = $result ? 'success' : 'danger';
    } elseif ($action === 'reject') {
        $result = $reviewModel->updateReviewModeration($reviewId, 'Rejected');
        $message = $result ? 'Review rejected successfully' : 'Error rejecting review';
        $status = $result ? 'success' : 'danger';
    }
    
    // Refresh pending reviews
    $pendingReviews = $reviewModel->getPendingReviews();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Moderation - Mental Health Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white shadow-sm">
                <div class="position-sticky pt-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size: 2rem;"></i>
                        <h5 class="fw-bold text-primary-custom mt-2">MentalCare System</h5>
                    </div>
                    
                    <ul class="nav flex-column mb-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-house-door me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="therapists.php">
                                <i class="bi bi-people me-2"></i> Therapists
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="patients.php">
                                <i class="bi bi-person-badge me-2"></i> Patients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="review-moderation.php">
                                <i class="bi bi-shield-check me-2"></i> Review Moderation
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="performance.php">
                                <i class="bi bi-graph-up me-2"></i> Performance
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="mx-3 mt-5">
                    <div class="px-3">
                        <a href="../Auth/logout.php" class="btn btn-outline-danger w-100 mt-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2 text-primary-custom fw-bold">Review Moderation</h1>
                    <div class="d-flex align-items-center">
                        <span class="text-secondary-custom me-3">
                            <i class="bi bi-person-circle me-1"></i> 
                            <?= htmlspecialchars($_SESSION['role']) ?>
                        </span>
                        <?php if (!empty($pendingReviews)): ?>
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <?= count($pendingReviews) ?> Pending
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isset($message)): ?>
                    <div class="alert alert-<?= $status ?> alert-dismissible fade show" role="alert">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Pending Reviews -->
                <div class="card card-custom">
                    <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-primary-custom mb-0">Pending Reviews</h5>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pendingReviews)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">All Reviews Moderated</h5>
                                <p class="text-muted">There are no pending reviews to moderate.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th>Review ID</th>
                                            <th>Patient</th>
                                            <th>Therapist</th>
                                            <th>Rating</th>
                                            <th>Review Text</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingReviews as $review): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary">#<?= $review['review_id'] ?></span></td>
                                                <td><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></td>
                                                <td>Dr. <?= htmlspecialchars($review['therapist_first'] . ' ' . $review['therapist_last']) ?></td>
                                                <td>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= $review['rating']): ?>
                                                            <i class="bi bi-star-fill text-warning"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-star text-muted"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                    <span class="ms-1">(<?= $review['rating'] ?>/5)</span>
                                                </td>
                                                <td>
                                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                                        <?= htmlspecialchars($review['review_text'] ?: '<em class="text-muted">No text provided</em>') ?>
                                                    </div>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($review['created_at'])) ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="review_id" value="<?= $review['review_id'] ?>">
                                                            <input type="hidden" name="action" value="approve">
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this review?')">
                                                                <i class="bi bi-check-circle me-1"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="review_id" value="<?= $review['review_id'] ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this review?')">
                                                                <i class="bi bi-x-circle me-1"></i> Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Moderation Guidelines -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card card-custom">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-primary-custom mb-0">Approval Guidelines</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Constructive feedback about therapy experience
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Professional and respectful language
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Specific examples of positive experiences
                                    </li>
                                    <li class="mb-0">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Genuine patient experiences
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-custom">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-primary-custom mb-0">Rejection Reasons</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bi bi-x-circle text-danger me-2"></i>
                                        Inappropriate or offensive language
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-x-circle text-danger me-2"></i>
                                        Personal attacks or harassment
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-x-circle text-danger me-2"></i>
                                        False or misleading information
                                    </li>
                                    <li class="mb-0">
                                        <i class="bi bi-x-circle text-danger me-2"></i>
                                        Spam or promotional content
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="card card-custom mt-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold text-primary-custom mb-0">Moderation Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="p-3">
                                    <h3 class="fw-bold text-warning"><?= count($pendingReviews) ?></h3>
                                    <p class="text-muted mb-0">Pending Reviews</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3">
                                    <h3 class="fw-bold text-success">
                                        <?php 
                                            // This would typically come from a statistics table
                                            echo "0"; // Placeholder
                                        ?>
                                    </h3>
                                    <p class="text-muted mb-0">Approved Today</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3">
                                    <h3 class="fw-bold text-danger">
                                        <?php 
                                            // This would typically come from a statistics table
                                            echo "0"; // Placeholder
                                        ?>
                                    </h3>
                                    <p class="text-muted mb-0">Rejected Today</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3">
                                    <h3 class="fw-bold text-info">
                                        <?php 
                                            // This would typically come from a statistics table
                                            echo "98"; // Placeholder
                                        ?>%
                                    </h3>
                                    <p class="text-muted mb-0">Approval Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
