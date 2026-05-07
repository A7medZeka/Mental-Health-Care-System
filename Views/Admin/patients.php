<?php
// ── Bootstrap MVC ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/../../Controllers/AdminDashboardController.php';

$controller = new AdminDashboardController();
$controller->handleRequest();               // auth + POST dispatch (exits on POST)

// Data for the view
$viewData      = $controller->getPatientsViewData();
$patients      = $viewData['patients'];
$totalPatients = $viewData['totalPatients'];
$featured      = $viewData['featured'];

// ── Helpers (view-only) ───────────────────────────────────────────────────────
$statusBadgeColor = static function (string $status): string {
    return match ($status) {
        'Registered' => 'secondary',
        'Screened'   => 'info',
        'Matched'    => 'warning',
        'Active'     => 'success',
        default      => 'secondary',
    };
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients – Admin MHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* Highlight the "Add Therapist" sidebar link */
        .nav-link[href*="therapist-register"] {
            color: #198754 !important;
            font-weight: 600 !important;
            background: rgba(25, 135, 84, .1) !important;
            border: 1px solid #198754 !important;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white shadow-sm">
            <div class="position-sticky pt-4">
                <div class="text-center mb-4">
                    <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size:2rem;"></i>
                    <h5 class="fw-bold text-primary-custom mt-2">MentalCare System</h5>
                </div>
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item"><a class="nav-link"        href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="patients.php"><i class="bi bi-people me-2"></i> Manage Patients</a></li>
                    <li class="nav-item"><a class="nav-link"        href="../Auth/therapist-register.php"><i class="bi bi-plus-circle me-2"></i> Add Therapist</a></li>
                    <li class="nav-item"><a class="nav-link"        href="therapists.php"><i class="bi bi-person-badge me-2"></i> Therapists Verification</a></li>
                    <li class="nav-item"><a class="nav-link"        href="rbac.php"><i class="bi bi-shield-lock me-2"></i> RBAC Settings</a></li>
                    <li class="nav-item"><a class="nav-link"        href="performance.php"><i class="bi bi-bar-chart-line me-2"></i> Therapist Performance</a></li>
                    <li class="nav-item"><a class="nav-link"        href="safety-logs.php"><i class="bi bi-journal-medical me-2"></i> Safety Logs</a></li>
                </ul>
            </div>
        </nav>

        <!-- ── Main content ─────────────────────────────────────────────────── -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 text-primary-custom fw-bold">
                    Manage Patients
                    <span class="badge bg-secondary fs-6 ms-2"><?= $totalPatients ?> total</span>
                </h1>
            </div>

            <?php if ($featured): ?>
            <div class="row">

                <!-- ── Profile card ───────────────────────────────────────────── -->
                <div class="col-lg-6 mb-4">
                    <div class="card card-custom h-100" id="profileCard">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h5 class="fw-bold text-primary-custom">
                                Patient Profile:
                                <span id="profileName">
                                    <?= htmlspecialchars($featured['first_name'] . ' ' . $featured['last_name']) ?>
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">

                            <!-- Patient details -->
                            <div class="mb-3">
                                <strong>ID:</strong> PT-<span id="profileId"><?= $featured['user_id'] ?></span><br>
                                <strong>Username:</strong> <span id="profileUsername"><?= htmlspecialchars($featured['username']) ?></span><br>
                                <strong>Email:</strong>    <span id="profileEmail"><?= htmlspecialchars($featured['email']) ?></span><br>
                                <strong>Current Status:</strong>
                                <span class="badge bg-<?= $statusBadgeColor($featured['status']) ?>" id="currentStatusBadge">
                                    <?= htmlspecialchars($featured['status']) ?>
                                </span>
                                <input type="hidden" id="currentStatus"     value="<?= htmlspecialchars($featured['status']) ?>">
                                <input type="hidden" id="selectedPatientId" value="<?= $featured['user_id'] ?>">
                            </div>

                            <hr>

                            <!-- Status transition -->
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

                            <hr>

                            <!-- Intake-form upload -->
                            <h6 class="text-secondary-custom mb-3">
                                2. Upload Intake Form for:
                                <span id="intakePatientName">
                                    <?= htmlspecialchars($featured['first_name'] . ' ' . $featured['last_name']) ?>
                                </span>
                            </h6>
                            <div class="mb-2">
                                <input type="file" class="form-control" id="intakeFile" accept=".pdf">
                                <div class="form-text">PDF only · max 5 MB</div>
                            </div>
                            <button class="btn btn-outline-secondary" id="uploadIntakeBtn">
                                <i class="bi bi-upload me-2"></i>Upload File
                            </button>
                            <div id="uploadFeedback" class="mt-2 small"></div>

                        </div>
                    </div>
                </div>
            </div><!-- /.row -->

            <?php else: ?>
                <div class="alert alert-info">No patients found in the system.</div>
            <?php endif; ?>

            <!-- ── Patients table ─────────────────────────────────────────────── -->
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
                                    $fullName = htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']);
                                    $regDate  = date('M j, Y', strtotime($patient['created_at']));
                                    $badge    = $statusBadgeColor($patient['status']);
                                ?>
                                <tr data-patient-id="<?= $patient['user_id'] ?>"
                                    data-status="<?=   htmlspecialchars($patient['status'])   ?>"
                                    data-name="<?=     $fullName ?>"
                                    data-username="<?= htmlspecialchars($patient['username']) ?>"
                                    data-email="<?=    htmlspecialchars($patient['email'])    ?>">

                                    <td class="px-4 py-3 fw-semibold">PT-<?= $patient['user_id'] ?></td>
                                    <td class="px-4 py-3"><?= $fullName ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($patient['username']) ?></td>
                                    <td class="px-4 py-3"><?= $regDate ?></td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-<?= $badge ?> status-badge">
                                            <?= htmlspecialchars($patient['status']) ?>
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

<!-- ── Toast container ──────────────────────────────────────────────────────── -->
<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/main.js"></script>
<script>
// ── Status → Bootstrap colour map ─────────────────────────────────────────────
const STATUS_COLORS = {
    Registered : 'secondary',
    Screened   : 'info',
    Matched    : 'warning',
    Active     : 'success',
};

// ── Toast helper ──────────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    const id = 'toast-' + Date.now();
    document.getElementById('toastContainer').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>`);
    new bootstrap.Toast(document.getElementById(id), { delay: 4000 }).show();
}

// ── Select a patient row → populate profile card ──────────────────────────────
document.getElementById('patientsTableBody').addEventListener('click', e => {
    const btn = e.target.closest('.select-patient-btn');
    if (!btn) return;

    const row = btn.closest('tr');
    const { patientId, status, name, username, email } = row.dataset;

    document.getElementById('profileId').textContent           = patientId;
    document.getElementById('profileName').textContent         = name;
    document.getElementById('profileUsername').textContent     = username;
    document.getElementById('profileEmail').textContent        = email;
    document.getElementById('selectedPatientId').value         = patientId;
    document.getElementById('currentStatus').value             = status;
    document.getElementById('intakePatientName').textContent   = name;

    const badge = document.getElementById('currentStatusBadge');
    badge.textContent = status;
    badge.className   = 'badge bg-' + (STATUS_COLORS[status] ?? 'secondary');

    document.querySelectorAll('#patientsTableBody tr').forEach(r => r.classList.remove('table-active'));
    row.classList.add('table-active');

    document.getElementById('profileCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
});

// ── Update patient status ─────────────────────────────────────────────────────
document.getElementById('updateStatusBtn').addEventListener('click', async function () {
    const patientId     = document.getElementById('selectedPatientId').value;
    const currentStatus = document.getElementById('currentStatus').value;
    const newStatus     = document.getElementById('newStatusSelect').value;

    if (!patientId) { showToast('Please select a patient first.', 'warning'); return; }
    if (newStatus === currentStatus) { showToast('Please choose a different status.', 'warning'); return; }

    this.disabled    = true;
    this.textContent = 'Updating…';

    try {
        const form = new FormData();
        form.append('action',         'update_status');
        form.append('patient_id',     patientId);
        form.append('current_status', currentStatus);
        form.append('new_status',     newStatus);

        const data = await fetch('patients.php', { method: 'POST', body: form }).then(r => r.json());

        if (data.success) {
            document.getElementById('currentStatus').value = newStatus;

            const badge     = document.getElementById('currentStatusBadge');
            badge.textContent = newStatus;
            badge.className   = 'badge bg-' + (STATUS_COLORS[newStatus] ?? 'secondary');

            const row = document.querySelector(`#patientsTableBody tr[data-patient-id="${patientId}"]`);
            if (row) {
                row.dataset.status = newStatus;
                const rb = row.querySelector('.status-badge');
                rb.textContent = newStatus;
                rb.className   = 'badge status-badge bg-' + (STATUS_COLORS[newStatus] ?? 'secondary');
            }
        }

        showToast(data.message, data.success ? 'success' : 'danger');

    } catch {
        showToast('Network error. Please try again.', 'danger');
    } finally {
        this.disabled    = false;
        this.textContent = 'Confirm Update';
    }
});

