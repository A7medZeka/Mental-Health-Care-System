<?php
require_once 'Validation.php';
require_once 'connection.php';
session_start();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

checkMethod($method);

if ($_SESSION['role'] !== 'Admin') {
    $map = [
        'Admin'     => 'admin-dashboard.php',
        'Patient'   => 'patient-dashboard.php',
        'Moderator' => 'moderator-dashboard.php',
    ];
    header('Location: ' . ($map[$_SESSION['role']] ?? 'index.php'));
    exit();
}

$role       = $_SESSION['role']       ?? 'Admin';
$first_name = $_SESSION['first_name'] ?? 'Admin';
$last_name  = $_SESSION['last_name']  ?? '';
$email      = $_SESSION['email']      ?? '';
$user_id    = $_SESSION['user_id'];

$conn = getConnection();

// FIX 1: Combined age + gender into a single query (was two separate queries before)
$stmt = $conn->prepare("SELECT age, gender FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
$age    = $currentUser['age']    ?? '';
$gender = $currentUser['gender'] ?? '';

// FIX 2: Renamed loop variable from $therapist to $therapistRow to avoid overwriting
//         the $therapist query result below.
// FIX 3: Fetch ALL verified therapists for the dropdown (was only fetching for the
//         logged-in admin's user_id, producing an empty or wrong list).
$stmtAll = $conn->prepare("
    SELECT
        t.therapist_id,
        u.username AS therapist_name
    FROM therapists t
    JOIN users u ON u.user_id = t.therapist_id
    WHERE t.is_verified = 1
    ORDER BY u.username
");
$stmtAll->execute();
$allTherapists = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

// FIX 4: Determine which therapist to show in the detail panel.
//         Default to the first verified therapist (or use a GET param for AJAX later).
$selected_therapist_id = $_GET['therapist_id'] ?? ($allTherapists[0]['therapist_id'] ?? null);

$therapist = null;
if ($selected_therapist_id) {
    $stmtDetail = $conn->prepare("
        SELECT
            t.therapist_id,
            u.username       AS therapist_name,
            COUNT(DISTINCT r.review_id)   AS total_reviews,
            ROUND(AVG(r.rating), 1)       AS avg_rating,
            COUNT(DISTINCT s.session_id)  AS total_sessions,
            CASE
                WHEN COUNT(DISTINCT s.session_id) > 0
                THEN ROUND(
                    SUM(CASE WHEN s.session_state = 'NoShow' THEN 1 ELSE 0 END) * 100.0
                    / COUNT(DISTINCT s.session_id), 1)
                ELSE 0
            END AS no_show_rate
        FROM therapists t
        JOIN users u ON u.user_id = t.therapist_id
        LEFT JOIN therapist_reviews r ON r.therapist_id = t.therapist_id
        LEFT JOIN sessions         s ON s.therapist_id = t.therapist_id
        WHERE t.therapist_id = ?
          AND t.is_verified  = 1
        GROUP BY t.therapist_id, u.username
    ");
    $stmtDetail->execute([$selected_therapist_id]);
    $therapist = $stmtDetail->fetch(PDO::FETCH_ASSOC);
}

// FIX 5: Rating breakdown now uses $selected_therapist_id, not the admin's user_id
$ratings = [];
if ($selected_therapist_id) {
    $stmtRatings = $conn->prepare("
        SELECT rating, COUNT(*) AS count
        FROM therapist_reviews
        WHERE therapist_id = ?
        GROUP BY rating
        ORDER BY rating DESC
    ");
    $stmtRatings->execute([$selected_therapist_id]);
    $ratings = $stmtRatings->fetchAll(PDO::FETCH_ASSOC);
}

// FIX 6: Recent feedback for the selected therapist
$recentFeedback = [];
if ($selected_therapist_id) {
    $stmtFeedback = $conn->prepare("
        SELECT r.rating, r.comment, r.created_at
        FROM therapist_reviews r
        WHERE r.therapist_id = ?
          AND r.comment IS NOT NULL
          AND r.comment <> ''
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $stmtFeedback->execute([$selected_therapist_id]);
    $recentFeedback = $stmtFeedback->fetchAll(PDO::FETCH_ASSOC);
}

// Helper: build star HTML
function starHtml(float $rating, string $size = '1rem'): string {
    $html = '<div class="star-rating">';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $html .= "<i class=\"bi bi-star-fill star-filled\" style=\"font-size:{$size};\"></i>";
        } elseif ($rating >= $i - 0.5) {
            $html .= "<i class=\"bi bi-star-half star-filled\" style=\"font-size:{$size};\"></i>";
        } else {
            $html .= "<i class=\"bi bi-star-empty star-empty\" style=\"font-size:{$size};\"></i>";
        }
    }
    return $html . '</div>';
}

// Helper: rating breakdown percentage
function ratingPercent(array $ratings, int $star): float {
    $total = array_sum(array_column($ratings, 'count'));
    if ($total === 0) return 0;
    foreach ($ratings as $r) {
        if ((int)$r['rating'] === $star) {
            return round($r['count'] / $total * 100, 1);
        }
    }
    return 0;
}

$avg           = $therapist['avg_rating']    ?? 0;
$totalReviews  = $therapist['total_reviews'] ?? 0;
$totalSessions = $therapist['total_sessions'] ?? 0;
// FIX 7: No-show rate now comes from the real DB value, not hardcoded 6.4%
$noShowRate    = $therapist['no_show_rate']  ?? 0;
$detailName    = htmlspecialchars($therapist['therapist_name'] ?? 'N/A');
$initials      = '';
if ($therapist) {
    $parts    = explode(' ', $therapist['therapist_name']);
    $initials = strtoupper(substr($parts[0], 0, 1) . (substr($parts[count($parts)-1], 0, 1)));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapist Performance - Admin - MentalCare System</title>
    <meta name="description" content="Admin view of therapist performance metrics, ratings and feedback. UC-32.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .star-rating { display:inline-flex; gap:2px; }
        .star-filled  { color:#F4B41A; }
        .star-empty   { color:#dee2e6; }
        .metric-card {
            border:none; border-radius:14px; background:#fff;
            box-shadow:0 2px 18px rgba(47,143,126,0.08); padding:1.5rem;
            transition:transform .25s, box-shadow .25s;
        }
        .metric-card:hover { transform:translateY(-3px); box-shadow:0 6px 28px rgba(47,143,126,0.14); }
        .metric-icon { width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; }
        .chart-bar-wrap { display:flex; flex-direction:column; gap:.55rem; }
        .chart-row { display:flex; align-items:center; gap:.75rem; font-size:.85rem; }
        .chart-label { width:110px; flex-shrink:0; color:var(--text-brown); font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .chart-track { flex:1; background:#e9ecef; border-radius:20px; height:12px; overflow:hidden; }
        .chart-fill  { height:100%; border-radius:20px; background:var(--primary-green); transition:width .8s cubic-bezier(.4,0,.2,1); }
        .chart-fill.warn   { background:#F4B41A; }
        .chart-fill.danger { background:#dc3545; }
        .chart-value { width:42px; text-align:right; font-weight:700; color:var(--primary-green); font-size:.85rem; }
        .therapist-row { display:flex; align-items:center; gap:1rem; padding:1rem 1.25rem; border-bottom:1px solid rgba(0,0,0,0.05); transition:background .15s; cursor:pointer; }
        .therapist-row:last-child { border-bottom:none; }
        .therapist-row:hover { background:var(--light-green); }
        .therapist-row.selected { background:#e6f5f2; }
        .therapist-avatar { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:.95rem; flex-shrink:0; }
        .feedback-item { padding:.85rem 1rem; border-radius:10px; background:#f8fdf9; border:1px solid rgba(47,143,126,0.12); margin-bottom:.6rem; font-size:.88rem; }
        .no-data-state { text-align:center; padding:3rem 1rem; color:var(--light-brown); }
        .no-data-state i { font-size:3rem; opacity:.3; }
        .sparkline { display:flex; align-items:flex-end; gap:3px; height:36px; }
        .spark-bar { width:8px; border-radius:3px 3px 0 0; background:var(--primary-green); opacity:.7; flex-shrink:0; }
        .period-btn { border-radius:20px; font-size:.82rem; font-weight:600; padding:.3rem .9rem; border:2px solid #dee2e6; background:transparent; color:var(--text-brown); cursor:pointer; transition:all .2s; }
        .period-btn.active, .period-btn:hover { background:var(--primary-green); color:#fff; border-color:var(--primary-green); }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">

        <!-- Admin Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white shadow-sm">
            <div class="position-sticky pt-4">
                <div class="text-center mb-4">
                    <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size:2rem;"></i>
                    <h5 class="fw-bold text-primary-custom mt-2">MentalCare System</h5>
                </div>
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin-dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-patients.php">
                            <i class="bi bi-people me-2"></i> Manage Patients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-therapists.php">
                            <i class="bi bi-person-badge me-2"></i> Therapists Verification
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-rbac.php">
                            <i class="bi bi-shield-lock me-2"></i> RBAC Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin-performance.php">
                            <i class="bi bi-bar-chart-line me-2"></i> Therapist Performance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-safety-logs.php">
                            <i class="bi bi-journal-medical me-2"></i> Safety Logs
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <div>
                    <h1 class="h2 text-primary-custom fw-bold"><i class="bi bi-bar-chart-line me-2"></i>Therapist Performance Metrics</h1>
                    <p class="text-secondary-custom mb-0">UC-32 — Aggregated ratings, session feedback and trend dashboards for clinic management.</p>
                </div>
                <!-- FIX 8: $gender was referencing the overwritten loop variable; now uses the correct $gender from the user query -->
                <span class="text-secondary-custom fw-bold">
                    <i class="bi bi-person-circle me-1"></i>
                    <?php echo 'Age: ' . ($age ?: 'N/A') . ' | ' . $role . ' | ' . htmlspecialchars($first_name . ' ' . $last_name) . ' | ' . htmlspecialchars($gender); ?>
                </span>
            </div>

            <!-- Filter Bar -->
            <!-- FIX 9: Form now submits via GET so therapist_id is passed back to PHP to update the detail panel -->
            <form method="GET" action="admin-performance.php" class="d-flex align-items-center gap-3 flex-wrap mb-4" id="filterForm">
                <div class="d-flex gap-2">
                    <button type="button" class="period-btn active" data-period="7">Last 7 Days</button>
                    <button type="button" class="period-btn" data-period="30">Last 30 Days</button>
                    <button type="button" class="period-btn" data-period="90">Last 90 Days</button>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <select class="form-select form-select-sm" id="therapistFilter" name="therapist_id" style="min-width:180px;">
                        <?php foreach ($allTherapists as $therapistRow): ?>
                            <option
                                value="<?php echo (int)$therapistRow['therapist_id']; ?>"
                                <?php echo ((int)$therapistRow['therapist_id'] === (int)$selected_therapist_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($therapistRow['therapist_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary-custom btn-sm px-3" id="btnApplyFilter">
                        <i class="bi bi-funnel me-1"></i> Apply
                    </button>
                </div>
            </form>

            <!-- KPI Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon bg-light-green text-primary-custom"><i class="bi bi-star-fill"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">Avg. Rating</p>
                            <h3 class="fw-bold mb-0 text-primary-custom" id="kpiAvgRating"><?php echo $avg ?: 'N/A'; ?></h3>
                            <div class="mt-1"><?php echo $avg ? starHtml((float)$avg, '.85rem') : '<small class="text-muted">No ratings yet</small>'; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon bg-light-green text-primary-custom"><i class="bi bi-chat-left-text-fill"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">Total Reviews</p>
                            <!-- FIX 10: Was using $therapist['total_reviews'] after $therapist was overwritten by the foreach loop -->
                            <h3 class="fw-bold mb-0 text-primary-custom"><?php echo $totalReviews; ?></h3>
                            <small class="text-secondary-custom">this period</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon bg-light-green text-primary-custom"><i class="bi bi-calendar-check-fill"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">Sessions Completed</p>
                            <h3 class="fw-bold mb-0 text-primary-custom"><?php echo $totalSessions; ?></h3>
                            <small class="text-secondary-custom">this period</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon" style="background:#fff3cd;"><i class="bi bi-person-x-fill" style="color:#e67e22;"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">No-Show Rate</p>
                            <!-- FIX 11: Was hardcoded to 6.4% — now shows real DB value -->
                            <h3 class="fw-bold mb-0" style="color:#e67e22;"><?php echo $noShowRate; ?>%</h3>
                            <small class="text-secondary-custom">selected therapist</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rankings + Detail -->
            <div class="row g-4">
                <!-- Therapist Rankings -->
                <div class="col-lg-5">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h5 class="fw-bold text-primary-custom mb-0"><i class="bi bi-trophy me-2"></i>Therapist Rankings</h5>
                            <small class="text-secondary-custom">Click a therapist to view detailed metrics</small>
                        </div>
                        <div class="card-body p-0 mt-2" id="therapistList">
                            <?php if (empty($allTherapists)): ?>
                                <div class="no-data-state">
                                    <i class="bi bi-people d-block mb-3"></i>
                                    <p class="fw-semibold">No verified therapists found.</p>
                                </div>
                            <?php else: ?>
                                <!-- FIX 12: Rankings now built from real DB data ($allTherapists) instead of static HTML -->
                                <?php
                                $avatarColors = ['#2F8F7E','#48B6A2','#F4B41A','#8F5E2F','#5B8FA8','#A87B5B'];
                                $colorIdx = 0;
                                foreach ($allTherapists as $idx => $therapistRow):
                                    $tid       = (int)$therapistRow['therapist_id'];
                                    $tName     = htmlspecialchars($therapistRow['therapist_name']);
                                    $nameParts = explode(' ', $therapistRow['therapist_name']);
                                    $tInitials = strtoupper(
                                        substr($nameParts[0], 0, 1) .
                                        substr($nameParts[count($nameParts)-1], 0, 1)
                                    );
                                    $isSelected = ($tid === (int)$selected_therapist_id);
                                    $color = $avatarColors[$colorIdx % count($avatarColors)];
                                    $colorIdx++;

                                    // Fetch per-therapist avg for ranking list
                                    $stmtAvg = $conn->prepare("
                                        SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS cnt
                                        FROM therapist_reviews WHERE therapist_id = ?
                                    ");
                                    $stmtAvg->execute([$tid]);
                                    $tStats = $stmtAvg->fetch(PDO::FETCH_ASSOC);
                                    $tAvg   = $tStats['avg_rating'] ?? 0;
                                    $tCnt   = $tStats['cnt'] ?? 0;
                                ?>
                                <a href="admin-performance.php?therapist_id=<?php echo $tid; ?>"
                                   class="therapist-row text-decoration-none <?php echo $isSelected ? 'selected' : ''; ?>">
                                    <div class="therapist-avatar" style="background:<?php echo $color; ?>;"><?php echo $tInitials; ?></div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-dark"><?php echo $tName; ?></div>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php echo $tAvg ? starHtml((float)$tAvg, '.75rem') : '<small class="text-muted">No ratings</small>'; ?>
                                            <small class="text-secondary-custom"><?php echo $tAvg ? "{$tAvg} · {$tCnt} reviews" : ''; ?></small>
                                        </div>
                                    </div>
                                    <?php if ($idx === 0): ?>
                                        <span class="badge" style="background:var(--light-green);color:var(--primary-green);">Top</span>
                                    <?php elseif ($tAvg > 0 && $tAvg < 3.5): ?>
                                        <span class="badge bg-warning text-dark" style="font-size:.7rem;">Watch</span>
                                    <?php endif; ?>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Detail Panel -->
                <div class="col-lg-7">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-0 d-flex align-items-center gap-3">
                            <div class="therapist-avatar" id="detailAvatar" style="background:#2F8F7E; width:52px; height:52px; font-size:1.1rem;">
                                <?php echo htmlspecialchars($initials ?: '?'); ?>
                            </div>
                            <div>
                                <h5 class="fw-bold text-primary-custom mb-0" id="detailName"><?php echo $detailName; ?></h5>
                                <small class="text-secondary-custom" id="detailSpec">Performance overview</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!$therapist): ?>
                                <div class="no-data-state">
                                    <i class="bi bi-bar-chart d-block mb-3"></i>
                                    <p class="fw-semibold">No therapist selected or no data available.</p>
                                </div>
                            <?php else: ?>
                                <h6 class="fw-bold text-primary-custom mb-3 mt-2">Rating Breakdown</h6>
                                <div class="chart-bar-wrap mb-4" id="ratingBreakdown">
                                    <?php
                                    $starLabels = [5 => '5 Stars', 4 => '4 Stars', 3 => '3 Stars', 2 => '2 Stars', 1 => '1 Star'];
                                    foreach ($starLabels as $star => $label):
                                        $pct = ratingPercent($ratings, $star);
                                        $fillClass = $star >= 4 ? '' : ($star === 3 ? 'warn' : 'danger');
                                        $valueColor = $star >= 4 ? 'var(--primary-green)' : ($star === 3 ? '#F4B41A' : '#dc3545');
                                    ?>
                                    <div class="chart-row">
                                        <span class="chart-label">
                                            <i class="bi bi-star-fill star-filled me-1" style="font-size:.75rem;"></i><?php echo $label; ?>
                                        </span>
                                        <div class="chart-track">
                                            <div class="chart-fill <?php echo $fillClass; ?>" style="width:<?php echo $pct; ?>%"></div>
                                        </div>
                                        <span class="chart-value" style="color:<?php echo $valueColor; ?>"><?php echo $pct; ?>%</span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-4">
                                        <div class="text-center p-3 rounded-3 bg-light-green">
                                            <div class="fw-bold text-primary-custom fs-5"><?php echo $totalSessions; ?></div>
                                            <small class="text-secondary-custom">Sessions</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center p-3 rounded-3 bg-light-green">
                                            <div class="fw-bold text-primary-custom fs-5"><?php echo $totalReviews; ?></div>
                                            <small class="text-secondary-custom">Reviews</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center p-3 rounded-3" style="background:#fff3cd;">
                                            <div class="fw-bold fs-5" style="color:#e67e22;"><?php echo $noShowRate; ?>%</div>
                                            <small class="text-secondary-custom">No-Show</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- FIX 13: Recent feedback now rendered from real DB data -->
                                <h6 class="fw-bold text-primary-custom mb-2">Recent Patient Feedback</h6>
                                <div id="feedbackList">
                                    <?php if (empty($recentFeedback)): ?>
                                        <div class="no-data-state">
                                            <i class="bi bi-chat-left-text d-block mb-3"></i>
                                            <p class="fw-semibold">No written feedback yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recentFeedback as $fb): ?>
                                            <div class="feedback-item">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <?php echo starHtml((float)$fb['rating'], '.75rem'); ?>
                                                    <small class="text-secondary-custom">
                                                        <?php echo htmlspecialchars(
                                                            date('d M Y', strtotime($fb['created_at']))
                                                        ); ?>
                                                    </small>
                                                </div>
                                                <?php echo htmlspecialchars($fb['comment']); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/admin.js"></script>
<script>
    // Period filter buttons (visual only — extend with AJAX if needed)
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
</body>
</html>