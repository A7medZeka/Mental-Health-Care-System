<?php
require_once 'Validation.php';
require_once 'connection.php';
session_start();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
checkMethod($method);
if ($_SESSION['role'] !== 'Patient') {
    $map = [
        'Admin'     => 'admin-dashboard.php',
        'Therapist' => 'therapist-dashboard.php',
        'Moderator' => 'moderator-dashboard.php',
        ];
        header('Location: ' . ($map[$_SESSION['role']] ?? 'index.php'));
        exit();
        }
$email = $_SESSION['email'] ?? '';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anonymous Community Forum - MentalCare System</title>
    <meta name="description" content="A safe, anonymous community forum for patients to share experiences and support each other.">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .forum-post-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 16px rgba(47,143,126,0.07);
            background: #fff;
            transition: box-shadow 0.2s, transform 0.2s;
            margin-bottom: 1rem;
        }
        .forum-post-card:hover { box-shadow: 0 6px 28px rgba(47,143,126,0.13); transform: translateY(-2px); }
        .forum-post-card.flagged-post { border-left: 4px solid #dc3545; }
        .forum-post-card.crisis-post  { border-left: 4px solid #fd7e14; background: #fff8f5; }

        .avatar-anon {
            width: 44px; height: 44px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1rem;
            color: #fff;
            flex-shrink: 0;
        }

        .crisis-banner {
            background: linear-gradient(135deg, #ff6b6b, #ffa852);
            color: white;
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            display: flex; align-items: center; gap: 1rem;
        }
        .crisis-banner .btn-crisis {
            background: white;
            color: #e84040;
            font-weight: 700;
            border: none;
            border-radius: 30px;
            padding: .5rem 1.4rem;
            white-space: nowrap;
        }
        .crisis-banner .btn-crisis:hover { background: #ffe0e0; }

        .post-compose-card {
            background: linear-gradient(135deg, var(--light-green), #f0fdf9);
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 16px rgba(47,143,126,0.08);
        }
        .pseudonym-badge {
            background: var(--light-green);
            border: 2px dashed var(--secondary-green);
            border-radius: 8px;
            padding: .4rem 1rem;
            color: var(--primary-green);
            font-weight: 600;
            display: inline-flex; align-items: center; gap: .5rem;
        }

        .keyword-alert-strip {
            background: #fff3cd;
            border-radius: 10px;
            border-left: 4px solid #ffc107;
            padding: .6rem 1rem;
            display: none;
        }

        .filter-chip {
            border-radius: 30px;
            padding: .35rem 1rem;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid var(--primary-green);
            color: var(--primary-green);
            background: transparent;
            transition: all .2s;
        }
        .filter-chip.active, .filter-chip:hover {
            background: var(--primary-green);
            color: white;
        }
        .reaction-btn {
            border: none; background: transparent;
            font-size: .9rem; color: #8C786E;
            padding: .2rem .6rem; border-radius: 20px;
            transition: background .2s;
        }
        .reaction-btn:hover { background: var(--light-green); color: var(--primary-green); }

        .sidebar-widget {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 16px rgba(47,143,126,0.07);
            padding: 1.25rem;
            margin-bottom: 1.25rem;
        }

        /* Patient sidebar */
        .patient-sidebar { background-color: #fff; border-right: 1px solid rgba(0,0,0,0.06); min-height: 100vh; }
        .patient-sidebar .nav-link { color: var(--text-brown); padding: 11px 20px; margin: 3px 10px; border-radius: 8px; font-weight: 500; transition: all .2s; }
        .patient-sidebar .nav-link:hover, .patient-sidebar .nav-link.active { background: var(--light-green); color: var(--primary-green); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- Patient Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block patient-sidebar collapse shadow-sm d-flex flex-column" style="height:100vh; position:sticky; top:0; overflow-y:auto;">
            <div class="p-3 pb-0">
                <div class="text-center mb-3">
                    <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size:2rem;"></i>
                    <h5 class="fw-bold text-primary-custom mt-2">MentalCare System</h5>
                </div>
            </div>
            <ul class="nav flex-column flex-grow-1 px-2">
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php"><i class="bi bi-clipboard-check me-2"></i>Onboarding Checklist</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php"><i class="bi bi-person-check me-2"></i>My Therapist</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php"><i class="bi bi-calendar-event me-2"></i>Appointments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php"><i class="bi bi-camera-video me-2"></i>Sessions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php"><i class="bi bi-heart-pulse me-2"></i>Mood Tracker</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php"><i class="bi bi-bullseye me-2"></i>Wellness Goals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php"><i class="bi bi-journal-richtext me-2"></i>My Journal</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php"><i class="bi bi-stars me-2"></i>Wellness Resources</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="patient-forum.php"><i class="bi bi-chat-square-heart me-2"></i>Community Forum</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php"><i class="bi bi-credit-card me-2"></i>Payments &amp; Insurance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php"><i class="bi bi-file-earmark-check me-2"></i>Legal Consents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient-dashboard.php" style="color:#dc3545;"><i class="bi bi-telephone-fill me-2" style="color:#dc3545;"></i><span style="color:#dc3545;">🆘 Emergency Help</span></a>
                </li>
            </ul>
            <div class="px-2 pb-3 pt-2 border-top mt-2">
                <a href="home.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">

            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <div>
                    <h1 class="h2 text-primary-custom fw-bold"><i class="bi bi-chat-square-heart me-2"></i>Anonymous Community Forum</h1>
                    <p class="text-secondary-custom mb-0">A safe space to share, support, and heal — your real identity is always protected.</p>
                </div>
                <span class="text-secondary-custom"><i class="bi bi-person-circle me-1"></i> Patient: Alex</span>
            </div>

            <!-- Crisis Banner (UC-30) -->
            <div class="crisis-banner mb-4" id="crisisBanner">
                <i class="bi bi-telephone-fill fs-2 flex-shrink-0"></i>
                <div class="flex-grow-1">
                    <strong class="d-block" style="font-size:1.05rem;">Feeling overwhelmed right now?</strong>
                    <small>You are not alone. Reach out to a crisis line — free, confidential, 24/7.</small>
                </div>
                <button class="btn-crisis" id="btnGetHelp" onclick="showEmergencyResources()">
                    <i class="bi bi-life-preserver me-1"></i> Get Help Now
                </button>
            </div>

            <div class="row">
                <!-- Feed Column -->
                <div class="col-lg-8">

                    <!-- Compose Post (UC-28) -->
                    <div class="post-compose-card p-4 mb-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar-anon" id="composeAvatar" style="background:#2F8F7E;">SB</div>
                            <div>
                                <div class="pseudonym-badge" id="pseudonymDisplay">
                                    <i class="bi bi-incognito"></i>
                                    <span id="pseudonymText">SilentBirch_4027</span>
                                </div>
                                <small class="text-secondary-custom d-block mt-1">Your identity is always hidden from other users.</small>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary ms-auto" id="btnResetPseudonym" title="UC-28 Alt: Request pseudonym reset">
                                <i class="bi bi-arrow-repeat me-1"></i> Reset Pseudonym
                            </button>
                        </div>
                        <textarea class="form-control mb-2" id="postContent" rows="3"
                            placeholder="Share what's on your mind anonymously... (Be kind and supportive 💚)" name="postContent"></textarea>

                        <!-- Keyword Alert Strip (UC-29) -->
                        <div class="keyword-alert-strip mb-3" id="keywordAlert">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                            <strong>Heads up:</strong> Your post may contain sensitive language. If you're in crisis, please use the <strong>Get Help Now</strong> button above.
                        </div>

                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <select class="form-select form-select-sm" id="postCategory" style="max-width:200px;" name="postCategory">
                                <option value="general">💬 General Support</option>
                                <option value="anxiety">😰 Anxiety & Stress</option>
                                <option value="depression">🌧️ Depression</option>
                                <option value="recovery">🌱 Recovery Journey</option>
                                <option value="gratitude">🙏 Gratitude</option>
                            </select>
                            <button class="btn btn-primary-custom ms-auto px-4" id="btnSubmitPost">
                                <i class="bi bi-send-fill me-2"></i>Post Anonymously
                            </button>
                        </div>
                    </div>

                    <!-- Filter Chips -->
                    <div class="d-flex gap-2 mb-4 flex-wrap">
                        <button class="filter-chip active" data-filter="all">All Posts</button>
                        <button class="filter-chip" data-filter="anxiety">Anxiety</button>
                        <button class="filter-chip" data-filter="depression">Depression</button>
                        <button class="filter-chip" data-filter="recovery">Recovery</button>
                        <button class="filter-chip" data-filter="gratitude">Gratitude</button>
                    </div>

                    <!-- Posts Feed -->
                    <div id="postsFeed">

                        <!-- Sample Post 1 -->
                        <div class="forum-post-card p-4" data-category="recovery">
                            <div class="d-flex align-items-start gap-3">
                                <div class="avatar-anon" style="background:#48B6A2;">WF</div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <strong class="text-primary-custom">WinterFox_8821</strong>
                                        <span class="badge bg-light text-primary-custom border" style="font-size:.75rem;">🌱 Recovery Journey</span>
                                        <small class="text-secondary-custom ms-auto">2 hours ago</small>
                                    </div>
                                    <p class="mb-3">Today I completed 30 days of my recovery journey. Some days felt impossible, but I kept going. If you're struggling, please know that every small step counts. 🌱</p>
                                    <div class="d-flex align-items-center gap-2 flex-wrap border-top pt-2">
                                        <button class="reaction-btn" onclick="reactPost(this,'heart')"><i class="bi bi-heart me-1"></i><span class="react-count">24</span></button>
                                        <button class="reaction-btn" onclick="reactPost(this,'hug')"><i class="bi bi-emoji-smile me-1"></i><span class="react-count">11</span></button>
                                        <button class="reaction-btn reply-btn"><i class="bi bi-chat me-1"></i>Reply</button>
                                        <button class="reaction-btn ms-auto text-danger flag-btn" onclick="flagPost(this)"><i class="bi bi-flag me-1"></i>Flag</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sample Post 2 -->
                        <div class="forum-post-card p-4" data-category="anxiety">
                            <div class="d-flex align-items-start gap-3">
                                <div class="avatar-anon" style="background:#F4B41A;">QM</div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <strong class="text-primary-custom">QuietMoon_3312</strong>
                                        <span class="badge bg-light text-warning border" style="font-size:.75rem;">😰 Anxiety & Stress</span>
                                        <small class="text-secondary-custom ms-auto">5 hours ago</small>
                                    </div>
                                    <p class="mb-3">Does anyone else find mornings really difficult? I wake up with this heavy dread feeling and it takes hours to shake off. Wondering if this is something others experience.</p>
                                    <div class="d-flex align-items-center gap-2 flex-wrap border-top pt-2">
                                        <button class="reaction-btn" onclick="reactPost(this,'heart')"><i class="bi bi-heart me-1"></i><span class="react-count">18</span></button>
                                        <button class="reaction-btn" onclick="reactPost(this,'hug')"><i class="bi bi-emoji-smile me-1"></i><span class="react-count">7</span></button>
                                        <button class="reaction-btn reply-btn"><i class="bi bi-chat me-1"></i>Reply</button>
                                        <button class="reaction-btn ms-auto text-danger flag-btn" onclick="flagPost(this)"><i class="bi bi-flag me-1"></i>Flag</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sample Post 3 - Gratitude -->
                        <div class="forum-post-card p-4" data-category="gratitude">
                            <div class="d-flex align-items-start gap-3">
                                <div class="avatar-anon" style="background:#8F5E2F;">SP</div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <strong class="text-primary-custom">SilverPine_7740</strong>
                                        <span class="badge bg-light text-success border" style="font-size:.75rem;">🙏 Gratitude</span>
                                        <small class="text-secondary-custom ms-auto">Yesterday</small>
                                    </div>
                                    <p class="mb-3">Grateful for this platform and all of you. Being able to share without fear of judgment has made therapy so much easier for me. Thank you all 💚</p>
                                    <div class="d-flex align-items-center gap-2 flex-wrap border-top pt-2">
                                        <button class="reaction-btn" onclick="reactPost(this,'heart')"><i class="bi bi-heart me-1"></i><span class="react-count">42</span></button>
                                        <button class="reaction-btn" onclick="reactPost(this,'hug')"><i class="bi bi-emoji-smile me-1"></i><span class="react-count">19</span></button>
                                        <button class="reaction-btn reply-btn"><i class="bi bi-chat me-1"></i>Reply</button>
                                        <button class="reaction-btn ms-auto text-danger flag-btn" onclick="flagPost(this)"><i class="bi bi-flag me-1"></i>Flag</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /postsFeed -->
                </div>

                <!-- Right Sidebar Widgets -->
                <div class="col-lg-4">

                    <!-- Your Pseudonym Card -->
                    <div class="sidebar-widget">
                        <h6 class="fw-bold text-primary-custom mb-3"><i class="bi bi-incognito me-2"></i>Your Anonymous Identity</h6>
                        <div class="text-center py-3">
                            <div class="avatar-anon mx-auto mb-3" style="background:#2F8F7E; width:60px; height:60px; font-size:1.4rem;">SB</div>
                            <div class="pseudonym-badge d-inline-flex mx-auto mb-2">
                                <i class="bi bi-shield-check"></i>
                                <span id="widgetPseudonym">SilentBirch_4027</span>
                            </div>
                            <p class="text-secondary-custom small mt-2 mb-0">Your real identity is known only to the system — never shared with other users or moderators.</p>
                        </div>
                    </div>

                    <!-- Community Guidelines -->
                    <div class="sidebar-widget">
                        <h6 class="fw-bold text-primary-custom mb-3"><i class="bi bi-book-half me-2"></i>Community Guidelines</h6>
                        <ul class="list-unstyled mb-0" style="font-size:.88rem; color:#5A4A42;">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-primary-custom me-2"></i>Be kind and supportive</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-primary-custom me-2"></i>No personal information</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-primary-custom me-2"></i>Do not give medical advice</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-primary-custom me-2"></i>Flag harmful content</li>
                            <li><i class="bi bi-check-circle-fill text-primary-custom me-2"></i>Use emergency resources when in crisis</li>
                        </ul>
                    </div>

                    <!-- Active Members -->
                    <div class="sidebar-widget">
                        <h6 class="fw-bold text-primary-custom mb-3"><i class="bi bi-people me-2"></i>Active This Week</h6>
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-anon me-2" style="background:#48B6A2; width:32px; height:32px; font-size:.8rem;">WF</div>
                            <span style="font-size:.88rem;">WinterFox_8821</span>
                            <span class="badge bg-success ms-auto" style="font-size:.7rem;">12 posts</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-anon me-2" style="background:#8F5E2F; width:32px; height:32px; font-size:.8rem;">SP</div>
                            <span style="font-size:.88rem;">SilverPine_7740</span>
                            <span class="badge bg-success ms-auto" style="font-size:.7rem;">9 posts</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar-anon me-2" style="background:#F4B41A; width:32px; height:32px; font-size:.8rem;">QM</div>
                            <span style="font-size:.88rem;">QuietMoon_3312</span>
                            <span class="badge bg-secondary ms-auto" style="font-size:.7rem;">5 posts</span>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>
</div>

<!-- Emergency Resources Modal (UC-30) -->
<div class="modal fade" id="emergencyModal" tabindex="-1" aria-labelledby="emergencyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,#ff6b6b,#ffa852); border-radius:16px 16px 0 0;">
                <h5 class="modal-title text-white fw-bold" id="emergencyModalLabel">
                    <i class="bi bi-life-preserver me-2"></i>Emergency Resources
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-secondary-custom mb-3">Select your region to see relevant crisis resources:</p>
                <select class="form-select mb-4" id="regionSelect" onchange="loadResources()" name="regionSelect">
                    <option value="">-- Select Region --</option>
                    <option value="eg">🇪🇬 Egypt</option>
                    <option value="us">🇺🇸 United States</option>
                    <option value="uk">🇬🇧 United Kingdom</option>
                    <option value="intl">🌍 International</option>
                </select>
                <div id="resourcesList"></div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="assets/js/main.js"></script>
<script src="assets/js/forum.js"></script>
</body>

<!--
    Variabled
        postCategory = ( general / anxiety / depression / recovery / gratitude )
        regionSelect = ( eg / us / uk / intl )
        postContent = postContent
-->
</html>
