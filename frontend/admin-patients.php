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

$conn = getConnection();

if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');

    $patientId  = filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT);
    $newStatus  = trim($_POST['new_status'] ?? '');
    $currentStatus = trim($_POST['current_status'] ?? '');

    $validStatuses = ['Registered', 'Screened', 'Matched', 'Active'];
    $validFlow = [
        'Registered' => 'Screened',
        'Screened'   => 'Matched',
        'Matched'    => 'Active',
        'Active'     => null,
    ];

    if (!$patientId) {
        echo json_encode(['success' => false, 'message' => 'Invalid patient ID.']);
        exit();
    }

    if (!in_array($newStatus, $validStatuses, true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
        exit();
    }

    $allowedNext = $validFlow[$currentStatus] ?? null;
    if ($newStatus !== $allowedNext) {
        echo json_encode([
            'success' => false,
            'message' => "Invalid transition. From '{$currentStatus}' you can only move to '" . ($allowedNext ?? 'nowhere') . "'."
        ]);
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, status FROM users WHERE user_id = ? AND role = 'Patient'");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch();

    if (!$patient) {
        echo json_encode(['success' => false, 'message' => 'Patient not found.']);
        exit();
    }

    if ($patient['status'] !== $currentStatus) {
        echo json_encode(['success' => false, 'message' => 'Status mismatch. Please refresh the page.']);
        exit();
    }

    // Update
    $update = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'Patient'");
    $update->execute([$newStatus, $patientId]);

    echo json_encode(['success' => true, 'message' => "Status updated to '{$newStatus}' successfully."]);
    exit();
}

if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_intake') {
    header('Content-Type: application/json');

    $patientId = filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT);

    if (!$patientId) {
        echo json_encode(['success' => false, 'message' => 'Invalid patient ID.']);
        exit();
    }

    if (!isset($_FILES['intakeFile']) || $_FILES['intakeFile']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
        exit();
    }

    $file     = $_FILES['intakeFile'];
    $maxSize  = 5 * 1024 * 1024; // 5 MB

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if ($mimeType !== 'application/pdf') {
        echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed.']);
        exit();
    }

    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File exceeds the 5 MB size limit.']);
        exit();
    }

    // Safe filename
    $uploadDir = __DIR__ . '/uploads/intake/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0750, true);
    }

    $safeFilename = 'intake_' . $patientId . '_' . time() . '.pdf';
    $destination  = $uploadDir . $safeFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file. Please try again.']);
        exit();
    }

    // Save record in DB
    $stmt = $conn->prepare(
        "INSERT INTO intake_forms (patient_id, file_path, uploaded_by, uploaded_at)
         VALUES (?, ?, ?, NOW())"
    );
    $stmt->execute([$patientId, 'uploads/intake/' . $safeFilename, $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Intake form uploaded successfully.']);
    exit();
}

$stmt = $conn->prepare(
    "SELECT user_id, first_name, last_name, username, email, status, created_at
     FROM users
     WHERE role = 'Patient'
     ORDER BY created_at DESC"
);
$stmt->execute();
$patients     = $stmt->fetchAll();
$totalPatients = count($patients);

$featured = $patients[0] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - Admin MHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <li class="nav-item"><a class="nav-link" href="admin-dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin-patients.php"><i class="bi bi-people me-2"></i> Manage Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-therapists.php"><i class="bi bi-person-badge me-2"></i> Therapists Verification</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-rbac.php"><i class="bi bi-shield-lock me-2"></i> RBAC Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-performance.php"><i class="bi bi-bar-chart-line me-2"></i> Therapist Performance</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-safety-logs.php"><i class="bi bi-journal-medical me-2"></i> Safety Logs</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 text-primary-custom fw-bold">
                    Manage Patients
                    <span class="badge bg-secondary fs-6 ms-2"><?php echo $totalPatients; ?> total</span>
                </h1>
            </div>

            <?php if ($featured): ?>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card card-custom h-100" id="profileCard">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h5 class="fw-bold text-primary-custom">
                                Patient Profile:
                                <span id="profileName">
                                    <?php echo htmlspecialchars($featured['first_name'] . ' ' . $featured['last_name']); ?>
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>ID:</strong> PT-<span id="profileId"><?php echo $featured['user_id']; ?></span><br>
                                <strong>Username:</strong> <span id="profileUsername"><?php echo htmlspecialchars($featured['username']); ?></span><br>
                                <strong>Email:</strong> <span id="profileEmail"><?php echo htmlspecialchars($featured['email']); ?></span><br>
                                <strong>Current Status:</strong>
                                <span class="badge" id="currentStatusBadge"><?php echo htmlspecialchars($featured['status']); ?></span>
                                <input type="hidden" id="currentStatus" value="<?php echo htmlspecialchars($featured['status']); ?>">
                                <input type="hidden" id="selectedPatientId" value="<?php echo $featured['user_id']; ?>">
                            </div>
                            <hr>
                            <h6 class="text-secondary-custom mb-3">1. Update Status Transition</h6>
                            <p class="small text-muted mb-2">Valid flow: Registered → Screened → Matched → Active</p>
                            <div class="d-flex gap-2 align-items-center mb-3">
                                <select class="form-select w-auto" id="newStatusSelect">
                                    <option value="Registered">Registered</option>
                                    <option value="Screened">Screened</option>
                                    <option value="Matched">Matched</option>
                                    <option value="Active">Active</option>
                                </select>
                                <button class="btn btn-primary-custom" id="updateStatusBtn">Confirm Update</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Intake Documentation -->
                <div class="col-lg-6 mb-4">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h5 class="fw-bold text-primary-custom">Intake Documentation</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-secondary-custom mb-3">
                                Upload intake form for: <strong id="intakePatientName">
                                    <?php echo htmlspecialchars($featured['first_name'] . ' ' . $featured['last_name']); ?>
                                </strong>
                            </p>
                            <div class="mb-3">
                                <label for="intakeFile" class="form-label fw-semibold">Select PDF File (max 5 MB)</label>
                                <input class="form-control" type="file" id="intakeFile" accept=".pdf">
                            </div>
                            <button class="btn btn-primary-custom" id="uploadIntakeBtn">
                                <i class="bi bi-upload me-2"></i>Upload File
                            </button>
                            <div id="uploadFeedback" class="mt-2 small"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-info">No patients found in the system.</div>
            <?php endif; ?>

            <!-- Patients Table -->
            <div class="card card-custom mt-2">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Patient ID</th>
                                    <th class="px-4 py-3">Full Name</th>
                                    <th class="px-4 py-3">Username</th>
                                    <th class="px-4 py-3">Registration Date</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody id="patientsTableBody">
                                <?php foreach ($patients as $patient):
                                    $statusColors = [
                                        'Registered' => 'secondary',
                                        'Screened'   => 'info',
                                        'Matched'    => 'warning',
                                        'Active'     => 'success',
                                    ];
                                    $badgeColor = $statusColors[$patient['status']] ?? 'secondary';
                                    $fullName   = htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']);
                                    $regDate    = date('M j, Y', strtotime($patient['created_at']));
                                ?>
                                <tr data-patient-id="<?php echo $patient['user_id']; ?>"
                                    data-status="<?php echo htmlspecialchars($patient['status']); ?>"
                                    data-name="<?php echo $fullName; ?>"
                                    data-username="<?php echo htmlspecialchars($patient['username']); ?>"
                                    data-email="<?php echo htmlspecialchars($patient['email']); ?>">
                                    <td class="px-4 py-3 fw-semibold">PT-<?php echo $patient['user_id']; ?></td>
                                    <td class="px-4 py-3"><?php echo $fullName; ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($patient['username']); ?></td>
                                    <td class="px-4 py-3"><?php echo $regDate; ?></td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-<?php echo $badgeColor; ?> status-badge">
                                            <?php echo htmlspecialchars($patient['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <button class="btn btn-sm btn-outline-primary select-patient-btn">
                                            <i class="bi bi-eye me-1"></i>View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
// ─── Status badge colour map ───────────────────────────────
const statusColors = {
    Registered: 'secondary',
    Screened:   'info',
    Matched:    'warning',
    Active:     'success'
};

// ─── Helper: show Bootstrap toast ─────────────────────────
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const id = 'toast-' + Date.now();
    container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`);
    const toast = new bootstrap.Toast(document.getElementById(id), { delay: 4000 });
    toast.show();
}

// ─── Select a patient row → load into profile card ────────
document.getElementById('patientsTableBody').addEventListener('click', function (e) {
    const btn = e.target.closest('.select-patient-btn');
    if (!btn) return;

    const row = btn.closest('tr');
    const id       = row.dataset.patientId;
    const name     = row.dataset.name;
    const username = row.dataset.username;
    const email    = row.dataset.email;
    const status   = row.dataset.status;

    // Update profile card
    document.getElementById('profileId').textContent       = id;
    document.getElementById('profileName').textContent     = name;
    document.getElementById('profileUsername').textContent = username;
    document.getElementById('profileEmail').textContent    = email;
    document.getElementById('selectedPatientId').value     = id;
    document.getElementById('currentStatus').value         = status;
    document.getElementById('intakePatientName').textContent = name;

    const badge = document.getElementById('currentStatusBadge');
    badge.textContent  = status;
    badge.className    = 'badge bg-' + (statusColors[status] ?? 'secondary');

    // Highlight row
    document.querySelectorAll('#patientsTableBody tr').forEach(r => r.classList.remove('table-active'));
    row.classList.add('table-active');

    // Scroll to card
    document.getElementById('profileCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
});

// ─── Update status ─────────────────────────────────────────
document.getElementById('updateStatusBtn').addEventListener('click', async function () {
    const patientId    = document.getElementById('selectedPatientId').value;
    const currentStatus = document.getElementById('currentStatus').value;
    const newStatus    = document.getElementById('newStatusSelect').value;

    if (!patientId) { showToast('Please select a patient first.', 'warning'); return; }
    if (newStatus === currentStatus) { showToast('Please choose a different status.', 'warning'); return; }

    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Updating…';

    try {
        const form = new FormData();
        form.append('action', 'update_status');
        form.append('patient_id', patientId);
        form.append('current_status', currentStatus);
        form.append('new_status', newStatus);

        const res  = await fetch('admin-patients.php', { method: 'POST', body: form });
        const data = await res.json();

        if (data.success) {
            // Update hidden field & badge in profile card
            document.getElementById('currentStatus').value = newStatus;
            const badge = document.getElementById('currentStatusBadge');
            badge.textContent = newStatus;
            badge.className   = 'badge bg-' + (statusColors[newStatus] ?? 'secondary');

            // Update matching row in table
            const row = document.querySelector(`#patientsTableBody tr[data-patient-id="${patientId}"]`);
            if (row) {
                row.dataset.status = newStatus;
                const rowBadge = row.querySelector('.status-badge');
                rowBadge.textContent = newStatus;
                rowBadge.className   = 'badge status-badge bg-' + (statusColors[newStatus] ?? 'secondary');
            }

            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'danger');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'danger');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Confirm Update';
    }
});

// ─── Upload intake file ────────────────────────────────────
document.getElementById('uploadIntakeBtn').addEventListener('click', async function () {
    const patientId = document.getElementById('selectedPatientId').value;
    const fileInput = document.getElementById('intakeFile');
    const feedback  = document.getElementById('uploadFeedback');

    if (!patientId) { showToast('Please select a patient first.', 'warning'); return; }
    if (!fileInput.files.length) { showToast('Please choose a PDF file.', 'warning'); return; }

    const file = fileInput.files[0];

    // Client-side type check (real check is server-side)
    if (!file.name.toLowerCase().endsWith('.pdf')) {
        showToast('Only PDF files are allowed.', 'danger');
        return;
    }

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading…';
    feedback.textContent = '';

    try {
        const form = new FormData();
        form.append('action', 'upload_intake');
        form.append('patient_id', patientId);
        form.append('intakeFile', file);

        const res  = await fetch('admin-patients.php', { method: 'POST', body: form });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            fileInput.value = '';
            feedback.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Uploaded successfully.</span>';
        } else {
            showToast(data.message, 'danger');
            feedback.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i>${data.message}</span>`;
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-upload me-2"></i>Upload File';
    }
});
</script>
</body>
</html>