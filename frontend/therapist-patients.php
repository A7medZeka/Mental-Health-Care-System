<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Management - Mental Health Care</title>
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
                            <a class="nav-link" href="therapist-dashboard.php">
                                <i class="bi bi-house-door me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="therapist-sessions.php">
                                <i class="bi bi-calendar-event me-2"></i> Manage Sessions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="therapist-patients.php">
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
                        <a href="index.php" class="btn btn-outline-danger w-100 mt-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2 text-primary-custom fw-bold">Patient Management</h1>
                    <div class="d-flex align-items-center">
                        <span class="text-secondary-custom me-3 fw-bold"><i class="bi bi-person-circle me-1"></i> Therapist: Dr. Harding</span>
                        <span class="badge bg-success py-2 px-3"><i class="bi bi-person-check-fill me-2"></i>Verified</span>
                    </div>
                </div>

                <div class="row">
                    <!-- Control Access to Sensitive Content (UC-20) -->
                    <div class="col-lg-8 mb-4">
                        <div class="card card-custom h-100" id="contentAccessUI">
                            <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold text-primary-custom"><i class="bi bi-shield-lock me-2"></i>Resource Access Rules</h5>
                                <select class="form-select w-auto" id="patientSelect" name="patientSelect">
                                    <option value="PT-1055">Jane Doe (PT-1055)</option>
                                </select>
                            </div>
                            <div class="card-body">
                                <p class="text-secondary-custom mb-4">Manage access to sensitive clinical materials for the selected patient.</p>
                                
                                <div class="list-group mb-4">
                                    
                                    <!-- Valid Change Scenario -->
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold">Advanced CBT Worksheets</h6>
                                            <small class="text-muted">Standard material (Level 1).</small>
                                        </div>
                                        <div class="form-check form-switch fs-4 mb-0">
                                            <input class="form-check-input permission-toggle" type="checkbox" role="switch" data-resource="cbt" checked>
                                        </div>
                                    </div>
                                    
                                    <!-- Permission Denied Scenario -->
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold">Trauma Recovery Guide (High-Risk)</h6>
                                            <small class="text-muted">Requires Senior Authorization.</small>
                                        </div>
                                        <div class="form-check form-switch fs-4 mb-0">
                                            <input class="form-check-input permission-toggle" type="checkbox" role="switch" data-resource="trauma_high">
                                        </div>
                                    </div>

                                </div>

                                <button class="btn btn-primary-custom" id="btnSavePermissions">Save Changes</button>
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
        patientSelect = ( PT-1055 )
-->
</html>
