/*
 * Global JavaScript for Mental Health Care Website
 */

//! Toast Notification System
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
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

//! Login Form

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        // Role pill selection — visual only, does NOT affect redirect
        const roleBtns = document.querySelectorAll('.role-pills .nav-link');
        roleBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                roleBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

        // Form submits naturally to handleForms.php via POST
        // No e.preventDefault() here — do NOT add one
    }
});