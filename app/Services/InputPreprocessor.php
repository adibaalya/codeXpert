<?php

namespace App\Services;

/**
 * InputPreprocessor - Converts database inputs to IDE-like console input
 * 
 * Handles inputs from database that may contain:
 * - Brackets: [ ]
 * - Commas: ,
 * - Quotes: " or '
 * - Backticks: `
 * - Extra whitespace/newlines
 * 
 * Outputs clean strings that work exactly like Eclipse/VSCode/PhpStorm console input
 */
class InputPreprocessor
{
    /**
     * Main preprocessing function - converts any database input to clean console input
     * 
     * @param mixed $rawInput - Input from database (string, array, JSON)
     * @param string $language - Target language (java, python, cpp, php, javascript)
     * @return string - Clean console input ready for Scanner/cin/input()
     */
    public function preprocessInput($rawInput, string $language = 'generic'): string
    {
        // Step 1: Parse the raw input
        $parsedData = $this->parseRawInput($rawInput);
        
        // Step 2: Clean and flatten the data
        $cleanedData = $this->cleanData($parsedData);
        
        // Step 3: Format for console input
        $consoleInput = $this->formatForConsole($cleanedData, $language);
        
        return $consoleInput;
    }
    
    /**
     * Parse raw input from database (handles JSON, arrays, strings)
     */
    private function parseRawInput($rawInput)
    {
        // Already an array
        if (is_array($rawInput)) {
            return $rawInput;
        }
        
        // Try to decode as JSON first
        if (is_string($rawInput)) {
            // First, aggressively clean the string to prevent JSON parsing of non-JSON
            // Check if it's actually valid JSON (has proper structure)
            $trimmed = trim($rawInput);
            $isLikelyJSON = (
                (substr($trimmed, 0, 1) === '{' && substr($trimmed, -1) === '}') ||
                (substr($trimmed, 0, 1) === '[' && substr($trimmed, -1) === ']' && 
                 preg_match('/^\[\s*\{/', $trimmed)) // Array of objects
            );
            
            if ($isLikelyJSON) {
                $decoded = json_decode($rawInput, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
            
            // Not valid JSON or simple array - treat as raw string that needs aggressive cleaning
            // This handles cases like: "[1, 2, 3, 4]" or "`1,2,3`" or "1, 2, 3, 4"
            return $this->parseRawString($rawInput);
        }
        
        // Primitive types
        return ['value' => $rawInput];
    }
    
    /**
     * Parse and clean raw string input (handles brackets, commas, quotes, backticks)
     * This prevents Scanner.nextInt() InputMismatchException in Java
     */
    private function parseRawString(string $input): array
    {
        // Remove ALL special characters that Scanner cannot parse
        $cleaned = $input;
        
        // Step 1: Remove quotes FIRST (before other cleaning)
        $cleaned = str_replace(['"', "'", '`'], '', $cleaned);
        
        // Step 2: Remove brackets: [ ]
        $cleaned = str_replace(['[', ']'], '', $cleaned);
        
        // Step 3: Remove braces: { }
        $cleaned = str_replace(['{', '}'], '', $cleaned);
        
        // Step 4: Remove markdown code blocks
        $cleaned = preg_replace('/```[\w]*\n?/', '', $cleaned);
        
        // Step 5: Replace commas and semicolons with spaces
        $cleaned = str_replace([',', ';'], ' ', $cleaned);
        
        // Step 6: Normalize all whitespace to single spaces
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        
        // Step 7: Trim
        $cleaned = trim($cleaned);
        
        // If it contains = (key-value format like "n=5"), handle separately
        if (strpos($cleaned, '=') !== false) {
            return $this->parseKeyValueFormat($cleaned);
        }
        
        // Split into values
        $parts = preg_split('/\s+/', $cleaned);
        
        // Return as single array value for formatting
        return ['value' => implode(' ', $parts)];
    }
    
    /**
     * Parse key-value format: "n=5, arr=[1,2,3]" -> {"n": 5, "arr": [1,2,3]}
     */
    private function parseKeyValueFormat(string $input): array
    {
        $result = [];
        
        // Match patterns like: key=value
        preg_match_all('/(\w+)\s*=\s*([^\s,]+(?:\s+[^\s,=]+)*)/', $input, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $key = $match[1];
            $value = trim($match[2]);
            
            // Try to detect if it's an array (multiple numbers)
            if (preg_match_all('/\d+/', $value, $numbers)) {
                if (count($numbers[0]) > 1) {
                    $result[$key] = $numbers[0];
                } else {
                    $result[$key] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }
        
        return empty($result) ? ['value' => $input] : $result;
    }
    
    /**
     * Clean data by removing special characters and formatting
     */
    private function cleanData($data)
    {
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleaned[$key] = $this->cleanData($value);
            }
            return $cleaned;
        }
        
        if (is_string($data)) {
            // Remove markdown code blocks
            $data = preg_replace('/```[\w]*\n?/', '', $data);
            $data = preg_replace('/```/', '', $data);
            
            // Remove backticks
            $data = str_replace('`', '', $data);
            
            // Remove extra quotes (but keep internal quotes)
            $data = trim($data, '"\'');
            
            // Normalize whitespace
            $data = preg_replace('/\s+/', ' ', $data);
            
            return trim($data);
        }
        
        return $data;
    }
    
    /**
     * Format cleaned data into console input string
     * 
     * Examples:
     * - Array: [1, 2, 3, 4, 5] -> "1 2 3 4 5"
     * - Multi-value: {"n": 5, "arr": [1,2,3]} -> "5\n1 2 3"
     * - String: "Hello World" -> "Hello World"
     */
    private function formatForConsole($data, string $language): string
    {
        if (!is_array($data)) {
            return $this->valueToString($data);
        }
        
        $lines = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Flatten arrays to space-separated values
                $lines[] = $this->flattenArray($value);
            } else {
                // Single values on their own line
                $lines[] = $this->valueToString($value);
            }
        }
        
        // Join with newlines
        return implode("\n", array_filter($lines));
    }
    
