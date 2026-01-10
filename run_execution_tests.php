<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\CodeExecutionService;

// ANSI color codes for terminal output
class Colors {
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const MAGENTA = "\033[35m";
    const RESET = "\033[0m";
    const BOLD = "\033[1m";
}

class TestRunner {
    private $service;
    private $passed = 0;
    private $failed = 0;
    private $errors = 0;
    private $failedTests = [];
    
    public function __construct() {
        $this->service = new CodeExecutionService();
    }
    
    public function run() {
        echo Colors::BOLD . Colors::CYAN . "\n";
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║           CodeExecutionService Test Suite Runner              ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n";
        echo Colors::RESET . "\n";
        
        // Load test cases
        $testsFile = __DIR__ . '/execution_tests.json';
        if (!file_exists($testsFile)) {
            echo Colors::RED . "Error: execution_tests.json not found!\n" . Colors::RESET;
            return;
        }
        
        $json = file_get_contents($testsFile);
        $data = json_decode($json, true);
        
        if (!isset($data['test_cases'])) {
            echo Colors::RED . "Error: Invalid test file format!\n" . Colors::RESET;
            return;
        }
        
        $testCases = $data['test_cases'];
        $totalTests = count($testCases);
        
        echo Colors::BLUE . "Found $totalTests test cases\n" . Colors::RESET;
        echo str_repeat("─", 70) . "\n\n";
        
        // Run each test
        foreach ($testCases as $index => $test) {
            $this->runTest($index + 1, $test, $totalTests);
        }
        
        // Summary
        $this->printSummary();
        
        // Show failed tests with debug info
        if (!empty($this->failedTests)) {
            $this->showDebugInfo();
        }
    }
    
    private function runTest($number, $test, $total) {
        $name = $test['name'];
        $language = $test['language'];
        $userCode = $test['user_code'];
        $testInput = $test['test_input'];
        $expected = $test['expected_output'];
        
        // Display test info
        echo Colors::BOLD . "[$number/$total] " . Colors::RESET;
        echo Colors::CYAN . $name . Colors::RESET . "\n";
        echo "        Language: " . Colors::YELLOW . strtoupper($language) . Colors::RESET . "\n";
        
        try {
            // Execute the test
            $result = $this->service->executeCode($userCode, $language, $testInput);
            
            if (!$result['success']) {
                $this->errors++;
                echo "        Status: " . Colors::RED . "ERROR" . Colors::RESET . "\n";
                echo "        Message: " . $result['output'] . "\n";
                
                $this->failedTests[] = [
                    'number' => $number,
                    'name' => $name,
                    'language' => $language,
                    'user_code' => $userCode,
                    'test_input' => $testInput,
                    'expected' => $expected,
                    'error' => $result['output']
                ];
            } else {
                // Normalize output for comparison
                $actual = $this->normalizeOutput($result['output']);
                $expectedNorm = $this->normalizeOutput($expected);
                
                if ($actual === $expectedNorm) {
                    $this->passed++;
                    echo "        Status: " . Colors::GREEN . "✓ PASSED" . Colors::RESET . "\n";
                    echo "        Output: " . Colors::GREEN . $actual . Colors::RESET . "\n";
                } else {
                    $this->failed++;
                    echo "        Status: " . Colors::RED . "✗ FAILED" . Colors::RESET . "\n";
                    echo "        Expected: " . Colors::GREEN . $expectedNorm . Colors::RESET . "\n";
                    echo "        Actual:   " . Colors::RED . $actual . Colors::RESET . "\n";
                    
                    $this->failedTests[] = [
                        'number' => $number,
                        'name' => $name,
                        'language' => $language,
                        'user_code' => $userCode,
                        'test_input' => $testInput,
                        'expected' => $expectedNorm,
                        'actual' => $actual
                    ];
                }
            }
        } catch (Exception $e) {
            $this->errors++;
            echo "        Status: " . Colors::RED . "EXCEPTION" . Colors::RESET . "\n";
            echo "        Message: " . $e->getMessage() . "\n";
            
            $this->failedTests[] = [
                'number' => $number,
                'name' => $name,
                'language' => $language,
                'user_code' => $userCode,
                'test_input' => $testInput,
                'expected' => $expected,
                'exception' => $e->getMessage()
            ];
        }
        
        echo "\n";
    }
    
