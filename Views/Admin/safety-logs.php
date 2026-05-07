<?php
require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Core/Database.php';
session_start();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (empty($_SESSION['user_id'])) {
    header('Location: ../Auth/login.php');
    exit();
}

checkMethod($method);

if ($_SESSION['role'] !== 'Admin') {
    $map = [
        'Admin'     => 'admin-dashboard.php',
        'Patient'   => '../Patient/dashboard.php',
        'Moderator' => '../Moderator/dashboard.php',
    ];
    header('Location: ' . ($map[$_SESSION['role']] ?? '../Auth/login.php'));
    exit();
}

$role       = $_SESSION['role']       ?? 'Admin';
$first_name = $_SESSION['first_name'] ?? 'Admin';
$last_name  = $_SESSION['last_name']  ?? '';
$email      = $_SESSION['email']      ?? '';
$user_id    = $_SESSION['user_id'];

$conn = getConnection();

// Fetch admin info (age + gender in one query)
$stmtUser = $conn->prepare("SELECT age, gender FROM users WHERE user_id = ?");
$stmtUser->execute([$user_id]);
$currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
$age    = $currentUser['age']    ?? '';
$gender = $currentUser['gender'] ?? '';

// --- Search / Filter ---
$search   = trim($_GET['search']   ?? '');
$severity = trim($_GET['severity'] ?? '');

// Build query dynamically based on filters
$where  = [];
$params = [];

