<?php
session_start();
require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../../Core/ImmutablePattern.php';
require_once __DIR__ . '/../../Models/Repositories/TherapistRepository.php';
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Therapist') {
    header('Location: ../Auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$therapistRepo = new TherapistRepository();
$userFactory = new ImmutableUserFactory();

// 1. جلب بيانات الثيرابيست (Immutable Object)
$therapistObj = $userFactory->createTherapistFromId($user_id);

if (!$therapistObj) {
    header('Location: ../Auth/logout.php');
    exit();
}

// 2. تجهيز المتغيرات للـ View (نفس الأسماء اللي الفرونت إند مستنيها)
$first_name = $therapistObj->getFirstName();
$last_name  = $therapistObj->getLastName();
$role       = $therapistObj->getRole();
$email      = $therapistObj->getEmail();

// 3. جلب الإحصائيات (بعد ما صلحنا الـ JOIN والـ session_state)
// --- بداية حل مشكلة العمر (Age) والنوع (Gender) ---
$db = SingletonDatabase::getInstance()->getConnection();
$stmt = $db->prepare("SELECT date_of_birth, gender FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch();

$age = 'N/A';
$gender = 'N/A'; // القيمة الافتراضية
if ($userData) {
    if (!empty($userData['date_of_birth'])) {
        $dob = new DateTime($userData['date_of_birth']);
        $now = new DateTime();
        $age = $now->diff($dob)->y;
    }
    if (!empty($userData['gender'])) {
        $gender = $userData['gender'];
    }
}
// --- نهاية حل مشكلة العمر والنوع ---
// --- بداية حل مشكلة العمر (Age) المحدث ---
$db = SingletonDatabase::getInstance()->getConnection();
$stmt = $db->prepare("SELECT date_of_birth FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch();

$age = 'N/A';
if ($userData && !empty($userData['date_of_birth'])) {
    $dob = new DateTime($userData['date_of_birth']);
    $now = new DateTime();
    $age = $now->diff($dob)->y; // حساب السن بناءً على تاريخ الميلاد والوقت الحالي
}
// --- نهاية حل مشكلة العمر ---

// 4. UC-14: جلب جدول المواعيد الحقيقي
$todaySchedule = $therapistRepo->getTherapistSchedule($user_id);

// 5. UC-17: معالجة بلاغات الحالات الحرجة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['welfareActionType'])) {
    $action = $_POST['welfareActionType'];
    $notes  = $_POST['welfareNotes'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapist Dashboard - Mental Health Care</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
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
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-house-door me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="sessions.php">
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

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2 text-primary-custom fw-bold">Therapist Dashboard</h1>
                    <div class="d-flex align-items-center">
                        <span class="text-secondary-custom me-3"><i class="bi bi-person-circle me-1"></i> <?php echo 'Age: ' . ($age ?: 'N/A') . ' | ' . $role . ' | ' . htmlspecialchars($first_name . ' ' . $last_name).' | '. $gender; ?></span>
                        <span class="badge bg-success py-2 px-3"><i class="bi bi-person-check-fill me-2"></i>Verified</span>
                    </div>
                </div>

                <!-- Notifications & Reminders (UC-14) -->
                <?php
                // --- بداية حل مشكلة التنبيه الديناميكي ---
                $nextSession = null;
                $timeDiff = 0;

                // البحث عن أقرب جلسة قادمة في جدول اليوم
                foreach ($todaySchedule as $session) {
                    if ($session['session_state'] === 'Scheduled') {
                        $sessionTime = strtotime($session['appointment_date']);
                        $nowTime = time();

                        // إذا كانت الجلسة لم تبدأ بعد
                        if ($sessionTime > $nowTime) {
                            $nextSession = $session;
                            $timeDiff = round(($sessionTime - $nowTime) / 60); // حساب الفرق بالدقائق
                            break; // نكتفي بأول جلسة قادمة
                        }
                    }
                }

                // إظهار التنبيه فقط لو الجلسة خلال 60 دقيقة أو أقل
                if ($nextSession && $timeDiff <= 60):
                    $patientName = htmlspecialchars($nextSession['first_name'] . ' ' . $nextSession['last_name']);
                    ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-custom border-warning border-start border-4">
                                <div class="card-body py-3 d-flex align-items-center" id="reminderNotification">
                                    <i class="bi bi-bell-fill text-warning fs-3 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">System Reminder: Upcoming Session</h6>
                                        <p class="mb-0 text-secondary-custom small">You have a scheduled session with <strong><?php echo $patientName; ?></strong> in <?php echo $timeDiff; ?> minutes.</p>
                                    </div>
                                    <button class="btn btn-sm btn-outline-secondary ms-auto" onclick="this.closest('.card').remove();">Dismiss</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- Action Required: High-Risk No-Show (UC-17) -->
                <?php
                $hasNoShowIncident = $therapistRepo->checkNoShow($user_id);
                if ($hasNoShowIncident):
                    ?>
                    <div class="row mb-4" id="incidentDashboardUI">
                        <div class="col-12">
                            <form method="POST" action="dashboard.php" class="alert alert-danger d-flex align-items-start border-0 shadow-sm p-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-1 me-4"></i>
                                <div class="w-100">
                                    <h4 class="alert-heading fw-bold mb-2">URGENT: Patient No-Show Detected</h4>
                                    <p class="mb-3 text-dark">Patient <strong>PT-1055 (High Risk)</strong> has not checked in for their scheduled session after the grace period. Please select a welfare action immediately.</p>

                                    <div class="bg-white p-3 rounded shadow-sm">
                                        <h6 class="fw-bold text-danger mb-3">Welfare Options (Incident #1055-A)</h6>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Select Action to Log:</label>
                                            <select class="form-select" id="welfareActionType" name="welfareActionType">
                                                <option value="call_patient">Called Patient Directly</option>
                                                <option value="call_emergency_contact">Contacted Emergency Contact</option>
                                                <option value="escalate_authorities">Escalated to Local Authorities</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Action Notes:</label>
                                            <textarea class="form-control" id="welfareNotes" rows="2" placeholder="Detail the outcome of the action..." name="welfareNotes"></textarea>
                                        </div>

                                        <div class="d-flex gap-2 flex-wrap mt-3 pt-3 border-top">
                                            <button type="submit" class="btn btn-danger px-4" id="btnLogAction">Submit Action & Save Log</button>

                                            <div class="ms-auto d-flex gap-2">
                                                <button type="button" class="btn btn-outline-warning text-dark" id="btnPatientLate">Patient Joined Late (Override)</button>
                                                <button type="button" class="btn btn-outline-secondary" id="btnFalseAlarm">Mark as False Alarm</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Today's Schedule Overview -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-custom h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold text-primary-custom"><i class="bi bi-calendar3 me-2"></i>Today's Schedule</h5>
                                <a href="sessions.php" class="btn btn-primary-custom rounded-pill">Go to Sessions Interface</a>
                            </div>
                            <div class="card-body p-0 mt-3">
                                <div class="table-responsive">
                                    <table class="table table-hover table-custom mb-0">
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-3">Time</th>
                                                <th class="px-4 py-3">Patient</th>
                                                <th class="px-4 py-3">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dashboardScheduleBody">
                                        <?php foreach ($todaySchedule as $row): ?>
                                            <tr>
                                                <td class="px-4 py-3 fw-semibold"><?php echo date('h:i A', strtotime($row['appointment_date'])); ?></td>
                                                <td class="px-4 py-3"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                                <td class="px-4 py-3">
                                                    <span class="badge <?php echo $row['session_state'] === 'Scheduled' ? 'bg-secondary' : 'bg-success'; ?>">
                                                        <?php echo $row['session_state']; ?>
                                                    </span>
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

    <!-- Toast Container -->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/therapist.js"></script>
</body>

<!--
    Variabled
        welfareActionType = ( call_patient / call_emergency_contact / escalate_authorities )
        welfareNotes = welfareNotes
-->
</html>
