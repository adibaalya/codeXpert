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

    /**
     * Build Java driver script - Optimized for LeetCode style
     */
    /**
     * Build Java driver script - Optimized for LeetCode style
     */
    private function buildJavaDriver($userCode, $inputData, $functionName, $parameters)
    {
        // 1. Handle Raw Input Parsing (Same as before)
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
        $script = "";
        $script .= "import java.util.*;\n"; 
        $script .= "import java.util.stream.*;\n"; 
        foreach ($imports as $import) {
            if (stripos($import, 'java.util') === false) {
                $script .= trim($import) . "\n";
            }
        }
        $script .= "\n";

        $script .= "public class Main {\n";
        
        // === NEW HELPER METHOD FOR PRINTING ARRAYS ===
        $script .= "    // Helper to print arrays correctly (fixes [I@... output)\n";
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
        $script .= "            // Handles String[], Integer[], and 2D arrays like int[][]\n";
        $script .= "            System.out.println(Arrays.deepToString((Object[]) o));\n";
        $script .= "        } else { \n";
        $script .= "            System.out.println(o);\n";
        $script .= "        }\n";
        $script .= "    }\n\n";
        // =============================================

        $script .= "    public static void main(String[] args) {\n";
        $script .= "        try {\n";
        
        $actualParams = array_keys($inputData);

        // Generate Variable Definitions
        foreach ($actualParams as $key) {
            $value = $inputData[$key];
            $script .= "            " . $this->formatJavaVariableAdvanced($key, $value) . "\n";
        }

        $script .= "\n";

        // Detect Class & Method Name
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

        // Instantiate and Call
        $script .= "            {$className} sol = new {$className}();\n";
        
        $paramNames = implode(', ', $actualParams);
        
        if (empty($actualParams)) {
             $script .= "            System.out.println(\"Error: No input variables found.\");\n";
        }

        // Execute
        $script .= "            Object result = sol.{$methodName}({$paramNames});\n";
        
        // Use the new helper to print
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
     * Build C++ driver script with LeetCode-style support
     * Includes helper templates for printing complex types
     */
    private function buildCppDriver($userCode, $inputData, $functionName, $parameters)
    {
        // Essential headers
        $script = "#include <iostream>\n#include <vector>\n#include <string>\n#include <algorithm>\n#include <map>\n#include <unordered_map>\n#include <set>\n#include <stack>\n#include <queue>\n#include <sstream>\nusing namespace std;\n\n";

        // --- FIX: REORDERED PRINT HELPERS ---
        // We declare prototypes first or define base types BEFORE containers
        $script .= <<<'EOT'
        // Forward declaration for nested vectors
        template<typename T> void printResult(const vector<T>& v);

        // 1. Helper: Print string (Specific override)
        void printResult(const string& s) {
            cout << "\"" << s << "\"";
        }
        
        // 2. Helper: Print basic types (Base case: int, bool, float)
        // This MUST be defined before the vector helper uses it
        template<typename T>
        void printResult(const T& x) {
            if constexpr (is_same_v<T, bool>) {
                cout << (x ? "true" : "false");
            } else {
                cout << x;
            }
        }

        // 3. Helper: Print vector (Recursive container)
        template<typename T>
        void printResult(const vector<T>& v) {
            cout << "[";
            for(size_t i=0; i<v.size(); ++i) {
                cout << (i==0?"":",");
                printResult(v[i]); // Now valid because base types are defined above
            }
            cout << "]";
        }
        EOT;
        $script .= "\n\n";

        $script .= "// --- USER CODE ---\n";
        $script .= $userCode . "\n";
        $script .= "// -----------------\n\n";

        $script .= "int main() {\n";
        
        // Filter metadata
        $actualParams = array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output']);
        });

        // Inject inputs
        foreach ($actualParams as $key) {
            $script .= "    " . $this->formatCppVariableAdvanced($key, $inputData[$key]) . "\n";
        }

        // Auto-detect function name
        $hasClass = preg_match('/class\s+Solution/', $userCode);
        
        if (empty($functionName)) {
            if ($hasClass) {
                // Look for method inside Solution class
                if (preg_match('/(?:int|void|bool|string|vector|double|float|long|ListNode|TreeNode)\s*(?:<[^>]+>)?\s*[*&]*\s+(\w+)\s*\(/', $userCode, $matches)) {
                    $functionName = $matches[1];
                } else {
                    $functionName = "solve"; 
                }
            } else {
                // Look for standalone function (excluding main)
                if (preg_match_all('/(?:int|void|bool|string|vector|double|float|long)\s*(?:<[^>]+>)?\s*[*&]*\s+(\w+)\s*\(/', $userCode, $matches)) {
                    foreach ($matches[1] as $match) {
                        if ($match !== 'main' && $match !== 'printResult') { // Exclude helpers
                            $functionName = $match;
                            break;
                        }
                    }
                }
            }
        }

        $paramsList = implode(', ', $actualParams);
        $callCode = "";

        if ($hasClass) {
            $script .= "    Solution sol;\n";
            $validFunc = !empty($functionName) ? $functionName : "solve";
            $callCode = "sol.{$validFunc}({$paramsList})";
        } else {
            $validFunc = !empty($functionName) ? $functionName : "solve";
            $callCode = "{$validFunc}({$paramsList})";
        }

        // Capture and Print
        $script .= "    auto result = {$callCode};\n";
        $script .= "    printResult(result);\n";
        $script .= "    return 0;\n";
        $script .= "}\n";

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

    /**
     * Build C driver script with robust Type Detection and Pointer support
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
        
        // --- FIX 1: Auto-close missing braces ---
        $openBraces = substr_count($userCode, '{');
        $closeBraces = substr_count($userCode, '}');
        if ($openBraces > $closeBraces) {
            $needed = $openBraces - $closeBraces;
            $script .= "\n" . str_repeat("}\n", $needed);
        }
        
        $script .= "// --- USER CODE END ---\n\n";
        
        // 1. Auto-Detect Function Signature
        $returnType = 'int'; 
        
        if (!$functionName) {
            // Find function definition (heuristic: first function that isn't main)
            if (preg_match('/^\s*((?:const\s+)?(?:unsigned\s+)?\w+(?:\s*\*)*)\s+(\w+)\s*\(/m', $userCode, $matches)) {
                $returnType = trim($matches[1]);
                $functionName = $matches[2];
            }
        } else {
            // If function name is known, find its specific return type
            if (preg_match('/^\s*((?:const\s+)?(?:unsigned\s+)?\w+(?:\s*\*)*)\s+' . preg_quote($functionName, '/') . '\s*\(/m', $userCode, $matches)) {
                $returnType = trim($matches[1]);
            }
        }

        $script .= "int main() {\n";
        
        // 2. Inject Variables
        $actualParams = array_filter(array_keys($inputData), function($k) {
            return !in_array($k, ['test_case', 'input', 'output']);
        });

        foreach ($actualParams as $key) {
            $value = $inputData[$key];
            $script .= "    " . $this->formatCVariable($key, $value) . "\n";
        }
        
        // 3. Call Function & Print Result
        if ($functionName) {
            $paramsList = implode(', ', $actualParams);
            $cleanType = str_replace(' ', '', $returnType); // Remove spaces for easier checking (e.g. "int *")
            
            // A. Handle Void Return
            if ($returnType === 'void') {
                $script .= "    {$functionName}({$paramsList});\n";
                $script .= "    printf(\"Function executed (void return).\\n\");\n";
            } 
            // B. Handle Known Types
            else {
                $script .= "    {$returnType} result = {$functionName}({$paramsList});\n";
                
                // Helper to check for pointers (look for *)
                $isPointer = strpos($returnType, '*') !== false;
                
                if ($isPointer) {
                     // --- FIX 2: Enhanced Pointer Handling ---
                     
                     // Case: String (char*)
                     if (strpos($cleanType, 'char*') !== false) {
                         $script .= "    if (result) printf(\"%s\", result); else printf(\"null\");\n";
                     } 
                     // Case: Integer Array (int*)
                     elseif (strpos($cleanType, 'int*') !== false) {
                         // Heuristic to guess array size for printing
                         $sizeExpr = "10"; // Fallback
                         if (in_array('sizeA', $actualParams) && in_array('sizeB', $actualParams)) {
                             $sizeExpr = "sizeA + sizeB";
                         } elseif (in_array('n', $actualParams)) {
                             $sizeExpr = "n";
                         }

                         $script .= "    printf(\"[\");\n";
                         // Protect against null pointer before looping
                         $script .= "    if (result == NULL) { printf(\"null\"); } else {\n";
                         $script .= "        for(int i=0; i < ({$sizeExpr}); i++) {\n";
                         $script .= "            printf(\"%d\", result[i]);\n";
                         $script .= "            if (i < ({$sizeExpr}) - 1) printf(\", \");\n";
                         $script .= "        }\n";
                         $script .= "    }\n";
                         $script .= "    printf(\"]\");\n";
                     }
                     else {
                         // Default Pointer Address
                         $script .= "    printf(\"Result Pointer: %p\", (void*)result);\n";
                     }
                }
                elseif ($returnType === 'bool') {
                    $script .= "    printf(result ? \"true\" : \"false\");\n";
                }
                elseif (strpos($returnType, 'float') !== false || strpos($returnType, 'double') !== false) {
                    $script .= "    printf(\"%.5f\", result);\n";
                }
                else {
                    // Default to int
                    $script .= "    printf(\"%d\", result);\n";
                }
            }
        } else {
             $script .= "    printf(\"Error: Could not detect function name in C code.\\n\");\n";
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
