# Output Normalization System - Presentation Error Handling

## Overview

This system implements a robust **Output Normalization** strategy to handle common presentation errors in Online Judge (OJ) systems. Instead of rejecting solutions due to minor formatting differences (trailing spaces, newlines, quote artifacts), the system uses smart comparison that normalizes both user output and expected output before comparison.

## The Problem

In competitive programming and coding challenges, users often get "Wrong Answer" verdicts even when their logic is correct, simply because of:

1. **Trailing Whitespace**: `"5 "` vs `"5"`
2. **Line Endings**: Windows (`\r\n`) vs Linux (`\n`)
3. **Quote Artifacts**: `"Hello World"` vs `Hello World` (from JSON encoding)
4. **Inconsistent JSON formatting**: `[1,2,3]` vs `[1, 2, 3]`
5. **Boolean representations**: `true` vs `1` vs `True`

This is often called a **"Presentation Error" (PE)** in competitive programming platforms like UVa Online Judge.

## The Solution

### OutputNormalizer Class

Location: `app/Services/OutputNormalizer.php`

This class provides multiple normalization methods:

#### 1. Basic Normalization (`normalize()`)
```php
OutputNormalizer::normalize($output)
```

**What it does:**
- Removes surrounding quotes (only if the entire string is wrapped)
- Standardizes newlines (converts `\r\n` and `\r` to `\n`)
- Trims each line (removes trailing spaces)
- Removes empty lines at the end
- Final trim of the entire output

**Example:**
```php
$input = "\"Hello World\" \n";
$output = OutputNormalizer::normalize($input);
// Result: "Hello World"
```

#### 2. Smart Comparison (`smartCompare()`)
```php
$result = OutputNormalizer::smartCompare($userOutput, $expectedOutput);
```

**What it does:**
- First tries standard comparison
- Falls back to boolean normalization (`true`/`false`/`1`/`0`)
- Then tries JSON normalization (handles spacing differences)
- Finally tries numeric normalization (handles precision)

**Returns:**
```php
[
    'status' => 'accepted',          // or 'wrong_answer'
    'match' => true,                 // or false
    'message' => 'Output matches (JSON formatting normalized)',
    'diff' => [...] // Only present if match is false
]
```

#### 3. Type-Specific Normalization

**Boolean Normalization:**
```php
OutputNormalizer::normalizeBoolean($output)
```
Converts: `true`/`True`/`1`/`yes` → `true`

**JSON Normalization:**
```php
OutputNormalizer::normalizeJson($output)
```
Handles: `[1, 2, 3]` vs `[1,2,3]`

**Numeric Normalization:**
```php
OutputNormalizer::normalizeNumeric($output, $precision = 6)
```
Handles: `3.14159265359` → `3.141593`

#### 4. Debugging Utilities

**Make Whitespace Visible:**
```php
$visible = OutputNormalizer::makeWhitespaceVisible($output);
```

Converts invisible characters to visible symbols:
- Trailing spaces → `␣`
- Newlines → `↵`

**Example:**
```php
Input:  "Hello World  \n"
Output: "Hello World␣␣↵\n"
```

## Integration

The OutputNormalizer has been integrated into two key controllers:

### 1. CodeExecutionController
- **Method**: `runCode()` - Line ~130
- **Method**: `submitCode()` - Line ~243

### 2. CompetencyTestController (Reviewer)
- **Method**: `runCode()` - Line ~520

### Usage Example

**Before (Strict Comparison):**
```php
$passed = ($actualOutput === $expectedOutput);
```

**After (Smart Comparison):**
```php
$comparisonResult = OutputNormalizer::smartCompare($actualOutput, $expectedOutput);
$passed = $comparisonResult['match'];

// Optional: Store comparison details
$testResults[] = [
    'passed' => $passed,
    'comparison_message' => $comparisonResult['message'] ?? null,
    'diff' => !$passed && isset($comparisonResult['diff']) ? $comparisonResult['diff'] : null
];
```

## Benefits

