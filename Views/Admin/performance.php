<?php
require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Core/Database.php';
require_once __DIR__ . '/../../Models/Admin.php';

session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: ../Auth/login.php');
    exit();
}

checkMethod($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($_SESSION['role'] !== 'Admin') {
    $map = [
        'Patient'   => '../Patient/dashboard.php',
        'Moderator' => '../Moderator/dashboard.php',
    ];
    header('Location: ' . ($map[$_SESSION['role']] ?? '../Auth/login.php'));
    exit();
}

$role       = $_SESSION['role']       ?? 'Admin';
$first_name = $_SESSION['first_name'] ?? 'Admin';
$last_name  = $_SESSION['last_name']  ?? '';
$user_id    = $_SESSION['user_id'];

// ── Data via Admin model ──────────────────────────────────────────────────────
$admin = new Admin();

$allTherapists        = $admin->getVerifiedTherapistList();
$selected_id          = (int)($_GET['therapist_id'] ?? ($allTherapists[0]['therapist_id'] ?? 0));

$therapist      = $selected_id ? $admin->getTherapistPerformanceDetail($selected_id) : null;
$ratings        = $selected_id ? $admin->getTherapistRatingBreakdown($selected_id)   : [];
$recentFeedback = $selected_id ? $admin->getTherapistRecentFeedback($selected_id)    : [];

// ── View helpers (presentation only — no DB) ──────────────────────────────────
function starHtml(float $rating, string $size = '1rem'): string {
    $html = '<div class="star-rating">';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $html .= "<i class=\"bi bi-star-fill star-filled\" style=\"font-size:{$size};\"></i>";
        } elseif ($rating >= $i - 0.5) {
            $html .= "<i class=\"bi bi-star-half star-filled\" style=\"font-size:{$size};\"></i>";
        } else {
            $html .= "<i class=\"bi bi-star star-empty\" style=\"font-size:{$size};\"></i>";
        }
    }
    return $html . '</div>';
}

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

function initials(string $name): string {
    $parts = explode(' ', trim($name));
    return strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
}

// ── Derived display vars ──────────────────────────────────────────────────────
$avg           = (float)($therapist['avg_rating']    ?? 0);
$totalReviews  = (int)  ($therapist['total_reviews'] ?? 0);
$totalSessions = (int)  ($therapist['total_sessions'] ?? 0);
$noShowRate    = (float)($therapist['no_show_rate']  ?? 0);
$detailName    = htmlspecialchars($therapist['therapist_name'] ?? 'N/A');
$detailInit    = $therapist ? initials($therapist['therapist_name']) : '?';

