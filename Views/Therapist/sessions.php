<?php
session_start();
require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../../Core/ImmutablePattern.php';
require_once __DIR__ . '/../../Controllers/SessionController.php';
require_once __DIR__ . '/../../Controllers/ClinicalNoteController.php';
require_once __DIR__ . '/../../Models/Repositories/TherapistRepository.php';

// 1. التأمين والتأكد من الهوية (Authentication)
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Therapist') {
    header('Location: ../Auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$therapistRepo = new TherapistRepository();
$sessionCtrl = new SessionController();
$noteCtrl = new ClinicalNoteController();
$userFactory = new ImmutableUserFactory();

// جلب بيانات الثيرابيست للهيدر
$therapistObj = $userFactory->createTherapistFromId($user_id);
if (!$therapistObj) {
    header('Location: ../Auth/logout.php');
    exit();
}

// 2. جلب جدول مواعيد اليوم لتحديد الجلسة النشطة
$schedule = $therapistRepo->getTherapistSchedule($user_id);

// تحديد الـ Session ID (سواء من الرابط أو أول جلسة في الجدول)
$currentSessionId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$currentSession = null;

if (!$currentSessionId && !empty($schedule)) {
    // لو مفيش ID في الرابط، اختار أول جلسة
    $currentSessionId = $schedule[0]['session_id'];
    $currentSession = $schedule[0];
} elseif ($currentSessionId) {
    // لو فيه ID، دور على بيانات الجلسة دي
    foreach ($schedule as $s) {
        if ($s['session_id'] == $currentSessionId) {
            $currentSession = $s;
            break;
        }
    }
}

// 3. معالجة الأكشنز (UC-16: Save Clinical Note)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['noteContent']) && $currentSessionId) {
    $content = $_POST['noteContent'];
    // الـ Controller بيقوم بالتشفير وعمل الـ Versioning أوتوماتيكياً
    $noteCtrl->saveNote($currentSessionId, $user_id, $content);
    // إعادة تحميل الصفحة لمنع تكرار الإرسال
    header("Location: sessions.php?id=$currentSessionId&success=1");
    exit();
}

// 4. جلب تاريخ الملحوظات للعرض
$noteHistory = $currentSessionId ? $noteCtrl->getVersionHistory($currentSessionId) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sessions - Therapist Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .history-item { position: relative; }
        .history-item::before {
            content: ''; position: absolute; left: -19px; top: 5px;
            width: 12px; height: 12px; border-radius: 50%;
            background-color: var(--primary-green); border: 2px solid #fff;
        }
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
                            <i class="bi bi-house-door me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="sessions.php">
                            <i class="bi bi-calendar-event me-2"></i> Manage Sessions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="patients.php">
                            <i class="bi bi-people me-2"></i> Manage Patients
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

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 text-primary-custom fw-bold">Active Session Workspace</h1>
                <div class="d-flex align-items-center">
                    <span class="text-secondary-custom me-3"><i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($therapistObj->getFirstName() . ' ' . $therapistObj->getLastName()); ?></span>
                    <span class="badge bg-success py-2 px-3"><i class="bi bi-shield-check me-2"></i>HIPAA Compliant Session</span>
                </div>
            </div>

            <?php if ($currentSession): ?>
                <div class="row">
                    <div class="col-lg-8">

                        <div class="card card-custom mb-4 border-start border-4 border-info">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="fw-bold mb-1">Session #<?php echo $currentSessionId; ?></h5>
                                    <p class="text-muted small mb-0">Patient: <strong class="text-dark"><?php echo htmlspecialchars($currentSession['first_name'] . ' ' . $currentSession['last_name']); ?></strong></p>
                                    <p class="text-muted small mb-0">Status:
                                        <span class="badge <?php echo $currentSession['session_state'] === 'Scheduled' ? 'bg-secondary' : ($currentSession['session_state'] === 'Live' ? 'bg-danger' : 'bg-success'); ?>" id="sessionStatusBadge">
                                            <?php echo $currentSession['session_state']; ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
                                    <?php if ($currentSession['session_state'] === 'Scheduled'): ?>
                                        <button class="btn btn-success" id="btnCheckIn" onclick="Session.checkIn(<?php echo $currentSessionId; ?>)">
                                            <i class="bi bi-person-check me-2"></i> Check-in Patient
                                        </button>
                                    <?php elseif ($currentSession['session_state'] === 'Live'): ?>
                                        <button class="btn btn-danger" id="btnEndSession" onclick="Session.end(<?php echo $currentSessionId; ?>)">
                                            <i class="bi bi-stop-circle me-2"></i> End Session & Bill
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="sessions.php?id=<?php echo $currentSessionId; ?>">
                            <div class="card card-custom shadow-sm mb-4">
                                <div class="card-header bg-white py-3">
                                    <h6 class="fw-bold mb-0 text-primary-custom">Live Clinical Documentation</h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control mb-3" name="noteContent" id="noteContent" rows="8" placeholder="Start typing clinical observations..." required></textarea>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><i class="bi bi-shield-lock-fill me-1"></i> Data is AES-encrypted & Immutable</small>
                                        <button type="submit" class="btn btn-primary-custom px-4">Save Version & Finalize</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-4">
                        <div class="card card-custom shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0 text-primary-custom">Note Version History</h6>
                            </div>
                            <div class="card-body scrollable-history">
                                <div id="sessionNoteHistory" class="ms-3 mt-2 border-start border-2 border-light">

                                    <?php if (!empty($noteHistory)): ?>
                                        <?php foreach ($noteHistory as $note): ?>
                                            <div class="history-item ps-3 mb-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-bold text-primary">v<?php echo $note['version_no']; ?>.0</span>
                                                    <span class="small text-muted font-monospace"><?php echo date('M d, h:i A', strtotime($note['created_at'])); ?></span>
                                                </div>
                                                <p class="mb-2 text-dark small bg-light p-2 rounded border">
                                                    <?php echo htmlspecialchars(base64_decode($note['encrypted_content'])); ?>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <p class="text-muted small">No previous notes recorded for this session.</p>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5 mt-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-secondary">No Sessions Scheduled</h4>
                    <p class="text-muted">You have no upcoming sessions to manage right now.</p>
                    <a href="dashboard.php" class="btn btn-primary-custom mt-2">Return to Dashboard</a>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/therapist.js"></script>

<?php if(isset($_GET['success'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('Clinical note saved and encrypted successfully.', 'success');
        });
    </script>
<?php endif; ?>
</body>
</html>