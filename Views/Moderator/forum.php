<?php
session_start();

// 1. استدعاء الملفات الأساسية والـ Dependencies
require_once __DIR__ . '/../../Core/Validation.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../../Models/Repositories/PostRepository.php';
require_once __DIR__ . '/../../Models/Repositories/ModeratorRepository.php';
require_once __DIR__ . '/../../Models/Services/CrisisService.php';
require_once __DIR__ . '/../../Models/Services/NotificationService.php';
require_once __DIR__ . '/../../Models/Services/ModerationService.php';

// 2. حماية الصفحة
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Moderator') {
    header('Location: ../Auth/login.php');
    exit();
}

$modName = $_SESSION['first_name'] ?? 'Moderator';

// 3. تجهيز الـ Services
$repo = new PostRepository();
$modRepo = new ModeratorRepository();
$crisis = new CrisisService(new NotificationService());
$modService = new ModerationService($repo, $modRepo, $crisis);

// 4. جلب المنشورات المرفوعة للمراجعة من الداتابيز
$flaggedPosts = $modService->getModerationQueue();

// 5. حساب الإحصائيات (Stats Banner) لايف من الداتابيز
$countFlagged = 0;
$countReview = 0;
$countHidden = 0;

if (!empty($flaggedPosts)) {
    foreach ($flaggedPosts as $p) {
        if ($p['status'] === 'Flagged') $countFlagged++;
        if ($p['status'] === 'Under Review' || $p['status'] === 'Escalated') $countReview++;
        if ($p['status'] === 'Hidden') $countHidden++;
    }
}

// حساب المنشورات المقبولة اليوم (Cleared/Published)
$db = SingletonDatabase::getInstance()->getConnection();
$stmt = $db->query("SELECT COUNT(*) FROM moderation_logs WHERE action_taken IN ('Cleared', 'Published') AND DATE(created_at) = CURDATE()");
$countCleared = $stmt->fetchColumn() ?: 0;

