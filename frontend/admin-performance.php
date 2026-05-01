<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapist Performance - Admin - MentalCare System</title>
    <meta name="description" content="Admin view of therapist performance metrics, ratings and feedback. UC-32.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .star-rating { display:inline-flex; gap:2px; }
        .star-filled  { color:#F4B41A; }
        .star-empty   { color:#dee2e6; }
        .metric-card {
            border:none; border-radius:14px; background:#fff;
            box-shadow:0 2px 18px rgba(47,143,126,0.08); padding:1.5rem;
            transition:transform .25s, box-shadow .25s;
        }
        .metric-card:hover { transform:translateY(-3px); box-shadow:0 6px 28px rgba(47,143,126,0.14); }
        .metric-icon { width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; }
        .chart-bar-wrap { display:flex; flex-direction:column; gap:.55rem; }
        .chart-row { display:flex; align-items:center; gap:.75rem; font-size:.85rem; }
        .chart-label { width:110px; flex-shrink:0; color:var(--text-brown); font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .chart-track { flex:1; background:#e9ecef; border-radius:20px; height:12px; overflow:hidden; }
        .chart-fill  { height:100%; border-radius:20px; background:var(--primary-green); transition:width .8s cubic-bezier(.4,0,.2,1); }
        .chart-fill.warn   { background:#F4B41A; }
        .chart-fill.danger { background:#dc3545; }
        .chart-value { width:42px; text-align:right; font-weight:700; color:var(--primary-green); font-size:.85rem; }
        .therapist-row { display:flex; align-items:center; gap:1rem; padding:1rem 1.25rem; border-bottom:1px solid rgba(0,0,0,0.05); transition:background .15s; cursor:pointer; }
        .therapist-row:last-child { border-bottom:none; }
        .therapist-row:hover { background:var(--light-green); }
        .therapist-row.selected { background:#e6f5f2; }
        .therapist-avatar { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:.95rem; flex-shrink:0; }
        .feedback-item { padding:.85rem 1rem; border-radius:10px; background:#f8fdf9; border:1px solid rgba(47,143,126,0.12); margin-bottom:.6rem; font-size:.88rem; }
        .no-data-state { text-align:center; padding:3rem 1rem; color:var(--light-brown); }
        .no-data-state i { font-size:3rem; opacity:.3; }
        .sparkline { display:flex; align-items:flex-end; gap:3px; height:36px; }
        .spark-bar { width:8px; border-radius:3px 3px 0 0; background:var(--primary-green); opacity:.7; flex-shrink:0; }
        .period-btn { border-radius:20px; font-size:.82rem; font-weight:600; padding:.3rem .9rem; border:2px solid #dee2e6; background:transparent; color:var(--text-brown); cursor:pointer; transition:all .2s; }
        .period-btn.active, .period-btn:hover { background:var(--primary-green); color:#fff; border-color:var(--primary-green); }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">

        <!-- Admin Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white shadow-sm">
            <div class="position-sticky pt-4">
                <div class="text-center mb-4">
                    <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size:2rem;"></i>
                    <h5 class="fw-bold text-primary-custom mt-2">MentalCare System</h5>
                </div>
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item"><a class="nav-link" href="admin-dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-patients.php"><i class="bi bi-people me-2"></i> Manage Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-therapists.php"><i class="bi bi-person-badge me-2"></i> Therapists Verification</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin-performance.php"><i class="bi bi-bar-chart-line me-2"></i> Therapist Performance</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-rbac.php"><i class="bi bi-shield-lock me-2"></i> RBAC Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-safety-logs.php"><i class="bi bi-journal-medical me-2"></i> Safety Logs</a></li>
                    <li class="nav-item"><a class="nav-link" href="moderator-dashboard.php"><i class="bi bi-shield-exclamation me-2"></i> Forum Moderation</a></li>
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
                <div>
                    <h1 class="h2 text-primary-custom fw-bold"><i class="bi bi-bar-chart-line me-2"></i>Therapist Performance Metrics</h1>
                    <p class="text-secondary-custom mb-0">UC-32 — Aggregated ratings, session feedback and trend dashboards for clinic management.</p>
                </div>
                <span class="text-secondary-custom fw-bold"><i class="bi bi-person-circle me-1"></i> System Administrator</span>
            </div>

            <!-- Filter Bar -->
            <div class="d-flex align-items-center gap-3 flex-wrap mb-4">
                <div class="d-flex gap-2">
                    <button class="period-btn active" data-period="7">Last 7 Days</button>
                    <button class="period-btn" data-period="30">Last 30 Days</button>
                    <button class="period-btn" data-period="90">Last 90 Days</button>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <select class="form-select form-select-sm" id="therapistFilter" style="min-width:180px;">
                        <option value="all">All Therapists</option>
                        <option value="t1">Dr. Sarah Harding</option>
                        <option value="t2">Dr. James Okafor</option>
                        <option value="t3">Dr. Lena Novak</option>
                        <option value="t4">Dr. Yusuf Al-Amin</option>
                    </select>
                    <button class="btn btn-primary-custom btn-sm px-3" id="btnApplyFilter">
                        <i class="bi bi-funnel me-1"></i> Apply
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon bg-light-green text-primary-custom"><i class="bi bi-star-fill"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">Avg. Rating</p>
                            <h3 class="fw-bold mb-0 text-primary-custom" id="kpiAvgRating">4.3</h3>
                            <div class="star-rating mt-1">
                                <i class="bi bi-star-fill star-filled"></i><i class="bi bi-star-fill star-filled"></i>
                                <i class="bi bi-star-fill star-filled"></i><i class="bi bi-star-fill star-filled"></i>
                                <i class="bi bi-star-half star-filled"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon bg-light-green text-primary-custom"><i class="bi bi-chat-left-text-fill"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">Total Reviews</p>
                            <h3 class="fw-bold mb-0 text-primary-custom">284</h3>
                            <small class="text-secondary-custom">this period</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon bg-light-green text-primary-custom"><i class="bi bi-calendar-check-fill"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">Sessions Completed</p>
                            <h3 class="fw-bold mb-0 text-primary-custom">612</h3>
                            <small class="text-secondary-custom">this period</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="metric-card d-flex align-items-center gap-3">
                        <div class="metric-icon" style="background:#fff3cd;"><i class="bi bi-person-x-fill" style="color:#e67e22;"></i></div>
                        <div>
                            <p class="text-secondary-custom small mb-0">No-Show Rate</p>
                            <h3 class="fw-bold mb-0" style="color:#e67e22;">6.4%</h3>
                            <small class="text-secondary-custom">across all therapists</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rankings + Detail -->
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h5 class="fw-bold text-primary-custom mb-0"><i class="bi bi-trophy me-2"></i>Therapist Rankings</h5>
                            <small class="text-secondary-custom">Click a therapist to view detailed metrics</small>
                        </div>
                        <div class="card-body p-0 mt-2" id="therapistList">
                            <div class="therapist-row selected" data-tid="t1" onclick="Performance.selectTherapist('t1',this)">
                                <div class="therapist-avatar" style="background:#2F8F7E;">SH</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Dr. Sarah Harding</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="star-rating">
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                        </div>
                                        <small class="text-secondary-custom">4.9 · 98 reviews</small>
                                    </div>
                                </div>
                                <span class="badge" style="background:var(--light-green);color:var(--primary-green);">Top</span>
                            </div>
                            <div class="therapist-row" data-tid="t2" onclick="Performance.selectTherapist('t2',this)">
                                <div class="therapist-avatar" style="background:#48B6A2;">JO</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Dr. James Okafor</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="star-rating">
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-half star-filled" style="font-size:.75rem;"></i>
                                        </div>
                                        <small class="text-secondary-custom">4.5 · 72 reviews</small>
                                    </div>
                                </div>
                            </div>
                            <div class="therapist-row" data-tid="t3" onclick="Performance.selectTherapist('t3',this)">
                                <div class="therapist-avatar" style="background:#F4B41A;">LN</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Dr. Lena Novak</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="star-rating">
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-empty star-empty" style="font-size:.75rem;"></i>
                                        </div>
                                        <small class="text-secondary-custom">4.1 · 61 reviews</small>
                                    </div>
                                </div>
                            </div>
                            <div class="therapist-row" data-tid="t4" onclick="Performance.selectTherapist('t4',this)">
                                <div class="therapist-avatar" style="background:#8F5E2F;">YA</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Dr. Yusuf Al-Amin</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="star-rating">
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-half star-filled" style="font-size:.75rem;"></i>
                                            <i class="bi bi-star-empty star-empty" style="font-size:.75rem;"></i>
                                        </div>
                                        <small class="text-secondary-custom">3.7 · 53 reviews</small>
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark" style="font-size:.7rem;">Watch</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Panel -->
                <div class="col-lg-7">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-0 d-flex align-items-center gap-3">
                            <div class="therapist-avatar" id="detailAvatar" style="background:#2F8F7E; width:52px; height:52px; font-size:1.1rem;">SH</div>
                            <div>
                                <h5 class="fw-bold text-primary-custom mb-0" id="detailName">Dr. Sarah Harding</h5>
                                <small class="text-secondary-custom" id="detailSpec">Specialisation: Anxiety &amp; CBT · 5 years experience</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold text-primary-custom mb-3 mt-2">Rating Breakdown</h6>
                            <div class="chart-bar-wrap mb-4" id="ratingBreakdown">
                                <div class="chart-row"><span class="chart-label"><i class="bi bi-star-fill star-filled me-1" style="font-size:.75rem;"></i>5 Stars</span><div class="chart-track"><div class="chart-fill" style="width:72%"></div></div><span class="chart-value">72%</span></div>
                                <div class="chart-row"><span class="chart-label"><i class="bi bi-star-fill star-filled me-1" style="font-size:.75rem;"></i>4 Stars</span><div class="chart-track"><div class="chart-fill" style="width:18%"></div></div><span class="chart-value">18%</span></div>
                                <div class="chart-row"><span class="chart-label"><i class="bi bi-star-fill star-filled me-1" style="font-size:.75rem;"></i>3 Stars</span><div class="chart-track"><div class="chart-fill warn" style="width:7%"></div></div><span class="chart-value" style="color:#F4B41A;">7%</span></div>
                                <div class="chart-row"><span class="chart-label"><i class="bi bi-star-fill star-filled me-1" style="font-size:.75rem;"></i>2 Stars</span><div class="chart-track"><div class="chart-fill danger" style="width:2%"></div></div><span class="chart-value" style="color:#dc3545;">2%</span></div>
                                <div class="chart-row"><span class="chart-label"><i class="bi bi-star-fill star-filled me-1" style="font-size:.75rem;"></i>1 Star</span><div class="chart-track"><div class="chart-fill danger" style="width:1%"></div></div><span class="chart-value" style="color:#dc3545;">1%</span></div>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-4"><div class="text-center p-3 rounded-3 bg-light-green"><div class="fw-bold text-primary-custom fs-5" id="dKpiSessions">187</div><small class="text-secondary-custom">Sessions</small></div></div>
                                <div class="col-4"><div class="text-center p-3 rounded-3 bg-light-green"><div class="fw-bold text-primary-custom fs-5" id="dKpiPatients">34</div><small class="text-secondary-custom">Patients</small></div></div>
                                <div class="col-4"><div class="text-center p-3 rounded-3" style="background:#fff3cd;"><div class="fw-bold fs-5" style="color:#e67e22;" id="dKpiNoShow">3.2%</div><small class="text-secondary-custom">No-Show</small></div></div>
                            </div>
                            <h6 class="fw-bold text-primary-custom mb-2">Rating Trend <small class="text-secondary-custom fw-normal">(last 7 weeks)</small></h6>
                            <div class="sparkline mb-4" id="sparkline">
                                <div class="spark-bar" style="height:60%;" title="3.8"></div>
                                <div class="spark-bar" style="height:65%;" title="4.0"></div>
                                <div class="spark-bar" style="height:72%;" title="4.2"></div>
                                <div class="spark-bar" style="height:78%;" title="4.5"></div>
                                <div class="spark-bar" style="height:80%;" title="4.6"></div>
                                <div class="spark-bar" style="height:90%;" title="4.8"></div>
                                <div class="spark-bar" style="height:98%;" title="4.9"></div>
                            </div>
                            <h6 class="fw-bold text-primary-custom mb-2">Recent Patient Feedback</h6>
                            <div id="feedbackList">
                                <div class="feedback-item">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="star-rating"><i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i><i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i><i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i><i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i><i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i></div>
                                        <small class="text-secondary-custom">2 days ago</small>
                                    </div>
                                    "Incredibly supportive and always listens carefully. I feel heard every session."
                                </div>
                                <div class="feedback-item">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="star-rating"><i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i><i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i><i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i><i class="bi bi-star-fill star-filled" style="font-size:.75rem;"></i><i class="bi bi-star-empty star-empty" style="font-size:.75rem;"></i></div>
                                        <small class="text-secondary-custom">5 days ago</small>
                                    </div>
                                    "Great therapist. Would appreciate slightly longer sessions."
                                </div>
                            </div>
                            <div class="no-data-state d-none" id="noDataState">
                                <i class="bi bi-bar-chart d-block mb-3"></i>
                                <p class="fw-semibold">Insufficient Data</p>
                                <p class="small">This therapist does not yet have enough feedback for meaningful metrics.</p>
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
<script src="assets/js/forum.js"></script>
</body>
</html>
