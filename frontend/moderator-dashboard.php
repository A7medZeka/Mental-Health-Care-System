<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderator Dashboard - MentalCare System</title>
    <meta name="description" content="Moderator control panel for forum moderation, safety audit and therapist performance.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .mod-stat-card {
            border: none; border-radius: 14px;
            background: white;
            box-shadow: 0 2px 18px rgba(47,143,126,0.08);
            padding: 1.5rem;
            transition: transform .25s, box-shadow .25s;
        }
        .mod-stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 28px rgba(47,143,126,0.14); }
        .mod-stat-icon { width: 54px; height: 54px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }

        .quick-action-btn {
            border: none; border-radius: 12px; padding: 1.2rem 1rem;
            display: flex; flex-direction: column; align-items: center; gap: .5rem;
            background: white; box-shadow: 0 2px 14px rgba(47,143,126,0.07);
            transition: all .2s; text-decoration: none; color: var(--text-brown);
            font-weight: 600; font-size: .9rem;
        }
        .quick-action-btn i { font-size: 1.8rem; color: var(--primary-green); }
        .quick-action-btn:hover { background: var(--light-green); color: var(--primary-green); transform: translateY(-2px); }

        .alert-item {
            border-radius: 10px; border: none;
            box-shadow: 0 2px 12px rgba(220,53,69,0.08);
            margin-bottom: .75rem; padding: 1rem 1.25rem;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Moderator Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white shadow-sm">
            <div class="position-sticky pt-4">
                <div class="text-center mb-4">
                    <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size:2rem;"></i>
                    <h5 class="fw-bold text-primary-custom mt-2">MentalCare System</h5>
                    <span class="badge" style="background:var(--primary-green); color:white;">Moderator Portal</span>
                </div>
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="moderator-dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="moderator-forum.php">
                            <i class="bi bi-shield-exclamation me-2"></i> Forum Moderation
                            <span class="badge bg-danger ms-auto" id="navBadgeForum">5</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="moderator-performance.php">
                            <i class="bi bi-bar-chart-line me-2"></i> Therapist Performance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="moderator-safety-audit.php">
                            <i class="bi bi-journal-medical me-2"></i> Safety Audit Log
                            <span class="badge bg-warning text-dark ms-auto" id="navBadgeAudit">2</span>
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
                <div>
                    <h1 class="h2 text-primary-custom fw-bold">Moderator Dashboard</h1>
                    <p class="text-secondary-custom mb-0">Overview of forum health, pending actions, and alerts.</p>
                </div>
                <span class="text-secondary-custom fw-bold"><i class="bi bi-person-circle me-1"></i> Moderator: Sarah M.</span>
            </div>

            <!-- Stats Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="mod-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-secondary-custom mb-1 small">Flagged Posts</p>
                                <h3 class="fw-bold mb-0" style="color:#dc3545;">5</h3>
                                <small class="text-secondary-custom">Awaiting review</small>
                            </div>
                            <div class="mod-stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-flag-fill"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="mod-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-secondary-custom mb-1 small">Crisis Alerts</p>
                                <h3 class="fw-bold mb-0" style="color:#fd7e14;">2</h3>
                                <small class="text-secondary-custom">Needs escalation</small>
                            </div>
                            <div class="mod-stat-icon bg-warning bg-opacity-10" style="color:#fd7e14;"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="mod-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-secondary-custom mb-1 small">Posts Today</p>
                                <h3 class="fw-bold mb-0 text-primary-custom">47</h3>
                                <small class="text-secondary-custom">Across all categories</small>
                            </div>
                            <div class="mod-stat-icon bg-light-green text-primary-custom"><i class="bi bi-chat-dots-fill"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="mod-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-secondary-custom mb-1 small">Actions Logged</p>
                                <h3 class="fw-bold mb-0 text-primary-custom">18</h3>
                                <small class="text-secondary-custom">This month</small>
                            </div>
                            <div class="mod-stat-icon bg-light-green text-primary-custom"><i class="bi bi-journal-check"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card card-custom mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="fw-bold text-primary-custom">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <a href="moderator-forum.php" class="quick-action-btn w-100">
                                <i class="bi bi-shield-exclamation"></i> Review Flagged Posts
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="moderator-performance.php" class="quick-action-btn w-100">
                                <i class="bi bi-bar-chart-line"></i> Therapist Metrics
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="moderator-safety-audit.php" class="quick-action-btn w-100">
                                <i class="bi bi-journal-medical"></i> Safety Audit Log
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="patient-forum.php" class="quick-action-btn w-100">
                                <i class="bi bi-eye"></i> View Public Forum
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Crisis Alerts -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-custom">
                        <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0" style="color:#dc3545;"><i class="bi bi-exclamation-octagon-fill me-2"></i>Recent Crisis Alerts (UC-29)</h5>
                            <a href="moderator-forum.php" class="btn btn-sm btn-primary-custom rounded-pill">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="alert-item bg-danger bg-opacity-10 d-flex align-items-center gap-3">
                                <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                                <div class="flex-grow-1">
                                    <strong class="d-block">Crisis Keyword Detected</strong>
                                    <small class="text-secondary-custom">Post by <em>QuietMoon_3312</em> — flagged for immediate review · 12 min ago</small>
                                </div>
                                <a href="moderator-forum.php" class="btn btn-sm btn-danger">Review</a>
                            </div>
                            <div class="alert-item" style="background:#fff3cd;">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-flag-fill text-warning fs-4"></i>
                                    <div class="flex-grow-1">
                                        <strong class="d-block">User Reported Post</strong>
                                        <small class="text-secondary-custom">Post by <em>StormCloud_1182</em> — reported by 3 users · 1 hour ago</small>
                                    </div>
                                    <a href="moderator-forum.php" class="btn btn-sm btn-warning text-dark">Review</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
