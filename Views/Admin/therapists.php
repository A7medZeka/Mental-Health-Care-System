<?php
require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Core/Database.php';
require_once __DIR__ . '/../../Models/Admin.php';

session_start();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (empty($_SESSION['user_id'])) {
    header('Location: ../Auth/login.php');
    exit();
}

checkMethod($method);

if ($_SESSION['role'] !== 'Admin') {
    $map = [
        'Patient'   => '../Patient/dashboard.php',
        'Therapist' => '../Therapist/dashboard.php',
        'Moderator' => '../Moderator/dashboard.php',
    ];
    header('Location: ' . ($map[$_SESSION['role']] ?? '../Auth/login.php'));
    exit();
}

$email    = $_SESSION['email']    ?? '';
$user_id  = $_SESSION['user_id']  ?? '';
$username = $_SESSION['username'] ?? '';
$role     = $_SESSION['role']     ?? '';

$admin = new Admin();

$pendingTherapists = $admin->getPendingTherapistsList();
$activeTherapists  = $admin->getActiveTherapists();

$today     = new DateTime();
$soonLimit = (new DateTime())->modify('+90 days');

$verifiedTherapists = [];
$expiringTherapists = [];
$expiredTherapists  = [];

foreach ($activeTherapists as $t) {
    if (empty($t['license_expiry_date'])) {
        $verifiedTherapists[] = $t;
        continue;
    }
    $expiry = new DateTime($t['license_expiry_date']);
    if ($expiry < $today) {
        $expiredTherapists[] = $t;
    } elseif ($expiry <= $soonLimit) {
        $expiringTherapists[] = $t;
    } else {
        $verifiedTherapists[] = $t;
    }
}

