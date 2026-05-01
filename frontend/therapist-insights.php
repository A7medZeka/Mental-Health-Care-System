<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinical Insights - Mental Health Care</title>
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
                            <a class="nav-link" href="therapist-patients.php">
                                <i class="bi bi-people me-2"></i> Manage Patients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="therapist-insights.php">
                                <i class="bi bi-graph-up me-2"></i> Clinical Insights
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="mx-3 mt-5">
                    <div class="px-3">
                        <a href="home.php" class="btn btn-outline-danger w-100 mt-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2 text-primary-custom fw-bold">Clinical Insights</h1>
                    <div class="d-flex align-items-center">
                        <span class="text-secondary-custom me-3 fw-bold"><i class="bi bi-person-circle me-1"></i> Therapist: Dr. Harding</span>
                        <span class="badge bg-success py-2 px-3"><i class="bi bi-person-check-fill me-2"></i>Verified</span>
                    </div>
                </div>

                <div class="row">
                    
                    <!-- UC 19: Review Mood Trend Reports -->
                    <div class="col-lg-6 mb-4">
                        <div class="card card-custom h-100" id="reportsUI">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-primary-custom"><i class="bi bi-bar-chart-fill me-2"></i>Mood Trend Reports</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-4">
                                    <select class="form-select w-auto" id="moodPatientSelect" name="moodPatientSelect">
                                        <option value="PT-1055">Jane Doe</option>
                                        <option value="PT-NEW">New Patient (No Data)</option>
                                    </select>
                                    <button class="btn btn-primary-custom" id="btnOpenMoodReport">Open Report</button>
                                </div>
                                
                                <div id="moodReportArea" style="display:none;">
                                    <div class="d-flex gap-2 align-items-center mb-3 p-3 bg-light rounded border">
                                        <label class="fw-semibold">Date Range:</label>
                                        <input type="date" class="form-control form-control-sm" id="startDate" name="startDate">
                                        <span>to</span>
                                        <input type="date" class="form-control form-control-sm" id="endDate" name="endDate">
                                        <button class="btn btn-sm btn-dark" id="btnGenerateTrend">Generate</button>
                                    </div>
                                    
                                    <div id="chartContainer" class="bg-white border rounded d-flex align-items-center justify-content-center p-4 text-center" style="min-height: 200px;">
                                        <span class="text-muted">Chart will render here...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- UC 27: Correlate Sleep and Mood Patterns -->
                    <div class="col-lg-6 mb-4">
                        <div class="card card-custom h-100" id="correlationUI">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-primary-custom"><i class="bi bi-moon-stars-fill me-2"></i>Sleep & Mood Correlation</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-4">
                                    <select class="form-select w-auto" id="insightPatientSelect" name="insightPatientSelect">
                                        <option value="PT-1055">Jane Doe (Has Data)</option>
                                        <option value="PT-LACK">John Smith (Incomplete Data)</option>
                                    </select>
                                    <button class="btn btn-primary-custom" id="btnOpenInsights">Open Insights</button>
                                </div>
                                
                                <div id="insightsAnalysisArea" style="display:none;">
                                    <div class="d-flex gap-2 align-items-center mb-3">
                                        <label class="fw-semibold">Select Window:</label>
                                        <select class="form-select form-select-sm w-auto" id="correlationWindow" name="correlationWindow">
                                            <option value="lastMonth">Last Month</option>
                                            <option value="last3Months">Last 3 Months</option>
                                        </select>
                                        <button class="btn btn-sm btn-dark" id="btnAnalyzeCorrelation">Analyze</button>
                                    </div>
                                    
                                    <div id="correlationResults" class="alert alert-success d-none mt-3">
                                        <h6 class="fw-bold"><i class="bi bi-check-circle-fill me-2"></i>Strong Correlation Detected</h6>
                                        <p class="mb-0 small">Analysis shows that when sleep is under 6 hours, mood score drops by an average of 40% the following day.</p>
                                    </div>
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
        startDate = startDate
        endDate = endDate
        moodPatientSelect = ( PT-1055 / PT-NEW )
        insightPatientSelect = ( PT-1055 / PT-LACK )
        correlationWindow = ( lastMonth / last3Months )
-->
</html>
