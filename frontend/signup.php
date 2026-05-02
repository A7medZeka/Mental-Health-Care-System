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
    <title>Patient Sign Up - MindCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .step-indicator { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 2rem; }
        .step { display: flex; flex-direction: column; align-items: center; position: relative; }
        .step-circle {
            width: 36px; height: 36px; border-radius: 50%; background-color: var(--light-green);
            color: var(--primary-green); font-weight: 700; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--primary-green); transition: all 0.3s ease; z-index: 1;
        }
        .step-circle.active { background-color: var(--primary-green); color: white; box-shadow: 0 4px 12px rgba(47,143,126,0.3); }
        .step-circle.completed { background-color: var(--primary-green); color: white; }
        .step-label { font-size: 11px; font-weight: 500; color: var(--light-brown); margin-top: 6px; white-space: nowrap; }
        .step.active .step-label { color: var(--primary-green); font-weight: 600; }
        .step-connector { width: 80px; height: 2px; background-color: #d9ece8; margin: 0 4px; margin-bottom: 22px; transition: background-color 0.3s ease; }
        .step-connector.completed { background-color: var(--primary-green); }
        .form-step { display: none; }
        .form-step.active { display: block; animation: fadeIn 0.4s ease; }
        .strength-bar { height: 4px; border-radius: 2px; background: #e9ecef; overflow: hidden; margin-top: 6px; }
        .strength-fill { height: 100%; border-radius: 2px; transition: width 0.3s ease, background-color 0.3s ease; width: 0%; }
        .strength-text { font-size: 11px; margin-top: 4px; }
        .input-icon-group { position: relative; }
        .input-icon-group .toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--light-brown); background: none; border: none; padding: 0; line-height: 1; }
        .input-icon-group .form-control { padding-right: 40px; }
        .form-check-input:checked { background-color: var(--primary-green); border-color: var(--primary-green); }
        .form-check-input:focus { border-color: var(--secondary-green); box-shadow: 0 0 0 0.2rem rgba(47,143,126,0.2); }
        .btn-back { background: none; border: 1.5px solid #d0e9e4; color: var(--light-brown); border-radius: 8px; padding: 9px 20px; font-weight: 500; transition: all 0.2s ease; }
        .btn-back:hover { border-color: var(--primary-green); color: var(--primary-green); background: var(--light-green); }
    </style>
</head>
<body class="d-flex align-items-center py-5" style="min-height: 100vh;">

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
                <h2 class="fw-bold text-primary-custom">Create Patient Account</h2>
                <p class="text-secondary-custom">Join our mental health community</p>
            </div>

            <!-- Session Messages -->
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Step Indicator -->
            <div class="step-indicator" id="stepIndicator">
                <div class="step" data-step="1">
                    <div class="step-circle active" id="circle-1">1</div>
                    <span class="step-label active" id="label-1">Details</span>
                </div>
                <div class="step-connector" id="connector-1"></div>
                <div class="step" data-step="2">
                    <div class="step-circle" id="circle-2">2</div>
                    <span class="step-label" id="label-2">Security</span>
                </div>
            </div>

            <!-- Sign Up Card -->
            <div class="card card-custom p-4 p-md-5">

                <!-- FIXED: added method="POST" so form data reaches handleForms.php -->
                <form action="handleForms.php" method="POST" id="signupForm" novalidate>
                    <input type="hidden" name="action" value="register">

                    <!-- STEP 1: Personal Details -->
                    <div class="form-step active" id="step1">
                        <h6 class="fw-semibold text-secondary-custom mb-3">Personal Information</h6>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="firstName" class="form-label text-secondary-custom">First Name</label>
                                <input type="text" class="form-control py-2" id="firstName" name="firstName" placeholder="John" required>
                                <div class="invalid-feedback">Please enter your first name.</div>
                            </div>
                            <div class="col-6">
                                <label for="lastName" class="form-label text-secondary-custom">Last Name</label>
                                <input type="text" class="form-control py-2" id="lastName" name="lastName" placeholder="Doe" required>
                                <div class="invalid-feedback">Please enter your last name.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="signupEmail" class="form-label text-secondary-custom">Email Address</label>
                            <input type="email" class="form-control py-2" id="signupEmail" name="signupEmail" placeholder="name@example.com" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label for="nationalID" class="form-label text-secondary-custom">National ID</label>
                            <input type="text" class="form-control py-2" id="nationalID" name="nationalID" placeholder="Enter your National ID" required>
                            <div class="invalid-feedback">Please enter your National ID.</div>
                        </div>

                        <div class="mb-3">
                            <label for="city" class="form-label text-secondary-custom">City</label>
                            <input type="text" class="form-control py-2" id="city" name="city" placeholder="e.g. Cairo" required>
                            <div class="invalid-feedback">Please enter your city.</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="phone" class="form-label text-secondary-custom">Phone <span class="text-muted">(optional)</span></label>
                                <input type="tel" class="form-control py-2" id="phone" name="phone" placeholder="+20 xxx xxx xxxx">
                            </div>
                            <div class="col-6">
                                <label for="dob" class="form-label text-secondary-custom">Date of Birth</label>
                                <input type="date" class="form-control py-2" id="dob" name="dob" required>
                                <div class="invalid-feedback">Please select your date of birth.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="gender" class="form-label text-secondary-custom">Gender</label>
                            <select class="form-select py-2" id="gender" name="gender" required>
                                <option value="" disabled selected>Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="prefer_not">Prefer not to say</option>
                            </select>
                            <div class="invalid-feedback">Please select your gender.</div>
                        </div>

                        <button type="button" class="btn btn-primary-custom w-100 py-2 fw-semibold" onclick="validateStep1()">
                            Continue
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- STEP 2: Security -->
                    <div class="form-step" id="step2">
                        <h6 class="fw-semibold text-secondary-custom mb-3">Set Up Your Password</h6>

                        <div class="mb-3">
                            <label for="signupPassword" class="form-label text-secondary-custom">Password</label>
                            <div class="input-icon-group">
                                <input type="password" class="form-control py-2" id="signupPassword" name="signupPassword" placeholder="••••••••" required oninput="checkStrength(this.value)">
                                <button type="button" class="toggle-pw" onclick="togglePw('signupPassword', this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="eye-icon" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                            <div class="strength-text" id="strengthText"></div>
                        </div>

                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label text-secondary-custom">Confirm Password</label>
                            <div class="input-icon-group">
                                <input type="password" class="form-control py-2" id="confirmPassword" name="confirmPassword" placeholder="••••••••" required>
                                <button type="button" class="toggle-pw" onclick="togglePw('confirmPassword', this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="eye-icon" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                                    </svg>
                                </button>
                            </div>
                            <div id="passwordMatchMsg" class="small mt-1"></div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" name="agreeTerms">
                            <label class="form-check-label text-secondary-custom small" for="agreeTerms">
                                I agree to the <a href="#" class="text-primary-custom">Terms of Service</a> and <a href="#" class="text-primary-custom">Privacy Policy</a>
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-back" onclick="goToStep(1)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                                </svg>
                                Back
                            </button>
                            <button type="submit" class="btn btn-primary-custom flex-grow-1 py-2 fw-semibold">
                                Create Account
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1" viewBox="0 0 16 16">
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                </form>

                <div class="text-center mt-4">
                    <p class="text-secondary-custom mb-0 small">Already have an account? <a href="index.php" class="text-primary-custom text-decoration-none fw-semibold">Sign In</a></p>
                    <p class="text-secondary-custom mb-0 small mt-2">Are you a therapist? <a href="therapist-register.php" class="text-accent text-decoration-none fw-semibold">Register here</a></p>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
    function goToStep(step) {
        document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
        document.getElementById('step' + step).classList.add('active');
        for (let i = 1; i <= 2; i++) {
            const circle = document.getElementById('circle-' + i);
            const label  = document.getElementById('label-' + i);
            circle.classList.remove('active', 'completed');
            label.classList.remove('active');
            if (i < step) {
                circle.classList.add('completed');
                circle.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="white" viewBox="0 0 16 16"><path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/></svg>';
            } else if (i === step) {
                circle.classList.add('active');
                circle.textContent = i;
                label.classList.add('active');
            } else {
                circle.textContent = i;
            }
        }
        document.getElementById('connector-1').classList.toggle('completed', step > 1);
    }

    function validateStep1() {
        const requiredFields = ['firstName', 'lastName', 'signupEmail', 'nationalID', 'city', 'dob', 'gender'];
        let valid = true;
        requiredFields.forEach(id => {
            const el = document.getElementById(id);
            if (!el.value.trim()) { el.classList.add('is-invalid'); valid = false; }
            else { el.classList.remove('is-invalid'); }
        });
        const email = document.getElementById('signupEmail');
        if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            email.classList.add('is-invalid'); valid = false;
        }
        if (valid) goToStep(2);
    }

    function checkStrength(val) {
        const fill = document.getElementById('strengthFill');
        const text = document.getElementById('strengthText');
        let score = 0;
        if (val.length >= 8)           score++;
        if (/[A-Z]/.test(val))         score++;
        if (/[0-9]/.test(val))         score++;
        if (/[^A-Za-z0-9]/.test(val))  score++;
        const levels = [
            { pct:'0%',   color:'#e9ecef', label:'' },
            { pct:'25%',  color:'#dc3545', label:'Weak' },
            { pct:'50%',  color:'#fd7e14', label:'Fair' },
            { pct:'75%',  color:'#ffc107', label:'Good' },
            { pct:'100%', color:'#2F8F7E', label:'Strong' },
        ];
        const lvl = val.length === 0 ? levels[0] : levels[score];
        fill.style.width           = lvl.pct;
        fill.style.backgroundColor = lvl.color;
        text.textContent           = lvl.label;
        text.style.color           = lvl.color;
    }

    function togglePw(inputId, btn) {
        const input  = document.getElementById(inputId);
        const isText = input.type === 'text';
        input.type   = isText ? 'password' : 'text';
        btn.querySelector('.eye-icon').style.opacity = isText ? '1' : '0.4';
    }

    // FIXED: validate then actually POST — no JS redirect
    document.getElementById('signupForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const pw  = document.getElementById('signupPassword').value;
        const cpw = document.getElementById('confirmPassword').value;
        const msg = document.getElementById('passwordMatchMsg');

        if (pw !== cpw) {
            msg.textContent = '✗ Passwords do not match.';
            msg.style.color = '#dc3545';
            document.getElementById('confirmPassword').classList.add('is-invalid');
            return;
        }
        msg.textContent = '✓ Passwords match!';
        msg.style.color = '#2F8F7E';
        document.getElementById('confirmPassword').classList.remove('is-invalid');

        if (!document.getElementById('agreeTerms').checked) {
            showToast('Please agree to the Terms of Service.', 'error');
            return;
        }

        // ✅ All checks passed — submit the form to handleForms.php
        this.submit();
    });

    document.getElementById('confirmPassword').addEventListener('input', function() {
        const pw  = document.getElementById('signupPassword').value;
        const msg = document.getElementById('passwordMatchMsg');
        if (!this.value) { msg.textContent = ''; return; }
        if (this.value === pw) {
            msg.textContent = '✓ Passwords match!'; msg.style.color = '#2F8F7E';
            this.classList.remove('is-invalid');
        } else {
            msg.textContent = '✗ Passwords do not match.'; msg.style.color = '#dc3545';
        }
    });

    document.querySelectorAll('.form-control, .form-select').forEach(el => {
        el.addEventListener('input', () => el.classList.remove('is-invalid'));
    });
</script>
</body>
</html>