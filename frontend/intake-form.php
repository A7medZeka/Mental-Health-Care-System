<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intake Assessment Form - MindCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dimension-header {
            background: var(--light-green);
            border-left: 4px solid var(--primary-green);
            padding: 12px 20px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 1rem;
        }
        .dimension-header h5 { margin: 0; color: var(--primary-green); font-weight: 700; }
        .dimension-header small { color: var(--light-brown); }
        .question-row {
            padding: 14px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .question-row:last-child { border-bottom: none; }
        .question-text { color: var(--text-brown); font-weight: 500; font-size: 0.95rem; }
        .scale-options { display: flex; gap: 6px; flex-wrap: wrap; }
        .scale-option {
            display: none;
        }
        .scale-label {
            display: inline-block;
            padding: 6px 14px;
            border: 2px solid #e9f5f2;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--light-brown);
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .scale-label:hover {
            border-color: var(--secondary-green);
            background: var(--light-green);
        }
        .scale-option:checked + .scale-label {
            background: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
            box-shadow: 0 2px 8px rgba(47,143,126,0.25);
        }
        .progress-section {
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .unanswered { background-color: #fff8f0 !important; border-left: 3px solid #fd7e14; }
    </style>
</head>
<body style="background: var(--bg-offwhite);">

    <!-- Header -->
    <div class="bg-white shadow-sm py-3 mb-4">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="var(--primary-green)" class="bi bi-heart-pulse-fill me-2" viewBox="0 0 16 16">
                        <path d="M1.475 9C2.702 10.84 4.779 12.871 8 15c3.221-2.129 5.298-4.16 6.525-6H12a.5.5 0 0 1-.464-.314l-1.457-3.642-1.598 5.593a.5.5 0 0 1-.945.049L5.889 6.568l-1.473 2.21A.5.5 0 0 1 4 9H1.475Z"/>
                        <path d="M.88 8C-2.427 1.68 4.41-2 7.823 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C11.59-2 18.426 1.68 15.12 8h-2.783l-1.874-4.686a.5.5 0 0 0-.945.049L7.921 8.956 6.464 5.314a.5.5 0 0 0-.88-.091L3.732 8H.88Z"/>
                    </svg>
                    <h5 class="fw-bold text-primary-custom mb-0">MindCare Intake Assessment</h5>
                </div>
                <span class="text-secondary-custom small"><i class="bi bi-shield-check me-1"></i>Your responses are confidential</span>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                <!-- Intro -->
                <div class="card card-custom p-4 mb-4">
                    <h4 class="fw-bold text-primary-custom mb-2">Clinical Intake Assessment</h4>
                    <p class="text-secondary-custom mb-2">This form helps us understand your current mental health condition and determine the appropriate level of care. Please answer each question honestly — there are no right or wrong answers.</p>
                    <div class="alert py-2 px-3 mb-0" style="background:var(--light-green); border:none; border-radius:8px;">
                        <small><i class="bi bi-info-circle text-primary-custom me-1"></i>Rate each statement on a scale: <strong>Never (1)</strong>, <strong>Rarely (2)</strong>, <strong>Sometimes (3)</strong>, <strong>Often (4)</strong>, <strong>Always (5)</strong></small>
                    </div>
                </div>

                <!-- Progress -->
                <div class="progress-section">
                    <div class="container">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-secondary-custom">Progress</small>
                            <small class="fw-bold text-primary-custom" id="progressText">0 / 20 answered</small>
                        </div>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar" id="progressBar" style="width:0%; background:var(--primary-green);"></div>
                        </div>
                    </div>
                </div>

                <form id="intakeForm" class="mt-3">

                    <!-- DIMENSION 1: Symptoms (Weight: 30%) -->
                    <div class="card card-custom p-4 mb-4">
                        <div class="dimension-header">
                            <h5><i class="bi bi-clipboard2-pulse me-2"></i>Section 1: Symptoms</h5>
                            <small>How you have been feeling recently</small>
                        </div>

                        <div class="question-row" data-q="q1">
                            <div class="question-text mb-2">1. I feel persistently sad, empty, or hopeless.</div>
                            <div class="scale-options">
                                <input type="radio" name="q1" value="1" id="q1_1" class="scale-option" onchange="updateProgress()"><label for="q1_1" class="scale-label">Never</label>
                                <input type="radio" name="q1" value="2" id="q1_2" class="scale-option" onchange="updateProgress()"><label for="q1_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q1" value="3" id="q1_3" class="scale-option" onchange="updateProgress()"><label for="q1_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q1" value="4" id="q1_4" class="scale-option" onchange="updateProgress()"><label for="q1_4" class="scale-label">Often</label>
                                <input type="radio" name="q1" value="5" id="q1_5" class="scale-option" onchange="updateProgress()"><label for="q1_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q2">
                            <div class="question-text mb-2">2. I experience excessive worry or anxiety that is hard to control.</div>
                            <div class="scale-options">
                                <input type="radio" name="q2" value="1" id="q2_1" class="scale-option" onchange="updateProgress()"><label for="q2_1" class="scale-label">Never</label>
                                <input type="radio" name="q2" value="2" id="q2_2" class="scale-option" onchange="updateProgress()"><label for="q2_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q2" value="3" id="q2_3" class="scale-option" onchange="updateProgress()"><label for="q2_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q2" value="4" id="q2_4" class="scale-option" onchange="updateProgress()"><label for="q2_4" class="scale-label">Often</label>
                                <input type="radio" name="q2" value="5" id="q2_5" class="scale-option" onchange="updateProgress()"><label for="q2_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q3">
                            <div class="question-text mb-2">3. I have sudden panic attacks or intense fear episodes.</div>
                            <div class="scale-options">
                                <input type="radio" name="q3" value="1" id="q3_1" class="scale-option" onchange="updateProgress()"><label for="q3_1" class="scale-label">Never</label>
                                <input type="radio" name="q3" value="2" id="q3_2" class="scale-option" onchange="updateProgress()"><label for="q3_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q3" value="3" id="q3_3" class="scale-option" onchange="updateProgress()"><label for="q3_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q3" value="4" id="q3_4" class="scale-option" onchange="updateProgress()"><label for="q3_4" class="scale-label">Often</label>
                                <input type="radio" name="q3" value="5" id="q3_5" class="scale-option" onchange="updateProgress()"><label for="q3_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q4">
                            <div class="question-text mb-2">4. I have trouble sleeping (falling asleep, staying asleep, or sleeping too much).</div>
                            <div class="scale-options">
                                <input type="radio" name="q4" value="1" id="q4_1" class="scale-option" onchange="updateProgress()"><label for="q4_1" class="scale-label">Never</label>
                                <input type="radio" name="q4" value="2" id="q4_2" class="scale-option" onchange="updateProgress()"><label for="q4_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q4" value="3" id="q4_3" class="scale-option" onchange="updateProgress()"><label for="q4_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q4" value="4" id="q4_4" class="scale-option" onchange="updateProgress()"><label for="q4_4" class="scale-label">Often</label>
                                <input type="radio" name="q4" value="5" id="q4_5" class="scale-option" onchange="updateProgress()"><label for="q4_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q5">
                            <div class="question-text mb-2">5. I feel emotionally numb or disconnected from reality.</div>
                            <div class="scale-options">
                                <input type="radio" name="q5" value="1" id="q5_1" class="scale-option" onchange="updateProgress()"><label for="q5_1" class="scale-label">Never</label>
                                <input type="radio" name="q5" value="2" id="q5_2" class="scale-option" onchange="updateProgress()"><label for="q5_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q5" value="3" id="q5_3" class="scale-option" onchange="updateProgress()"><label for="q5_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q5" value="4" id="q5_4" class="scale-option" onchange="updateProgress()"><label for="q5_4" class="scale-label">Often</label>
                                <input type="radio" name="q5" value="5" id="q5_5" class="scale-option" onchange="updateProgress()"><label for="q5_5" class="scale-label">Always</label>
                            </div>
                        </div>
                    </div>

                    <!-- DIMENSION 2: Daily Functioning (Weight: 20%) -->
                    <div class="card card-custom p-4 mb-4">
                        <div class="dimension-header">
                            <h5><i class="bi bi-calendar-day me-2"></i>Section 2: Daily Functioning</h5>
                            <small>How your mental health affects daily life</small>
                        </div>

                        <div class="question-row" data-q="q6">
                            <div class="question-text mb-2">6. I have difficulty concentrating or making decisions.</div>
                            <div class="scale-options">
                                <input type="radio" name="q6" value="1" id="q6_1" class="scale-option" onchange="updateProgress()"><label for="q6_1" class="scale-label">Never</label>
                                <input type="radio" name="q6" value="2" id="q6_2" class="scale-option" onchange="updateProgress()"><label for="q6_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q6" value="3" id="q6_3" class="scale-option" onchange="updateProgress()"><label for="q6_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q6" value="4" id="q6_4" class="scale-option" onchange="updateProgress()"><label for="q6_4" class="scale-label">Often</label>
                                <input type="radio" name="q6" value="5" id="q6_5" class="scale-option" onchange="updateProgress()"><label for="q6_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q7">
                            <div class="question-text mb-2">7. I struggle to maintain my daily routine (work, school, hygiene).</div>
                            <div class="scale-options">
                                <input type="radio" name="q7" value="1" id="q7_1" class="scale-option" onchange="updateProgress()"><label for="q7_1" class="scale-label">Never</label>
                                <input type="radio" name="q7" value="2" id="q7_2" class="scale-option" onchange="updateProgress()"><label for="q7_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q7" value="3" id="q7_3" class="scale-option" onchange="updateProgress()"><label for="q7_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q7" value="4" id="q7_4" class="scale-option" onchange="updateProgress()"><label for="q7_4" class="scale-label">Often</label>
                                <input type="radio" name="q7" value="5" id="q7_5" class="scale-option" onchange="updateProgress()"><label for="q7_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q8">
                            <div class="question-text mb-2">8. I have lost interest in activities I used to enjoy.</div>
                            <div class="scale-options">
                                <input type="radio" name="q8" value="1" id="q8_1" class="scale-option" onchange="updateProgress()"><label for="q8_1" class="scale-label">Never</label>
                                <input type="radio" name="q8" value="2" id="q8_2" class="scale-option" onchange="updateProgress()"><label for="q8_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q8" value="3" id="q8_3" class="scale-option" onchange="updateProgress()"><label for="q8_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q8" value="4" id="q8_4" class="scale-option" onchange="updateProgress()"><label for="q8_4" class="scale-label">Often</label>
                                <input type="radio" name="q8" value="5" id="q8_5" class="scale-option" onchange="updateProgress()"><label for="q8_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q9">
                            <div class="question-text mb-2">9. I withdraw from social interactions or avoid people.</div>
                            <div class="scale-options">
                                <input type="radio" name="q9" value="1" id="q9_1" class="scale-option" onchange="updateProgress()"><label for="q9_1" class="scale-label">Never</label>
                                <input type="radio" name="q9" value="2" id="q9_2" class="scale-option" onchange="updateProgress()"><label for="q9_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q9" value="3" id="q9_3" class="scale-option" onchange="updateProgress()"><label for="q9_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q9" value="4" id="q9_4" class="scale-option" onchange="updateProgress()"><label for="q9_4" class="scale-label">Often</label>
                                <input type="radio" name="q9" value="5" id="q9_5" class="scale-option" onchange="updateProgress()"><label for="q9_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q10">
                            <div class="question-text mb-2">10. I experience significant changes in appetite or weight.</div>
                            <div class="scale-options">
                                <input type="radio" name="q10" value="1" id="q10_1" class="scale-option" onchange="updateProgress()"><label for="q10_1" class="scale-label">Never</label>
                                <input type="radio" name="q10" value="2" id="q10_2" class="scale-option" onchange="updateProgress()"><label for="q10_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q10" value="3" id="q10_3" class="scale-option" onchange="updateProgress()"><label for="q10_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q10" value="4" id="q10_4" class="scale-option" onchange="updateProgress()"><label for="q10_4" class="scale-label">Often</label>
                                <input type="radio" name="q10" value="5" id="q10_5" class="scale-option" onchange="updateProgress()"><label for="q10_5" class="scale-label">Always</label>
                            </div>
                        </div>
                    </div>

                    <!-- DIMENSION 3: History / Trauma (Weight: 20%) -->
                    <div class="card card-custom p-4 mb-4">
                        <div class="dimension-header">
                            <h5><i class="bi bi-clock-history me-2"></i>Section 3: History & Trauma</h5>
                            <small>Past experiences that may affect your wellbeing</small>
                        </div>

                        <div class="question-row" data-q="q11">
                            <div class="question-text mb-2">11. I am affected by traumatic events from my past.</div>
                            <div class="scale-options">
                                <input type="radio" name="q11" value="1" id="q11_1" class="scale-option" onchange="updateProgress()"><label for="q11_1" class="scale-label">Never</label>
                                <input type="radio" name="q11" value="2" id="q11_2" class="scale-option" onchange="updateProgress()"><label for="q11_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q11" value="3" id="q11_3" class="scale-option" onchange="updateProgress()"><label for="q11_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q11" value="4" id="q11_4" class="scale-option" onchange="updateProgress()"><label for="q11_4" class="scale-label">Often</label>
                                <input type="radio" name="q11" value="5" id="q11_5" class="scale-option" onchange="updateProgress()"><label for="q11_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q12">
                            <div class="question-text mb-2">12. I experience flashbacks, nightmares, or intrusive memories.</div>
                            <div class="scale-options">
                                <input type="radio" name="q12" value="1" id="q12_1" class="scale-option" onchange="updateProgress()"><label for="q12_1" class="scale-label">Never</label>
                                <input type="radio" name="q12" value="2" id="q12_2" class="scale-option" onchange="updateProgress()"><label for="q12_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q12" value="3" id="q12_3" class="scale-option" onchange="updateProgress()"><label for="q12_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q12" value="4" id="q12_4" class="scale-option" onchange="updateProgress()"><label for="q12_4" class="scale-label">Often</label>
                                <input type="radio" name="q12" value="5" id="q12_5" class="scale-option" onchange="updateProgress()"><label for="q12_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q13">
                            <div class="question-text mb-2">13. I have a family history of mental health conditions.</div>
                            <div class="scale-options">
                                <input type="radio" name="q13" value="1" id="q13_1" class="scale-option" onchange="updateProgress()"><label for="q13_1" class="scale-label">Never</label>
                                <input type="radio" name="q13" value="2" id="q13_2" class="scale-option" onchange="updateProgress()"><label for="q13_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q13" value="3" id="q13_3" class="scale-option" onchange="updateProgress()"><label for="q13_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q13" value="4" id="q13_4" class="scale-option" onchange="updateProgress()"><label for="q13_4" class="scale-label">Often</label>
                                <input type="radio" name="q13" value="5" id="q13_5" class="scale-option" onchange="updateProgress()"><label for="q13_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q14">
                            <div class="question-text mb-2">14. I have previously received therapy or psychiatric treatment.</div>
                            <div class="scale-options">
                                <input type="radio" name="q14" value="1" id="q14_1" class="scale-option" onchange="updateProgress()"><label for="q14_1" class="scale-label">Never</label>
                                <input type="radio" name="q14" value="2" id="q14_2" class="scale-option" onchange="updateProgress()"><label for="q14_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q14" value="3" id="q14_3" class="scale-option" onchange="updateProgress()"><label for="q14_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q14" value="4" id="q14_4" class="scale-option" onchange="updateProgress()"><label for="q14_4" class="scale-label">Often</label>
                                <input type="radio" name="q14" value="5" id="q14_5" class="scale-option" onchange="updateProgress()"><label for="q14_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q15">
                            <div class="question-text mb-2">15. I use substances (alcohol, drugs) to cope with my emotions.</div>
                            <div class="scale-options">
                                <input type="radio" name="q15" value="1" id="q15_1" class="scale-option" onchange="updateProgress()"><label for="q15_1" class="scale-label">Never</label>
                                <input type="radio" name="q15" value="2" id="q15_2" class="scale-option" onchange="updateProgress()"><label for="q15_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q15" value="3" id="q15_3" class="scale-option" onchange="updateProgress()"><label for="q15_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q15" value="4" id="q15_4" class="scale-option" onchange="updateProgress()"><label for="q15_4" class="scale-label">Often</label>
                                <input type="radio" name="q15" value="5" id="q15_5" class="scale-option" onchange="updateProgress()"><label for="q15_5" class="scale-label">Always</label>
                            </div>
                        </div>
                    </div>

                    <!-- DIMENSION 4: Safety / Crisis (Weight: 30%) -->
                    <div class="card card-custom p-4 mb-4">
                        <div class="dimension-header" style="border-left-color:#dc3545; background:#fdf2f2;">
                            <h5 style="color:#dc3545;"><i class="bi bi-exclamation-triangle me-2"></i>Section 4: Safety & Crisis Indicators</h5>
                            <small style="color:var(--light-brown);">Please answer honestly — your safety is our priority</small>
                        </div>

                        <div class="question-row" data-q="q16">
                            <div class="question-text mb-2">16. I have thoughts of harming myself.</div>
                            <div class="scale-options">
                                <input type="radio" name="q16" value="1" id="q16_1" class="scale-option" onchange="updateProgress()"><label for="q16_1" class="scale-label">Never</label>
                                <input type="radio" name="q16" value="2" id="q16_2" class="scale-option" onchange="updateProgress()"><label for="q16_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q16" value="3" id="q16_3" class="scale-option" onchange="updateProgress()"><label for="q16_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q16" value="4" id="q16_4" class="scale-option" onchange="updateProgress()"><label for="q16_4" class="scale-label">Often</label>
                                <input type="radio" name="q16" value="5" id="q16_5" class="scale-option" onchange="updateProgress()"><label for="q16_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q17">
                            <div class="question-text mb-2">17. I have thoughts of ending my life.</div>
                            <div class="scale-options">
                                <input type="radio" name="q17" value="1" id="q17_1" class="scale-option" onchange="updateProgress()"><label for="q17_1" class="scale-label">Never</label>
                                <input type="radio" name="q17" value="2" id="q17_2" class="scale-option" onchange="updateProgress()"><label for="q17_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q17" value="3" id="q17_3" class="scale-option" onchange="updateProgress()"><label for="q17_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q17" value="4" id="q17_4" class="scale-option" onchange="updateProgress()"><label for="q17_4" class="scale-label">Often</label>
                                <input type="radio" name="q17" value="5" id="q17_5" class="scale-option" onchange="updateProgress()"><label for="q17_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q18">
                            <div class="question-text mb-2">18. I feel like a burden to others and that they would be better off without me.</div>
                            <div class="scale-options">
                                <input type="radio" name="q18" value="1" id="q18_1" class="scale-option" onchange="updateProgress()"><label for="q18_1" class="scale-label">Never</label>
                                <input type="radio" name="q18" value="2" id="q18_2" class="scale-option" onchange="updateProgress()"><label for="q18_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q18" value="3" id="q18_3" class="scale-option" onchange="updateProgress()"><label for="q18_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q18" value="4" id="q18_4" class="scale-option" onchange="updateProgress()"><label for="q18_4" class="scale-label">Often</label>
                                <input type="radio" name="q18" value="5" id="q18_5" class="scale-option" onchange="updateProgress()"><label for="q18_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q19">
                            <div class="question-text mb-2">19. I have engaged in self-harming behaviors (cutting, burning, etc.).</div>
                            <div class="scale-options">
                                <input type="radio" name="q19" value="1" id="q19_1" class="scale-option" onchange="updateProgress()"><label for="q19_1" class="scale-label">Never</label>
                                <input type="radio" name="q19" value="2" id="q19_2" class="scale-option" onchange="updateProgress()"><label for="q19_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q19" value="3" id="q19_3" class="scale-option" onchange="updateProgress()"><label for="q19_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q19" value="4" id="q19_4" class="scale-option" onchange="updateProgress()"><label for="q19_4" class="scale-label">Often</label>
                                <input type="radio" name="q19" value="5" id="q19_5" class="scale-option" onchange="updateProgress()"><label for="q19_5" class="scale-label">Always</label>
                            </div>
                        </div>

                        <div class="question-row" data-q="q20">
                            <div class="question-text mb-2">20. I feel unsafe in my current living situation.</div>
                            <div class="scale-options">
                                <input type="radio" name="q20" value="1" id="q20_1" class="scale-option" onchange="updateProgress()"><label for="q20_1" class="scale-label">Never</label>
                                <input type="radio" name="q20" value="2" id="q20_2" class="scale-option" onchange="updateProgress()"><label for="q20_2" class="scale-label">Rarely</label>
                                <input type="radio" name="q20" value="3" id="q20_3" class="scale-option" onchange="updateProgress()"><label for="q20_3" class="scale-label">Sometimes</label>
                                <input type="radio" name="q20" value="4" id="q20_4" class="scale-option" onchange="updateProgress()"><label for="q20_4" class="scale-label">Often</label>
                                <input type="radio" name="q20" value="5" id="q20_5" class="scale-option" onchange="updateProgress()"><label for="q20_5" class="scale-label">Always</label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="card card-custom p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="text-secondary-custom mb-0 small">Please review your answers before submitting.</p>
                            <button type="submit" class="btn btn-primary-custom px-4 py-2 fw-semibold">
                                <i class="bi bi-check-circle me-1"></i> Submit Assessment
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- Result Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border:none; border-radius:16px;">
                <div class="modal-body text-center p-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light-green rounded-circle mb-3" style="width:80px;height:80px;">
                        <i class="bi bi-check-circle fs-1 text-primary-custom"></i>
                    </div>
                    <h4 class="fw-bold text-primary-custom mb-2">Assessment Submitted!</h4>
                    <p class="text-secondary-custom mb-2">Thank you for completing your intake assessment.</p>
                    <div class="alert py-3 px-3 mb-3" style="background:var(--light-green); border:none; border-radius:8px;">
                        <p class="mb-1 fw-semibold" style="color:var(--text-brown);">Your Level of Care: <span id="levelOfCare" class="text-primary-custom fw-bold"></span></p>
                        <p class="mb-0 small text-secondary-custom">Total Score: <span id="totalScore"></span> / 100</p>
                    </div>
                    <p class="text-secondary-custom small mb-4">Your results have been recorded. A therapist will be matched based on your assessment. You can now close this tab.</p>
                    <button type="button" class="btn btn-primary-custom w-100 py-2 fw-semibold" onclick="window.close();">Close</button>
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
        const TOTAL_QUESTIONS = 20;

        function updateProgress() {
            let answered = 0;
            for (let i = 1; i <= TOTAL_QUESTIONS; i++) {
                if (document.querySelector('input[name="q' + i + '"]:checked')) answered++;
            }
            document.getElementById('progressText').textContent = answered + ' / ' + TOTAL_QUESTIONS + ' answered';
            document.getElementById('progressBar').style.width = (answered / TOTAL_QUESTIONS * 100) + '%';
        }

        document.getElementById('intakeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Check all questions are answered
            let allAnswered = true;
            for (let i = 1; i <= TOTAL_QUESTIONS; i++) {
                const row = document.querySelector('[data-q="q' + i + '"]');
                if (!document.querySelector('input[name="q' + i + '"]:checked')) {
                    row.classList.add('unanswered');
                    allAnswered = false;
                } else {
                    row.classList.remove('unanswered');
                }
            }
            if (!allAnswered) {
                showToast('Please answer all questions before submitting.', 'error');
                document.querySelector('.unanswered').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Gather scores per dimension
            // Symptoms: q1-q5, Daily Functioning: q6-q10, History/Trauma: q11-q15, Safety/Crisis: q16-q20
            function getDimensionAvg(start, end) {
                let sum = 0;
                for (let i = start; i <= end; i++) {
                    sum += parseInt(document.querySelector('input[name="q' + i + '"]:checked').value);
                }
                return sum / (end - start + 1);
            }

            const symptomsAvg = getDimensionAvg(1, 5);       // Weight: 30%
            const functioningAvg = getDimensionAvg(6, 10);    // Weight: 20%
            const historyAvg = getDimensionAvg(11, 15);       // Weight: 20%
            const safetyAvg = getDimensionAvg(16, 20);        // Weight: 30%

            // Normalize averages to 0-1 scale (from 1-5 scale): (avg - 1) / 4
            const normalizedSymptoms = (symptomsAvg - 1) / 4;
            const normalizedFunctioning = (functioningAvg - 1) / 4;
            const normalizedHistory = (historyAvg - 1) / 4;
            const normalizedSafety = (safetyAvg - 1) / 4;

            // Total_Score = Sum(Dimension_Average × Weight × 100)
            let totalScore = (normalizedSymptoms * 0.30 + normalizedFunctioning * 0.20 + normalizedHistory * 0.20 + normalizedSafety * 0.30) * 100;
            totalScore = Math.round(totalScore);

            // Override rule: if any safety question (q16-q20) >= 4 (mapped from 1-5 scale; 4 = "Often", 5 = "Always" which maps to >7 on 1-10 scale)
            let crisisOverride = false;
            for (let i = 16; i <= 20; i++) {
                const val = parseInt(document.querySelector('input[name="q' + i + '"]:checked').value);
                if (val >= 4) { crisisOverride = true; break; }
            }

            // Determine level of care
            let level;
            if (crisisOverride) {
                level = 'Crisis';
            } else if (totalScore <= 25) {
                level = 'Low';
            } else if (totalScore <= 60) {
                level = 'Moderate';
            } else if (totalScore <= 85) {
                level = 'High';
            } else {
                level = 'Crisis';
            }

            // Show result modal
            document.getElementById('totalScore').textContent = totalScore;
            const levelEl = document.getElementById('levelOfCare');
            levelEl.textContent = level;
            if (level === 'Crisis') levelEl.style.color = '#dc3545';
            else if (level === 'High') levelEl.style.color = '#fd7e14';
            else if (level === 'Moderate') levelEl.style.color = '#ffc107';
            else levelEl.style.color = '#2F8F7E';

            var modal = new bootstrap.Modal(document.getElementById('resultModal'));
            modal.show();
        });
    </script>
</body>
</html>
