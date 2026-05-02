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
if ($_SESSION['role'] !== 'Moderator') {
    $map = [
        'Admin'     => 'admin-dashboard.php',
        'Patient'   => 'patient-dashboard.php',
        'Therapist' => 'therapist-dashboard.php',
        ];
        header('Location: ' . ($map[$_SESSION['role']] ?? 'index.php'));
        exit();
        }
$email = $_SESSION['email'] ?? '';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Moderation - MentalCare System</title>
    <meta name="description" content="Moderator queue to review, hide, or delete flagged community forum posts.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ── Status pill colours ── */
        .status-flagged     { background:#fff0f0; color:#dc3545; border:1.5px solid #f5c6cb; }
        .status-under-review{ background:#fff8e1; color:#e67e22; border:1.5px solid #ffe0a0; }
        .status-hidden      { background:#f4f4f4; color:#6c757d; border:1.5px solid #dee2e6; }
        .status-deleted     { background:#2d0000; color:#ff8080; border:1.5px solid #8b0000; }
        .status-cleared     { background:#e6f5f2; color:#2F8F7E; border:1.5px solid #aad9d0; }

        .status-badge {
            display:inline-flex; align-items:center; gap:.35rem;
            font-size:.78rem; font-weight:700; border-radius:20px; padding:.3rem .85rem;
        }

        /* ── Post Queue Card ── */
        .mod-post-card {
            background:#fff; border-radius:14px;
            box-shadow:0 2px 18px rgba(47,143,126,0.07);
            margin-bottom:1rem; overflow:hidden;
            transition:box-shadow .2s;
        }
        .mod-post-card:hover { box-shadow:0 6px 28px rgba(47,143,126,0.13); }

        .mod-post-card .card-accent {
            width:5px; flex-shrink:0;
        }
        .accent-crisis   { background:#dc3545; }
        .accent-flagged  { background:#fd7e14; }
        .accent-review   { background:#ffc107; }
        .accent-hidden   { background:#adb5bd; }

        .avatar-anon {
            width:40px; height:40px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-weight:700; font-size:.9rem; color:#fff; flex-shrink:0;
        }

        /* ── Severity chip ── */
        .severity-crisis  { color:#dc3545; font-weight:700; }
        .severity-high    { color:#fd7e14; font-weight:700; }
        .severity-normal  { color:#6c757d; }

        /* ── Filter tabs ── */
        .mod-tab { border-radius:30px; font-weight:600; font-size:.87rem;
                   padding:.4rem 1.1rem; border:2px solid transparent;
                   transition:all .2s; cursor:pointer; background:transparent; }
        .mod-tab.active { background:var(--primary-green); color:#fff; border-color:var(--primary-green); }
        .mod-tab:not(.active) { color:var(--text-brown); border-color:#dee2e6; }
        .mod-tab:not(.active):hover { border-color:var(--primary-green); color:var(--primary-green); }

        /* ── Action buttons inside card ── */
        .action-btn {
            border-radius:8px; font-size:.82rem; font-weight:600;
            padding:.35rem .9rem; border:none; transition:all .2s;
        }

        .timeline-dot {
            width:10px; height:10px; border-radius:50%; flex-shrink:0; margin-top:5px;
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
                    <span class="badge" style="background:var(--primary-green);color:white;">Moderator Portal</span>
                </div>
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="moderator-dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="moderator-forum.php">
                            <i class="bi bi-shield-exclamation me-2"></i> Forum Moderation
                            <span class="badge bg-danger ms-2" id="navBadgeForum">5</span>
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

            <!-- Page Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div>
                    <h1 class="h2 text-primary-custom fw-bold">
                        <i class="bi bi-shield-exclamation me-2"></i>Forum Moderation Queue
                    </h1>
                    <p class="text-secondary-custom mb-0">
                        UC-31 — Review, escalate, hide or delete flagged posts.
                        Workflow: <code>Flagged → Under Review → Hidden / Deleted / Cleared</code>
                    </p>
                </div>
                <span class="text-secondary-custom fw-bold">
                    <i class="bi bi-person-circle me-1"></i> Moderator: Sarah M.
                </span>
            </div>

            <!-- Stats Banner -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card card-custom text-center py-3">
                        <div class="h4 fw-bold text-danger mb-0" id="countFlagged">3</div>
                        <small class="text-secondary-custom">Flagged</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-custom text-center py-3">
                        <div class="h4 fw-bold mb-0" style="color:#e67e22;" id="countReview">2</div>
                        <small class="text-secondary-custom">Under Review</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-custom text-center py-3">
                        <div class="h4 fw-bold text-secondary mb-0" id="countHidden">1</div>
                        <small class="text-secondary-custom">Hidden</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-custom text-center py-3">
                        <div class="h4 fw-bold text-primary-custom mb-0" id="countCleared">4</div>
                        <small class="text-secondary-custom">Cleared Today</small>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="d-flex gap-2 mb-4 flex-wrap" id="modFilterBar">
                <button class="mod-tab active" data-filter="all">All Posts</button>
                <button class="mod-tab" data-filter="flagged">
                    <i class="bi bi-flag-fill me-1 text-danger"></i> Flagged
                </button>
                <button class="mod-tab" data-filter="crisis">
                    <i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> Crisis
                </button>
                <button class="mod-tab" data-filter="under-review">
                    <i class="bi bi-hourglass-split me-1" style="color:#e67e22;"></i> Under Review
                </button>
                <button class="mod-tab" data-filter="hidden">
                    <i class="bi bi-eye-slash me-1 text-secondary"></i> Hidden
                </button>
            </div>

            <!-- ═══════════════════════════════════════════
                 MODERATION QUEUE
                 ═══════════════════════════════════════════ -->
            <div id="moderationQueue">

                <!-- ── POST 1: Crisis (auto-flagged by keyword scan, UC-29) ── -->
                <div class="mod-post-card d-flex" data-postid="post-001" data-status="flagged" data-category="crisis">
                    <div class="card-accent accent-crisis"></div>
                    <div class="p-4 flex-grow-1">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="avatar-anon" style="background:#dc3545;">QM</div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <strong class="text-primary-custom">QuietMoon_3312</strong>
                                    <span class="status-badge status-flagged">
                                        <i class="bi bi-robot"></i> Auto-Flagged · Crisis
                                    </span>
                                    <span class="ms-auto text-secondary-custom small">12 min ago</span>
                                </div>
                                <p class="mb-0 text-dark">
                                    "I feel like I can't go on anymore. Nothing matters and I just want everything to stop. I don't know what to do..."
                                </p>
                                <div class="mt-2 d-flex gap-3 small text-secondary-custom">
                                    <span><i class="bi bi-flag me-1"></i>Reported by: <strong>System (keyword scan)</strong></span>
                                    <span class="severity-crisis"><i class="bi bi-exclamation-triangle-fill me-1"></i>Severity: Crisis</span>
                                </div>
                            </div>
                        </div>

                        <!-- ── Notes Field ── -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Moderator Note (logged with action):</label>
                            <textarea class="form-control form-control-sm mod-note" rows="2"
                                placeholder="Add a note for this action..."></textarea>
                        </div>

                        <!-- ── Action Buttons (UC-31 Typical + Alternate Courses) ── -->
                        <div class="d-flex gap-2 flex-wrap align-items-center border-top pt-3">
                            <!-- Typical: set Under Review first -->
                            <button class="action-btn btn-warning text-dark" onclick="Forum.moderatePost(this,'under-review')">
                                <i class="bi bi-hourglass-split me-1"></i> Mark Under Review
                            </button>
                            <!-- Typical: hide post -->
                            <button class="action-btn btn-secondary text-white" onclick="Forum.moderatePost(this,'hidden')">
                                <i class="bi bi-eye-slash me-1"></i> Hide Post
                            </button>
                            <!-- Typical: delete -->
                            <button class="action-btn btn-danger" onclick="Forum.moderatePost(this,'deleted')">
                                <i class="bi bi-trash3 me-1"></i> Delete Post
                            </button>
                            <!-- Alt: clear / keep -->
                            <button class="action-btn ms-auto btn-outline-success" style="border:2px solid #2F8F7E; color:#2F8F7E;"
                                onclick="Forum.moderatePost(this,'cleared')">
                                <i class="bi bi-check-circle me-1"></i> Keep Post (Clear Flag)
                            </button>
                            <!-- Escalate to Admin -->
                            <button class="action-btn btn-outline-danger" style="border:2px solid #dc3545;"
                                onclick="Forum.escalateToAdmin(this)">
                                <i class="bi bi-arrow-up-circle me-1"></i> Escalate to Admin
                            </button>
                        </div>

                        <!-- ── Status Timeline (updated by JS) ── -->
                        <div class="status-timeline mt-3 pt-2 border-top small text-secondary-custom" id="timeline-post-001"></div>
                    </div>
                </div>

                <!-- ── POST 2: User-Reported ── -->
                <div class="mod-post-card d-flex" data-postid="post-002" data-status="flagged" data-category="flagged">
                    <div class="card-accent accent-flagged"></div>
                    <div class="p-4 flex-grow-1">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="avatar-anon" style="background:#8F5E2F;">SC</div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <strong class="text-primary-custom">StormCloud_1182</strong>
                                    <span class="status-badge status-flagged">
                                        <i class="bi bi-people"></i> User-Reported
                                    </span>
                                    <span class="ms-auto text-secondary-custom small">1 hour ago</span>
                                </div>
                                <p class="mb-0 text-dark">
                                    "Honestly therapy is a scam, just save your money. None of this actually works and your therapist doesn't care about you."
                                </p>
                                <div class="mt-2 d-flex gap-3 small text-secondary-custom">
                                    <span><i class="bi bi-flag me-1"></i>Reported by: <strong>3 users</strong></span>
                                    <span class="severity-high"><i class="bi bi-dash-circle me-1"></i>Reason: Harmful / Discouraging</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Moderator Note:</label>
                            <textarea class="form-control form-control-sm mod-note" rows="2"
                                placeholder="Add a note for this action..."></textarea>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center border-top pt-3">
                            <button class="action-btn btn-warning text-dark" onclick="Forum.moderatePost(this,'under-review')">
                                <i class="bi bi-hourglass-split me-1"></i> Mark Under Review
                            </button>
                            <button class="action-btn btn-secondary text-white" onclick="Forum.moderatePost(this,'hidden')">
                                <i class="bi bi-eye-slash me-1"></i> Hide Post
                            </button>
                            <button class="action-btn btn-danger" onclick="Forum.moderatePost(this,'deleted')">
                                <i class="bi bi-trash3 me-1"></i> Delete Post
                            </button>
                            <button class="action-btn ms-auto btn-outline-success" style="border:2px solid #2F8F7E; color:#2F8F7E;"
                                onclick="Forum.moderatePost(this,'cleared')">
                                <i class="bi bi-check-circle me-1"></i> Keep Post (Clear Flag)
                            </button>
                            <button class="action-btn btn-outline-danger" style="border:2px solid #dc3545;"
                                onclick="Forum.escalateToAdmin(this)">
                                <i class="bi bi-arrow-up-circle me-1"></i> Escalate to Admin
                            </button>
                        </div>
                        <div class="status-timeline mt-3 pt-2 border-top small text-secondary-custom" id="timeline-post-002"></div>
                    </div>
                </div>

                <!-- ── POST 3: Already Under Review (Alt scenario demo) ── -->
                <div class="mod-post-card d-flex" data-postid="post-003" data-status="under-review" data-category="under-review">
                    <div class="card-accent accent-review"></div>
                    <div class="p-4 flex-grow-1">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="avatar-anon" style="background:#e67e22;">RF</div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <strong class="text-primary-custom">RiverFall_0055</strong>
                                    <span class="status-badge status-under-review">
                                        <i class="bi bi-hourglass-split"></i> Under Review
                                    </span>
                                    <span class="ms-auto text-secondary-custom small">3 hours ago</span>
                                </div>
                                <p class="mb-0 text-dark">
                                    "I know a way to feel numb that doesn't involve pills. Ask me about it..."
                                </p>
                                <div class="mt-2 d-flex gap-3 small text-secondary-custom">
                                    <span><i class="bi bi-flag me-1"></i>Reported by: <strong>System + 1 user</strong></span>
                                    <span class="severity-high"><i class="bi bi-exclamation-circle me-1"></i>Severity: High Risk</span>
                                </div>
                                <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <em>Alt scenario (UC-31 A1): Previously marked "Under Review" — temporarily hidden pending decision.</em>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Moderator Note:</label>
                            <textarea class="form-control form-control-sm mod-note" rows="2"
                                placeholder="Add a note for this action..."></textarea>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center border-top pt-3">
                            <button class="action-btn btn-secondary text-white" onclick="Forum.moderatePost(this,'hidden')">
                                <i class="bi bi-eye-slash me-1"></i> Hide Post
                            </button>
                            <button class="action-btn btn-danger" onclick="Forum.moderatePost(this,'deleted')">
                                <i class="bi bi-trash3 me-1"></i> Delete Post
                            </button>
                            <button class="action-btn ms-auto btn-outline-success" style="border:2px solid #2F8F7E; color:#2F8F7E;"
                                onclick="Forum.moderatePost(this,'cleared')">
                                <i class="bi bi-check-circle me-1"></i> Keep Post (Clear Flag)
                            </button>
                            <button class="action-btn btn-outline-danger" style="border:2px solid #dc3545;"
                                onclick="Forum.escalateToAdmin(this)">
                                <i class="bi bi-arrow-up-circle me-1"></i> Escalate to Admin
                            </button>
                        </div>
                        <div class="status-timeline mt-3 pt-2 border-top small text-secondary-custom" id="timeline-post-003">
                            <div class="d-flex gap-2 align-items-start">
                                <div class="timeline-dot bg-warning"></div>
                                <span><strong>2026-04-29 11:05</strong> — Marked "Under Review" by <em>Sarah M.</em></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── POST 4: Hidden (already processed) ── -->
                <div class="mod-post-card d-flex" data-postid="post-004" data-status="hidden" data-category="hidden">
                    <div class="card-accent accent-hidden"></div>
                    <div class="p-4 flex-grow-1">
                        <div class="d-flex align-items-start gap-3">
                            <div class="avatar-anon bg-secondary">MR</div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <strong class="text-secondary">MistRaven_2201</strong>
                                    <span class="status-badge status-hidden">
                                        <i class="bi bi-eye-slash"></i> Hidden
                                    </span>
                                    <span class="ms-auto text-secondary-custom small">Yesterday</span>
                                </div>
                                <p class="mb-0 text-muted fst-italic">[Content hidden — post awaiting final deletion decision]</p>
                                <div class="mt-2 d-flex gap-3 small text-secondary-custom">
                                    <span>Actioned by: <strong>Sarah M.</strong></span>
                                    <span>Reason: Promotes self-harm</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap border-top mt-3 pt-3">
                            <button class="action-btn btn-danger" onclick="Forum.moderatePost(this,'deleted')">
                                <i class="bi bi-trash3 me-1"></i> Confirm Delete
                            </button>
                            <button class="action-btn btn-outline-success ms-auto" style="border:2px solid #2F8F7E; color:#2F8F7E;"
                                onclick="Forum.moderatePost(this,'cleared')">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Restore &amp; Clear
                            </button>
                        </div>
                        <div class="status-timeline mt-3 pt-2 border-top small text-secondary-custom" id="timeline-post-004">
                            <div class="d-flex gap-2 align-items-start">
                                <div class="timeline-dot" style="background:#adb5bd;"></div>
                                <span><strong>2026-04-28 16:41</strong> — Hidden by <em>Sarah M.</em> — "Promotes harmful behaviour"</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── POST 5: Second User-Reported ── -->
                <div class="mod-post-card d-flex" data-postid="post-005" data-status="flagged" data-category="flagged">
                    <div class="card-accent accent-flagged"></div>
                    <div class="p-4 flex-grow-1">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="avatar-anon" style="background:#6f42c1;">NW</div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <strong class="text-primary-custom">NightWillow_9931</strong>
                                    <span class="status-badge status-flagged">
                                        <i class="bi bi-people"></i> User-Reported
                                    </span>
                                    <span class="ms-auto text-secondary-custom small">4 hours ago</span>
                                </div>
                                <p class="mb-0 text-dark">
                                    "DM me your real name and I'll send you something that will definitely help you feel better 😉"
                                </p>
                                <div class="mt-2 d-flex gap-3 small text-secondary-custom">
                                    <span><i class="bi bi-flag me-1"></i>Reported by: <strong>5 users</strong></span>
                                    <span class="severity-high"><i class="bi bi-dash-circle me-1"></i>Reason: Solicitation / Privacy Violation</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Moderator Note:</label>
                            <textarea class="form-control form-control-sm mod-note" rows="2"
                                placeholder="Add a note for this action..."></textarea>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center border-top pt-3">
                            <button class="action-btn btn-warning text-dark" onclick="Forum.moderatePost(this,'under-review')">
                                <i class="bi bi-hourglass-split me-1"></i> Mark Under Review
                            </button>
                            <button class="action-btn btn-secondary text-white" onclick="Forum.moderatePost(this,'hidden')">
                                <i class="bi bi-eye-slash me-1"></i> Hide Post
                            </button>
                            <button class="action-btn btn-danger" onclick="Forum.moderatePost(this,'deleted')">
                                <i class="bi bi-trash3 me-1"></i> Delete Post
                            </button>
                            <button class="action-btn ms-auto btn-outline-success" style="border:2px solid #2F8F7E; color:#2F8F7E;"
                                onclick="Forum.moderatePost(this,'cleared')">
                                <i class="bi bi-check-circle me-1"></i> Keep Post (Clear Flag)
                            </button>
                            <button class="action-btn btn-outline-danger" style="border:2px solid #dc3545;"
                                onclick="Forum.escalateToAdmin(this)">
                                <i class="bi bi-arrow-up-circle me-1"></i> Escalate to Admin
                            </button>
                        </div>
                        <div class="status-timeline mt-3 pt-2 border-top small text-secondary-custom" id="timeline-post-005"></div>
                    </div>
                </div>

            </div><!-- /moderationQueue -->

        </main>
    </div>
</div>

<!-- Toast Container -->
<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/forum.js"></script>
</body>
</html>
