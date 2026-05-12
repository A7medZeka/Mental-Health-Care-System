<?php
session_start();

// توليد توكن الحماية إذا لم يكن موجوداً
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../../Core/ImmutablePattern.php';
require_once __DIR__ . '/../../Controllers/SessionController.php';
require_once __DIR__ . '/../../Controllers/ClinicalNoteController.php';
require_once __DIR__ . '/../../Models/Repositories/TherapistRepository.php';

// 1. التأمين والتحقق من الهوية (Authentication)
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Therapist') {
    header('Location: ../Auth/login.php');
    exit();
}

// 2. تعريف المتغيرات الأساسية (الترتيب الصحيح لمنع أخطاء Undefined)
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

// جلب جدول المواعيد القادمة
$schedule = $therapistRepo->getTherapistSchedule($user_id, 'upcoming');

// تحديد الجلسة الحالية
$currentSessionId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$currentSession = null;

if (!$currentSessionId && !empty($schedule)) {
    $currentSessionId = $schedule[0]['session_id'];
    $currentSession = $schedule[0];
} elseif ($currentSessionId) {
    foreach ($schedule as $s) {
        if ($s['session_id'] == $currentSessionId) {
            $currentSession = $s;
            break;
        }
    }
}

// حماية IDOR: التأكد أن الجلسة تخص هذا الطبيب
if ($currentSessionId && !$currentSession) {
    die("Unauthorized Access: You do not have permission to view this session.");
}

// 3. معالجة الأكشنز (UC-16: Save Clinical Note) - بعد تعريف كل المتغيرات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['noteContent']) && $currentSessionId) {

    // التحقق من توكن الحماية CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF Token Validation Failed. Request denied.");
    }

    $content = $_POST['noteContent'];
    $noteCtrl->saveNote($currentSessionId, $user_id, $content);

    header("Location: sessions.php?id=$currentSessionId&success=1");
    exit();
}

