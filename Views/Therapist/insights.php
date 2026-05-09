<?php
session_start();
require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../../Core/ImmutablePattern.php';
require_once __DIR__ . '/../../Models/Repositories/TherapistRepository.php';

// 1. التأمين (Authentication)
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Therapist') {
    header('Location: ../Auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$therapistRepo = new TherapistRepository();
$userFactory = new ImmutableUserFactory();

// جلب بيانات الثيرابيست الحقيقية
$therapistObj = $userFactory->createTherapistFromId($user_id);
$myPatients = $therapistRepo->getMyPatients($user_id);

// 2. معالجة البيانات (UC-19 & UC-27)
$selectedMoodId = $_GET['moodPatientSelect'] ?? null;
$selectedInsightId = $_GET['insightPatientSelect'] ?? null;

$moodData = [];
$correlation = null;

if ($selectedMoodId) {
    $moodData = $therapistRepo->getPatientMoodEntries($selectedMoodId);
}

if ($selectedInsightId) {
    // جلب الحسابات الرياضية الحقيقية من الـ Repository (Math Logic)
    $correlation = $therapistRepo->getMoodSleepCorrelation($selectedInsightId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinical Insights - Mental Health Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .is-invalid-custom { border: 2px solid #dc3545 !important; }
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
                    <li class="nav-item"><a class="nav-link" href="sessions.php"><i class="bi bi-calendar-event me-2"></i> Sessions</a></li>
                    <li class="nav-item"><a class="nav-link" href="patients.php"><i class="bi bi-people me-2"></i> Patients</a></li>
                    <li class="nav-item"><a class="nav-link active" href="insights.php"><i class="bi bi-graph-up me-2"></i> Insights</a></li>
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
            <div class="d-flex justify-content-between align-items-center border-bottom mb-4 pb-2">
                <h1 class="h2 text-primary-custom fw-bold">Clinical Insights</h1>
                <div class="d-flex align-items-center">
                    <span class="text-secondary-custom me-3 fw-bold"><i class="bi bi-person-circle me-1"></i> Therapist: <?php echo htmlspecialchars($therapistObj->getFirstName() . ' ' . $therapistObj->getLastName()); ?></span>
                    <span class="badge bg-success py-2 px-3"><i class="bi bi-person-check-fill me-2"></i>Verified</span>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card card-custom h-100 shadow-sm">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h5 class="fw-bold text-primary-custom"><i class="bi bi-bar-chart-fill me-2"></i>Mood Trend Reports</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="insights.php" id="formMood">
                                <div class="d-flex gap-2 mb-4">
                                    <select class="form-select w-auto" id="moodPatientSelect" name="moodPatientSelect">
                                        <option value="" disabled <?php echo !$selectedMoodId ? 'selected' : ''; ?>>Select Patient...</option>
                                        <?php foreach ($myPatients as $patient): ?>
                                            <option value="<?php echo $patient['user_id']; ?>" <?php echo ($selectedMoodId == $patient['user_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary-custom" id="btnOpenMoodReport">Load Report</button>
                                </div>
                            </form>

                            <?php if ($selectedMoodId): ?>
                                <div id="chartContainer" class="bg-light border rounded p-4 text-center">
                                    <?php if (!empty($moodData)): ?>
                                        <canvas id="patientMoodChart"></canvas>
                                        <script>window.moodEntries = <?php echo json_encode($moodData); ?>;</script>
                                    <?php else: ?>
                                        <p class="text-muted small">No mood data available for this patient.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card card-custom h-100 shadow-sm">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h5 class="fw-bold text-primary-custom"><i class="bi bi-moon-stars-fill me-2"></i>Sleep & Mood Correlation</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="insights.php" id="formInsight">
                                <div class="d-flex gap-2 mb-4">
                                    <select class="form-select w-auto" id="insightPatientSelect" name="insightPatientSelect">
                                        <option value="" disabled <?php echo !$selectedInsightId ? 'selected' : ''; ?>>Select Patient...</option>
                                        <?php foreach ($myPatients as $patient): ?>
                                            <option value="<?php echo $patient['user_id']; ?>" <?php echo ($selectedInsightId == $patient['user_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary-custom" id="btnOpenInsights">Analyze Data</button>
                                </div>
                            </form>

                            <?php if ($selectedInsightId && $correlation): ?>
                                <div class="p-3 bg-light rounded border mb-3">
                                    <div class="row text-center small">
                                        <div class="col-6 border-end">Avg Mood: <strong><?php echo number_format($correlation['avg_mood'] ?? 0, 1); ?></strong></div>
                                        <div class="col-6">Avg Sleep: <strong><?php echo number_format($correlation['avg_sleep'] ?? 0, 1); ?>h</strong></div>
                                    </div>
                                </div>
                                <?php
                                $coeff = $correlation['correlation_coefficient'] ?? 0;
                                $alert = ($coeff > 0.5) ? 'alert-success' : (($coeff < -0.5) ? 'alert-danger' : 'alert-info');
                                ?>
                                <div class="alert <?php echo $alert; ?> small mt-3">
                                    <h6 class="fw-bold mb-1">Mathematical Insight:</h6>
                                    <p class="mb-0">The analysis shows a <strong><?php echo number_format($coeff * 100, 1); ?>%</strong> correlation factor between sleep and mood recovery.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JS Validation for "Must Select Patient"
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const select = this.querySelector('select');
            if (!select.value) {
                e.preventDefault();
                select.classList.add('is-invalid-custom');
                // Show red toast error
                if (typeof showToast === 'function') {
                    showToast('Please select a patient before analyzing.', 'danger');
                } else {
                    alert('Selection Required: Please choose a patient from the list.');
                }
            } else {
                select.classList.remove('is-invalid-custom');
            }
        });
    });
</script>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/therapist.js"></script>
</body>
</html>