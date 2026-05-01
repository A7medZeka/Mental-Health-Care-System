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
    <title>Sign Up - Mental Health Care</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* Progress Steps */
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 2rem;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--light-green);
            color: var(--primary-green);
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--primary-green);
            transition: all 0.3s ease;
            z-index: 1;
        }
        .step-circle.active {
            background-color: var(--primary-green);
            color: white;
            box-shadow: 0 4px 12px rgba(47, 143, 126, 0.3);
        }
        .step-circle.completed {
            background-color: var(--primary-green);
            color: white;
        }
        .step-label {
            font-size: 11px;
            font-weight: 500;
            color: var(--light-brown);
            margin-top: 6px;
            white-space: nowrap;
        }
        .step.active .step-label {
            color: var(--primary-green);
            font-weight: 600;
        }
        .step-connector {
            width: 60px;
            height: 2px;
            background-color: #d9ece8;
            margin: 0 4px;
            margin-bottom: 22px;
            transition: background-color 0.3s ease;
        }
        .step-connector.completed {
            background-color: var(--primary-green);
        }

        /* Role selection cards */
        .role-card {
            border: 2px solid #e9f5f2;
            border-radius: 10px;
            padding: 14px 12px;
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: center;
            background: white;
        }
        .role-card:hover {
            border-color: var(--secondary-green);
            background-color: var(--light-green);
        }
        .role-card.selected {
            border-color: var(--primary-green);
            background-color: var(--light-green);
            box-shadow: 0 4px 12px rgba(47, 143, 126, 0.15);
        }
        .role-card .role-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background-color: var(--light-green);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            transition: background-color 0.25s ease;
        }
        .role-card.selected .role-icon {
            background-color: var(--primary-green);
        }
        .role-card.selected .role-icon svg {
            fill: white !important;
        }
        .role-card .role-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-brown);
        }
        .role-card.selected .role-name {
            color: var(--primary-green);
        }

        /* Form step panels */
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
            animation: fadeIn 0.4s ease;
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

        /* Terms checkbox */
        .form-check-input:checked {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }
        .form-check-input:focus {
            border-color: var(--secondary-green);
            box-shadow: 0 0 0 0.2rem rgba(47, 143, 126, 0.2);
        }

        /* Back button */
        .btn-back {
            background: none;
            border: 1.5px solid #d0e9e4;
            color: var(--light-brown);
            border-radius: 8px;
            padding: 9px 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn-back:hover {
            border-color: var(--primary-green);
            color: var(--primary-green);
            background: var(--light-green);
        }
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
                    <h2 class="fw-bold text-primary-custom">Create Account</h2>
                    <p class="text-secondary-custom">Join our mental health community</p>
                </div>

                <!-- Step Indicator -->
                <div class="step-indicator" id="stepIndicator">
                    <div class="step" data-step="1">
                        <div class="step-circle active" id="circle-1">1</div>
                        <span class="step-label active" id="label-1">Role</span>
                    </div>
                    <div class="step-connector" id="connector-1"></div>
                    <div class="step" data-step="2">
                        <div class="step-circle" id="circle-2">2</div>
                        <span class="step-label" id="label-2">Details</span>
                    </div>
                    <div class="step-connector" id="connector-2"></div>
                    <div class="step" data-step="3">
                        <div class="step-circle" id="circle-3">3</div>
                        <span class="step-label" id="label-3">Security</span>
                    </div>
                </div>

                <!-- Sign Up Card -->
                <div class="card card-custom p-4 p-md-5">

                    <form action="handleForms.php" id="signupForm" novalidate>
                        <!-- //! action = register -->
                        <input type="hidden" name="action" value="register">

                        <!-- ── STEP 1: Choose Role ── -->
                        <div class="form-step active" id="step1">
                            <h6 class="fw-semibold text-secondary-custom mb-3">I am joining as a…</h6>
                            <div class="row g-2 mb-4">

                                <div class="col-6">
                                    <div class="role-card selected" data-role="patient" onclick="selectRole(this)">
                                        <div class="role-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="var(--primary-green)" viewBox="0 0 16 16">
                                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.029 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664h10z"/>
                                            </svg>
                                        </div>
                                        <div class="role-name">Patient</div>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="role-card" data-role="therapist" onclick="selectRole(this)">
                                        <div class="role-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="var(--primary-green)" viewBox="0 0 16 16">
                                                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                                <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"/>
                                                <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                                            </svg>
                                        </div>
                                        <div class="role-name">Therapist</div>
                                    </div>
                                </div>

                            </div>

                            <!-- Role description -->
                            <div id="roleDescription" class="alert border-0 py-2 px-3 mb-4" style="background: var(--light-green); font-size: 13px; color: var(--text-brown); border-radius: 8px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="var(--primary-green)" class="me-1" viewBox="0 0 16 16">
                                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                                </svg>
                                As a <strong>Patient</strong>, you'll connect with therapists and access mental health resources.
                            </div>

                            <!-- Hidden role input -->
                            <input type="hidden" id="selectedRole" value="patient" name="selectedRole">

                            <button type="button" class="btn btn-primary-custom w-100 py-2 fw-semibold" onclick="goToStep(2)">
                                Continue
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                            </button>
                        </div>

                        <!-- ── STEP 2: Personal Details ── -->
                        <div class="form-step" id="step2">

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label for="firstName" class="form-label text-secondary-custom">First Name</label>
                                    <input type="text" class="form-control py-2" id="firstName" placeholder="John" required name="firstName">
                                    <div class="invalid-feedback">Please enter your first name.</div>
                                </div>
                                <div class="col-6">
                                    <label for="lastName" class="form-label text-secondary-custom">Last Name</label>
                                    <input type="text" class="form-control py-2" id="lastName" placeholder="Doe" required name="lastName">
                                    <div class="invalid-feedback">Please enter your last name.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="signupEmail" class="form-label text-secondary-custom">Email Address</label>
                                <input type="email" class="form-control py-2" id="signupEmail" placeholder="name@example.com" required name="signupEmail">
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <div class="mb-3">
                                <label for="nationalID" class="form-label text-secondary-custom">National ID</label>
                                <input type="text" class="form-control py-2" id="nationalID" placeholder="e.g. 29901010123456" required name="nationalID">
                                <div class="invalid-feedback">Please enter your National ID.</div>
                            </div>
                            <div class="mb-3">
                                <label for="city" class="form-label text-secondary-custom">City</label>
                                <input type="text" class="form-control py-2" id="city" placeholder="e.g. Cairo" required name="city">
                                <div class="invalid-feedback">Please enter your city.</div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label text-secondary-custom">Phone Number <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="tel" class="form-control py-2" id="phone" placeholder="+20 100 000 0000" name="phone">
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label for="dob" class="form-label text-secondary-custom">Date of Birth</label>
                                    <input type="date" class="form-control py-2" id="dob" required name="dob">
                                    <div class="invalid-feedback">Please enter your date of birth.</div>
                                </div>
                                <div class="col-6">
                                    <label for="gender" class="form-label text-secondary-custom">Gender</label>
                                    <select class="form-select py-2" id="gender" required name="gender">
                                        <option value="" disabled selected>Select…</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="prefer_not">Prefer not to say</option>
                                    </select>
                                    <div class="invalid-feedback">Please select your gender.</div>
                                </div>
                            </div>

                            <!-- Therapist-only field: specialization (from class diagram Therapist.specialization) -->
                            <div class="mb-4" id="specialtyField" style="display:none;">
                                <label for="specialty" class="form-label text-secondary-custom">Specialty / Area of Practice</label>
                                <select class="form-select py-2" id="specialty" name="specialty">
                                    <option value="" disabled selected>Select your specialty…</option>
                                    <option>Anxiety &amp; Stress</option>
                                    <option>Depression</option>
                                    <option>Trauma &amp; PTSD</option>
                                    <option>Couples Therapy</option>
                                    <option>Child &amp; Adolescent</option>
                                    <option>Addiction</option>
                                    <option>Cognitive Behavioral Therapy (CBT)</option>
                                    <option>Other</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-back py-2 px-3" onclick="goToStep(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                                    </svg>
                                    Back
                                </button>
                                <button type="button" class="btn btn-primary-custom flex-grow-1 py-2 fw-semibold" onclick="validateStep2()">
                                    Continue
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- ── STEP 3: Security ── -->
                        <div class="form-step" id="step3">

                            <div class="mb-3">
                                <label for="signupPassword" class="form-label text-secondary-custom">Password</label>
                                <div class="input-icon-group">
                                    <input type="password" class="form-control py-2" id="signupPassword" placeholder="••••••••" required oninput="checkStrength(this.value)" name="signupPassword">
                                    <button type="button" class="toggle-pw" onclick="togglePw('signupPassword', this)" tabindex="-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="eye-icon">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                        </svg>
                                    </button>
                                </div>
                                <!-- Strength bar -->
                                <div class="strength-bar mt-2">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div class="strength-text text-secondary-custom" id="strengthText"></div>
                            </div>

                            <div class="mb-4">
                                <label for="confirmPassword" class="form-label text-secondary-custom">Confirm Password</label>
                                <div class="input-icon-group">
                                    <input type="password" class="form-control py-2" id="confirmPassword" placeholder="••••••••" required name="confirmPassword">
                                    <button type="button" class="toggle-pw" onclick="togglePw('confirmPassword', this)" tabindex="-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="eye-icon">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div id="passwordMatchMsg" class="mt-1" style="font-size: 12px;"></div>
                            </div>

                            <!-- Terms -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agreeTerms" required name="agreeTerms">
                                <label class="form-check-label text-secondary-custom small" for="agreeTerms">
                                    I agree to the <a href="#" class="text-primary-custom fw-semibold text-decoration-none">Terms of Service</a> and <a href="#" class="text-primary-custom fw-semibold text-decoration-none">Privacy Policy</a>
                                </label>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-back py-2 px-3" onclick="goToStep(2)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                                    </svg>
                                    Back
                                </button>
                                <button type="submit" class="btn btn-primary-custom flex-grow-1 py-2 fw-semibold">
                                    Create Account
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </form>

                    <div class="text-center mt-4">
                        <p class="text-secondary-custom mb-0 small">Already have an account? <a href="index.php" class="text-primary-custom text-decoration-none fw-semibold">Sign In</a></p>
                    </div>

                </div><!-- /card -->

            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script>
        /* ── Role Descriptions ── */
        const roleDescriptions = {
            patient:   'As a <strong>Patient</strong>, you\'ll connect with therapists and access mental health resources.',
            therapist: 'As a <strong>Therapist</strong>, you\'ll manage patient sessions and provide professional care.',
        };

        function selectRole(card) {
            document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            const role = card.getAttribute('data-role');
            document.getElementById('selectedRole').value = role;
            document.getElementById('roleDescription').innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="var(--primary-green)" class="me-1" viewBox="0 0 16 16">
                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                </svg>
                ${roleDescriptions[role]}
            `;
            // Show specialty field only for therapists
            document.getElementById('specialtyField').style.display = role === 'therapist' ? 'block' : 'none';
        }

        /* ── Step Navigation ── */
        function goToStep(step) {
            document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');

            for (let i = 1; i <= 3; i++) {
                const circle = document.getElementById('circle-' + i);
                const label  = document.getElementById('label-' + i);
                circle.classList.remove('active', 'completed');
                label.classList.remove('active');

                if (i < step) {
                    circle.classList.add('completed');
                    circle.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="white" viewBox="0 0 16 16"><path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/></svg>`;
                } else if (i === step) {
                    circle.classList.add('active');
                    circle.textContent = i;
                    label.classList.add('active');
                } else {
                    circle.textContent = i;
                }
            }

            for (let i = 1; i <= 2; i++) {
                const conn = document.getElementById('connector-' + i);
                conn.classList.toggle('completed', i < step);
            }
        }

        /* ── Step 2 Validation ── */
        function validateStep2() {
            const requiredFields = ['firstName', 'lastName', 'signupEmail', 'nationalID', 'dob', 'gender'];
            let valid = true;

            requiredFields.forEach(id => {
                const el = document.getElementById(id);
                if (!el.value.trim()) {
                    el.classList.add('is-invalid');
                    valid = false;
                } else {
                    el.classList.remove('is-invalid');
                }
            });

            // Email format check
            const email = document.getElementById('signupEmail');
            if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                email.classList.add('is-invalid');
                valid = false;
            }

            if (valid) goToStep(3);
        }

        /* ── Password Strength ── */
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
            fill.style.width = lvl.pct;
            fill.style.backgroundColor = lvl.color;
            text.textContent = lvl.label;
            text.style.color = lvl.color;
        }

        /* ── Toggle Password Visibility ── */
        function togglePw(inputId, btn) {
            const input = document.getElementById(inputId);
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            btn.querySelector('.eye-icon').style.opacity = isText ? '1' : '0.4';
        }

        /* ── Form Submit → redirect to index.php ── */
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
            } else {
                msg.textContent = '✓ Passwords match!';
                msg.style.color = '#2F8F7E';
                document.getElementById('confirmPassword').classList.remove('is-invalid');
            }

            if (!document.getElementById('agreeTerms').checked) {
                showToast('Please agree to the Terms of Service.', 'error');
                return;
            }

            showToast('Account created successfully! Redirecting to Sign In…', 'success');
            setTimeout(() => { window.location.href = 'index.php'; }, 2000);
        });

        /* ── Real-time password match indicator ── */
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const pw  = document.getElementById('signupPassword').value;
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

        /* ── Clear invalid state on input ── */
        document.querySelectorAll('.form-control, .form-select').forEach(el => {
            el.addEventListener('input', () => el.classList.remove('is-invalid'));
        });
    </script>
</body>

<!--
    Variabled
        action = action (hidden, value="register")
        selectedRole = selectedRole (hidden, value="patient") //!have to be deleted
        firstName = firstName
        lastName = lastName
        signupEmail = signupEmail
        nationalID = nationalID
        phone = phone
        dob = dob
        signupPassword = signupPassword
        confirmPassword = confirmPassword
        agreeTerms = agreeTerms
        gender = ( male / female / prefer_not )
        specialty = specialty
-->
</html>
