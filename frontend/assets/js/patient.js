// patient.js — Patient Dashboard logic
// Relies on showToast() from main.js

// ---------- Navigation ----------
function showSection(id) {
  document.querySelectorAll('main > div[id^="section-"]').forEach(s => s.style.display = 'none');
  const target = document.getElementById(id);
  if (target) target.style.display = 'block';
  document.querySelectorAll('.sidebar .nav-link[data-section]').forEach(l => l.classList.remove('active'));
  const activeLink = document.querySelector(`.sidebar .nav-link[data-section="${id}"]`);
  if (activeLink) activeLink.classList.add('active');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ---------- UC-10: Preferences ----------
function savePreferences() {
  showToast('Matching preferences saved successfully.', 'success');
}

// ---------- UC-12, UC-9: Appointments ----------
function checkAvailability() {
  const date = document.getElementById('apptDate').value;
  const resultDiv = document.getElementById('availabilityResult');
  resultDiv.style.display = 'block';
  if (date) {
    resultDiv.innerHTML = '<div class="alert alert-success mb-0"><i class="bi bi-check-circle me-1"></i> Slot available! Click Confirm Booking.</div>';
  } else {
    resultDiv.innerHTML = '<div class="alert alert-warning mb-0"><i class="bi bi-exclamation-circle me-1"></i> No slots available for this time. <a href="#" onclick="joinWaitlist(); return false;" class="alert-link">Join Waitlist</a></div>';
  }
}
function joinWaitlist() {
  bootstrap.Modal.getInstance(document.getElementById('bookAppointmentModal'))?.hide();
  showToast('You have been added to the waitlist. You will be notified when a slot opens.', 'success');
}
function confirmBooking() {
  bootstrap.Modal.getInstance(document.getElementById('bookAppointmentModal'))?.hide();
  showToast('Appointment booked successfully!', 'success');
}

// ---------- UC-13, UC-15: Sessions ----------
let patientTimerInterval;
function patientCheckIn() {
  document.getElementById('statePreSession').style.display = 'none';
  document.getElementById('stateWaitingRoom').style.display = 'block';
  document.getElementById('sessionBadge').className = 'badge bg-warning text-dark ms-auto';
  document.getElementById('sessionBadge').textContent = 'In Waiting Room';
  showToast('Checked in successfully. Waiting for Dr. Hassan.', 'success');
  setTimeout(() => {
    document.getElementById('stateWaitingRoom').style.display = 'none';
    document.getElementById('stateLiveSession').style.display = 'block';
    document.getElementById('sessionBadge').className = 'badge bg-danger ms-auto';
    document.getElementById('sessionBadge').textContent = 'Session Live';
    showToast('Dr. Hassan has admitted you. Session is now live!', 'success');
    let secs = 0;
    patientTimerInterval = setInterval(() => {
      secs++;
      const m = Math.floor(secs / 60), s = secs % 60;
      document.getElementById('patientSessionTimer').textContent =
        `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
    }, 1000);
  }, 3000);
}
function leaveWaitingRoom() {
  document.getElementById('stateWaitingRoom').style.display = 'none';
  document.getElementById('statePreSession').style.display = 'block';
  document.getElementById('sessionBadge').className = 'badge bg-secondary ms-auto';
  document.getElementById('sessionBadge').textContent = 'Scheduled';
  showToast('You have left the waiting room.', 'success');
}
function leaveSession() {
  clearInterval(patientTimerInterval);
  document.getElementById('stateLiveSession').style.display = 'none';
  document.getElementById('statePreSession').style.display = 'block';
  document.getElementById('sessionBadge').className = 'badge bg-success ms-auto';
  document.getElementById('sessionBadge').textContent = 'Completed';
  showToast('Session ended. Thank you!', 'success');
}

// ---------- UC-19: Mood Tracker ----------
function saveMoodEntry() {
  const label = document.getElementById('moodLabel');
  if (!label || label.textContent === '—') {
    showToast('Please select a mood before saving.', 'error');
    return;
  }
  showToast(`Mood "${label.textContent}" logged successfully.`, 'success');
}

// ---------- UC-21: Wellness Goals ----------
function saveGoal() {
  const titleEl = document.getElementById('goalTitle');
  const title = titleEl.value.trim();
  if (!title) { showToast('Please enter a goal title.', 'error'); return; }
  showToast(`Goal "${title}" saved successfully.`, 'success');
  titleEl.value = '';
}

// ---------- UC-22: Journal ----------
function saveJournalEntry() {
  const title = document.getElementById('journalTitle').value.trim();
  const content = document.getElementById('journalContent').value.trim();
  const privacyEl = document.querySelector('input[name="privacy"]:checked');
  const privacy = privacyEl ? privacyEl.value : 'Private';
  if (!title || !content) { showToast('Please fill in both title and content.', 'error'); return; }
  showToast(`Entry "${title}" saved as ${privacy}.`, 'success');
  document.getElementById('journalTitle').value = '';
  document.getElementById('journalContent').value = '';
}
function togglePrivacy(btn) {
  const badge = btn.closest('.card').querySelector('.badge');
  if (badge.classList.contains('bg-secondary')) {
    badge.className = 'badge bg-primary';
    badge.textContent = 'Shared';
    showToast('Entry is now shared with your therapist.', 'success');
  } else {
    badge.className = 'badge bg-secondary';
    badge.textContent = 'Private';
    showToast('Entry is now private.', 'success');
  }
}

// ---------- UC-24: Mindfulness Timer ----------
let mindfulTimer, mindfulSeconds = 300, mindfulTotalSeconds = 300, mindfulRunning = false;
function startMindfulnessTimer(minutes) {
  clearInterval(mindfulTimer);
  mindfulRunning = false;
  mindfulTotalSeconds = minutes * 60;
  mindfulSeconds = mindfulTotalSeconds;
  updateMindfulDisplay();
  document.getElementById('mindfulnessStatus').textContent = `${minutes}-minute session ready. Press Start.`;
  document.getElementById('btnStartTimer').disabled = false;
  document.getElementById('btnPauseTimer').disabled = true;
  document.getElementById('btnStopTimer').disabled = true;
}
function updateMindfulDisplay() {
  const m = Math.floor(mindfulSeconds / 60), s = mindfulSeconds % 60;
  const el = document.getElementById('mindfulnessDisplay');
  if (el) el.textContent = `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
}
function controlTimer(action) {
  if (action === 'start') {
    if (mindfulRunning) return;
    mindfulRunning = true;
    document.getElementById('btnStartTimer').disabled = true;
    document.getElementById('btnPauseTimer').disabled = false;
    document.getElementById('btnStopTimer').disabled = false;
    document.getElementById('mindfulnessStatus').textContent = 'Session in progress... 🧘';
    mindfulTimer = setInterval(() => {
      mindfulSeconds--;
      updateMindfulDisplay();
      if (mindfulSeconds <= 0) {
        clearInterval(mindfulTimer);
        mindfulRunning = false;
        document.getElementById('mindfulnessStatus').textContent = '✅ Session complete! Great work.';
        document.getElementById('btnStartTimer').disabled = false;
        document.getElementById('btnPauseTimer').disabled = true;
        document.getElementById('btnStopTimer').disabled = true;
        showToast('Mindfulness session completed!', 'success');
      }
    }, 1000);
  } else if (action === 'pause') {
    clearInterval(mindfulTimer);
    mindfulRunning = false;
    document.getElementById('btnStartTimer').disabled = false;
    document.getElementById('btnPauseTimer').disabled = true;
    document.getElementById('mindfulnessStatus').textContent = 'Paused. Press Start to resume.';
  } else if (action === 'stop') {
    clearInterval(mindfulTimer);
    mindfulRunning = false;
    mindfulSeconds = mindfulTotalSeconds;
    updateMindfulDisplay();
    document.getElementById('btnStartTimer').disabled = false;
    document.getElementById('btnPauseTimer').disabled = true;
    document.getElementById('btnStopTimer').disabled = true;
    document.getElementById('mindfulnessStatus').textContent = 'Stopped. Select a duration to try again.';
    showToast('Session stopped.', 'success');
  }
}

// ---------- UC-34: Disputes ----------
function submitDispute() {
  showToast('Dispute submitted. Our team will review within 2-3 business days.', 'success');
}

// ---------- UC-30: Emergency Resources ----------
const emergencyData = {
  eg: [
    { name: 'Egyptian Mental Health Hotline', number: '08008880700', desc: 'Free 24/7 crisis support in Arabic' },
    { name: 'Nefsi Platform', number: 'nefsi.org', desc: 'Online mental health resources in Egypt' }
  ],
  us: [
    { name: '988 Suicide & Crisis Lifeline', number: '988', desc: 'Call or text 988, available 24/7' },
    { name: 'Crisis Text Line', number: 'Text HOME to 741741', desc: 'Free text-based crisis support' }
  ],
  uk: [
    { name: 'Samaritans', number: '116 123', desc: 'Free 24/7 emotional support' },
    { name: 'MIND', number: '0300 123 3393', desc: 'Mental health support and information' }
  ],
  intl: [
    { name: 'International Association for Suicide Prevention', number: 'iasp.info/resources/Crisis_Centres/', desc: 'Directory of global crisis centers' }
  ]
};
function loadEmergencyResources() {
  const region = document.getElementById('emergencyRegion').value;
  const list = document.getElementById('emergencyResourcesList');
  if (!region) { list.innerHTML = ''; return; }
  list.innerHTML = emergencyData[region].map(r => `
    <div class="card card-custom mb-3 p-3">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h6 class="fw-bold text-primary-custom mb-1">${r.name}</h6>
          <p class="mb-1"><strong class="text-danger">${r.number}</strong></p>
          <small class="text-secondary-custom">${r.desc}</small>
        </div>
        <i class="bi bi-telephone-fill text-danger fs-3"></i>
      </div>
    </div>`).join('');
}

// ---------- DOMContentLoaded ----------
document.addEventListener('DOMContentLoaded', () => {
  // Mood emoji button highlighting
  document.querySelectorAll('.mood-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.mood-btn').forEach(b => {
        b.classList.remove('btn-primary-custom', 'text-white');
        b.classList.add('btn-light');
      });
      btn.classList.remove('btn-light');
      btn.classList.add('btn-primary-custom', 'text-white');
      const label = document.getElementById('moodLabel');
      if (label) label.textContent = btn.dataset.label;
    });
  });

  // Goal progress slider live label
  const gp = document.getElementById('goalProgress');
  if (gp) gp.addEventListener('input', e => {
    const pv = document.getElementById('progressVal');
    if (pv) pv.textContent = e.target.value;
  });

  // Send Message
  const btnSendMessage = document.getElementById('btnSendMessage');
  if (btnSendMessage) btnSendMessage.addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('messageTherapistModal'))?.hide();
    showToast('Message sent to Dr. Hassan successfully.', 'success');
  });

  // Confirm Re-match
  const btnRematch = document.getElementById('btnConfirmRematch');
  if (btnRematch) btnRematch.addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('rematchModal'))?.hide();
    showToast('Re-match request submitted. You will be notified shortly.', 'success');
  });

  // Cancel appointment
  const btnConfirmCancel = document.getElementById('btnConfirmCancel');
  if (btnConfirmCancel) btnConfirmCancel.addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('cancelAppointmentModal'))?.hide();
    showToast('Appointment cancelled.', 'success');
  });

  // Save goal progress
  const btnSaveGoalProgress = document.getElementById('btnSaveGoalProgress');
  if (btnSaveGoalProgress) btnSaveGoalProgress.addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('updateGoalModal'))?.hide();
    showToast('Goal progress updated!', 'success');
  });

  // Save insurance
  const btnSaveInsurance = document.getElementById('btnSaveInsurance');
  if (btnSaveInsurance) btnSaveInsurance.addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('updateInsuranceModal'))?.hide();
    showToast('Insurance information updated.', 'success');
  });

  // Save card
  const btnSaveCard = document.getElementById('btnSaveCard');
  if (btnSaveCard) btnSaveCard.addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('paymentModal'))?.hide();
    showToast('Payment method saved successfully.', 'success');
  });

  // Sign consent
  const btnSign = document.getElementById('btnSignConsent');
  if (btnSign) btnSign.addEventListener('click', () => {
    if (!document.getElementById('consentCheck').checked) {
      showToast('Please read and check the agreement box first.', 'error');
      return;
    }
    bootstrap.Modal.getInstance(document.getElementById('consentSignModal'))?.hide();
    showToast('Consent signed and recorded successfully.', 'success');
  });
});
