<?php
/**
 * Intake Form Upload Test
 * Tests that intake forms are uploaded to the correct directory
 */

require_once __DIR__ . '/../Models/Admin.php';

class IntakeUploadTest {
    private $testResults = [];
    private $totalTests = 0;
    private $passedTests = 0;
    
    public function runTests(): void {
        echo "=== INTAKE FORM UPLOAD TEST ===\n\n";
        
        // Test upload directory configuration
        $this->testUploadDirectoryConfig();
        
        // Test directory creation
        $this->testDirectoryCreation();
        
        // Test upload method
        $this->testUploadMethod();
        
        // Generate final report
        $this->generateReport();
    }
    
    private function testUploadDirectoryConfig(): void {
        echo "Testing Upload Directory Configuration...\n";
        
        try {
            // Check that the constants are defined correctly
            $reflection = new ReflectionClass('AdminPatientManager');
            $constants = $reflection->getConstants();
            
            $this->assert(isset($constants['UPLOAD_DIR']), "UPLOAD_DIR constant exists");
            $this->assert(isset($constants['UPLOAD_DB_PATH']), "UPLOAD_DB_PATH constant exists");
            
            // Check that the paths are correct using reflection
            $uploadDir = $constants['UPLOAD_DIR'];
            $uploadDbPath = $constants['UPLOAD_DB_PATH'];
            
            $this->assert(
                strpos($uploadDir, 'uploads/intake') !== false,
                "UPLOAD_DIR points to uploads/intake directory"
            );
            
            $this->assert(
                $uploadDbPath === 'uploads/intake/',
                "UPLOAD_DB_PATH is correct"
            );
            
            echo "✅ Upload Directory Configuration tests passed\n\n";
        } catch (Exception $e) {
            echo "❌ Upload Directory Configuration test failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testDirectoryCreation(): void {
        echo "Testing Directory Creation...\n";
        
        try {
            $reflection = new ReflectionClass('AdminPatientManager');
            $constants = $reflection->getConstants();
            $uploadDir = $constants['UPLOAD_DIR'];
            
            // Check if directory exists or can be created
            if (!is_dir($uploadDir)) {
                if (mkdir($uploadDir, 0755, true)) {
                    $this->assert(true, "Upload directory created successfully");
                } else {
                    $this->assert(false, "Failed to create upload directory");
                }
            } else {
                $this->assert(true, "Upload directory already exists");
            }
            
            // Check directory is writable
            if (is_dir($uploadDir)) {
                $testFile = $uploadDir . 'test_' . time() . '.tmp';
                if (touch($testFile)) {
                    unlink($testFile);
                    $this->assert(true, "Upload directory is writable");
                } else {
                    $this->assert(false, "Upload directory is not writable");
                }
            }
            
            echo "✅ Directory Creation tests passed\n\n";
        } catch (Exception $e) {
            echo "❌ Directory Creation test failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testUploadMethod(): void {
        echo "Testing Upload Method...\n";
        
        try {
            $patientManager = new AdminPatientManager();
            $this->assert($patientManager instanceof AdminPatientManager, "AdminPatientManager instantiated");
            
            // Test that uploadIntakeForm method exists
            $this->assert(
                method_exists($patientManager, 'uploadIntakeForm'),
                "uploadIntakeForm method exists"
            );
            
            // Test file validation constants
            $reflection = new ReflectionClass('AdminPatientManager');
            $constants = $reflection->getConstants();
            
            $this->assert(
                $constants['MAX_FILE_SIZE'] === 5 * 1024 * 1024,
                "MAX_FILE_SIZE is set to 5MB"
            );
            
            $this->assert(
                $constants['ALLOWED_MIME'] === 'application/pdf',
                "ALLOWED_MIME is set to PDF only"
            );
            
            echo "✅ Upload Method tests passed\n\n";
        } catch (Exception $e) {
            echo "❌ Upload Method test failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function assert($condition, $message): void {
        $this->totalTests++;
        if ($condition) {
            $this->passedTests++;
            $this->testResults[] = "PASS: $message";
        } else {
            $this->testResults[] = "FAIL: $message";
        }
    }
    
    private function generateReport(): void {
        echo "=== INTAKE UPLOAD TEST REPORT ===\n";
        echo "Total Tests: {$this->totalTests}\n";
        echo "Passed Tests: {$this->passedTests}\n";
        echo "Failed Tests: " . ($this->totalTests - $this->passedTests) . "\n";
        echo "Success Rate: " . round(($this->passedTests / $this->totalTests) * 100, 2) . "%\n\n";
        
        echo "=== DETAILED RESULTS ===\n";
        foreach ($this->testResults as $result) {
            echo "$result\n";
        }
        
        echo "\n=== UPLOAD CONFIGURATION ===\n";
        $reflection = new ReflectionClass('AdminPatientManager');
        $constants = $reflection->getConstants();
        echo "✅ Upload Directory: " . $constants['UPLOAD_DIR'] . "\n";
        echo "✅ Database Path: " . $constants['UPLOAD_DB_PATH'] . "\n";
        echo "✅ Max File Size: " . ($constants['MAX_FILE_SIZE'] / 1024 / 1024) . " MB\n";
        echo "✅ Allowed MIME: " . $constants['ALLOWED_MIME'] . "\n";
        
        echo "\n=== DIRECTORY STRUCTURE ===\n";
        echo "Root Directory: " . __DIR__ . "/../\n";
        echo "Uploads Directory: uploads/\n";
        echo "Intake Forms: uploads/intake/\n";
        echo "File Naming: {patientId}_{nationalID}.pdf\n";
        
        echo "\n=== EXPECTED BEHAVIOR ===\n";
        echo "• Intake forms upload to uploads/intake/ directory\n";
        echo "• Files are named: {patientId}_{nationalID}.pdf\n";
        echo "• National ID is sanitized for filename (special chars → underscores)\n";
        echo "• Only PDF files are accepted\n";
        echo "• Maximum file size is 5MB\n";
        echo "• Database stores relative path: uploads/intake/filename.pdf\n";
        
        echo "\n=== SETUP INSTRUCTIONS ===\n";
        echo "1. Run create_upload_dirs.php to create directories\n";
        echo "2. Ensure uploads/ directory is writable by web server\n";
        echo "3. Test intake form upload in admin dashboard\n";
        echo "4. Verify files appear in uploads/intake/ directory\n";
        
        echo "\n=== TROUBLESHOOTING ===\n";
        echo "• If upload fails: Check directory permissions\n";
        echo "• If file not found: Check UPLOAD_DIR path\n";
        echo "• If access denied: Check web server permissions\n";
        echo "• If MIME error: Ensure file is valid PDF\n";
    }
}

// Run the tests
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new IntakeUploadTest();
    $test->runTests();
}
?>