if (isset($_GET['action']) && $currentSessionId) {
    if ($_GET['action'] === 'checkin') {
        $sessionCtrl->checkInSession($currentSessionId);
        header("Location: sessions.php?id=$currentSessionId");
        exit();
    } elseif ($_GET['action'] === 'admit') {
        $sessionCtrl->admitPatient($currentSessionId, $currentSession['patient_id']);
        header("Location: sessions.php?id=$currentSessionId");
        exit();
    } elseif ($_GET['action'] === 'end') {
        $sessionCtrl->endSession($currentSessionId);
        header("Location: sessions.php?id=$currentSessionId");
        exit();
    }
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house-door me-2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="sessions.php"><i class="bi bi-calendar-event me-2"></i> Manage Sessions</a></li>
                    <li class="nav-item"><a class="nav-link" href="patients.php"><i class="bi bi-people me-2"></i> Manage Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="reviews.php"><i class="bi bi-star me-2"></i> Reviews & Ratings</a></li>
                    <li class="nav-item"><a class="nav-link" href="insights.php"><i class="bi bi-graph-up me-2"></i> Clinical Insights</a></li>
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
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 text-primary-custom fw-bold">Active Session Workspace</h1>
                <div class="d-flex align-items-center">
                    <span class="text-secondary-custom me-3"><i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($therapistObj->getFirstName() . ' ' . $therapistObj->getLastName()); ?></span>
                    <span class="badge bg-success py-2 px-3"><i class="bi bi-shield-check me-2"></i>HIPAA Compliant Session</span>
                </div>
            </div>

            <?php if ($currentSession): ?>
                <div class="row">
                    <!-- Session List Sidebar -->
                    <div class="col-lg-3">
                        <div class="card card-custom shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0 text-primary-custom">All Sessions</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php if (!empty($schedule)): ?>
                                        <?php foreach ($schedule as $session): ?>
                                            <a href="sessions.php?id=<?php echo $session['session_id']; ?>" 
                                               class="list-group-item list-group-item-action <?php echo ($session['session_id'] == $currentSessionId) ? 'active' : ''; ?>">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">
                                                            <?php echo htmlspecialchars($session['first_name'] . ' ' . $session['last_name']); ?>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <?php echo date('M d, h:i A', strtotime($session['appointment_date'])); ?>
                                                        </small>
                                                    </div>
                                                    <span class="badge <?php echo $session['session_state'] === 'Scheduled' ? 'bg-secondary' : ($session['session_state'] === 'CheckedIn' ? 'bg-warning' : ($session['session_state'] === 'Live' ? 'bg-danger' : 'bg-success')); ?>">
                                                        <?php echo $session['session_state']; ?>
                                                    </span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="list-group-item text-center text-muted py-4">
                                            No upcoming sessions
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-9">
                        <div class="card card-custom mb-4 border-start border-4 border-info">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="fw-bold mb-1">Session #<?php echo $currentSessionId; ?></h5>
                                    <p class="text-muted small mb-0">Patient: <strong class="text-dark"><?php echo htmlspecialchars($currentSession['first_name'] . ' ' . $currentSession['last_name']); ?></strong></p>
                                    <p class="text-muted small mb-0">Status:
                                        <span class="badge <?php echo $currentSession['session_state'] === 'Scheduled' ? 'bg-secondary' : ($currentSession['session_state'] === 'CheckedIn' ? 'bg-warning' : ($currentSession['session_state'] === 'Live' ? 'bg-danger' : 'bg-success')); ?>">
                                                <?php echo $currentSession['session_state']; ?>
                                            </span>
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
                                    <?php if ($currentSession['session_state'] === 'Scheduled'): ?>
                                        <button class="btn btn-success" onclick="Session.checkIn(<?php echo $currentSessionId; ?>)"><i class="bi bi-person-check me-2"></i> Check-in</button>
                                    <?php elseif ($currentSession['session_state'] === 'CheckedIn'): ?>
                                        <button class="btn btn-primary" onclick="Session.admit(<?php echo $currentSessionId; ?>)"><i class="bi bi-door-open me-2"></i> Admit Patient</button>
                                    <?php elseif ($currentSession['session_state'] === 'Live'): ?>
                                        <button class="btn btn-danger" onclick="Session.end(<?php echo $currentSessionId; ?>)"><i class="bi bi-stop-circle me-2"></i> End Session</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="sessions.php?id=<?php echo $currentSessionId; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="card card-custom shadow-sm mb-4">
                                <div class="card-header bg-white py-3">
                                    <h6 class="fw-bold mb-0 text-primary-custom">Live Clinical Documentation</h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control mb-3" name="noteContent" rows="10" placeholder="Start typing clinical observations..." required></textarea>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><i class="bi bi-shield-lock-fill me-1"></i> Data is AES-encrypted & Immutable</small>
                                        <button type="submit" class="btn btn-primary-custom px-4">Save Version & Finalize</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-3">
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
                                                    <span class="fw-bold text-primary">v<?php echo $note->getVersionNo(); ?>.0</span>
                                                    <span class="small text-muted font-monospace"><?php echo date('M d, h:i A', strtotime($note->getCreatedAt())); ?></span>
                                                </div>
                                                <p class="mb-2 text-dark small bg-light p-2 rounded border">
                                                    <?php echo htmlspecialchars($noteCtrl->decryptNote($note->getEncryptedContent())); ?>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-center text-muted py-4">No previous versions.</p>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/therapist.js"></script>
<script>
    const Session = {
        checkIn: function(sessionId) {
            window.location.href = `sessions.php?id=${sessionId}&action=checkin`;
        },
        admit: function(sessionId) {
            if(confirm('Admit patient to start live session?')) {
                window.location.href = `sessions.php?id=${sessionId}&action=admit`;
            }
        },
        end: function(sessionId) {
            if(confirm('Are you sure you want to end this session?')) {
                window.location.href = `sessions.php?id=${sessionId}&action=end`;
            }
        }
    };
</script>
</body>
</html>