<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AICodeFeedbackService
{
    /**
     * Generate AI feedback for student code submission
     * 
     * @param string $code The student's submitted code
     * @param string $language Programming language used
     * @param string $questionTitle The question/problem title
     * @param array $testResults Array of test results with pass/fail status
     * @return array Structured feedback with sections
     */
    public function generateFeedback(string $code, string $language, string $questionTitle, array $testResults): array
    {
        $apiKey = config('services.gemini.api_key');
        
        if (!$apiKey) {
            Log::warning('Gemini API key not configured');
            return $this->getFallbackFeedback();
        }
        
        try {
            // Calculate test case statistics
            $totalTests = count($testResults);
            $passedTests = collect($testResults)->where('passed', true)->count();
            $failedTests = $totalTests - $passedTests;
            
            // Build the prompt
            $prompt = $this->buildPrompt($code, $language, $questionTitle, $testResults, $passedTests, $totalTests);
            
            // Call Gemini API
            $response = Http::timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1000,
                    ]
                ]);
            
            if ($response->successful()) {
                $responseData = $response->json();
                $feedbackText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                // Parse the feedback into structured sections
                return $this->parseFeedback($feedbackText);
            } else {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return $this->getFallbackFeedback();
            }
            
        } catch (\Exception $e) {
            Log::error('AI Feedback generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getFallbackFeedback();
        }
    }
    
    /**
     * Build the prompt for AI analysis
     */
    private function buildPrompt(string $code, string $language, string $questionTitle, array $testResults, int $passedTests, int $totalTests): string
    {
        $testResultsSummary = $this->formatTestResultsSummary($testResults);
        
        return <<<PROMPT
You are an expert coding instructor. A student has submitted the following code in response to a coding question. Your task is to:

**Check Correctness:**
- Analyze if the code solves the problem as intended.
- Highlight any logical or runtime errors.

**Evaluate Style and Readability:**
- Assess variable/function naming, indentation, comments, and overall clarity.
- Suggest improvements for better readability and maintainability.

**Error Pattern Analysis:**
- Identify any recurring types of mistakes (e.g., off-by-one errors, misuse of loops, incorrect conditionals).
- Point out patterns if the same mistakes occur multiple times.

**Provide Feedback and Suggestions:**
- Give actionable advice for correcting errors.
- Suggest ways to avoid similar mistakes in future coding exercises.

**Question:** {$questionTitle}
**Language:** {$language}
**Test Results:** {$passedTests}/{$totalTests} test cases passed

**Test Case Results:**
{$testResultsSummary}

**Student Code:**
```{$language}
{$code}
```

Provide your feedback in a clear, structured format with these exact section headers:
## Correctness
## Style & Readability
## Error Analysis
## Suggestions
PROMPT;
    }
    
    /**
     * Format test results summary for the prompt
     */
    private function formatTestResultsSummary(array $testResults): string
    {
        $summary = [];
        foreach ($testResults as $test) {
            $status = $test['passed'] ? '✓ PASSED' : '✗ FAILED';
            $summary[] = "Test Case {$test['test_number']}: {$status}";
            if (!$test['passed']) {
                $summary[] = "  Expected: {$test['expected']}";
                $summary[] = "  Got: {$test['actual']}";
            }
        }
        return implode("\n", $summary);
    }
    
    /**
     * Parse AI feedback into structured sections
     */
    private function parseFeedback(string $feedbackText): array
    {
        $sections = [
            'correctness' => '',
            'style' => '',
            'errors' => '',
            'suggestions' => ''
        ];
        
        // Try to parse sections using headers
        $patterns = [
            'correctness' => '/##\s*Correctness\s*\n(.*?)(?=##|$)/si',
            'style' => '/##\s*Style\s*&?\s*Readability\s*\n(.*?)(?=##|$)/si',
            'errors' => '/##\s*Error\s*Analysis\s*\n(.*?)(?=##|$)/si',
            'suggestions' => '/##\s*Suggestions\s*\n(.*?)(?=##|$)/si',
        ];
        
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $feedbackText, $matches)) {
                $sections[$key] = trim($matches[1]);
            }
        }
        
        // If parsing failed, return full text in suggestions
        if (empty($sections['correctness']) && empty($sections['style']) && empty($sections['errors']) && empty($sections['suggestions'])) {
            $sections['suggestions'] = $feedbackText;
        }
        
        return $sections;
    }
    
    /**
     * Get fallback feedback when AI is not available
     */
    private function getFallbackFeedback(): array
    {
        return [
            'correctness' => 'Your code has been evaluated against the test cases. Review the failed tests to understand where your solution differs from expected outputs.',
            'style' => 'Consider using clear variable names, proper indentation, and adding comments to explain complex logic.',
            'errors' => 'Common issues include: boundary conditions, data type mismatches, and incorrect algorithm implementation. Review the test cases that failed.',
            'suggestions' => 'Practice similar problems, review the solution carefully, and test edge cases. Consider discussing your approach with peers or reviewing online resources for the topic.'
        ];
    }
}
