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
if ($_SESSION['role'] !== 'Therapist') {
    $map = [
        'Admin'     => 'admin-dashboard.php',
        'Patient'   => 'patient-dashboard.php',
        'Moderator' => 'moderator-dashboard.php',
    ];
    header('Location: ' . ($map[$_SESSION['role']] ?? 'index.php'));
    exit();
}
$role = $_SESSION['role'] ?? 'Therapist';
$first_name = $_SESSION['first_name'] ?? 'Therapist';
$last_name  = $_SESSION['last_name']  ?? '';
$email = $_SESSION['email'] ?? '';
$age = $_SESSION['age'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$user_id = $_SESSION['user_id'];
$conn = getConnection();
$stmt = $conn->prepare("SELECT age FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$age = $user['age'] ?? '';
$stmt2 = $conn->prepare("SELECT gender FROM users WHERE user_id = ?");
$stmt2->execute([$user_id]);
$user2 = $stmt2->fetch();
$gender = $user2['gender'] ?? '';
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
                        <li class="nav-item">
                            <a class="nav-link active" href="therapist-dashboard.php">
                                <i class="bi bi-house-door me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="therapist-sessions.php">
                                <i class="bi bi-calendar-event me-2"></i> Manage Sessions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="therapist-patients.php">
                                <i class="bi bi-people me-2"></i> Manage Patients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="therapist-insights.php">
                                <i class="bi bi-graph-up me-2"></i> Clinical Insights
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="mx-3 mt-5">
                    <div class="px-3">
                        <a href="logout.php" class="btn btn-outline-danger w-100 mt-3 d-flex align-items-center justify-content-center">
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
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card card-custom border-warning border-start border-4">
                            <div class="card-body py-3 d-flex align-items-center" id="reminderNotification">
                                <i class="bi bi-bell-fill text-warning fs-3 me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">System Reminder: Upcoming Session</h6>
                                    <p class="mb-0 text-secondary-custom small">You have a scheduled session with <strong>Jane Doe</strong> in 15 minutes.</p>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary ms-auto" onclick="this.closest('.card').remove();">Dismiss</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Required: High-Risk No-Show (UC-17) -->
                <div class="row mb-4" id="incidentDashboardUI">
                    <div class="col-12">
                        <div class="alert alert-danger d-flex align-items-start border-0 shadow-sm p-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-1 me-4"></i>
                            <div class="w-100">
                                <h4 class="alert-heading fw-bold mb-2">URGENT: Patient No-Show Detected</h4>
                                <p class="mb-3 text-dark">Patient <strong>PT-1055 (High Risk)</strong> has not checked in for their scheduled session after the grace period. Please select a welfare action immediately.</p>
                                
                                <!-- Therapist Welfare Options -->
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
                                        <!-- Main Scenario -->
                                        <button class="btn btn-danger px-4" id="btnLogAction">Submit Action & Save Log</button>
                                        
                                        <!-- Alt Scenarios -->
                                        <div class="ms-auto d-flex gap-2">
                                            <button class="btn btn-outline-warning text-dark" id="btnPatientLate">Patient Joined Late (Override)</button>
                                            <button class="btn btn-outline-secondary" id="btnFalseAlarm">Mark as False Alarm</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule Overview -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-custom h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold text-primary-custom"><i class="bi bi-calendar3 me-2"></i>Today's Schedule</h5>
                                <a href="therapist-sessions.php" class="btn btn-primary-custom rounded-pill">Go to Sessions Interface</a>
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
                                            <tr id="sessionRow_PT1055">
                                                <td class="px-4 py-3 fw-semibold">09:00 AM</td>
                                                <td class="px-4 py-3 text-danger fw-bold">PT-1055 (High Risk)</td>
                                                <td class="px-4 py-3"><span class="badge bg-danger" id="statusBadge_PT1055">No-Show</span></td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 fw-semibold">10:00 AM</td>
                                                <td class="px-4 py-3">Jane Doe</td>
                                                <td class="px-4 py-3"><span class="badge bg-secondary">Scheduled</span></td>
                                            </tr>
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
    <script src="assets/js/main.js"></script>
    <script src="assets/js/therapist.js"></script>
</body>

<!--
    Variabled
        welfareActionType = ( call_patient / call_emergency_contact / escalate_authorities )
        welfareNotes = welfareNotes
-->
</html>
