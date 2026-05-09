<?php
/**
 * REFACTORED Admin Dashboard Example
 * Shows how to eliminate hardcoding using View Helper classes
 */

// Include View Helpers
require_once __DIR__ . '/../../Core/ViewHelpers/AuthHelper.php';
require_once __DIR__ . '/../../Core/ViewHelpers/NavigationHelper.php';
require_once __DIR__ . '/../../Core/ViewHelpers/AssetHelper.php';
require_once __DIR__ . '/../../Core/ViewHelpers/TextHelper.php';
require_once __DIR__ . '/../../Controllers/AdminDashboardController.php';

// Authentication check using AuthHelper
AuthHelper::requireRole('Admin');

// Initialize controllers and data
$controller = new AdminDashboardController();
$dashboardData = $controller->handleRequest();
$userData = $controller->getUserData();

// Get user info
$role = $userData['role'];
$firstName = $userData['first_name'];
$lastName = $userData['last_name'];
$email = $userData['email'];
$age = $userData['age'];
$gender = $userData['gender'];

// Dashboard statistics
$totalPatients = $dashboardData['total_patients'];
$totalTherapists = $dashboardData['total_therapists'];
$highRiskAlerts = $dashboardData['high_risk_alerts'];

// Generate head section using AssetHelper
$headSection = AssetHelper::generateHeadSection(
    TextHelper::get('nav_dashboard') . ' - Mental Health Care',
    [
        'description' => 'Admin dashboard for managing mental health care system',
        'keywords' => 'admin, dashboard, mental health, management'
    ]
);

// Generate navigation using NavigationHelper
$navigation = NavigationHelper::generateNavigation('Admin', 'dashboard');

// Generate logout link
$logoutLink = NavigationHelper::generateLogoutLink();
?>

<!DOCTYPE html>
<html lang="en">
<?php echo $headSection; ?>
<body>

    <div class="container-fluid">
        <div class="row">
            
            <!-- Sidebar with Dynamic Navigation -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white shadow-sm">
                <div class="position-sticky pt-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size: 2rem;"></i>
                        <h5 class="fw-bold text-primary-custom mt-2">MentalCare System</h5>
                    </div>
                    
                    <?php echo $navigation; ?>
                    
                    <hr class="mx-3 mt-5">
                    <div class="px-3">
                        <?php echo $logoutLink; ?>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 fade-in">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2 text-primary-custom fw-bold"><?php echo TextHelper::get('dashboard_overview'); ?></h1>
                    <div class="d-flex align-items-center">
                        <span class="text-secondary-custom me-3">
                            <i class="bi bi-person-circle me-1"></i> 
                            <?php echo 'Age: ' . ($age ?: 'N/A') . ' | ' . $role . ' | ' . htmlspecialchars($firstName . ' ' . $lastName) . ' | ' . $gender; ?>
                        </span>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card card-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-secondary-custom mb-2"><?php echo TextHelper::get('total_patients'); ?></h6>
                                        <h3 class="fw-bold text-primary-custom mb-0"><?php echo $totalPatients; ?></h3>
                                    </div>
                                    <div class="bg-light-green p-3 rounded-circle text-primary-custom">
                                        <i class="bi bi-people fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="card card-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-secondary-custom mb-2"><?php echo TextHelper::get('total_therapists'); ?></h6>
                                        <h3 class="fw-bold text-accent mb-0"><?php echo $totalTherapists; ?></h3>
                                    </div>
                                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                                        <i class="bi bi-person-badge fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card card-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-secondary-custom mb-2"><?php echo TextHelper::get('high_risk_alerts'); ?></h6>
                                        <h3 class="fw-bold text-danger mb-0"><?php echo $highRiskAlerts; ?></h3>
                                    </div>
                                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                                        <i class="bi bi-exclamation-triangle fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-primary-custom">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-secondary-custom">Select a module from the sidebar to begin managing the system.</p>
                                <div class="d-flex gap-3 flex-wrap">
                                    <a href="patients.php" class="btn btn-primary-custom">
                                        <i class="bi bi-arrow-right-circle me-2"></i><?php echo TextHelper::get('nav_patients'); ?>
                                    </a>
                                    <a href="<?php echo AuthHelper::getTherapistRegisterUrl(); ?>" class="btn btn-success">
                                        <i class="bi bi-plus-circle me-2"></i><?php echo TextHelper::get('nav_add_therapist'); ?>
                                    </a>
                                    <a href="therapists.php" class="btn btn-primary-custom">
                                        <i class="bi bi-arrow-right-circle me-2"></i><?php echo TextHelper::get('nav_therapists'); ?>
                                    </a>
                                    <a href="rbac.php" class="btn btn-primary-custom">
                                        <i class="bi bi-arrow-right-circle me-2"></i><?php echo TextHelper::get('nav_rbac'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Toast Container -->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
    </div>

    <!-- Scripts using AssetHelper -->
    <?php echo AssetHelper::generateJSIncludes(); ?>
    
    <!-- Additional JS files -->
    <script src="<?php echo AssetHelper::getAssetUrl('js', 'admin'); ?>"></script>
    
    <!-- Language Switcher -->
    <script>
        function changeLanguage(lang) {
            // This would typically make an AJAX call to set the language
            console.log('Changing language to: ' + lang);
            location.reload();
        }
    </script>
</body>
</html>
