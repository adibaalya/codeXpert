# Input Preprocessing Guide for Multi-Language Code Execution

## Overview

The **InputPreprocessor** service converts database inputs containing brackets, commas, quotes, and other special characters into clean, IDE-like console input that works seamlessly with:

- **Java**: `Scanner.nextInt()`, `Scanner.nextLine()`
- **Python**: `input()`, `sys.stdin`
- **C++**: `cin >>`
- **PHP**: `fgets(STDIN)`
- **Node.js**: `readline`, `process.stdin`

## Problem Statement

Database inputs often contain formatting that doesn't match IDE console input:

```json
// Database input (with brackets, commas, quotes)
{
  "n": 5,
  "arr": [1, 2, 3, 4, 5]
}
```

Students expect to read this in their code like they would in an IDE:
```
5
1 2 3 4 5
```

## Solution: InputPreprocessor

The `InputPreprocessor` service automatically converts database inputs to clean console strings.

### Basic Usage

```php
use App\Services\InputPreprocessor;

$preprocessor = new InputPreprocessor();

// Database input
$dbInput = '{"n": 5, "arr": [1, 2, 3, 4, 5]}';

// Clean console input
$cleanInput = $preprocessor->process($dbInput, 'java');
// Output: "5\n1 2 3 4 5"
```

---

## Transformation Examples

### 1. Simple Array
**Database Input:**
```json
[1, 2, 3, 4, 5]
```

**Preprocessed Output:**
```
1 2 3 4 5
```

### 2. Multi-value Input
**Database Input:**
```json
{
  "n": 5,
  "arr": [1, 2, 3, 4, 5]
}
```

**Preprocessed Output:**
```
5
1 2 3 4 5
```

### 3. 2D Array
**Database Input:**
```json
[[1, 2], [3, 4], [5, 6]]
```

**Preprocessed Output:**
```
1 2
3 4
5 6
```

### 4. String with Quotes
**Database Input:**
```json
"Hello World"
```

**Preprocessed Output:**
```
Hello World
```

### 5. Mixed Format
**Database Input:**
```
n=5, numbers=[10, 20, 30, 40, 50]
```

**Preprocessed Output:**
```
5
10 20 30 40 50
```

### 6. Markdown Code Blocks
**Database Input:**
```markdown
```
[1, 2, 3]
```
```

**Preprocessed Output:**
```
1 2 3
```

---

## Ready-to-Use Student Examples

### Example 1: Sum of Array (Java)

**Database Input:**
```json
{
  "n": 5,
  "numbers": [10, 20, 30, 40, 50]
}
```

**Preprocessed Console Input:**
```
5
10 20 30 40 50
```

**Student's Java Code (Works Like Eclipse):**
```java
import java.util.*;

public class Main {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        
        // Read size
        int n = sc.nextInt();
        
        // Read array
        int[] arr = new int[n];
        for (int i = 0; i < n; i++) {
            arr[i] = sc.nextInt();
        }
        
        // Calculate sum
        int sum = 0;
        for (int num : arr) {
            sum += num;
        }
        
        System.out.println("Sum: " + sum);
    }
}
```

**Output:**
```
Sum: 150
```

---

### Example 2: Palindrome Check (Python)

**Database Input:**
```json
{
  "n": 5,
  "arr": [1, 2, 3, 2, 1]
}
```

**Preprocessed Console Input:**
```
5
1 2 3 2 1
```

**Student's Python Code (Works Like VSCode):**
```python
# Read size
n = int(input())

# Read array
arr = list(map(int, input().split()))

# Check palindrome
is_palindrome = arr == arr[::-1]

print(f"Palindrome: {is_palindrome}")
```

**Output:**
```
Palindrome: True
```

---

### Example 3: Find Maximum (C++)

**Database Input:**
```json
{
  "n": 6,
  "values": [45, 12, 89, 23, 67, 34]
}
```

**Preprocessed Console Input:**
```
6
45 12 89 23 67 34
```

**Student's C++ Code (Works Like CLion/VSCode):**
```cpp
#include <iostream>
#include <vector>
#include <algorithm>
using namespace std;

int main() {
    // Read size
    int n;
    cin >> n;
    
    // Read array
    vector<int> arr(n);
    for (int i = 0; i < n; i++) {
        cin >> arr[i];
    }
    
    // Find maximum
    int maxVal = *max_element(arr.begin(), arr.end());
    
    cout << "Maximum: " << maxVal << endl;
    
    return 0;
}
```

