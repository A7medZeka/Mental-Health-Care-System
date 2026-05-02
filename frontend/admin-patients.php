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
$email = $_SESSION['email'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
$totalPatients = 0;
$conn = getConnection();
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'Patient'");
$stmt->execute();
$totalPatients = $stmt->fetch()['total'] ?? 0;
$stmt = $conn->prepare("SELECT username  FROM users WHERE role = 'Patient'");
$stmt->execute();
$patientName = $stmt->fetch()['username'] ?? 0;
$date = date('Y-m-d H:i:s');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - Admin MHC</title>
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
                            <a class="nav-link" href="admin-dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin-patients.php">
                                <i class="bi bi-people me-2"></i> Manage Patients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-therapists.php">
                                <i class="bi bi-person-badge me-2"></i> Therapists Verification
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-rbac.php">
                                <i class="bi bi-shield-lock me-2"></i> RBAC Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-performance.php">
                                <i class="bi bi-bar-chart-line me-2"></i> Therapist Performance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-safety-logs.php">
                                <i class="bi bi-journal-medical me-2"></i> Safety Logs
                            </a>
                        </li>
                        
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2 text-primary-custom fw-bold">Manage Patients</h1>
                </div>

                <div class="row">
                    <!-- Demo Patient Card (Simulating a selected patient profile) -->
                    <div class="col-lg-6 mb-4">
                        <div class="card card-custom h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-primary-custom">Patient Profile: Jane Doe</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>ID:</strong> PT-98234 <br>
                                    <strong>Current Status:</strong> <span class="badge bg-secondary" id="currentStatusBadge">Registered</span>
                                    <input type="hidden" id="currentStatus" value="registered" name="currentStatus">
                                </div>

                                <hr>
                                
                                <h6 class="text-secondary-custom mb-3">1. Update Status Transition</h6>
                                <p class="small text-muted mb-2">Valid flow: Registered → Screened → Matched → Active</p>
                                <div class="d-flex gap-2 align-items-center mb-3">
                                    <select class="form-select w-auto" id="newStatusSelect" name="newStatusSelect">
                                        <option value="registered">Registered</option>
                                        <option value="screened">Screened</option>
                                        <option value="matched">Matched</option>
                                        <option value="active">Active</option>
                                    </select>
                                    <button class="btn btn-primary-custom" id="updateStatusBtn">Confirm Update</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card card-custom h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-primary-custom">Intake Documentation</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-secondary-custom mb-3">Upload patient intake forms (PDF only).</p>
                                
                                <div class="mb-3">
                                    <label for="intakeFile" class="form-label fw-semibold">Select PDF File</label>
                                    <input class="form-control" type="file" id="intakeFile" accept=".pdf,.jpg,.exe" name="intakeFile">
                                </div>
                                <button class="btn btn-primary-custom" id="uploadIntakeBtn">
                                    <i class="bi bi-upload me-2"></i>Upload File
                                </button>
                                <p class="small text-muted mt-2">Try uploading a non-PDF file to trigger the alternative scenario.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table (Visual representation) -->
                <div class="card card-custom mt-2">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3">Patient ID</th>
                                        <th class="px-4 py-3">Name</th>
                                        <th class="px-4 py-3">Registration Date</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($i = 1; $i <= $totalPatients; $i++){ ?>
                                        <tr class="bg-light">
                                            <td class="px-4 py-3 fw-semibold">PT-<?php echo $i; ?></td>
                                            <td class="px-4 py-3"> <?php echo $patientName; ?></td>
                                            <td class="px-4 py-3"><?php echo date('M j, Y'); ?></td>
                                            <td class="px-4 py-3"><span class="badge bg-secondary">Registered</span></td>
                                            <td class="px-4 py-3"><button class="btn btn-sm btn-outline-secondary" disabled>Currently Viewing</button></td>
                                        </tr>
                                    <?php } ?>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/admin.js"></script>
</body>

<!--
    Variabled
        currentStatus = currentStatus (hidden, value="registered")
        intakeFile = intakeFile
        newStatusSelect = ( registered / screened / matched / active )
-->
</html>
