<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Question;
use App\Services\AchievementService;

class QuestionGeneratorController extends Controller
{
    public function showGeneratePage()
    {
        $reviewer = auth('reviewer')->user();
        
        $languages = Question::select('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language')
            ->toArray();

        if (empty($languages)) {
            $languages = ['Python', 'JavaScript', 'Java', 'C++', 'C#'];
        }

        return view('reviewer.generate', compact('languages', 'reviewer'));
    }

    public function generateQuestion(Request $request)
    {
        set_time_limit(120);
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
                    'message' => 'Gemini API key not configured.'
                ], 500);
            }

            $systemPrompt = $this->buildPrompt($request->all());

            // Increase token limit for advanced questions
            $maxTokens = match(strtolower($request->difficulty)) {
                'advanced' => 20480,      // Increased to 20K for complex solutions
                'intermediate' => 12288,  // Increased for moderate complexity
                default => 8192           // Standard for beginner
            };

            $maxRetries = 3;
            $retryDelay = 2;
            $lastError = null;

            for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
                try {
                    if ($attempt > 0) {
                        $waitTime = $retryDelay * pow(2, $attempt - 1);
                        sleep($waitTime);
                    }

                    $response = Http::timeout(90)->withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}", [
                        'contents' => [['parts' => [['text' => $systemPrompt]]]],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => $maxTokens,
                            'topP' => 0.95,
                            'topK' => 40
                        ]
                    ]);

                    if ($response->status() === 429) {
                        $lastError = 'Rate limit exceeded.';
                        if ($attempt === $maxRetries - 1) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Service busy. Please try again in a moment.'
                            ], 429);
                        }
                        continue;
                    }

                    if ($response->failed()) {
                        $errorBody = $response->body();
                        Log::error('Gemini API Error', [
                            'status' => $response->status(),
                            'body' => substr($errorBody, 0, 500),
                            'difficulty' => $request->difficulty
                        ]);
                        throw new \Exception('API Error: ' . $response->status());
                    }

                    $result = $response->json();
                    
                    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                        throw new \Exception('Invalid response structure from AI');
                    }

                    $generatedText = $result['candidates'][0]['content']['parts'][0]['text'];
                    
                    // Log raw response for debugging advanced questions
                    if (strtolower($request->difficulty) === 'advanced') {
                        Log::info('Advanced Question Raw Response', [
                            'sample' => substr($generatedText, 0, 300)
                        ]);
                    }
                    
                    // Parse using the robust parser with better error handling
                    $parsedQuestion = $this->parseGeneratedQuestion($generatedText);
                    
                    // Validate we have minimum required data
                    if (empty($parsedQuestion['title']) || empty($parsedQuestion['solution'])) {
                        throw new \Exception('Generated question missing required fields');
                    }

                    // Basic validation
                    if (isset($parsedQuestion['tests']) && is_array($parsedQuestion['tests'])) {
                        foreach ($parsedQuestion['tests'] as &$test) {
                            if (isset($test['input']) && (is_array($test['input']) || is_object($test['input']))) {
                                // Convert Array/Object to String for Display
                                $test['input'] = json_encode($test['input']);
                            }
                            if (isset($test['output']) && (is_array($test['output']) || is_object($test['output']))) {
                                $test['output'] = json_encode($test['output']);
                            }
                        }
                    }

                    return response()->json([
                        'success' => true,
                        'data' => $parsedQuestion
                    ]);

                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    Log::warning('Question Generation Attempt Failed', [
                        'attempt' => $attempt + 1,
                        'error' => $lastError,
                        'difficulty' => $request->difficulty
                    ]);
                    if ($attempt === $maxRetries - 1) throw $e;
                }
            }

        } catch (\Exception $e) {
            Log::error('Question Generation Error', [
                'message' => $e->getMessage(),
                'difficulty' => $request->difficulty ?? 'unknown',
                'language' => $request->language ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate question. Please try again or adjust your prompt.'
            ], 500);
        }
    }

    public function saveQuestion(Request $request)
    {
        // Enhanced error logging
        Log::info('Save Question Request', [
            'data' => $request->all(),
            'is_authenticated' => auth('reviewer')->check(),
            'user_id' => auth('reviewer')->id()
        ]);

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
            'function_name' => 'nullable|string',
            'return_type' => 'nullable|string',
        ]);

        try {
            $reviewer = auth('reviewer')->user();
            
            // Check if reviewer is authenticated
            if (!$reviewer) {
                Log::error('Save Question Error: Reviewer not authenticated');
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in as a reviewer to save questions.'
                ], 401);
            }

            $constraintsText = '';
            foreach ($request->constraints as $constraint) {
                $constraintsText .= "- {$constraint}\n";
            }

            // Prepare input test cases (Key-Value Array Structure)
            $inputData = [];
            $expectedOutputData = [];

            foreach ($request->tests as $index => $test) {
                // Validate test structure
                if (!isset($test['input']) || !isset($test['output'])) {
                    Log::error('Invalid test structure', ['test' => $test, 'index' => $index]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid test case format at index ' . $index
                    ], 400);
                }

                // Check if input is a JSON string (from our fix) or an array
                $inputVal = $test['input'];
                
                if (is_string($inputVal)) {
                    $decoded = json_decode($inputVal, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $inputVal = $decoded;
                    }
                }

                $inputData[] = [
                    'test_case' => $index + 1,
                    'input' => $inputVal
                ];

                $expectedOutputData[] = [
                    'test_case' => $index + 1,
                    'output' => $test['output']
                ];
            }

            $content = "# {$request->title}\n\n";
            $content .= "## Description\n{$request->description}\n\n";
            $content .= "## Problem Statement\n{$request->problemStatement}\n\n";
            $content .= "## Constraints\n" . $constraintsText;
            $content .= "\n## Hints\n{$request->expectedApproach}";

            $levelMap = ['easy' => 'beginner', 'beginner' => 'beginner', 'intermediate' => 'intermediate', 'hard' => 'advanced', 'advanced' => 'advanced'];
            $level = strtolower($request->difficulty);
            $mappedLevel = $levelMap[$level] ?? 'intermediate';

            // Use the parsed function name and return type, or fallback
            $functionName = $request->input('function_name', 'solve');
            $returnType = $request->input('return_type', 'void');

            Log::info('Attempting to create question', [
                'title' => $request->title,
                'reviewer_id' => $reviewer->reviewer_ID,
                'language' => $request->language,
                'level' => $mappedLevel
            ]);

            $question = Question::create([
                'title' => $request->title,
                'function_name' => $functionName,
                'return_type' => $returnType,
                'content' => $content,
                'description' => $request->description,
                'problem_statement' => $request->problemStatement,
                'constraints' => trim($constraintsText),
                'expected_output' => $expectedOutputData,
                'answersData' => $request->solution,
                'status' => 'Pending',
                'reviewer_ID' => $reviewer->reviewer_ID,
                'language' => $request->language,
                'level' => $mappedLevel,
                'questionCategory' => 'learnerPractice',
                'questionType' => 'Code_Solution',
                'chapter' => $request->topic,
                'hint' => $request->expectedApproach,
                'input' => $inputData,
            ]);

            Log::info('Question created successfully', ['question_id' => $question->question_ID]);

            // ============================================================
            // ACHIEVEMENT SYSTEM: Update Stats & Check Badges
            // ============================================================
            $reviewer->questions_generated_count++;
            $reviewer->save();
            
            // Check for earned badges
            $achievementService = app(AchievementService::class);
            $achievementService->checkReviewerBadges($reviewer);

            return response()->json([
                'success' => true,
                'message' => 'Question saved successfully!',
                'question_id' => $question->question_ID
            ]);

        } catch (\Exception $e) {
            Log::error('Save Question Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save question: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function buildPrompt(array $params)
    {
        $domains = ['FinTech', 'Healthcare', 'E-commerce', 'Logistics', 'Gaming', 'Social Media', 'EdTech', 'Real Estate'];
        $selectedDomain = $domains[array_rand($domains)];

        // Define difficulty-specific guidelines with STRICT line limits
        $difficultyGuidelines = [
            'beginner' => [
                'complexity' => 'Simple, straightforward logic with basic data structures (arrays, strings, basic loops)',
                'topics' => 'Basic Arrays, String Manipulation, Simple Math, Basic Loops',
                'solution_length' => 'MAXIMUM 10 lines of code (excluding function signature and closing brace)',
                'line_limit' => 'STRICTLY under 10 lines',
                'concepts' => 'Single loop, simple if-else, basic array/string operations',
                'time_complexity' => 'O(n) or O(n log n) maximum',
                'examples' => 'Find max value in array, reverse a string, count occurrences, simple validation',
                'no_advanced' => 'No recursion, no complex algorithms, no dynamic programming, no graph theory'
            ],
            'intermediate' => [
                'complexity' => 'Moderate complexity with common algorithms and data structures',
                'topics' => 'Hash Tables, Two Pointers, Sliding Window, Stack, Queue, Sorting, Binary Search',
                'solution_length' => 'MAXIMUM 20 lines of code (excluding function signature and closing brace)',
                'line_limit' => 'STRICTLY under 20 lines',
                'concepts' => 'Multiple loops, hash maps, two pointers, basic recursion, sorting algorithms',
                'time_complexity' => 'O(n log n) typical, O(n²) acceptable',
                'examples' => 'Two Sum with hash map, validate parentheses, merge intervals, find duplicates',
                'may_include' => 'Simple recursion, basic tree traversal, hash table optimization'
            ],
            'advanced' => [
                'complexity' => 'Complex algorithms requiring deep problem-solving skills',
                'topics' => 'Dynamic Programming, Graph Algorithms, Advanced Tree Problems, Backtracking, Trie, Segment Tree',
                'solution_length' => 'MAXIMUM 25 lines of code (excluding function signature and closing brace)',
                'line_limit' => 'STRICTLY under 25 lines',
                'concepts' => 'DP optimization, DFS/BFS, complex recursion, advanced data structures',
                'time_complexity' => 'Requires optimization beyond O(n²), often O(n log n) or better',
                'examples' => 'Longest increasing subsequence, shortest path in graph, optimize delivery routes, design LRU cache',
                'requires' => 'Advanced algorithmic thinking, optimization techniques, complex state management'
            ]
        ];

        $guide = $difficultyGuidelines[strtolower($params['difficulty'])] ?? $difficultyGuidelines['intermediate'];

        $prompt = "You are an expert coding challenge creator following LeetCode's question format.\n\n";
        $prompt .= "Create a '{$params['difficulty']}' level coding challenge for {$params['language']}.\n";
        $prompt .= "Domain: {$selectedDomain}\n";
        $prompt .= "User Requirement: {$params['prompt']}\n\n";

        $prompt .= "==== CRITICAL: SINGLE METHOD REQUIREMENT ====\n\n";
        $prompt .= "⚠️ MANDATORY: The solution must implement EXACTLY ONE METHOD/FUNCTION\n";
        $prompt .= "⚠️ MANDATORY: {$guide['line_limit']}\n";
        $prompt .= "⚠️ DO NOT create helper methods, classes, or multiple functions\n";
        $prompt .= "⚠️ ALL logic must fit inside ONE single method\n\n";

        $prompt .= "==== DIFFICULTY LEVEL: " . strtoupper($params['difficulty']) . " ====\n\n";
        $prompt .= "IMPORTANT: This is a {$params['difficulty']} level question. Follow these strict guidelines:\n\n";
        $prompt .= "Complexity: {$guide['complexity']}\n";
        $prompt .= "Suitable Topics: {$guide['topics']}\n";
        $prompt .= "Solution Length: {$guide['solution_length']}\n";
        $prompt .= "Key Concepts: {$guide['concepts']}\n";
        $prompt .= "Time Complexity: {$guide['time_complexity']}\n";
        $prompt .= "Example Problems: {$guide['examples']}\n";
        
        if (isset($guide['no_advanced'])) {
            $prompt .= "STRICTLY AVOID: {$guide['no_advanced']}\n";
        }
        if (isset($guide['may_include'])) {
            $prompt .= "May Include: {$guide['may_include']}\n";
        }
        if (isset($guide['requires'])) {
            $prompt .= "Requires: {$guide['requires']}\n";
        }
        $prompt .= "\n";

        $prompt .= "==== LEETCODE-STYLE FORMATTING RULES ====\n\n";
        
        $prompt .= "1. TITLE: Create a concise, action-oriented title matching the difficulty level\n";
        if ($params['difficulty'] === 'beginner') {
            $prompt .= "   Examples: 'Find Maximum Number', 'Reverse String', 'Count Vowels', 'Valid Palindrome'\n\n";
        } elseif ($params['difficulty'] === 'intermediate') {
            $prompt .= "   Examples: 'Two Sum', 'Valid Parentheses', 'Merge Intervals', 'Group Anagrams'\n\n";
        } else {
            $prompt .= "   Examples: 'Longest Increasing Subsequence', 'Word Ladder', 'Design LRU Cache', 'Optimize Delivery Routes'\n\n";
        }
        
        $prompt .= "2. DESCRIPTION: Write a brief real-world scenario (2-3 sentences) from {$selectedDomain} domain\n\n";
        
        $prompt .= "3. PROBLEM STATEMENT: Write clear, structured requirements appropriate for {$params['difficulty']} level:\n";
        $prompt .= "   - Start with 'Given...' or 'You are given...'\n";
        $prompt .= "   - Clearly state the input format (keep it simple for beginner)\n";
        $prompt .= "   - Clearly state what to return\n";
        $prompt .= "   - Must be solvable with ONE single method\n";
        if ($params['difficulty'] === 'beginner') {
            $prompt .= "   - Use simple, direct language - avoid complex terminology\n";
            $prompt .= "   - Focus on basic operations that anyone can understand\n";
        }
        $prompt .= "   - Include edge cases to consider\n\n";
        
        $prompt .= "4. EXAMPLES: Provide 2-3 test cases with:\n";
        $prompt .= "   - Input (simple values for beginner, more complex for advanced)\n";
        $prompt .= "   - Output (expected result)\n";
        $prompt .= "   - Explanation (clear and concise)\n\n";
        
        $prompt .= "5. CONSTRAINTS: List technical constraints appropriate for difficulty:\n";
        if ($params['difficulty'] === 'beginner') {
            $prompt .= "   - Small input sizes (e.g., '1 <= n <= 100' or '1 <= array.length <= 1000')\n";
            $prompt .= "   - Simple data types (integers, strings)\n";
            $prompt .= "   - Basic validation requirements\n\n";
        } elseif ($params['difficulty'] === 'intermediate') {
            $prompt .= "   - Moderate input sizes (e.g., '1 <= n <= 10^4')\n";
            $prompt .= "   - May include edge cases like negative numbers, duplicates\n";
            $prompt .= "   - Time/space complexity hints\n\n";
        } else {
            $prompt .= "   - Large input sizes (e.g., '1 <= n <= 10^5' or '1 <= n <= 10^6')\n";
            $prompt .= "   - Complex constraints requiring optimization\n";
            $prompt .= "   - Multiple constraint types\n\n";
        }
        
        $prompt .= "6. FUNCTION SIGNATURE: Use camelCase function names appropriate for the problem\n\n";
        
        $prompt .= "7. EXPECTED APPROACH: Provide numbered algorithmic steps:\n";
        if ($params['difficulty'] === 'beginner') {
            $prompt .= "   - 2-3 simple steps\n";
            $prompt .= "   - Focus on basic iteration and simple operations\n\n";
        } elseif ($params['difficulty'] === 'intermediate') {
            $prompt .= "   - 3-5 steps with moderate detail\n";
            $prompt .= "   - May include optimization hints\n\n";
        } else {
            $prompt .= "   - 4-6 detailed steps\n";
            $prompt .= "   - Include optimization strategy and complexity analysis\n\n";
        }
        
        $prompt .= "8. SOLUTION: Provide a complete, working implementation:\n";
        $prompt .= "   ⚠️ CRITICAL: EXACTLY ONE METHOD ONLY - {$guide['solution_length']}\n";
        $prompt .= "   ⚠️ NO helper methods, NO multiple functions\n";
        $prompt .= "   - Match the specified difficulty level\n";
        $prompt .= "   - Include minimal comments explaining key logic only\n";
        if ($params['difficulty'] === 'beginner') {
            $prompt .= "   - Use simple, readable code with basic constructs\n";
            $prompt .= "   - Avoid advanced language features\n";
            $prompt .= "   - Single straightforward approach under 10 lines\n\n";
        } elseif ($params['difficulty'] === 'intermediate') {
            $prompt .= "   - Show efficient algorithm implementation\n";
            $prompt .= "   - Keep it concise under 20 lines\n\n";
        } else {
            $prompt .= "   - Show optimized algorithm implementation\n";
            $prompt .= "   - Keep it concise under 25 lines\n\n";
        }
        
        $prompt .= "9. TOPIC: Categorize appropriately:\n";
        $prompt .= "   Suitable topics: {$guide['topics']}\n\n";
        
        $prompt .= "==== SINGLE METHOD EXAMPLES BY DIFFICULTY ====\n\n";
        
        if ($params['difficulty'] === 'beginner') {
            $prompt .= "BEGINNER Example (Under 10 lines):\n";
            $prompt .= "Title: 'Find Largest Number'\n";
            $prompt .= "Description: A school needs to find the student with the highest score from a list.\n";
            $prompt .= "Problem: Given an array of integers representing test scores, return the highest score.\n";
            $prompt .= "Solution (ONE method, 6 lines):\n";
            $prompt .= "function findLargest(scores) {\n";
            $prompt .= "    let max = scores[0];\n";
            $prompt .= "    for (let i = 1; i < scores.length; i++) {\n";
            $prompt .= "        if (scores[i] > max) max = scores[i];\n";
            $prompt .= "    }\n";
            $prompt .= "    return max;\n";
            $prompt .= "}\n\n";
        } elseif ($params['difficulty'] === 'intermediate') {
            $prompt .= "INTERMEDIATE Example (Under 20 lines):\n";
            $prompt .= "Title: 'Two Sum'\n";
            $prompt .= "Description: A payment system needs to find two transaction amounts that sum to a target.\n";
            $prompt .= "Problem: Given an array of numbers and a target, return indices of two numbers that sum to target.\n";
            $prompt .= "Solution (ONE method, 12 lines):\n";
            $prompt .= "function twoSum(nums, target) {\n";
            $prompt .= "    const map = new Map();\n";
            $prompt .= "    for (let i = 0; i < nums.length; i++) {\n";
            $prompt .= "        const complement = target - nums[i];\n";
            $prompt .= "        if (map.has(complement)) {\n";
            $prompt .= "            return [map.get(complement), i];\n";
            $prompt .= "        }\n";
            $prompt .= "        map.set(nums[i], i);\n";
            $prompt .= "    }\n";
            $prompt .= "    return [];\n";
            $prompt .= "}\n\n";
        } else {
            $prompt .= "ADVANCED Example (Under 25 lines):\n";
            $prompt .= "Title: 'Longest Increasing Subsequence'\n";
            $prompt .= "Description: A stock trading platform needs to find the longest period of consecutive price increases.\n";
            $prompt .= "Problem: Given an array of stock prices, find the length of the longest increasing subsequence.\n";
            $prompt .= "Solution (ONE method, 18 lines with DP):\n";
            $prompt .= "function lengthOfLIS(nums) {\n";
            $prompt .= "    if (!nums.length) return 0;\n";
            $prompt .= "    const dp = new Array(nums.length).fill(1);\n";
            $prompt .= "    let maxLen = 1;\n";
            $prompt .= "    for (let i = 1; i < nums.length; i++) {\n";
            $prompt .= "        for (let j = 0; j < i; j++) {\n";
            $prompt .= "            if (nums[i] > nums[j]) {\n";
            $prompt .= "                dp[i] = Math.max(dp[i], dp[j] + 1);\n";
            $prompt .= "            }\n";
            $prompt .= "        }\n";
            $prompt .= "        maxLen = Math.max(maxLen, dp[i]);\n";
            $prompt .= "    }\n";
            $prompt .= "    return maxLen;\n";
            $prompt .= "}\n\n";
        }
        
        $prompt .= "==== JSON OUTPUT FORMAT ====\n\n";
        $prompt .= "Return ONLY valid JSON (no markdown, no extra text):\n\n";
        $prompt .= "{\n";
        $prompt .= "  \"title\": \"Action-Oriented Title (match difficulty)\",\n";
        $prompt .= "  \"difficulty\": \"{$params['difficulty']}\",\n";
        $prompt .= "  \"description\": \"Brief real-world scenario (2-3 sentences) from {$selectedDomain}\",\n";
        $prompt .= "  \"problem_statement\": \"Clear problem description solvable with ONE single method.\",\n";
        $prompt .= "  \"constraints\": [\n";
        if ($params['difficulty'] === 'beginner') {
            $prompt .= "    \"1 <= array.length <= 100\",\n";
            $prompt .= "    \"Solution must be ONE method under 10 lines\"\n";
        } elseif ($params['difficulty'] === 'intermediate') {
            $prompt .= "    \"1 <= array.length <= 10^4\",\n";
            $prompt .= "    \"Solution must be ONE method under 20 lines\"\n";
        } else {
            $prompt .= "    \"1 <= n <= 10^5\",\n";
            $prompt .= "    \"Solution must be ONE method under 25 lines\"\n";
        }
        $prompt .= "  ],\n";
        $prompt .= "  \"function_name\": \"camelCaseFunctionName\",\n";
        $prompt .= "  \"return_type\": \"int|string|boolean|array|void\",\n";
        $prompt .= "  \"tests\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"input\": \"Simple, clear input format\",\n";
        $prompt .= "      \"output\": \"Expected result\",\n";
        $prompt .= "      \"explanation\": \"Why this output\"\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"expected_approach\": \"" . ($params['difficulty'] === 'beginner' ? '2-3 simple steps' : ($params['difficulty'] === 'intermediate' ? '3-5 moderate steps' : '4-6 detailed steps')) . "\",\n";
        $prompt .= "  \"solution\": \"ONE complete method only - {$guide['solution_length']}\",\n";
        $prompt .= "  \"topic\": \"Choose from: {$guide['topics']}\"\n";
        $prompt .= "}\n\n";
        
        $prompt .= "🚨 CRITICAL REMINDERS 🚨\n";
        $prompt .= "1. EXACTLY ONE METHOD - No helper functions, no classes, no additional methods\n";
        $prompt .= "2. LINE LIMIT STRICTLY ENFORCED: {$guide['line_limit']}\n";
        $prompt .= "3. TRUE {$params['difficulty']} level complexity\n";
        $prompt .= "4. REAL-WORLD scenario from {$selectedDomain}\n";
        $prompt .= "5. LeetCode's clear, structured format\n";
        $prompt .= "6. All logic must fit in ONE single method body\n";

        return $prompt;
    }

    private function parseGeneratedQuestion(string $text)
    {
        Log::info('Raw AI Response:', ['text' => substr($text, 0, 500) . '...']);
        
        // 1. Clean Markdown JSON blocks
        $cleanText = preg_replace('/^```json\s*/i', '', trim($text));
        $cleanText = preg_replace('/^```\s*/', '', $cleanText);
        $cleanText = preg_replace('/```$/', '', $cleanText);
        
        $decoded = json_decode($cleanText, true);

        // 2. Handle cases where AI returns an array containing the object
        if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
            $decoded = $decoded[0];
        }

        // 3. Validation & Fallback
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::error('JSON Parse Error', ['error' => json_last_error_msg()]);
            return $this->getFallbackStructure();
        }

        // 4. Mapping to your Build Prompt attributes
        return [
            'title'             => $decoded['title'] ?? 'Business Challenge',
            'difficulty'        => $decoded['difficulty'] ?? 'Beginner',
            'return_type'       => $decoded['return_type'] ?? 'void', // Added based on new prompt
            
            // Ensure the description stays short (3-4 sentences max as per prompt)
            'description'       => $decoded['description'] ?? 'A real-world business logic challenge.',
            
            // Technical requirements
            'problemStatement' => $decoded['problem_statement'] ?? 'Implement the logic to solve the business requirement.',
            
            'constraints'       => $decoded['constraints'] ?? ['Complexity: O(n)'],
            'function_name'     => $decoded['function_name'] ?? 'solve',
            
            // Solution is now expected to be 10-15+ lines
            'solution'          => $decoded['solution'] ?? '// Logic implementation required.',
            
            // Hint mapped from the numbered list in buildPrompt
            'expectedApproach' => $decoded['expected_approach'] ?? ($decoded['hint'] ?? '1. Analyze input.'),
            
            'tests'             => $decoded['tests'] ?? [],
            'topic'             => $decoded['topic'] ?? 'General',
        ];
    }

    /**
     * Ensures the app doesn't crash if the AI fails
     */
    private function getFallbackStructure(): array
    {
        return [
            'title'             => 'System Logic Task',
            'difficulty'        => 'Medium',
            'return_type'       => 'boolean',
            'description'       => 'We encountered an error generating the scenario.',
            'problem_statement' => 'Please refresh to generate a new technical challenge.',
            'constraints'       => ['N/A'],
            'function_name'     => 'process',
            'solution'          => '// Error in generation',
            'hint'              => 'Check your prompt configuration.',
            'tests'             => [],
            'topic'             => 'General'
        ];
    }

    /**
     * Fetch random LeetCode problem based on difficulty
     */
    public function fetchLeetCodeQuestion(Request $request)
    {
        $request->validate([
            'difficulty' => 'required|string|in:Easy,Medium,Hard',
            'language' => 'required|string',
        ]);

        try {
            // Fetch all problems from LeetCode API
            $response = Http::timeout(30)->get('https://leetcode-api-pied.vercel.app/problems');

            if ($response->failed()) {
                throw new \Exception('Failed to fetch LeetCode problems');
            }

            $allProblems = $response->json();
            
            // Filter by difficulty and exclude paid-only problems
            $filteredProblems = array_filter($allProblems, function($problem) use ($request) {
                return $problem['difficulty'] === $request->difficulty && 
                       $problem['paid_only'] === false;
            });

            if (empty($filteredProblems)) {
                throw new \Exception('No problems found for this difficulty level');
            }

            // Select a random problem
            $selectedProblem = $filteredProblems[array_rand($filteredProblems)];

            // Now fetch detailed problem information
            $detailResponse = Http::timeout(30)->get("https://leetcode-api-pied.vercel.app/{$selectedProblem['title_slug']}");
            
            if ($detailResponse->failed()) {
                // If detail endpoint fails, use basic info to create a question structure
                return $this->createQuestionFromBasicInfo($selectedProblem, $request->language);
            }

            $problemDetail = $detailResponse->json();
            
            // Parse and format the problem for your system
            $formattedQuestion = $this->formatLeetCodeProblem($problemDetail, $request->language);

            return response()->json([
                'success' => true,
                'data' => $formattedQuestion,
                'source' => 'leetcode'
            ]);

        } catch (\Exception $e) {
            Log::error('LeetCode Fetch Error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error fetching LeetCode problem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create question structure from basic LeetCode info (fallback)
     */
    private function createQuestionFromBasicInfo($problem, $language)
    {
        $difficultyMap = [
            'Easy' => 'easy',
            'Medium' => 'intermediate',
            'Hard' => 'advanced'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $problem['title'],
                'difficulty' => $difficultyMap[$problem['difficulty']] ?? 'intermediate',
                'description' => "LeetCode Problem #{$problem['frontend_id']}: {$problem['title']}",
                'problemStatement' => "Visit {$problem['url']} for full problem details.",
                'constraints' => ['See LeetCode for constraints'],
                'function_name' => $this->generateFunctionName($problem['title']),
                'solution' => "// Implement your solution here\n// Problem: {$problem['title']}",
                'expectedApproach' => '1. Analyze the problem requirements\n2. Design an efficient algorithm\n3. Implement and test',
                'tests' => [],
                'topic' => 'Algorithms',
                'leetcode_url' => $problem['url'],
                'leetcode_id' => $problem['frontend_id']
            ],
            'source' => 'leetcode_basic'
        ]);
    }

    /**
     * Format detailed LeetCode problem for your system
     */
    private function formatLeetCodeProblem($problemDetail, $language)
    {
        $difficultyMap = [
            'Easy' => 'easy',
            'Medium' => 'intermediate',
            'Hard' => 'advanced'
        ];

        // Extract test cases if available
        $tests = [];
        if (isset($problemDetail['exampleTestcases'])) {
            $testInputs = explode("\n", $problemDetail['exampleTestcases']);
            foreach ($testInputs as $index => $input) {
                $tests[] = [
                    'input' => trim($input),
                    'output' => '' // LeetCode API doesn't always provide outputs
                ];
            }
        }

        // Parse constraints
        $constraints = [];
        if (isset($problemDetail['constraints'])) {
            $constraints = is_array($problemDetail['constraints']) 
                ? $problemDetail['constraints'] 
                : explode("\n", $problemDetail['constraints']);
        }

        return [
            'title' => $problemDetail['title'] ?? $problemDetail['questionTitle'] ?? 'LeetCode Problem',
            'difficulty' => $difficultyMap[$problemDetail['difficulty']] ?? 'intermediate',
            'description' => strip_tags($problemDetail['content'] ?? $problemDetail['description'] ?? ''),
            'problemStatement' => strip_tags($problemDetail['content'] ?? ''),
            'constraints' => array_filter($constraints),
            'function_name' => $this->generateFunctionName($problemDetail['title'] ?? 'solve'),
            'solution' => $this->generateBoilerplate($problemDetail, $language),
            'expectedApproach' => $problemDetail['hints'] ?? '1. Break down the problem\n2. Consider edge cases\n3. Optimize your solution',
            'tests' => $tests,
            'topic' => implode(', ', $problemDetail['topicTags'] ?? ['Algorithms']),
            'leetcode_url' => "https://leetcode.com/problems/{$problemDetail['titleSlug']}/",
            'leetcode_id' => $problemDetail['questionFrontendId'] ?? ''
        ];
    }

    /**
     * Generate function name from problem title
     */
    private function generateFunctionName($title)
    {
        // Convert "Two Sum" to "twoSum"
        $words = explode(' ', $title);
        $functionName = lcfirst(str_replace([' ', '-', '(', ')', '.', ','], '', ucwords(strtolower($title))));
        return preg_replace('/[^a-zA-Z0-9_]/', '', $functionName);
    }

    /**
     * Generate code boilerplate based on language
     */
    private function generateBoilerplate($problemDetail, $language)
    {
        $functionName = $this->generateFunctionName($problemDetail['title'] ?? 'solve');
        
        $templates = [
            'Python' => "def {$functionName}():\n    # Write your solution here\n    pass",
            'JavaScript' => "function {$functionName}() {\n    // Write your solution here\n}",
            'Java' => "class Solution {\n    public void {$functionName}() {\n        // Write your solution here\n    }\n}",
            'C++' => "class Solution {\npublic:\n    void {$functionName}() {\n        // Write your solution here\n    }\n};",
            'C#' => "public class Solution {\n    public void {$functionName}() {\n        // Write your solution here\n    }\n}",
        ];

        return $templates[$language] ?? "// Implement {$functionName}\n// Language: {$language}";
    }
}