    private function normalizeOutput($output) {
        // Remove whitespace variations
        $output = trim($output);
        // Remove spaces around brackets and commas for array comparison
        $output = preg_replace('/\s*,\s*/', ',', $output);
        $output = preg_replace('/\s*\[\s*/', '[', $output);
        $output = preg_replace('/\s*\]\s*/', ']', $output);
        return $output;
    }
    
    private function printSummary() {
        echo str_repeat("═", 70) . "\n";
        echo Colors::BOLD . Colors::CYAN . "TEST SUMMARY\n" . Colors::RESET;
        echo str_repeat("═", 70) . "\n\n";
        
        $total = $this->passed + $this->failed + $this->errors;
        
        echo Colors::GREEN . "✓ Passed:  " . $this->passed . "/$total" . Colors::RESET . "\n";
        echo Colors::RED . "✗ Failed:  " . $this->failed . "/$total" . Colors::RESET . "\n";
        echo Colors::YELLOW . "⚠ Errors:  " . $this->errors . "/$total" . Colors::RESET . "\n\n";
        
        $percentage = $total > 0 ? round(($this->passed / $total) * 100, 2) : 0;
        
        if ($percentage == 100) {
            echo Colors::GREEN . Colors::BOLD . "SUCCESS RATE: $percentage%" . Colors::RESET . "\n";
        } elseif ($percentage >= 70) {
            echo Colors::YELLOW . Colors::BOLD . "SUCCESS RATE: $percentage%" . Colors::RESET . "\n";
        } else {
            echo Colors::RED . Colors::BOLD . "SUCCESS RATE: $percentage%" . Colors::RESET . "\n";
        }
        
        echo "\n";
    }
    
    private function showDebugInfo() {
        echo str_repeat("═", 70) . "\n";
        echo Colors::BOLD . Colors::MAGENTA . "DEBUG MODE - FAILED TEST DRIVER SCRIPTS\n" . Colors::RESET;
        echo str_repeat("═", 70) . "\n\n";
        
        echo Colors::YELLOW . "Generating driver scripts for failed tests...\n\n" . Colors::RESET;
        
        foreach ($this->failedTests as $test) {
            echo Colors::RED . Colors::BOLD . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" . Colors::RESET;
            echo Colors::BOLD . "[Test #{$test['number']}] {$test['name']}\n" . Colors::RESET;
            echo Colors::RED . Colors::BOLD . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" . Colors::RESET;
            echo "\n";
            
            // Show failure reason
            if (isset($test['exception'])) {
                echo Colors::RED . "Exception: " . Colors::RESET . $test['exception'] . "\n\n";
            } elseif (isset($test['error'])) {
                echo Colors::RED . "Error: " . Colors::RESET . $test['error'] . "\n\n";
            } else {
                echo Colors::YELLOW . "Expected: " . Colors::RESET . $test['expected'] . "\n";
                echo Colors::RED . "Actual:   " . Colors::RESET . $test['actual'] . "\n\n";
            }
            
            // Generate debug output
            try {
                echo Colors::CYAN . "Generated Driver Script ({$test['language']}):\n" . Colors::RESET;
                echo Colors::BLUE . str_repeat("─", 70) . "\n" . Colors::RESET;
                
                $debugResult = $this->service->executeCode(
                    $test['user_code'],
                    $test['language'],
                    $test['test_input'],
                    null,
                    [],
                    true  // Debug mode enabled
                );
                
                if ($debugResult['success']) {
                    // Syntax highlight the output
                    $driverScript = $debugResult['output'];
                    echo $driverScript . "\n";
                } else {
                    echo Colors::RED . "Failed to generate driver script\n" . Colors::RESET;
                }
                
                echo Colors::BLUE . str_repeat("─", 70) . "\n" . Colors::RESET;
                
            } catch (Exception $e) {
                echo Colors::RED . "Debug Error: " . $e->getMessage() . "\n" . Colors::RESET;
            }
            
            echo "\n";
        }
    }
}

// Run the tests
$runner = new TestRunner();
$runner->run();

