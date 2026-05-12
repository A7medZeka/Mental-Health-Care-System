<?php
require_once __DIR__ . '/../../Controllers/PatientDashboardController.php';

$controller          = new PatientDashboardController();
$dashboardData       = $controller->handleRequest();

// ── View data from controller data providers ──────────────────────────────
$first_name          = $dashboardData['first_name'];
$last_name           = $dashboardData['last_name'];
$email               = $dashboardData['email'];
$age                 = $dashboardData['age'];
$gender              = $dashboardData['gender'];
$role                = $_SESSION['role'] ?? 'Patient';
$patientId           = (int)$_SESSION['user_id'];

$recentActivity      = $controller->getRecentActivity();
$onboardingChecklist = $controller->getOnboardingChecklist();
$myTherapist         = $controller->getMyTherapist();
$upcomingAppts       = $controller->getUpcomingAppointments();
$pastAppts           = $controller->getPastAppointments();
$availTherapists     = $controller->getAvailableTherapists();
$moodHistory         = $controller->getMoodHistory(7);
$todayMood           = $controller->getTodayMood();
$goals               = $controller->getGoals();
$journalEntries      = $controller->getJournalEntries(10);
$payments            = $controller->getPayments();
$insurance           = $controller->getInsurance();
$consents            = $controller->getConsents();
$resources           = $controller->getResources();
$goalResources       = $controller->getResourcesByGoalCategories();
$notifications       = $controller->getNotifications();
$unreadNotifs        = $controller->getUnreadNotifCount();
$intakeStatus        = $controller->getIntakeStatus();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Patient Dashboard | MentalCare System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 sidebar d-flex flex-column" style="height:100vh; position:sticky; top:0; overflow-y:auto;">
        <div class="p-3 pb-0">
          <div class="d-flex align-items-center mb-3">
            <i class="bi bi-heart-pulse-fill fs-3 text-primary-custom me-2"></i>
            <h5 class="fw-bold mb-0 text-primary-custom">MentalCare System</h5>
          </div>
        </div>
        <ul class="nav flex-column flex-grow-1 px-2">
          <li class="nav-item"><a class="nav-link active" data-section="section-dashboard" href="#" onclick="showSection('section-dashboard'); return false;"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-onboarding" href="#" onclick="showSection('section-onboarding'); return false;"><i class="bi bi-clipboard-check me-2"></i>Onboarding Checklist</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-therapist" href="#" onclick="showSection('section-therapist'); return false;"><i class="bi bi-person-check me-2"></i>My Therapist</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-reviews" href="#" onclick="showSection('section-reviews'); return false;"><i class="bi bi-star me-2"></i>Reviews & Ratings</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-appointments" href="#" onclick="showSection('section-appointments'); return false;"><i class="bi bi-calendar-event me-2"></i>Appointments</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-sessions" href="#" onclick="showSection('section-sessions'); return false;"><i class="bi bi-camera-video me-2"></i>Sessions</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-mood" href="#" onclick="showSection('section-mood'); return false;"><i class="bi bi-heart-pulse me-2"></i>Mood Tracker</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-goals" href="#" onclick="showSection('section-goals'); return false;"><i class="bi bi-bullseye me-2"></i>Wellness Goals</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-journal" href="#" onclick="showSection('section-journal'); return false;"><i class="bi bi-journal-richtext me-2"></i>My Journal</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-resources" href="#" onclick="showSection('section-resources'); return false;"><i class="bi bi-stars me-2"></i>Wellness Resources</a></li>
          <li class="nav-item"><a class="nav-link" href="forum.php"><i class="bi bi-chat-square-heart me-2"></i>Community Forum</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-payments" href="#" onclick="showSection('section-payments'); return false;"><i class="bi bi-credit-card me-2"></i>Payments &amp; Insurance</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-consents" href="#" onclick="showSection('section-consents'); return false;"><i class="bi bi-file-earmark-check me-2"></i>Legal Consents</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-emergency" href="#" style="color:#dc3545;" onclick="showSection('section-emergency'); return false;"><i class="bi bi-telephone-fill me-2" style="color:#dc3545;"></i><span style="color:#dc3545;">🚨 Crisis Support</span></a></li>
        </ul>
        <div class="px-2 pb-3 pt-2 border-top mt-2">
          <a href="../Auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
        </div>
      </nav>

      <!-- Main -->
      <main class="col-md-9 col-lg-10 p-4 fade-in">

        <!-- DASHBOARD -->
        <div id="section-dashboard">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Dashboard</h2>
                <span class="text-secondary-custom me-3"><i class="bi bi-person-circle me-1"></i> <?php echo 'Age: ' . ($age ?: 'N/A') . ' | ' . $role . ' | ' . htmlspecialchars($first_name . ' ' . $last_name).' | '. $gender; ?></span>
          </div>

          <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
              <div class="card card-custom h-100"><div class="card-body d-flex justify-content-between align-items-center">
                <div><h6 class="text-secondary-custom mb-2">Next Appointment</h6><h3 class="fw-bold text-primary-custom mb-0"><?php 
                  $nextAppt = !empty($upcomingAppts) ? $upcomingAppts[0] : null;
                  if ($nextAppt) {
                      echo date('M j, Y', strtotime($nextAppt['appointment_date']));
                  } else {
                      echo 'None scheduled';
                  }
                ?></h3></div>
                <div class="bg-light-green p-3 rounded-circle text-primary-custom"><i class="bi bi-calendar-check fs-4"></i></div>
              </div></div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="card card-custom h-100"><div class="card-body d-flex justify-content-between align-items-center">
                <div><h6 class="text-secondary-custom mb-2">Today's Mood</h6><h3 class="fw-bold text-primary-custom mb-0"><?php
                  $moodScore = $dashboardData['today_mood_score'] ?? 0;
                  echo match(true) {
                      $moodScore >= 5 => '😄',
                      $moodScore >= 4 => '😊',
                      $moodScore >= 3 => '😐',
                      $moodScore >= 2 => '😟',
                      $moodScore >= 1 => '😢',
                      default         => '—'
                  };
                  echo ' ' . htmlspecialchars($dashboardData['today_mood'] ?: 'Not logged');
                ?></h3></div>
                <div class="bg-light-green p-3 rounded-circle text-primary-custom d-flex align-items-center justify-content-center"><?php
                  $moodIconScore = $dashboardData['today_mood_score'] ?? 0;
                  echo match(true) {
                      $moodIconScore >= 5 => '<span style="font-size: 1.5rem;">😄</span>',
                      $moodIconScore >= 4 => '<span style="font-size: 1.5rem;">😊</span>',
                      $moodIconScore >= 3 => '<span style="font-size: 1.5rem;">😐</span>',
                      $moodIconScore >= 2 => '<span style="font-size: 1.5rem;">😟</span>',
                      $moodIconScore >= 1 => '<span style="font-size: 1.5rem;">😢</span>',
                      default         => '<span style="font-size: 1.5rem;">😐</span>'
                  };
                ?></div>
              </div></div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="card card-custom h-100"><div class="card-body d-flex justify-content-between align-items-center">
                <div><h6 class="text-secondary-custom mb-2">Active Goals</h6><h3 class="fw-bold text-primary-custom mb-0"><?php echo $dashboardData['active_goals']; ?> Goals</h3></div>
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-accent"><i class="bi bi-bullseye fs-4"></i></div>
              </div></div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="card card-custom h-100"><div class="card-body d-flex justify-content-between align-items-center">
                <div><h6 class="text-secondary-custom mb-2">Pending Actions</h6><h3 class="fw-bold text-danger mb-0"><?php echo $dashboardData['pending_actions']; ?> Items</h3></div>
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger"><i class="bi bi-exclamation-circle fs-4"></i></div>
              </div></div>
            </div>
          </div>

          <div class="row g-4 mb-4">
            <div class="col-12">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Onboarding Progress</h5></div>
                <div class="card-body">
                  <div class="d-flex justify-content-between mb-2"><span class="text-secondary-custom">Overall Completion</span><span class="fw-bold text-primary-custom"><?php echo $dashboardData['onboarding_progress']; ?>%</span></div>
                  <div class="progress mb-4" style="height:10px;"><div class="progress-bar bg-success" style="width:<?php echo $dashboardData['onboarding_progress']; ?>%"></div></div>
                  <div class="row g-3">
                    <?php foreach ($onboardingChecklist as $i => [$title, $desc, $status]): ?>
                      <div class="col-md-6">
                        <i class="bi <?php echo $status === 'Completed' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-circle-fill text-warning'; ?> me-2"></i>
                        <?php echo htmlspecialchars($title); ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="mt-3"><a href="#" onclick="showSection('section-onboarding'); return false;" class="text-primary-custom fw-semibold">View Full Checklist <i class="bi bi-arrow-right"></i></a></div>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-4 mb-4">
            <div class="col-12">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Quick Actions</h5></div>
                <div class="card-body d-flex flex-wrap gap-2">
                  <button type="button" class="btn btn-primary-custom" onclick="showSection('section-appointments'); return false;"><i class="bi bi-calendar-plus me-1"></i> Book Appointment</button>
                  <button type="button" class="btn btn-primary-custom" onclick="showSection('section-mood'); return false;"><i class="bi bi-heart me-1"></i> Log Mood</button>
                  <button type="button" class="btn btn-primary-custom" onclick="showSection('section-therapist'); return false;"><i class="bi bi-person me-1"></i> View My Therapist</button>
                  <button type="button" class="btn btn-danger" onclick="showSection('section-emergency'); return false;">🚨 Get Help Now</button>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-12">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Recent Activity</h5></div>
                <div class="card-body">
                  <ul class="list-group list-group-flush">
                    <?php if (empty($recentActivity)): ?>
                      <li class="list-group-item text-secondary-custom">No recent activity yet.</li>
                    <?php else: foreach ($recentActivity as $act): ?>
                      <?php
                        $act = is_array($act) ? $act : [];
                        $actType = isset($act['type']) ? $act['type'] : '';
                        $actIcon = isset($act['icon'])
                          ? $act['icon']
                          : ($actType === 'appointment' ? 'calendar-event' : ($actType === 'mood' ? 'emoji-smile' : 'clock-history'));
                        $actText = isset($act['text']) ? $act['text'] : (isset($act['description']) ? $act['description'] : '');
                        $actDate = isset($act['date']) ? $act['date'] : '';
                      ?>
                      <li class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-<?= htmlspecialchars($actIcon) ?> text-primary-custom me-2"></i><?= htmlspecialchars($actText) ?></span>
                        <small class="text-secondary-custom"><?= htmlspecialchars($actDate) ?></small>
                      </li>
                    <?php endforeach; endif; ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ONBOARDING -->
        <div id="section-onboarding" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Onboarding Checklist</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          <div class="row"><div class="col-12">
            <div class="card card-custom">
              <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Your Onboarding Checklist</h5></div>
              <div class="card-body">
                <?php
                  $completedCount  = count(array_filter($onboardingChecklist, fn($i) => $i[2] === 'Completed'));
                  $progressPct     = (int)$dashboardData['onboarding_progress'];
                  $statusColors    = ['Completed'=>'success','Pending'=>'warning','Locked'=>'secondary'];
                ?>
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary-custom">Overall Progress</span><span class="fw-bold text-primary-custom"><?= $progressPct ?>%</span></div>
                <div class="progress mb-4" style="height:10px;"><div class="progress-bar bg-success" style="width:<?= $progressPct ?>%"></div></div>
                <ul class="list-group list-group-flush">
                  <?php foreach ($onboardingChecklist as $i => [$title, $desc, $status]):
                    $color = $statusColors[$status] ?? 'secondary';
                  ?>
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center">
                      <div class="bg-light-green text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width:40px;height:40px;"><?= $i + 1 ?></div>
                      <div><div class="fw-bold"><?= htmlspecialchars($title) ?></div><small class="text-secondary-custom"><?= htmlspecialchars($desc) ?></small></div>
                    </div>
                    <?php if ($status === 'Pending' && $title === 'Submit Intake Form'): ?>
                      <div><span class="badge bg-warning text-dark me-2">Pending</span><button type="button" class="btn btn-sm btn-primary-custom" onclick="window.open('intake-form.php','_blank')">Start Now</button></div>
                    <?php elseif ($status === 'Pending' && $title === 'Add Payment Method'): ?>
                      <div><span class="badge bg-warning text-dark me-2">Pending</span><button type="button" class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#paymentModal">Add Now</button></div>
                    <?php elseif ($status === 'Pending' && $title === 'Sign Consent'): ?>
                      <div><span class="badge bg-warning text-dark me-2">Pending</span><button type="button" class="btn btn-sm btn-primary-custom" onclick="showSection('section-sessions')">Sign Now</button></div>
                    <?php elseif ($status === 'Pending' && $title === 'Get Matched with Therapist'): ?>
                      <div><span class="badge bg-warning text-dark me-2">Pending</span><button type="button" class="btn btn-sm btn-primary-custom" onclick="showSection('section-therapist')">Browse Therapists</button></div>
                    <?php elseif ($status === 'Pending' && $title === 'Schedule First Appointment'): ?>
                      <div><span class="badge bg-warning text-dark me-2">Pending</span><button type="button" class="btn btn-sm btn-primary-custom" onclick="showSection('section-appointments')">Schedule Now</button></div>
                    <?php else: ?>
                      <span class="badge bg-<?= $color ?>"><?= htmlspecialchars($status) ?></span>
                    <?php endif; ?>
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div></div>
        </div>

        <!-- THERAPIST -->
        <div id="section-therapist" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">My Therapist</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          <div class="row g-4">
            <div class="col-lg-7">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">My Therapist</h5></div>
                <div class="card-body text-center">
                  <div class="bg-light-green rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:100px;height:100px;"><i class="bi bi-person-fill text-primary-custom" style="font-size:3rem;"></i></div>
                  <?php if ($myTherapist): ?>
                    <h4 class="fw-bold text-primary-custom mb-1">Dr. <?= htmlspecialchars($myTherapist['first_name'] . ' ' . $myTherapist['last_name']) ?></h4>
                    <p class="text-secondary-custom mb-3"><?= htmlspecialchars($myTherapist['specialization'] ?? 'General Therapy') ?></p>
                    <ul class="list-unstyled text-start mx-auto" style="max-width:380px;">
                      <li class="mb-2"><i class="bi bi-translate text-primary-custom me-2"></i> Languages: <?= htmlspecialchars($myTherapist['languages'] ?? 'N/A') ?></li>
                      <li class="mb-2"><i class="bi bi-star-fill text-accent me-2"></i> Rating: <?= number_format((float)($myTherapist['rating'] ?? 0), 1) ?> / 5</li>
                      <li class="mb-2"><i class="bi bi-briefcase text-primary-custom me-2"></i> Experience: <?= (int)($myTherapist['experience_years'] ?? 0) ?> yrs</li>
                      <li class="mb-2"><i class="bi bi-shield-check text-success me-2"></i> <?= $myTherapist['is_verified'] ? 'Active & Verified' : 'Pending Verification' ?></li>
                    </ul>
                  <?php else: ?>
                    <h5 class="text-secondary-custom mt-3">No therapist assigned yet.</h5>
                    <p class="text-muted small">Complete your intake form to receive a therapist match.</p>
                  <?php endif; ?>
                  <?php if ($myTherapist): ?>
                  <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-primary-custom w-50" data-bs-toggle="modal" data-bs-target="#messageTherapistModal"><i class="bi bi-envelope me-1"></i> Send Message</button>
                    <button type="button" class="btn btn-outline-secondary w-50" data-bs-toggle="modal" data-bs-target="#rematchModal"><i class="bi bi-arrow-repeat me-1"></i> Request Re-Match</button>
                  </div>
                  <p class="text-secondary-custom small mt-3 mb-0"><i class="bi bi-shield-lock me-1"></i> Direct contact details are never shared. All communication is platform-only.</p>
                  <?php else: ?>
                  <div class="mt-3">
                    <?php if (!empty($intakeStatus)): ?>
                      <button type="button" class="btn btn-primary-custom" onclick="requestTherapistMatch()"><i class="bi bi-person-check me-1"></i> Find Therapist Match Now</button>
                    <?php else: ?>
                      <button type="button" class="btn btn-primary-custom" onclick="window.open('intake-form.php','_blank')"><i class="bi bi-clipboard-check me-1"></i> Complete Intake Form to Get Matched</button>
                    <?php endif; ?>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">My Matching Preferences</h5></div>
                <div class="card-body">
                  <?php $profile = $controller->getProfileData(); ?>
                  <div class="mb-3"><label class="form-label">Preferred Language</label>
                    <select name="prefLang" class="form-select">
                      <?php foreach (['Arabic','English','French','Other'] as $opt): ?>
                        <option <?= ($profile['pref_language'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                      <?php endforeach; ?>
                    </select></div>
                  <div class="mb-3"><label class="form-label">Preferred Therapist Gender</label>
                    <select name="prefGender" class="form-select">
                      <?php foreach (['No Preference','Male','Female'] as $opt): ?>
                        <option <?= ($profile['pref_therapist_gender'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                      <?php endforeach; ?>
                    </select></div>
                  <div class="mb-3"><label class="form-label">Cultural / Religious Background</label>
                    <select name="prefCulture" class="form-select">
                      <?php foreach (['No Preference','Muslim','Christian','Other'] as $opt): ?>
                        <option <?= ($profile['pref_cultural_background'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                      <?php endforeach; ?>
                    </select></div>
                  <div class="mb-3"><label class="form-label">Specialization Needed</label>
                    <select name="prefSpecialization" class="form-select">
                      <?php foreach (['CBT','Anxiety','Depression','Trauma','other'] as $opt): ?>
                        <option <?= ($profile['pref_specialization'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                      <?php endforeach; ?>
                    </select></div>
                  <button type="button" class="btn btn-primary-custom w-100" onclick="savePreferences()">Save Preferences</button>
                  <p class="text-secondary-custom small mt-3 mb-0">These preferences are used when matching or re-matching you with a therapist.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- REVIEWS -->
        <div id="section-reviews" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Reviews & Ratings</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          <div class="row g-4">
            <div class="col-lg-5">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Write a Review</h5></div>
                <div class="card-body">
                  <?php if ($myTherapist): ?>
                    <div class="mb-3">
                      <label class="form-label">Therapist</label>
                      <input type="text" class="form-control" value="Dr. <?= htmlspecialchars($myTherapist['first_name'] . ' ' . $myTherapist['last_name']) ?>" readonly>
                      <input type="hidden" id="reviewTherapistId" value="<?= $myTherapist['user_id'] ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Rating</label>
                      <div class="d-flex gap-1" id="ratingStars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                          <button type="button" class="btn btn-outline-warning rating-star" data-rating="<?= $i ?>" onclick="setRating(<?= $i ?>)">
                            <i class="bi bi-star-fill"></i>
                          </button>
                        <?php endfor; ?>
                      </div>
                      <small class="text-muted">Click to rate from 1 to 5 stars</small>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Your Review</label>
                      <textarea id="reviewText" class="form-control" rows="4" placeholder="Share your experience with this therapist..." maxlength="1000"></textarea>
                      <small class="text-muted">Maximum 1000 characters</small>
                    </div>
                    <button type="button" class="btn btn-primary-custom w-100" onclick="submitReview()">
                      <i class="bi bi-star me-1"></i> Submit Review
                    </button>
                    <div id="reviewMessage" class="mt-3"></div>
                  <?php else: ?>
                    <div class="text-center py-4">
                      <i class="bi bi-person-x text-muted" style="font-size:3rem;"></i>
                      <p class="text-muted mt-3">You need to be matched with a therapist to write a review.</p>
                      <button type="button" class="btn btn-primary-custom" onclick="showSection('section-therapist')">Find a Therapist</button>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                  <h5 class="fw-bold text-primary-custom mb-0">My Reviews</h5>
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshMyReviews()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                  </button>
                </div>
                <div class="card-body">
                  <div id="myReviewsList">
                    <div class="text-center py-4">
                      <div class="spinner-border text-primary-custom" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- APPOINTMENTS -->
        <div id="section-appointments" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Appointments</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          <div class="row"><div class="col-12">
            <div class="card card-custom">
              <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-primary-custom mb-0">My Appointments</h5>
                <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#bookAppointmentModal"><i class="bi bi-plus-circle me-1"></i> Book New Appointment</button>
              </div>
              <div class="card-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                  <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabUpcoming" type="button" role="tab">Upcoming</button></li>
                  <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPast" type="button" role="tab">Past</button></li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane fade show active" id="tabUpcoming" role="tabpanel">
                    <table class="table table-hover table-custom">
                      <thead><tr><th>Date &amp; Time</th><th>Therapist</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
                      <tbody>
                        <?php if (empty($upcomingAppts)): ?>
                          <tr><td colspan="5" class="text-center text-muted py-3">No upcoming appointments.</td></tr>
                        <?php else: foreach ($upcomingAppts as $a):
                          $apptColors = ['Scheduled'=>'primary','Confirmed'=>'success','Cancelled'=>'danger'];
                          $apptColor  = $apptColors[$a['status']] ?? 'secondary';
                        ?>
                          <tr data-appt-id="<?= $a['appointment_id'] ?>">
                            <td><?= date('M j, Y · g:i A', strtotime($a['appointment_date'])) ?></td>
                            <td>Dr. <?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
                            <td><?= htmlspecialchars($a['session_type']) ?></td>
                            <td><span class="badge bg-<?= $apptColor ?>"><?= htmlspecialchars($a['status']) ?></span></td>
                            <td>
                              <button type="button" class="btn btn-sm btn-outline-secondary" disabled>Join Session</button>
                              <button type="button" class="btn btn-sm btn-outline-danger" onclick="cancelAppointment(<?= $a['appointment_id'] ?>)">Cancel</button>
                            </td>
                          </tr>
                        <?php endforeach; endif; ?>
                      </tbody>
                    </table>
                  </div>
                  <div class="tab-pane fade" id="tabPast" role="tabpanel">
                    <table class="table table-hover table-custom">
                      <thead><tr><th>Date &amp; Time</th><th>Therapist</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
                      <tbody>
                        <?php if (empty($pastAppts)): ?>
                          <tr><td colspan="5" class="text-center text-muted py-3">No past appointments.</td></tr>
                        <?php else: foreach ($pastAppts as $a):
                          $pastColors = ['Completed'=>'success','No-Show'=>'danger','Cancelled'=>'secondary'];
                          $pastColor  = $pastColors[$a['status']] ?? 'secondary';
                        ?>
                          <tr>
                            <td><?= date('M j, Y · g:i A', strtotime($a['appointment_date'])) ?></td>
                            <td>Dr. <?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
                            <td><?= htmlspecialchars($a['session_type']) ?></td>
                            <td><span class="badge bg-<?= $pastColor ?>"><?= htmlspecialchars($a['status']) ?></span></td>
                            <td><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sessionSummaryModal">View Summary</button></td>
                          </tr>
                        <?php endforeach; endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div></div>
        </div>

        <!-- SESSIONS -->
        <div id="section-sessions" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Sessions</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          <div class="row"><div class="col-12">
            <div class="card card-custom">
              <div class="card-header bg-white border-0 pt-4 pb-0 d-flex align-items-center">
                <h5 class="fw-bold text-primary-custom mb-0">Session Room</h5>
                <span id="sessionBadge" class="badge bg-secondary ms-auto">Scheduled</span>
              </div>
              <div class="card-body">
                <?php
                  $nextSession = !empty($upcomingAppts) ? $upcomingAppts[0] : null;
                  $nextTherapistName = $nextSession ? 'Dr. ' . htmlspecialchars($nextSession['first_name'] . ' ' . $nextSession['last_name']) : 'No upcoming session';
                  $nextSessionTime  = $nextSession ? date('M j, Y \a\t g:i A', strtotime($nextSession['appointment_date'])) : '';
                ?>
                <div class="alert alert-<?= $nextSession ? 'info' : 'secondary' ?>">
                  <i class="bi bi-info-circle me-2"></i>
                  <?php if ($nextSession): ?>
                    <strong>Next Session:</strong> <?= $nextSessionTime ?> with <?= $nextTherapistName ?>
                  <?php else: ?>
                    <strong>No upcoming sessions.</strong> Book an appointment to get started.
                  <?php endif; ?>
                </div>

                <div id="statePreSession">
                  <div class="text-center py-5">
                    <i class="bi bi-camera-video text-primary-custom" style="font-size:4rem;"></i>
                    <p class="text-secondary-custom mt-3 mb-4">Your session hasn't started yet. You can check in up to 5 minutes before.</p>
                    <button
                      id="btnPatientCheckIn"
                      type="button"
                      class="btn btn-primary-custom btn-lg"
                      data-session-id="<?= (int)($nextSession['session_id'] ?? 0) ?>"
                      onclick="patientCheckIn()"
                      <?= empty($nextSession['session_id']) ? 'disabled' : '' ?>
                    ><i class="bi bi-box-arrow-in-right me-1"></i> Check In &amp; Enter Waiting Room</button>
                    <?php if (!empty($nextSession['meeting_link'])): ?>
                      <div class="mt-3">
                        <a class="btn btn-outline-primary" href="<?= htmlspecialchars($nextSession['meeting_link']) ?>" target="_blank" rel="noopener">Open Meeting Link</a>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <div id="stateWaitingRoom" style="display:none;">
                  <div class="alert alert-warning"><i class="bi bi-hourglass-split me-2"></i> You are in the virtual waiting room. Your therapist will admit you shortly.</div>
                  <div class="text-center py-5">
                    <div class="spinner-border text-primary-custom mb-3" role="status" style="width:3rem;height:3rem;"></div>
                    <h5>Waiting for <?= $nextTherapistName ?? 'your therapist' ?> to admit you...</h5>
                    <button type="button" class="btn btn-outline-danger mt-3" onclick="leaveWaitingRoom()">Leave Waiting Room</button>
                  </div>
                </div>

                <div id="stateLiveSession" style="display:none;">
                  <div class="text-center py-5">
                    <div class="bg-light-green rounded p-5 mb-3"><i class="bi bi-camera-video-fill text-primary-custom" style="font-size:5rem;"></i></div>
                    <h2 id="patientSessionTimer" class="fw-bold text-primary-custom">00:00</h2>
                    <button type="button" class="btn btn-danger mt-3" onclick="leaveSession()"><i class="bi bi-telephone-x me-1"></i> Leave Session</button>
                  </div>
                </div>
              </div>
            </div>
          </div></div>
        </div>

        <!-- MOOD -->
        <div id="section-mood" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Mood Tracker</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          <div class="row g-4">
            <div class="col-lg-5">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Log Today's Mood</h5></div>
                <div class="card-body">
                  <div class="d-flex justify-content-between mb-3">
                    <button type="button" class="btn btn-light mood-btn" data-mood="1" data-label="Very Low"><span style="font-size:1.5rem;">😢</span></button>
                    <button type="button" class="btn btn-light mood-btn" data-mood="2" data-label="Low"><span style="font-size:1.5rem;">😟</span></button>
                    <button type="button" class="btn btn-light mood-btn" data-mood="3" data-label="Neutral"><span style="font-size:1.5rem;">😐</span></button>
                    <button type="button" class="btn btn-light mood-btn" data-mood="4" data-label="Good"><span style="font-size:1.5rem;">😊</span></button>
                    <button type="button" class="btn btn-light mood-btn" data-mood="5" data-label="Excellent"><span style="font-size:1.5rem;">😄</span></button>
                  </div>
                  <p class="text-center text-secondary-custom mb-3">Selected: <span id="moodLabel" class="fw-bold text-primary-custom">—</span></p>
                  <div class="mb-3"><label class="form-label">Notes (optional)</label><textarea id="moodNotes" class="form-control" rows="3" placeholder="How are you feeling today?" name="moodNotes"></textarea></div>
                  <button type="button" class="btn btn-primary-custom w-100" onclick="saveMoodEntry()">Save Mood Entry</button>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex align-items-center">
                  <h5 class="fw-bold text-primary-custom mb-0">Weekly Mood Trend</h5>
                  <select class="form-select form-select-sm ms-auto" style="width:auto;"><option>Last 7 days</option><option>Last 14 days</option><option>Last 30 days</option></select>
                </div>
                <div class="card-body">
                  <?php
                    $moodColorMap = [1=>'danger',2=>'warning',3=>'warning',4=>'success',5=>'success'];
                    $moodEmoji    = [1=>'😢',2=>'😟',3=>'😐',4=>'😊',5=>'😄'];
                    $moodAvg      = !empty($moodHistory) ? round(array_sum(array_column($moodHistory,'mood_score')) / count($moodHistory), 1) : null;
                  ?>
                  <table class="table table-custom">
                    <thead><tr><th>Day</th><th>Mood</th><th>Score</th><th>Trend</th></tr></thead>
                    <tbody>
                      <?php if (empty($moodHistory)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">No mood entries this week. Log your first mood!</td></tr>
                      <?php else: foreach ($moodHistory as $m):
                        $score    = (int)$m['mood_score'];
                        $barColor = $moodColorMap[$score] ?? 'secondary';
                        $barWidth = ($score / 5) * 100;
                        $emoji    = $moodEmoji[$score] ?? '😐';
                        $dayLabel = date('D, M j', strtotime($m['entry_date']));
                      ?>
                        <tr>
                          <td><?= $dayLabel ?></td>
                          <td><?= $emoji ?> <?= htmlspecialchars($m['mood_label'] ?? ucfirst($m['mood_score'])) ?></td>
                          <td><strong><?= $score ?></strong>/5</td>
                          <td>
                            <div class="progress" style="height:8px;">
                              <div class="progress-bar bg-<?= $barColor ?>" style="width:<?= $barWidth ?>%"></div>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; endif; ?>
                    </tbody>
                  </table>
                  <p class="text-center text-secondary-custom mb-0 mt-3">
                    Weekly average:
                    <span class="fw-bold text-primary-custom">
                      <?= $moodAvg !== null ? $moodAvg . ' / 5' : 'No data yet' ?>
                    </span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- GOALS -->
        <div id="section-goals" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Wellness Goals</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          <div class="row g-4">
            <div class="col-lg-5">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Add New Goal</h5></div>
                <div class="card-body">
                  <div class="mb-3"><label class="form-label">Goal Title</label><input id="goalTitle" type="text" class="form-control" placeholder="e.g. Daily walk" name="goalTitle"></div>
                  <div class="mb-3"><label class="form-label">Target (days per week)</label><input id="goalTargetDays" type="number" class="form-control" min="1" max="7" value="5" name="goalTargetDays"></div>
                  <div class="mb-3"><label class="form-label">Category</label><select id="goalCategory" class="form-select" name="goalCategory"><option value="Mindfulness">Mindfulness</option><option value="Exercise">Exercise</option><option value="Sleep">Sleep</option><option value="Journaling">Journaling</option><option value="Medication">Medication</option><option value="Other">Other</option></select></div>
                  <button type="button" class="btn btn-primary-custom w-100" onclick="saveGoal()">Save Goal</button>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">My Goals</h5></div>
                <div class="card-body">
                  <ul class="list-group list-group-flush">
                    <?php if (empty($goals)): ?>
                      <li class="list-group-item text-center text-muted py-4">No goals yet. Add your first goal!</li>
                    <?php else:
                      $goalIcons    = ['Mindfulness'=>'lungs','Exercise'=>'bicycle','Sleep'=>'moon','Nutrition'=>'apple','Other'=>'bullseye'];
                      $goalColors   = ['Achieved'=>'success','In-Progress'=>'warning','Failed'=>'danger'];
                    foreach ($goals as $g):
                      $pct   = min(100, (int)$g['progress']);
                      $gColor = $pct >= 100 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                      $icon   = $goalIcons[$g['category']] ?? 'bullseye';
                    ?>
                      <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center">
                          <div class="bg-light-green text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;"><i class="bi bi-<?= $icon ?> fs-4"></i></div>
                          <div><div class="fw-bold"><?= htmlspecialchars($g['title']) ?></div><small class="text-secondary-custom"><?= htmlspecialchars($g['category']) ?></small></div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                          <span class="text-<?= $gColor ?> fw-bold"><?= $pct ?>%</span>
                          <div class="progress" style="width:120px;height:8px;"><div class="progress-bar bg-<?= $gColor ?>" style="width:<?= $pct ?>%"></div></div>
                          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openGoalModal(<?= $g['goal_id'] ?>, <?= $pct ?>)">Update</button>
                        </div>
                      </li>
                    <?php endforeach; endif; ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- JOURNAL -->
        <div id="section-journal" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">My Journal</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          <div class="row g-4">
            <div class="col-lg-5">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">New Journal Entry</h5></div>
                <div class="card-body">
                  <div class="mb-3"><label class="form-label">Title</label><input id="journalTitle" type="text" class="form-control" placeholder="Entry title" name="journalTitle"></div>
                  <div class="mb-3"><label class="form-label">Content</label><textarea id="journalContent" class="form-control" rows="6" placeholder="Write your thoughts..." name="journalContent"></textarea></div>
                  <div class="mb-3">
                    <label class="form-label d-block">Privacy</label>
                    <div class="btn-group" role="group">
                      <input type="radio" class="btn-check" name="privacy" id="privPrivate" value="Private" checked>
                      <label class="btn btn-outline-secondary" for="privPrivate"><i class="bi bi-lock me-1"></i> Private</label>
                      <input type="radio" class="btn-check" name="privacy" id="privShared" value="Shared">
                      <label class="btn btn-outline-secondary" for="privShared"><i class="bi bi-share me-1"></i> Share with Therapist</label>
                    </div>
                  </div>
                  <button type="button" class="btn btn-primary-custom w-100" onclick="saveJournalEntry()">Save Entry</button>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">My Entries</h5></div>
                <div class="card-body">
                  <?php if (empty($journalEntries)): ?>
                    <p class="text-center text-muted py-4">No journal entries yet.</p>
                  <?php else: foreach ($journalEntries as $j):
                    $privColor = $j['privacy_level'] === 'Private' ? 'secondary' : 'primary';
                    $privLabel = $j['privacy_level'] === 'Private' ? 'Private' : 'Shared';
                    $snippet   = htmlspecialchars(mb_substr($j['content'] ?? '', 0, 80)) . '...';
                  ?>
                    <div class="card card-custom mb-3 p-3">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0 text-primary-custom"><?= htmlspecialchars($j['title']) ?></h6>
                        <div>
                          <small class="text-secondary-custom me-2"><?= date('M j', strtotime($j['created_at'])) ?></small>
                          <span class="badge bg-<?= $privColor ?> privacy-badge"><?= $privLabel ?></span>
                        </div>
                      </div>
                      <p class="text-secondary-custom small mb-2"><?= $snippet ?></p>
                      <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewJournalModal">Read</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePrivacy(this, <?= $j['entry_id'] ?>)">Change Privacy</button>
                      </div>
                    </div>
                  <?php endforeach; endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RESOURCES -->
        <div id="section-resources" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Wellness Resources</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          
          <!-- Resources Based on Your Goals -->
          <?php if (!empty($goalResources)): ?>
            <div class="row g-4 mb-4">
              <?php foreach ($goalResources as $category => $categoryResources): ?>
                <div class="col-12">
                  <div class="card card-custom">
                    <div class="card-header bg-white border-0 pt-4 pb-0 d-flex align-items-center">
                      <h5 class="fw-bold text-primary-custom mb-0">
                        <i class="bi bi-bullseye me-2"></i><?= htmlspecialchars($category) ?> Resources
                      </h5>
                      <span class="badge bg-light text-secondary-custom ms-auto">Based on your goals</span>
                    </div>
                    <div class="card-body">
                      <div class="row g-3">
                        <?php 
                          $resIcons = ['Mindfulness'=>'lungs','Therapy'=>'book','Audio'=>'music-note-beamed','Exercise'=>'bicycle','Sleep'=>'moon','Nutrition'=>'heart-pulse','Stress'=>'shield-check'];
                          $resActions = ['Timer'=>'startMindfulnessTimer(5)','PDF'=>"showToast('Opening resource...','success')",'Audio'=>"showToast('Playing...','success')",'Video'=>"showToast('Loading video...','success')"];
                          foreach ($categoryResources as $res):
                            $icon   = $resIcons[$res['category']] ?? 'star';
                            $action = $resActions[$res['resource_type']] ?? "showToast('Opening...','success')";
                            $btnLabel = match($res['resource_type']) { 'Timer'=>'Start Timer', 'PDF'=>'Open', 'Audio'=>'Play', 'Video'=>'Watch', default=>'Open' };
                        ?>
                          <div class="col-md-6 col-lg-4">
                            <div class="card card-custom h-100 p-3">
                              <div class="d-flex align-items-start">
                                <div class="bg-light-green rounded-circle d-flex align-items-center justify-content-center me-3" style="width:50px;height:50px;flex-shrink:0;">
                                  <i class="bi bi-<?= $icon ?> text-primary-custom fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                  <h6 class="fw-bold text-primary-custom mb-1"><?= htmlspecialchars($res['title']) ?></h6>
                                  <span class="badge mb-2" style="background-color:var(--primary-green);"><?= htmlspecialchars($res['category']) ?></span>
                                  <p class="small text-secondary-custom mb-3"><?= htmlspecialchars(mb_substr($res['description'] ?? '', 0, 80)) ?></p>
                                  <button type="button" class="btn btn-sm btn-primary-custom" onclick="<?= $action ?>; useResource(<?= $res['resource_id'] ?>, 5)"><?= $btnLabel ?></button>
                                </div>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- All Resources Section -->
          <div class="row g-4 mb-4"><div class="col-12">
            <div class="card card-custom">
              <div class="card-header bg-white border-0 pt-4 pb-0 d-flex align-items-center">
                <h5 class="fw-bold text-primary-custom mb-0">All Resources</h5>
                <span class="badge bg-light text-secondary-custom ms-auto">Browse complete library</span>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <?php if (empty($resources)): ?>
                    <div class="col-12"><p class="text-center text-muted py-4">No resources available.</p></div>
                  <?php else:
                    $resIcons = ['Mindfulness'=>'lungs','Therapy'=>'book','Audio'=>'music-note-beamed','Exercise'=>'bicycle','Sleep'=>'moon','Nutrition'=>'heart-pulse','Stress'=>'shield-check'];
                    $resActions = ['Timer'=>'startMindfulnessTimer(5)','PDF'=>"showToast('Opening resource...','success')",'Audio'=>"showToast('Playing...','success')",'Video'=>"showToast('Loading video...','success')"];
                    foreach ($resources as $res):
                      $icon   = $resIcons[$res['category']] ?? 'star';
                      $action = $resActions[$res['resource_type']] ?? "showToast('Opening...','success')";
                      $btnLabel = match($res['resource_type']) { 'Timer'=>'Start Timer', 'PDF'=>'Open', 'Audio'=>'Play', 'Video'=>'Watch', default=>'Open' };
                  ?>
                    <div class="col-md-6 col-lg-3"><div class="card card-custom h-100 p-3 text-center">
                      <div class="bg-light-green rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px;"><i class="bi bi-<?= $icon ?> text-primary-custom fs-3"></i></div>
                      <h6 class="fw-bold text-primary-custom"><?= htmlspecialchars($res['title']) ?></h6>
                      <span class="badge mb-2" style="background-color:var(--primary-green);"><?= htmlspecialchars($res['category']) ?></span>
                      <p class="small text-secondary-custom"><?= htmlspecialchars(mb_substr($res['description'] ?? '', 0, 60)) ?></p>
                      <button type="button" class="btn btn-sm btn-primary-custom" onclick="<?= $action ?>; useResource(<?= $res['resource_id'] ?>, 5)"><?= $btnLabel ?></button>
                    </div></div>
                  <?php endforeach; endif; ?>
                </div>
              </div>
            </div>
          </div></div>
          <div class="row g-4"><div class="col-lg-6">
            <div class="card card-custom">
              <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Mindfulness Session Timer</h5></div>
              <div class="card-body">
                <div class="d-flex gap-2 mb-4 justify-content-center">
                  <button type="button" class="btn btn-outline-secondary" onclick="startMindfulnessTimer(5)">5 min</button>
                  <button type="button" class="btn btn-outline-secondary" onclick="startMindfulnessTimer(10)">10 min</button>
                  <button type="button" class="btn btn-outline-secondary" onclick="startMindfulnessTimer(15)">15 min</button>
                </div>
                <div class="text-center py-4">
                  <h1 id="mindfulnessDisplay" class="display-1 fw-bold text-primary-custom mb-3">05:00</h1>
                  <p id="mindfulnessStatus" class="text-secondary-custom">Select a duration and press Start</p>
                </div>
                <div class="d-flex justify-content-center gap-3">
                  <button type="button" id="btnStartTimer" class="btn btn-primary-custom" onclick="controlTimer('start')"><i class="bi bi-play-fill"></i> Start</button>
                  <button type="button" id="btnPauseTimer" class="btn btn-outline-secondary" onclick="controlTimer('pause')" disabled><i class="bi bi-pause-fill"></i> Pause</button>
                  <button type="button" id="btnStopTimer" class="btn btn-outline-danger" onclick="controlTimer('stop')" disabled><i class="bi bi-stop-fill"></i> Stop</button>
                </div>
              </div>
            </div>
          </div></div>
        </div>

        <!-- PAYMENTS -->
        <div id="section-payments" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Payments &amp; Insurance</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          <div class="card card-custom mb-4">
            <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Insurance Information</h5></div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <?php if ($insurance): ?>
                    <div class="mb-2"><small class="text-secondary-custom">Provider</small><div class="fw-bold"><?= htmlspecialchars($insurance['provider_name']) ?></div></div>
                    <div class="mb-2"><small class="text-secondary-custom">Policy Number</small><div class="fw-bold"><?= htmlspecialchars($insurance['policy_number']) ?></div></div>
                    <div class="mb-2"><small class="text-secondary-custom">Eligibility Status</small><div><span class="badge bg-success"><?= htmlspecialchars($insurance['eligibility_status']) ?></span></div></div>
                  <?php else: ?>
                    <p class="text-muted">No insurance on file. <a href="#" data-bs-toggle="modal" data-bs-target="#updateInsuranceModal">Add now</a>.</p>
                  <?php endif; ?>
                </div>
                <div class="col-md-6">
                  <?php if ($insurance): ?>
                    <div class="mb-2"><small class="text-secondary-custom">Plan Type</small><div class="fw-bold"><?= htmlspecialchars($insurance['plan_type']) ?></div></div>
                    <div class="mb-2"><small class="text-secondary-custom">Coverage</small><div class="fw-bold"><?= htmlspecialchars($insurance['coverage']) ?></div></div>
                    <div class="mb-2"><small class="text-secondary-custom">Expiry</small><div class="fw-bold"><?= htmlspecialchars($insurance['expiry_date']) ?></div></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="card-footer bg-white border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#updateInsuranceModal">Update Insurance Details</button></div>
          </div>
          <div class="card card-custom mb-4">
            <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Invoices &amp; Payments</h5></div>
            <div class="card-body">
              <table class="table table-hover table-custom">
                <thead><tr><th>Date</th><th>Session</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                  <?php if (empty($payments)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No payment records found.</td></tr>
                  <?php else: foreach ($payments as $p):
                    $payColors = ['Paid'=>'success','Unpaid'=>'danger','Refunded'=>'warning'];
                    $payColor  = $payColors[$p['status']] ?? 'secondary';
                  ?>
                    <tr>
                      <td><?= date('M j, Y', strtotime($p['payment_date'])) ?></td>
                      <td>
                        <?php if ($p['therapist_first']): ?>
                          Dr. <?= htmlspecialchars($p['therapist_first'] . ' ' . $p['therapist_last']) ?>
                          – <?= htmlspecialchars($p['session_type'] ?? '') ?>
                        <?php else: ?>
                          <span class="text-muted">Direct Payment</span>
                          <?php if ($p['invoice_number']): ?><small class="text-secondary-custom d-block"><?= htmlspecialchars($p['invoice_number']) ?></small><?php endif; ?>
                        <?php endif; ?>
                      </td>
                      <td><?= number_format((float)$p['amount'], 2) ?> EGP</td>
                      <td><span class="badge bg-<?= $payColor ?>"><?= htmlspecialchars($p['status']) ?></span></td>
                      <td>
                        <?php if ($p['status'] === 'Unpaid'): ?>
                          <button type="button" class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#paymentModal">Pay Now</button>
                        <?php else: ?>
                          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showToast('Invoice #<?= htmlspecialchars($p['invoice_number'] ?? '') ?> downloaded.','success')">Download</button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card card-custom">
            <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Submit a Dispute</h5></div>
            <div class="card-body">
              <div class="mb-3"><label class="form-label">Related Session</label>
                <select id="disputeAppt" class="form-select">
                  <option value="">-- Select session --</option>
                  <?php foreach ($payments as $p): ?>
                  <option value="<?= $p['appointment_id'] ?>"><?= date('M j, Y', strtotime($p['payment_date'])) ?> – Dr. <?= htmlspecialchars($p['therapist_first'] . ' ' . $p['therapist_last']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3"><label class="form-label">Dispute Reason</label><select id="disputeReason" class="form-select"><option value="incorrect charge">Incorrect charge</option><option value="session not received">Session not received</option><option value="Technical issue">Technical issue</option><option value="Other">Other</option></select></div>
              <div class="mb-3"><label class="form-label">Description</label><textarea id="disputeDesc" class="form-control" rows="3" placeholder="Provide details..."></textarea></div>
              <button type="button" class="btn btn-primary-custom" onclick="submitDispute()">Submit Dispute</button>
              <?php
                // Show any open disputes from DB
                $conn = SingletonDatabase::getInstance()->getConnection();
                $dStmt = $conn->prepare(
                    "SELECT d.dispute_code, d.status, d.created_at,
                            a.appointment_date, u.first_name, u.last_name
                     FROM disputes d
                     JOIN appointments a ON a.appointment_id = d.appointment_id
                     JOIN users u ON u.user_id = a.therapist_id
                     WHERE d.raised_by_id = ? AND d.status = 'Under Review'
                     ORDER BY d.created_at DESC LIMIT 3"
                );
                $dStmt->execute([$patientId]);
                $openDisputes = $dStmt->fetchAll(PDO::FETCH_ASSOC);
              ?>
              <?php foreach ($openDisputes as $d): ?>
                <div class="alert alert-warning mt-3 mb-0">
                  <i class="bi bi-info-circle me-2"></i>
                  <strong>Open Dispute #<?= htmlspecialchars($d['dispute_code']) ?></strong>
                  &middot; <?= date('M j', strtotime($d['appointment_date'])) ?> session with Dr. <?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name']) ?>
                  &middot; Status: <?= htmlspecialchars($d['status']) ?>
                </div>
              <?php endforeach; ?>
              <?php if (empty($openDisputes)): ?>
                <div class="alert alert-success mt-3 mb-0"><i class="bi bi-check-circle me-2"></i>No open disputes.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- CONSENTS -->
        <div id="section-consents" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Legal Consents</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          <div class="row"><div class="col-12">
            <div class="card card-custom">
              <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Legal Consents &amp; Agreements</h5></div>
              <div class="card-body">
                <ul class="list-group list-group-flush">
                  <?php if (empty($consents)): ?>
                    <li class="list-group-item text-center text-muted py-4">No consent records on file.</li>
                  <?php else: foreach ($consents as $c): ?>
                    <li class="list-group-item d-flex align-items-center justify-content-between py-3 border-bottom">
                      <div>
                        <div class="fw-bold"><?= htmlspecialchars($c['document_name']) ?> (v<?= htmlspecialchars($c['document_version']) ?>)</div>
                        <small class="text-secondary-custom"><?= $c['signed_date'] ? 'Signed ' . date('M j, Y', strtotime($c['signed_date'])) : 'Not yet signed' ?></small>
                      </div>
                      <div>
                        <?php if ($c['signed_date']): ?>
                          <span class="badge bg-success me-2">Signed</span>
                          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showToast('Document opened.','success')">View</button>
                        <?php else: ?>
                          <span class="badge bg-warning text-dark me-2">Unsigned</span>
                          <button type="button" class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#consentSignModal">Review &amp; Sign</button>
                        <?php endif; ?>
                      </div>
                    </li>
                  <?php endforeach; endif; ?>
                </ul>
              </div>
            </div>
          </div></div>
        </div>

        <!-- EMERGENCY -->
        <div id="section-emergency" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Crisis Support</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: <?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          </div>
          <div class="row"><div class="col-12">
            <div class="p-5 mb-4 rounded text-white text-center" style="background: linear-gradient(135deg, #F4B41A 0%, #dc3545 100%);">
              <i class="bi bi-heart-fill mb-3" style="font-size:3rem;"></i>
              <h2 class="fw-bold mb-2">You Are Not Alone</h2>
              <p class="lead mb-2">If you are in crisis, please reach out. Help is available 24/7, free and confidential.</p>
              <p class="mb-0"><i class="bi bi-shield-check me-1"></i> Your safety matters. Reaching out is a sign of strength.</p>
            </div>
            <div class="card card-custom">
              <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Find Local Resources</h5></div>
              <div class="card-body">
                <div class="mb-3"><label class="form-label">Select Your Region</label>
                  <select id="emergencyRegion" class="form-select" onchange="loadEmergencyResources()" name="emergencyRegion">
                    <option value="">-- Select Region --</option>
                    <option value="eg">🇪🇬 Egypt</option>
                    <option value="us">🇺🇸 United States</option>
                    <option value="uk">🇬🇧 United Kingdom</option>
                    <option value="intl">🌍 International</option>
                  </select>
                </div>
                <div id="emergencyResourcesList"></div>
              </div>
            </div>
          </div></div>
        </div>

      </main>
    </div>
  </div>

  <!-- MODALS -->
  <div class="modal fade" id="messageTherapistModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Send Message to Dr. Hassan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><label class="form-label">Message</label><textarea class="form-control" rows="5" placeholder="Type your message..."></textarea></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" id="btnSendMessage" class="btn btn-primary-custom">Send Message</button></div>
  </div></div></div>

  <div class="modal fade" id="rematchModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Request Therapist Re-Match</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <p>Are you sure you want to request a new therapist? Your current match will be ended.</p>
      <label class="form-label">Reason for Re-Match</label>
      <select class="form-select"><option>Scheduling conflicts</option><option>Specialization mismatch</option><option>Personal preference</option><option>Other</option></select>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" id="btnConfirmRematch" class="btn btn-danger">Confirm Re-Match</button></div>
  </div></div></div>

  <div class="modal fade" id="bookAppointmentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Book New Appointment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3">
        <label class="form-label">Therapist</label>
        <select id="apptTherapist" class="form-select" name="apptTherapist">
          <?php foreach ($availTherapists as $t): ?>
            <option value="<?= (int)$t['user_id'] ?>"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name'] . ' — ' . ($t['specialization'] ?? '')) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3"><label class="form-label">Preferred Date</label><input id="apptDate" type="date" class="form-control" name="apptDate"></div>
      <div class="mb-3"><label class="form-label">Preferred Time Slot</label><select class="form-select"><option>9:00 AM</option><option>10:00 AM</option><option>11:00 AM</option><option>2:00 PM</option><option>3:00 PM</option><option>4:00 PM</option></select></div>
      <div class="mb-3"><label class="form-label">Session Type</label><select id="apptType" class="form-select"><option>Video Session</option><option>In-Person</option></select></div>
      <button type="button" class="btn btn-outline-secondary w-100 mb-2" onclick="checkAvailability()">Check Availability</button>
      <div id="availabilityResult" style="display:none;"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="button" id="btnConfirmBooking" class="btn btn-primary-custom" onclick="confirmBooking()">Confirm Booking</button></div>
  </div></div></div>

  <div class="modal fade" id="cancelAppointmentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Cancel Appointment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><p>Are you sure you want to cancel? Cancellations within 24 hours may incur a fee.</p></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Appointment</button><button type="button" id="btnConfirmCancel" class="btn btn-danger">Yes, Cancel</button></div>
  </div></div></div>

  <div class="modal fade" id="sessionSummaryModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Session Summary</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <p><strong>Date:</strong> April 28, 2026 | <strong>Duration:</strong> 50 minutes | <strong>Therapist:</strong> Dr. Hassan</p>
      <p><strong>Notes:</strong> Session focused on anxiety management. Discussed grounding techniques. Homework: 5-min daily breathing exercise.</p>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
  </div></div></div>

  <div class="modal fade" id="updateGoalModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Update Goal Progress</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <label class="form-label">Progress (%)</label>
      <input id="goalProgress" type="range" class="form-range" min="0" max="100" value="80" name="goalProgress">
      <p class="text-center">Current: <span id="progressVal" class="fw-bold">80</span>%</p>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" id="btnSaveGoalProgress" class="btn btn-primary-custom">Save Progress</button></div>
  </div></div></div>

  <div class="modal fade" id="viewJournalModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Journal Entry</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <h4 class="fw-bold text-primary-custom">Feeling Better Today</h4>
      <p class="text-secondary-custom"><small>Apr 28, 2026 · Private</small></p>
      <p>Today I woke up feeling lighter than usual. The meditation exercises are starting to show results. Dr. Hassan's advice about morning routines has been helpful. I feel more grounded and ready to face the day.</p>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
  </div></div></div>

  <div class="modal fade" id="updateInsuranceModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Update Insurance Information</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label">Provider</label><input id="insProvider" type="text" class="form-control" value="<?= htmlspecialchars($insurance['provider_name'] ?? '') ?>"></div>
      <div class="mb-3"><label class="form-label">Policy Number</label><input id="insPolicyNum" type="text" class="form-control" value="<?= htmlspecialchars($insurance['policy_number'] ?? '') ?>"></div>
      <div class="mb-3"><label class="form-label">Plan Type</label><input id="insPlanType" type="text" class="form-control" value="<?= htmlspecialchars($insurance['plan_type'] ?? '') ?>"></div>
      <div class="mb-3"><label class="form-label">Coverage %</label><input id="insCoverage" type="text" class="form-control" value="<?= htmlspecialchars($insurance['coverage'] ?? '') ?>"></div>
      <div class="mb-3"><label class="form-label">Expiry Date</label><input id="insExpiry" type="text" class="form-control" value="<?= htmlspecialchars($insurance['expiry_date'] ?? '') ?>"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" id="btnSaveInsurance" class="btn btn-primary-custom">Save</button></div>
  </div></div></div>

  <div class="modal fade" id="consentSignModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Review &amp; Sign: Updated Terms of Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="border rounded p-3 mb-3" style="max-height:280px; overflow-y:auto;">
        <p>These updated Terms of Service govern your use of the MentalCare platform. By using this platform, you agree to maintain confidentiality of all interactions, use the platform solely for its intended therapeutic purpose, and comply with all applicable laws regarding mental health services. You acknowledge that session data is encrypted and stored securely, and that you have the right to request data deletion at any time. Violation of these terms may result in suspension of access.</p>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="consentCheck" name="consentCheck">
        <label class="form-check-label" for="consentCheck">I have read and agree to the Updated Terms of Service (v3.0)</label>
      </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" id="btnSignConsent" class="btn btn-primary-custom">Sign Document</button></div>
  </div></div></div>

  <div class="modal fade" id="paymentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>Simulate Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="alert alert-info py-2 small mb-3"><i class="bi bi-info-circle me-1"></i>This is a simulation — enter any values. Data will be stored securely.</div>
      <div class="mb-3"><label class="form-label">Card Number</label><input id="cardNumber" type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19"></div>
      <div class="row g-2 mb-3">
        <div class="col-6"><label class="form-label">Expiry Date</label><input id="cardExpiry" type="text" class="form-control" placeholder="MM/YY" maxlength="5"></div>
        <div class="col-6"><label class="form-label">CVV</label><input id="cardCvv" type="text" class="form-control" placeholder="123" maxlength="4"></div>
      </div>
      <div class="mb-3"><label class="form-label">Cardholder Name</label><input id="cardHolder" type="text" class="form-control" placeholder="Full name on card"></div>
      <div class="mb-3"><label class="form-label">Amount (EGP)</label><input id="cardAmount" type="number" class="form-control" placeholder="e.g. 350" min="1" step="0.01"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" id="btnSaveCard" class="btn btn-primary-custom" onclick="saveCard()"><i class="bi bi-lock-fill me-1"></i>Pay Now</button></div>
  </div></div></div>

  <!-- Toast container -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/js/main.js"></script>
  <script src="../../assets/js/patient.js"></script>
  <script src="../../assets/js/reviews.js"></script>
</body>

<!--
    Variabled
        goalTitle = goalTitle
        journalTitle = journalTitle
        privacy = privacy
        privacy = privacy
        apptDate = apptDate
        goalProgress = goalProgress
        consentCheck = consentCheck
        emergencyRegion = ( eg / us / uk / intl )
        moodNotes = moodNotes
        journalContent = journalContent
-->
</html>
