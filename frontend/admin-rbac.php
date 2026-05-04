<?php
require_once 'Validation.php';
require_once 'connection.php';
session_start();

$method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$user_id = $_SESSION['user_id'] ?? null;

if (empty($user_id)) {
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

$allowedTransitions = [
    'Therapist' => 'Moderator',
    'Moderator' => 'Admin',
    'Patient'   => null,
    'Admin'     => null,
];

if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    header('Content-Type: application/json');

    $targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $newRole  = trim($_POST['new_role'] ?? '');

    if (!$targetId) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit();
    }

    if ($targetId === (int)$user_id) {
        echo json_encode(['success' => false, 'message' => 'You cannot change your own role.']);
        exit();
    }

    $stmt = $conn->prepare('SELECT `role`, username FROM users WHERE user_id = ?');
    $stmt->execute([$targetId]);
    $target = $stmt->fetch();

    if (!$target) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    $currentRole  = $target['role'];
    $allowedNext  = $allowedTransitions[$currentRole] ?? null;

    if ($allowedNext === null) {
        echo json_encode(['success' => false, 'message' => "\"{$currentRole}\" role cannot be changed."]);
        exit();
    }

    if ($newRole !== $allowedNext) {
        echo json_encode(['success' => false, 'message' => "A {$currentRole} can only be promoted to {$allowedNext}."]);
        exit();
    }

    $update = $conn->prepare("UPDATE users SET `role` = ? WHERE user_id = ?");
    $update->execute([$newRole, $targetId]);

    echo json_encode([
        'success'  => true,
        'message'  => "\"" . $target['username'] . "\" promoted from {$currentRole} to {$newRole}.",
        'new_role' => $newRole
    ]);
    exit();
}

if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    header('Content-Type: application/json');

    $targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if (!$targetId) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit();
    }

    if ($targetId === (int)$user_id) {
        echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
        exit();
    }

    $stmt = $conn->prepare('SELECT `role`, username FROM users WHERE user_id = ?');
    $stmt->execute([$targetId]);
    $target = $stmt->fetch();

    if (!$target) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    if ($target['role'] === 'Admin') {
        echo json_encode(['success' => false, 'message' => 'Admin accounts cannot be deleted.']);
        exit();
    }

    $delete = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $delete->execute([$targetId]);

    echo json_encode([
        'success' => true,
        'message' => "User \"" . $target['username'] . "\" has been deleted.",
        'user_id' => $targetId
    ]);
    exit();
}

