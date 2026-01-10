<?php
require 'vendor/autoload.php';

use App\Services\InputPreprocessor;
use App\Services\CodeExecutionService;

$preprocessor = new InputPreprocessor();
$codeExecutionService = new CodeExecutionService();

echo "=== Testing Java Scanner with Real Code ===\n\n";

// Your exact Java code
$javaCode = '
import java.util.HashSet;
import java.util.Scanner;

public class Main {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        int n = sc.nextInt(); // number of elements
        HashSet<Long> set = new HashSet<>();

        for (int i = 0; i < n; i++) {
            long num = sc.nextLong(); // read number
            set.add(num); // add to set, duplicates ignored
        }

        System.out.println(set.size()); // print number of distinct integers
    }
}
';

// Test with problematic inputs that previously caused InputMismatchException
$testInputs = [
    'Test 1: Brackets with commas' => '{"n": 5, "numbers": [1, 2, 3, 2, 1]}',
    'Test 2: Simple array' => '[1, 2, 3, 4]',
    'Test 3: Backticks' => '`5` `1` `2` `3` `2` `1`',
    'Test 4: Direct format' => '5
1 2 3 2 1',
];

foreach ($testInputs as $testName => $dbInput) {
    echo "$testName\n";
    echo "Database Input: $dbInput\n";
    
    $cleanedInput = $preprocessor->process($dbInput, 'java');
    echo "Cleaned Input: " . str_replace("\n", "\\n", $cleanedInput) . "\n";
    
    // Execute the code
    $result = $codeExecutionService->executeCode($javaCode, 'java', $dbInput);
    
    if ($result['success']) {
        echo "Result: ✓ SUCCESS - Output: " . trim($result['output']) . "\n";
    } else {
        echo "Result: ✗ FAILED\n";
        echo "Error: " . substr($result['output'], 0, 200) . "...\n";
    }
    echo "\n";
}

echo "=== All tests should work without InputMismatchException ===\n";
