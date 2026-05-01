<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCare – Holistic Mental Health & Wellness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, var(--light-green) 0%, var(--bg-offwhite) 100%);
            min-height: 80vh;
            display: flex;
            align-items: center;
        }
        .hero-doodle {
            font-size: 4rem;
            opacity: 0.15;
            position: absolute;
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--light-green);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .feature-icon i {
            font-size: 1.5rem;
            color: var(--primary-green);
        }
        .cta-card {
            border: 2px solid var(--light-green);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            background: white;
        }
        .cta-card:hover {
            border-color: var(--primary-green);
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(47, 143, 126, 0.12);
        }
        .cta-card .icon-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: var(--light-green);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .cta-card .icon-circle i {
            font-size: 1.8rem;
            color: var(--primary-green);
        }
        .doodle-leaf { position: absolute; opacity: 0.08; font-size: 5rem; }
        footer {
            background-color: var(--primary-green);
            color: white;
        }
        footer a { color: var(--accent-yellow); text-decoration: none; }
        footer a:hover { color: white; }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 12px rgba(90,74,66,0.06);
        }
        .navbar-custom .nav-link { color: var(--text-brown); font-weight: 500; }
        .navbar-custom .nav-link:hover { color: var(--primary-green); }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="home.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="var(--primary-green)" class="bi bi-heart-pulse-fill me-2" viewBox="0 0 16 16">
                    <path d="M1.475 9C2.702 10.84 4.779 12.871 8 15c3.221-2.129 5.298-4.16 6.525-6H12a.5.5 0 0 1-.464-.314l-1.457-3.642-1.598 5.593a.5.5 0 0 1-.945.049L5.889 6.568l-1.473 2.21A.5.5 0 0 1 4 9H1.475Z"/>
                    <path d="M.88 8C-2.427 1.68 4.41-2 7.823 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C11.59-2 18.426 1.68 15.12 8h-2.783l-1.874-4.686a.5.5 0 0 0-.945.049L7.921 8.956 6.464 5.314a.5.5 0 0 0-.88-.091L3.732 8H.88Z"/>
                </svg>
                <span class="fw-bold text-primary-custom fs-5">MindCare</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about-footer">About Us</a></li>
                    <li class="nav-item"><a class="btn btn-primary-custom px-3 py-2" href="index.php">Log In</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section position-relative overflow-hidden">
        <span class="doodle-leaf" style="top:10%;left:5%;">🌿</span>
        <span class="doodle-leaf" style="top:60%;right:8%;">🧠</span>
        <span class="doodle-leaf" style="bottom:15%;left:20%;">💚</span>
        <span class="doodle-leaf" style="top:25%;right:25%;">💚</span>

        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="fw-bold mb-3" style="font-size:2.8rem; color:var(--text-brown);">
                        Your Journey to <span class="text-primary-custom">Mental Wellness</span> Starts Here
                    </h1>
                    <p class="text-secondary-custom mb-4" style="font-size:1.1rem; max-width:520px;">
                        MindCare connects you with licensed therapists, provides self-help tools, and fosters a supportive community — all in a safe, accessible platform.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="signup.php" class="btn btn-primary-custom btn-lg px-4 py-2 fw-semibold">
                            <i class="bi bi-person-plus me-1"></i> Join as a Patient
                        </a>
                        <a href="therapist-register.php" class="btn btn-accent btn-lg px-4 py-2 fw-semibold">
                            <i class="bi bi-briefcase me-1"></i> Apply as a Therapist
                        </a>
                    </div>
                </div>
               
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5" style="background:var(--bg-offwhite);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-primary-custom">How MindCare Helps You</h2>
                <p class="text-secondary-custom">A comprehensive approach to mental health and wellness</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-custom h-100 p-4 text-center">
                        <div class="feature-icon mx-auto"><i class="bi bi-person-check"></i></div>
                        <h5 class="fw-bold" style="color:var(--text-brown);">Therapist Matching</h5>
                        <p class="text-secondary-custom small">Our intelligent algorithm pairs you with the most suitable therapist based on your needs, preferences, and clinical profile.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom h-100 p-4 text-center">
                        <div class="feature-icon mx-auto"><i class="bi bi-heart-pulse"></i></div>
                        <h5 class="fw-bold" style="color:var(--text-brown);">Mood & Goal Tracking</h5>
                        <p class="text-secondary-custom small">Track your daily mood, set wellness goals, monitor your sleep, and visualize your progress over time.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom h-100 p-4 text-center">
                        <div class="feature-icon mx-auto"><i class="bi bi-shield-check"></i></div>
                        <h5 class="fw-bold" style="color:var(--text-brown);">Safe Community</h5>
                        <p class="text-secondary-custom small">Engage anonymously in moderated forums with others on similar journeys, with built-in crisis detection for your safety.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5" style="background:white;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-primary-custom">Get Started Today</h2>
                <p class="text-secondary-custom">Choose your path to wellness</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4">
                    <div class="cta-card">
                        <div class="icon-circle"><i class="bi bi-person-plus"></i></div>
                        <h5 class="fw-bold" style="color:var(--text-brown);">Join as a Patient</h5>
                        <p class="text-secondary-custom small mb-3">Create your account, complete an intake assessment, and get matched with a therapist.</p>
                        <a href="signup.php" class="btn btn-primary-custom w-100 py-2 fw-semibold">Sign Up</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="cta-card">
                        <div class="icon-circle"><i class="bi bi-briefcase"></i></div>
                        <h5 class="fw-bold" style="color:var(--text-brown);">Apply as a Therapist</h5>
                        <p class="text-secondary-custom small mb-3">Submit your credentials and join our network of licensed professionals.</p>
                        <a href="therapist-register.php" class="btn btn-accent w-100 py-2 fw-semibold">Apply Now</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="cta-card">
                        <div class="icon-circle"><i class="bi bi-box-arrow-in-right"></i></div>
                        <h5 class="fw-bold" style="color:var(--text-brown);">Already a Member?</h5>
                        <p class="text-secondary-custom small mb-3">Sign in to access your dashboard, sessions, and resources.</p>
                        <a href="index.php" class="btn btn-primary-custom w-100 py-2 fw-semibold">Log In</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer with About Us -->
    <footer id="about-footer" class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="white" class="bi bi-heart-pulse-fill me-2" viewBox="0 0 16 16">
                            <path d="M1.475 9C2.702 10.84 4.779 12.871 8 15c3.221-2.129 5.298-4.16 6.525-6H12a.5.5 0 0 1-.464-.314l-1.457-3.642-1.598 5.593a.5.5 0 0 1-.945.049L5.889 6.568l-1.473 2.21A.5.5 0 0 1 4 9H1.475Z"/>
                            <path d="M.88 8C-2.427 1.68 4.41-2 7.823 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C11.59-2 18.426 1.68 15.12 8h-2.783l-1.874-4.686a.5.5 0 0 0-.945.049L7.921 8.956 6.464 5.314a.5.5 0 0 0-.88-.091L3.732 8H.88Z"/>
                        </svg>
                        <h5 class="fw-bold mb-0">MindCare</h5>
                    </div>
                    <h6 class="fw-semibold mb-2">About Us</h6>
                    <p class="small" style="opacity:0.9;">
                        MindCare is a web-based platform designed to provide a supportive environment for individuals experiencing mental health challenges. We facilitate communication between patients and therapists and assist users throughout their therapeutic journey.
                    </p>
                    <p class="small" style="opacity:0.9;">
                        Our system includes therapist matching, online therapy sessions, mood tracking, goal tracking, sleep monitoring, and an anonymous community space — all supported by moderation mechanisms and risk detection logic to ensure safety.
                    </p>
                    <p class="small" style="opacity:0.8;">
                        MindCare focuses on enhancing accessibility, safety, and user engagement while supporting — but not replacing — professional mental health care.
                    </p>
                </div>
                <div class="col-lg-3 offset-lg-1">
                    <h6 class="fw-semibold mb-3">Quick Links</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="signup.php">Join as a Patient</a></li>
                        <li class="mb-2"><a href="therapist-register.php">Apply as a Therapist</a></li>
                        <li class="mb-2"><a href="index.php">Log In</a></li>
                        <li class="mb-2"><a href="#features">Features</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="fw-semibold mb-3">Contact & Support</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i>support@mindcare.com</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i>Crisis Hotline: 988</li>
                        <li class="mb-2"><i class="bi bi-clock me-2"></i>24/7 Emergency Support</li>
                    </ul>
                </div>
            </div>
            <hr style="border-color:rgba(255,255,255,0.2);" class="my-4">
            <div class="text-center small" style="opacity:0.7;">
                &copy; 2026 MindCare — Holistic Mental Health Care & Wellness Portal. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
