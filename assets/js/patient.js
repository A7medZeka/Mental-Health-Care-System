/**
 * patient.js — Patient dashboard AJAX handlers
 * All POST requests use the action dispatcher in PatientDashboardController.
 * Mirrors Admin's pattern: fetch → JSON → toast feedback.
 */

'use strict';

const DASHBOARD_URL = window.location.href.split('?')[0];

// ── Section navigation ────────────────────────────────────────────────────────
// All dashboard content lives in <div id="section-*"> blocks inside dashboard.php.
// showSection() hides every section, shows the target, and marks the sidebar link active.

function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('main > div[id^="section-"]').forEach(el => {
        el.style.display = 'none';
    });

    // Show the requested section
    const target = document.getElementById(sectionId);
    if (target) {
        target.style.display = 'block';
        // Smooth scroll to top of content
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Update active sidebar link
    document.querySelectorAll('.nav-link[data-section]').forEach(link => {
        link.classList.remove('active');
        if (link.dataset.section === sectionId) {
            link.classList.add('active');
        }
    });

    // Persist to sessionStorage so refresh keeps the same section
    try { sessionStorage.setItem('patientSection', sectionId); } catch(e) {}
}

// On load — restore last section or default to dashboard
document.addEventListener('DOMContentLoaded', () => {
    // First hide all sections
    document.querySelectorAll('main > div[id^="section-"]').forEach(el => {
        el.style.display = 'none';
    });

    // Restore from sessionStorage or default
    const saved = sessionStorage.getItem('patientSection') || 'section-dashboard';
    showSection(saved);
});

// ── Utility ──────────────────────────────────────────────────────────────────

function patientPost(formData, onSuccess) {
    fetch(DASHBOARD_URL, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Done!', 'success');
            if (typeof onSuccess === 'function') onSuccess(data);
        } else {
            showToast(data.message || 'Something went wrong.', 'danger');
        }
    })
    .catch(() => showToast('Network error. Please try again.', 'danger'));
}

function buildForm(action, fields) {
    const fd = new FormData();
    fd.append('action', action);
    for (const [k, v] of Object.entries(fields)) fd.append(k, v);
    return fd;
}

// ── Profile ───────────────────────────────────────────────────────────────────

function saveProfile() {
    const fd = buildForm('update_profile', {
        first_name:   document.getElementById('profileFirstName')?.value.trim()  || '',
        last_name:    document.getElementById('profileLastName')?.value.trim()   || '',
        phone_number: document.getElementById('profilePhone')?.value.trim()      || '',
        city:         document.getElementById('profileCity')?.value.trim()       || '',
        gender:       document.getElementById('profileGender')?.value            || '',
    });
    patientPost(fd);
}

function savePreferences() {
    const fd = buildForm('update_preferences', {
        pref_language:            document.querySelector('[name="prefLang"]')?.value            || '',
        pref_therapist_gender:    document.querySelector('[name="prefGender"]')?.value          || '',
        pref_cultural_background: document.querySelector('[name="prefCulture"]')?.value         || '',
        pref_specialization:      document.querySelector('[name="prefSpecialization"]')?.value  || '',
    });
    patientPost(fd);
}

// ── Appointments ──────────────────────────────────────────────────────────────

function confirmBooking() {
    const therapistId = document.getElementById('apptTherapist')?.value;
    const date        = document.getElementById('apptDate')?.value;
    const type        = document.getElementById('apptType')?.value || 'Video Session';

    if (!therapistId || !date) { showToast('Please fill all fields.', 'warning'); return; }

    const fd = buildForm('book_appointment', {
        therapist_id:     therapistId,
        appointment_date: date,
        session_type:     type,
    });
    patientPost(fd, () => setTimeout(() => location.reload(), 1200));
}

function cancelAppointment(appointmentId) {
    if (!confirm('Cancel this appointment?')) return;
    const fd = buildForm('cancel_appointment', { appointment_id: appointmentId });
    patientPost(fd, () => setTimeout(() => location.reload(), 1200));
}

function checkAvailability() {
    const result = document.getElementById('availabilityResult');
    if (result) {
        result.style.display = 'block';
        result.innerHTML = '<div class="alert alert-success mb-0"><i class="bi bi-check-circle me-2"></i>Slot is available!</div>';
    }
}

// ── Mood ─────────────────────────────────────────────────────────────────────

let selectedMoodScore = null;
let selectedMoodLabel = null;