$avatarColors  = ['#2F8F7E','#48B6A2','#F4B41A','#8F5E2F','#5B8FA8','#A87B5B'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapist Performance - Admin - MentalCare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .star-rating  { display:inline-flex; gap:2px; }
        .star-filled  { color:#F4B41A; }
        .star-empty   { color:#dee2e6; }

        .metric-card  {
            border:none; border-radius:14px; background:#fff;
            box-shadow:0 2px 18px rgba(47,143,126,.08); padding:1.5rem;
            transition:transform .25s, box-shadow .25s;
        }
        .metric-card:hover { transform:translateY(-3px); box-shadow:0 6px 28px rgba(47,143,126,.14); }
        .metric-icon  { width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; }

        .chart-bar-wrap { display:flex; flex-direction:column; gap:.55rem; }
        .chart-row    { display:flex; align-items:center; gap:.75rem; font-size:.85rem; }
        .chart-label  { width:110px; flex-shrink:0; color:var(--text-brown); font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .chart-track  { flex:1; background:#e9ecef; border-radius:20px; height:12px; overflow:hidden; }
        .chart-fill   { height:100%; border-radius:20px; background:var(--primary-green); transition:width .8s cubic-bezier(.4,0,.2,1); }
        .chart-fill.warn   { background:#F4B41A; }
        .chart-fill.danger { background:#dc3545; }
        .chart-value  { width:42px; text-align:right; font-weight:700; color:var(--primary-green); font-size:.85rem; }

        .therapist-row { display:flex; align-items:center; gap:1rem; padding:1rem 1.25rem; border-bottom:1px solid rgba(0,0,0,.05); transition:background .15s; cursor:pointer; }
        .therapist-row:last-child { border-bottom:none; }
        .therapist-row:hover   { background:var(--light-green); }
        .therapist-row.selected{ background:#e6f5f2; }

        .therapist-avatar { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:.95rem; flex-shrink:0; }
        .feedback-item { padding:.85rem 1rem; border-radius:10px; background:#f8fdf9; border:1px solid rgba(47,143,126,.12); margin-bottom:.6rem; font-size:.88rem; }
        .no-data-state { text-align:center; padding:3rem 1rem; color:var(--light-brown); }
        .no-data-state i { font-size:3rem; opacity:.3; }

        .period-btn { border-radius:20px; font-size:.82rem; font-weight:600; padding:.3rem .9rem; border:2px solid #dee2e6; background:transparent; color:var(--text-brown); cursor:pointer; transition:all .2s; }
        .period-btn.active, .period-btn:hover { background:var(--primary-green); color:#fff; border-color:var(--primary-green); }
    </style>
</head>
<body>
<div class="container-fluid">
<div class="row">

    <!-- ═══════════════ SIDEBAR ═══════════════ -->
    <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white shadow-sm">
        <div class="position-sticky pt-4">
            <div class="text-center mb-4">
                <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size:2rem;"></i>
                <h5 class="fw-bold text-primary-custom mt-2">MentalCare System</h5>
            </div>
            <ul class="nav flex-column mb-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="patients.php"><i class="bi bi-people me-2"></i>Manage Patients</a></li>
                <li class="nav-item"><a class="nav-link" href="therapists.php"><i class="bi bi-person-badge me-2"></i>Therapists Verification</a></li>
                <li class="nav-item"><a class="nav-link" href="rbac.php"><i class="bi bi-shield-lock me-2"></i>RBAC Settings</a></li>
                <li class="nav-item"><a class="nav-link active" href="performance.php"><i class="bi bi-bar-chart-line me-2"></i>Therapist Performance</a></li>
                <li class="nav-item"><a class="nav-link" href="safety-logs.php"><i class="bi bi-journal-medical me-2"></i>Safety Logs</a></li>
            </ul>
        </div>
    </nav>

    <!-- ═══════════════ MAIN ═══════════════ -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">

        <!-- Header -->
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
            <div>
                <h1 class="h2 text-primary-custom fw-bold">
                    <i class="bi bi-bar-chart-line me-2"></i>Therapist Performance Metrics
                </h1>
                <p class="text-secondary-custom mb-0">Aggregated ratings, session feedback and trend dashboards.</p>
            </div>
            <span class="text-secondary-custom fw-bold">
                <i class="bi bi-person-circle me-1"></i>
                <?= $role . ' | ' . htmlspecialchars($first_name . ' ' . $last_name) ?>
            </span>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="performance.php" class="d-flex align-items-center gap-3 flex-wrap mb-4">
            <div class="d-flex gap-2">
                <button type="button" class="period-btn active" data-period="7">Last 7 Days</button>
                <button type="button" class="period-btn" data-period="30">Last 30 Days</button>
                <button type="button" class="period-btn" data-period="90">Last 90 Days</button>
            </div>
            <div class="ms-auto d-flex gap-2">
                <select class="form-select form-select-sm" name="therapist_id" style="min-width:180px;">
                    <?php foreach ($allTherapists as $t): ?>
                        <option value="<?= (int)$t['therapist_id'] ?>"
                            <?= ((int)$t['therapist_id'] === $selected_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['therapist_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary-custom btn-sm px-3">
                    <i class="bi bi-funnel me-1"></i>Apply
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
                        <h3 class="fw-bold mb-0 text-primary-custom"><?= $avg ?: 'N/A' ?></h3>
                        <div class="mt-1">
                            <?= $avg ? starHtml($avg, '.85rem') : '<small class="text-muted">No ratings yet</small>' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-card d-flex align-items-center gap-3">
                    <div class="metric-icon bg-light-green text-primary-custom"><i class="bi bi-chat-left-text-fill"></i></div>
                    <div>
                        <p class="text-secondary-custom small mb-0">Total Reviews</p>
                        <h3 class="fw-bold mb-0 text-primary-custom"><?= $totalReviews ?></h3>
                        <small class="text-secondary-custom">this period</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-card d-flex align-items-center gap-3">
                    <div class="metric-icon bg-light-green text-primary-custom"><i class="bi bi-calendar-check-fill"></i></div>
                    <div>
                        <p class="text-secondary-custom small mb-0">Sessions Completed</p>
                        <h3 class="fw-bold mb-0 text-primary-custom"><?= $totalSessions ?></h3>
                        <small class="text-secondary-custom">this period</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-card d-flex align-items-center gap-3">
                    <div class="metric-icon" style="background:#fff3cd;">
                        <i class="bi bi-person-x-fill" style="color:#e67e22;"></i>
                    </div>
                    <div>
                        <p class="text-secondary-custom small mb-0">No-Show Rate</p>
                        <h3 class="fw-bold mb-0" style="color:#e67e22;"><?= $noShowRate ?>%</h3>
                        <small class="text-secondary-custom">selected therapist</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rankings + Detail -->
        <div class="row g-4">

            <!-- Rankings list -->
            <div class="col-lg-5">
                <div class="card card-custom h-100">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold text-primary-custom mb-0"><i class="bi bi-trophy me-2"></i>Therapist Rankings</h5>
                        <small class="text-secondary-custom">Click a therapist to view detailed metrics</small>
                    </div>
                    <div class="card-body p-0 mt-2">
                        <?php if (empty($allTherapists)): ?>
                            <div class="no-data-state">
                                <i class="bi bi-people d-block mb-3"></i>
                                <p class="fw-semibold">No verified therapists found.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($allTherapists as $idx => $t):
                                $tid        = (int)$t['therapist_id'];
                                $tName      = htmlspecialchars($t['therapist_name']);
                                $tInit      = initials($t['therapist_name']);
                                $isSelected = ($tid === $selected_id);
                                $color      = $avatarColors[$idx % count($avatarColors)];
                                $stat       = $admin->getTherapistRankingStat($tid);
                                $tAvg       = (float)$stat['avg_rating'];
                                $tCnt       = (int)  $stat['cnt'];
                            ?>
                            <a href="performance.php?therapist_id=<?= $tid ?>"
                               class="therapist-row text-decoration-none <?= $isSelected ? 'selected' : '' ?>">
                                <div class="therapist-avatar" style="background:<?= $color ?>;"><?= $tInit ?></div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark"><?= $tName ?></div>
                                    <div class="d-flex align-items-center gap-2">
                                        <?= $tAvg ? starHtml($tAvg, '.75rem') : '<small class="text-muted">No ratings</small>' ?>
                                        <?php if ($tAvg): ?>
                                            <small class="text-secondary-custom"><?= $tAvg ?> · <?= $tCnt ?> reviews</small>
                                        <?php endif; ?>
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

            <!-- Detail panel -->
            <div class="col-lg-7">
                <div class="card card-custom h-100">
                    <div class="card-header bg-white border-0 pt-4 pb-0 d-flex align-items-center gap-3">
                        <div class="therapist-avatar" style="background:#2F8F7E; width:52px; height:52px; font-size:1.1rem;">
                            <?= htmlspecialchars($detailInit) ?>
                        </div>
                        <div>
                            <h5 class="fw-bold text-primary-custom mb-0"><?= $detailName ?></h5>
                            <small class="text-secondary-custom">Performance overview</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!$therapist): ?>
                            <div class="no-data-state">
                                <i class="bi bi-bar-chart d-block mb-3"></i>
                                <p class="fw-semibold">No therapist selected or no data available.</p>
                            </div>
                        <?php else: ?>

                            <!-- Rating breakdown bars -->
                            <h6 class="fw-bold text-primary-custom mb-3 mt-2">Rating Breakdown</h6>
                            <div class="chart-bar-wrap mb-4">
                                <?php
                                $starLabels = [5 => '5 Stars', 4 => '4 Stars', 3 => '3 Stars', 2 => '2 Stars', 1 => '1 Star'];
                                foreach ($starLabels as $star => $label):
                                    $pct        = ratingPercent($ratings, $star);
                                    $fillClass  = $star >= 4 ? '' : ($star === 3 ? 'warn' : 'danger');
                                    $valueColor = $star >= 4 ? 'var(--primary-green)' : ($star === 3 ? '#F4B41A' : '#dc3545');
                                ?>
                                <div class="chart-row">
                                    <span class="chart-label">
                                        <i class="bi bi-star-fill star-filled me-1" style="font-size:.75rem;"></i><?= $label ?>
                                    </span>
                                    <div class="chart-track">
                                        <div class="chart-fill <?= $fillClass ?>" style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <span class="chart-value" style="color:<?= $valueColor ?>"><?= $pct ?>%</span>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Summary stats -->
                            <div class="row g-3 mb-4">
                                <div class="col-4">
                                    <div class="text-center p-3 rounded-3 bg-light-green">
                                        <div class="fw-bold text-primary-custom fs-5"><?= $totalSessions ?></div>
                                        <small class="text-secondary-custom">Sessions</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-3 rounded-3 bg-light-green">
                                        <div class="fw-bold text-primary-custom fs-5"><?= $totalReviews ?></div>
                                        <small class="text-secondary-custom">Reviews</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-3 rounded-3" style="background:#fff3cd;">
                                        <div class="fw-bold fs-5" style="color:#e67e22;"><?= $noShowRate ?>%</div>
                                        <small class="text-secondary-custom">No-Show</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent feedback -->
                            <h6 class="fw-bold text-primary-custom mb-2">Recent Patient Feedback</h6>
                            <?php if (empty($recentFeedback)): ?>
                                <div class="no-data-state">
                                    <i class="bi bi-chat-left-text d-block mb-3"></i>
                                    <p class="fw-semibold">No written feedback yet.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentFeedback as $fb): ?>
                                    <div class="feedback-item">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <?= starHtml((float)$fb['rating'], '.75rem') ?>
                                            <small class="text-secondary-custom">
                                                <?= htmlspecialchars(date('d M Y', strtotime($fb['created_at']))) ?>
                                            </small>
                                        </div>
                                        <?= htmlspecialchars($fb['comment']) ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div><!-- /row -->
    </main>
</div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/main.js"></script>
<script>
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
</body>
</html>