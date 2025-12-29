<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class CodeExecutionService
{
    /**
     * Execute user code with test cases - IDE-like behavior (no driver script)
     */
    public function executeCode($userCode, $language, $testInput, $functionName = null, $parameters = [])
    {
        try {
            // Check if user wants complete program execution (default for most cases)
            $useCompleteProgram = $this->shouldUseCompleteProgram($userCode, $language);
            
            // Build runnable code based on execution mode
            if ($useCompleteProgram) {
                // Use user's code as-is (Eclipse/VSCode behavior)
                $runnableCode = $userCode;
            } else {
                // Use driver script for function-based testing
                $runnableCode = $this->buildDriverScript($userCode, $testInput, $language, $functionName, $parameters);
            }
            
            // Map language names to script-friendly names
            $languageMap = [
                'python' => 'python',
                'java' => 'java',
                'javascript' => 'javascript',
                'c++' => 'cpp',
                'c' => 'c',
                'php' => 'php',
                'c#' => 'csharp'
            ];

            $mappedLanguage = $languageMap[strtolower($language)] ?? strtolower($language);

            // Map language to file extension for Docker container
            $extensionMap = [
                'cpp' => 'cpp',
                'c' => 'c',
                'python' => 'py',
                'javascript' => 'js',
                'php' => 'php',
                'java' => 'java',
                'csharp' => 'cs'
            ];
            $ext = $extensionMap[$mappedLanguage] ?? 'txt';

            // Prepare stdin input for programs that read from Scanner/input()
            $stdinInput = $useCompleteProgram ? $this->prepareStdinInput($testInput, $language) : '';

            // Execute code using Docker with Process
            $process = new \Symfony\Component\Process\Process([
                'docker', 'run', '--rm',
                '--memory=256m', // Increased for large datasets
                '--cpus=1.0',
                '--network=none',
                '--pids-limit=100',
                '-i', // Interactive mode to support stdin
                '-e', "FILE_EXTENSION=" . $ext, // Pass extension for compile vs interpret decision
                '-e', "USER_CODE=" . $runnableCode,
                '-e', "LANGUAGE=" . $mappedLanguage,
                '-e', "TEST_INPUT=" . $stdinInput,
                'code-sandbox'
            ]);

            // Set timeout (15 seconds for large datasets)
            $process->setTimeout(15);
            
            // Provide input via stdin if needed
            if (!empty($stdinInput)) {
                $process->setInput($stdinInput);
            }
            
            // Run the process
            $process->run();

            $output = $process->getOutput();
            $error = $process->getErrorOutput();

            // If there's an error, return it
            if ($process->getExitCode() !== 0 && $error) {
                return [
                    'success' => false,
                    'output' => "Error:\n" . $error
                ];
            }

            // Return output - handle cases where output is "0" or other falsy values
            $trimmedOutput = trim($output);
            return [
                'success' => true,
                'output' => $trimmedOutput !== '' ? $trimmedOutput : "Code executed successfully with no output."
            ];

        } catch (ProcessTimedOutException $e) {
            return [
                'success' => false,
                'output' => 'Error: Execution timed out (15 seconds limit). Please optimize your code.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => 'Error: ' . $e->getMessage()
            ];
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
                // Check if user has script-style PHP
                return preg_match('/<\?php/i', $userCode) ||
                       preg_match('/echo|fgets|readline/i', $userCode);
            
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

    /**
     * Build the driver script that combines:
     * 1. Database input (as variables)
     * 2. User's code (function definition)
     * 3. Execution trigger (function call with print)
     */
    private function buildDriverScript($userCode, $testInput, $language, $functionName = null, $parameters = [])
    {
        $language = strtolower($language);
        
        // Parse test input - handle both JSON object and JSON string
        $inputData = $this->parseTestInput($testInput);
        
        // Build driver script based on language
        switch ($language) {
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
            
            case 'c':
                return $this->buildCDriver($userCode, $inputData, $functionName, $parameters);
            
            case 'c#':
            case 'csharp':
                return $this->buildCSharpDriver($userCode, $inputData, $functionName, $parameters);
            
            default:
                return $userCode; // Fallback to original code
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

    /**
     * Parse multiple variable assignments from a string
     * Examples:
     * - "queue_list = [1, 2, 3], num_dequeues = 2"
     * - "code_string = '{[()]}'"
     * - "customer_data = {\"Premium\": [(10, 100.0)], \"Basic\": [(2, 20.0)]}"
     */
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

    /**
     * Build Python driver script
     * Example output:
     * job_durations = [10, 5, 15, 20]
     * num_workers = 2
     * max_worker_load = 35
     * 
     * [user's function code]
     * 
     * print(solve_job(job_durations, num_workers, max_worker_load))
     */
    private function buildPythonDriver($userCode, $inputData, $functionName, $parameters)
    {
        $script = "# Driver Script - Test Input Variables\n";
        $script .= "import json\n\n";
        
        // Section 1: Inject input variables from database
        foreach ($inputData as $key => $value) {
            // Skip metadata fields
            if (in_array($key, ['test_case', 'input', 'output'])) {
                continue;
            }
            $script .= $this->formatPythonVariable($key, $value) . "\n";
        }
        
        $script .= "\n# User Code\n";
        $script .= $userCode . "\n\n";
        
        // Check if user code contains a class definition
        $hasClass = preg_match('/^\s*class\s+(\w+)/m', $userCode, $classMatch);
        $className = $hasClass ? $classMatch[1] : null;
        
        // Section 2: Execute the function and print result
        if ($functionName) {
            // Get actual parameter names (excluding metadata)
            $actualParams = array_filter(array_keys($inputData), function($k) {
                return !in_array($k, ['test_case', 'input', 'output']);
            });
            
            if (!empty($actualParams)) {
                $script .= "# Execute and print result\n";
                $paramsList = implode(', ', $actualParams);
                
                // If there's a class, instantiate it first
                if ($className) {
                    $script .= "solution = {$className}()\n";
                    $script .= "result = solution.{$functionName}({$paramsList})\n";
                } else {
                    $script .= "result = {$functionName}({$paramsList})\n";
                }
                
                $script .= "# Print as JSON for consistent formatting\n";
                $script .= "if isinstance(result, dict):\n";
                $script .= "    print(json.dumps(result, separators=(',', ': ')))\n";
                $script .= "else:\n";
                $script .= "    print(result)\n";
            } else {
                // No parameters - just call the function
                $script .= "# Execute and print result\n";
                
                if ($className) {
                    $script .= "solution = {$className}()\n";
                    $script .= "result = solution.{$functionName}()\n";
                } else {
                    $script .= "result = {$functionName}()\n";
                }
                
                $script .= "# Print as JSON for consistent formatting\n";
                $script .= "if isinstance(result, dict):\n";
                $script .= "    print(json.dumps(result, separators=(',', ': ')))\n";
                $script .= "else:\n";
                $script .= "    print(result)\n";
            }
        } else {
            // Try to auto-detect function name from user code
            // First check if there's a method inside a class
            if ($hasClass && preg_match('/^\s{4,}def\s+(\w+)\s*\(/m', $userCode, $methodMatch)) {
                $detectedMethodName = $methodMatch[1];
                
                // Skip __init__ and other magic methods
                if (!preg_match('/^__.*__$/', $detectedMethodName)) {
                    $actualParams = array_filter(array_keys($inputData), function($k) {
                        return !in_array($k, ['test_case', 'input', 'output']);
                    });
                    
                    if (!empty($actualParams)) {
                        $script .= "# Auto-detected class method execution\n";
                        $paramsList = implode(', ', $actualParams);
                        $script .= "solution = {$className}()\n";
                        $script .= "result = solution.{$detectedMethodName}({$paramsList})\n";
                        $script .= "# Print as JSON for consistent formatting\n";
                        $script .= "if isinstance(result, dict):\n";
                        $script .= "    print(json.dumps(result, separators=(',', ': ')))\n";
                        $script .= "else:\n";
                        $script .= "    print(result)\n";
                    } else {
                        $script .= "# Auto-detected class method execution\n";
                        $script .= "solution = {$className}()\n";
                        $script .= "result = solution.{$detectedMethodName}()\n";
                        $script .= "# Print as JSON for consistent formatting\n";
                        $script .= "if isinstance(result, dict):\n";
                        $script .= "    print(json.dumps(result, separators=(',', ': ')))\n";
                        $script .= "else:\n";
                        $script .= "    print(result)\n";
                    }
                }
            }
            // If no class, try to detect standalone function
            elseif (preg_match('/^\s*def\s+(\w+)\s*\(/m', $userCode, $matches)) {
                $detectedFunctionName = $matches[1];
                
                $actualParams = array_filter(array_keys($inputData), function($k) {
                    return !in_array($k, ['test_case', 'input', 'output']);
                });
                
                if (!empty($actualParams)) {
                    $script .= "# Auto-detected function execution\n";
                    $paramsList = implode(', ', $actualParams);
                    $script .= "result = {$detectedFunctionName}({$paramsList})\n";
                    $script .= "# Print as JSON for consistent formatting\n";
                    $script .= "if isinstance(result, dict):\n";
                    $script .= "    print(json.dumps(result, separators=(',', ': ')))\n";
                    $script .= "else:\n";
                    $script .= "    print(result)\n";
                } else {
                    $script .= "# Auto-detected function execution\n";
                    $script .= "result = {$detectedFunctionName}()\n";
                    $script .= "# Print as JSON for consistent formatting\n";
                    $script .= "if isinstance(result, dict):\n";
                    $script .= "    print(json.dumps(result, separators=(',', ': ')))\n";
                    $script .= "else:\n";
                    $script .= "    print(result)\n";
                }
            }
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

    /**
     * Robust JavaScript Driver - Handles Arrow Functions & Objects
     */
    private function buildJavaScriptDriver($userCode, $inputData, $functionName, $parameters)
    {
        $script = "// Driver Script\n";
        
        // 1. Inject Variables
        // Filter out metadata
        $actualParams = array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output']);
        });

        foreach ($actualParams as $key) {
            $script .= $this->formatJavaScriptVariable($key, $inputData[$key]) . "\n";
        }
        
        $script .= "\n// --- USER CODE START ---\n";
        $script .= $userCode . "\n";
        $script .= "// --- USER CODE END ---\n\n";
        
        // 2. Auto-Detect Function Name (Enhanced for Arrow Functions)
        if (!$functionName) {
            // Priority 1: Standard function 'function solve('
            if (preg_match('/function\s+(\w+)\s*\(/', $userCode, $matches)) {
                $functionName = $matches[1];
            } 
            // Priority 2: Arrow/Variable function 'const solve = (' or 'var solve = function'
            elseif (preg_match('/(?:const|let|var)\s+(\w+)\s*=\s*(?:function|\(?[\w\s,]*\)?\s*=>)/', $userCode, $matches)) {
                $functionName = $matches[1];
            }
            // Priority 3: Class method (LeetCode style)
            elseif (preg_match('/class\s+Solution/', $userCode)) {
                $functionName = "solve"; // Default assumption for class, requires instantiation logic below
            }
        }

        // 3. Execution Block with Try/Catch
        $script .= "// --- EXECUTION ---\n";
        $script .= "try {\n";
        
        $paramsList = implode(', ', $actualParams);
        
        // Handle Class-based solutions (LeetCode)
        if (preg_match('/class\s+Solution/', $userCode)) {
             // Try to find the method name inside the class
             if (preg_match('/(\w+)\s*\(/', $userCode, $methodMatch)) {
                 // This is a naive check, might grab constructor. 
                 // Better to default to 'solve' or 'maxProfit' type names if passed explicitly.
                 // For now, we assume the user provided functionName or we guess 'solve'
                 $methodToCall = $functionName ?: 'solve'; 
             } else {
                 $methodToCall = 'solve';
             }
             
             $script .= "    const solution = new Solution();\n";
             // check if method exists
             $script .= "    if (typeof solution.{$methodToCall} === 'function') {\n";
             $script .= "        const result = solution.{$methodToCall}({$paramsList});\n";
             $script .= "        printResult(result);\n";
             $script .= "    } else {\n";
             $script .= "        console.log('Error: Method {$methodToCall} not found in Solution class');\n";
             $script .= "    }\n";
        } 
        // Handle Standard Functions
        else if ($functionName) {
            $script .= "    if (typeof {$functionName} === 'function') {\n";
            $script .= "        const result = {$functionName}({$paramsList});\n";
            $script .= "        printResult(result);\n";
            $script .= "    } else {\n";
            $script .= "        console.log('Error: Function {$functionName} not found.');\n";
            $script .= "    }\n";
        } else {
            $script .= "    console.log('Error: No function detected.');\n";
        }
        
        $script .= "} catch (error) {\n";
        $script .= "    console.log('Runtime Error: ' + error.message);\n";
        $script .= "}\n\n";

        // 4. Helper for Printing (Ensures Objects/Arrays are visible)
        $script .= "function printResult(val) {\n";
        $script .= "    if (val === undefined) {\n";
        $script .= "        console.log('undefined');\n";
        $script .= "    } else if (val === null) {\n";
        $script .= "        console.log('null');\n";
        $script .= "    } else if (typeof val === 'object') {\n";
        $script .= "        // JSON.stringify ensures arrays/objects are printed fully on one line\n";
        $script .= "        console.log(JSON.stringify(val));\n";
        $script .= "    } else {\n";
        $script .= "        console.log(val);\n";
        $script .= "    }\n";
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

    private function buildJavaDriver($userCode, $inputData, $functionName, $parameters)
    {
        // --- STEP 1: Handle User Imports ---
        $userImports = [];
        $userCodeClean = preg_replace_callback('/^\s*import\s+[\w\.*]+;\s*$/m', function($matches) use (&$userImports) {
            $userImports[] = trim($matches[0]);
            return ''; 
        }, $userCode);

        // --- STEP 2: Generate Header ---
        $script = "import java.util.*;\n";
        $script .= "import java.util.stream.*;\n";
        $script .= "import java.io.*;\n";
        $script .= implode("\n", array_unique($userImports)) . "\n\n";
        
        // --- STEP 3: Auto-Detect Function Name & Types (GENERIC-SAFE) ---
        $paramTypes = []; 
        
        // A. Find the method signature
        $paramsString = "";
        if (empty($functionName)) {
            if (preg_match('/public\s+(?:static\s+)?(?:[\w<>[\]]+\s+)(\w+)\s*\(([^)]*)\)/', $userCodeClean, $matches)) {
                $functionName = $matches[1];
                $paramsString = $matches[2];
            } else {
                $functionName = "unknownFunction";
            }
        } else {
            if (preg_match('/public\s+(?:static\s+)?(?:[\w<>[\]]+\s+)' . preg_quote($functionName, '/') . '\s*\(([^)]*)\)/', $userCodeClean, $matches)) {
                $paramsString = $matches[1];
            }
        }

        // B. Robust Parsing of "Map<String, Integer> map, int k"
        if (!empty($paramsString)) {
            // Replace commas inside <...> with a placeholder so explode() doesn't break them
            $cleanParams = "";
            $balance = 0;
            for ($i = 0; $i < strlen($paramsString); $i++) {
                $char = $paramsString[$i];
                if ($char === '<') $balance++;
                if ($char === '>') $balance--;
                
                if ($char === ',' && $balance > 0) {
                    $cleanParams .= "###COMMA###"; // Placeholder
                } else {
                    $cleanParams .= $char;
                }
            }
            
            // Now safely explode by comma
            $args = explode(',', $cleanParams);
            
            foreach ($args as $arg) {
                // Restore the original comma
                $arg = str_replace("###COMMA###", ",", trim($arg));
                
                // Regex to split "Type Name" (Name is the last word)
                if (preg_match('/^(.*)\s+(\w+)$/', $arg, $parts)) {
                    $pType = trim($parts[1]);
                    $pName = trim($parts[2]);
                    $paramTypes[$pName] = $pType;
                }
            }
        }

        $script .= "public class Main {\n";
        $script .= "    public static void main(String[] args) {\n";
        $script .= "        Solution sol = new Solution();\n";
        
        // --- STEP 4: Inject Variables ---
        $actualParams = array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output']);
        });

        foreach ($actualParams as $key) {
            $value = $inputData[$key];
            // Pass the correctly detected type (e.g. Map<String, Integer>)
            $enforcedType = isset($paramTypes[$key]) ? $paramTypes[$key] : null;
            $script .= "        " . $this->formatJavaVariable($key, $value, $enforcedType) . "\n";
        }
        
        $paramsList = implode(', ', $actualParams);

        // --- STEP 5: Call Function & Print Result ---
        $returnType = 'Object'; 
        if (preg_match('/^\s*public\s+(?:static\s+)?([a-zA-Z0-9_<>\[\]]+)\s+' . preg_quote($functionName, '/') . '\s*\(/m', $userCodeClean, $matches)) {
            $returnType = trim($matches[1]);
        }
        
        if ($returnType === 'void') {
            $script .= "        sol.{$functionName}({$paramsList});\n";
            $script .= "        System.out.println(\"null\");\n";
        } else {
            $script .= "        {$returnType} result = sol.{$functionName}({$paramsList});\n";
            
            if (strpos($returnType, '[][]') !== false) {
                $script .= "        System.out.println(Arrays.deepToString(result));\n";
            } elseif (strpos($returnType, '[]') !== false) {
                $script .= "        System.out.println(Arrays.toString(result));\n";
            } else {
                $script .= "        System.out.println(result);\n";
            }
        }

        $script .= "    }\n";
        $script .= "}\n\n";

        // --- STEP 6: Append User Code ---
        if (preg_match('/\bclass\s+\w+/', $userCodeClean)) {
            // Remove 'public' from 'public class Solution' to avoid filename mismatch
            $script .= preg_replace('/public\s+class\s+Solution/', 'class Solution', $userCodeClean);
        } else {
            $script .= "class Solution {\n";
            $script .= $userCodeClean . "\n";
            $script .= "}\n";
        }
        
        return $script;
    }

    /**
     * Helper: Format PHP data into Java Variable Declarations
     * Handles primitives, 1D/2D arrays, and Maps.
     */
    private function formatJavaVariable($name, $value, $enforcedType = null)
    {
        // --- 1. Handle Maps (FIX FOR YOUR ERROR) ---
        // If the detected type is a Map (e.g. Map<String, Integer>), we build a HashMap.
        if ($enforcedType && strpos($enforcedType, 'Map') !== false && is_array($value)) {
            // Default types
            $keyType = 'String'; 
            $valType = 'Integer';
            
            // Try to extract specific types from Map<K, V>
            if (preg_match('/Map\s*<\s*([a-zA-Z0-9_]+)\s*,\s*([a-zA-Z0-9_]+)\s*>/', $enforcedType, $matches)) {
                $keyType = $matches[1];
                $valType = $matches[2];
            }

            // Generate Map initialization code
            // We use a multi-line string to create the map and add items one by one.
            $lines = [];
            $lines[] = "Map<{$keyType}, {$valType}> {$name} = new HashMap<>();";
            
            foreach ($value as $k => $v) {
                // Format the Key
                $kFormatted = ($keyType === 'String') ? "\"$k\"" : $k;
                
                // Format the Value
                $vFormatted = $v;
                if ($valType === 'String') {
                    $vFormatted = "\"$v\"";
                } elseif ($valType === 'Boolean') {
                    $vFormatted = ($v ? 'true' : 'false');
                }
                
                $lines[] = "{$name}.put({$kFormatted}, {$vFormatted});";
            }
            
            // Return the code block, joined with proper indentation
            return implode("\n        ", $lines);
        }

        // --- 2. Handle 2D Arrays (Existing Logic) ---
        if ($enforcedType === 'char[][]' && is_array($value)) {
            $rows = [];
            foreach ($value as $subArr) {
                $charRow = array_map(function($v) { return "'" . $v . "'"; }, $subArr);
                $rows[] = "{" . implode(', ', $charRow) . "}";
            }
            return "char[][] {$name} = { " . implode(', ', $rows) . " };";
        }

        // --- 3. Handle 1D Character Arrays ---
        if ($enforcedType === 'char[]' && is_array($value)) {
            $vals = implode(', ', array_map(function($v) { return "'$v'"; }, $value));
            return "char[] {$name} = {{$vals}};";
        }

        // --- 4. Standard Auto-Detection (Fallback) ---
        if (is_int($value)) {
            return "int {$name} = {$value};";
        } elseif (is_float($value)) {
            return "double {$name} = {$value};";
        } elseif (is_bool($value)) {
            return "boolean {$name} = " . ($value ? 'true' : 'false') . ";";
        } elseif (is_string($value)) {
            return "String {$name} = \"" . addslashes($value) . "\";";
        } elseif (is_array($value)) {
            if (empty($value)) return "int[] {$name} = {};";
            
            $first = reset($value);
            
            // 2D Array
            if (is_array($first)) {
                $rows = [];
                foreach ($value as $subArr) {
                    if (empty($subArr)) $rows[] = "{}";
                    else $rows[] = "{" . implode(', ', $subArr) . "}";
                }
                return "int[][] {$name} = { " . implode(', ', $rows) . " };";
            }
            
            // 1D Array
            if (is_int($first)) {
                return "int[] {$name} = {" . implode(', ', $value) . "};";
            } elseif (is_string($first)) {
                $vals = implode(', ', array_map(function($v) { return "\"$v\""; }, $value));
                return "String[] {$name} = {{$vals}};";
            }
        }
        
        return "Object {$name} = null;";
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
     * Robust PHP Driver - Forces output and handles errors
     */
    private function buildPHPDriver($userCode, $inputData, $functionName, $parameters)
    {
        // 1. Clean User Code (Remove <?php tags if present)
        $cleanCode = preg_replace('/^<\?php\s*/i', '', trim($userCode));
        $cleanCode = preg_replace('/\?>\s*$/', '', $cleanCode);

        $script = "<?php\n";
        $script .= "// --- DRIVER SETUP ---\n";
        $script .= "error_reporting(E_ALL);\n"; // Capture all errors
        $script .= "ini_set('display_errors', '1');\n\n";

        // 2. Inject Variables
        $actualParams = array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output']);
        });

        foreach ($actualParams as $key) {
            $script .= $this->formatPHPVariable($key, $inputData[$key]) . "\n";
        }

        $script .= "\n// --- USER CODE START ---\n";
        $script .= $cleanCode . "\n";
        $script .= "// --- USER CODE END ---\n\n";

        // 3. Auto-Detect Function/Class if not provided
        if (!$functionName) {
            // Check for Class (LeetCode style)
            if (preg_match('/class\s+(\w+)/i', $cleanCode, $classMatch)) {
                $className = $classMatch[1];
                // Find method inside class
                if (preg_match('/function\s+(\w+)\s*\(/i', $cleanCode, $methodMatch)) {
                    $functionName = $methodMatch[1];
                } else {
                    $functionName = "solve"; // Fallback
                }
            } 
            // Check for standalone function
            elseif (preg_match('/function\s+(\w+)\s*\(/i', $cleanCode, $match)) {
                $functionName = $match[1];
                $className = null;
            }
        } else {
             // Check if class exists even if function name is provided
             if (preg_match('/class\s+(\w+)/i', $cleanCode, $classMatch)) {
                 $className = $classMatch[1];
             } else {
                 $className = null;
             }
        }

        // 4. Execution Block
        $script .= "// --- EXECUTION ---\n";
        $script .= "try {\n";
        
        $paramsList = '$' . implode(', $', $actualParams);
        
        if (isset($className) && $className) {
            $script .= "    \$solution = new {$className}();\n";
            $script .= "    \$result = \$solution->{$functionName}({$paramsList});\n";
        } else {
            // If function found, call it
            if ($functionName) {
                $script .= "    if (function_exists('{$functionName}')) {\n";
                $script .= "        \$result = {$functionName}({$paramsList});\n";
                $script .= "    } else {\n";
                $script .= "        // If no function, assume user code ran as script (echo manually)\n";
                $script .= "        // But if they didn't echo, we set a default message\n";
                $script .= "        \$result = 'Error: Function {$functionName} not found.';\n";
                $script .= "    }\n";
            } else {
                 $script .= "    \$result = 'Error: No function detected.';\n";
            }
        }

        // 5. Output Formatting (Force JSON)
        $script .= "\n    // Convert result to JSON for safe printing\n";
        $script .= "    if (\$result === null) {\n";
        $script .= "        echo 'null';\n";
        $script .= "    } else if (is_bool(\$result)) {\n";
        $script .= "        echo \$result ? 'true' : 'false';\n";
        $script .= "    } else if (is_string(\$result)) {\n";
        $script .= "        // Check if it's an error message\n";
        $script .= "        echo \$result;\n"; 
        $script .= "    } else {\n";
        $script .= "        \$json = json_encode(\$result);\n";
        $script .= "        if (\$json === false) {\n";
        $script .= "            echo 'Error: JSON encoding failed (recursion or bad chars).';\n";
        $script .= "        } else {\n";
        $script .= "            echo \$json;\n";
        $script .= "        }\n";
        $script .= "    }\n";

        $script .= "} catch (Exception \$e) {\n";
        $script .= "    echo 'Runtime Error: ' . \$e->getMessage();\n";
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

    /**
     * Build C++ driver script with Support for 2D Vectors and JSON-like output
     */
    private function buildCppDriver($userCode, $inputData, $functionName, $parameters)
    {
        // --- FIX 1: Suppress Warnings ---
        $script = "#pragma GCC diagnostic ignored \"-Wsign-compare\"\n";
        
        $script .= "#include <iostream>\n";
        $script .= "#include <vector>\n";
        $script .= "#include <string>\n";
        $script .= "#include <algorithm>\n";
        $script .= "#include <map>\n";
        $script .= "#include <unordered_map>\n";
        $script .= "using namespace std;\n\n";

        // --- STEP 1: Handle Class Wrapping ---
        if (strpos($userCode, 'class Solution') !== false) {
            $script .= $userCode . "\n";
        } else {
            $script .= "class Solution {\npublic:\n";
            $script .= $userCode . "\n";
            $script .= "};\n";
        }

        // --- STEP 2: Function & Type Detection ---
        $paramTypes = [];
        $returnType = "auto"; 
        
        if (preg_match('/(?:^|\s)([\w<>:&*]+)\s+(\w+)\s*\(([^)]*)\)/', $userCode, $matches)) {
            if (empty($functionName)) {
                $functionName = $matches[2];
            }
            if ($functionName === $matches[2]) {
                $returnType = trim($matches[1]);
                $argsRaw = $matches[3];
                $args = explode(',', $argsRaw);
                foreach ($args as $arg) {
                    $arg = trim($arg);
                    if (preg_match('/^(.+?)\s+([a-zA-Z0-9_]+)$/', $arg, $pMatches)) {
                        $pType = str_replace(['&', 'const '], '', trim($pMatches[1]));
                        $pName = trim($pMatches[2]);
                        $paramTypes[$pName] = $pType;
                    }
                }
            }
        }
        
        if (empty($functionName)) $functionName = "unknown_function";

        $script .= "\nint main() {\n";
        $script .= "    Solution sol;\n";

        // --- STEP 3: Inject Variables ---
        $actualParams = array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output']);
        });

        foreach ($actualParams as $key) {
            $value = $inputData[$key];
            $enforcedType = isset($paramTypes[$key]) ? $paramTypes[$key] : null;
            $script .= "    " . $this->formatCppVariable($key, $value, $enforcedType) . "\n";
        }

        // --- STEP 4: Call Function & Print (THE FIX IS HERE) ---
        $paramsList = implode(', ', $actualParams);
        
        if ($returnType === 'void') {
            $script .= "    sol.{$functionName}({$paramsList});\n";
            $script .= "    cout << \"null\" << endl;\n";
        } else {
            $script .= "    auto result = sol.{$functionName}({$paramsList});\n";
            
            // --- Logic to Print Result based on Type ---
            
            // Check if return type needs quotes (strings or chars)
            $needsQuotes = (strpos($returnType, 'string') !== false || strpos($returnType, 'char') !== false);
            
            // Case A: 2D Vector (vector<vector<T>>)
            if (strpos($returnType, 'vector<vector') !== false) {
                $script .= "    cout << \"[\";\n";
                $script .= "    for (size_t i = 0; i < result.size(); ++i) {\n";
                $script .= "        cout << \"[\";\n";
                $script .= "        for (size_t j = 0; j < result[i].size(); ++j) {\n";
                if ($needsQuotes) {
                    $script .= "            cout << \"\\\"\" << result[i][j] << \"\\\"\";\n";
                } else {
                    $script .= "            cout << result[i][j];\n";
                }
                $script .= "            if (j < result[i].size() - 1) cout << \",\";\n";
                $script .= "        }\n";
                $script .= "        cout << \"]\";\n";
                $script .= "        if (i < result.size() - 1) cout << \",\";\n";
                $script .= "    }\n";
                $script .= "    cout << \"]\" << endl;\n";
            }
            // Case B: 1D Vector (vector<T>)
            elseif (strpos($returnType, 'vector') !== false) {
                $script .= "    cout << \"[\";\n";
                $script .= "    for (size_t i = 0; i < result.size(); ++i) {\n";
                if ($needsQuotes) {
                    $script .= "        cout << \"\\\"\" << result[i] << \"\\\"\";\n";
                } else {
                    $script .= "        cout << result[i];\n";
                }
                $script .= "        cout << (i < result.size() - 1 ? \",\" : \"\");\n";
                $script .= "    }\n";
                $script .= "    cout << \"]\" << endl;\n";
            }
            // Case C: Boolean
            elseif ($returnType === 'bool') {
                // If expected output is 1/0, use integer logic. If true/false, use boolalpha.
                // Standardizing to true/false for JSON compatibility usually works best, 
                // but for specific C++ challenges that expect 1, use: result ? 1 : 0
                $script .= "    cout << (result ? \"true\" : \"false\") << endl;\n"; 
            }
            // Case D: Primitives (int, string, double)
            else {
                $script .= "    cout << result << endl;\n";
            }
        }

        $script .= "    return 0;\n";
        $script .= "}\n";
        
        return $script;
    }

    /**
     * Helper: Format C++ Variable Declaration
     */
    private function formatCppVariable($name, $value, $enforcedType = null)
    {
        // Check if we need to force Char format ('A' vs "A")
        $isCharType = ($enforcedType && strpos($enforcedType, 'char') !== false);

        if (is_array($value)) {
            if (empty($value)) {
                $type = $enforcedType ?: "vector<int>";
                return "{$type} {$name} = {};";
            }

            $first = reset($value);
            
            // 2D Vector
            if (is_array($first)) {
                $rows = [];
                foreach ($value as $subArr) {
                    $elems = array_map(function($v) use ($isCharType) {
                        if ($isCharType) return "'{$v}'"; // Force single quotes
                        return is_string($v) ? "\"{$v}\"" : $v;
                    }, $subArr);
                    $rows[] = "{" . implode(',', $elems) . "}";
                }
                $type = $enforcedType ?: "vector<vector<int>>";
                return "{$type} {$name} = {" . implode(',', $rows) . "};";
            }
            
            // 1D Vector
            $elems = array_map(function($v) use ($isCharType) {
                if ($isCharType) return "'{$v}'";
                return is_string($v) ? "\"{$v}\"" : $v;
            }, $value);
            
            // Fallback Type Inference if detection failed
            $type = $enforcedType;
            if (!$type) {
                if (is_string($first)) $type = "vector<string>";
                elseif (is_float($first)) $type = "vector<double>";
                else $type = "vector<int>";
            }
            
            return "{$type} {$name} = {" . implode(',', $elems) . "};";
        }
        
        // Scalars
        if (is_bool($value)) return "bool {$name} = " . ($value ? 'true' : 'false') . ";";
        if (is_string($value)) return "string {$name} = \"{$value}\";";
        if (is_float($value)) return "double {$name} = {$value};";
        
        return "int {$name} = {$value};";
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

    /**
     * Build C driver script with Fix for Array Syntax "type name[]"
     */
    private function buildCDriver($userCode, $inputData, $functionName, $parameters)
    {
        $script = "#include <stdio.h>\n";
        $script .= "#include <stdlib.h>\n";
        $script .= "#include <string.h>\n";
        $script .= "#include <stdbool.h>\n";
        $script .= "#include <math.h>\n\n";
        
        $script .= "// --- USER CODE START ---\n";
        $script .= $userCode . "\n";
        
        // Auto-close missing braces
        $openBraces = substr_count($userCode, '{');
        $closeBraces = substr_count($userCode, '}');
        if ($openBraces > $closeBraces) {
            $script .= "\n" . str_repeat("}\n", $openBraces - $closeBraces);
        }
        $script .= "// --- USER CODE END ---\n\n";
        
        // --- STEP 1: Detect Function Signature ---
        $returnType = 'int'; 
        $funcArgs = []; 
        $foundFunction = false;

        // Try to find the function signature using regex
        // We look for: ReturnType FunctionName ( Arguments )
        if ($functionName) {
            $pattern = '/^\s*((?:const\s+)?(?:unsigned\s+)?\w+(?:\s*\*)*)\s+' . preg_quote($functionName, '/') . '\s*\(([^)]*)\)/m';
            if (preg_match($pattern, $userCode, $matches)) {
                $returnType = trim($matches[1]);
                $rawArgs = $matches[2];
                $foundFunction = true;
            }
        } 
        
        // Fallback: Scan for the first valid function if name not known or not found
        if (!$foundFunction) {
            if (preg_match_all('/^\s*((?:const\s+)?(?:unsigned\s+)?\w+(?:\s*\*)*)\s+(\w+)\s*\(([^)]*)\)/m', $userCode, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $t = trim($match[1]);
                    $n = trim($match[2]);
                    if (in_array($n, ['main', 'if', 'for', 'while', 'switch'])) continue;
                    $returnType = $t;
                    $functionName = $n;
                    $rawArgs = $match[3];
                    $foundFunction = true;
                    break;
                }
            }
        }
        
        // --- FIX: Robust Argument Parsing for "int arr[]" syntax ---
        if ($foundFunction && !empty($rawArgs)) {
            $argsSplit = explode(',', $rawArgs);
            foreach ($argsSplit as $arg) {
                $arg = trim($arg);
                if (empty($arg)) continue;
                
                // Regex to split Type and Name
                // Captures: "int *", "ptr" OR "int", "arr[]"
                // Now supports "[]" suffix in the name part
                if (preg_match('/^(.*[\s*])(\w+)(\[\])?$/', $arg, $parts)) {
                    $type = trim($parts[1]);
                    $name = trim($parts[2]); // The name without []
                    $isArray = isset($parts[3]) && $parts[3] === '[]';
                    
                    // If it had [], treat it as a pointer type
                    if ($isArray) {
                        $type .= '*'; 
                    }

                    $funcArgs[] = [
                        'type' => $type,
                        'name' => $name
                    ];
                }
            }
        }

        $script .= "int main() {\n";
        
        // --- STEP 2: Inject Input Variables ---
        $actualParams = array_values(array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output']);
        }));

        foreach ($actualParams as $key) {
            $value = $inputData[$key];
            $script .= "    " . $this->formatCVariable($key, $value) . "\n";
            $script .= "    (void){$key};\n"; // Suppress unused variable warning
        }
        
        // --- STEP 3: Build Function Call (Smart Mapping) ---
        $callArgs = [];
        $resultSizeVar = null;
        $usedInputIndices = []; 
        
        if (!empty($funcArgs)) {
            foreach ($funcArgs as $i => $arg) {
                $pName = $arg['name'];
                $pType = $arg['type'];
                
                // 1. EXACT Name Match (Best Case)
                if (in_array($pName, $actualParams)) {
                    $callArgs[] = $pName;
                    $keyIndex = array_search($pName, $actualParams);
                    if ($keyIndex !== false) $usedInputIndices[] = $keyIndex;
                } 
                // 2. Output Size Pointer (e.g. int* returnSize)
                elseif (strpos($pType, '*') !== false && strpos($pType, 'int') !== false && strpos(strtolower($pName), 'return') !== false) {
                    $script .= "    int {$pName} = 0; \n";
                    $callArgs[] = "&{$pName}";
                    $resultSizeVar = $pName;
                }
                // 3. Positional Mapping (Fallback)
                else {
                    // Grab the first unused input from DB
                    $found = false;
                    foreach ($actualParams as $index => $paramName) {
                        if (!in_array($index, $usedInputIndices)) {
                            // Basic Type Check (Optional but safer)
                            // If arg is int* (array), map to array input
                            // If arg is int, map to int input
                            // For now, we trust the order matches standard convention
                            
                            $callArgs[] = $paramName;
                            $usedInputIndices[] = $index;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) $callArgs[] = "0"; 
                }
            }
        } else {
            // Parser Failed? Dump inputs in order
            $callArgs = $actualParams;
        }

        // --- STEP 4: Call & Print ---
        $paramString = implode(', ', $callArgs);
        
        if ($returnType === 'void') {
            $script .= "    {$functionName}({$paramString});\n";
            $script .= "    printf(\"null\");\n";
        } else {
            $script .= "    {$returnType} result = {$functionName}({$paramString});\n";
            
            $cleanType = str_replace(' ', '', $returnType);
            
            // Handle Arrays (int*)
            if (strpos($cleanType, 'int*') !== false) {
                $sizeExpr = "10"; 
                if ($resultSizeVar) {
                    $sizeExpr = $resultSizeVar;
                } elseif (in_array('returnSize', $actualParams)) {
                    $sizeExpr = "returnSize";
                } elseif (in_array('size', $actualParams)) {
                    $sizeExpr = "size";
                } elseif (in_array('n', $actualParams)) {
                    $sizeExpr = "n";
                } 
                elseif (!empty($actualParams)) {
                    foreach ($actualParams as $p) {
                        if (strpos(strtolower($p), 'size') !== false || $p === 'n' || $p === 'len') {
                            $sizeExpr = $p;
                            break;
                        }
                    }
                }

                $script .= "    printf(\"[\");\n";
                $script .= "    if (result == NULL) { printf(\"null\"); } else {\n";
                $script .= "        for(int i=0; i < ({$sizeExpr}); i++) {\n";
                $script .= "            printf(\"%d\", result[i]);\n";
                $script .= "            if (i < ({$sizeExpr}) - 1) printf(\", \");\n";
                $script .= "        }\n";
                $script .= "    }\n";
                $script .= "    printf(\"]\");\n";
            } 
            elseif (strpos($cleanType, 'char*') !== false) {
                 $script .= "    if (result) printf(\"%s\", result); else printf(\"null\");\n";
            }
            elseif ($cleanType === 'bool') {
                 $script .= "    printf(result ? \"true\" : \"false\");\n";
            }
            else {
                 $script .= "    printf(\"%d\", result);\n";
            }
        }
        
        $script .= "\n    return 0;\n";
        $script .= "}\n";
        
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
