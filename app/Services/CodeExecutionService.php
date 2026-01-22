<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class CodeExecutionService
{
    /**
     * Execute user code with test cases - IDE-like behavior (no driver script)
     * 
     * @param string $userCode The user's submitted code
     * @param string $language The programming language
     * @param mixed $testInput The test input data
     * @param string|null $functionName Optional function name to execute
     * @param array $parameters Optional parameters
     * @param bool $debug If true, return the generated driver script instead of executing
     */
    public function executeCode($userCode, $language, $testInput, $functionName = null, $parameters = [], $debug = false, $returnType = 'int')
    {
        try {
            $useCompleteProgram = $this->shouldUseCompleteProgram($userCode, $language);
            
            if ($useCompleteProgram) {
                $runnableCode = $userCode;
            } else {
                // UPDATED: Pass the return type into the driver script builder
                $runnableCode = $this->buildDriverScript($userCode, $testInput, $language, $functionName, $parameters, $returnType);
            }
            
            if ($debug) {
                return [
                    'success' => true,
                    'output' => $runnableCode,
                    'debug_mode' => true,
                    'use_complete_program' => $useCompleteProgram
                ];
            }
            
            $languageMap = [
                'python' => 'python', 'java' => 'java', 'javascript' => 'javascript',
                'c++' => 'cpp', 'c' => 'c', 'php' => 'php', 'c#' => 'csharp'
            ];

            $mappedLanguage = $languageMap[strtolower($language)] ?? strtolower($language);
            $extensionMap = [
                'cpp' => 'cpp', 'c' => 'c', 'python' => 'py', 'javascript' => 'js',
                'php' => 'php', 'java' => 'java', 'csharp' => 'cs'
            ];
            $ext = $extensionMap[$mappedLanguage] ?? 'txt';

            $stdinInput = $useCompleteProgram ? $this->prepareStdinInput($testInput, $language) : '';

            $process = new \Symfony\Component\Process\Process([
                'docker', 'run', '--rm', '--memory=256m', '--cpus=1.0', '--network=none', '--pids-limit=100', '-i',
                '-e', "FILE_EXTENSION=" . $ext,
                '-e', "USER_CODE=" . $runnableCode,
                '-e', "LANGUAGE=" . $mappedLanguage,
                '-e', "TEST_INPUT=" . $stdinInput,
                'code-sandbox'
            ]);

            $process->setTimeout(15);
            if (!empty($stdinInput)) { $process->setInput($stdinInput); }
            
            $process->run();
            $rawOutput = $process->getOutput();

            if (preg_match('/JSON_START(.*?)JSON_END/s', $rawOutput, $matches)) {
                $output = $matches[1];
            } else {
                $output = $rawOutput;
            }

            if ($process->getExitCode() !== 0 && $process->getErrorOutput()) {
                return ['success' => false, 'output' => "Error:\n" . $process->getErrorOutput()];
            }

            $trimmedOutput = trim($output);
            return [
                'success' => true, 
                'output' => $trimmedOutput !== '' ? $trimmedOutput : "Code executed successfully with no output."
            ];

        } catch (ProcessTimedOutException $e) {
            return ['success' => false, 'output' => 'Error: Execution timed out.'];
        } catch (\Exception $e) {
            return ['success' => false, 'output' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Determine if user code should be executed as-is (complete program)
     * or needs driver script wrapper (function testing)
     */
    private function shouldUseCompleteProgram($userCode, $language)
    {
        $language = strtolower($language);
        
        // If user explicitly defined a Solution class (LeetCode style), force Driver mode
        if (preg_match('/class\s+Solution/i', $userCode)) {
            return false; 
        }
        
        switch ($language) {
            case 'java':
                // Check if user has complete Main class with main method
                return preg_match('/public\s+class\s+\w+\s*\{[\s\S]*public\s+static\s+void\s+main\s*\(/i', $userCode);
            
            case 'python':
                // Check if user has script-style code (not just function definitions)
                return preg_match('/if\s+__name__\s*==\s*["\']__main__["\']/i', $userCode) ||
                       preg_match('/^\s*(print|input)\s*\(/m', $userCode) ||
                       !preg_match('/^\s*def\s+\w+\s*\(/m', $userCode);
            
            case 'c++':
            case 'cpp':
            case 'c':
                // Check if user has main function
                return preg_match('/int\s+main\s*\(/i', $userCode);
            
            case 'javascript':
                // Check if user has console.log or process.stdin
                return preg_match('/console\.log|process\.stdin|readline/i', $userCode) ||
                       !preg_match('/^\s*function\s+\w+\s*\(/m', $userCode);
            
            case 'php':
                // Only use complete program if it's a flat script with echo/print and no class
                return preg_match('/echo|print|fgets|readline/i', $userCode) && 
                       !preg_match('/class\s+Solution/i', $userCode);     
            
            default:
                // Default to complete program
                return true;
        }
    }

    /**
     * Prepare stdin input for programs that read from Scanner/stdin
     */
    private function prepareStdinInput($testInput, $language)
    {
        // Use InputPreprocessor for clean, IDE-like input
        $preprocessor = new InputPreprocessor();
        
        // Parse the test input
        $inputData = is_array($testInput) ? $testInput : json_decode($testInput, true);
        
        // If parsing failed or it's a string, preprocess it
        if (!is_array($inputData)) {
            return $preprocessor->process($testInput, $language);
        }
        
        // Check if there's a 'stdin' or 'input_data' field specifically for stdin
        if (isset($inputData['stdin'])) {
            return $preprocessor->process($inputData['stdin'], $language);
        }
        
        if (isset($inputData['input_data'])) {
            return $preprocessor->process($inputData['input_data'], $language);
        }
        
        // For code-based tests (with 'input' key containing code), return empty
        if (isset($inputData['input']) && is_string($inputData['input'])) {
            $cleaned = trim($inputData['input']);
            // If it looks like code (not data), don't use it as stdin
            if (preg_match('/new\s+\w+|\.enqueue|\.dequeue|\.push|\.pop|class\s+\w+/i', $cleaned)) {
                return '';
            }
            // Otherwise, preprocess it as stdin data
            return $preprocessor->process($cleaned, $language);
        }
        
        // For structured test data, use preprocessor to convert to clean stdin
        return $preprocessor->process($inputData, $language);
    }

    private function buildDriverScript($userCode, $testInput, $language, $functionName = null, $parameters = [], $returnType = 'int')
    {
        $language = strtolower($language);
        $inputData = $this->parseTestInput($testInput);
        
        switch ($language) {
            case 'c':
                return $this->buildCDriver($userCode, $inputData, $functionName, $parameters, $returnType);
            case 'python':
                return $this->buildPythonDriver($userCode, $inputData, $functionName, $parameters);
            case 'javascript':
                return $this->buildJavaScriptDriver($userCode, $inputData, $functionName, $parameters);
            case 'java':
                return $this->buildJavaDriver($userCode, $inputData, $functionName, $parameters);
            case 'php':
                return $this->buildPHPDriver($userCode, $inputData, $functionName, $parameters);
            case 'c++':
            case 'cpp':
                return $this->buildCppDriver($userCode, $inputData, $functionName, $parameters);
            case 'c#':
            case 'csharp':
                return $this->buildCSharpDriver($userCode, $inputData, $functionName, $parameters);
            default:
                return $userCode;
        }
    }

    /**
     * Parse test input from database format
     */
    private function parseTestInput($testInput)
    {
        // If already an array, check if it has an 'input' field to parse
        if (is_array($testInput)) {
            // If it has an 'input' field, parse that field specifically
            if (isset($testInput['input']) && is_string($testInput['input'])) {
                $cleanedInput = $this->cleanInputData($testInput['input']);
                
                // Try to parse multiple variable assignments (e.g., "var1 = [1,2,3], var2 = 5")
                $parsedVars = $this->parseMultipleVariables($cleanedInput);
                if (!empty($parsedVars)) {
                    return $parsedVars;
                }
            }
            
            // Otherwise, clean all fields and return
            return $this->cleanInputData($testInput);
        }
        
        // Try to decode JSON
        $decoded = json_decode($testInput, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $this->parseTestInput($decoded); // Recursively parse
        }
        
        // Clean the string input
        $cleanedInput = $this->cleanInputData($testInput);
        
        // Try to parse multiple variable assignments
        $parsedVars = $this->parseMultipleVariables($cleanedInput);
        if (!empty($parsedVars)) {
            return $parsedVars;
        }
        
        // Return as-is wrapped in input key
        return ['input' => $cleanedInput];
    }

    private function parseMultipleVariables($input)
    {
        $variables = [];
        
        // Check if input contains variable assignments
        if (!preg_match('/\w+\s*=/', $input)) {
            return $variables;
        }
        
        // More sophisticated parsing that handles nested structures
        $position = 0;
        $length = strlen($input);
        
        while ($position < $length) {
            // Skip whitespace
            while ($position < $length && ctype_space($input[$position])) {
                $position++;
            }
            
            if ($position >= $length) break;
            
            // Match variable name
            if (!preg_match('/(\w+)\s*=\s*/A', $input, $matches, 0, $position)) {
                break;
            }
            
            $varName = $matches[1];
            $position += strlen($matches[0]);
            
            // Extract the value (handle nested structures)
            $value = $this->extractValue($input, $position);
            
            if ($value !== null) {
                // Convert Python tuples to JSON arrays for proper parsing
                // e.g., (10, 100.0) becomes [10, 100.0]
                $jsonCompatibleValue = preg_replace('/\(([^)]+)\)/', '[$1]', $value);
                
                // Try to parse as JSON first (handles dicts, lists, tuples as arrays)
                $decoded = json_decode($jsonCompatibleValue, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $variables[$varName] = $decoded;
                } elseif (is_numeric($value)) {
                    // Numeric value
                    $variables[$varName] = strpos($value, '.') !== false ? floatval($value) : intval($value);
                } elseif (in_array(strtolower($value), ['true', 'false'])) {
                    // Boolean value
                    $variables[$varName] = strtolower($value) === 'true';
                } elseif (preg_match('/^["\'](.*)["\']\s*$/s', $value, $strMatch)) {
                    // Quoted string
                    $variables[$varName] = $strMatch[1];
                } else {
                    // Keep as-is
                    $variables[$varName] = $value;
                }
            }
            
            // Skip past comma if present
            while ($position < $length && ($input[$position] === ',' || ctype_space($input[$position]))) {
                $position++;
            }
        }
        
        return $variables;
    }
    
    /**
     * Extract a value from input string starting at position, handling nested structures
     * Returns the extracted value string and updates position
     */
    private function extractValue($input, &$position)
    {
        $length = strlen($input);
        $start = $position;
        
        // Skip leading whitespace
        while ($position < $length && ctype_space($input[$position])) {
            $position++;
            $start++;
        }
        
        if ($position >= $length) return null;
        
        $firstChar = $input[$position];
        
        // Handle different value types
        if ($firstChar === '{') {
            // Dictionary/object - find matching closing brace
            return $this->extractBalanced($input, $position, '{', '}');
        } elseif ($firstChar === '[') {
            // Array/list - find matching closing bracket
            return $this->extractBalanced($input, $position, '[', ']');
        } elseif ($firstChar === '"' || $firstChar === "'") {
            // Quoted string
            return $this->extractQuotedString($input, $position, $quote);
        } else {
            // Simple value (number, boolean, or unquoted string) - read until comma or end
            $end = $position;
            while ($end < $length && $input[$end] !== ',') {
                $end++;
            }
            $value = trim(substr($input, $position, $end - $position));
            $position = $end;
            return $value;
        }
    }
    
    /**
     * Extract a balanced structure (e.g., {...} or [...])
     */
    private function extractBalanced($input, &$position, $open, $close)
    {
        $length = strlen($input);
        $start = $position;
        $depth = 0;
        $inString = false;
        $stringChar = null;
        
        while ($position < $length) {
            $char = $input[$position];
            
            // Handle string literals inside the structure
            if (($char === '"' || $char === "'") && ($position === 0 || $input[$position - 1] !== '\\')) {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                    $stringChar = null;
                }
            }
            
            // Only count brackets/braces outside of strings
            if (!$inString) {
                if ($char === $open) {
                    $depth++;
                } elseif ($char === $close) {
                    $depth--;
                    if ($depth === 0) {
                        $position++;
                        return substr($input, $start, $position - $start);
                    }
                }
            }
            
            $position++;
        }
        
        // If we get here, the structure was unbalanced - return what we have
        return substr($input, $start, $position - $start);
    }
    
    /**
     * Extract a quoted string
     */
    private function extractQuotedString($input, &$position, $quote)
    {
        $length = strlen($input);
        $start = $position;
        $position++; // Skip opening quote
        
        while ($position < $length) {
            $char = $input[$position];
            
            if ($char === $quote && $input[$position - 1] !== '\\') {
                $position++; // Include closing quote
                return substr($input, $start, $position - $start);
            }
            
            $position++;
        }
        
        // Unclosed string - return what we have
        return substr($input, $start, $position - $start);
    }

    /**
     * Clean input data by removing markdown code blocks and fixing formatting
     */
    private function cleanInputData($data)
    {
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleaned[$key] = $this->cleanInputData($value);
            }
            return $cleaned;
        }
        
        if (is_string($data)) {
            // Remove backticks (markdown inline code)
            $data = str_replace('`', '', $data);
            
            // Remove markdown code blocks (```language ... ```)
            $data = preg_replace('/```[\w]*\n?/', '', $data);
            $data = preg_replace('/```/', '', $data);
            
            // Remove comment blocks that are not actual code
            $data = preg_replace('/\/\/\s*After.*?\n/', '', $data);
            $data = preg_replace('/\/\/\s*No specific output.*?\n/', '', $data);
            
            return trim($data);
        }
        
        return $data;
    }
    

    private function buildPythonDriver($userCode, $inputData, $functionName, $parameters)
    {
        $script = "# Driver Script\nimport json\n\n";
        $metadataKeys = ['test_case', 'input', 'output', 'expected'];
        $actualParams = array_filter(array_keys($inputData), function($k) use ($metadataKeys) {
            return !in_array($k, $metadataKeys);
        });
        $isNumericList = true;
        foreach ($actualParams as $key) {
            if (!is_numeric($key)) {
                $isNumericList = false;
                break;
            }
        }

        $paramsList = "";
        if ($isNumericList && !empty($actualParams)) {
            $listValues = array_values(array_intersect_key($inputData, array_flip($actualParams)));
            $script .= "input_arg = " . json_encode($listValues) . "\n";
            $paramsList = "input_arg";
        } else {
            foreach ($actualParams as $key) {
                $script .= $this->formatPythonVariable($key, $inputData[$key]) . "\n";
            }
            $paramsList = implode(', ', $actualParams);
        }
        
        // 3. Fallback for 'input' field if paramsList is empty
        if (empty($paramsList) && isset($inputData['input'])) {
            $decodedInput = json_decode($inputData['input'], true);
            $valueToUse = ($decodedInput !== null) ? $decodedInput : $inputData['input'];
            $script .= "input_arg = " . json_encode($valueToUse) . "\n";
            $paramsList = "input_arg";
        }
        
        $script .= "\n# User Code\n" . $userCode . "\n\n";
        
        // 4. Function detection
        if (!$functionName) {
            if (preg_match('/^\s*def\s+(\w+)\s*\(/m', $userCode, $match)) {
                $functionName = $match[1];
            }
        }

        if ($functionName) {
            $script .= "# Execution Block\n";
            $script .= "try:\n";
            if (preg_match('/^\s*class\s+(\w+)/m', $userCode, $classMatch)) {
                $className = $classMatch[1];
                $script .= "    sol = {$className}()\n";
                $script .= "    result = sol.{$functionName}({$paramsList})\n";
            } else {
                $script .= "    result = {$functionName}({$paramsList})\n";
            }
            
            // 5. HYBRID OUTPUT LOGIC:
            // Check if the expected output in DB is a wrapped object or raw data
            $expected = $inputData['expected'] ?? ($inputData['output'] ?? '');
            $isStructuredJSON = (strpos(trim($expected), '{') === 0 && strpos(trim($expected), 'test_case') !== false);

            if ($isStructuredJSON) {
                // Returns {"test_case": 1, "output": "35"}
                $testCaseId = $inputData['test_case'] ?? 1;
                $script .= "    output_obj = {\"test_case\": {$testCaseId}, \"output\": str(result)}\n";
                $script .= "    print(f'JSON_START{json.dumps(output_obj)}JSON_END')\n";
            } else {
                // Returns raw data like ["T1001", "T1002"]
                $script .= "    print(f'JSON_START{json.dumps(result)}JSON_END')\n";
            }
            
            $script .= "except Exception as e:\n";
            $script .= "    print(f'JSON_START{{\"error\": \"{str(e)}\"}}JSON_END')\n";
        }
        
        return $script;
    }

    /**
     * Format Python variable assignment
     */
    private function formatPythonVariable($name, $value)
    {
        if (is_array($value)) {
            return "{$name} = " . json_encode($value);
        } elseif (is_string($value)) {
            return "{$name} = \"" . addslashes($value) . "\"";
        } elseif (is_bool($value)) {
            return "{$name} = " . ($value ? 'True' : 'False');
        } elseif (is_null($value)) {
            return "{$name} = None";
        } else {
            return "{$name} = {$value}";
        }
    }

    private function buildJavaScriptDriver($userCode, $inputData, $functionName, $parameters)
    {
        if (empty($functionName)) {
            if (preg_match('/(?:const|let|var|function)\s+([a-zA-Z0-9_]+)\s*[:=]\s*\(?|function\s+([a-zA-Z0-9_]+)\s*\(/', $userCode, $matches)) {
                $functionName = !empty($matches[1]) ? $matches[1] : $matches[2];
            } elseif (preg_match('/class\s+Solution\s*\{[^}]*?([a-zA-Z0-9_]+)\s*\(/s', $userCode, $matches)) {
                $functionName = $matches[1];
            } else {
                $functionName = 'solution';
            }
        }

        $script = "// Driver Script\n";
        $actualParams = array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output', 'expected']);
        });

        // --- FIX: Logic to decide whether to spread or pass as single argument ---
        $isNumericList = true;
        foreach ($actualParams as $key) {
            if (!is_numeric($key)) {
                $isNumericList = false;
                break;
            }
        }

        $paramsList = "";
        if ($isNumericList && !empty($actualParams)) {
            $listValues = array_values(array_intersect_key($inputData, array_flip($actualParams)));
            // CHANGE: Remove the '...' spread operator if you want the array passed as a single object
            $script .= "const input_arg = " . json_encode($listValues) . ";\n";
            $paramsList = "input_arg"; 
        } else {
            $cleanNames = [];
            foreach ($actualParams as $key) {
                $validName = is_numeric($key) ? "var_" . $key : $key;
                $cleanNames[] = $validName;
                $script .= "const {$validName} = " . json_encode($inputData[$key]) . ";\n";
            }
            $paramsList = implode(', ', $cleanNames);
        }
        
        $script .= "\n// --- USER CODE ---\n" . $userCode . "\n\n";
        $script .= "// --- EXECUTION BLOCK ---\n";
        $script .= "try {\n";
        
        if (preg_match('/class\s+Solution/', $userCode)) {
            $script .= "    const sol = new Solution();\n";
            $script .= "    const result = sol.{$functionName}({$paramsList});\n";
        } else {
            $script .= "    const result = {$functionName}({$paramsList});\n";
        }
        
        $script .= "    process.stdout.write(JSON.stringify(result === undefined ? null : result));\n";
        $script .= "} catch (e) {\n";
        $script .= "    process.stdout.write('Error: ' + e.message);\n";
        $script .= "}\n";

        return $script;
    }

    /**
     * Format JavaScript variable
     */
    private function formatJavaScriptVariable($name, $value)
    {
        if (is_array($value)) {
            return "const {$name} = " . json_encode($value) . ";";
        } elseif (is_string($value)) {
            return "const {$name} = \"" . addslashes($value) . "\";";
        } elseif (is_bool($value)) {
            return "const {$name} = " . ($value ? 'true' : 'false') . ";";
        } elseif (is_null($value)) {
            return "const {$name} = null;";
        } else {
            return "const {$name} = {$value};";
        }
    }

    /**
     * Build Java driver script - Final Version with Dynamic Type Support (String/Int/2D)
     */
    private function buildJavaDriver($userCode, $inputData, $functionName, $parameters)
    {
        // 1. Handle Raw Input Parsing
        $hasRawInput = isset($inputData['input']) && is_string($inputData['input']);
        if ($hasRawInput) {
            $parsedVars = $this->parseVariableString($inputData['input']);
            if (!empty($parsedVars)) {
                $inputData = array_merge($inputData, $parsedVars);
                unset($inputData['input']);
            }
        }
        
        unset($inputData['test_case']);
        unset($inputData['expected']);

        // 2. Handle Imports
        $imports = [];
        $codeWithoutImports = $userCode;
        if (preg_match_all('/^\s*import\s+[\w.*]+\s*;\s*$/m', $userCode, $matches)) {
            $imports = $matches[0];
            $codeWithoutImports = preg_replace('/^\s*import\s+[\w.*]+\s*;\s*$/m', '', $userCode);
        }
        
        // 3. Start Script Generation
        $script = "import java.util.*;\n";
        $script .= "import java.util.stream.*;\n";
        foreach ($imports as $import) {
            if (stripos($import, 'java.util') === false) {
                $script .= trim($import) . "\n";
            }
        }
        $script .= "\npublic class Main {\n";
        
        // Helper method for printing results
        $script .= "    public static void printResult(Object o) {\n";
        $script .= "        if (o == null) { \n";
        $script .= "            System.out.println(\"null\");\n";
        $script .= "        } else if (o instanceof int[]) { \n";
        $script .= "            System.out.println(Arrays.toString((int[]) o));\n";
        $script .= "        } else if (o instanceof double[]) { \n";
        $script .= "            System.out.println(Arrays.toString((double[]) o));\n";
        $script .= "        } else if (o instanceof boolean[]) { \n";
        $script .= "            System.out.println(Arrays.toString((boolean[]) o));\n";
        $script .= "        } else if (o instanceof Object[]) { \n";
        $script .= "            System.out.println(Arrays.deepToString((Object[]) o));\n";
        $script .= "        } else { \n";
        $script .= "            System.out.println(o);\n";
        $script .= "        }\n";
        $script .= "    }\n\n";

        $script .= "    public static void main(String[] args) {\n";
        $script .= "        try {\n";
        
        $actualParams = array_keys($inputData);
        $paramString = "";

        // 4. Logic: Check for Numeric Sequence (Raw List)
        $isNumericList = true;
        foreach ($actualParams as $key) {
            if (!is_numeric($key)) {
                $isNumericList = false;
                break;
            }
        }

        if ($isNumericList && !empty($actualParams)) {
            $firstValue = reset($inputData);
            
            // --- DYNAMIC TYPE INFERENCE ---
            if (is_array($firstValue)) {
                $javaType = "int[][]";
            } elseif (is_string($firstValue)) {
                $javaType = "String[]";
            } else {
                $javaType = "int[]";
            }

            $formattedValues = array_map(function($v) {
                if (is_array($v)) return "new int[] {" . implode(', ', $v) . "}";
                if (is_bool($v)) return $v ? "true" : "false";
                return is_string($v) ? '"' . addslashes($v) . '"' : $v;
            }, array_values($inputData));

            $values = implode(', ', $formattedValues);
            $script .= "            {$javaType} input_array = {{$values}};\n";
            $paramString = "input_array";
        } else {
            $cleanParamNames = [];
            foreach ($actualParams as $key) {
                $validName = is_numeric($key) ? "input_" . $key : $key;
                $cleanParamNames[] = $validName;
                $value = $inputData[$key];
                $script .= "            " . $this->formatJavaVariableAdvanced($validName, $value) . "\n";
            }
            $paramString = implode(', ', $cleanParamNames);
        }

        $script .= "\n";

        // 5. Detect Class & Method Name
        $className = "Solution";
        if (preg_match('/\bclass\s+(\w+)/', $codeWithoutImports, $matches)) {
            $className = $matches[1];
        }
        
        $methodName = $functionName;
        if (!$methodName) {
            if (preg_match('/public\s+[\w<>\[\]]+\s+(\w+)\s*\(/', $codeWithoutImports, $matches)) {
                $methodName = $matches[1];
            } else {
                $methodName = "solve";
            }
        }

        // 6. Instantiate and Call
        $script .= "            {$className} sol = new {$className}();\n";
        $script .= "            Object result = sol.{$methodName}({$paramString});\n";
        $script .= "            printResult(result);\n";

        $script .= "        } catch (Exception e) {\n";
        $script .= "            e.printStackTrace();\n";
        $script .= "        }\n";
        $script .= "    }\n";
        $script .= "}\n\n";

        $userCodeCleaned = preg_replace('/\bpublic\s+class\s+/', 'class ', $codeWithoutImports);
        $script .= $userCodeCleaned;

        return $script;
    }

    
    /**
     * Format Java variable with support for Maps, Lists, and Nested types
     */
    private function formatJavaVariableAdvanced($name, $value)
    {
        // 1. Handle Lists/Arrays
        if (is_array($value)) {
            if (empty($value)) {
                // Default to int[] for empty arrays as it's the safest fallback in algorithms
                return "int[] {$name} = {};";
            }

            $firstKey = array_key_first($value);
            $firstValue = $value[$firstKey];
            
            // ==========================================
            // CASE A: MAP (Associative Array)
            // ==========================================
            if (!is_numeric($firstKey)) {
                $keyType = is_string($firstKey) ? 'String' : 'Object';
                
                // Check if values are nested Lists
                $isNestedList = is_array($firstValue);
                
                if ($isNestedList) {
                    $innerFirst = !empty($firstValue) ? reset($firstValue) : null;
                    $innerType = is_int($innerFirst) ? "Integer" : "String";
                    $valueType = "List<{$innerType}>";
                } else {
                    $valueType = is_int($firstValue) ? "Integer" : (is_float($firstValue) ? "Double" : "String");
                }
                
                $code = "Map<{$keyType}, {$valueType}> {$name} = new HashMap<>();\n";
                foreach ($value as $k => $v) {
                    $formattedKey = is_string($k) ? "\"{$k}\"" : $k;
                    
                    if ($isNestedList) {
                        $processedItems = array_map(function($item) {
                            return is_string($item) ? "\"{$item}\"" : $item;
                        }, $v);
                        $listContent = implode(', ', $processedItems);
                        $formattedValue = "Arrays.asList({$listContent})";
                    } else {
                        $formattedValue = is_string($v) ? "\"{$v}\"" : (is_bool($v) ? ($v ? 'true' : 'false') : $v);
                    }
                    $code .= "            {$name}.put({$formattedKey}, {$formattedValue});\n";
                }
                return rtrim($code);
            }
            
            // ==========================================
            // CASE B: STANDARD LIST/ARRAY
            // ==========================================
            else {
                if (is_int($firstValue)) {
                    // *** CRITICAL FIX: Use primitive int[] for integers ***
                    $args = implode(', ', $value);
                    return "int[] {$name} = {{$args}};";
                } 
                elseif (is_float($firstValue)) {
                    // *** CRITICAL FIX: Use primitive double[] for decimals ***
                    $args = implode(', ', $value);
                    return "double[] {$name} = {{$args}};";
                }
                elseif (is_string($firstValue)) {
                    // Strings are usually handled as Lists in Java Collections
                    $args = implode(', ', array_map(function($v) { return "\"{$v}\""; }, $value));
                    return "List<String> {$name} = new ArrayList<>(Arrays.asList({$args}));";
                } 
                elseif (is_array($firstValue)) {
                    // 2D Arrays - Assume 2D primitive int[][] if contents are ints
                    $innerFirst = reset($firstValue);
                    if (is_int(reset($innerFirst))) {
                        $rows = [];
                        foreach ($value as $row) {
                            $rows[] = "{" . implode(', ', $row) . "}";
                        }
                        $args = implode(', ', $rows);
                        return "int[][] {$name} = {{$args}};";
                    }
                    // Fallback for other 2D types could be added here
                    return "Object[][] {$name} = ..."; // Simplified fallback
                }
                else {
                    $args = implode(', ', $value);
                    return "Object[] {$name} = {{$args}};";
                }
            }
        } 
        
        // 2. Handle Primitives (String, boolean, int, double)
        elseif (is_string($value)) {
            return "String {$name} = \"" . addslashes($value) . "\";";
        } elseif (is_bool($value)) {
            return "boolean {$name} = " . ($value ? 'true' : 'false') . ";";
        } elseif (is_int($value)) {
            return "int {$name} = {$value};";
        } elseif (is_float($value)) {
            return "double {$name} = {$value};";
        } else {
            return "Object {$name} = null;";
        }
    }
    
    /**
     * Detect class name from variable name
     * e.g., "discountTiers" -> "DiscountTier"
     *       "orderValues" -> "OrderValue" (but this won't be used for primitives)
     */
    private function detectClassNameFromVariable($varName)
    {
        // Remove common plural suffixes
        $singular = preg_replace('/(s|es|ies)$/i', $varName);
        
        // Handle special cases
        if (preg_match('/Tiers?$/i', $varName)) {
            $singular = preg_replace('/Tiers?$/i', 'Tier', $varName);
        } elseif (preg_match('/ies$/i', $varName)) {
            $singular = preg_replace('/ies$/i', 'y', $varName);
        } elseif (preg_match('/ses$/i', $varName)) {
            $singular = preg_replace('/ses$/i', 's', $varName);
        } elseif (preg_match('/s$/i', $varName)) {
            $singular = preg_replace('/s$/i', '', $varName);
        }
        
        // Convert to PascalCase (capitalize first letter of each word)
        $singular = str_replace('_', ' ', $singular);
        $singular = ucwords($singular);
        $singular = str_replace(' ', '', $singular);
        
        return $singular;
    }

    /**
     * Format Java variable with support for Maps, Lists, and Nested types
     */
    private function formatJavaVariable($name, $value)
    {
        // 1. Handle Lists/Arrays
        if (is_array($value)) {
            if (empty($value)) {
                // Default to simple int array if empty, as it's most common
                return "int[] {$name} = {};";
            }

            $firstKey = array_key_first($value);
            $firstValue = $value[$firstKey];
            
            // ==========================================
            // CASE A: MAP (Associative Array)
            // ==========================================
            if (!is_numeric($firstKey)) {
                $keyType = is_string($firstKey) ? 'String' : 'Object';
                
                // Check if values are nested Lists (Map<String, List<...>>)
                $isNestedList = is_array($firstValue);
                
                if ($isNestedList) {
                    $innerFirst = !empty($firstValue) ? reset($firstValue) : null;
                    $innerType = is_int($innerFirst) ? "Integer" : "String";
                    $valueType = "List<{$innerType}>";
                } else {
                    $valueType = is_int($firstValue) ? "Integer" : (is_float($firstValue) ? "Double" : "String");
                }
                
                $code = "Map<{$keyType}, {$valueType}> {$name} = new HashMap<>();\n";
                foreach ($value as $k => $v) {
                    $formattedKey = is_string($k) ? "\"{$k}\"" : $k;
                    
                    if ($isNestedList) {
                        $processedItems = array_map(function($item) {
                            return is_string($item) ? "\"{$item}\"" : $item;
                        }, $v);
                        
                        $listContent = implode(', ', $processedItems);
                        $formattedValue = "Arrays.asList({$listContent})";
                    } else {
                        $formattedValue = is_string($v) ? "\"{$v}\"" : (is_bool($v) ? ($v ? 'true' : 'false') : $v);
                    }
                    
                    $code .= "            {$name}.put({$formattedKey}, {$formattedValue});\n";
                }
                return rtrim($code);
            }
            
            // ==========================================
            // CASE B: STANDARD LIST/ARRAY
            // ==========================================
            else {
                if (is_string($firstValue)) {
                    // String list -> List<String> (Strings are rarely String[])
                    $args = implode(', ', array_map(function($v) { return "\"{$v}\""; }, $value));
                    return "List<String> {$name} = new ArrayList<>(Arrays.asList({$args}));";
                } 
                elseif (is_int($firstValue)) {
                    // *** FIX: FORCE PRIMITIVE int[] for integers ***
                    $args = implode(', ', $value);
                    return "int[] {$name} = {{$args}};";
                } 
                elseif (is_float($firstValue)) {
                    // *** FIX: FORCE PRIMITIVE double[] for decimals ***
                    $args = implode(', ', $value);
                    return "double[] {$name} = {{$args}};";
                }
                else {
                    // Fallback for mixed/objects
                    $args = implode(', ', $value);
                    return "Object[] {$name} = {{$args}};";
                }
            }
        } 
        
        // 2. Handle Primitives (String, boolean, int, double)
        elseif (is_string($value)) {
            return "String {$name} = \"" . addslashes($value) . "\";";
        } elseif (is_bool($value)) {
            return "boolean {$name} = " . ($value ? 'true' : 'false') . ";";
        } elseif (is_int($value)) {
            return "int {$name} = {$value};";
        } elseif (is_float($value)) {
            return "double {$name} = {$value};";
        } else {
            return "Object {$name} = null;";
        }
    }

    private function buildPHPDriver($userCode, $inputData, $functionName, $parameters)
    {
        // Remove existing PHP tags to avoid syntax errors when nesting
        $cleanCode = trim(preg_replace('/^<\?php\s*/i', '', trim($userCode)));
        $cleanCode = preg_replace('/\?>\s*$/', '', $cleanCode);

        $script = "<?php\n";
        $script .= "ini_set('display_errors', 0);\n";
        $script .= "error_reporting(0);\n\n";

        // Filter out metadata
        $actualParams = array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output', 'expected']);
        });

        // Determine if the input is a single array (like [[1,3],[2,6]]) 
        // or multiple named variables.
        $isNumericList = true;
        foreach ($actualParams as $key) {
            if (!is_numeric($key)) {
                $isNumericList = false;
                break;
            }
        }

        $paramsList = "";
        if ($isNumericList && !empty($actualParams)) {
            // Pass the entire numeric-keyed array as the first argument (e.g., $events)
            $val = array_values(array_intersect_key($inputData, array_flip($actualParams)));
            $script .= "\$input_data = " . var_export($val, true) . ";\n";
            $paramsList = "\$input_data";
        } else {
            $cleanNames = [];
            foreach ($actualParams as $key) {
                $varName = is_numeric($key) ? "arg_" . $key : $key;
                $cleanNames[] = "\${$varName}";
                $script .= "\${$varName} = " . var_export($inputData[$key], true) . ";\n";
            }
            $paramsList = implode(', ', $cleanNames);
        }

        $script .= "\n// --- USER CODE ---\n" . $cleanCode . "\n\n";

        // Auto-detect the function name if it wasn't provided
        if (!$functionName) {
            if (preg_match('/function\s+([a-zA-Z0-9_]+)\s*\(/', $cleanCode, $matches)) {
                $functionName = $matches[1];
            } else {
                $functionName = 'maxConcurrentEvents'; 
            }
        }

        $script .= "// --- EXECUTION ---\n";
        $script .= "try {\n";
        $script .= "    \$sol = new Solution();\n";
        $script .= "    \$result = \$sol->{$functionName}({$paramsList});\n";
        // Output result between delimiters for the CodeExecutionService to grab
        $script .= "    echo 'JSON_START' . json_encode(\$result) . 'JSON_END';\n";
        $script .= "} catch (Throwable \$e) {\n";
        $script .= "    echo 'JSON_START' . json_encode(['error' => \$e->getMessage()]) . 'JSON_END';\n";
        $script .= "}\n";
        
        return $script;
    }

    /**
     * Format PHP variable
     */
    private function formatPHPVariable($name, $value)
    {
        $phpValue = var_export($value, true);
        return "\${$name} = {$phpValue};";
    }

    private function buildCppDriver($userCode, $inputData, $functionName, $parameters, $returnType = 'int')
    {
        // 1. Headers and setup
        $script = "#include <iostream>\n#include <vector>\n#include <string>\n#include <algorithm>\n";
        $script .= "#include <map>\n#include <unordered_map>\n#include <set>\n#include <stack>\n";
        $script .= "#include <queue>\n#include <sstream>\n#include <cmath>\n#include <iomanip>\n";
        $script .= "using namespace std;\n\n";

        // 2. Print Helpers (Handles vectors, strings, bools, and doubles)
        $script .= <<<'EOT'
        template<typename T> void printResult(const vector<T>& v);
        void printResult(const string& s) { cout << "\"" << s << "\""; }
        void printResult(bool x) { cout << (x ? "true" : "false"); }
        void printResult(double x) { cout << fixed << setprecision(6) << x; } // Fix for decimal precision
        template<typename T> void printResult(const T& x) { cout << x; }
        
        template<typename T>
        void printResult(const vector<T>& v) {
            cout << "[";
            for(size_t i=0; i<v.size(); ++i) {
                cout << (i==0?"":",");
                printResult(v[i]);
            }
            cout << "]";
        }
        EOT;
        $script .= "\n\n";

        // 3. Inject User Code
        $script .= "// --- USER CODE START ---\n" . $userCode . "\n// --- USER CODE END ---\n\n";

        $script .= "int main() {\n";
        
        // 4. Identify Parameters (Excluding metadata)
        $metadataKeys = ['test_case', 'input', 'output', 'expected'];
        $actualParams = array_filter(array_keys($inputData), function($k) use ($metadataKeys) {
            return !in_array($k, $metadataKeys);
        });

        $cleanParamNames = [];
        if (empty($actualParams) && isset($inputData['input'])) {
            // Fallback if the input is a single string/JSON in the 'input' field
            $script .= "    " . $this->formatCppVariableAdvanced("arg0", $inputData['input']) . "\n";
            $cleanParamNames[] = "arg0";
        } else {
            foreach ($actualParams as $key) {
                $varName = is_numeric($key) ? "var_" . $key : $key;
                $cleanParamNames[] = $varName;
                $script .= "    " . $this->formatCppVariableAdvanced($varName, $inputData[$key]) . "\n";
            }
        }

        // 5. Detect Class and Method
        $hasClass = preg_match('/class\s+Solution/', $userCode);
        if (empty($functionName)) {
            if (preg_match('/public:[\s\S]*?(\w+)\s*\(/', $userCode, $methodMatch)) {
                $functionName = $methodMatch[1];
            } else {
                $functionName = "solve"; 
            }
        }

        // 6. Execution and Output with Delimiters
        $paramsList = implode(', ', $cleanParamNames);
        $script .= "\n    try {\n";
        if ($hasClass) {
            $script .= "        Solution sol;\n";
            $script .= "        auto result = sol.{$functionName}({$paramsList});\n";
        } else {
            $script .= "        auto result = {$functionName}({$paramsList});\n";
        }

        // Check if expected output is structured JSON or raw value
        $expected = $inputData['expected'] ?? ($inputData['output'] ?? '');
        $isStructuredJSON = (strpos(trim($expected), '{') === 0 && strpos(trim($expected), 'test_case') !== false);

        if ($isStructuredJSON) {
            $testCaseId = $inputData['test_case'] ?? 1;
            $script .= "        cout << \"JSON_START{\\\"test_case\\\": {$testCaseId}, \\\"output\\\": \\\"\";\n";
            $script .= "        printResult(result);\n";
            $script .= "        cout << \"\\\"}JSON_END\" << endl;\n";
        } else {
            $script .= "        cout << \"JSON_START\";\n";
            $script .= "        printResult(result);\n";
            $script .= "        cout << \"JSON_END\" << endl;\n";
        }

        $script .= "    } catch (...) {\n";
        $script .= "        return 1;\n";
        $script .= "    }\n";
        $script .= "    return 0;\n}\n";

        return $script;
    }
    
    /**
     * Format C++ variable with advanced type detection
     */
    private function formatCppVariableAdvanced($name, $value)
    {
        if (is_array($value)) {
            if (empty($value)) {
                return "vector<int> {$name};";
            }
            
            $firstValue = reset($value);
            
            // Check if it's a 2D array
            if (is_array($firstValue)) {
                $innerFirst = !empty($firstValue) ? reset($firstValue) : null;
                $innerType = $this->inferCppType($innerFirst);
                $formatted2D = [];
                foreach ($value as $row) {
                    $formattedRow = array_map(function($v) {
                        return is_string($v) ? "\"{$v}\"" : $v;
                    }, $row);
                    $formatted2D[] = '{' . implode(', ', $formattedRow) . '}';
                }
                return "vector<vector<{$innerType}>> {$name} = {" . implode(', ', $formatted2D) . "};";
            }
            
            // 1D array/vector
            $type = $this->inferCppType($firstValue);
            $values = implode(', ', array_map(function($v) {
                return is_string($v) ? "\"{$v}\"" : $v;
            }, $value));
            return "vector<{$type}> {$name} = {{$values}};";
        } elseif (is_string($value)) {
            return "string {$name} = \"" . addslashes($value) . "\";";
        } elseif (is_bool($value)) {
            return "bool {$name} = " . ($value ? 'true' : 'false') . ";";
        } elseif (is_int($value)) {
            return "int {$name} = {$value};";
        } elseif (is_float($value)) {
            return "double {$name} = {$value};";
        }
        
        return "auto {$name};";
    }
    
    /**
     * Infer C++ type from PHP value
     */
    private function inferCppType($value)
    {
        if (is_string($value)) return 'string';
        if (is_int($value)) return 'int';
        if (is_float($value)) return 'double';
        if (is_bool($value)) return 'bool';
        return 'int'; // Default
    }

    private function buildCDriver($userCode, $inputData, $functionName, $parameters, $returnType = 'int')
    {
        $script = "#include <stdio.h>\n#include <stdlib.h>\n#include <string.h>\n#include <stdbool.h>\n#include <math.h>\n\n";
        $script .= "// --- USER CODE START ---\n" . $userCode . "\n// --- USER CODE END ---\n\n";

        // Handle return types and printf specifiers
        $cReturnType = 'int';
        $printfFormat = '%d';
        $lowerReturn = strtolower($returnType);
        
        if ($lowerReturn === 'double' || $lowerReturn === 'float') {
            $cReturnType = 'double';
            $printfFormat = '%f'; 
        }

        if (!$functionName) {
            if (preg_match('/(?:\w+\s+){0,2}\w+(?:\s*\*+)?\s+([a-zA-Z_]\w*)\s*\(/', $userCode, $matches)) {
                $functionName = $matches[1];
            }
        }

        $script .= "int main() {\n";
        
        $actualParams = array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output', 'expected']);
        });

        $callArgs = [];
        foreach ($actualParams as $key) {
            $varName = is_numeric($key) ? "arg_" . $key : $key;
            $value = $inputData[$key];

            // 1. Declare the primary variable
            $script .= "    " . $this->formatCVariable($varName, $value) . "\n";
            $callArgs[] = $varName;

            // 2. Only inject Size if the function expects it
            // This must match the logic in CodeTemplateService::formatCParameters
            if (is_array($value)) {
                $sizeVarName = $varName . "Size";
                $script .= "    int {$sizeVarName} = " . count($value) . ";\n";
                $callArgs[] = $sizeVarName;
            }
        }
        
        if ($functionName) {
            $paramsList = implode(', ', $callArgs);
            $script .= "    printf(\"JSON_START\");\n";
            $script .= "    {$cReturnType} result = {$functionName}({$paramsList});\n";
            $script .= "    printf(\"{$printfFormat}\", result);\n";
            $script .= "    printf(\"JSON_END\");\n";
        }
        
        $script .= "\n    return 0;\n}\n";
        return $script;
    }

    /**
     * Format C variable assignment
     */
    private function formatCVariable($name, $value)
    {
        if (is_int($value)) {
            return "int {$name} = {$value};";
        } elseif (is_float($value)) {
            return "double {$name} = {$value};";
        } elseif (is_bool($value)) {
            return "bool {$name} = " . ($value ? 'true' : 'false') . ";";
        } elseif (is_string($value)) {
            return "char {$name}[] = \"" . addslashes($value) . "\";";
        } elseif (is_array($value)) {
            // Handle Arrays
            if (empty($value)) {
                // --- FIX START ---
                // Original: return "int {$name}[] = {};";
                // New: Initialize with a dummy 0 so the array has memory (4 bytes)
                return "int {$name}[] = {0};"; 
                // --- FIX END ---
            }
            
            $first = reset($value);
            if (is_int($first)) {
                $vals = implode(', ', $value);
                return "int {$name}[] = {{$vals}};";
            } elseif (is_string($first)) {
                // String array
                $vals = implode(', ', array_map(function($v) { return "\"$v\""; }, $value));
                return "char* {$name}[] = {{$vals}};";
            } elseif (is_float($first)) {
                 $vals = implode(', ', $value);
                 return "double {$name}[] = {{$vals}};";
            }
        }
        
        // Fallback safety
        return "int {$name} = 0; // Unknown type";
    }

    /**
     * Helper: Manually parse string inputs like "x = [1,2]\ny = 5"
     */
    private function parseVariableString($inputString) {
        $variables = [];
        
        // Split by newlines (handling \r\n or \n)
        $lines = preg_split('/\r\n|\r|\n/', $inputString);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Regex to find "varName = value"
            // Matches "cart" = "[...]"
            if (preg_match('/^(\w+)\s*=\s*(.*)$/', $line, $matches)) {
                $varName = $matches[1];
                $jsonValue = $matches[2];
                
                // Try to decode the value part as JSON
                // Note: Your input uses python syntax (None, True) vs JSON (null, true)
                // simple fixes for common python-isms:
                $jsonValue = str_replace(['None', 'True', 'False', "'"], ['null', 'true', 'false', '"'], $jsonValue);
                
                $decoded = json_decode($jsonValue, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    $variables[$varName] = $decoded;
                } else {
                    // Fallback: keep as string if not valid JSON
                    $variables[$varName] = $jsonValue;
                }
            }
        }
        return $variables;
    }

    /**
     * Build C# driver script with LeetCode-style support
     */
    private function buildCSharpDriver($userCode, $inputData, $functionName, $parameters)
    {
        $script = "using System;\n";
        $script .= "using System.Collections.Generic;\n";
        $script .= "using System.Linq;\n";
        $script .= "using System.Text;\n\n";
        
        $script .= "// --- USER CODE START ---\n";
        $script .= $userCode . "\n";
        $script .= "// --- USER CODE END ---\n\n";
        
        $script .= "public class Program\n";
        $script .= "{\n";
        $script .= "    public static void Main(string[] args)\n";
        $script .= "    {\n";
        $script .= "        try\n";
        $script .= "        {\n";
        
        // Filter out metadata keys
        $actualParams = array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output']);
        });
        
        // Inject variables
        foreach ($actualParams as $key) {
            $value = $inputData[$key];
            $script .= "            " . $this->formatCSharpVariable($key, $value) . "\n";
        }
        
        $script .= "\n";
        
        // Detect class and method
        $hasClass = preg_match('/class\s+(\w+)/i', $userCode, $classMatch);
        $className = $hasClass ? $classMatch[1] : 'Solution';
        
        // Auto-detect function name if not provided
        if (!$functionName) {
            if (preg_match('/public\s+\w+\s+(\w+)\s*\(/m', $userCode, $methodMatch)) {
                $functionName = $methodMatch[1];
            } else {
                $functionName = 'Solve';
            }
        }
        
        // Call the function
        if ($hasClass) {
            $script .= "            {$className} solution = new {$className}();\n";
            $paramsList = implode(', ', $actualParams);
            $script .= "            var result = solution.{$functionName}({$paramsList});\n";
        } else {
            $paramsList = implode(', ', $actualParams);
            $script .= "            var result = {$className}.{$functionName}({$paramsList});\n";
        }
        
        // Print result with proper formatting
        $script .= "\n";
        $script .= "            // Print result\n";
        $script .= "            if (result is System.Collections.IEnumerable && !(result is string))\n";
        $script .= "            {\n";
        $script .= "                Console.WriteLine(\"[\" + string.Join(\", \", ((System.Collections.IEnumerable)result).Cast<object>()) + \"]\");\n";
        $script .= "            }\n";
        $script .= "            else\n";
        $script .= "            {\n";
        $script .= "                Console.WriteLine(result);\n";
        $script .= "            }\n";
        
        $script .= "        }\n";
        $script .= "        catch (Exception ex)\n";
        $script .= "        {\n";
        $script .= "            Console.WriteLine(\"Error: \" + ex.Message);\n";
        $script .= "        }\n";
        $script .= "    }\n";
        $script .= "}\n";
        
        return $script;
    }
    
    /**
     * Format C# variable with proper type inference
     */
    private function formatCSharpVariable($name, $value)
    {
        if (is_array($value)) {
            if (empty($value)) {
                return "List<object> {$name} = new List<object>();";
            }
            
            $firstValue = reset($value);
            
            // Check if it's a 2D array
            if (is_array($firstValue)) {
                $innerFirst = !empty($firstValue) ? reset($firstValue) : null;
                $innerType = $this->inferCSharpType($innerFirst);
                
                $formatted2D = [];
                foreach ($value as $row) {
                    $formattedRow = array_map(function($v) {
                        if (is_string($v)) return "\"{$v}\"";
                        if (is_bool($v)) return $v ? 'true' : 'false';
                        return $v;
                    }, $row);
                    $formatted2D[] = 'new List<' . $innerType . '> { ' . implode(', ', $formattedRow) . ' }';
                }
                
                return "List<List<{$innerType}>> {$name} = new List<List<{$innerType}>> { " . implode(', ', $formatted2D) . " };";
            }
            
            // Check if it's an associative array (Dictionary)
            $keys = array_keys($value);
            if (!is_numeric($keys[0])) {
                $keyType = is_string($keys[0]) ? 'string' : 'object';
                $valueType = $this->inferCSharpType($firstValue);
                
                $code = "Dictionary<{$keyType}, {$valueType}> {$name} = new Dictionary<{$keyType}, {$valueType}>()\n";
                $code .= "            {\n";
                
                foreach ($value as $k => $v) {
                    $formattedKey = is_string($k) ? "\"{$k}\"" : $k;
                    $formattedValue = is_string($v) ? "\"{$v}\"" : (is_bool($v) ? ($v ? 'true' : 'false') : $v);
                    $code .= "                { {$formattedKey}, {$formattedValue} },\n";
                }
                
                $code .= "            };";
                return rtrim($code);
            }
            
            // 1D array/list
            $type = $this->inferCSharpType($firstValue);
            $values = implode(', ', array_map(function($v) {
                if (is_string($v)) return "\"{$v}\"";
                if (is_bool($v)) return $v ? 'true' : 'false';
                return $v;
            }, $value));
            
            return "List<{$type}> {$name} = new List<{$type}> { {$values} };";
            
        } elseif (is_string($value)) {
            return "string {$name} = \"" . addslashes($value) . "\";";
        } elseif (is_bool($value)) {
            return "bool {$name} = " . ($value ? 'true' : 'false') . ";";
        } elseif (is_int($value)) {
            return "int {$name} = {$value};";
        } elseif (is_float($value)) {
            return "double {$name} = {$value};";
        }
        
        return "var {$name} = null;";
    }
    
    /**
     * Infer C# type from PHP value
     */
    private function inferCSharpType($value)
    {
        if (is_string($value)) return 'string';
        if (is_int($value)) return 'int';
        if (is_float($value)) return 'double';
        if (is_bool($value)) return 'bool';
        return 'object'; // Default
    }
    
    /**
     * Improved PHP driver with LeetCode-style support
     */
    private function buildPHPDriverImproved($userCode, $inputData, $functionName, $parameters)
    {
        $script = "<?php\n";
        $script .= "// Driver Script - LeetCode Style\n\n";
        
        // Filter out metadata keys
        $actualParams = array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output']);
        });
        
        // Inject variables
        foreach ($actualParams as $key) {
            $value = $inputData[$key];
            $script .= $this->formatPHPVariable($key, $value) . "\n";
        }
        
        $script .= "\n// --- USER CODE START ---\n";
        
        // Strip <?php tag if user included it
        $cleanedCode = preg_replace('/^<\?php\s*/i', '', $userCode);
        $cleanedCode = preg_replace('/\?>\s*$/i', '', $cleanedCode);
        
        $script .= $cleanedCode . "\n";
        $script .= "// --- USER CODE END ---\n\n";
        
        // Detect class
        $hasClass = preg_match('/class\s+(\w+)/i', $cleanedCode, $classMatch);
        $className = $hasClass ? $classMatch[1] : null;
        
        // Auto-detect function name
        if (!$functionName) {
            if ($hasClass && preg_match('/(?:public|private|protected)?\s*function\s+(\w+)\s*\(/m', $cleanedCode, $methodMatch)) {
                $functionName = $methodMatch[1];
                // Skip __construct and other magic methods
                if (strpos($functionName, '__') === 0) {
                    if (preg_match_all('/(?:public|private|protected)?\s*function\s+(\w+)\s*\(/m', $cleanedCode, $allMatches)) {
                        foreach ($allMatches[1] as $method) {
                            if (strpos($method, '__') !== 0) {
                                $functionName = $method;
                                break;
                            }
                        }
                    }
                }
            } elseif (preg_match('/function\s+(\w+)\s*\(/m', $cleanedCode, $funcMatch)) {
                $functionName = $funcMatch[1];
            } else {
                $functionName = 'solve';
            }
        }
        
        // Execute and print
        $script .= "// Execute function\n";
        
        if ($className) {
            $script .= "\$solution = new {$className}();\n";
            if (!empty($actualParams)) {
                $paramsList = '$' . implode(', $', $actualParams);
                $script .= "\$result = \$solution->{$functionName}({$paramsList});\n";
            } else {
                $script .= "\$result = \$solution->{$functionName}();\n";
            }
        } else {
            if (!empty($actualParams)) {
                $paramsList = '$' . implode(', $', $actualParams);
                $script .= "\$result = {$functionName}({$paramsList});\n";
            } else {
                $script .= "\$result = {$functionName}();\n";
            }
        }
        
        // Print result with JSON encoding for arrays
        $script .= "\n// Print result\n";
        $script .= "if (is_array(\$result)) {\n";
        $script .= "    echo json_encode(\$result);\n";
        $script .= "} elseif (is_bool(\$result)) {\n";
        $script .= "    echo \$result ? 'true' : 'false';\n";
        $script .= "} else {\n";
        $script .= "    echo \$result;\n";
        $script .= "}\n";
        $script .= "?>";
        
        return $script;
    }
}
