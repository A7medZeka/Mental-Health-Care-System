<?php
session_start();
require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

// 1. Page Protection
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Moderator') {
    header('Location: ../Auth/login.php');
    exit();
}

$modName = $_SESSION['first_name'] ?? 'Moderator';

// =========================================================================
// 2. Performance Service (100% REAL DATA - FULLY LOGICAL)
// =========================================================================
class PerformanceService {
    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function getDashboardData(int $days): array {
        // Fetch all active therapists including License Expiry Date
        $stmt = $this->db->prepare("
            SELECT u.user_id, u.first_name, u.last_name, t.specialization, t.license_expiry_date
            FROM users u
            LEFT JOIN therapists t ON u.user_id = t.therapist_id
            WHERE u.role = 'Therapist'
            AND u.status IN ('Active', 'Registered', 'Screened', 'Matched')
        ");
        $stmt->execute();
        $therapists = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $kpis = ['avg_rating' => 0.0, 'total_reviews' => 0, 'sessions_completed' => 0, 'no_show_rate' => 0.0];

        // Global KPI Stats for the period
        $revStmt = $this->db->prepare("SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM therapist_reviews WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $revStmt->execute([$days]);
        $revData = $revStmt->fetch(PDO::FETCH_ASSOC);
        $kpis['avg_rating'] = $revData['cnt'] > 0 ? round((float)$revData['avg_r'], 1) : 0.0;
        $kpis['total_reviews'] = (int)$revData['cnt'];

        $sessStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE status = 'Completed' AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $sessStmt->execute([$days]);
        $kpis['sessions_completed'] = (int)$sessStmt->fetchColumn();

        // Global No-Show Rate Calculation
        $totApptStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $totApptStmt->execute([$days]);
        $totalApps = (int)$totApptStmt->fetchColumn();

        $nsApptStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE status = 'No-Show' AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $nsApptStmt->execute([$days]);
        $nsApps = (int)$nsApptStmt->fetchColumn();
        $kpis['no_show_rate'] = $totalApps > 0 ? round(($nsApps / $totalApps) * 100, 1) : 0.0;

        $colors = ['#2F8F7E', '#48B6A2', '#F4B41A', '#8F5E2F', '#6c757d'];

        foreach ($therapists as $index => &$t) {
            $tid = (int)$t['user_id'];
            $t['initials'] = strtoupper(substr($t['first_name'], 0, 1) . substr($t['last_name'], 0, 1));
            $t['color'] = $colors[$index % 5];

            // 1. Real Rating and Review Count
            $trStmt = $this->db->prepare("SELECT AVG(rating) as r_avg, COUNT(*) as r_count FROM therapist_reviews WHERE therapist_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
            $trStmt->execute([$tid, $days]);
            $trData = $trStmt->fetch(PDO::FETCH_ASSOC);
            $t['rating'] = round((float)($trData['r_avg'] ?? 0), 1);
            $t['reviews_count'] = (int)($trData['r_count'] ?? 0);

            // 2. Real Completed Sessions
            $tsStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE therapist_id = ? AND status = 'Completed' AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
            $tsStmt->execute([$tid, $days]);
            $tSess = (int)$tsStmt->fetchColumn();

            // 3. REAL Unique Patient Count
            $tpStmt = $this->db->prepare("SELECT COUNT(DISTINCT patient_id) FROM appointments WHERE therapist_id = ? AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
            $tpStmt->execute([$tid, $days]);
            $tPat = (int)$tpStmt->fetchColumn();

            // 4. REAL No-Show Rate for this therapist
            $ttStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE therapist_id = ? AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
            $ttStmt->execute([$tid, $days]);
            $therapistTotal = (int)$ttStmt->fetchColumn();

            $tnStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE therapist_id = ? AND status = 'No-Show' AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
            $tnStmt->execute([$tid, $days]);
            $therapistNS = (int)$tnStmt->fetchColumn();
            $tNoShowRate = $therapistTotal > 0 ? round(($therapistNS / $therapistTotal) * 100, 1) : 0.0;

            // 5. Feedback Comments (Matching 'comment' column in SQL)
            $fbStmt = $this->db->prepare("SELECT rating as stars, created_at, comment as text FROM therapist_reviews WHERE therapist_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) ORDER BY created_at DESC LIMIT 5");
            $fbStmt->execute([$tid, $days]);
            $feedback = [];
            while ($fb = $fbStmt->fetch(PDO::FETCH_ASSOC)) {
                $feedback[] = [
                        'stars' => (int)$fb['stars'],
                        'time'  => date('M j, Y', strtotime($fb['created_at'])),
                        'text'  => htmlspecialchars($fb['text'], ENT_QUOTES, 'UTF-8')
                ];
            }

            // 6. Star Breakdown percentages
            $breakdown = ['star5' => 0, 'star4' => 0, 'star3' => 0, 'star2' => 0, 'star1' => 0];
            if ($t['reviews_count'] > 0) {
                $bdStmt = $this->db->prepare("SELECT rating, COUNT(*) as cnt FROM therapist_reviews WHERE therapist_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY rating");
                $bdStmt->execute([$tid, $days]);
                while ($row = $bdStmt->fetch(PDO::FETCH_ASSOC)) {
                    $starKey = 'star' . round($row['rating']);
                    if (isset($breakdown[$starKey])) {
                        $breakdown[$starKey] = round(($row['cnt'] / $t['reviews_count']) * 100);
                    }
                }
            }

            $t['details'] = [
                    'sessions'       => $tSess,
                    'patients'       => $tPat,
                    'no_show'        => $tNoShowRate,
                    'breakdown'      => $breakdown,
                    'feedback'       => $feedback,
                    'license_expiry' => $t['license_expiry_date'] ?? 'Not Set'
            ];
        }

        usort($therapists, fn($a, $b) => $b['rating'] <=> $a['rating']);
        return ['kpis' => $kpis, 'therapists' => $therapists];
    }
}

// 3. Execution
$perfService = new PerformanceService();
$period = isset($_GET['period']) ? (int)$_GET['period'] : 30;
$data = $perfService->getDashboardData($period);
$kpis = $data['kpis'];
$therapists = $data['therapists'];

function renderStars($rating) {
    $html = '';
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    for ($i=0; $i<$fullStars; $i++) $html .= '<i class="bi bi-star-fill star-filled"></i>';
    if ($halfStar) $html .= '<i class="bi bi-star-half star-filled"></i>';
    for ($i=0; $i<(5 - $fullStars - $halfStar); $i++) $html .= '<i class="bi bi-star star-empty"></i>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapist Performance - MentalCare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .star-rating { display:inline-flex; gap:2px; }
        .star-filled  { color:#F4B41A; }
        .star-empty   { color:#dee2e6; }
        .metric-card { border:none; border-radius:14px; background:#fff; box-shadow:0 2px 18px rgba(47,143,126,0.08); padding:1.5rem; transition:transform .25s, box-shadow .25s; }
        .metric-card:hover { transform:translateY(-3px); box-shadow:0 6px 28px rgba(47,143,126,0.14); }
        .metric-icon { width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; }

        .chart-bar-wrap { display:flex; flex-direction:column; gap:.55rem; }
        .chart-row { display:flex; align-items:center; gap:.75rem; font-size:.85rem; }
        .chart-label { width:110px; flex-shrink:0; color:var(--text-brown); font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .chart-track { flex:1; background:#e9ecef; border-radius:20px; height:12px; overflow:hidden; }
        .chart-fill  { height:100%; border-radius:20px; background:var(--primary-green); transition:width .8s cubic-bezier(.4,0,.2,1); }
        .chart-fill.warn { background:#F4B41A; }
        .chart-fill.danger { background:#dc3545; }
        .chart-value { width:42px; text-align:right; font-weight:700; color:var(--primary-green); font-size:.85rem; }

        .therapist-row { display:flex; align-items:center; gap:1rem; padding:1rem 1.25rem; border-bottom:1px solid rgba(0,0,0,0.05); transition:background .15s; cursor:pointer; }
        .therapist-row:last-child { border-bottom:none; }
        .therapist-row:hover { background:var(--light-green); }
        .therapist-row.selected { background:#e6f5f2; border-left: 4px solid var(--primary-green); }
        .therapist-avatar { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:.95rem; flex-shrink:0; }

        .feedback-item { padding:.85rem 1rem; border-radius:10px; background:#f8fdf9; border:1px solid rgba(47,143,126,0.12); margin-bottom:.6rem; font-size:.88rem; }
        .sparkline { display:flex; align-items:flex-end; gap:3px; height:36px; }
        .spark-bar { width:8px; border-radius:3px 3px 0 0; background:var(--primary-green); opacity:.7; flex-shrink:0; }
        .period-btn { border-radius:20px; font-size:.82rem; font-weight:600; padding:.3rem .9rem; border:2px solid #dee2e6; background:transparent; color:var(--text-brown); cursor:pointer; transition:all .2s; text-decoration: none; }
        .period-btn.active, .period-btn:hover { background:var(--primary-green); color:#fff; border-color:var(--primary-green); }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white shadow-sm">
            <div class="position-sticky pt-4">
                <div class="text-center mb-4">
                    <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size:2rem;"></i>
                    <h5 class="fw-bold text-primary-custom mt-2">MentalCare System</h5>
                    <span class="badge" style="background:var(--primary-green);color:white;">Moderator Portal</span>
                </div>
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="forum.php"><i class="bi bi-shield-exclamation me-2"></i> Forum Moderation</a></li>
                    <li class="nav-item"><a class="nav-link active" href="performance.php"><i class="bi bi-bar-chart-line me-2"></i> Therapist Performance</a></li>
                    <li class="nav-item"><a class="nav-link" href="safety-audit.php"><i class="bi bi-journal-medical me-2"></i> Safety Audit Log</a></li>
                </ul>
                <hr class="mx-3 mt-5">
                <div class="px-3">
                    <a href="../Auth/logout.php" class="btn btn-outline-danger w-100 mt-3 d-flex align-items-center justify-content-center">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <div>
                    <h1 class="h2 text-primary-custom fw-bold"><i class="bi bi-bar-chart-line me-2"></i>Therapist Performance Metrics</h1>
                    <p class="text-secondary-custom mb-0">UC-32 — Aggregated ratings, feedback and trend analysis across therapists.</p>
                </div>
                <span class="text-secondary-custom fw-bold"><i class="bi bi-person-circle me-1"></i> Moderator: <?php echo htmlspecialchars($modName); ?></span>
            </div>

            <div class="d-flex align-items-center gap-3 flex-wrap mb-4">
                <div class="d-flex gap-2">
                    <a href="?period=7" class="period-btn <?php echo $period == 7 ? 'active' : ''; ?>">Last 7 Days</a>
                    <a href="?period=30" class="period-btn <?php echo $period == 30 ? 'active' : ''; ?>">Last 30 Days</a>
                    <a href="?period=90" class="period-btn <?php echo $period == 90 ? 'active' : ''; ?>">Last 90 Days</a>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <select class="form-select form-select-sm" id="therapistFilterSelect" style="min-width:180px;">
                        <option value="all">All Therapists</option>
                        <?php foreach($therapists as $index => $t): ?>
                            <option value="<?php echo $index; ?>">Dr. <?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary-custom btn-sm px-3" id="btnApplyFilter" onclick="applyTherapistFilter()">
                        <i class="bi bi-funnel me-1"></i> Apply
                    </button>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon bg-light-green text-primary-custom"><i class="bi bi-star-fill"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">Avg. Rating</p>
                            <h3 class="fw-bold mb-0 text-primary-custom"><?php echo number_format($kpis['avg_rating'], 1); ?></h3>
                            <div class="star-rating mt-1" style="font-size:0.8rem;"><?php echo renderStars($kpis['avg_rating']); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon bg-light-green text-primary-custom"><i class="bi bi-chat-left-text-fill"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">Total Reviews</p>
                            <h3 class="fw-bold mb-0 text-primary-custom"><?php echo $kpis['total_reviews']; ?></h3>
                            <small class="text-secondary-custom">this period</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon bg-light-green text-primary-custom"><i class="bi bi-calendar-check-fill"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">Sessions Completed</p>
                            <h3 class="fw-bold mb-0 text-primary-custom"><?php echo $kpis['sessions_completed']; ?></h3>
                            <small class="text-secondary-custom">this period</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon" style="background:#fff3cd;"><i class="bi bi-person-x-fill" style="color:#e67e22;"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">No-Show Rate</p>
                            <h3 class="fw-bold mb-0" style="color:#e67e22;"><?php echo $kpis['no_show_rate']; ?>%</h3>
                            <small class="text-secondary-custom">across all therapists</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h5 class="fw-bold text-primary-custom mb-0"><i class="bi bi-trophy me-2"></i>Therapist Rankings</h5>
                            <small class="text-secondary-custom">Click a therapist to view detailed metrics</small>
                        </div>
                        <div class="card-body p-0 mt-2" id="therapistList">
                            <?php if (empty($therapists)): ?>
                                <p class="text-center text-muted p-4">No active therapists found.</p>
                            <?php else: ?>
                                <?php foreach ($therapists as $index => $t): ?>
                                    <div class="therapist-row" id="row-therapist-<?php echo $index; ?>" onclick="loadTherapistDetails(<?php echo $index; ?>)">
                                        <div class="therapist-avatar" style="background:<?php echo $t['color']; ?>;"><?php echo $t['initials']; ?></div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">Dr. <?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></div>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="star-rating" style="font-size:.75rem;">
                                                    <?php echo renderStars($t['rating']); ?>
                                                </div>
                                                <small class="text-secondary-custom"><?php echo number_format($t['rating'], 1); ?> · <?php echo $t['reviews_count']; ?> reviews</small>
                                            </div>
                                        </div>
                                        <?php if ($index === 0 && count($therapists) > 1 && $t['reviews_count'] > 0): ?>
                                            <span class="badge" style="background:var(--light-green); color:var(--primary-green);">Top</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card card-custom h-100" id="detailPanel">
                        <div class="card-header bg-white border-0 pt-4 pb-0 d-flex align-items-start gap-3">
                            <div class="therapist-avatar" id="detailAvatar" style="background:#2F8F7E; width:52px; height:52px; font-size:1.1rem;">--</div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold text-primary-custom mb-0" id="detailName">Select a Therapist</h5>
                                <small class="text-secondary-custom" id="detailSpec">Specialisation: --</small>
                                <br>
                                <span class="badge bg-danger mt-1" style="font-size: 0.75rem;" id="detailLicense">License Expires: --</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold text-primary-custom mb-3 mt-2">Rating Breakdown</h6>
                            <div class="chart-bar-wrap mb-4" id="detailBreakdown"></div>

                            <div class="row g-3 mb-4">
                                <div class="col-4">
                                    <div class="text-center p-3 rounded-3 bg-light-green">
                                        <div class="fw-bold text-primary-custom fs-5" id="dKpiSessions">0</div>
                                        <small class="text-secondary-custom">Sessions</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-3 rounded-3 bg-light-green">
                                        <div class="fw-bold text-primary-custom fs-5" id="dKpiPatients">0</div>
                                        <small class="text-secondary-custom">Patients</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-3 rounded-3" style="background:#fff3cd;">
                                        <div class="fw-bold fs-5" style="color:#e67e22;" id="dKpiNoShow">0%</div>
                                        <small class="text-secondary-custom">No-Show Rate</small>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold text-primary-custom mb-2">Recent Patient Feedback</h6>
                            <div id="detailFeedback"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const therapistsData = <?php echo json_encode($therapists); ?>;

    function getStarsHTML(rating) {
        let html = '';
        let full = Math.floor(rating);
        let half = (rating - full) >= 0.5 ? 1 : 0;
        let empty = 5 - full - half;
        for(let i=0; i<full; i++) html += '<i class="bi bi-star-fill star-filled"></i>';
        if(half) html += '<i class="bi bi-star-half star-filled"></i>';
        for(let i=0; i<empty; i++) html += '<i class="bi bi-star star-empty"></i>';
        return html;
    }

    function loadTherapistDetails(index) {
        document.querySelectorAll('.therapist-row').forEach(el => el.classList.remove('selected'));
        document.getElementById('row-therapist-' + index).classList.add('selected');

        const data = therapistsData[index];
        if(!data) return;

        document.getElementById('detailAvatar').innerText = data.initials;
        document.getElementById('detailAvatar').style.background = data.color;
        document.getElementById('detailName').innerText = 'Dr. ' + data.first_name + ' ' + data.last_name;
        document.getElementById('detailSpec').innerText = 'Specialisation: ' + (data.specialization || 'General');
        document.getElementById('detailLicense').innerText = 'License Expires: ' + (data.details.license_expiry || 'Not Set');

        document.getElementById('dKpiSessions').innerText = data.details.sessions;
        document.getElementById('dKpiPatients').innerText = data.details.patients;
        document.getElementById('dKpiNoShow').innerText = data.details.no_show + '%';

        const bd = data.details.breakdown;
        let breakdownHTML = `
            <div class="chart-row"><span class="chart-label">5 Stars</span><div class="chart-track"><div class="chart-fill" style="width:${bd.star5}%"></div></div><span class="chart-value">${bd.star5}%</span></div>
            <div class="chart-row"><span class="chart-label">4 Stars</span><div class="chart-track"><div class="chart-fill" style="width:${bd.star4}%"></div></div><span class="chart-value">${bd.star4}%</span></div>
            <div class="chart-row"><span class="chart-label">3 Stars</span><div class="chart-track"><div class="chart-fill warn" style="width:${bd.star3}%"></div></div><span class="chart-value" style="color:#F4B41A;">${bd.star3}%</span></div>
            <div class="chart-row"><span class="chart-label">2 Stars</span><div class="chart-track"><div class="chart-fill danger" style="width:${bd.star2}%"></div></div><span class="chart-value" style="color:#dc3545;">${bd.star2}%</span></div>
            <div class="chart-row"><span class="chart-label">1 Star</span><div class="chart-track"><div class="chart-fill danger" style="width:${bd.star1}%"></div></div><span class="chart-value" style="color:#dc3545;">${bd.star1}%</span></div>
        `;
        document.getElementById('detailBreakdown').innerHTML = breakdownHTML;

        let fbHTML = '';
        data.details.feedback.forEach(fb => {
            fbHTML += `
                <div class="feedback-item">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="star-rating" style="font-size:.75rem;">${getStarsHTML(fb.stars)}</div>
                        <small class="text-secondary-custom">${fb.time}</small>
                    </div>
                    "${fb.text}"
                </div>
            `;
        });
        document.getElementById('detailFeedback').innerHTML = fbHTML || '<p class="text-muted small">No recent feedback.</p>';
    }

    function applyTherapistFilter() {
        const selectedVal = document.getElementById('therapistFilterSelect').value;
        if(selectedVal !== 'all') {
            loadTherapistDetails(selectedVal);
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        if(therapistsData.length > 0) {
            loadTherapistDetails(0);
        }
    });
</script>
</body>
</html>