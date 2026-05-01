<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Patient Dashboard | MentalCare System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
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
          <li class="nav-item"><a class="nav-link" data-section="section-appointments" href="#" onclick="showSection('section-appointments'); return false;"><i class="bi bi-calendar-event me-2"></i>Appointments</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-sessions" href="#" onclick="showSection('section-sessions'); return false;"><i class="bi bi-camera-video me-2"></i>Sessions</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-mood" href="#" onclick="showSection('section-mood'); return false;"><i class="bi bi-heart-pulse me-2"></i>Mood Tracker</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-goals" href="#" onclick="showSection('section-goals'); return false;"><i class="bi bi-bullseye me-2"></i>Wellness Goals</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-journal" href="#" onclick="showSection('section-journal'); return false;"><i class="bi bi-journal-richtext me-2"></i>My Journal</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-resources" href="#" onclick="showSection('section-resources'); return false;"><i class="bi bi-stars me-2"></i>Wellness Resources</a></li>
          <li class="nav-item"><a class="nav-link" href="patient-forum.php"><i class="bi bi-chat-square-heart me-2"></i>Community Forum</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-payments" href="#" onclick="showSection('section-payments'); return false;"><i class="bi bi-credit-card me-2"></i>Payments &amp; Insurance</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-consents" href="#" onclick="showSection('section-consents'); return false;"><i class="bi bi-file-earmark-check me-2"></i>Legal Consents</a></li>
          <li class="nav-item"><a class="nav-link" data-section="section-emergency" href="#" style="color:#dc3545;" onclick="showSection('section-emergency'); return false;"><i class="bi bi-telephone-fill me-2" style="color:#dc3545;"></i><span style="color:#dc3545;">🆘 Emergency Help</span></a></li>
        </ul>
        <div class="px-2 pb-3 pt-2 border-top mt-2">
          <a href="index.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
        </div>
      </nav>

      <!-- Main -->
      <main class="col-md-9 col-lg-10 p-4 fade-in">

        <!-- DASHBOARD -->
        <div id="section-dashboard">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Dashboard</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
          </div>

          <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
              <div class="card card-custom h-100"><div class="card-body d-flex justify-content-between align-items-center">
                <div><h6 class="text-secondary-custom mb-2">Next Appointment</h6><h3 class="fw-bold text-primary-custom mb-0">May 5, 2026</h3></div>
                <div class="bg-light-green p-3 rounded-circle text-primary-custom"><i class="bi bi-calendar-check fs-4"></i></div>
              </div></div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="card card-custom h-100"><div class="card-body d-flex justify-content-between align-items-center">
                <div><h6 class="text-secondary-custom mb-2">Today's Mood</h6><h3 class="fw-bold text-primary-custom mb-0">😊 Good</h3></div>
                <div class="bg-light-green p-3 rounded-circle text-primary-custom"><i class="bi bi-emoji-smile fs-4"></i></div>
              </div></div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="card card-custom h-100"><div class="card-body d-flex justify-content-between align-items-center">
                <div><h6 class="text-secondary-custom mb-2">Active Goals</h6><h3 class="fw-bold text-primary-custom mb-0">3 Goals</h3></div>
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-accent"><i class="bi bi-bullseye fs-4"></i></div>
              </div></div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="card card-custom h-100"><div class="card-body d-flex justify-content-between align-items-center">
                <div><h6 class="text-secondary-custom mb-2">Pending Actions</h6><h3 class="fw-bold text-danger mb-0">2 Items</h3></div>
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger"><i class="bi bi-exclamation-circle fs-4"></i></div>
              </div></div>
            </div>
          </div>

          <div class="row g-4 mb-4">
            <div class="col-12">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Onboarding Progress</h5></div>
                <div class="card-body">
                  <div class="d-flex justify-content-between mb-2"><span class="text-secondary-custom">Overall Completion</span><span class="fw-bold text-primary-custom">60%</span></div>
                  <div class="progress mb-4" style="height:10px;"><div class="progress-bar bg-success" style="width:60%"></div></div>
                  <div class="row g-3">
                    <div class="col-md-6"><i class="bi bi-check-circle-fill text-success me-2"></i>Profile Created</div>
                    <div class="col-md-6"><i class="bi bi-check-circle-fill text-success me-2"></i>Insurance Verified</div>
                    <div class="col-md-6"><i class="bi bi-check-circle-fill text-success me-2"></i>Consents Signed</div>
                    <div class="col-md-6"><i class="bi bi-exclamation-circle-fill text-warning me-2"></i>Intake Form Pending</div>
                    <div class="col-md-6"><i class="bi bi-exclamation-circle-fill text-warning me-2"></i>Payment Method Pending</div>
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
                  <button type="button" class="btn btn-danger" onclick="showSection('section-emergency'); return false;">🆘 Get Help Now</button>
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
                    <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-camera-video text-primary-custom me-2"></i>Session completed with Dr. Hassan</span><small class="text-secondary-custom">Apr 28, 2026</small></li>
                    <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-heart-pulse text-primary-custom me-2"></i>Mood logged: Anxious</span><small class="text-secondary-custom">Apr 27, 2026</small></li>
                    <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-bullseye text-primary-custom me-2"></i>Wellness goal updated: Meditation</span><small class="text-secondary-custom">Apr 26, 2026</small></li>
                    <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-journal-richtext text-primary-custom me-2"></i>Journal entry added</span><small class="text-secondary-custom">Apr 25, 2026</small></li>
                    <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-calendar-event text-primary-custom me-2"></i>Appointment booked for May 5</span><small class="text-secondary-custom">Apr 24, 2026</small></li>
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
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
          </div>
          <div class="row"><div class="col-12">
            <div class="card card-custom">
              <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Your Onboarding Checklist</h5></div>
              <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary-custom">Overall Progress</span><span class="fw-bold text-primary-custom">60%</span></div>
                <div class="progress mb-4" style="height:10px;"><div class="progress-bar bg-success" style="width:60%"></div></div>
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center"><div class="bg-light-green text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width:40px;height:40px;">1</div><div><div class="fw-bold">Create Profile</div><small class="text-secondary-custom">Complete your personal information</small></div></div>
                    <span class="badge bg-success">Completed</span>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center"><div class="bg-light-green text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width:40px;height:40px;">2</div><div><div class="fw-bold">Submit Intake Form</div><small class="text-secondary-custom">Answer clinical assessment questions</small></div></div>
                    <div><span class="badge bg-warning text-dark me-2">Pending</span><button type="button" class="btn btn-sm btn-primary-custom" onclick="showToast('Intake form coming soon.','success')">Start Now</button></div>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center"><div class="bg-light-green text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width:40px;height:40px;">3</div><div><div class="fw-bold">Verify Insurance</div><small class="text-secondary-custom">Add your insurance provider details</small></div></div>
                    <span class="badge bg-success">Completed</span>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center"><div class="bg-light-green text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width:40px;height:40px;">4</div><div><div class="fw-bold">Sign Legal Consents</div><small class="text-secondary-custom">Review and sign required documents</small></div></div>
                    <span class="badge bg-success">Completed</span>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center"><div class="bg-light-green text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width:40px;height:40px;">5</div><div><div class="fw-bold">Add Payment Method</div><small class="text-secondary-custom">Set up billing for sessions</small></div></div>
                    <div><span class="badge bg-warning text-dark me-2">Pending</span><button type="button" class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#paymentModal">Add Now</button></div>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center"><div class="bg-light-green text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width:40px;height:40px;">6</div><div><div class="fw-bold">Receive Therapist Match</div><small class="text-secondary-custom">Awaiting intake form completion</small></div></div>
                    <span class="badge bg-secondary">Locked</span>
                  </li>
                </ul>
              </div>
            </div>
          </div></div>
        </div>

        <!-- THERAPIST -->
        <div id="section-therapist" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">My Therapist</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
          </div>
          <div class="row g-4">
            <div class="col-lg-7">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">My Therapist</h5></div>
                <div class="card-body text-center">
                  <div class="bg-light-green rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:100px;height:100px;"><i class="bi bi-person-fill text-primary-custom" style="font-size:3rem;"></i></div>
                  <h4 class="fw-bold text-primary-custom mb-1">Dr. Amira Hassan</h4>
                  <p class="text-secondary-custom mb-3">Cognitive Behavioral Therapy</p>
                  <ul class="list-unstyled text-start mx-auto" style="max-width:380px;">
                    <li class="mb-2"><i class="bi bi-translate text-primary-custom me-2"></i> Languages: Arabic, English</li>
                    <li class="mb-2"><i class="bi bi-gender-female text-primary-custom me-2"></i> Gender: Female</li>
                    <li class="mb-2"><i class="bi bi-star-fill text-accent me-2"></i> Rating: 4.2 / 5</li>
                    <li class="mb-2"><i class="bi bi-shield-check text-success me-2"></i> Status: Active &amp; Verified</li>
                  </ul>
                  <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-primary-custom w-50" data-bs-toggle="modal" data-bs-target="#messageTherapistModal"><i class="bi bi-envelope me-1"></i> Send Message</button>
                    <button type="button" class="btn btn-outline-secondary w-50" data-bs-toggle="modal" data-bs-target="#rematchModal"><i class="bi bi-arrow-repeat me-1"></i> Request Re-Match</button>
                  </div>
                  <p class="text-secondary-custom small mt-3 mb-0"><i class="bi bi-shield-lock me-1"></i> Direct contact details are never shared. All communication is platform-only.</p>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">My Matching Preferences</h5></div>
                <div class="card-body">
                  <div class="mb-3"><label class="form-label">Preferred Language</label><select class="form-select"><option selected>Arabic</option><option>English</option><option>French</option><option>Other</option></select></div>
                  <div class="mb-3"><label class="form-label">Preferred Therapist Gender</label><select class="form-select"><option>No Preference</option><option selected>Female</option><option>Male</option></select></div>
                  <div class="mb-3"><label class="form-label">Cultural / Religious Background</label><select class="form-select"><option selected>No Preference</option><option>Muslim</option><option>Christian</option><option>Other</option></select></div>
                  <div class="mb-3"><label class="form-label">Specialization Needed</label><select class="form-select"><option selected>CBT</option><option>Anxiety</option><option>Depression</option><option>Trauma</option><option>Other</option></select></div>
                  <button type="button" class="btn btn-primary-custom w-100" onclick="savePreferences()">Save Preferences</button>
                  <p class="text-secondary-custom small mt-3 mb-0">These preferences are used when matching or re-matching you with a therapist.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- APPOINTMENTS -->
        <div id="section-appointments" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Appointments</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
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
                        <tr><td>May 5, 2026 · 3:00 PM</td><td>Dr. Hassan</td><td>Video</td><td><span class="badge bg-primary">Scheduled</span></td><td><button type="button" class="btn btn-sm btn-outline-secondary" disabled>Join Session</button> <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelAppointmentModal">Cancel</button></td></tr>
                        <tr><td>May 12, 2026 · 11:00 AM</td><td>Dr. Hassan</td><td>Video</td><td><span class="badge bg-primary">Scheduled</span></td><td><button type="button" class="btn btn-sm btn-outline-secondary" disabled>Join Session</button> <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelAppointmentModal">Cancel</button></td></tr>
                        <tr><td>May 19, 2026 · 2:00 PM</td><td>Dr. Hassan</td><td>In-Person</td><td><span class="badge bg-primary">Scheduled</span></td><td><button type="button" class="btn btn-sm btn-outline-secondary" disabled>Join Session</button> <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelAppointmentModal">Cancel</button></td></tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="tab-pane fade" id="tabPast" role="tabpanel">
                    <table class="table table-hover table-custom">
                      <thead><tr><th>Date &amp; Time</th><th>Therapist</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
                      <tbody>
                        <tr><td>Apr 28, 2026 · 3:00 PM</td><td>Dr. Hassan</td><td>Video</td><td><span class="badge bg-success">Completed</span></td><td><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sessionSummaryModal">View Summary</button></td></tr>
                        <tr><td>Apr 21, 2026 · 3:00 PM</td><td>Dr. Hassan</td><td>Video</td><td><span class="badge bg-success">Completed</span></td><td><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sessionSummaryModal">View Summary</button></td></tr>
                        <tr><td>Apr 10, 2026 · 11:00 AM</td><td>Dr. Hassan</td><td>Video</td><td><span class="badge bg-danger">No-Show</span></td><td><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sessionSummaryModal">View Summary</button></td></tr>
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
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
          </div>
          <div class="row"><div class="col-12">
            <div class="card card-custom">
              <div class="card-header bg-white border-0 pt-4 pb-0 d-flex align-items-center">
                <h5 class="fw-bold text-primary-custom mb-0">Session Room</h5>
                <span id="sessionBadge" class="badge bg-secondary ms-auto">Scheduled</span>
              </div>
              <div class="card-body">
                <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i><strong>Next Session:</strong> May 5, 2026 at 3:00 PM with Dr. Hassan</div>

                <div id="statePreSession">
                  <div class="text-center py-5">
                    <i class="bi bi-camera-video text-primary-custom" style="font-size:4rem;"></i>
                    <p class="text-secondary-custom mt-3 mb-4">Your session hasn't started yet. You can check in up to 5 minutes before.</p>
                    <button type="button" class="btn btn-primary-custom btn-lg" onclick="patientCheckIn()"><i class="bi bi-box-arrow-in-right me-1"></i> Check In &amp; Enter Waiting Room</button>
                  </div>
                </div>

                <div id="stateWaitingRoom" style="display:none;">
                  <div class="alert alert-warning"><i class="bi bi-hourglass-split me-2"></i> You are in the virtual waiting room. Your therapist will admit you shortly.</div>
                  <div class="text-center py-5">
                    <div class="spinner-border text-primary-custom mb-3" role="status" style="width:3rem;height:3rem;"></div>
                    <h5>Waiting for Dr. Hassan to admit you...</h5>
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
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
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
                  <table class="table table-custom">
                    <thead><tr><th>Day</th><th>Mood</th><th>Score</th><th>Trend</th></tr></thead>
                    <tbody>
                      <tr><td>Mon</td><td>Good</td><td>4</td><td><div class="progress" style="height:8px;"><div class="progress-bar bg-success" style="width:80%"></div></div></td></tr>
                      <tr><td>Tue</td><td>Anxious</td><td>2</td><td><div class="progress" style="height:8px;"><div class="progress-bar bg-danger" style="width:40%"></div></div></td></tr>
                      <tr><td>Wed</td><td>Neutral</td><td>3</td><td><div class="progress" style="height:8px;"><div class="progress-bar bg-warning" style="width:60%"></div></div></td></tr>
                      <tr><td>Thu</td><td>Good</td><td>4</td><td><div class="progress" style="height:8px;"><div class="progress-bar bg-success" style="width:80%"></div></div></td></tr>
                      <tr><td>Fri</td><td>Excellent</td><td>5</td><td><div class="progress" style="height:8px;"><div class="progress-bar bg-success" style="width:100%"></div></div></td></tr>
                      <tr><td>Sat</td><td>Low</td><td>2</td><td><div class="progress" style="height:8px;"><div class="progress-bar bg-danger" style="width:40%"></div></div></td></tr>
                      <tr><td>Sun</td><td>Good</td><td>4</td><td><div class="progress" style="height:8px;"><div class="progress-bar bg-success" style="width:80%"></div></div></td></tr>
                    </tbody>
                  </table>
                  <p class="text-center text-secondary-custom mb-0 mt-3">Weekly average: <span class="fw-bold text-primary-custom">3.4 / 5</span></p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- GOALS -->
        <div id="section-goals" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Wellness Goals</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
          </div>
          <div class="row g-4">
            <div class="col-lg-5">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Add New Goal</h5></div>
                <div class="card-body">
                  <div class="mb-3"><label class="form-label">Goal Title</label><input id="goalTitle" type="text" class="form-control" placeholder="e.g. Daily walk" name="goalTitle"></div>
                  <div class="mb-3"><label class="form-label">Target (days per week)</label><input type="number" class="form-control" min="1" max="7" value="5"></div>
                  <div class="mb-3"><label class="form-label">Category</label><select class="form-select"><option>Mindfulness</option><option>Exercise</option><option>Sleep</option><option>Journaling</option><option>Medication</option><option>Other</option></select></div>
                  <button type="button" class="btn btn-primary-custom w-100" onclick="saveGoal()">Save Goal</button>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">My Goals</h5></div>
                <div class="card-body">
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                      <div class="d-flex align-items-center"><div class="bg-light-green text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;"><i class="bi bi-lungs fs-4"></i></div><div><div class="fw-bold">Daily Meditation</div><small class="text-secondary-custom">Mindfulness</small></div></div>
                      <div class="d-flex align-items-center gap-3"><span class="text-success fw-bold">80%</span><div class="progress" style="width:120px;height:8px;"><div class="progress-bar bg-success" style="width:80%"></div></div><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#updateGoalModal">Update</button></div>
                    </li>
                    <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                      <div class="d-flex align-items-center"><div class="bg-light-green text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;"><i class="bi bi-bicycle fs-4"></i></div><div><div class="fw-bold">Morning Walk</div><small class="text-secondary-custom">Exercise</small></div></div>
                      <div class="d-flex align-items-center gap-3"><span class="text-warning fw-bold">60%</span><div class="progress" style="width:120px;height:8px;"><div class="progress-bar bg-warning" style="width:60%"></div></div><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#updateGoalModal">Update</button></div>
                    </li>
                    <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                      <div class="d-flex align-items-center"><div class="bg-light-green text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;"><i class="bi bi-moon fs-4"></i></div><div><div class="fw-bold">Sleep by 11 PM</div><small class="text-secondary-custom">Sleep</small></div></div>
                      <div class="d-flex align-items-center gap-3"><span class="text-danger fw-bold">40%</span><div class="progress" style="width:120px;height:8px;"><div class="progress-bar bg-danger" style="width:40%"></div></div><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#updateGoalModal">Update</button></div>
                    </li>
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
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
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
                  <div class="card card-custom mb-3 p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2"><h6 class="fw-bold mb-0 text-primary-custom">Feeling Better Today</h6><div><small class="text-secondary-custom me-2">Apr 28</small><span class="badge bg-secondary">Private</span></div></div>
                    <p class="text-secondary-custom small mb-2">Today I woke up feeling lighter than usual. The meditation exercises are starting to...</p>
                    <div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewJournalModal">Read</button><button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePrivacy(this)">Change Privacy</button></div>
                  </div>
                  <div class="card card-custom mb-3 p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2"><h6 class="fw-bold mb-0 text-primary-custom">Session Reflection</h6><div><small class="text-secondary-custom me-2">Apr 25</small><span class="badge bg-primary">Shared</span></div></div>
                    <p class="text-secondary-custom small mb-2">Dr. Hassan suggested I try journaling before bed. It's helping me process my...</p>
                    <div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewJournalModal">Read</button><button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePrivacy(this)">Change Privacy</button></div>
                  </div>
                  <div class="card card-custom mb-3 p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2"><h6 class="fw-bold mb-0 text-primary-custom">Hard Day</h6><div><small class="text-secondary-custom me-2">Apr 22</small><span class="badge bg-secondary">Private</span></div></div>
                    <p class="text-secondary-custom small mb-2">Work was overwhelming today. I felt anxious most of the morning but used the...</p>
                    <div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewJournalModal">Read</button><button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePrivacy(this)">Change Privacy</button></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RESOURCES -->
        <div id="section-resources" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Wellness Resources</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
          </div>
          <div class="row g-4 mb-4"><div class="col-12">
            <div class="card card-custom">
              <div class="card-header bg-white border-0 pt-4 pb-0 d-flex align-items-center">
                <h5 class="fw-bold text-primary-custom mb-0">Recommended For You</h5>
                <span class="badge bg-light text-secondary-custom ms-auto">Based on your mood</span>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6 col-lg-3"><div class="card card-custom h-100 p-3 text-center">
                    <div class="bg-light-green rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px;"><i class="bi bi-lungs text-primary-custom fs-3"></i></div>
                    <h6 class="fw-bold text-primary-custom">5-Min Breathing</h6>
                    <span class="badge mb-2" style="background-color:var(--primary-green);">Mindfulness</span>
                    <p class="small text-secondary-custom">Box breathing for calm</p>
                    <button type="button" class="btn btn-sm btn-primary-custom" onclick="startMindfulnessTimer(5); showSection('section-resources'); return false;">Start Timer</button>
                  </div></div>
                  <div class="col-md-6 col-lg-3"><div class="card card-custom h-100 p-3 text-center">
                    <div class="bg-light-green rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px;"><i class="bi bi-book text-primary-custom fs-3"></i></div>
                    <h6 class="fw-bold text-primary-custom">CBT Worksheet</h6>
                    <span class="badge mb-2" style="background-color:var(--primary-green);">Therapy</span>
                    <p class="small text-secondary-custom">Challenge negative thoughts</p>
                    <button type="button" class="btn btn-sm btn-primary-custom" onclick="showToast('Resource opened.', 'success')">Open</button>
                  </div></div>
                  <div class="col-md-6 col-lg-3"><div class="card card-custom h-100 p-3 text-center">
                    <div class="bg-light-green rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px;"><i class="bi bi-music-note-beamed text-primary-custom fs-3"></i></div>
                    <h6 class="fw-bold text-primary-custom">Calm Sounds</h6>
                    <span class="badge mb-2" style="background-color:var(--primary-green);">Audio</span>
                    <p class="small text-secondary-custom">Nature sounds for focus</p>
                    <button type="button" class="btn btn-sm btn-primary-custom" onclick="showToast('Playing...', 'success')">Play</button>
                  </div></div>
                  <div class="col-md-6 col-lg-3"><div class="card card-custom h-100 p-3 text-center">
                    <div class="bg-light-green rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px;"><i class="bi bi-bicycle text-primary-custom fs-3"></i></div>
                    <h6 class="fw-bold text-primary-custom">Morning Movement</h6>
                    <span class="badge mb-2" style="background-color:var(--primary-green);">Exercise</span>
                    <p class="small text-secondary-custom">10-min wake-up routine</p>
                    <button type="button" class="btn btn-sm btn-primary-custom" onclick="showToast('Starting exercise guide.', 'success')">Start</button>
                  </div></div>
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
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
          </div>
          <div class="card card-custom mb-4">
            <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Insurance Information</h5></div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="mb-2"><small class="text-secondary-custom">Provider</small><div class="fw-bold">MediCare Egypt</div></div>
                  <div class="mb-2"><small class="text-secondary-custom">Policy Number</small><div class="fw-bold">MCE-20240512</div></div>
                  <div class="mb-2"><small class="text-secondary-custom">Eligibility Status</small><div><span class="badge bg-success">Eligible</span></div></div>
                </div>
                <div class="col-md-6">
                  <div class="mb-2"><small class="text-secondary-custom">Plan Type</small><div class="fw-bold">Individual</div></div>
                  <div class="mb-2"><small class="text-secondary-custom">Coverage</small><div class="fw-bold">80%</div></div>
                  <div class="mb-2"><small class="text-secondary-custom">Expiry</small><div class="fw-bold">December 2026</div></div>
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
                  <tr><td>Apr 28, 2026</td><td>Dr. Hassan – Video</td><td>250 EGP</td><td><span class="badge bg-success">Paid</span></td><td><button type="button" class="btn btn-sm btn-outline-secondary" onclick="showToast('Invoice downloaded.', 'success')">Download</button></td></tr>
                  <tr><td>Apr 21, 2026</td><td>Dr. Hassan – Video</td><td>250 EGP</td><td><span class="badge bg-success">Paid</span></td><td><button type="button" class="btn btn-sm btn-outline-secondary" onclick="showToast('Invoice downloaded.', 'success')">Download</button></td></tr>
                  <tr><td>Apr 10, 2026</td><td>Dr. Hassan – Video</td><td>250 EGP</td><td><span class="badge bg-danger">Unpaid</span></td><td><button type="button" class="btn btn-sm btn-primary-custom" onclick="showToast('Redirecting to payment gateway...', 'success')">Pay Now</button></td></tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card card-custom">
            <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Submit a Dispute</h5></div>
            <div class="card-body">
              <div class="mb-3"><label class="form-label">Related Session</label><select class="form-select"><option>Apr 28 session</option><option>Apr 21 session</option><option>Apr 10 session</option></select></div>
              <div class="mb-3"><label class="form-label">Dispute Reason</label><select class="form-select"><option>Incorrect charge</option><option>Session not received</option><option>Technical issue</option><option>Other</option></select></div>
              <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" rows="3" placeholder="Provide details..."></textarea></div>
              <button type="button" class="btn btn-primary-custom" onclick="submitDispute()">Submit Dispute</button>
              <div class="alert alert-warning mt-3 mb-0"><i class="bi bi-info-circle me-2"></i><strong>Open Dispute #D-002</strong> · Apr 10 session · Status: Under Review</div>
            </div>
          </div>
        </div>

        <!-- CONSENTS -->
        <div id="section-consents" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Legal Consents</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
          </div>
          <div class="row"><div class="col-12">
            <div class="card card-custom">
              <div class="card-header bg-white border-0 pt-4 pb-0"><h5 class="fw-bold text-primary-custom mb-0">Legal Consents &amp; Agreements</h5></div>
              <div class="card-body">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3 border-bottom">
                    <div><div class="fw-bold">Informed Consent for Therapy</div><small class="text-secondary-custom">Rights and responsibilities in therapy</small></div>
                    <div><span class="badge bg-success me-2">Signed · Apr 1, 2026</span><button type="button" class="btn btn-sm btn-outline-secondary" onclick="showToast('Document opened.', 'success')">View</button></div>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3 border-bottom">
                    <div><div class="fw-bold">Privacy Policy (v2.1)</div><small class="text-secondary-custom">How your data is stored and used</small></div>
                    <div><span class="badge bg-success me-2">Signed · Apr 1, 2026</span><button type="button" class="btn btn-sm btn-outline-secondary" onclick="showToast('Document opened.', 'success')">View</button></div>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3 border-bottom">
                    <div><div class="fw-bold">Telehealth Agreement</div><small class="text-secondary-custom">Terms for video/online sessions</small></div>
                    <div><span class="badge bg-success me-2">Signed · Apr 1, 2026</span><button type="button" class="btn btn-sm btn-outline-secondary" onclick="showToast('Document opened.', 'success')">View</button></div>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                    <div><div class="fw-bold">Updated Terms of Service (v3.0)</div><small class="text-secondary-custom">New platform terms — action required</small></div>
                    <div><span class="badge bg-warning text-dark me-2">Unsigned</span><button type="button" class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#consentSignModal">Review &amp; Sign</button></div>
                  </li>
                </ul>
              </div>
            </div>
          </div></div>
        </div>

        <!-- EMERGENCY -->
        <div id="section-emergency" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary-custom mb-0">Emergency Help</h2>
            <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Sarah Johnson</span>
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
      <div class="mb-3"><label class="form-label">Preferred Date</label><input id="apptDate" type="date" class="form-control" name="apptDate"></div>
      <div class="mb-3"><label class="form-label">Preferred Time Slot</label><select class="form-select"><option>9:00 AM</option><option>10:00 AM</option><option>11:00 AM</option><option>2:00 PM</option><option>3:00 PM</option><option>4:00 PM</option></select></div>
      <div class="mb-3"><label class="form-label">Session Type</label><select class="form-select"><option>Video Session</option><option>In-Person</option></select></div>
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
      <div class="mb-3"><label class="form-label">Provider</label><input type="text" class="form-control" value="MediCare Egypt"></div>
      <div class="mb-3"><label class="form-label">Policy Number</label><input type="text" class="form-control" value="MCE-20240512"></div>
      <div class="mb-3"><label class="form-label">Member ID</label><input type="text" class="form-control"></div>
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
    <div class="modal-header"><h5 class="modal-title">Add Payment Method</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label">Card Number</label><input type="text" class="form-control" placeholder="1234 5678 9012 3456"></div>
      <div class="row g-2 mb-3">
        <div class="col-6"><label class="form-label">Expiry Date</label><input type="text" class="form-control" placeholder="MM/YY"></div>
        <div class="col-6"><label class="form-label">CVV</label><input type="text" class="form-control" placeholder="123"></div>
      </div>
      <div class="mb-3"><label class="form-label">Cardholder Name</label><input type="text" class="form-control" placeholder="Full name"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" id="btnSaveCard" class="btn btn-primary-custom">Save Card</button></div>
  </div></div></div>

  <!-- Toast container -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
  <script src="assets/js/patient.js"></script>
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
