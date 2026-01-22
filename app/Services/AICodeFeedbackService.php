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
        $geminiApiKey = env('GEMINI_API_KEY');
        
        Log::info('=== AI FEEDBACK GENERATION STARTED ===', [
            'api_key_exists' => !empty($geminiApiKey),
            'api_key_length' => strlen($geminiApiKey ?? ''),
            'language' => $language,
            'question' => $questionTitle
        ]);
        
        if (!$geminiApiKey) {
            Log::warning('Gemini API key not configured');
            return $this->getFallbackFeedback();
        }
        
        try {
            // Calculate test case statistics
            $totalTests = count($testResults);
            $passedTests = collect($testResults)->where('passed', true)->count();
            $failedTests = $totalTests - $passedTests;
            
            // Get list of failed test numbers for better context
            $failedTestNumbers = collect($testResults)
                ->filter(fn($test) => !$test['passed'])
                ->pluck('test_number')
                ->implode(', ');
            
            Log::info('Test results summary', [
                'total' => $totalTests,
                'passed' => $passedTests,
                'failed' => $failedTests,
                'failed_test_numbers' => $failedTestNumbers
            ]);
            
            // Build the prompt
            $prompt = $this->buildPrompt($code, $language, $questionTitle, $testResults, $passedTests, $totalTests, $failedTestNumbers);
            
            Log::info('Calling Gemini API...', ['prompt_length' => strlen($prompt)]);
            
            // Call Gemini API using the same pattern as monthly generation
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
                        'maxOutputTokens' => 2048,
                    ]
                ]);
            
            Log::info('Gemini API Response received', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);
            
            // Check for rate limit error (429 Too Many Requests)
            if ($response->status() === 429) {
                Log::warning('Gemini API rate limit exceeded');
                return $this->getRateLimitFeedback();
            }
            
            if ($response->successful()) {
                $responseData = $response->json();
                $feedbackText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                Log::info('Gemini response parsed', [
                    'has_feedback' => !empty($feedbackText),
                    'feedback_length' => strlen($feedbackText),
                    'full_response' => $feedbackText  // Log full response for debugging
                ]);
                
                // Parse the feedback into structured sections
                $parsed = $this->parseFeedback($feedbackText);
                
                Log::info('Feedback parsed into sections', [
                    'correctness_length' => strlen($parsed['correctness'] ?? ''),
                    'style_length' => strlen($parsed['style'] ?? ''),
                    'errors_length' => strlen($parsed['errors'] ?? ''),
                    'suggestions_length' => strlen($parsed['suggestions'] ?? '')
                ]);
                
                return $parsed;
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
    

    private function buildPrompt(string $code, string $language, string $questionTitle, array $testResults, int $passedTests, int $totalTests, string $failedTestNumbers): string
    {
        $testResultsSummary = $this->formatTestResultsSummary($testResults);
        
        $failedTestsNote = !empty($failedTestNumbers) ? "\nFailed test cases: {$failedTestNumbers}" : "";
        
        return <<<PROMPT
You are a university lecturer reviewing a student's code submission. Provide specific, personalized feedback.

**CRITICAL RULES:**
1. Be BRIEF and CONCISE - keep each section to 2-4 sentences maximum
2. Write in a sincere, encouraging tone - like a mentor, not a formal document
3. Every point MUST reference this specific student's code (variable names, logic choices, structure)
4. Do NOT write greetings, conclusions, or numbered lists - just clear observations
5. You MUST provide ALL 4 sections below

---

**Submission Context:**
- Question: {$questionTitle}
- Language: {$language}
- Test Results: {$passedTests}/{$totalTests} passed{$failedTestsNote}

**Test Details:**
{$testResultsSummary}

**Student's Code:**
```{$language}
{$code}
```

---

**Output Format (REQUIRED - keep each section brief, 1 sentence):**

## Correctness
[In 1 sentence: Does their logic work? If tests failed, explain why based on their specific code. If passed, acknowledge what they did right. Reference their actual code.]

## Style & Readability
[In 1 sentence: Comment on their variable names, code structure, and readability. Be specific to what you see.]

## Error Analysis
[In 1 sentence: What pattern do you notice in how they approached this? What assumption or mistake did they make? Keep it focused.]

## Suggestions
[In 1 sentence: Give 1-2 concrete improvements they should make. Be practical and specific to their code.]
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
        // Initialize sections as null to detect missing ones
        $sections = [
            'correctness' => null,
            'style' => null,
            'errors' => null,
            'suggestions' => null
        ];
        
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
        
        // Get fallback feedback
        $fallback = $this->getFallbackFeedback();
        
        // Use partial fallback: only fill in missing sections
        foreach ($sections as $key => $value) {
            if (empty($value)) {
                $sections[$key] = $fallback[$key];
            }
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

    /**
     * Get rate limit feedback when API quota is exceeded
     */
    private function getRateLimitFeedback(): array
    {
        return [
            'correctness' => 'Gemini API has too many requests. Unable to generate AI feedback at this time.',
            'style' => 'Gemini API has too many requests. Unable to generate AI feedback at this time.',
            'errors' => 'Gemini API has too many requests. Unable to generate AI feedback at this time.',
            'suggestions' => 'Gemini API has too many requests. Please try again later or contact support.',
            'rate_limited' => true  // Flag to indicate rate limiting
        ];
    }
}
