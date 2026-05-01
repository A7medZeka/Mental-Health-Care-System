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
    <title>Login - Mental Health Care</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <h2 class="fw-bold text-primary-custom">Welcome Back</h2>
                    <p class="text-secondary-custom">Please sign in to your account</p>
                </div>

                <!-- Login Card -->
                <div class="card card-custom p-4 p-md-5">

                    <form action="handleForms.php" id="loginForm" method="POST" >
                        <!-- action = login -->
                        <input type="hidden" name="action" value="login">

                        <!-- Email Input -->
                        <div class="mb-3">
                            <label for="email" class="form-label text-secondary-custom">Email address</label>
                            <input type="email" class="form-control py-2" id="email" name="email" placeholder="name@example.com" required>
                        </div>

                        <!-- Password Input -->
                        <div class="mb-4">
                            <label for="password" class="form-label text-secondary-custom">Password</label>
                            <input type="password" class="form-control py-2" id="password" name="password" placeholder="••••••••" required>
                            <div class="d-flex justify-content-end mt-2">
                                <a href="forgot-password.php" class="text-decoration-none text-primary-custom small">Forgot password?</a>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary-custom w-100 py-2 fw-semibold mb-3" >
                            Sign In
                        </button>

                        <div class="text-center">
                            <p class="text-secondary-custom mb-0">Don't have an account? <a href="signup.php" class="text-accent text-decoration-none fw-semibold">Sign up</a></p>
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
</body>

<!--
    Variabled
        action = action (hidden, value="login")
        email = email
        password = password
-->
</html>