### For Users
- ✅ No more "Wrong Answer" for trailing spaces
- ✅ Works regardless of OS (Windows/Mac/Linux newlines)
- ✅ Handles different boolean/numeric representations
- ✅ Shows clear diff with visible whitespace when output is truly wrong

### For Maintainers
- ✅ Centralized normalization logic
- ✅ Easy to add new normalization rules
- ✅ Backward compatible (doesn't break existing tests)
- ✅ Detailed logging of comparison decisions

## Advanced Usage

### Strict vs Loose Mode

The `compare()` method supports both:

```php
$result = OutputNormalizer::compare($userOutput, $expectedOutput);

if ($result['status'] === 'accepted' && $result['message'] === 'Output matches exactly') {
    // Strict match (no normalization needed)
    echo "Perfect! ✨";
} elseif ($result['status'] === 'accepted') {
    // Loose match (normalization applied)
    echo "Accepted (with formatting differences)";
} else {
    // Wrong answer
    echo "Failed: " . $result['message'];
}
```

### Custom Precision for Floating Point

```php
$normalized = OutputNormalizer::normalizeNumeric($output, 3); // 3 decimal places
```

### Getting Detailed Diff

```php
$result = OutputNormalizer::smartCompare($userOutput, $expectedOutput);

if (!$result['match']) {
    $diff = $result['diff'];
    echo "Expected: " . $diff['expected_visible'] . "\n";
    echo "Actual:   " . $diff['actual_visible'] . "\n";
    echo "Expected length: " . $diff['expected_length'] . "\n";
    echo "Actual length:   " . $diff['actual_length'] . "\n";
}
```

## Testing Examples

### Example 1: Trailing Space
```php
$expected = "Hello World";
$actual = "Hello World ";

$result = OutputNormalizer::smartCompare($actual, $expected);
// Result: ['status' => 'accepted', 'match' => true]
```

### Example 2: Quote Mismatch
```php
$expected = "5";
$actual = "\"5\"";

$result = OutputNormalizer::smartCompare($actual, $expected);
// Result: ['status' => 'accepted', 'match' => true]
```

### Example 3: JSON Array Spacing
```php
$expected = "[1,2,3]";
$actual = "[1, 2, 3]";

$result = OutputNormalizer::smartCompare($actual, $expected);
// Result: ['status' => 'accepted', 'match' => true, 'message' => 'Output matches (JSON formatting normalized)']
```

### Example 4: Boolean Representations
```php
$expected = "true";
$actual = "1";

$result = OutputNormalizer::smartCompare($actual, $expected);
// Result: ['status' => 'accepted', 'match' => true, 'message' => 'Output matches (boolean formatting normalized)']
```

## Future Enhancements

Potential additions:
1. **Floating point tolerance**: Accept outputs within ±0.0001
2. **Array order independence**: For problems where order doesn't matter
3. **Case-insensitive mode**: For string matching problems
4. **Regex pattern matching**: For problems with multiple valid outputs

## Configuration

To adjust behavior, modify the constants in `OutputNormalizer.php`:

```php
// Default numeric precision
const DEFAULT_PRECISION = 6;

// Boolean representations
const TRUE_VALUES = ['true', '1', 'yes'];
const FALSE_VALUES = ['false', '0', 'no'];
```

## Troubleshooting

### Issue: Tests still failing with identical-looking output

**Solution**: Use the `makeWhitespaceVisible()` method to debug:

```php
$visible = OutputNormalizer::makeWhitespaceVisible($output);
echo $visible; // Shows hidden spaces and newlines
```

### Issue: Need stricter comparison

**Solution**: Use `compare()` instead of `smartCompare()` to skip type-specific normalization.

### Issue: Custom output format not recognized

**Solution**: Extend the `OutputNormalizer` class with a custom method:

```php
public static function normalizeCustomFormat($output) {
    // Your custom logic here
    return $normalized;
}
```

---

**Last Updated**: December 23, 2025  
**Author**: CodeXpert Team  
**Version**: 1.0.0
