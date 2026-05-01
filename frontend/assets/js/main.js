/**
 * Global JavaScript for Mental Health Care Website
 */

// Simple Toast Notification System
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;

    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 mb-2`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toastEl);
    
    // Initialize and show toast using Bootstrap's JS API
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();

    // Clean up DOM after hide
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

// Login Handling
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Get selected role
            const activeRoleBtn = document.querySelector('.role-pills .nav-link.active');
            const role = activeRoleBtn ? activeRoleBtn.getAttribute('data-role') : 'patient';
            
            // Simulate Authentication based on role
            // Since this is front-end only, we just redirect.
            if (role === 'admin') {
                window.location.href = 'admin-dashboard.php';
            } else if (role === 'moderator') {
                window.location.href = 'moderator-dashboard.php';
            } else if (role === 'therapist') {
                window.location.href = 'therapist-dashboard.php';
            } else {
                window.location.href = 'patient-dashboard.php';
            }
        });

        // Role pill selection visual logic
        const roleBtns = document.querySelectorAll('.role-pills .nav-link');
        roleBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                roleBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    }
});
