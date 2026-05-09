<?php
/**
 * Test Runner for Role Test Suite
 * Executes all hard code tests and generates reports
 */

// Error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the test suite
require_once __DIR__ . '/RoleTestSuite.php';

echo "Starting Hard Code Tests for Mental Health Care System...\n";
echo "====================================================\n\n";

try {
    // Create and run the test suite
    $testSuite = new RoleTestSuite();
    $testSuite->runAllTests();
    
    echo "\nTest execution completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ TEST EXECUTION FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n❌ FATAL ERROR DURING TESTING\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n====================================================\n";
echo "End of Test Execution\n";
?>
