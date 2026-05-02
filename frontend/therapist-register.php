<?php
require_once 'Validation.php';
require_once 'connection.php';
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapist Registration - MindCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .step-indicator {
            display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 2rem;
        }
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
        .step-connector { width: 60px; height: 2px; background-color: #d9ece8; margin: 0 4px; margin-bottom: 22px; transition: background-color 0.3s ease; }
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
        .file-upload-area {
            border: 2px dashed #d0e9e4; border-radius: 12px; padding: 2rem; text-align: center;
            cursor: pointer; transition: all 0.3s ease; background: var(--bg-offwhite);
        }
        .file-upload-area:hover { border-color: var(--primary-green); background: var(--light-green); }
        .file-upload-area.has-file { border-color: var(--primary-green); background: var(--light-green); }
    </style>
</head>
<body class="d-flex align-items-center py-5" style="min-height: 100vh;">

    <div class="container fade-in">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7 col-xl-6">

                <!-- Logo/Header -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light-green rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="var(--primary-green)" class="bi bi-heart-pulse-fill" viewBox="0 0 16 16">
                            <path d="M1.475 9C2.702 10.84 4.779 12.871 8 15c3.221-2.129 5.298-4.16 6.525-6H12a.5.5 0 0 1-.464-.314l-1.457-3.642-1.598 5.593a.5.5 0 0 1-.945.049L5.889 6.568l-1.473 2.21A.5.5 0 0 1 4 9H1.475Z"/>
                            <path d="M.88 8C-2.427 1.68 4.41-2 7.823 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C11.59-2 18.426 1.68 15.12 8h-2.783l-1.874-4.686a.5.5 0 0 0-.945.049L7.921 8.956 6.464 5.314a.5.5 0 0 0-.88-.091L3.732 8H.88Z"/>
                        </svg>
                    </div>
                    <h2 class="fw-bold text-primary-custom">Therapist Registration</h2>
                    <p class="text-secondary-custom">Apply to join our network of professionals</p>
                </div>

                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step" data-step="1">
                        <div class="step-circle active" id="circle-1">1</div>
                        <span class="step-label" id="label-1">Personal</span>
                    </div>
                    <div class="step-connector" id="connector-1"></div>
                    <div class="step" data-step="2">
                        <div class="step-circle" id="circle-2">2</div>
                        <span class="step-label" id="label-2">Professional</span>
                    </div>
                    <div class="step-connector" id="connector-2"></div>
                    <div class="step" data-step="3">
                        <div class="step-circle" id="circle-3">3</div>
                        <span class="step-label" id="label-3">Security</span>
                    </div>
                </div>

                <!-- Registration Card -->
                <div class="card card-custom p-4 p-md-5">
                    <form id="therapistRegForm" action="handleForms.php" method="POST" enctype="multipart/form-data" novalidate>  
                        <input type="hidden" name="action" value="register_therapist">

                        <!-- STEP 1: Personal Details -->
                        <div class="form-step active" id="step1">
                            <h6 class="fw-semibold text-secondary-custom mb-3">Personal Information</h6>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label for="firstName" class="form-label text-secondary-custom">First Name</label>
                                    <input type="text" class="form-control py-2" id="firstName" name="firstName" placeholder="Jane" required>
                                    <div class="invalid-feedback">Required.</div>
                                </div>
                                <div class="col-6">
                                    <label for="lastName" class="form-label text-secondary-custom">Last Name</label>
                                    <input type="text" class="form-control py-2" id="lastName" name="lastName" placeholder="Smith" required>
                                    <div class="invalid-feedback">Required.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label text-secondary-custom">Email Address</label>
                                <input type="email" class="form-control py-2" id="email" name="email" placeholder="name@example.com" required>
                                <div class="invalid-feedback">Valid email required.</div>
                            </div>

                            <div class="mb-3">
                                <label for="nationalID" class="form-label text-secondary-custom">National ID</label>
                                <input type="text" class="form-control py-2" id="nationalID" name="nationalID" placeholder="Enter your National ID" required>
                                <div class="invalid-feedback">Required.</div>
                            </div>
                             <div class="mb-3">
                                <label for="city" class="form-label text-secondary-custom">City</label>
                                <input type="text" class="form-control py-2" id="city" placeholder="e.g. Cairo" required name="city">
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
                                    <div class="invalid-feedback">Required.</div>
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
                                <div class="invalid-feedback">Required.</div>
                            </div>

                            <button type="button" class="btn btn-primary-custom w-100 py-2 fw-semibold" onclick="validateStep1()">
                                Continue <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>

                        <!-- STEP 2: Professional Details -->
                        <div class="form-step" id="step2">
                            <h6 class="fw-semibold text-secondary-custom mb-3">Professional Information</h6>

                            <div class="mb-3">
                                <label for="specialization" class="form-label text-secondary-custom">Specialization</label>
                                <select class="form-select py-2" id="specialization" name="specialization" required>
                                    <option value="" disabled selected>Select specialization</option>
                                    <option value="clinical_psychology">Clinical Psychology</option>
                                    <option value="counseling">Counseling</option>
                                    <option value="psychiatry">Psychiatry</option>
                                    <option value="cognitive_behavioral">Cognitive Behavioral Therapy (CBT)</option>
                                    <option value="family_therapy">Family & Marriage Therapy</option>
                                    <option value="child_adolescent">Child & Adolescent Therapy</option>
                                    <option value="substance_abuse">Substance Abuse Counseling</option>
                                    <option value="trauma">Trauma & PTSD</option>
                                    <option value="other">Other</option>
                                </select>
                                <div class="invalid-feedback">Required.</div>
                            </div>

                            <div class="mb-3">
                                <label for="licenseStatus" class="form-label text-secondary-custom">License Status</label>
                                <select class="form-select py-2" id="licenseStatus" name="licenseStatus" required>
                                    <option value="" disabled selected>Select license status</option>
                                    <option value="licensed">Fully Licensed</option>
                                    <option value="provisional">Provisional / Under Supervision</option>
                                    <option value="pending">License Pending</option>
                                </select>
                                <div class="invalid-feedback">Required.</div>
                            </div>

                            <div class="mb-3">
                                <label for="yearsOfExperience" class="form-label text-secondary-custom">Years of Experience</label>
                                <input type="number" class="form-control py-2" id="yearsOfExperience" name="yearsOfExperience" placeholder="e.g. 5" min="0" max="60" required>
                                <div class="invalid-feedback">Required.</div>
                            </div>

                            <div class="mb-3">
                                <label for="availabilitySchedule" class="form-label text-secondary-custom">Availability Schedule</label>
                                <select class="form-select py-2" id="availabilitySchedule" name="availabilitySchedule" required>
                                    <option value="" disabled selected>Select availability</option>
                                    <option value="full_time">Full-Time (5+ days/week)</option>
                                    <option value="part_time">Part-Time (2-4 days/week)</option>
                                    <option value="weekends">Weekends Only</option>
                                    <option value="flexible">Flexible / On-Demand</option>
                                </select>
                                <div class="invalid-feedback">Required.</div>
                            </div>

                            <!-- PDF Upload -->
                            <div class="mb-4">
                                <label class="form-label text-secondary-custom">Upload Credentials (PDF)</label>
                                <div class="file-upload-area" id="fileUploadArea" onclick="document.getElementById('credentialFile').click();">
                                    <i class="bi bi-cloud-arrow-up fs-2 text-primary-custom"></i>
                                    <p class="text-secondary-custom mb-1 mt-2" id="fileLabel">Click to upload your license / credential PDF</p>
                                    <small class="text-muted">PDF format only, max 5MB</small>
                                </div>
                                <input type="file" class="d-none" id="credentialFile" name="credentialFile" accept=".pdf" onchange="handleFileSelect(this)">
                                <div class="invalid-feedback" id="fileError" style="display:none;">Please upload your credentials PDF.</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-back" onclick="goToStep(1)">
                                    <i class="bi bi-arrow-left me-1"></i> Back
                                </button>
                                <button type="button" class="btn btn-primary-custom flex-grow-1 py-2 fw-semibold" onclick="validateStep2()">
                                    Continue <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 3: Security -->
                        <div class="form-step" id="step3">
                            <h6 class="fw-semibold text-secondary-custom mb-3">Set Up Your Password</h6>

                            <div class="mb-3">
                                <label for="password" class="form-label text-secondary-custom">Password</label>
                                <div class="input-icon-group">
                                    <input type="password" class="form-control py-2" id="password" name="password" placeholder="••••••••" required oninput="checkStrength(this.value)">
                                    <button type="button" class="toggle-pw" onclick="togglePw('password', this)">
                                        <i class="bi bi-eye eye-icon"></i>
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
                                        <i class="bi bi-eye eye-icon"></i>
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
                                <button type="button" class="btn btn-back" onclick="goToStep(2)">
                                    <i class="bi bi-arrow-left me-1"></i> Back
                                </button>
                                <button type="submit" class="btn btn-primary-custom flex-grow-1 py-2 fw-semibold">
                                    Submit Application <i class="bi bi-check-lg ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-secondary-custom mb-0 small">Already have an account? <a href="index.php" class="text-primary-custom text-decoration-none fw-semibold">Sign In</a></p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border:none; border-radius:16px;">
                <div class="modal-body text-center p-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light-green rounded-circle mb-3" style="width:80px;height:80px;">
                        <i class="bi bi-envelope-check fs-1 text-primary-custom"></i>
                    </div>
                    <h4 class="fw-bold text-primary-custom mb-2">Application Submitted!</h4>
                    <p class="text-secondary-custom mb-1">Thank you for applying to join MindCare as a therapist.</p>
                    <p class="text-secondary-custom mb-4">Your application is now <strong>pending review</strong>. A confirmation email will be sent to your email address once an administrator has verified your credentials.</p>
                    <div class="alert py-2 px-3 mb-4" style="background:var(--light-green); border:none; border-radius:8px;">
                        <small class="text-secondary-custom"><i class="bi bi-info-circle me-1 text-primary-custom"></i>You will be able to log in using your credentials once your account has been approved.</small>
                    </div>
                    <a href="home.php" class="btn btn-primary-custom w-100 py-2 fw-semibold">Back to Home</a>
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
            for (let i = 1; i <= 3; i++) {
                const circle = document.getElementById('circle-' + i);
                const label = document.getElementById('label-' + i);
                circle.classList.remove('active', 'completed');
                label.classList.remove('active');
                if (i < step) {
                    circle.classList.add('completed');
                    circle.innerHTML = '<i class="bi bi-check"></i>';
                } else if (i === step) {
                    circle.classList.add('active');
                    circle.textContent = i;
                    label.classList.add('active');
                } else {
                    circle.textContent = i;
                }
            }
            for (let i = 1; i <= 2; i++) {
                document.getElementById('connector-' + i).classList.toggle('completed', i < step);
            }
        }

        function validateStep1() {
            const fields = ['firstName','lastName','email','nationalID','dob','gender'];
            let valid = true;
            fields.forEach(id => {
                const el = document.getElementById(id);
                if (!el.value.trim()) { el.classList.add('is-invalid'); valid = false; }
                else { el.classList.remove('is-invalid'); }
            });
            const emailEl = document.getElementById('email');
            if (emailEl.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value)) {
                emailEl.classList.add('is-invalid'); valid = false;
            }
            if (valid) goToStep(2);
        }

        function validateStep2() {
            const fields = ['specialization','licenseStatus','yearsOfExperience','availabilitySchedule'];
            let valid = true;
            fields.forEach(id => {
                const el = document.getElementById(id);
                if (!el.value.trim()) { el.classList.add('is-invalid'); valid = false; }
                else { el.classList.remove('is-invalid'); }
            });
            const fileInput = document.getElementById('credentialFile');
            if (!fileInput.files.length) {
                document.getElementById('fileError').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('fileError').style.display = 'none';
            }
            if (valid) goToStep(3);
        }

        function handleFileSelect(input) {
            const area = document.getElementById('fileUploadArea');
            const label = document.getElementById('fileLabel');
            if (input.files.length) {
                const file = input.files[0];
                if (file.type !== 'application/pdf') {
                    showToast('Please upload a PDF file only.', 'error');
                    input.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    showToast('File size must be under 5MB.', 'error');
                    input.value = '';
                    return;
                }
                area.classList.add('has-file');
                label.innerHTML = '<i class="bi bi-file-earmark-pdf text-primary-custom me-1"></i>' + file.name;
                document.getElementById('fileError').style.display = 'none';
            }
        }

        function checkStrength(val) {
            const fill = document.getElementById('strengthFill');
            const text = document.getElementById('strengthText');
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            const levels = [
                { pct:'0%', color:'#e9ecef', label:'' },
                { pct:'25%', color:'#dc3545', label:'Weak' },
                { pct:'50%', color:'#fd7e14', label:'Fair' },
                { pct:'75%', color:'#ffc107', label:'Good' },
                { pct:'100%', color:'#2F8F7E', label:'Strong' },
            ];
            const lvl = val.length === 0 ? levels[0] : levels[score];
            fill.style.width = lvl.pct;
            fill.style.backgroundColor = lvl.color;
            text.textContent = lvl.label;
            text.style.color = lvl.color;
        }

        function togglePw(inputId, btn) {
            const input = document.getElementById(inputId);
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            btn.querySelector('.eye-icon').style.opacity = isText ? '1' : '0.4';
        }

        document.getElementById('confirmPassword').addEventListener('input', function() {
            const pw = document.getElementById('password').value;
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

        document.getElementById('therapistRegForm').addEventListener('submit', function(e) {
            const pw = document.getElementById('password').value;
            const cpw = document.getElementById('confirmPassword').value;
            const msg = document.getElementById('passwordMatchMsg');
            if (pw !== cpw) {
                msg.textContent = '✗ Passwords do not match.'; msg.style.color = '#dc3545';
                document.getElementById('confirmPassword').classList.add('is-invalid');
                return;
            }
            if (!document.getElementById('agreeTerms').checked) {
                showToast('Please agree to the Terms of Service.', 'error');
                return;
            }
            // Show confirmation modal
            var modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            modal.show();
        });
    </script>
</body>
</html>
