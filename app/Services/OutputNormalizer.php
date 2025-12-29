<?php

namespace App\Services;

class OutputNormalizer
{
    /**
     * Normalizes output string for comparison.
     * Handles trailing whitespace, line endings, and quote artifacts.
     * 
     * @param string|null $output
     * @return string
     */
    public static function normalize($output)
    {
        if (is_null($output) || $output === '') {
            return '';
        }

        // Convert to string if not already
        $output = (string) $output;

        // 1. Remove surrounding quotes (handles JSON encoding artifacts)
        // Only strip if the ENTIRE string is wrapped in quotes
        // This preserves internal quotes like: Hello "World"
        if (strlen($output) >= 2) {
            $firstChar = $output[0];
            $lastChar = $output[strlen($output) - 1];
            
            if (($firstChar === '"' && $lastChar === '"') || 
                ($firstChar === "'" && $lastChar === "'")) {
                $output = substr($output, 1, -1);
            }
        }

        // 2. Standardize newlines (Windows \r\n -> Unix \n)
        $output = str_replace("\r\n", "\n", $output);
        $output = str_replace("\r", "\n", $output);

        // 3. Trim each line (removes trailing spaces on each line)
        $lines = explode("\n", $output);
        $lines = array_map('trim', $lines);

        // 4. Remove completely empty lines at the end
        while (!empty($lines) && end($lines) === '') {
            array_pop($lines);
        }

        // 5. Rejoin lines
        $output = implode("\n", $lines);

        // 6. Final trim (remove leading/trailing whitespace from entire output)
        return trim($output);
    }

    /**
     * Compare two outputs with normalization.
     * Returns comparison result with status and details.
     * 
     * @param string $userOutput
     * @param string $expectedOutput
     * @return array
     */
    public static function compare($userOutput, $expectedOutput)
    {
        $normalizedUser = self::normalize($userOutput);
        $normalizedExpected = self::normalize($expectedOutput);

        // Strict comparison first
        if ($userOutput === $expectedOutput) {
            return [
                'status' => 'accepted',
                'match' => true,
                'message' => 'Output matches exactly'
            ];
        }

        // Loose comparison (normalized)
        if ($normalizedUser === $normalizedExpected) {
            return [
                'status' => 'accepted', // or 'presentation_error' if you want to distinguish
                'match' => true,
                'message' => 'Output matches (minor formatting differences ignored)'
            ];
        }

        // No match
        return [
            'status' => 'wrong_answer',
            'match' => false,
            'message' => 'Output does not match',
            'expected' => $normalizedExpected,
            'actual' => $normalizedUser,
            'diff' => self::generateDiff($normalizedExpected, $normalizedUser)
        ];
    }

    /**
     * Generate a human-readable diff with visible whitespace characters.
     * 
     * @param string $expected
     * @param string $actual
     * @return array
     */
    public static function generateDiff($expected, $actual)
    {
        return [
            'expected_visible' => self::makeWhitespaceVisible($expected),
            'actual_visible' => self::makeWhitespaceVisible($actual),
            'expected_length' => strlen($expected),
            'actual_length' => strlen($actual)
        ];
    }

    /**
     * Make whitespace characters visible for debugging.
     * 
     * @param string $text
     * @return string
     */
    public static function makeWhitespaceVisible($text)
    {
        $visible = $text;
        
        // Replace spaces with visible character (only trailing spaces per line)
        $lines = explode("\n", $visible);
        $processedLines = [];
        
        foreach ($lines as $line) {
            // Find trailing spaces
            $trimmed = rtrim($line);
            $trailingSpaces = strlen($line) - strlen($trimmed);
            
            if ($trailingSpaces > 0) {
                $line = $trimmed . str_repeat('␣', $trailingSpaces);
            }
            
            $processedLines[] = $line;
        }
        
        $visible = implode('↵' . "\n", $processedLines);
        
        // Add final newline indicator if present
        if (substr($text, -1) === "\n") {
            $visible .= '↵';
        }
        
        return $visible;
    }

    /**
     * Advanced normalization for numeric outputs.
     * Handles floating point precision issues.
     * 
     * @param string $output
     * @param int $precision
     * @return string
     */
    public static function normalizeNumeric($output, $precision = 6)
    {
        $normalized = self::normalize($output);
        
        // Check if output is a single number
        if (is_numeric($normalized)) {
            return number_format((float)$normalized, $precision, '.', '');
        }
        
        // Check if output is an array of numbers (JSON format)
        if (preg_match('/^\[.*\]$/', $normalized)) {
            $decoded = json_decode($normalized, true);
            if (is_array($decoded)) {
                $rounded = array_map(function($val) use ($precision) {
                    return is_numeric($val) ? round($val, $precision) : $val;
                }, $decoded);
                return json_encode($rounded);
            }
        }
        
        return $normalized;
    }

    /**
     * Normalize boolean outputs (true/false, 1/0, True/False).
     * 
     * @param string $output
     * @return string
     */
    public static function normalizeBoolean($output)
    {
        $normalized = self::normalize($output);
        $lower = strtolower($normalized);
        
        // Convert various boolean representations to lowercase true/false
        if (in_array($lower, ['true', '1', 'yes'])) {
            return 'true';
        }
        
        if (in_array($lower, ['false', '0', 'no'])) {
            return 'false';
        }
        
        return $normalized;
    }

    /**
     * Normalize array/JSON outputs.
     * Handles inconsistent spacing in JSON output.
     * 
     * @param string $output
     * @return string
     */
    public static function normalizeJson($output)
    {
        $normalized = self::normalize($output);
        
        // Try to decode and re-encode for consistent formatting
        $decoded = json_decode($normalized, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Re-encode with consistent formatting (no spaces after commas/colons)
            return json_encode($decoded, JSON_UNESCAPED_SLASHES);
        }
        
        return $normalized;
    }

    /**
     * Smart comparison that detects output type and applies appropriate normalization.
     * 
     * @param string $userOutput
     * @param string $expectedOutput
     * @return array
     */
    public static function smartCompare($userOutput, $expectedOutput)
    {
        // First try standard comparison
        $result = self::compare($userOutput, $expectedOutput);
        
        if ($result['match']) {
            return $result;
        }

        // Try boolean normalization
        $normalizedUserBool = self::normalizeBoolean($userOutput);
        $normalizedExpectedBool = self::normalizeBoolean($expectedOutput);
        
        if ($normalizedUserBool === $normalizedExpectedBool && 
            in_array($normalizedUserBool, ['true', 'false'])) {
            return [
                'status' => 'accepted',
                'match' => true,
                'message' => 'Output matches (boolean formatting normalized)'
            ];
        }

        // Try JSON normalization
        $normalizedUserJson = self::normalizeJson($userOutput);
        $normalizedExpectedJson = self::normalizeJson($expectedOutput);
        
        if ($normalizedUserJson === $normalizedExpectedJson) {
            return [
                'status' => 'accepted',
                'match' => true,
                'message' => 'Output matches (JSON formatting normalized)'
            ];
        }

        // Try numeric normalization
        $normalizedUserNum = self::normalizeNumeric($userOutput);
        $normalizedExpectedNum = self::normalizeNumeric($expectedOutput);
        
        if ($normalizedUserNum === $normalizedExpectedNum) {
            return [
                'status' => 'accepted',
                'match' => true,
                'message' => 'Output matches (numeric precision normalized)'
            ];
        }

        // No match found - return original comparison result
        return $result;
    }
}
