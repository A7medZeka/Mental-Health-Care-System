<?php
session_start();
require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../../Models/Therapist.php';
require_once __DIR__ . '/../../Models/TherapistReview.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Therapist') {
    header('Location: ../Auth/login.php');
    exit();
}

$therapistId = $_SESSION['user_id'];

// Create therapist object
$therapist = new Therapist(['therapist_id' => $therapistId]);
$therapist->loadTherapistData($therapistId);

// Get review data
$reviewModel = new TherapistReview();
$reviews = $reviewModel->getTherapistReviews($therapistId, 20);
$stats = $reviewModel->getTherapistRatingStats($therapistId);

// Get therapist info for display
$db = SingletonDatabase::getInstance()->getConnection();
$stmt = $db->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
$stmt->execute([$therapistId]);
$therapistInfo = $stmt->fetch(PDO::FETCH_ASSOC);

$first_name = $therapistInfo['first_name'] ?? '';
$last_name = $therapistInfo['last_name'] ?? '';
$email = $therapistInfo['email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews & Ratings - Mental Health Care</title>
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
                            <a class="nav-link" href="sessions.php">
                                <i class="bi bi-calendar-event me-2"></i> Manage Sessions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="patients.php">
                                <i class="bi bi-people me-2"></i> Manage Patients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="reviews.php">
                                <i class="bi bi-star me-2"></i> Reviews & Ratings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="insights.php">
                                <i class="bi bi-graph-up me-2"></i> Clinical Insights
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
                    <h1 class="h2 text-primary-custom fw-bold">Reviews & Ratings</h1>
                    <div class="d-flex align-items-center">
                        <span class="text-secondary-custom me-3">
                            <i class="bi bi-person-circle me-1"></i> 
                            Dr. <?= htmlspecialchars($first_name . ' ' . $last_name) ?>
                        </span>
                    </div>
                </div>

                <!-- Rating Overview -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card card-custom h-100">
                            <div class="card-body text-center">
                                <h1 class="fw-bold text-primary-custom mb-2"><?= number_format($stats['average_rating'], 1) ?></h1>
                                <div class="mb-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= round($stats['average_rating'])): ?>
                                            <i class="bi bi-star-fill text-warning"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star text-muted"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-muted mb-0">Average Rating</p>
                                <small class="text-muted"><?= $stats['total_reviews'] ?> reviews</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card card-custom h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-primary-custom mb-0">Rating Distribution</h5>
                            </div>
                            <div class="card-body">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="me-2" style="width: 60px;"><?= $i ?> stars</span>
                                        <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                            <?php 
                                            $starKey = $i === 5 ? 'five_star' : ($i === 4 ? 'four_star' : ($i === 3 ? 'three_star' : ($i === 2 ? 'two_star' : 'one_star')));
                                            $pctKey = $i === 5 ? 'five_star_pct' : ($i === 4 ? 'four_star_pct' : ($i === 3 ? 'three_star_pct' : ($i === 2 ? 'two_star_pct' : 'one_star_pct')));
                                            ?>
                                            <div class="progress-bar bg-warning" style="width: <?= $stats[$pctKey] ?? 0 ?>%"></div>
                                        </div>
                                        <span style="width: 40px; text-align: right;"><?= $stats[$starKey] ?? 0 ?></span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews List -->
                <div class="card card-custom">
                    <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-primary-custom mb-0">Patient Reviews</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" style="width: auto;" onchange="sortReviews(this.value)">
                                <option value="recent">Most Recent</option>
                                <option value="highest">Highest Rating</option>
                                <option value="lowest">Lowest Rating</option>
                                <option value="helpful">Most Helpful</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($reviews)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-star text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">No Reviews Yet</h5>
                                <p class="text-muted">Your patients haven't left any reviews yet. Keep providing excellent service!</p>
                            </div>
                        <?php else: ?>
                            <div id="reviewsList">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="border-bottom pb-3 mb-3 review-item" data-rating="<?= $review['rating'] ?>" data-date="<?= $review['created_at'] ?>" data-helpful="<?= $review['helpful_count'] ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-1">Patient Review</h6>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= $review['rating']): ?>
                                                            <i class="bi bi-star-fill text-warning"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-star text-muted"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                    <span class="text-muted small"><?= $review['formatted_date'] ?></span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success">Published</span>
                                            </div>
                                        </div>
                                        <p class="mb-2"><?= $review['review_text'] ?: '<em class="text-muted">No written review provided.</em>' ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <?= $review['helpful_count'] ?> people found this helpful
                                            </small>
                                            <small class="text-muted">
                                                Review ID: #<?= $review['review_id'] ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (count($reviews) >= 20): ?>
                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-outline-primary" onclick="loadMoreReviews()">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Load More Reviews
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Performance Insights -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card card-custom">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-primary-custom mb-0">Performance Tips</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Maintain consistent session quality
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Respond promptly to patient messages
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Provide clear session summaries
                                    </li>
                                    <li class="mb-0">
                                        <i class="bi bi-lightbulb text-warning me-2"></i>
                                        Encourage satisfied patients to leave reviews
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-custom">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-primary-custom mb-0">Review Guidelines</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bi bi-info-circle text-info me-2"></i>
                                        Reviews are moderated for appropriateness
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-info-circle text-info me-2"></i>
                                        Only verified patients can leave reviews
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-info-circle text-info me-2"></i>
                                        Reviews are linked to completed sessions
                                    </li>
                                    <li class="mb-0">
                                        <i class="bi bi-info-circle text-info me-2"></i>
                                        Contact support for review concerns
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentReviews = <?= json_encode($reviews) ?>;
        let currentSort = 'recent';

        function sortReviews(sortBy) {
            currentSort = sortBy;
            const reviewsList = document.getElementById('reviewsList');
            
            let sortedReviews = [...currentReviews];
            
            switch(sortBy) {
                case 'highest':
                    sortedReviews.sort((a, b) => b.rating - a.rating);
                    break;
                case 'lowest':
                    sortedReviews.sort((a, b) => a.rating - b.rating);
                    break;
                case 'helpful':
                    sortedReviews.sort((a, b) => b.helpful_count - a.helpful_count);
                    break;
                case 'recent':
                default:
                    sortedReviews.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    break;
            }
            
            displaySortedReviews(sortedReviews);
        }

        function displaySortedReviews(reviews) {
            const reviewsList = document.getElementById('reviewsList');
            
            let html = '';
            reviews.forEach(review => {
                let stars = '';
                for (let i = 1; i <= 5; i++) {
                    stars += i <= review.rating ? 
                        '<i class="bi bi-star-fill text-warning"></i>' : 
                        '<i class="bi bi-star text-muted"></i>';
                }
                
                html += `
                    <div class="border-bottom pb-3 mb-3 review-item" data-rating="${review.rating}" data-date="${review.created_at}" data-helpful="${review.helpful_count}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-1">Patient Review</h6>
                                <div class="d-flex align-items-center gap-2">
                                    ${stars}
                                    <span class="text-muted small">${review.formatted_date}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success">Published</span>
                            </div>
                        </div>
                        <p class="mb-2">${review.review_text || '<em class="text-muted">No written review provided.</em>'}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                ${review.helpful_count} people found this helpful
                            </small>
                            <small class="text-muted">
                                Review ID: #${review.review_id}
                            </small>
                        </div>
                    </div>
                `;
            });
            
            reviewsList.innerHTML = html;
        }

        function loadMoreReviews() {
            // In a real implementation, this would load more reviews via AJAX
            alert('Load more reviews functionality would be implemented here.');
        }

        // Initialize tooltips if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Any initialization code
        });
    </script>
</body>
</html>