    /**
     * Flatten array to space-separated string
     * [1, 2, 3] -> "1 2 3"
     * [[1, 2], [3, 4]] -> "1 2\n3 4"
     */
    private function flattenArray(array $arr): string
    {
        // Check if it's a 2D array
        if ($this->is2DArray($arr)) {
            $lines = [];
            foreach ($arr as $row) {
                $lines[] = implode(' ', array_map([$this, 'valueToString'], $row));
            }
            return implode("\n", $lines);
        }
        
        // 1D array - space-separated
        return implode(' ', array_map([$this, 'valueToString'], $arr));
    }
    
    /**
     * Check if array is 2D
     */
    private function is2DArray(array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }
        
        foreach ($arr as $item) {
            if (is_array($item)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Convert value to string (handle different types)
     */
    private function valueToString($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        
        if (is_null($value)) {
            return '';
        }
        
        if (is_numeric($value)) {
            return (string)$value;
        }
        
        // String - remove extra quotes
        return trim(str_replace(['"', "'", '`'], '', (string)$value));
    }
    
    /**
     * Advanced preprocessing for specific test case formats
     * 
     * Examples:
     * 1. "n=5, arr=[1,2,3,4,5]" -> "5\n1 2 3 4 5"
     * 2. "[1, 2, 3]" -> "1 2 3"
     * 3. "[[1,2],[3,4]]" -> "1 2\n3 4"
     */
    public function preprocessFromString(string $input): string
    {
        // Remove all brackets and clean up
        $cleaned = $input;
        
        // Handle array notation: [1, 2, 3]
        if (preg_match('/^\s*\[.*\]\s*$/', $cleaned)) {
            $cleaned = trim($cleaned, '[] ');
            $cleaned = str_replace([',', ';'], ' ', $cleaned);
            $cleaned = preg_replace('/\s+/', ' ', $cleaned);
            return trim($cleaned);
        }
        
        // Handle key-value pairs: n=5, arr=[1,2,3]
        if (strpos($cleaned, '=') !== false) {
            $pairs = [];
            
            // Split by commas (outside brackets)
            preg_match_all('/(\w+)\s*=\s*(\[.*?\]|[^,]+)/', $cleaned, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $value = trim($match[2]);
                
                // Remove brackets and format
                $value = trim($value, '[] ');
                $value = str_replace([',', ';'], ' ', $value);
                $value = preg_replace('/\s+/', ' ', $value);
                
                $pairs[] = trim($value);
            }
            
            return implode("\n", $pairs);
        }
        
        // Default: clean and return
        $cleaned = str_replace([',', ';', '[', ']', '{', '}'], ' ', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        
        return trim($cleaned);
    }
    
    /**
     * Preprocess for specific common patterns
     */
    public function preprocessCommonPatterns(string $input): string
    {
        $patterns = [
            // Pattern: "Input: [1,2,3]" -> "1 2 3"
            '/Input:\s*\[(.*?)\]/i' => function($matches) {
                return str_replace(',', ' ', $matches[1]);
            },
            
            // Pattern: "n = 5, array = [1,2,3]" -> "5\n1 2 3"
            '/(\w+)\s*=\s*(\d+).*?(\w+)\s*=\s*\[(.*?)\]/' => function($matches) {
                return $matches[2] . "\n" . str_replace(',', ' ', $matches[4]);
            },
            
            // Pattern: "5 [1 2 3 4 5]" -> "5\n1 2 3 4 5"
            '/(\d+)\s*\[(.*?)\]/' => function($matches) {
                return $matches[1] . "\n" . str_replace(',', ' ', $matches[2]);
            },
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $input, $matches)) {
                return $replacement($matches);
            }
        }
        
        return $input;
    }
    
    /**
     * Language-specific formatting (for edge cases)
     */
    public function formatForLanguage(string $cleanInput, string $language): string
    {
        switch (strtolower($language)) {
            case 'java':
                // Java Scanner works with space-separated or newline-separated
                return $cleanInput;
            
            case 'python':
                // Python input() reads line by line
                return $cleanInput;
            
            case 'cpp':
            case 'c++':
                // C++ cin works with whitespace-separated
                return $cleanInput;
            
            case 'php':
                // PHP fgets reads line by line
                return $cleanInput;
            
            case 'javascript':
            case 'nodejs':
                // Node.js readline reads line by line
                return $cleanInput;
            
            default:
                return $cleanInput;
        }
    }
    
    /**
     * Quick helper: Process database input in one call
     * 
     * Usage:
     * $preprocessor = new InputPreprocessor();
     * $cleanInput = $preprocessor->process($dbInput, 'java');
     */
    public function process($rawInput, string $language = 'generic'): string
    {
        // If it's already a simple string without special chars, return as-is
        if (is_string($rawInput) && !preg_match('/[\[\]{},"\'`]/', $rawInput)) {
            return trim($rawInput);
        }
        
        return $this->preprocessInput($rawInput, $language);
    }
    
    /**
     * Validate that the preprocessed input is clean
     */
    public function validate(string $processedInput): bool
    {
        // Check for remaining special characters
        $invalidChars = ['[', ']', '{', '}', ',', '`'];
        
        foreach ($invalidChars as $char) {
            if (strpos($processedInput, $char) !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get example transformations for documentation
     */
    public static function getExamples(): array
    {
        return [
            'Array input' => [
                'input' => '[1, 2, 3, 4, 5]',
                'output' => '1 2 3 4 5',
            ],
            'Multi-value input' => [
                'input' => '{"n": 5, "arr": [1, 2, 3, 4, 5]}',
                'output' => "5\n1 2 3 4 5",
            ],
            '2D array input' => [
                'input' => '[[1, 2], [3, 4], [5, 6]]',
                'output' => "1 2\n3 4\n5 6",
            ],
            'String with quotes' => [
                'input' => '"Hello World"',
                'output' => 'Hello World',
            ],
            'Mixed input' => [
                'input' => 'n=5, numbers=[10, 20, 30, 40, 50]',
                'output' => "5\n10 20 30 40 50",
            ],
            'Markdown code block' => [
                'input' => "```\n[1, 2, 3]\n```",
                'output' => '1 2 3',
            ],
        ];
    }
}
