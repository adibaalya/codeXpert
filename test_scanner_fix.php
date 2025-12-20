<?php
require 'vendor/autoload.php';

use App\Services\InputPreprocessor;

$preprocessor = new InputPreprocessor();

echo "=== Testing Scanner.nextInt() Compatibility Fix ===\n\n";

$problematicInputs = [
    '[1, 2, 3, 4]',
    '`1, 2, 3, 4`',
    '"1, 2, 3, 4"',
    "'1, 2, 3, 4'",
    '[`1`, `2`, `3`]',
    '{"data": [1, 2, 3]}',
];

foreach ($problematicInputs as $input) {
    $output = $preprocessor->process($input, 'java');
    $hasComma = strpos($output, ',') !== false;
    $hasBracket = strpos($output, '[') !== false || strpos($output, ']') !== false;
    $hasQuote = strpos($output, '"') !== false || strpos($output, "'") !== false;
    $hasBacktick = strpos($output, '`') !== false;
    
    $allClean = !$hasComma && !$hasBracket && !$hasQuote && !$hasBacktick;
    
    echo "Input:  {$input}\n";
    echo "Output: {$output}\n";
    echo "Scanner-safe: " . ($allClean ? "✓ YES" : "✗ NO") . "\n";
    
    if ($hasComma) echo "  ⚠️  Still has commas!\n";
    if ($hasBracket) echo "  ⚠️  Still has brackets!\n";
    if ($hasQuote) echo "  ⚠️  Still has quotes!\n";
    if ($hasBacktick) echo "  ⚠️  Still has backticks!\n";
    
    echo "\n";
}

echo "=== Summary ===\n";
echo "All inputs should be clean (only numbers and spaces/newlines)\n";
echo "This prevents Scanner.nextInt() InputMismatchException\n";