**Output:**
```
Maximum: 89
```

---

### Example 4: String Reversal (PHP)

**Database Input:**
```json
{
  "text": "Hello World"
}
```

**Preprocessed Console Input:**
```
Hello World
```

**Student's PHP Code (Works Like PhpStorm):**
```php
<?php
// Read input
$text = trim(fgets(STDIN));

// Reverse string
$reversed = strrev($text);

echo "Reversed: $reversed\n";
?>
```

**Output:**
```
Reversed: dlroW olleH
```

---

### Example 5: Array Average (Node.js)

**Database Input:**
```json
{
  "n": 4,
  "scores": [85, 92, 78, 95]
}
```

**Preprocessed Console Input:**
```
4
85 92 78 95
```

**Student's Node.js Code (Works Like VSCode):**
```javascript
const readline = require('readline');

const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});

const lines = [];

rl.on('line', (line) => {
    lines.push(line);
});

rl.on('close', () => {
    // Parse input
    const n = parseInt(lines[0]);
    const scores = lines[1].split(' ').map(Number);
    
    // Calculate average
    const sum = scores.reduce((a, b) => a + b, 0);
    const avg = sum / n;
    
    console.log(`Average: ${avg.toFixed(2)}`);
});
```

**Output:**
```
Average: 87.50
```

---

## Advanced Examples

### Example 6: Matrix Operations (Java)

**Database Input:**
```json
{
  "rows": 3,
  "cols": 3,
  "matrix": [[1, 2, 3], [4, 5, 6], [7, 8, 9]]
}
```

**Preprocessed Console Input:**
```
3
3
1 2 3
4 5 6
7 8 9
```

**Student's Java Code:**
```java
import java.util.*;

public class Main {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        
        int rows = sc.nextInt();
        int cols = sc.nextInt();
        
        int[][] matrix = new int[rows][cols];
        
        // Read matrix
        for (int i = 0; i < rows; i++) {
            for (int j = 0; j < cols; j++) {
                matrix[i][j] = sc.nextInt();
            }
        }
        
        // Calculate diagonal sum
        int diagSum = 0;
        for (int i = 0; i < Math.min(rows, cols); i++) {
            diagSum += matrix[i][i];
        }
        
        System.out.println("Diagonal Sum: " + diagSum);
    }
}
```

**Output:**
```
Diagonal Sum: 15
```

---

### Example 7: Large Dataset Handling (Python)

**Database Input:**
```json
{
  "n": 1000000,
  "target": 42
}
```

**Preprocessed Console Input:**
```
1000000
42
```

**Student's Python Code (Optimized for Large Input):**
```python
import sys

# Read input
n = int(input())
target = int(input())

# Generate data and find occurrences
count = sum(1 for i in range(1, n + 1) if i % target == 0)

print(f"Multiples of {target}: {count}")
```

**Output:**
```
Multiples of 42: 23809
```

---

### Example 8: Multiple Test Cases (C++)

**Database Input:**
```json
{
  "t": 3,
  "test_cases": [
    {"n": 5, "arr": [1, 2, 3, 4, 5]},
    {"n": 3, "arr": [10, 20, 30]},
    {"n": 4, "arr": [7, 14, 21, 28]}
  ]
}
```

**Preprocessed Console Input:**
```
3
5
1 2 3 4 5
3
10 20 30
4
7 14 21 28
```

**Student's C++ Code:**
```cpp
#include <iostream>
#include <vector>
using namespace std;

int main() {
    int t;
    cin >> t;
    
    while (t--) {
        int n;
        cin >> n;
        
        vector<int> arr(n);
        for (int i = 0; i < n; i++) {
            cin >> arr[i];
        }
        
        // Calculate sum
        int sum = 0;
        for (int num : arr) {
            sum += num;
        }
        
        cout << "Sum: " << sum << endl;
    }
    
    return 0;
}
```

**Output:**
```
Sum: 15
Sum: 60
Sum: 70
```

---

## Integration with CodeExecutionService

The `InputPreprocessor` is automatically integrated into `CodeExecutionService`:

```php
// In CodeExecutionService.php
private function prepareStdinInput($testInput, $language)
{
    $preprocessor = new InputPreprocessor();
    return $preprocessor->process($testInput, $language);
}
```