// ── Upload intake PDF ─────────────────────────────────────────────────────────
document.getElementById('uploadIntakeBtn').addEventListener('click', async function () {
    const patientId = document.getElementById('selectedPatientId').value;
    const fileInput = document.getElementById('intakeFile');
    const feedback  = document.getElementById('uploadFeedback');

    if (!patientId)           { showToast('Please select a patient first.', 'warning'); return; }
    if (!fileInput.files.length) { showToast('Please choose a PDF file.', 'warning'); return; }

    if (!fileInput.files[0].name.toLowerCase().endsWith('.pdf')) {
        showToast('Only PDF files are allowed.', 'danger');
        return;
    }

    this.disabled   = true;
    this.innerHTML  = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading…';
    feedback.textContent = '';

    try {
        const form = new FormData();
        form.append('action',     'upload_intake');
        form.append('patient_id', patientId);
        form.append('intakeFile', fileInput.files[0]);

        const data = await fetch('patients.php', { method: 'POST', body: form }).then(r => r.json());

        if (data.success) {
            fileInput.value     = '';
            feedback.innerHTML  = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Uploaded successfully.</span>';
        } else {
            feedback.innerHTML  = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i>${data.message}</span>`;
        }

        showToast(data.message, data.success ? 'success' : 'danger');

    } catch {
        showToast('Network error. Please try again.', 'danger');
    } finally {
        this.disabled  = false;
        this.innerHTML = '<i class="bi bi-upload me-2"></i>Upload File';
    }
});
</script>
</body>
</html>