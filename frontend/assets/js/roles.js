/**
 * Roles Specific JavaScript
 * Handles main and alternative scenarios for Patient, Therapist, and Moderator tasks.
 */

document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // PATIENT SCENARIOS
    // ==========================================

    // UC-1: Determine Level of Care (Intake)
    const submitIntakeBtn = document.getElementById('submitIntakeBtn');
    if (submitIntakeBtn) {
        submitIntakeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const symptomScore = document.getElementById('symptomScore').value;
            if (!symptomScore) {
                // Alternative Scenario: Leaves mandatory fields empty
                showToast('Error: Please answer all mandatory questions.', 'error');
            } else {
                // Main Scenario: Computes Level of Care
                showToast('Intake complete. Level of Care calculated successfully.', 'success');
                document.getElementById('intakeStatusBadge').textContent = 'Completed';
                document.getElementById('intakeStatusBadge').className = 'badge bg-success';
            }
        });
    }

    // UC-2: Match Patient with Therapist
    const requestMatchBtn = document.getElementById('requestMatchBtn');
    if (requestMatchBtn) {
        requestMatchBtn.addEventListener('click', () => {
            const prefLanguage = document.getElementById('prefLanguage').value;
            // Simulated matching logic
            if (prefLanguage === 'rare_language') {
                // Alternative Scenario: No therapist meets criteria
                showToast('No therapists currently available matching your exact criteria. Adding to waitlist.', 'error');
            } else {
                // Main Scenario: Finds match
                showToast('Match found! Meet your new therapist.', 'success');
                document.getElementById('matchedTherapistCard').classList.remove('d-none');
            }
        });
    }

    // UC-21: Track Wellness Goals
    const addGoalBtn = document.getElementById('addGoalBtn');
    if (addGoalBtn) {
        addGoalBtn.addEventListener('click', () => {
            const goalDesc = document.getElementById('goalDesc').value;
            if (goalDesc.length < 3) {
                // Alternative Scenario: Invalid target
                showToast('Goal must be descriptive and realistic.', 'error');
            } else {
                // Main Scenario: Creates goal
                showToast('Wellness goal added successfully.', 'success');
                // Simulate adding to list
                const ul = document.getElementById('goalsList');
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `${goalDesc} <span class="badge bg-primary-custom rounded-pill">0%</span>`;
                ul.appendChild(li);
                document.getElementById('goalDesc').value = '';
            }
        });
    }

    // UC-22: Manage Journal Entry Privacy
    const saveJournalBtn = document.getElementById('saveJournalBtn');
    if (saveJournalBtn) {
        saveJournalBtn.addEventListener('click', () => {
            const isPrivate = document.getElementById('privacyToggle').checked;
            if (isPrivate) {
                showToast('Journal entry saved privately.', 'success');
            } else {
                showToast('Journal entry saved and shared with your therapist.', 'success');
            }
        });
    }

    // UC-24: Run Mindfulness Session Timer
    const startTimerBtn = document.getElementById('startTimerBtn');
    let timerInterval;
    if (startTimerBtn) {
        startTimerBtn.addEventListener('click', () => {
            const timerDisplay = document.getElementById('timerDisplay');
            if (startTimerBtn.textContent.includes('Start')) {
                // Main Scenario: Start Timer
                startTimerBtn.textContent = 'Pause Session';
                startTimerBtn.classList.replace('btn-primary-custom', 'btn-warning');
                let time = 600; // 10 mins
                timerInterval = setInterval(() => {
                    time--;
                    let m = Math.floor(time / 60);
                    let s = time % 60;
                    timerDisplay.textContent = `${m}:${s < 10 ? '0' : ''}${s}`;
                    if (time <= 0) {
                        clearInterval(timerInterval);
                        showToast('Session complete! Great job.', 'success');
                    }
                }, 1000);
            } else {
                // Alternative Scenario: Pause
                clearInterval(timerInterval);
                startTimerBtn.textContent = 'Start Session';
                startTimerBtn.classList.replace('btn-warning', 'btn-primary-custom');
                showToast('Session paused.', 'success');
            }
        });
    }

    // ==========================================
    // THERAPIST SCENARIOS
    // ==========================================

    // UC-15: Manage Virtual Waiting Room
    const admitPatientBtns = document.querySelectorAll('.admit-btn');
    admitPatientBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Main Scenario: Admits patient to live session
            const patientName = e.target.getAttribute('data-name');
            showToast(`${patientName} admitted to Live Session.`, 'success');
            e.target.parentElement.parentElement.remove(); // Remove from waiting list
        });
    });

    // UC-16: Manage Clinical Note Versioning
    const saveNoteBtn = document.getElementById('saveNoteBtn');
    if (saveNoteBtn) {
        saveNoteBtn.addEventListener('click', () => {
            // Main Scenario: Creates append-only version
            showToast('Clinical note saved as a new immutable version.', 'success');
        });
    }
    const editPastNoteBtns = document.querySelectorAll('.edit-past-note-btn');
    editPastNoteBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Alternative Scenario: Attempts to edit past version
            showToast('Security Alert: Past note versions are immutable and cannot be edited.', 'error');
        });
    });

    // UC-20: Control Access to Sensitive Content
    const toggleContentBtns = document.querySelectorAll('.toggle-content-btn');
    toggleContentBtns.forEach(btn => {
        btn.addEventListener('change', (e) => {
            // Main Scenario: Toggles content access
            if (e.target.checked) {
                showToast('Resource access granted to patient.', 'success');
            } else {
                showToast('Resource access revoked from patient.', 'success');
            }
        });
    });


    // ==========================================
    // MODERATOR SCENARIOS
    // ==========================================

    // UC-31: Moderate Community Forum Content
    const moderateActionBtns = document.querySelectorAll('.mod-action-btn');
    moderateActionBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const action = e.target.getAttribute('data-action');
            const row = e.target.closest('tr');
            
            if (action === 'delete') {
                // Main Scenario: Delete flagged post
                showToast('Post permanently deleted.', 'success');
                row.remove();
            } else if (action === 'hide') {
                // Main Scenario: Hide flagged post
                showToast('Post hidden from public view.', 'success');
                row.querySelector('.badge').className = 'badge bg-secondary';
                row.querySelector('.badge').textContent = 'Hidden';
            } else if (action === 'review') {
                // Alternative Scenario: Mark for further review
                showToast('Post marked as Under Review.', 'success');
                row.querySelector('.badge').className = 'badge bg-warning text-dark';
                row.querySelector('.badge').textContent = 'Under Review';
            }
        });
    });

});
