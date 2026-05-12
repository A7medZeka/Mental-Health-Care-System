<?php
session_start();
require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../../Core/ImmutablePattern.php';
require_once __DIR__ . '/../../Models/Repositories/TherapistRepository.php';
require_once __DIR__ . '/../../Controllers/PermissionController.php';
// Top of patients.php
$selectedPatientId = $_GET['patient_id'] ?? null;
$currentPermissions = [];

if ($selectedPatientId) {
    $stmt = SingletonDatabase::getInstance()->getConnection()->prepare(
            "SELECT resource_id, is_allowed FROM resource_access_control WHERE patient_id = ?"
    );
    $stmt->execute([$selectedPatientId]);
    $currentPermissions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Returns [resource_id => is_allowed]
}
// 1. التأمين (Authentication)
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Therapist') {
    header('Location: ../Auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$therapistRepo = new TherapistRepository();
$userFactory = new ImmutableUserFactory();
$permissionCtrl = new PermissionController();

// جلب بيانات الثيرابيست للهيدر
$therapistObj = $userFactory->createTherapistFromId($user_id);

// 2. UC-20: جلب المرضى الحقيقيين
$myPatients = $therapistRepo->getMyPatients($user_id);

// متغيرات لرسائل النجاح والخطأ
$success_msg = '';
$error_msg = '';

// 3. معالجة حفظ الصلاحيات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient_id'])) {
    $targetPatient = (int)$_POST['patient_id'];
    $permissions = $_POST['permissions'] ?? [];

    // إرسال الأمر للـ Controller
    $result = $permissionCtrl->updatePermissions($user_id, $targetPatient, $permissions);

    if ($result) {
        $success_msg = "Permissions updated successfully for Patient ID: $targetPatient";
    } else {
        $error_msg = "Failed to update permissions or permission denied.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Management - Mental Health Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
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
                        <a class="nav-link" href="sessions.php">
                            <i class="bi bi-calendar-event me-2"></i> Manage Sessions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="patients.php">
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
                <h1 class="h2 text-primary-custom fw-bold">Patient Management</h1>
                <div class="d-flex align-items-center">
                        <span class="text-secondary-custom me-3 fw-bold">
                            <i class="bi bi-person-circle me-1"></i>
                            Therapist: <?php echo htmlspecialchars($therapistObj->getFirstName() . ' ' . $therapistObj->getLastName()); ?>
                        </span>
                    <span class="badge bg-success py-2 px-3"><i class="bi bi-person-check-fill me-2"></i>Verified</span>
                </div>
            </div>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card card-custom h-100" id="contentAccessUI">
                        <form method="POST" action="patients.php">
                            <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold text-primary-custom"><i class="bi bi-shield-lock me-2"></i>Resource Access Rules</h5>

                                <select class="form-select w-auto" id="patientSelect" name="patient_id" required>
                                    <option value="" disabled selected>Select a patient...</option>
                                    <?php foreach ($myPatients as $patient): ?>
                                        <option value="<?php echo $patient['user_id']; ?>">
                                            <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?> (PT-<?php echo $patient['user_id']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="card-body">
                                <p class="text-secondary-custom mb-4">Manage access to sensitive clinical materials for the selected patient.</p>

                                <div class="list-group mb-4">

                                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold">Advanced CBT Worksheets</h6>
                                        </div>
                                        <div class="form-check form-switch fs-4 mb-0">
                                            <input class="form-check-input" type="checkbox" name="permissions[1]"
                                                    <?php echo (isset($currentPermissions[1]) && $currentPermissions[1]) ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold">Trauma Recovery Guide (High-Risk)</h6>
                                            <small class="text-muted">Requires Senior Authorization.</small>
                                        </div>
                                        <div class="form-check form-switch fs-4 mb-0">
                                            <input class="form-check-input permission-toggle" type="checkbox" role="switch" name="permissions[2]" value="1">
                                        </div>
                                    </div>

                                </div>

                                <button type="submit" class="btn btn-primary-custom" id="btnSavePermissions">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/therapist.js"></script>
</body>
</html>