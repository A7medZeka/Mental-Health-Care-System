<?php
session_start();
$error_message   = $_SESSION['error_message']   ?? '';
$success_message = $_SESSION['success_message'] ?? '';
$active_form     = $_SESSION['active_form']     ?? '';
unset($_SESSION['error_message'], $_SESSION['success_message'], $_SESSION['active_form']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Mental Health Care</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* Toggle password visibility */
        .input-icon-group {
            position: relative;
        }
        .input-icon-group .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--light-brown);
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
        }
        .input-icon-group .form-control {
            padding-right: 40px;
        }

        /* Password strength */
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e9ecef;
            overflow: hidden;
            margin-top: 6px;
        }
        .strength-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s ease, background-color 0.3s ease;
            width: 0%;
        }
        .strength-text {
            font-size: 11px;
            margin-top: 4px;
        }
    </style>
</head>
<body class="d-flex align-items-center py-4" style="min-height: 100vh;">

    <div class="container fade-in">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">

                <!-- Logo/Header -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light-green rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="var(--primary-green)" class="bi bi-heart-pulse-fill" viewBox="0 0 16 16">
                            <path d="M1.475 9C2.702 10.84 4.779 12.871 8 15c3.221-2.129 5.298-4.16 6.525-6H12a.5.5 0 0 1-.464-.314l-1.457-3.642-1.598 5.593a.5.5 0 0 1-.945.049L5.889 6.568l-1.473 2.21A.5.5 0 0 1 4 9H1.475Z"/>
                            <path d="M.88 8C-2.427 1.68 4.41-2 7.823 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C11.59-2 18.426 1.68 15.12 8h-2.783l-1.874-4.686a.5.5 0 0 0-.945.049L7.921 8.956 6.464 5.314a.5.5 0 0 0-.88-.091L3.732 8H.88Z"/>
                        </svg>
                    </div>
                    <h2 class="fw-bold text-primary-custom">Reset Password</h2>
                    <p class="text-secondary-custom">Enter your details below to reset your password</p>
                </div>

                <!-- Reset Card -->
                <div class="card card-custom p-4 p-md-5">

                    <form action="handleForms.php" id="forgotPasswordForm" method="POST">
                        <!-- //! action = reset_password -->
                        <input type="hidden" name="action" value="reset_password">

                        <!-- Email or Phone -->
                        <div class="mb-3">
                            <label for="emailOrPhone" class="form-label text-secondary-custom">Email address or Phone number</label>
                            <input
                                type="text"
                                class="form-control py-2"
                                id="emailOrPhone"
                                name="email_or_phone"
                                placeholder="name@example.com or +20 100 000 0000"
                                required
                            >
                            <div class="invalid-feedback">Please enter your email address or phone number.</div>
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="newPassword" class="form-label text-secondary-custom">New Password</label>
                            <div class="input-icon-group">
                                <input
                                    type="password"
                                    class="form-control py-2"
                                    id="newPassword"
                                    name="new_password"
                                    placeholder="••••••••"
                                    required
                                    oninput="checkStrength(this.value)"
                                >
                                <button type="button" class="toggle-pw" onclick="togglePw('newPassword', this)" tabindex="-1">
                                    <i class="bi bi-eye" id="eyeNew" style="font-size:1.1rem;"></i>
                                </button>
                            </div>
                            <!-- Password strength bar -->
                            <div class="strength-bar mt-2">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <div class="strength-text text-secondary-custom" id="strengthText"></div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label text-secondary-custom">Confirm New Password</label>
                            <div class="input-icon-group">
                                <input
                                    type="password"
                                    class="form-control py-2"
                                    id="confirmPassword"
                                    name="confirm_password"
                                    placeholder="••••••••"
                                    required
                                >
                                <button type="button" class="toggle-pw" onclick="togglePw('confirmPassword', this)" tabindex="-1">
                                    <i class="bi bi-eye" id="eyeConfirm" style="font-size:1.1rem;"></i>
                                </button>
                            </div>
                            <div id="passwordMatchMsg" class="mt-1" style="font-size: 12px;"></div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary-custom w-100 py-2 fw-semibold mb-3">
                            <i class="bi bi-shield-lock me-2"></i>Reset Password
                        </button>

                        <div class="text-center">
                            <a href="index.php" class="text-primary-custom text-decoration-none small fw-semibold">
                                <i class="bi bi-arrow-left me-1"></i>Back to Sign In
                            </a>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>

    <script>
        /* ── Toggle Password Visibility ── */
        function togglePw(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        /* ── Password Strength Indicator ── */
        function checkStrength(val) {
            const fill = document.getElementById('strengthFill');
            const text = document.getElementById('strengthText');
            let score = 0;
            if (val.length >= 8)           score++;
            if (/[A-Z]/.test(val))         score++;
            if (/[0-9]/.test(val))         score++;
            if (/[^A-Za-z0-9]/.test(val))  score++;

            const levels = [
                { pct: '0%',   color: '#e9ecef', label: '' },
                { pct: '25%',  color: '#dc3545', label: 'Weak' },
                { pct: '50%',  color: '#fd7e14', label: 'Fair' },
                { pct: '75%',  color: '#ffc107', label: 'Good' },
                { pct: '100%', color: '#2F8F7E', label: 'Strong' },
            ];

            const lvl = val.length === 0 ? levels[0] : levels[score];
            fill.style.width           = lvl.pct;
            fill.style.backgroundColor = lvl.color;
            text.textContent           = lvl.label;
            text.style.color           = lvl.color;
        }

        /* ── Real-time password match indicator ── */
        document.getElementById('confirmPassword').addEventListener('input', function () {
            const pw  = document.getElementById('newPassword').value;
            const msg = document.getElementById('passwordMatchMsg');
            if (!this.value) { msg.textContent = ''; return; }
            if (this.value === pw) {
                msg.textContent = '✓ Passwords match!';
                msg.style.color = '#2F8F7E';
                this.classList.remove('is-invalid');
            } else {
                msg.textContent = '✗ Passwords do not match.';
                msg.style.color = '#dc3545';
            }
        });

        /* ── Client-side validation before POST ── */
        document.getElementById('forgotPasswordForm').addEventListener('submit', function (e) {
            let valid = true;

            const contact = document.getElementById('emailOrPhone');
            if (!contact.value.trim()) {
                contact.classList.add('is-invalid');
                valid = false;
            } else {
                contact.classList.remove('is-invalid');
            }

            const pw  = document.getElementById('newPassword').value;
            const cpw = document.getElementById('confirmPassword').value;
            const msg = document.getElementById('passwordMatchMsg');

            if (!pw) {
                document.getElementById('newPassword').classList.add('is-invalid');
                valid = false;
            }

            if (pw && cpw && pw !== cpw) {
                msg.textContent = '✗ Passwords do not match.';
                msg.style.color = '#dc3545';
                document.getElementById('confirmPassword').classList.add('is-invalid');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
            // If valid, the form POSTs naturally to the backend.
        });

        /* ── Clear invalid state on input ── */
        document.querySelectorAll('.form-control').forEach(el => {
            el.addEventListener('input', () => el.classList.remove('is-invalid'));
        });
    </script>
</body>

<!--
    Variabled
        action = action (hidden, value="reset_password")
        email_or_phone = email_or_phone
        new_password = new_password
        confirm_password = confirm_password
-->
</html>
