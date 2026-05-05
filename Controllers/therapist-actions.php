<?php
/**
 * Controllers/therapist-actions.php
 * ───────────────────────────────────
 * AJAX-only endpoint — all responses are JSON.
 * Called by the inline <script> in Views/Admin/therapists.php.
 *
 * Supported POST actions:
 *   get_pending       – fetch one pending application for the detail modal
 *   approve           – approve pending → INSERT users + therapists
 *   reject_pending    – mark pending application Rejected
 *   renew             – update license_expiry_date (+ optional file upload)
 *   remove_therapist  – DELETE therapists row, set user Inactive
 */

require_once __DIR__ . '/../Core/Validation.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/Admin.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

// ── Auth guard ────────────────────────────────────────────────────────────────
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// ── POST only ─────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// ── Input ─────────────────────────────────────────────────────────────────────
$action = trim($_POST['action'] ?? '');
$id     = (int)($_POST['id']     ?? 0);

if (!$action || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$admin = new Admin();

// ── Route ─────────────────────────────────────────────────────────────────────
switch ($action) {

    // ── Detail modal data ─────────────────────────────────────────────────────
    case 'get_pending':
        $data = $admin->getPendingTherapistById($id);
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Application not found']);
            break;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // ── Approve ───────────────────────────────────────────────────────────────
    case 'approve':
        $ok = $admin->approveTherapist($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok
                ? 'Therapist approved — account created successfully.'
                : 'Approval failed. Please try again.',
        ]);
        break;

    // ── Reject ────────────────────────────────────────────────────────────────
    case 'reject_pending':
        $ok = $admin->rejectTherapist($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Application rejected.' : 'Rejection failed.',
        ]);
        break;

    // ── Renew license ─────────────────────────────────────────────────────────
    case 'renew':
        $new_expiry = trim($_POST['new_expiry'] ?? '');
        $parsed     = $new_expiry ? strtotime($new_expiry) : false;

        if (!$parsed || $parsed <= time()) {
            echo json_encode(['success' => false, 'message' => 'A valid future expiry date is required.']);
            break;
        }

        // Optional credential file
        $credential_path = null;
        if (!empty($_FILES['credential']['tmp_name'])) {
            $upload_dir  = __DIR__ . '/../uploads/credentials/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
            $ext     = strtolower(pathinfo($_FILES['credential']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed, true)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: PDF, JPG, PNG.']);
                break;
            }

            $filename = 'therapist_' . $id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['credential']['tmp_name'], $upload_dir . $filename)) {
                $credential_path = 'uploads/credentials/' . $filename;
            }
        }

        $ok = $admin->renewTherapistLicense($id, date('Y-m-d', $parsed), $credential_path);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'License renewed successfully.' : 'Renewal failed.',
        ]);
        break;

    // ── Remove therapist ──────────────────────────────────────────────────────
    case 'remove_therapist':
        $ok = $admin->removeTherapist($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Therapist removed from the system.' : 'Removal failed.',
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}