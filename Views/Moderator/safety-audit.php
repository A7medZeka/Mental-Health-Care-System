<?php
require_once __DIR__ . '/../../Models/Services/SafetyAuditService.php';
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Moderator') {
    header('Location: ../Auth/login.php');
    exit();
}

$service = new SafetyAuditService();
$flaggedCount = $service->getFlaggedPostsCount();
$stats = $service->getAuditStats();
$logs = $service->getAllLogs();
$modName = $_SESSION['first_name'] ?? 'Sarah M.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safety Audit Log - Moderator - MentalCare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* الـ CSS الأصلي بتاعك بالمللي */
        .worm-badge { background: linear-gradient(135deg,#2F8F7E,#48B6A2); color: white; border-radius: 30px; padding: .4rem 1.2rem; font-size: .83rem; font-weight: 700; display: inline-flex; align-items: center; gap: .4rem; }
        .sev-badge { display:inline-flex; align-items:center; gap:.3rem; font-size:.76rem; font-weight:700; border-radius:20px; padding:.25rem .75rem; }
        .sev-critical { background:#fff0f0; color:#dc3545; border:1.5px solid #f5c6cb; }
        .audit-entry { background:#fff; border-radius:14px; box-shadow:0 2px 16px rgba(47,143,126,0.07); margin-bottom:1rem; border-left:5px solid transparent; }
        .audit-entry.border-Critical { border-color:#dc3545; }
        .audit-entry.border-High { border-color:#fd7e14; }
        .audit-timeline { display:flex; flex-direction:column; gap:.5rem; }
        .tl-item { display:flex; gap:.75rem; align-items:flex-start; font-size:.84rem; }
        .tl-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; margin-top:4px; }
        .tl-dot-system { background:#48B6A2; } .tl-dot-mod { background:#F4B41A; }
        .immutability-toast { background:linear-gradient(135deg,#2d0000,#5c0000); color:#ff9999; border-radius:10px; padding:.75rem 1rem; font-size:.85rem; font-weight:600; display:none; margin-top:.5rem; }
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
                <ul class="nav flex-column mb-auto text-start">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="forum.php">
                            <i class="bi bi-shield-exclamation me-2"></i> Forum Moderation
                            <span class="badge bg-danger ms-2"><?php echo $flaggedCount; ?></span>
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="performance.php"><i class="bi bi-bar-chart-line me-2"></i> Therapist Performance</a></li>
                    <li class="nav-item"><a class="nav-link active" href="safety-audit.php"><i class="bi bi-journal-medical me-2"></i> Safety Audit Log</a></li>
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
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-4 border-bottom">
                <div>
                    <h1 class="h2 text-primary-custom fw-bold">Safety & Audit Log</h1>
                    <p class="text-secondary-custom mb-0">UC-35 — Non-repudiable log. Entries are <strong>immutable</strong>.</p>
                </div>
                <div class="text-end">
                    <span class="text-secondary-custom fw-bold d-block mb-1">Moderator: <?php echo htmlspecialchars($modName); ?></span>
                    <span class="worm-badge"><i class="bi bi-shield-lock-fill"></i> WORM Compliant</span>
                </div>
            </div>

            <div class="row g-3 mb-4 text-center">
                <div class="col-md-3"><div class="p-3 bg-white rounded shadow-sm"><h4 class="text-danger fw-bold"><?php echo $stats['critical']; ?></h4><small>Critical Events</small></div></div>
                <div class="col-md-3"><div class="p-3 bg-white rounded shadow-sm"><h4 class="text-warning fw-bold"><?php echo $stats['high']; ?></h4><small>High Risk</small></div></div>
                <div class="col-md-3"><div class="p-3 bg-white rounded shadow-sm"><h4 class="text-primary fw-bold"><?php echo $stats['total']; ?></h4><small>Actions Logged</small></div></div>
                <div class="col-md-3"><div class="p-3 bg-white rounded shadow-sm"><h4 style="color:#6f42c1;"><?php echo $stats['tampers']; ?></h4><small>Tamper Attempts</small></div></div>
            </div>

            <div id="auditLogContainer">
                <?php foreach ($logs as $log): ?>
                    <div class="audit-entry border-<?php echo $log['severity']; ?>">
                        <div class="p-4">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <span class="font-monospace fw-bold text-danger"><?php echo htmlspecialchars($log['eventID'] ?? $log['eventId'] ?? 'EVT-' . str_pad($log['id'] ?? rand(100,999), 4, '0', STR_PAD_LEFT)); ?></span>
                                    <span class="sev-badge sev-<?php echo strtolower($log['severity']); ?> ms-2"><?php echo $log['severity']; ?></span>
                                </div>
                                <small class="text-muted"><?php echo $log['timestamp']; ?> UTC</small>
                            </div>
                            <p class="fw-semibold mb-3"><?php echo htmlspecialchars($log['description']); ?></p>

                            <div class="audit-timeline mb-3">
                                <div class="tl-item">
                                    <div class="tl-dot tl-dot-system"></div>
                                    <div><strong>System</strong> — Event flagged and record created.</div>
                                </div>
                                <?php foreach ($log['timeline'] ?? [] as $step): ?>
                                    <div class="tl-item">
                                        <div class="tl-dot tl-dot-mod"></div>
                                        <div><strong><?php echo $step['timestamp']; ?></strong> — <?php echo htmlspecialchars($step['action']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-secondary" onclick="Audit.tryTamper(this)"><i class="bi bi-pencil"></i> Try Edit</button>
                                <button class="btn btn-sm btn-outline-danger" onclick="Audit.tryTamper(this)"><i class="bi bi-trash"></i> Try Delete</button>
                            </div>
                            <div class="immutability-toast mt-2"><i class="bi bi-lock-fill"></i> Denied. WORM-protected entry.</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>

<script>
    const Audit = {
        tryTamper: function(btn) {
            const toast = btn.closest('.p-4').querySelector('.immutability-toast');
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3000);
        }
    };
</script>
</body>
</html>