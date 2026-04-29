/**
 * Therapist Specific JavaScript
 * Simulates the Controllers, Services, and Repositories defined in the Sequence Diagrams
 * (UC-13, UC-14, UC-15, UC-16, UC-17, UC-19, UC-20, UC-27)
 */

document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // UC-14: Send Session Reminders (Dashboard)
    // ==========================================
    const reminderNotification = document.getElementById('reminderNotification');
    if (reminderNotification) {
        console.log("ReminderController: receiveReminder() triggered.");
    }

    // ==========================================
    // UC-17: Handle Patient No-Show for High-Risk Case (Dashboard)
    // ==========================================
    const btnLogAction = document.getElementById('btnLogAction');
    const incidentUI = document.getElementById('incidentDashboardUI');
    
    if (btnLogAction) {
        btnLogAction.addEventListener('click', () => {
            const notes = document.getElementById('welfareNotes').value;
            if(!notes) {
                showToast('Validation Error: Please provide notes for the welfare action.', 'error');
                return;
            }
            showToast('submitIncidentReport(): Action logged to IncidentRepository.', 'success');
            if (incidentUI) incidentUI.remove();
            const badge = document.getElementById('statusBadge_PT1055');
            if (badge) badge.textContent = 'Incident Logged';
        });
    }

    const btnPatientLate = document.getElementById('btnPatientLate');
    if (btnPatientLate) {
        btnPatientLate.addEventListener('click', () => {
            showToast('overrideStatus("Late"): Patient status updated to Late.', 'success');
            if (incidentUI) incidentUI.remove();
            const badge = document.getElementById('statusBadge_PT1055');
            if (badge) {
                badge.className = 'badge bg-warning text-dark';
                badge.textContent = 'Late';
            }
        });
    }

    const btnFalseAlarm = document.getElementById('btnFalseAlarm');
    if (btnFalseAlarm) {
        btnFalseAlarm.addEventListener('click', () => {
            showToast('closeIncident(): Incident closed as false alarm.', 'success');
            if (incidentUI) incidentUI.remove();
        });
    }

    // ==========================================
    // UC-15 & UC-13: Manage Virtual Waiting Room & Session Check-In (Sessions Page)
    // ==========================================
    const btnAdmitSession = document.getElementById('btnAdmitSession');
    const waitingRoomSection = document.getElementById('waitingRoomSection');
    const liveSessionSection = document.getElementById('liveSessionSection');
    const liveIndicator = document.getElementById('liveIndicator');
    const sessionStatusBadge = document.getElementById('sessionStatusBadge');
    let sessionTimer;

    if(waitingRoomSection) {
        setTimeout(() => {
            const alert = document.getElementById('patientArrivedAlert');
            if(alert) alert.style.display = 'block';
        }, 1000);
    }

    if (btnAdmitSession) {
        btnAdmitSession.addEventListener('click', () => {
            waitingRoomSection.style.display = 'none';
            liveSessionSection.style.display = 'block';
            if (liveIndicator) liveIndicator.classList.remove('d-none');
            if (sessionStatusBadge) {
                sessionStatusBadge.className = 'badge bg-danger';
                sessionStatusBadge.textContent = 'Session Live';
            }
            showToast('startVideoConnection(): Live session started successfully.', 'success');

            let seconds = 0;
            const timerEl = document.getElementById('liveTimer');
            sessionTimer = setInterval(() => {
                seconds++;
                let m = Math.floor(seconds / 60);
                let s = seconds % 60;
                if(timerEl) timerEl.textContent = `${m < 10 ? '0':''}${m}:${s < 10 ? '0':''}${s}`;
            }, 1000);
        });
    }

    const btnEndSession = document.getElementById('btnEndSession');
    const btnFinalizeSession = document.getElementById('btnFinalizeSession');

    if (btnEndSession) {
        btnEndSession.addEventListener('click', () => {
            clearInterval(sessionTimer);
            if (liveIndicator) liveIndicator.classList.add('d-none');
            btnEndSession.disabled = true;
            if(btnFinalizeSession) btnFinalizeSession.disabled = false;
            btnFinalizeSession.classList.replace('btn-secondary', 'btn-primary-custom');
            showToast('endSession(): Video connection terminated.', 'success');
        });
    }

    if (btnFinalizeSession) {
        btnFinalizeSession.addEventListener('click', () => {
            showToast('finalizeSession(): Transitioned to Completed. triggerBillingWorkflow() initiated.', 'success');
            if (sessionStatusBadge) {
                sessionStatusBadge.className = 'badge bg-success';
                sessionStatusBadge.textContent = 'Completed & Billed';
            }
            btnFinalizeSession.disabled = true;
        });
    }

    // ==========================================
    // UC-16: Manage Clinical Note Versioning (Sessions Page)
    // ==========================================
    const btnSaveNote = document.getElementById('btnSaveNote');
    const noteContent = document.getElementById('noteContent');
    const noteHistoryContainer = document.getElementById('noteHistoryContainer');
    let versionCounter = 2;

    if (btnSaveNote) {
        btnSaveNote.addEventListener('click', () => {
            const content = noteContent.value.trim();
            if(!content) return;
            const now = new Date();
            const timeString = `${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2,'0')}-${now.getDate().toString().padStart(2,'0')} ${now.getHours()}:${now.getMinutes().toString().padStart(2,'0')}`;
            const newNoteHTML = `
                <div class="p-3 mb-2 bg-light rounded border border-start-4 border-start-primary fade-in" id="version_${versionCounter}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-primary-custom">v${versionCounter}.0</strong>
                        <span class="small text-muted font-monospace">${timeString}</span>
                    </div>
                    <p class="mb-2 text-dark small">${content}</p>
                    <div class="text-end">
                        <button class="btn btn-sm btn-outline-danger btn-delete-history" data-version="v${versionCounter}.0">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;
            noteHistoryContainer.insertAdjacentHTML('afterbegin', newNoteHTML);
            noteContent.value = '';
            versionCounter++;
            showToast('createNewVersion(): Note successfully saved as immutable version.', 'success');
            attachDeleteListeners();
        });
    }

    function attachDeleteListeners() {
        const deleteHistoryBtns = document.querySelectorAll('.btn-delete-history');
        deleteHistoryBtns.forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', (e) => {
                const version = e.target.closest('button').getAttribute('data-version');
                showToast(`rejectAction(): Security Error - Cannot delete past versions (${version}). Note history is immutable.`, 'error');
            });
        });
    }
    attachDeleteListeners();

    // ==========================================
    // UC-20: Control Access to Sensitive Content (Patients Page)
    // ==========================================
    const btnSavePermissions = document.getElementById('btnSavePermissions');
    const permissionToggles = document.querySelectorAll('.permission-toggle');
    
    if (btnSavePermissions) {
        btnSavePermissions.addEventListener('click', () => {
            let hasDenied = false;
            permissionToggles.forEach(toggle => {
                if (toggle.checked && toggle.getAttribute('data-resource') === 'trauma_high') {
                    hasDenied = true;
                    toggle.checked = false; // Revert
                }
            });
            if (hasDenied) {
                showToast('validateAction(): Authorization Error - You do not have permission to grant High-Risk Trauma access.', 'error');
            } else {
                showToast('confirmValid(): updateAccessRule() successful.', 'success');
            }
        });
    }

    // ==========================================
    // UC-19: Review Mood Trend Reports (Insights Page)
    // ==========================================
    const btnOpenMoodReport = document.getElementById('btnOpenMoodReport');
    const moodPatientSelect = document.getElementById('moodPatientSelect');
    const moodReportArea = document.getElementById('moodReportArea');
    const btnGenerateTrend = document.getElementById('btnGenerateTrend');
    const chartContainer = document.getElementById('chartContainer');

    if (btnOpenMoodReport) {
        btnOpenMoodReport.addEventListener('click', () => {
            if (moodPatientSelect.value === 'PT-NEW') {
                showToast('showEmptyMessage(): No data yet for this patient.', 'error');
                moodReportArea.style.display = 'none';
            } else {
                showToast('fetchMoodLogs(): Data retrieved.', 'success');
                moodReportArea.style.display = 'block';
            }
        });
    }

    if (btnGenerateTrend) {
        btnGenerateTrend.addEventListener('click', () => {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            if(!start || !end) {
                showToast('Validation Error: Select date range.', 'error');
                return;
            }
            chartContainer.innerHTML = `<h5 class="text-primary-custom fw-bold"><i class="bi bi-graph-up-arrow me-2"></i> renderCharts(): Average Mood +20%</h5><p class="small text-muted mb-0">Simulated Line Chart Data from ${start} to ${end}</p>`;
            chartContainer.classList.add('bg-light-green');
            chartContainer.classList.remove('bg-white');
            showToast('calculateMoodAverages(): TrendStats generated successfully.', 'success');
        });
    }

    // ==========================================
    // UC-27: Correlate Sleep and Mood Patterns (Insights Page)
    // ==========================================
    const btnOpenInsights = document.getElementById('btnOpenInsights');
    const insightPatientSelect = document.getElementById('insightPatientSelect');
    const insightsAnalysisArea = document.getElementById('insightsAnalysisArea');
    const btnAnalyzeCorrelation = document.getElementById('btnAnalyzeCorrelation');
    const correlationResults = document.getElementById('correlationResults');

    if (btnOpenInsights) {
        btnOpenInsights.addEventListener('click', () => {
            if (insightPatientSelect.value === 'PT-LACK') {
                showToast('showIncompleteDataMsg(): Insufficient overlapping data for correlation.', 'error');
                insightsAnalysisArea.style.display = 'none';
            } else {
                showToast('getSleepLogs() & getMoodLogs(): Sufficient data found.', 'success');
                insightsAnalysisArea.style.display = 'block';
            }
        });
    }

    if (btnAnalyzeCorrelation) {
        btnAnalyzeCorrelation.addEventListener('click', () => {
            showToast('calculatePattern(): Correlation results computed.', 'success');
            correlationResults.classList.remove('d-none');
        });
    }

});