$totalNavBadge = $countFlagged + $countReview;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Moderation - MentalCare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .status-flagged     { background:#fff0f0; color:#dc3545; border:1.5px solid #f5c6cb; }
        .status-under-review{ background:#fff8e1; color:#e67e22; border:1.5px solid #ffe0a0; }
        .status-hidden      { background:#f4f4f4; color:#6c757d; border:1.5px solid #dee2e6; }
        .status-deleted     { background:#2d0000; color:#ff8080; border:1.5px solid #8b0000; }
        .status-cleared     { background:#e6f5f2; color:#2F8F7E; border:1.5px solid #aad9d0; }

        .status-badge { display:inline-flex; align-items:center; gap:.35rem; font-size:.78rem; font-weight:700; border-radius:20px; padding:.3rem .85rem; }

        .mod-post-card { background:#fff; border-radius:14px; box-shadow:0 2px 18px rgba(47,143,126,0.07); margin-bottom:1rem; overflow:hidden; transition:box-shadow .2s; }
        .mod-post-card:hover { box-shadow:0 6px 28px rgba(47,143,126,0.13); }

        .mod-post-card .card-accent { width:5px; flex-shrink:0; }
        .accent-crisis   { background:#dc3545; }
        .accent-flagged  { background:#fd7e14; }
        .accent-review   { background:#ffc107; }
        .accent-hidden   { background:#adb5bd; }

        .avatar-anon { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.9rem; color:#fff; flex-shrink:0; }

        .severity-crisis  { color:#dc3545; font-weight:700; }
        .severity-high    { color:#fd7e14; font-weight:700; }
        .severity-normal  { color:#6c757d; }

        .mod-tab { border-radius:30px; font-weight:600; font-size:.87rem; padding:.4rem 1.1rem; border:2px solid transparent; transition:all .2s; cursor:pointer; background:transparent; }
        .mod-tab.active { background:var(--primary-green); color:#fff; border-color:var(--primary-green); }
        .mod-tab:not(.active) { color:var(--text-brown); border-color:#dee2e6; }
        .mod-tab:not(.active):hover { border-color:var(--primary-green); color:var(--primary-green); }

        .action-btn { border-radius:8px; font-size:.82rem; font-weight:600; padding:.35rem .9rem; border:none; transition:all .2s; }
        .timeline-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; margin-top:5px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white shadow-sm">
            <div class="position-sticky pt-4">
                <div class="text-center mb-4">
                    <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size:2rem;"></i>
                    <h5 class="fw-bold text-primary-custom mt-2">MentalCare System</h5>
                    <span class="badge" style="background:var(--primary-green);color:white;">Moderator Portal</span>
                </div>
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="nav-item">
                        <a class="nav-link active" href="forum.php">
                            <i class="bi bi-shield-exclamation me-2"></i> Forum Moderation
                            <span class="badge bg-danger ms-2" id="navBadgeForum"><?php echo $totalNavBadge; ?></span>
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="performance.php"><i class="bi bi-bar-chart-line me-2"></i> Therapist Performance</a></li>
                    <li class="nav-item"><a class="nav-link" href="safety-audit.php"><i class="bi bi-journal-medical me-2"></i> Safety Audit Log</a></li>
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
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div>
                    <h1 class="h2 text-primary-custom fw-bold"><i class="bi bi-shield-exclamation me-2"></i>Forum Moderation Queue</h1>
                    <p class="text-secondary-custom mb-0">UC-31 — Review, escalate, hide or delete flagged posts. Workflow: <code>Flagged → Under Review → Hidden / Deleted / Published</code></p>
                </div>
                <span class="text-secondary-custom fw-bold"><i class="bi bi-person-circle me-1"></i> Moderator: <?php echo htmlspecialchars($modName); ?></span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3"><div class="card card-custom text-center py-3"><div class="h4 fw-bold text-danger mb-0" id="countFlagged"><?php echo $countFlagged; ?></div><small class="text-secondary-custom">Flagged</small></div></div>
                <div class="col-6 col-md-3"><div class="card card-custom text-center py-3"><div class="h4 fw-bold mb-0" style="color:#e67e22;" id="countReview"><?php echo $countReview; ?></div><small class="text-secondary-custom">Under Review</small></div></div>
                <div class="col-6 col-md-3"><div class="card card-custom text-center py-3"><div class="h4 fw-bold text-secondary mb-0" id="countHidden"><?php echo $countHidden; ?></div><small class="text-secondary-custom">Hidden</small></div></div>
                <div class="col-6 col-md-3"><div class="card card-custom text-center py-3"><div class="h4 fw-bold text-primary-custom mb-0" id="countCleared"><?php echo $countCleared; ?></div><small class="text-secondary-custom">Cleared Today</small></div></div>
            </div>

            <div class="d-flex gap-2 mb-4 flex-wrap" id="modFilterBar">
                <button class="mod-tab active" data-filter="all">All Posts</button>
                <button class="mod-tab" data-filter="flagged"><i class="bi bi-flag-fill me-1 text-danger"></i> Flagged</button>
                <button class="mod-tab" data-filter="crisis"><i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> Crisis</button>
                <button class="mod-tab" data-filter="under-review"><i class="bi bi-hourglass-split me-1" style="color:#e67e22;"></i> Under Review</button>
                <button class="mod-tab" data-filter="hidden"><i class="bi bi-eye-slash me-1 text-secondary"></i> Hidden</button>
            </div>

            <div id="moderationQueue">
                <?php if (empty($flaggedPosts)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-shield-check text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-secondary">All caught up!</h4>
                        <p class="text-muted">There are no flagged posts awaiting moderation at this time.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($flaggedPosts as $post):
                        $postId = $post['post_id'];
                        $content = htmlspecialchars($post['content'] ?? '');
                        $authorName = htmlspecialchars($post['author_pseudonym'] ?? 'User_' . $post['user_id']);
                        $initials = strtoupper(substr($authorName, 0, 2));
                        $createdDate = isset($post['created_at']) ? date('M d, H:i', strtotime($post['created_at'])) : 'N/A';
                        $status = $post['status'] ?? 'Flagged';

                        // تصنيف الخطورة زي الديزاين بتاعك
                        $severityLevel = $crisis->classifySeverity($content);
                        $isCrisis = ($severityLevel === 'Critical');

                        // الألوان والحالات الافتراضية
                        $dataCategory = 'flagged'; $accentClass = 'accent-flagged'; $badgeClass = 'status-flagged';
                        $iconClass = 'bi-people'; $badgeText = 'User-Reported'; $avatarBg = '#8F5E2F';

                        // تغيير الألوان حسب الحالة (Crisis, Under Review, Hidden)
                        if ($isCrisis && $status !== 'Hidden') {
                            $dataCategory = 'crisis'; $accentClass = 'accent-crisis'; $iconClass = 'bi-robot';
                            $badgeText = 'Auto-Flagged · Crisis'; $avatarBg = '#dc3545';
                        } elseif ($status === 'Under Review') {
                            $dataCategory = 'under-review'; $accentClass = 'accent-review'; $badgeClass = 'status-under-review';
                            $iconClass = 'bi-hourglass-split'; $badgeText = 'Under Review'; $avatarBg = '#e67e22';
                        } elseif ($status === 'Hidden') {
                            $dataCategory = 'hidden'; $accentClass = 'accent-hidden'; $badgeClass = 'status-hidden';
                            $iconClass = 'bi-eye-slash'; $badgeText = 'Hidden'; $avatarBg = '#6c757d';
                        }
                        ?>

                        <div class="mod-post-card d-flex" data-postid="<?php echo $postId; ?>" data-status="<?php echo strtolower(str_replace(' ', '-', $status)); ?>" data-category="<?php echo $dataCategory; ?>">
                            <div class="card-accent <?php echo $accentClass; ?>"></div>
                            <div class="p-4 flex-grow-1">
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="avatar-anon" style="background:<?php echo $avatarBg; ?>;"><?php echo $initials; ?></div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                            <strong class="<?php echo ($status === 'Hidden') ? 'text-secondary' : 'text-primary-custom'; ?>"><?php echo $authorName; ?></strong>
                                            <span class="status-badge <?php echo $badgeClass; ?>">
                                            <i class="bi <?php echo $iconClass; ?>"></i> <?php echo $badgeText; ?>
                                        </span>
                                            <span class="ms-auto text-secondary-custom small"><?php echo $createdDate; ?></span>
                                        </div>

                                        <p class="mb-0 <?php echo ($status === 'Hidden') ? 'text-muted fst-italic' : 'text-dark'; ?>">
                                            <?php echo ($status === 'Hidden') ? '[Content hidden — post awaiting final deletion decision]' : "\"" . $content . "\""; ?>
                                        </p>

                                        <div class="mt-2 d-flex gap-3 small text-secondary-custom">
                                            <span><i class="bi bi-flag me-1"></i>Reported by: <strong><?php echo $isCrisis ? 'System (keyword scan)' : 'Community users'; ?></strong></span>
                                            <?php if ($status !== 'Hidden'): ?>
                                                <span class="severity-<?php echo strtolower($severityLevel === 'Critical' ? 'crisis' : 'high'); ?>">
                                                <i class="bi <?php echo $isCrisis ? 'bi-exclamation-triangle-fill' : 'bi-dash-circle'; ?> me-1"></i>
                                                Severity: <?php echo $isCrisis ? 'Crisis' : 'High Risk'; ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($status === 'Under Review'): ?>
                                            <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small">
                                                <i class="bi bi-info-circle me-1"></i><em>Previously marked "Under Review" — temporarily hidden pending decision.</em>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Moderator Note (logged with action):</label>
                                    <textarea class="form-control form-control-sm mod-note" rows="2" placeholder="Add a note for this action..."></textarea>
                                </div>

                                <div class="d-flex gap-2 flex-wrap align-items-center border-top pt-3">
                                    <?php if ($status !== 'Hidden'): ?>
                                        <button class="action-btn btn-warning text-dark" onclick="Forum.moderatePost(this,'Under Review')"><i class="bi bi-hourglass-split me-1"></i> Mark Under Review</button>
                                        <button class="action-btn btn-secondary text-white" onclick="Forum.moderatePost(this,'Hidden')"><i class="bi bi-eye-slash me-1"></i> Hide Post</button>
                                    <?php endif; ?>

                                    <button class="action-btn btn-danger" onclick="Forum.moderatePost(this,'Deleted')">
                                        <i class="bi bi-trash3 me-1"></i> <?php echo ($status === 'Hidden') ? 'Confirm Delete' : 'Delete Post'; ?>
                                    </button>

                                    <button class="action-btn ms-auto btn-outline-success" style="border:2px solid #2F8F7E; color:#2F8F7E;" onclick="Forum.moderatePost(this,'Published')">
                                        <i class="bi <?php echo ($status === 'Hidden') ? 'bi-arrow-counterclockwise' : 'bi-check-circle'; ?> me-1"></i> <?php echo ($status === 'Hidden') ? 'Restore & Clear' : 'Keep Post (Clear Flag)'; ?>
                                    </button>

                                    <?php if ($status !== 'Hidden'): ?>
                                        <button class="action-btn btn-outline-danger" style="border:2px solid #dc3545;" onclick="Forum.escalateToAdmin(this)"><i class="bi bi-arrow-up-circle me-1"></i> Escalate to Admin</button>
                                    <?php endif; ?>
                                </div>

                                <div class="status-timeline mt-3 pt-2 border-top small text-secondary-custom" id="timeline-post-<?php echo $postId; ?>">
                                    <?php if (!empty($post['latest_action'])): ?>
                                        <div class="d-flex gap-2 align-items-start">
                                            <div class="timeline-dot <?php echo ($status === 'Hidden') ? 'bg-secondary' : 'bg-warning'; ?>"></div>
                                            <span><strong>Last Action:</strong> <?php echo htmlspecialchars($post['latest_action']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/forum.js"></script>
</body>
</html>