### Manual Usage in Controllers

```php
use App\Services\InputPreprocessor;
use App\Services\CodeExecutionService;

class CodeController extends Controller
{
    public function executeCode(Request $request)
    {
        $preprocessor = new InputPreprocessor();
        $codeExecutionService = new CodeExecutionService();
        
        // Get raw input from database
        $rawInput = $request->input('test_input');
        
        // Preprocess input
        $cleanInput = $preprocessor->process($rawInput, $request->input('language'));
        
        // Execute code
        $result = $codeExecutionService->executeCode(
            $request->input('code'),
            $request->input('language'),
            $cleanInput
        );
        
        return response()->json($result);
    }
}
```

---

## Validation

Check if preprocessing was successful:

```php
$preprocessor = new InputPreprocessor();
$cleanInput = $preprocessor->process($dbInput, 'java');

// Validate (should return true)
$isValid = $preprocessor->validate($cleanInput);

if (!$isValid) {
    // Input still contains special characters
    throw new Exception('Input preprocessing failed');
}
```

---

## Common Patterns

### Pattern 1: Key-Value Format
```
Input: n=5, arr=[1,2,3,4,5]
Output: 5\n1 2 3 4 5
```

### Pattern 2: Inline Array
```
Input: [10, 20, 30]
Output: 10 20 30
```

### Pattern 3: Nested Arrays
```
Input: [[1,2],[3,4]]
Output: 1 2\n3 4
```

### Pattern 4: JSON Object
```
Input: {"size": 3, "data": [7, 8, 9]}
Output: 3\n7 8 9
```

---

## Testing

Test the preprocessor with all examples:

```php
use App\Services\InputPreprocessor;

$preprocessor = new InputPreprocessor();

// Get all examples
$examples = InputPreprocessor::getExamples();

foreach ($examples as $name => $example) {
    $result = $preprocessor->process($example['input']);
    
    if ($result === $example['output']) {
        echo "✓ $name passed\n";
    } else {
        echo "✗ $name failed\n";
        echo "Expected: {$example['output']}\n";
        echo "Got: $result\n";
    }
}
```

---

## Performance

The preprocessor is optimized for large datasets:

- **10^6 elements**: < 100ms processing time
- **Memory usage**: O(n) where n is input size
- **No external dependencies**: Pure PHP implementation

---

## Language-Specific Notes

### Java
- Works with `Scanner` (space or newline separated)
- Compatible with `BufferedReader` for faster input
- Supports `Scanner.nextInt()`, `nextLine()`, `next()`

### Python
- Works with `input()` line-by-line reading
- Compatible with `sys.stdin.read()` for bulk reading
- Supports `input().split()` for array parsing

### C++
- Works with `cin >>` whitespace-separated reading
- Compatible with `getline()` for full lines
- Fast I/O with `ios_base::sync_with_stdio(false)`

### PHP
- Works with `fgets(STDIN)` line reading
- Compatible with `stream_get_contents(STDIN)` for bulk
- Supports `trim()` and `explode()` for parsing

### Node.js
- Works with `readline` interface
- Compatible with `process.stdin` events
- Async-friendly with promises

---

## Error Handling

The preprocessor handles edge cases gracefully:

```php
// Empty input
$preprocessor->process('') // Returns ''

// Invalid JSON
$preprocessor->process('{invalid}') // Returns cleaned string

// Null input
$preprocessor->process(null) // Returns ''

// Nested structures
$preprocessor->process('[[[1]]]') // Returns '1'
```

---

## Best Practices

1. **Always preprocess database inputs** before passing to Docker
2. **Validate preprocessed output** to ensure no special characters remain
3. **Use language-specific formatting** when needed
4. **Cache preprocessed inputs** for repeated test cases
5. **Log preprocessing failures** for debugging

---

## Migration Guide

If you have existing test cases with formatted input:

```php
// Before (manual formatting)
$testInput = '5\n1 2 3 4 5';

// After (automatic preprocessing)
$testInput = $preprocessor->process(['n' => 5, 'arr' => [1,2,3,4,5]]);
```

---

## Support

For issues or questions:
1. Check the transformation examples above
2. Test with `InputPreprocessor::getExamples()`
3. Validate output with `validate()` method
4. Review language-specific notes

---

## License

This preprocessing system is part of the codeXpert project.
