/**
 * Admin Portal Specific JavaScript
 * Handles main and alternative scenarios for Admin tasks.
 */

document.addEventListener('DOMContentLoaded', () => {

    // 1. Patient Status Transition
    const updateStatusBtn = document.getElementById('updateStatusBtn');
    if (updateStatusBtn) {
        updateStatusBtn.addEventListener('click', () => {
            const currentStatus = document.getElementById('currentStatus').value; // Hidden or tracked state
            const selectedStatus = document.getElementById('newStatusSelect').value;
            
            // Define valid transitions (simplified)
            // Sequence: registered -> screened -> matched -> active
            const statusOrder = { 'registered': 1, 'screened': 2, 'matched': 3, 'active': 4 };
            
            const currentOrder = statusOrder[currentStatus];
            const nextOrder = statusOrder[selectedStatus];

            if (nextOrder === currentOrder + 1) {
                // Main Scenario: Valid transition
                showToast(`Patient status successfully updated to ${selectedStatus.charAt(0).toUpperCase() + selectedStatus.slice(1)}.`, 'success');
                // Simulate UI update
                document.getElementById('currentStatusBadge').textContent = selectedStatus.charAt(0).toUpperCase() + selectedStatus.slice(1);
                document.getElementById('currentStatus').value = selectedStatus;
            } else if (nextOrder === currentOrder) {
                showToast('Status is already set to ' + selectedStatus, 'error');
            } else {
                // Alternative Scenario: Illegal jump
                showToast(`Error: Illegal status transition. Cannot jump from ${currentStatus} to ${selectedStatus}.`, 'error');
            }
        });
    }

    // 2. Intake PDF Upload
    const uploadIntakeBtn = document.getElementById('uploadIntakeBtn');
    if (uploadIntakeBtn) {
        uploadIntakeBtn.addEventListener('click', () => {
            const fileInput = document.getElementById('intakeFile');
            const file = fileInput.files[0];

            if (!file) {
                showToast('Please select a file to upload.', 'error');
                return;
            }

            const fileName = file.name;
            const fileExtension = fileName.split('.').pop().toLowerCase();

            if (fileExtension === 'pdf') {
                // Main Scenario: Uploads intake PDF
                showToast('Intake document successfully uploaded.', 'success');
                // Reset input
                fileInput.value = '';
            } else {
                // Alternative Scenario: Corrupted/unsupported file
                showToast('Error: Unsupported file format. Please upload a valid PDF.', 'error');
            }
        });
    }

    // 3. Verify Therapist Licenses
    const verifyLicenseBtns = document.querySelectorAll('.verify-license-btn');
    verifyLicenseBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const therapistId = e.target.getAttribute('data-id');
            const fileInput = document.getElementById(`licenseFile_${therapistId}`);
            const isExpired = e.target.getAttribute('data-expired') === 'true';

            if (isExpired) {
                if (fileInput && fileInput.files.length > 0) {
                    // Main Scenario: Uploads/updates license and verifies
                    showToast('License documentation updated and therapist verified.', 'success');
                    e.target.parentElement.innerHTML = '<span class="badge bg-success">Verified</span>';
                } else {
                    // Alternative Scenario: Leaves expired license unupdated
                    showToast('Error: Cannot verify an expired license without uploading new documentation.', 'error');
                }
            } else {
                showToast('Therapist verified successfully.', 'success');
                e.target.parentElement.innerHTML = '<span class="badge bg-success">Verified</span>';
            }
        });
    });

    // 4. Role-Based Access Permissions (RBAC)
    const saveRoleBtn = document.getElementById('saveRoleBtn');
    if (saveRoleBtn) {
        saveRoleBtn.addEventListener('click', () => {
            const selectedRoles = Array.from(document.getElementById('roleSelect').selectedOptions).map(opt => opt.value);
            
            // Check for conflicting roles (e.g. Patient and Admin)
            if (selectedRoles.includes('patient') && (selectedRoles.includes('admin') || selectedRoles.includes('therapist'))) {
                // Alternative Scenario: Attempts to assign forbidden combo
                showToast('Error: Forbidden role combination. A Patient cannot be assigned Admin or Therapist roles.', 'error');
            } else if (selectedRoles.length === 0) {
                 showToast('Error: Please select at least one role.', 'error');
            } else {
                // Main Scenario: Assigns or changes roles
                showToast('Role permissions updated successfully.', 'success');
                // Close modal (simulated via bootstrap API)
                const modalEl = document.getElementById('rbacModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        });
    }

    // 5. Safety Logs Protection
    const logRows = document.querySelectorAll('.safety-log-row');
    logRows.forEach(row => {
        // Prevent double click editing or deleting
        row.addEventListener('dblclick', () => {
            // Alternative Scenario: Attempt to modify log
            showToast('Security Alert: Safety logs are immutable and cannot be modified or deleted.', 'error');
        });
    });
    
    const deleteLogBtns = document.querySelectorAll('.delete-log-btn');
    deleteLogBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
             e.preventDefault();
             // Alternative Scenario: Attempt to delete log
             showToast('Security Alert: Safety logs are immutable and cannot be deleted.', 'error');
        });
    });

});