if ($search !== '') {
    $where[]  = "(al.eventID LIKE ? OR al.description LIKE ? OR al.incident_code LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($severity !== '') {
    $where[]  = "al.severity = ?";
    $params[] = $severity;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Join with users to resolve handledBy (int FK) → username
$stmtLogs = $conn->prepare("
    SELECT
        al.id,
        al.parent_log_id,
        al.eventID,
        al.incident_code,
        al.action,
        al.severity,
        al.description,
        al.handledBy,
        al.timestamp,
        u.username AS handled_by_name
    FROM audit_logs al
    LEFT JOIN users u ON u.user_id = al.handledBy
    {$whereSQL}
    ORDER BY al.timestamp DESC
");
$stmtLogs->execute($params);
$logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

// Severity badge helper
function severityBadge(string $sev): string {
    return match($sev) {
        'Critical' => '<span class="badge bg-danger">Critical</span>',
        'High'     => '<span class="badge bg-warning text-dark">High</span>',
        'Medium'   => '<span class="badge bg-info text-dark">Medium</span>',
        'Low'      => '<span class="badge bg-secondary">Low</span>',
        default    => '<span class="badge bg-light text-dark">' . htmlspecialchars($sev) . '</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safety Logs - Admin MHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .nav-link[href*="therapist-register"] {
            color: #198754 !important;
            font-weight: 600 !important;
            background: rgba(25, 135, 84, 0.1) !important;
            border: 1px solid #198754 !important;
        }
    </style>
    <style>
        .log-row-critical { border-left: 4px solid #dc3545; }
        .log-row-high     { border-left: 4px solid #ffc107; }
        .log-row-medium   { border-left: 4px solid #0dcaf0; }
        .log-row-low      { border-left: 4px solid #6c757d; }
        .immutable-flash  { animation: flashRed .6s ease; }
        @keyframes flashRed {
            0%,100% { background: transparent; }
            50%      { background: rgba(220,53,69,.15); }
        }
        .filter-bar { background:#f8fdf9; border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.25rem; }
        .stat-pill  { background:#fff; border-radius:10px; padding:.5rem 1.1rem;
                      box-shadow:0 2px 8px rgba(47,143,126,.09); font-size:.85rem; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white shadow-sm">
            <div class="position-sticky pt-4">
                <div class="text-center mb-4">
                    <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size:2rem;"></i>
                    <h5 class="fw-bold text-primary-custom mt-2">MentalCare System</h5>
                </div>
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="patients.php">
                            <i class="bi bi-people me-2"></i> Manage Patients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Auth/therapist-register.php">
                            <i class="bi bi-plus-circle me-2"></i>Add Therapist
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="therapists.php">
                            <i class="bi bi-person-badge me-2"></i> Therapists Verification
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="rbac.php">
                            <i class="bi bi-shield-lock me-2"></i> RBAC Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="performance.php">
                            <i class="bi bi-bar-chart-line me-2"></i> Therapist Performance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="safety-logs.php">
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
                    <h1 class="h2 text-primary-custom fw-bold">
                        <i class="bi bi-journal-medical me-2"></i>Safety &amp; Audit Logs
                    </h1>
                    <p class="text-secondary-custom mb-0">Immutable record of high-risk events and system actions.</p>
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                    <span class="text-secondary-custom fw-bold">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo 'Age: ' . ($age ?: 'N/A') . ' | ' . $role . ' | '
                            . htmlspecialchars($first_name . ' ' . $last_name) . ' | '
                            . htmlspecialchars($gender); ?>
                    </span>
                    <div class="alert alert-info py-2 mb-0 border-0 bg-light-green text-primary-custom">
                        <i class="bi bi-shield-check me-2"></i>
                        <strong>WORM compliant:</strong> Write Once, Read Many
                    </div>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="d-flex flex-wrap gap-3 mb-4">
                <?php
                $counts = ['Critical' => 0, 'High' => 0, 'Medium' => 0, 'Low' => 0];
                foreach ($logs as $log) {
                    if (isset($counts[$log['severity']])) $counts[$log['severity']]++;
                }
                $statColors = [
                    'Critical' => '#dc3545',
                    'High'     => '#ffc107',
                    'Medium'   => '#0dcaf0',
                    'Low'      => '#6c757d',
                ];
                foreach ($counts as $sev => $cnt):
                ?>
                <div class="stat-pill d-flex align-items-center gap-2">
                    <span class="fw-bold" style="color:<?php echo $statColors[$sev]; ?>"><?php echo $cnt; ?></span>
                    <span class="text-secondary-custom"><?php echo $sev; ?></span>
                </div>
                <?php endforeach; ?>
                <div class="stat-pill d-flex align-items-center gap-2 ms-auto">
                    <i class="bi bi-list-ul text-primary-custom"></i>
                    <span class="fw-bold text-primary-custom"><?php echo count($logs); ?></span>
                    <span class="text-secondary-custom">Total Logs</span>
                </div>
            </div>

            <!-- Filter Bar -->
            <form method="GET" action="admin-safety-logs.php" class="filter-bar d-flex flex-wrap gap-3 align-items-end">
                <div class="flex-grow-1" style="min-width:200px;">
                    <label class="form-label small fw-semibold text-secondary-custom mb-1">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-secondary-custom"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="form-control border-start-0 ps-0"
                            placeholder="Event ID, description, incident code…"
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div style="min-width:160px;">
                    <label class="form-label small fw-semibold text-secondary-custom mb-1">Severity</label>
                    <select name="severity" class="form-select">
                        <option value="">All Severities</option>
                        <?php foreach (['Critical','High','Medium','Low'] as $sev): ?>
                            <option value="<?php echo $sev; ?>" <?php echo $severity === $sev ? 'selected' : ''; ?>>
                                <?php echo $sev; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom px-4">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="safety-logs.php" class="btn btn-outline-secondary px-3">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                </div>
            </form>

            <!-- Logs Table -->
            <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-danger mb-0">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>High Risk Event Logs
                    </h5>
                    <small class="text-secondary-custom">
                        Showing <strong><?php echo count($logs); ?></strong> record(s)
                        <?php if ($search || $severity) echo '— filtered'; ?>
                    </small>
                </div>
                <div class="card-body p-0">
                    <div class="alert alert-warning m-3 py-2">
                        <small>
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Immutability Demo:</strong> Double-click a row or click the
                            <i class="bi bi-trash-fill"></i> icon to test WORM protections.
                        </small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0" id="logsTable">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Timestamp (UTC)</th>
                                    <th class="px-4 py-3">Event ID</th>
                                    <th class="px-4 py-3">Incident Code</th>
                                    <th class="px-4 py-3">Severity</th>
                                    <th class="px-4 py-3">Description</th>
                                    <th class="px-4 py-3">Action Taken</th>
                                    <th class="px-4 py-3">Handled By</th>
                                    <th class="px-4 py-3 text-center">
                                        <i class="bi bi-lock-fill" title="Immutable"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-secondary-custom">
                                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                            No log records found<?php echo ($search || $severity) ? ' matching your filters.' : '.'; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log):
                                        $sevClass = 'log-row-' . strtolower($log['severity']);
                                        $handler  = $log['handled_by_name']
                                                    ? htmlspecialchars($log['handled_by_name'])
                                                    : ($log['handledBy'] ? 'User #' . $log['handledBy'] : 'System');
                                        $ts = $log['timestamp']
                                            ? date('Y-m-d H:i:s', strtotime($log['timestamp']))
                                            : '—';
                                    ?>
                                    <tr class="safety-log-row <?php echo $sevClass; ?>"
                                        data-id="<?php echo (int)$log['id']; ?>"
                                        title="Double-click to attempt edit (Forbidden)">
                                        <td class="px-4 py-3 text-muted font-monospace small"><?php echo $ts; ?></td>
                                        <td class="px-4 py-3 fw-semibold"><?php echo htmlspecialchars($log['eventID'] ?? '—'); ?></td>
                                        <td class="px-4 py-3 font-monospace small"><?php echo htmlspecialchars($log['incident_code'] ?? '—'); ?></td>
                                        <td class="px-4 py-3"><?php echo severityBadge($log['severity']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($log['description'] ?? '—'); ?></td>
                                        <td class="px-4 py-3 text-secondary-custom small"><?php echo htmlspecialchars($log['action'] ?? '—'); ?></td>
                                        <td class="px-4 py-3"><?php echo $handler; ?></td>
                                        <td class="px-4 py-3 text-center">
                                            <button class="btn btn-sm btn-light text-danger delete-log-btn"
                                                    data-id="<?php echo (int)$log['id']; ?>"
                                                    title="Delete Log (Forbidden)">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Toast Container -->
<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>

<!-- Immutability Modal -->
<div class="modal fade" id="immutableModal" tabindex="-1" aria-labelledby="immutableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="immutableModalLabel">
                    <i class="bi bi-shield-lock-fill me-2"></i>Action Forbidden
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-exclamation-octagon-fill text-danger" style="font-size:3rem;"></i>
                <h5 class="mt-3 fw-bold" id="immutableModalMessage">This log record is immutable.</h5>
                <p class="text-secondary-custom mt-2 mb-0">
                    Safety logs are <strong>WORM-protected</strong>. Modifications and deletions are
                    permanently blocked and this attempt has been recorded.
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">Understood</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/admin.js"></script>
<script>
(function () {
    const modal      = new bootstrap.Modal(document.getElementById('immutableModal'));
    const msgEl      = document.getElementById('immutableModalMessage');
    const toastCont  = document.getElementById('toastContainer');

    function showToast(msg, type = 'danger') {
        const id = 'toast_' + Date.now();
        toastCont.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive">
                <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`);
        new bootstrap.Toast(document.getElementById(id), { delay: 4000 }).show();
    }

    function showImmutableModal(action, logId) {
        msgEl.textContent = `Cannot ${action} log #${logId} — WORM protection active.`;
        modal.show();
        showToast(`<i class="bi bi-shield-lock-fill me-1"></i> Forbidden: attempt to ${action} log #${logId} has been recorded.`);
    }

    document.querySelectorAll('.delete-log-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const row   = this.closest('tr');
            const logId = this.dataset.id || row?.dataset.id || '?';
            row?.classList.add('immutable-flash');
            setTimeout(() => row?.classList.remove('immutable-flash'), 700);
            showImmutableModal('delete', logId);
        });
    });

    document.querySelectorAll('.safety-log-row').forEach(row => {
        row.addEventListener('dblclick', function () {
            const logId = this.dataset.id || '?';
            this.classList.add('immutable-flash');
            setTimeout(() => this.classList.remove('immutable-flash'), 700);
            showImmutableModal('edit', logId);
        });
    });
})();
</script>
</body>
</html>