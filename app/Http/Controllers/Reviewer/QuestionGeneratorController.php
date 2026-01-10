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

            $maxRetries = 3;
            $retryDelay = 2;
            $lastError = null;

            for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
                try {
                    if ($attempt > 0) {
                        $waitTime = $retryDelay * pow(2, $attempt - 1);
                        sleep($waitTime);
                    }

                    $response = Http::timeout(60)->withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}", [
                        'contents' => [['parts' => [['text' => $systemPrompt]]]],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 8192,
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
                        throw new \Exception('API Error: ' . $response->status());
                    }

                    $result = $response->json();
                    
                    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                        throw new \Exception('Invalid response structure');
                    }

                    $generatedText = $result['candidates'][0]['content']['parts'][0]['text'];
                    
                    // Parse using the new robust parser
                    $parsedQuestion = $this->parseGeneratedQuestion($generatedText);

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
                    if ($attempt === $maxRetries - 1) throw $e;
                }
            }

        } catch (\Exception $e) {
            Log::error('Question Generation Error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
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
            'function_name' => 'nullable|string',
            'return_type' => 'nullable|string', // Added validation for return_type
        ]);

        try {
            $reviewer = auth('reviewer')->user();

            $constraintsText = '';
            foreach ($request->constraints as $constraint) {
                $constraintsText .= "- {$constraint}\n";
            }

            // Prepare input test cases (Key-Value Array Structure)
            $inputData = [];
            $expectedOutputData = [];

            foreach ($request->tests as $index => $test) {
                // Check if input is a JSON string (from our fix) or an array
                $inputVal = $test['input'] ?? '';
                
                // If the frontend sends back the string "{\"a\":1}", 
                // we decode it back to an array so Laravel stores it cleanly as JSON
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
                    'output' => $test['output'] ?? ''
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

            // Use the parsed function name, or fallback to 'solve'
            $functionName = $request->input('function_name', 'solve');
            $returnType = $request->input('return_type', 'void');

            $question = Question::create([
                'title' => $request->title,
                'function_name' => $functionName,
                'return_type' => $returnType, // Added return_type
                'content' => $content,
                'description' => $request->description,
                'problem_statement' => $request->problemStatement,
                'constraints' => trim($constraintsText),
                'expected_output' => $expectedOutputData, // Casts to JSON
                'answersData' => $request->solution,
                'status' => 'Pending',
                'reviewer_ID' => $reviewer->reviewer_ID,
                'language' => $request->language,
                'level' => $mappedLevel,
                'questionCategory' => 'learnerPractice',
                'questionType' => 'Code_Solution',
                'chapter' => $request->topic,
                'hint' => $request->expectedApproach,
                'input' => $inputData, // Casts to JSON
            ]);

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
        $domains = ['FinTech', 'Healthcare', 'E-commerce', 'Logistics', 'Gaming'];
        $selectedDomain = $domains[array_rand($domains)];

        $prompt  = "You are a Senior Technical Interviewer. Create a '{$params['difficulty']}' challenge for {$params['language']}.\n";
        $prompt .= "Domain: {$selectedDomain}.\n\n";

        $prompt .= "STRICT JSON STRUCTURE REQUIREMENTS:\n";
        $prompt .= "1. **description**: A high-level business summary (max 3 sentences).\n";
        $prompt .= "2. **problem_statement**: MANDATORY. Detailed technical rules. Explain what the code must do (max 4 sentences).\n";
        $prompt .= "3. **expected_approach**: MANDATORY. A numbered list (1, 2, 3) of the algorithmic steps.\n";
        $prompt .= "4. **Anti-Simple Rule**: The solution must handle edge cases and be at least 10 lines of code. No one-liners.\n\n";

        $prompt .= "Return ONLY valid JSON:\n";
        $prompt .= "{\n";
        $prompt .= "  \"title\": \"String\",\n";
        $prompt .= "  \"difficulty\": \"{$params['difficulty']}\",\n";
        $prompt .= "  \"return_type\": \"String\",\n";
        $prompt .= "  \"description\": \"Business context here.\",\n";
        $prompt .= "  \"problem_statement\": \"Technical logic requirements here.\",\n";
        $prompt .= "  \"constraints\": [\"Constraint 1\", \"Constraint 2\"],\n";
        $prompt .= "  \"function_name\": \"methodName\",\n";
        $prompt .= "  \"solution\": \"Full implementation code\",\n";
        $prompt .= "  \"expected_approach\": \"1. Step one... 2. Step two...\",\n";
        $prompt .= "  \"topic\": \"Algorithm Category\",\n";
        $prompt .= "  \"tests\": [{\"input\": {}, \"output\": \"\"}]\n";
        $prompt .= "}";

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
}