document.addEventListener('DOMContentLoaded', () => {
    // Mood button selection
    document.querySelectorAll('.mood-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.mood-btn').forEach(b => b.classList.remove('btn-primary-custom','active'));
            btn.classList.add('btn-primary-custom','active');
            selectedMoodScore = btn.dataset.mood;
            selectedMoodLabel = btn.dataset.label;
            const label = document.getElementById('moodLabel');
            if (label) label.textContent = selectedMoodLabel;
        });
    });

    // Goal progress range
    const rangeInput = document.getElementById('goalProgress');
    const progressVal = document.getElementById('progressVal');
    if (rangeInput && progressVal) {
        rangeInput.addEventListener('input', () => { progressVal.textContent = rangeInput.value; });
    }

    // Notification badge
    const badge = document.getElementById('notifBadge');
    if (badge && parseInt(badge.textContent, 10) > 0) badge.style.display = 'inline';
});

function saveMoodEntry() {
    if (!selectedMoodScore) { showToast('Please select a mood.', 'warning'); return; }
    const notes = document.getElementById('moodNotes')?.value.trim() || '';
    const fd = buildForm('log_mood', {
        mood_score: selectedMoodScore,
        mood_label: selectedMoodLabel,
        notes:      notes,
    });
    patientPost(fd, () => {
        document.getElementById('moodNotes').value = '';
        selectedMoodScore = null; selectedMoodLabel = null;
        document.querySelectorAll('.mood-btn').forEach(b => b.classList.remove('btn-primary-custom','active'));
    });
}

// ── Goals ─────────────────────────────────────────────────────────────────────

function saveGoal() {
    const title      = document.getElementById('goalTitle')?.value.trim();
    const targetDays = document.getElementById('goalTargetDays')?.value || 5;
    const category   = document.getElementById('goalCategory')?.value  || 'Other';

    if (!title) { showToast('Goal title is required.', 'warning'); return; }
    const fd = buildForm('create_goal', { goal_title: title, target_days: targetDays, category });
    patientPost(fd, () => setTimeout(() => location.reload(), 1200));
}

function saveGoalProgress() {
    const goalId   = document.getElementById('activeGoalId')?.value;
    const progress = document.getElementById('goalProgress')?.value || 0;
    if (!goalId) { showToast('No goal selected.', 'warning'); return; }
    const fd = buildForm('update_goal', { goal_id: goalId, progress });
    patientPost(fd, () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('updateGoalModal'));
        if (modal) modal.hide();
        setTimeout(() => location.reload(), 800);
    });
}

function openGoalModal(goalId, currentProgress) {
    document.getElementById('activeGoalId').value = goalId;
    const range = document.getElementById('goalProgress');
    const val   = document.getElementById('progressVal');
    if (range) { range.value = currentProgress; }
    if (val)   { val.textContent = currentProgress; }
    const modal = new bootstrap.Modal(document.getElementById('updateGoalModal'));
    modal.show();
}

// ── Journal ───────────────────────────────────────────────────────────────────

function saveJournalEntry() {
    const title   = document.getElementById('journalTitle')?.value.trim();
    const content = document.getElementById('journalContent')?.value.trim();
    const privacy = document.querySelector('[name="privacy"]:checked')?.value || 'Private';

    if (!title || !content) { showToast('Title and content are required.', 'warning'); return; }
    const fd = buildForm('create_journal', { journalTitle: title, journalContent: content, privacy });
    patientPost(fd, () => {
        document.getElementById('journalTitle').value   = '';
        document.getElementById('journalContent').value = '';
        setTimeout(() => location.reload(), 1000);
    });
}

function togglePrivacy(btn, entryId) {
    const fd = buildForm('toggle_privacy', { entry_id: entryId });
    patientPost(fd, data => {
        const badge = btn.closest('.card')?.querySelector('.privacy-badge');
        if (badge) badge.textContent = data.new_privacy === 'Private' ? 'Private' : 'Shared';
    });
}

// ── Payments & Insurance ──────────────────────────────────────────────────────