$stmt  = $conn->query('SELECT user_id, username, email, `role` FROM users ORDER BY role, username');
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RBAC Settings - Admin MHC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <li class="nav-item"><a class="nav-link" href="admin-dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-patients.php"><i class="bi bi-people me-2"></i> Manage Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-therapists.php"><i class="bi bi-person-badge me-2"></i> Therapists Verification</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin-rbac.php"><i class="bi bi-shield-lock me-2"></i> RBAC Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-performance.php"><i class="bi bi-bar-chart-line me-2"></i> Therapist Performance</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-safety-logs.php"><i class="bi bi-journal-medical me-2"></i> Safety Logs</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 text-primary-custom fw-bold">Role-Based Access Control</h1>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card card-custom mb-4">
                        <div class="card-header bg-white border-0 pt-4 pb-2">
                            <h5 class="fw-bold text-primary-custom">Current Role Assignments</h5>
                            <p class="small text-muted mb-0 mt-1">
                                <i class="bi bi-arrow-right-circle me-1"></i>
                                Therapist → Moderator &nbsp;|&nbsp; Moderator → Admin &nbsp;|&nbsp; Patient &amp; Admin are locked
                            </p>
                        </div>
                        <div class="card-body p-0 mt-1">
                            <div class="table-responsive">
                                <table class="table table-hover table-custom mb-0">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-3">Username</th>
                                            <th class="px-4 py-3">Email</th>
                                            <th class="px-4 py-3">Current Role</th>
                                            <th class="px-4 py-3">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usersTableBody">
                                        <?php
                                        $badgeMap = [
                                            'Admin'     => 'bg-danger',
                                            'Moderator' => 'bg-info text-dark',
                                            'Therapist' => 'bg-primary',
                                            'Patient'   => 'bg-secondary',
                                        ];
                                        $promoteLabel = [
                                            'Therapist' => 'Promote to Moderator',
                                            'Moderator' => 'Promote to Admin',
                                        ];
                                        foreach ($users as $row):
                                            $badge     = $badgeMap[$row['role']] ?? 'bg-light text-dark';
                                            $isSelf    = ($row['user_id'] == $user_id);
                                            $isLocked  = ($row['role'] === 'Admin' || $row['role'] === 'Patient');
                                            $canEdit   = !$isSelf && !$isLocked && isset($allowedTransitions[$row['role']]) && $allowedTransitions[$row['role']] !== null;
                                            $canDelete = !$isSelf && $row['role'] !== 'Admin';
                                            $nextRole  = $allowedTransitions[$row['role']] ?? null;
                                        ?>
                                        <tr id="user-row-<?= $row['user_id']; ?>">
                                            <td class="px-4 py-3 fw-semibold">
                                                <?= htmlspecialchars($row['username']); ?>
                                                <?php if ($isSelf): ?><span class="badge bg-warning text-dark ms-1">You</span><?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3"><?= htmlspecialchars($row['email']); ?></td>
                                            <td class="px-4 py-3">
                                                <span class="badge <?= $badge; ?> role-badge" id="role-badge-<?= $row['user_id']; ?>">
                                                    <?= htmlspecialchars($row['role']); ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="d-flex gap-2">
                                                    <?php if ($canEdit): ?>
                                                        <button class="btn btn-sm btn-outline-primary editRoleBtn"
                                                                data-user-id="<?= $row['user_id']; ?>"
                                                                data-username="<?= htmlspecialchars($row['username']); ?>"
                                                                data-current-role="<?= htmlspecialchars($row['role']); ?>"
                                                                data-new-role="<?= htmlspecialchars($nextRole); ?>">
                                                            <i class="bi bi-arrow-up-circle me-1"></i><?= $promoteLabel[$row['role']] ?? 'Promote'; ?>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                                            <i class="bi bi-lock me-1"></i><?= $isSelf ? 'Your Role' : 'Locked'; ?>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($canDelete): ?>
                                                        <button class="btn btn-sm btn-outline-danger deleteUserBtn"
                                                                data-user-id="<?= $row['user_id']; ?>"
                                                                data-username="<?= htmlspecialchars($row['username']); ?>">
                                                            <i class="bi bi-trash me-1"></i>Delete
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                                            <i class="bi bi-shield-fill me-1"></i>Protected
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<div class="modal fade" id="confirmRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content card-custom">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary-custom">Confirm Promotion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary-custom mb-2">You are about to promote: <strong id="roleModalUsername">—</strong></p>
                <p class="mb-0">
                    <span class="badge bg-secondary fs-6" id="roleModalFrom">—</span>
                    <i class="bi bi-arrow-right mx-2"></i>
                    <span class="badge bg-danger fs-6" id="roleModalTo">—</span>
                </p>
                <p class="small text-muted mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>This action cannot be undone from this panel.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="confirmRoleBtn">
                    <i class="bi bi-check-lg me-1"></i>Confirm Promotion
                </button>
            </div>
            <input type="hidden" id="roleModalUserId">
            <input type="hidden" id="roleModalNewRole">
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content card-custom">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary-custom mb-2">You are about to permanently delete: <strong id="deleteModalUsername">—</strong></p>
                <p class="small text-muted mb-0">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>This is irreversible. All data for this user will be removed.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>Delete User
                </button>
            </div>
            <input type="hidden" id="deleteModalUserId">
        </div>
    </div>