$defaultTab = count($pendingTherapists) ? 'pendingSection' : 'verifiedSection';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapists Verification - Admin MHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .section-tabs .nav-link {
            border-radius: 20px;
            padding: 5px 16px;
            font-size: .83rem;
            font-weight: 500;
            color: #6c757d;
            border: 1px solid #dee2e6;
            margin-right: 6px;
            transition: background .15s, border-color .15s, color .15s;
        }
        .section-tabs .nav-link:hover { border-color: #adb5bd; color: #343a40; }
        .section-tabs .nav-link.active { color: #fff !important; border-color: transparent; }
        .section-tabs .nav-link[data-target="pendingSection"].active  { background: #fd7e14; }
        .section-tabs .nav-link[data-target="verifiedSection"].active { background: #198754; }
        .section-tabs .nav-link[data-target="expiringSection"].active { background: #c79a00; }
        .section-tabs .nav-link[data-target="expiredSection"].active  { background: #dc3545; }

        .count-pill {
            display: inline-flex; align-items: center; justify-content: center;
            width: 19px; height: 19px; border-radius: 50%;
            font-size: .68rem; font-weight: 700;
            margin-left: 4px; vertical-align: middle;
        }
        .action-group { display: flex; gap: .35rem; flex-wrap: wrap; }
        .empty-state  { text-align: center; padding: 3.5rem 1rem; color: #adb5bd; }
        .empty-state i { font-size: 2.4rem; display: block; margin-bottom: .6rem; }
    </style>
</head>
<body>

<div class="container-fluid">
<div class="row">

    <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white shadow-sm">
        <div class="position-sticky pt-4">
            <div class="text-center mb-4">
                <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size: 2rem;"></i>
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
                    <a class="nav-link active" href="therapists.php">
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
                    <a class="nav-link" href="safety-logs.php">
                        <i class="bi bi-journal-medical me-2"></i> Safety Logs
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
            <div>
                <h1 class="h2 text-primary-custom fw-bold mb-0">Therapists Verification</h1>
                <p class="text-muted small mb-0">Review applications, approve licenses, and manage renewals.</p>
            </div>
        </div>

        <ul class="nav section-tabs mb-4" id="sectionTabs">
            <li class="nav-item">
                <a class="nav-link" href="#" data-target="pendingSection">
                    <i class="bi bi-hourglass-split me-1"></i>Pending
                    <?php if ($pendingTherapists): ?>
                        <span class="count-pill bg-warning text-dark"><?= count($pendingTherapists) ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-target="verifiedSection">
                    <i class="bi bi-patch-check me-1"></i>Verified
                    <span class="count-pill bg-success text-white"><?= count($verifiedTherapists) ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-target="expiringSection">
                    <i class="bi bi-clock-history me-1"></i>Expiring Soon
                    <?php if ($expiringTherapists): ?>
                        <span class="count-pill bg-warning text-dark"><?= count($expiringTherapists) ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-target="expiredSection">
                    <i class="bi bi-x-circle me-1"></i>Expired
                    <?php if ($expiredTherapists): ?>
                        <span class="count-pill bg-danger text-white"><?= count($expiredTherapists) ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <div id="pendingSection" class="section-panel d-none">
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-hourglass-split text-warning me-2"></i>Pending Applications
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if ($pendingTherapists): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Specialization</th>
                                    <th class="px-4 py-3">City</th>
                                    <th class="px-4 py-3">Submitted</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($pendingTherapists as $pt): ?>
                                <tr id="pending-row-<?= (int)$pt['id'] ?>">
                                    <td class="px-4 py-3 fw-semibold">
                                        <?= htmlspecialchars($pt['first_name'] . ' ' . $pt['last_name']) ?>
                                    </td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($pt['specialization']) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($pt['city'] ?? '—') ?></td>
                                    <td class="px-4 py-3 text-muted small">
                                        <?= date('M d, Y', strtotime($pt['submitted_at'])) ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-warning text-dark">Pending Review</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="action-group">
                                            <button class="btn btn-sm btn-outline-primary"
                                                    onclick="showPendingData(<?= (int)$pt['id'] ?>)">
                                                <i class="bi bi-eye me-1"></i>Show Data
                                            </button>
                                            <button class="btn btn-sm btn-success"
                                                    onclick="confirmAction('approve', <?= (int)$pt['id'] ?>, 'pending')">
                                                <i class="bi bi-check-circle me-1"></i>Approve
                                            </button>
                                            <button class="btn btn-sm btn-danger"
                                                    onclick="confirmAction('reject_pending', <?= (int)$pt['id'] ?>, 'pending')">
                                                <i class="bi bi-x-circle me-1"></i>Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>No pending applications at this time.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <div id="verifiedSection" class="section-panel d-none">
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-patch-check text-success me-2"></i>Verified Therapists
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if ($verifiedTherapists): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Specialization</th>
                                    <th class="px-4 py-3">License Expiry</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($verifiedTherapists as $t): ?>
                                <tr>
                                    <td class="px-4 py-3 fw-semibold">
                                        <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?>
                                    </td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($t['specialization'] ?? '—') ?></td>
                                    <td class="px-4 py-3">
                                        <?= $t['license_expiry_date']
                                            ? date('M Y', strtotime($t['license_expiry_date']))
                                            : '—' ?>
                                    </td>
                                    <td class="px-4 py-3"><span class="badge bg-success">Verified</span></td>
                                    <td class="px-4 py-3">
                                        <button class="btn btn-sm btn-outline-secondary" disabled>Up to date</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-people"></i>No verified therapists yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <div id="expiringSection" class="section-panel d-none">
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-clock-history text-warning me-2"></i>Expiring Soon
                        <small class="text-muted fw-normal">(within 90 days)</small>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if ($expiringTherapists): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Specialization</th>
                                    <th class="px-4 py-3">License Expiry</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($expiringTherapists as $t): ?>
                                <tr id="therapist-row-<?= (int)$t['therapist_id'] ?>">
                                    <td class="px-4 py-3 fw-semibold">
                                        <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?>
                                    </td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($t['specialization'] ?? '—') ?></td>
                                    <td class="px-4 py-3 text-warning fw-semibold">
                                        <?= date('M d, Y', strtotime($t['license_expiry_date'])) ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-warning text-dark">Expiring Soon</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="action-group">
                                            <button class="btn btn-sm btn-warning"
                                                    onclick="openRenewModal(
                                                        <?= (int)$t['therapist_id'] ?>,
                                                        '<?= htmlspecialchars($t['first_name'].' '.$t['last_name'], ENT_QUOTES) ?>'
                                                    )">
                                                <i class="bi bi-arrow-repeat me-1"></i>Renew
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmAction('remove_therapist', <?= (int)$t['therapist_id'] ?>, 'therapist')">
                                                <i class="bi bi-trash me-1"></i>Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-calendar-check"></i>No licenses expiring in the next 90 days.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <div id="expiredSection" class="section-panel d-none">
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-x-circle text-danger me-2"></i>Expired Licenses
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if ($expiredTherapists): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Specialization</th>
                                    <th class="px-4 py-3">Expired On</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($expiredTherapists as $t): ?>
                                <tr id="therapist-row-<?= (int)$t['therapist_id'] ?>"
                                    class="bg-danger bg-opacity-10">
                                    <td class="px-4 py-3 fw-semibold text-danger">
                                        <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?>
                                    </td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($t['specialization'] ?? '—') ?></td>
                                    <td class="px-4 py-3 text-danger fw-bold">
                                        Expired (<?= date('M Y', strtotime($t['license_expiry_date'])) ?>)
                                    </td>
                                    <td class="px-4 py-3"><span class="badge bg-danger">Expired</span></td>
                                    <td class="px-4 py-3">
                                        <div class="action-group">
                                            <button class="btn btn-sm btn-danger"
                                                    onclick="openRenewModal(
                                                        <?= (int)$t['therapist_id'] ?>,
                                                        '<?= htmlspecialchars($t['first_name'].' '.$t['last_name'], ENT_QUOTES) ?>'
                                                    )">
                                                <i class="bi bi-arrow-repeat me-1"></i>Renew License
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmAction('remove_therapist', <?= (int)$t['therapist_id'] ?>, 'therapist')">
                                                <i class="bi bi-trash me-1"></i>Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-shield-check"></i>No expired licenses — all good!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>
</div>
</div>


<div class="modal fade" id="pendingDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>Therapist Application Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pendingDetailBody">
                <!-- filled by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger"  id="modalRejectBtn">
                    <i class="bi bi-x-circle me-1"></i>Reject
                </button>
                <button type="button" class="btn btn-success" id="modalApproveBtn">
                    <i class="bi bi-check-circle me-1"></i>Approve
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="renewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-arrow-repeat me-2 text-warning"></i>Renew License
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Renewing license for <strong id="renewTherapistName"></strong></p>
                <input type="hidden" id="renewTherapistId">
                <div class="mb-3">
                    <label for="renewExpiryDate" class="form-label fw-semibold">
                        New Expiry Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="renewExpiryDate" required>
                    <div class="invalid-feedback">Please select a valid future date.</div>
                </div>
                <div class="mb-3">
                    <label for="renewCredential" class="form-label fw-semibold">
                        Upload New Credential <span class="text-muted fw-normal">(optional)</span>
                    </label>
                    <input type="file" class="form-control" id="renewCredential" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">PDF or image of the renewed license.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="submitRenew()">
                    <i class="bi bi-arrow-repeat me-1"></i>Renew License
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="confirmModalTitle">Confirm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">Are you sure?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm" id="confirmModalOk">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="../../assets/js/main.js"></script>

<script>
'use strict';

const ACTION_URL  = '../../Controllers/therapist-actions.php';
const DEFAULT_TAB = '<?= $defaultTab ?>';

const pendingDetailModal = new bootstrap.Modal(document.getElementById('pendingDetailModal'));
const renewModal         = new bootstrap.Modal(document.getElementById('renewModal'));
const confirmModal       = new bootstrap.Modal(document.getElementById('confirmModal'));


function activateTab(targetId) {
    document.querySelectorAll('#sectionTabs .nav-link')
            .forEach(l => l.classList.remove('active'));
    document.querySelectorAll('.section-panel')
            .forEach(p => p.classList.add('d-none'));

    const link = document.querySelector(`[data-target="${targetId}"]`);
    if (link)  link.classList.add('active');
    const panel = document.getElementById(targetId);
    if (panel) panel.classList.remove('d-none');
}

document.querySelectorAll('#sectionTabs .nav-link').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        activateTab(link.dataset.target);
    });
});

activateTab(DEFAULT_TAB);


async function showPendingData(id) {
    document.getElementById('pendingDetailBody').innerHTML = spinnerHtml();
    pendingDetailModal.show();

    try {
        const res = await postJSON({ action: 'get_pending', id });

        if (!res.success) {
            document.getElementById('pendingDetailBody').innerHTML = alertHtml('danger', res.message);
            return;
        }

        const d = res.data;

        // Wire footer buttons to this applicant
        wireBtn('modalApproveBtn', () => { pendingDetailModal.hide(); confirmAction('approve', id, 'pending'); });
        wireBtn('modalRejectBtn',  () => { pendingDetailModal.hide(); confirmAction('reject_pending', id, 'pending'); });

        const credHtml = d.credential_file_path
            ? `<a href="/${esc(d.credential_file_path)}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                   <i class="bi bi-file-earmark-pdf me-1"></i>View Credential File
               </a>`
            : '<span class="text-muted small">No file uploaded</span>';

        document.getElementById('pendingDetailBody').innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person me-2"></i>Personal Info</h6>
                            ${detailTable([
                                ['Full Name',     esc(d.first_name) + ' ' + esc(d.last_name)],
                                ['Email',         esc(d.email)],
                                ['Phone',         esc(d.phone_number)],
                                ['Gender',        esc(d.gender)],
                                ['Date of Birth', esc(d.date_of_birth)],
                                ['National ID',   esc(d.national_id)],
                                ['City',          esc(d.city)],
                            ])}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <h6 class="fw-bold text-success mb-3"><i class="bi bi-briefcase me-2"></i>Professional Info</h6>
                            ${detailTable([
                                ['Specialization', esc(d.specialization)],
                                ['License Status', esc(d.license_status)],
                                ['Experience',     esc(d.years_of_experience) + ' years'],
                                ['Availability',   esc(d.availability_schedule)],
                                ['Submitted',      fmtDate(d.submitted_at)],
                            ])}
                            <div class="mt-3">
                                <span class="text-muted small fw-semibold d-block mb-1">Credential File</span>
                                ${credHtml}
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;

    } catch (err) {
        document.getElementById('pendingDetailBody').innerHTML = alertHtml('danger', 'Failed to load: ' + err.message);
    }
}


const CONFIRM_CFG = {
    approve: {
        title:    'Approve Therapist',
        body:     'This will create a user account and therapist profile. Proceed?',
        btnClass: 'btn-success',
        btnText:  'Approve',
    },
    reject_pending: {
        title:    'Reject Application',
        body:     'The application will be marked Rejected. No account will be created.',
        btnClass: 'btn-danger',
        btnText:  'Reject',
    },
    remove_therapist: {
        title:    'Remove Therapist',
        body:     'This will delete the therapist profile and deactivate the account. Cannot be undone.',
        btnClass: 'btn-danger',
        btnText:  'Remove',
    },
};

function confirmAction(action, id, rowType) {
    const cfg = CONFIRM_CFG[action];
    if (!cfg) return;

    document.getElementById('confirmModalTitle').textContent = cfg.title;
    document.getElementById('confirmModalBody').textContent  = cfg.body;

    const okBtn = document.getElementById('confirmModalOk');
    okBtn.className   = `btn btn-sm ${cfg.btnClass}`;
    okBtn.textContent = cfg.btnText;

    wireBtn('confirmModalOk', async () => {
        confirmModal.hide();
        await executeAction(action, id, rowType);
    });

    confirmModal.show();
}


async function executeAction(action, id, rowType) {
    try {
        const res = await postJSON({ action, id });
        showToast(res.success ? 'success' : 'danger', res.message);

        if (res.success) {
            const rowId = rowType === 'pending'
                ? `pending-row-${id}`
                : `therapist-row-${id}`;
            document.getElementById(rowId)?.remove();
        }
    } catch (err) {
        showToast('danger', 'Request failed: ' + err.message);
    }
}


function openRenewModal(therapistId, name) {
    document.getElementById('renewTherapistId').value         = therapistId;
    document.getElementById('renewTherapistName').textContent = name;
    document.getElementById('renewCredential').value          = '';

    const nextYear = new Date();
    nextYear.setFullYear(nextYear.getFullYear() + 1);
    const inp = document.getElementById('renewExpiryDate');
    inp.value = nextYear.toISOString().split('T')[0];
    inp.min   = new Date().toISOString().split('T')[0];
    inp.classList.remove('is-invalid');

    renewModal.show();
}

async function submitRenew() {
    const id     = document.getElementById('renewTherapistId').value;
    const expiry = document.getElementById('renewExpiryDate').value;
    const fileEl = document.getElementById('renewCredential');

    if (!expiry || new Date(expiry) <= new Date()) {
        document.getElementById('renewExpiryDate').classList.add('is-invalid');
        return;
    }
    document.getElementById('renewExpiryDate').classList.remove('is-invalid');

    const body = new FormData();
    body.append('action',     'renew');
    body.append('id',         id);
    body.append('new_expiry', expiry);
    if (fileEl.files[0]) body.append('credential', fileEl.files[0]);

    try {
        const res  = await fetch(ACTION_URL, { method: 'POST', body });
        const data = await res.json();
        renewModal.hide();
        showToast(data.success ? 'success' : 'danger', data.message);
        if (data.success) document.getElementById(`therapist-row-${id}`)?.remove();
    } catch (err) {
        showToast('danger', 'Renewal failed: ' + err.message);
    }
}



async function postJSON(params) {
    const res = await fetch(ACTION_URL, { method: 'POST', body: new URLSearchParams(params) });
    if (!res.ok) throw new Error(`Server error ${res.status}`);
    return res.json();
}

function wireBtn(id, handler) {
    const btn   = document.getElementById(id);
    const clone = btn.cloneNode(true);
    btn.replaceWith(clone);
    clone.addEventListener('click', handler);
}

function detailTable(rows) {
    return `<table class="table table-sm table-borderless mb-0">` +
        rows.map(([label, value]) =>
            `<tr>
                <th class="text-muted fw-normal" style="width:42%;white-space:nowrap">${label}</th>
                <td class="fw-semibold">${value ?? '—'}</td>
            </tr>`
        ).join('') +
    `</table>`;
}

function fmtDate(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function esc(str) {
    if (str == null) return '—';
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function spinnerHtml() {
    return `<div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted small">Loading…</p>
            </div>`;
}

function alertHtml(type, msg) {
    return `<div class="alert alert-${type} mb-0">${esc(msg)}</div>`;
}

function showToast(type, message) {
    const container = document.getElementById('toastContainer');
    const id = 'toast-' + Date.now();
    const icons = { success: 'bi-check-circle-fill', danger: 'bi-exclamation-circle-fill', warning: 'bi-exclamation-triangle-fill' };
    container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${icons[type] ?? 'bi-info-circle-fill'} me-2"></i>${esc(message)}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`);
    const el = document.getElementById(id);
    new bootstrap.Toast(el, { delay: 4500 }).show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
}
</script>
</body>
</html>