function saveCard() {
    const cardNumber = document.getElementById('cardNumber')?.value.trim();
    const cvv        = document.getElementById('cardCvv')?.value.trim();
    const expiry     = document.getElementById('cardExpiry')?.value.trim();
    const holder     = document.getElementById('cardHolder')?.value.trim();
    const amount     = document.getElementById('cardAmount')?.value.trim();

    if (!cardNumber) { showToast('Please enter a card number.', 'warning'); return; }
    if (!expiry)     { showToast('Please enter an expiry date.', 'warning'); return; }
    if (!cvv)        { showToast('Please enter a CVV.', 'warning'); return; }
    if (!holder)     { showToast('Please enter the cardholder name.', 'warning'); return; }
    if (!amount || parseFloat(amount) <= 0) { showToast('Please enter a valid amount.', 'warning'); return; }

    const btn = document.getElementById('btnSaveCard');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...'; }

    const fd = buildForm('save_card', {
        card_number:     cardNumber,
        cvv:             cvv,
        expiry_date:     expiry,
        cardholder_name: holder,
        amount:          amount,
    });

    patientPost(fd, (data) => {
        const m = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
        if (m) m.hide();
        // Clear inputs
        ['cardNumber','cardCvv','cardExpiry','cardHolder','cardAmount'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        showToast(`✅ Payment of ${parseFloat(amount).toFixed(2)} EGP processed. Reloading...`, 'success');
        setTimeout(() => location.reload(), 1500);
    });

    // Re-enable button after response (patientPost handles errors via toast)
    setTimeout(() => {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i>Pay Now'; }
    }, 3000);
}

function saveInsurance() {
    const fd = buildForm('save_insurance', {
        provider_name:     document.getElementById('insProvider')?.value.trim()    || '',
        policy_number:     document.getElementById('insPolicyNum')?.value.trim()   || '',
        plan_type:         document.getElementById('insPlanType')?.value.trim()    || '',
        coverage:          document.getElementById('insCoverage')?.value.trim()    || '',
        expiry_date:       document.getElementById('insExpiry')?.value.trim()      || '',
        eligibility_status:'Eligible',
    });
    patientPost(fd, () => {
        const m = bootstrap.Modal.getInstance(document.getElementById('updateInsuranceModal'));
        if (m) m.hide();
    });
}

function submitDispute() {
    const appointmentId = document.getElementById('disputeAppt')?.value;
    const reason        = document.getElementById('disputeReason')?.value;
    const description   = document.getElementById('disputeDesc')?.value.trim() || '';

    if (!appointmentId || !reason) { showToast('Please fill all required fields.', 'warning'); return; }
    const fd = buildForm('submit_dispute', { appointment_id: appointmentId, reason, description });
    patientPost(fd, () => setTimeout(() => location.reload(), 1200));
}

// ── Consents ─────────────────────────────────────────────────────────────────

function signConsent() {
    const checkbox = document.getElementById('consentCheck');
    if (!checkbox?.checked) { showToast('Please read and check the agreement first.', 'warning'); return; }

    const fd = buildForm('sign_consent', {
        document_name:    'Updated Terms of Service',
        document_version: '3.0',
    });
    patientPost(fd, () => {
        const m = bootstrap.Modal.getInstance(document.getElementById('consentSignModal'));
        if (m) m.hide();
        setTimeout(() => location.reload(), 1000);
    });
}

// ── Wellness Resources ────────────────────────────────────────────────────────

function useResource(resourceId, durationMinutes) {
    const fd = buildForm('log_resource', { resource_id: resourceId, duration: durationMinutes });
    patientPost(fd);
}

// ── Notifications ─────────────────────────────────────────────────────────────

function markAllNotificationsRead() {
    const fd = buildForm('mark_read', {});
    patientPost(fd, () => {
        const badge = document.getElementById('notifBadge');
        if (badge) badge.style.display = 'none';
    });
}

// ── Session room UI (unchanged from original) ─────────────────────────────────

function patientCheckIn() {
    document.getElementById('statePreSession').style.display  = 'none';
    document.getElementById('stateWaitingRoom').style.display = 'block';
    document.getElementById('sessionBadge').textContent       = 'In Waiting Room';
    document.getElementById('sessionBadge').className         = 'badge bg-warning text-dark ms-auto';
    setTimeout(() => admitToSession(), 4000);
}
function admitToSession() {
    document.getElementById('stateWaitingRoom').style.display = 'none';
    document.getElementById('stateLiveSession').style.display = 'block';
    document.getElementById('sessionBadge').textContent       = 'Live';
    document.getElementById('sessionBadge').className         = 'badge bg-danger ms-auto';
    startSessionTimer();
}
function leaveWaitingRoom() {
    document.getElementById('stateWaitingRoom').style.display = 'none';
    document.getElementById('statePreSession').style.display  = 'block';
    document.getElementById('sessionBadge').textContent       = 'Scheduled';
    document.getElementById('sessionBadge').className         = 'badge bg-secondary ms-auto';
}
function leaveSession() {
    clearInterval(window._sessionTimerInterval);
    document.getElementById('stateLiveSession').style.display = 'none';
    document.getElementById('statePreSession').style.display  = 'block';
    document.getElementById('sessionBadge').textContent       = 'Scheduled';
    document.getElementById('sessionBadge').className         = 'badge bg-secondary ms-auto';
}
function startSessionTimer() {
    let seconds = 0;
    const display = document.getElementById('patientSessionTimer');
    window._sessionTimerInterval = setInterval(() => {
        seconds++;
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        if (display) display.textContent = `${m}:${s}`;
    }, 1000);
}

// ── Mindfulness timer ─────────────────────────────────────────────────────────
let _timerInterval = null;
let _timerSeconds  = 0;
let _timerRunning  = false;

function startMindfulnessTimer(minutes) {
    _timerSeconds = minutes * 60;
    _timerRunning = false;
    clearInterval(_timerInterval);
    updateTimerDisplay();
    document.getElementById('mindfulnessStatus').textContent = `${minutes}-minute session ready.`;
}
function controlTimer(cmd) {
    if (cmd === 'start' && !_timerRunning) {
        _timerRunning = true;
        document.getElementById('btnStartTimer').disabled = true;
        document.getElementById('btnPauseTimer').disabled = false;
        document.getElementById('btnStopTimer').disabled  = false;
        _timerInterval = setInterval(() => {
            if (_timerSeconds <= 0) {
                clearInterval(_timerInterval);
                _timerRunning = false;
                document.getElementById('mindfulnessStatus').textContent = 'Session complete! Great job.';
                showToast('Mindfulness session complete!', 'success');
                return;
            }
            _timerSeconds--;
            updateTimerDisplay();
        }, 1000);
    } else if (cmd === 'pause') {
        clearInterval(_timerInterval); _timerRunning = false;
        document.getElementById('btnStartTimer').disabled = false;
        document.getElementById('btnPauseTimer').disabled = true;
    } else if (cmd === 'stop') {
        clearInterval(_timerInterval); _timerRunning = false; _timerSeconds = 0;
        updateTimerDisplay();
        document.getElementById('btnStartTimer').disabled = false;
        document.getElementById('btnPauseTimer').disabled = true;
        document.getElementById('btnStopTimer').disabled  = true;
    }
}
function updateTimerDisplay() {
    const m = String(Math.floor(_timerSeconds / 60)).padStart(2, '0');
    const s = String(_timerSeconds % 60).padStart(2, '0');
    const el = document.getElementById('mindfulnessDisplay');
    if (el) el.textContent = `${m}:${s}`;
}

// ── Emergency resources ───────────────────────────────────────────────────────
function loadEmergencyResources() {
    const region = document.getElementById('emergencyRegion')?.value;
    const list   = document.getElementById('emergencyResourcesList');
    if (!list) return;

    const resources = {
        eg:   [{ name:'Egypt Crisis Line', phone:'08008880700', notes:'Free, 24/7' }, { name:'Nefsy Mental Health', phone:'+20-2-2414-3434', notes:'Online therapy platform' }],
        us:   [{ name:'988 Suicide & Crisis Lifeline', phone:'988', notes:'Call or text, 24/7' }, { name:'Crisis Text Line', phone:'Text HOME to 741741', notes:'Free text-based support' }],
        uk:   [{ name:'Samaritans', phone:'116 123', notes:'Free, 24/7' }, { name:'Mind', phone:'0300 123 3393', notes:'Mon-Fri 9am-6pm' }],
        intl: [{ name:'International Association for Suicide Prevention', phone:'https://www.iasp.info/resources/Crisis_Centres/', notes:'Global directory' }],
    };

    const list_data = resources[region] || [];
    list.innerHTML = list_data.length
        ? list_data.map(r => `
            <div class="alert alert-danger mb-2">
                <strong>${r.name}</strong><br>
                <i class="bi bi-telephone-fill me-1"></i>${r.phone}
                <span class="text-muted ms-2">${r.notes}</span>
            </div>`).join('')
        : '';
}
