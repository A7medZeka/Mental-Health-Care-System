<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safety Logs - Admin MHC</title>
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
                            <a class="nav-link" href="admin-patients.php">
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
                            <a class="nav-link active" href="admin-safety-logs.php">
                                <i class="bi bi-journal-medical me-2"></i> Safety Logs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="moderator-dashboard.php">
                                <i class="bi bi-shield-exclamation me-2"></i> Forum Moderation
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <div>
                        <h1 class="h2 text-primary-custom fw-bold">Safety & Audit Logs</h1>
                        <p class="text-secondary-custom mb-0">Immutable record of high-risk events and system actions.</p>
                    </div>
                    <div class="alert alert-info py-2 mb-0 border-0 bg-light-green text-primary-custom">
                        <i class="bi bi-shield-check me-2"></i><strong>WORM compliant:</strong> Write Once, Read Many
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-white border-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold text-danger mb-0"><i class="bi bi-exclamation-octagon-fill me-2"></i>High Risk Event Logs</h5>
                                <div class="input-group" style="max-width: 300px;">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Search logs...">
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="alert alert-warning m-3 py-2">
                                    <small><i class="bi bi-info-circle me-2"></i><strong>Alternative Scenario Demo:</strong> Double-click a row to attempt an edit, or click the delete (trash) icon to test immutability protections.</small>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-custom mb-0">
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-3">Timestamp (UTC)</th>
                                                <th class="px-4 py-3">Event ID</th>
                                                <th class="px-4 py-3">Severity</th>
                                                <th class="px-4 py-3">Description</th>
                                                <th class="px-4 py-3">Handled By</th>
                                                <th class="px-4 py-3 text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="safety-log-row" title="Double click to edit (Forbidden)">
                                                <td class="px-4 py-3 text-muted font-monospace small">2026-04-22 14:32:01</td>
                                                <td class="px-4 py-3 fw-semibold">EVT-9921</td>
                                                <td class="px-4 py-3"><span class="badge bg-danger">Critical</span></td>
                                                <td class="px-4 py-3">Patient PT-98234 triggered keyword alert during intake.</td>
                                                <td class="px-4 py-3">Mod_Sarah</td>
                                                <td class="px-4 py-3 text-center">
                                                    <button class="btn btn-sm btn-light text-danger delete-log-btn" title="Delete Log">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="safety-log-row" title="Double click to edit (Forbidden)">
                                                <td class="px-4 py-3 text-muted font-monospace small">2026-04-21 09:15:44</td>
                                                <td class="px-4 py-3 fw-semibold">EVT-9905</td>
                                                <td class="px-4 py-3"><span class="badge bg-warning text-dark">High</span></td>
                                                <td class="px-4 py-3">Failed authentication attempt (5x) for Admin account.</td>
                                                <td class="px-4 py-3">System</td>
                                                <td class="px-4 py-3 text-center">
                                                    <button class="btn btn-sm btn-light text-danger delete-log-btn" title="Delete Log">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="safety-log-row" title="Double click to edit (Forbidden)">
                                                <td class="px-4 py-3 text-muted font-monospace small">2026-04-20 18:02:11</td>
                                                <td class="px-4 py-3 fw-semibold">EVT-9882</td>
                                                <td class="px-4 py-3"><span class="badge bg-danger">Critical</span></td>
                                                <td class="px-4 py-3">Emergency contact protocol initiated for PT-91102.</td>
                                                <td class="px-4 py-3">Dr. Grant</td>
                                                <td class="px-4 py-3 text-center">
                                                    <button class="btn btn-sm btn-light text-danger delete-log-btn" title="Delete Log">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </td>
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
    <script src="assets/js/admin.js"></script>
</body>
</html>
