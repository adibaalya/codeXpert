<?php
require 'vendor/autoload.php';

// Simulate the backend response format
$backendResponse = [
    'success' => true,
    'passedTests' => 3,
    'totalTests' => 5,
    'testResults' => [
        [
            'test_number' => 1,
            'input' => '{"n": 5, "arr": [1,2,3,2,1]}',
            'expected' => '3',
            'actual' => '3',
            'passed' => true
        ],
        [
            'test_number' => 2,
            'input' => '[1, 2, 3, 4]',
            'expected' => '4',
            'actual' => '1',
            'passed' => false
        ]
    ],
    'message' => '⚠ Some test cases failed (3/5)'
];

echo "Backend Response:\n";
echo json_encode($backendResponse, JSON_PRETTY_PRINT);
echo "\n\n";

echo "Frontend expects:\n";
echo "- data.success (bool)\n";
echo "- data.passedTests (int)\n";
echo "- data.totalTests (int)\n";
echo "- data.testResults (array)\n";
echo "- data.testResults[i].test_number\n";
echo "- data.testResults[i].input\n";
echo "- data.testResults[i].expected\n";
echo "- data.testResults[i].actual\n";
echo "- data.testResults[i].passed\n";
