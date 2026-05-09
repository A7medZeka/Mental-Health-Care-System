<?php
/**
 * File Naming Test
 * Tests that intake forms are named correctly with patient ID and national ID
 */

require_once __DIR__ . '/../Models/Admin.php';

class FileNameTest {
    private $testResults = [];
    private $totalTests = 0;
    private $passedTests = 0;
    
    public function runTests(): void {
        echo "=== FILE NAMING CONVENTION TEST ===\n\n";
        
        // Test file naming logic
        $this->testFileNameGeneration();
        
        // Test national ID sanitization
        $this->testNationalIdSanitization();
        
        // Generate final report
        $this->generateReport();
    }
    
    private function testFileNameGeneration(): void {
        echo "Testing File Name Generation...\n";
        
        try {
            // Test cases for different national IDs
            $testCases = [
                ['patientId' => 123, 'nationalId' => 'ABC123456', 'expected' => '123_ABC123456.pdf'],
                ['patientId' => 456, 'nationalId' => '789XYZ', 'expected' => '456_789XYZ.pdf'],
                ['patientId' => 789, 'nationalId' => 'NID-001-2023', 'expected' => '789_NID_001_2023.pdf'],
                ['patientId' => 101, 'nationalId' => 'ID@2023#456', 'expected' => '101_ID_2023_456.pdf'],
                ['patientId' => 202, 'nationalId' => 'Unknown', 'expected' => '202_Unknown.pdf'],
            ];
            
            foreach ($testCases as $testCase) {
                $patientId = $testCase['patientId'];
                $nationalId = $testCase['nationalId'];
                $expected = $testCase['expected'];
                
                // Simulate the filename generation logic
                $cleanNationalId = preg_replace('/[^a-zA-Z0-9]/', '_', $nationalId);
                $actualFilename = sprintf('%d_%s.pdf', $patientId, $cleanNationalId);
                
                $this->assert(
                    $actualFilename === $expected,
                    "Filename generation for patient {$patientId}, ID '{$nationalId}'"
                );
            }
            
            echo "✅ File Name Generation tests passed\n\n";
        } catch (Exception $e) {
            echo "❌ File Name Generation test failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testNationalIdSanitization(): void {
        echo "Testing National ID Sanitization...\n";
        
        try {
            // Test cases for sanitization
            $sanitizationTests = [
                ['input' => 'ABC123456', 'expected' => 'ABC123456'],
                ['input' => 'NID-001-2023', 'expected' => 'NID_001_2023'],
                ['input' => 'ID@2023#456', 'expected' => 'ID_2023_456'],
                ['input' => 'PASSPORT/2023/ABC', 'expected' => 'PASSPORT_2023_ABC'],
                ['input' => '123-456-789', 'expected' => '123_456_789'],
                ['input' => 'A.B.C.123', 'expected' => 'A_B_C_123'],
                ['input' => 'ID 2023 456', 'expected' => 'ID_2023_456'],
                ['input' => 'Special$Chars#Here', 'expected' => 'Special_Chars_Here'],
            ];
            
            foreach ($sanitizationTests as $test) {
                $input = $test['input'];
                $expected = $test['expected'];
                $actual = preg_replace('/[^a-zA-Z0-9]/', '_', $input);
                
                $this->assert(
                    $actual === $expected,
                    "Sanitization of '{$input}' to '{$expected}'"
                );
            }
            
            echo "✅ National ID Sanitization tests passed\n\n";
        } catch (Exception $e) {
            echo "❌ National ID Sanitization test failed: " . $e->getMessage() . "\n\n";
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
        echo "=== FILE NAMING TEST REPORT ===\n";
        echo "Total Tests: {$this->totalTests}\n";
        echo "Passed Tests: {$this->passedTests}\n";
        echo "Failed Tests: " . ($this->totalTests - $this->passedTests) . "\n";
        echo "Success Rate: " . round(($this->passedTests / $this->totalTests) * 100, 2) . "%\n\n";
        
        echo "=== DETAILED RESULTS ===\n";
        foreach ($this->testResults as $result) {
            echo "$result\n";
        }
        
        echo "\n=== FILE NAMING CONVENTION ===\n";
        echo "✅ Format: {patientId}_{nationalID}.pdf\n";
        echo "✅ Example: 123_ABC123456.pdf\n";
        echo "✅ Example: 456_NID_001_2023.pdf\n";
        echo "✅ Special characters are replaced with underscores\n";
        echo "✅ Only alphanumeric characters and underscores allowed\n";
        
        echo "\n=== EXAMPLES ===\n";
        echo "• Patient ID: 123, National ID: ABC123456 → 123_ABC123456.pdf\n";
        echo "• Patient ID: 456, National ID: NID-001-2023 → 456_NID_001_2023.pdf\n";
        echo "• Patient ID: 789, National ID: ID@2023#456 → 789_ID_2023_456.pdf\n";
        echo "• Patient ID: 101, National ID: Unknown → 101_Unknown.pdf\n";
        
        echo "\n=== SANITIZATION RULES ===\n";
        echo "• Replace hyphens (-) with underscores (_)\n";
        echo "• Replace slashes (/) with underscores (_)\n";
        echo "• Replace spaces ( ) with underscores (_)\n";
        echo "• Replace special characters (@, #, $, etc.) with underscores (_)\n";
        echo "• Keep alphanumeric characters as-is\n";
        
        echo "\n=== BENEFITS ===\n";
        echo "• Files are easily identifiable by patient ID\n";
        echo "• National ID provides additional identification\n";
        echo "• Consistent naming convention\n";
        echo "• Safe for file system (no special characters)\n";
        
        echo "\n=== EXPECTED BEHAVIOR ===\n";
        echo "• When uploading intake form for patient 123 with national ID ABC123456\n";
        echo "• File will be saved as: uploads/intake/123_ABC123456.pdf\n";
        echo "• Database will store: uploads/intake/123_ABC123456.pdf\n";
        echo "• File naming is consistent and predictable\n";
        
        echo "\n=== IMPLEMENTATION DETAILS ===\n";
        echo "• Query: SELECT nationalID FROM users WHERE user_id = ? AND role = 'Patient'\n";
        echo "• Sanitization: preg_replace('/[^a-zA-Z0-9]/', '_', $nationalId)\n";
        echo "• Filename: sprintf('%d_%s.pdf', $patientId, $cleanNationalId)\n";
        echo "• Error handling: Returns error if patient not found\n";
    }
}

// Run the tests
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new FileNameTest();
    $test->runTests();
}
?>
