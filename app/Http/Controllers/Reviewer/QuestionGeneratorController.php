<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class QuestionGeneratorController extends Controller
{
    public function showGeneratePage()
    {
        // Get the authenticated reviewer
        $reviewer = auth('reviewer')->user();
        
        // Get distinct languages from questions table
        $languages = Question::select('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language')
            ->toArray();

        // If no languages in database, provide defaults
        if (empty($languages)) {
            $languages = ['Python', 'JavaScript', 'Java', 'C++', 'C#'];
        }

        return view('reviewer.generate', compact('languages', 'reviewer'));
    }

    public function generateQuestion(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'language' => 'required|string',
            'difficulty' => 'required|string',
        ]);

        try {
            $geminiApiKey = env('GEMINI_API_KEY');
            
            if (!$geminiApiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini API key not configured. Please add GEMINI_API_KEY to your .env file.'
                ], 500);
            }

            // Build the prompt for Gemini
            $systemPrompt = $this->buildPrompt($request->all());

            // Retry logic with exponential backoff
            $maxRetries = 3;
            $retryDelay = 2; // seconds
            $lastError = null;

            for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
                try {
                    if ($attempt > 0) {
                        // Wait before retrying (exponential backoff)
                        $waitTime = $retryDelay * pow(2, $attempt - 1);
                        Log::info("Retrying request after {$waitTime} seconds (attempt {$attempt})");
                        sleep($waitTime);
                    }

                    // Call Gemini API with correct model name
                    $response = Http::timeout(60)->withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $systemPrompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 8192,  // Increased to allow full solution code
                        ]
                    ]);

                    // Log the response
                    Log::info('Gemini API Response Status: ' . $response->status());

                    // Handle rate limiting (429)
                    if ($response->status() === 429) {
                        $lastError = 'Rate limit exceeded. The API is receiving too many requests.';
                        Log::warning("Rate limit hit on attempt {$attempt}");
                        
                        // If this is the last attempt, return a user-friendly error
                        if ($attempt === $maxRetries - 1) {
                            return response()->json([
                                'success' => false,
                                'message' => 'The AI service is currently experiencing high demand. Please try again in a few minutes.',
                                'error_type' => 'rate_limit'
                            ], 429);
                        }
                        continue; // Retry
                    }

                    if ($response->failed()) {
                        Log::error('Gemini API Error', ['response' => $response->body()]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to generate question. API Error: ' . $response->status()
                        ], 500);
                    }

                    $result = $response->json();
                    
                    // Check if response has the expected structure
                    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                        Log::error('Invalid Gemini Response Structure', [
                            'response' => $result,
                            'has_candidates' => isset($result['candidates']),
                            'candidates_count' => isset($result['candidates']) ? count($result['candidates']) : 0
                        ]);
                        
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid response from AI service. The response structure was unexpected.'
                        ], 500);
                    }

                    $generatedText = $result['candidates'][0]['content']['parts'][0]['text'];
                    
                    // Parse the generated content
                    $parsedQuestion = $this->parseGeneratedQuestion($generatedText);

                    return response()->json([
                        'success' => true,
                        'data' => $parsedQuestion
                    ]);

                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    $lastError = $e->getMessage();
                    Log::warning("Connection error on attempt {$attempt}: {$lastError}");
                    if ($attempt === $maxRetries - 1) {
                        throw $e;
                    }
                    continue; // Retry
                }
            }

            // If we exhausted all retries
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate question after multiple attempts. Please try again later.',
                'error' => $lastError
            ], 500);

        } catch (\Exception $e) {
            Log::error('Question Generation Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveQuestion(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'problemStatement' => 'required|string',
            'constraints' => 'required|array',
            'expectedApproach' => 'required|string',
            'tests' => 'required|array',
            'solution' => 'required|string',
            'language' => 'required|string',
            'difficulty' => 'required|string',
            'topic' => 'required|string',
        ]);

        try {
            $reviewer = auth('reviewer')->user();

            // Prepare constraints as a string
            $constraintsText = '';
            foreach ($request->constraints as $constraint) {
                $constraintsText .= "- {$constraint}\n";
            }

            // Prepare input test cases as array (proper structure for database)
            $inputData = [];
            foreach ($request->tests as $index => $test) {
                $inputData[] = [
                    'test_case' => $index + 1,
                    'input' => $test['input']
                ];
            }

            // Prepare expected output as array (proper structure for database)
            $expectedOutputData = [];
            foreach ($request->tests as $index => $test) {
                $expectedOutputData[] = [
                    'test_case' => $index + 1,
                    'output' => $test['output']
                ];
            }

            // Legacy content field for backward compatibility
            $content = "# {$request->title}\n\n";
            $content .= "## Description\n{$request->description}\n\n";
            $content .= "## Problem Statement\n{$request->problemStatement}\n\n";
            $content .= "## Constraints\n" . $constraintsText;
            $content .= "\n## Hints\n{$request->expectedApproach}";

            // Map difficulty to level (handle both formats)
            $levelMap = [
                'easy' => 'beginner',
                'beginner' => 'beginner',
                'intermediate' => 'intermediate',
                'hard' => 'advanced',
                'advanced' => 'advanced'
            ];

            $level = strtolower($request->difficulty);
            $mappedLevel = $levelMap[$level] ?? 'intermediate';

            $question = Question::create([
                'title' => $request->title,
                'content' => $content, // Legacy field
                'description' => $request->description,
                'problem_statement' => $request->problemStatement,
                'constraints' => trim($constraintsText),
                'expected_output' => $expectedOutputData, // Now stored as array
                'answersData' => $request->solution,
                'status' => 'Pending',
                'reviewer_ID' => $reviewer->reviewer_ID,
                'language' => $request->language,
                'level' => $mappedLevel,
                'questionCategory' => 'learnerPractice',
                'questionType' => 'Code_Solution',
                'chapter' => $request->topic,
                'hint' => $request->expectedApproach,
                'input' => $inputData, // Stored as array with proper structure
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Question saved successfully!',
                'question_id' => $question->question_ID
            ]);

        } catch (\Exception $e) {
            Log::error('Save Question Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save question: ' . $e->getMessage()
            ], 500);
        }
    }

    private function buildPrompt(array $params)
    {
        $prompt  = "You are an expert programming instructor who creates real-world, single-file coding interview questions.\n\n";
        $prompt .= "Generate a simple real-world coding question suitable for a technical interview.\n\n";

        $prompt .= "Difficulty: {$params['difficulty']}\n";
        $prompt .= "Language: {$params['language']}\n";
        $prompt .= "User Request: {$params['prompt']}\n\n";

        $prompt .= "Use the following structure EXACTLY:\n\n";

        $prompt .= "TITLE:\n";
        $prompt .= "[Generate a clear, concise, and descriptive title for this coding question. The title should be specific and indicate what the problem is about. Examples: 'Find Maximum Subarray Sum', 'Implement Binary Search Tree', 'Validate Parentheses']\n\n";

        $prompt .= "LANGUAGE:\n";
        $prompt .= "[Use: {$params['language']}]\n\n";

        $prompt .= "TOPIC:\n";
        $prompt .= "[Generate ONE specific programming concept/category that best describes this question. Choose from concepts like: Array, String, Loop, Sorting, Searching, Recursion, Dynamic Programming, Hash Table, Stack, Queue, Linked List, Tree, Graph, Greedy Algorithm, Backtracking, Bit Manipulation, Math, Two Pointers, Sliding Window, Divide and Conquer, or other relevant programming concepts. Use Title Case (e.g., 'Hash Table', 'Dynamic Programming')]\n\n";

        $prompt .= "DIFFICULTY:\n{$params['difficulty']}\n\n";

        $prompt .= "DESCRIPTION:\n";
        $prompt .= "[Write a brief 1-2 sentence overview of the problem and its real-world context]\n\n";

        $prompt .= "PROBLEM_STATEMENT:\n";
        $prompt .= "[Provide a detailed technical specification of what needs to be implemented. Include the business scenario, what the function/program should do, and any important details.]\n\n";

        $prompt .= "CONSTRAINTS:\n";
        $prompt .= "- Input parameters:\n";
        $prompt .= "  [List each parameter]\n";
        $prompt .= "- Output:\n";
        $prompt .= "  [Expected output]\n";
        $prompt .= "- Rules:\n";
        $prompt .= "  [Validation or rules]\n";
        $prompt .= "- Edge cases:\n";
        $prompt .= "  [List at least 2 edge cases]\n\n";

        $prompt .= "HINTS:\n";
        $prompt .= "1. [Hint 1]\n";
        $prompt .= "2. [Hint 2]\n";
        $prompt .= "3. [Hint 3]\n\n";

        $prompt .= "INPUT:\n";
        $prompt .= "Provide exactly 4 test cases with clear, specific input values.\n";
        $prompt .= "Test Case 1 (Normal):\n";
        $prompt .= "Input: [Provide actual input values, be specific]\n\n";
        $prompt .= "Test Case 2 (Failure/Invalid):\n";
        $prompt .= "Input: [Provide actual input values, be specific]\n\n";
        $prompt .= "Test Case 3 (Edge Case):\n";
        $prompt .= "Input: [Provide actual input values, be specific]\n\n";
        $prompt .= "Test Case 4 (Complex/Mixed):\n";
        $prompt .= "Input: [Provide actual input values, be specific]\n\n";

        $prompt .= "EXPECTED_OUTPUT:\n";
        $prompt .= "Provide the exact expected output for each test case above.\n";
        $prompt .= "Test Case 1:\n";
        $prompt .= "Output: [Provide exact output]\n\n";
        $prompt .= "Test Case 2:\n";
        $prompt .= "Output: [Provide exact output]\n\n";
        $prompt .= "Test Case 3:\n";
        $prompt .= "Output: [Provide exact output]\n\n";
        $prompt .= "Test Case 4:\n";
        $prompt .= "Output: [Provide exact output]\n\n";

        $prompt .= "SOLUTION:\n";
        $prompt .= "[Provide a complete, correct, working solution in {$params['language']}. Must fit inside ONE FILE only. No external libraries unless built-in.]\n\n";

        $prompt .= "IMPORTANT:\n";
        $prompt .= "• The question must be solvable in ONE FILE only.\n";
        $prompt .= "• Make the solution clean and easy for students to understand.\n";
        $prompt .= "• Ensure the question is real-world, practical, and consistent with the topic.\n";
        $prompt .= "• The TOPIC must be a specific programming concept/data structure, not a general application domain.\n";
        $prompt .= "• Provide SPECIFIC input values and their corresponding outputs, not placeholders.\n";

        return $prompt;
    }

    private function parseGeneratedQuestion(string $text)
    {
        // Log the raw response for debugging
        Log::info('Raw AI Response:', ['text' => $text]);
        
        $question = [
            'title' => '',
            'description' => '',
            'problemStatement' => '',
            'constraints' => [],
            'expectedApproach' => '',
            'tests' => [],
            'solution' => '',
            'topic' => ''
        ];

        // Extract title directly from the response
        if (preg_match('/TITLE:\s*(.+?)(?=LANGUAGE:|$)/s', $text, $matches)) {
            $question['title'] = trim($matches[1]);
            Log::info('Parsed title:', ['title' => $question['title']]);
        }

        // Extract language
        $language = '';
        if (preg_match('/LANGUAGE:\s*(.+?)(?:\n|$)/s', $text, $matches)) {
            $language = trim($matches[1]);
        }

        // Extract topic
        if (preg_match('/TOPIC:\s*(.+?)(?=DIFFICULTY:|$)/s', $text, $matches)) {
            $topic = trim($matches[1]);
            $question['topic'] = $topic;
            Log::info('Parsed topic:', ['topic' => $question['topic']]);
        }

        // Extract description (brief overview)
        if (preg_match('/DESCRIPTION:\s*(.+?)(?=PROBLEM_STATEMENT:|$)/s', $text, $matches)) {
            $question['description'] = trim($matches[1]);
            Log::info('Parsed description:', ['description' => $question['description']]);
        }

        // Extract problem statement (detailed specification)
        if (preg_match('/PROBLEM_STATEMENT:\s*(.+?)(?=CONSTRAINTS:|$)/s', $text, $matches)) {
            $question['problemStatement'] = trim($matches[1]);
            Log::info('Parsed problem statement:', ['length' => strlen($question['problemStatement'])]);
        }

        // Extract constraints
        if (preg_match('/CONSTRAINTS:\s*(.+?)(?=HINTS:|$)/s', $text, $matches)) {
            $constraintsText = trim($matches[1]);
            $lines = explode("\n", $constraintsText);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && $line !== '-' && !preg_match('/^(Input parameters|Output|Rules|Edge cases):$/i', $line)) {
                    // Remove leading dash or bullet
                    $line = preg_replace('/^[-•]\s*/', '', $line);
                    if (!empty($line)) {
                        $question['constraints'][] = $line;
                    }
                }
            }
            Log::info('Parsed constraints:', ['count' => count($question['constraints'])]);
        }

        // Extract hints (this becomes expected approach)
        if (preg_match('/HINTS:\s*(.+?)(?=INPUT:|$)/s', $text, $matches)) {
            $hintsText = trim($matches[1]);
            $question['expectedApproach'] = $hintsText;
            Log::info('Parsed hints:', ['length' => strlen($question['expectedApproach'])]);
        }

        // Extract input test cases - improved parsing with multiple pattern attempts
        $inputSection = '';
        if (preg_match('/INPUT:\s*(.+?)(?=EXPECTED_OUTPUT:|$)/s', $text, $matches)) {
            $inputSection = $matches[1];
            Log::info('Found INPUT section:', ['length' => strlen($inputSection)]);
            
            // Try multiple patterns to match test cases
            $patterns = [
                '/Test Case (\d+)[^:]*:\s*Input:\s*(.+?)(?=Test Case \d+|$)/s',
                '/Test Case (\d+)[^:]*:\s*(.+?)(?=Test Case \d+|$)/s',
                '/Test (\d+)[^:]*:\s*Input:\s*(.+?)(?=Test \d+|$)/s',
                '/Test (\d+)[^:]*:\s*(.+?)(?=Test \d+|$)/s',
            ];
            
            $matched = false;
            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $inputSection, $inputMatches, PREG_SET_ORDER)) {
                    if (!empty($inputMatches)) {
                        Log::info('Matched input pattern:', ['pattern' => $pattern, 'count' => count($inputMatches)]);
                        foreach ($inputMatches as $match) {
                            $testCaseNum = (int)$match[1];
                            $inputValue = trim($match[2]);
                            // Remove "Input:" label if it appears at the start
                            $inputValue = preg_replace('/^Input:\s*/i', '', $inputValue);
                            
                            $question['tests'][$testCaseNum - 1] = [
                                'input' => trim($inputValue),
                                'output' => '',
                                'explanation' => ''
                            ];
                        }
                        $matched = true;
                        break;
                    }
                }
            }
            
            if (!$matched) {
                Log::warning('No input pattern matched. Input section:', ['section' => substr($inputSection, 0, 500)]);
            }
        } else {
            Log::warning('INPUT section not found in response');
        }

        // Extract expected output - improved parsing with multiple pattern attempts
        $outputSection = '';
        if (preg_match('/EXPECTED_OUTPUT:\s*(.+?)(?=SOLUTION:|$)/s', $text, $matches)) {
            $outputSection = $matches[1];
            Log::info('Found EXPECTED_OUTPUT section:', ['length' => strlen($outputSection)]);
            
            // Try multiple patterns to match outputs
            $patterns = [
                '/Test Case (\d+)[^:]*:\s*Output:\s*(.+?)(?=Test Case \d+|$)/s',
                '/Test Case (\d+)[^:]*:\s*(.+?)(?=Test Case \d+|$)/s',
                '/Test (\d+)[^:]*:\s*Output:\s*(.+?)(?=Test \d+|$)/s',
                '/Test (\d+)[^:]*:\s*(.+?)(?=Test \d+|$)/s',
            ];
            
            $matched = false;
            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $outputSection, $outputMatches, PREG_SET_ORDER)) {
                    if (!empty($outputMatches)) {
                        Log::info('Matched output pattern:', ['pattern' => $pattern, 'count' => count($outputMatches)]);
                        foreach ($outputMatches as $match) {
                            $testCaseNum = (int)$match[1];
                            $outputValue = trim($match[2]);
                            // Remove "Output:" label if it appears at the start
                            $outputValue = preg_replace('/^Output:\s*/i', '', $outputValue);
                            
                            if (isset($question['tests'][$testCaseNum - 1])) {
                                $question['tests'][$testCaseNum - 1]['output'] = trim($outputValue);
                            }
                        }
                        $matched = true;
                        break;
                    }
                }
            }
            
            if (!$matched) {
                Log::warning('No output pattern matched. Output section:', ['section' => substr($outputSection, 0, 500)]);
            }
        } else {
            Log::warning('EXPECTED_OUTPUT section not found in response');
        }

        // Extract solution - try multiple patterns
        $solutionPatterns = [
            '/SOLUTION:\s*```[\w]*\s*(.+?)```/s',
            '/SOLUTION:\s*```(.+?)```/s',
            '/SOLUTION:\s*(.+?)(?=IMPORTANT:|$)/s',
        ];
        
        $solutionFound = false;
        foreach ($solutionPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $solutionText = trim($matches[1]);
                // Remove code block markers if present
                $solutionText = preg_replace('/```[\w]*\s*/', '', $solutionText);
                $solutionText = preg_replace('/```\s*$/', '', $solutionText);
                $question['solution'] = trim($solutionText);
                
                if (!empty($question['solution'])) {
                    Log::info('Parsed solution:', ['length' => strlen($question['solution'])]);
                    $solutionFound = true;
                    break;
                }
            }
        }
        
        if (!$solutionFound) {
            Log::warning('Solution not found in response');
        }

        // Log summary before fallbacks
        Log::info('Parsing summary before fallbacks:', [
            'has_title' => !empty($question['title']),
            'has_description' => !empty($question['description']),
            'has_problem_statement' => !empty($question['problemStatement']),
            'constraints_count' => count($question['constraints']),
            'has_expected_approach' => !empty($question['expectedApproach']),
            'tests_count' => count($question['tests']),
            'has_solution' => !empty($question['solution']),
        ]);

        // Fallback values if parsing failed
        if (empty($question['title'])) {
            $question['title'] = 'Generated Coding Question';
            Log::warning('Using fallback title');
        }
        if (empty($question['description'])) {
            $question['description'] = 'Please solve the following coding problem.';
            Log::warning('Using fallback description');
        }
        if (empty($question['problemStatement'])) {
            $question['problemStatement'] = $question['description'];
            Log::warning('Using fallback problem statement');
        }
        if (empty($question['constraints'])) {
            $question['constraints'] = ['Input size: 1 ≤ n ≤ 10^3', 'Values: -10^9 ≤ value ≤ 10^9', 'Time complexity requirement: O(n) or better'];
            Log::warning('Using fallback constraints');
        }
        if (empty($question['expectedApproach'])) {
            $question['expectedApproach'] = 'Follow the requirements and hints provided in the problem description.';
            Log::warning('Using fallback expected approach');
        }
        if (empty($question['tests'])) {
            $question['tests'][] = [
                'input' => 'Example input',
                'output' => 'Example output',
                'explanation' => ''
            ];
            Log::warning('Using fallback tests');
        }
        if (empty($question['solution'])) {
            $question['solution'] = '// Solution will be generated';
            Log::warning('Using fallback solution');
        }

        return $question;
    }
}