</div>

<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
const badgeMap = {
    Admin:     'bg-danger',
    Moderator: 'bg-info text-dark',
    Therapist: 'bg-primary',
    Patient:   'bg-secondary'
};

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
    new bootstrap.Toast(document.getElementById(id), { delay: 4000 }).show();
}

document.getElementById('usersTableBody').addEventListener('click', function (e) {
    const editBtn = e.target.closest('.editRoleBtn');
    if (editBtn) {
        document.getElementById('roleModalUserId').value         = editBtn.dataset.userId;
        document.getElementById('roleModalNewRole').value        = editBtn.dataset.newRole;
        document.getElementById('roleModalUsername').textContent = editBtn.dataset.username;

        const fromBadge = document.getElementById('roleModalFrom');
        fromBadge.textContent = editBtn.dataset.currentRole;
        fromBadge.className   = 'badge fs-6 ' + (badgeMap[editBtn.dataset.currentRole] ?? 'bg-secondary');

        const toBadge = document.getElementById('roleModalTo');
        toBadge.textContent = editBtn.dataset.newRole;
        toBadge.className   = 'badge fs-6 ' + (badgeMap[editBtn.dataset.newRole] ?? 'bg-secondary');

        new bootstrap.Modal(document.getElementById('confirmRoleModal')).show();
        return;
    }

    const delBtn = e.target.closest('.deleteUserBtn');
    if (delBtn) {
        document.getElementById('deleteModalUserId').value          = delBtn.dataset.userId;
        document.getElementById('deleteModalUsername').textContent  = delBtn.dataset.username;
        new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
    }
});

document.getElementById('confirmRoleBtn').addEventListener('click', async function () {
    const userId  = document.getElementById('roleModalUserId').value;
    const newRole = document.getElementById('roleModalNewRole').value;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';

    try {
        const form = new FormData();
        form.append('action',   'update_role');
        form.append('user_id',  userId);
        form.append('new_role', newRole);

        const res  = await fetch('admin-rbac.php', { method: 'POST', body: form });
        const data = await res.json();

        if (data.success) {
            const badge = document.getElementById('role-badge-' + userId);
            if (badge) {
                badge.textContent = data.new_role;
                badge.className   = 'badge role-badge ' + (badgeMap[data.new_role] ?? 'bg-secondary');
            }

            const actionCell = document.querySelector(`#user-row-${userId} td:last-child .d-flex`);
            if (actionCell && data.new_role === 'Admin') {
                actionCell.innerHTML = `
                    <button class="btn btn-sm btn-outline-secondary" disabled><i class="bi bi-lock me-1"></i>Locked</button>
                    <button class="btn btn-sm btn-outline-secondary" disabled><i class="bi bi-shield-fill me-1"></i>Protected</button>`;
            } else if (actionCell && data.new_role === 'Moderator') {
                const editBtn = actionCell.querySelector('.editRoleBtn');
                if (editBtn) {
                    editBtn.dataset.currentRole = 'Moderator';
                    editBtn.dataset.newRole     = 'Admin';
                    editBtn.innerHTML           = '<i class="bi bi-arrow-up-circle me-1"></i>Promote to Admin';
                }
            }

            bootstrap.Modal.getInstance(document.getElementById('confirmRoleModal')).hide();
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'danger');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Confirm Promotion';
    }
});

document.getElementById('confirmDeleteBtn').addEventListener('click', async function () {
    const userId = document.getElementById('deleteModalUserId').value;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting…';

    try {
        const form = new FormData();
        form.append('action',  'delete_user');
        form.append('user_id', userId);

        const res  = await fetch('admin-rbac.php', { method: 'POST', body: form });
        const data = await res.json();

        if (data.success) {
            document.getElementById('user-row-' + userId)?.remove();
            bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'danger');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete User';
    }
});
</script>
</body>
</html>