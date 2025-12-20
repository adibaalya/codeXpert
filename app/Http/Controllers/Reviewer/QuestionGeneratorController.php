<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Question;

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
            'function_name' => 'nullable|string', // Added validation
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
                $inputVal = $test['input'];
                
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

            // Use the parsed function name, or fallback to 'solve'
            $functionName = $request->input('function_name', 'solve');

            $question = Question::create([
                'title' => $request->title,
                'function_name' => $functionName, // Save to new column
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

            return response()->json([
                'success' => true,
                'message' => 'Question saved successfully!',
                'question_id' => $question->question_ID
            ]);

        } catch (\Exception $e) {
            Log::error('Save Question Error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save question: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function buildPrompt(array $params)
    {
        // 1. Domains
        $domains = [
            'FinTech (Banking, Fees, Interest)',
            'Healthcare (Triage, Vitals, Schedules)',
            'E-commerce (Discounts, Inventory, Cart)',
            'Gaming (Leaderboards, Scores, Inventory)',
            'Logistics (Routes, Cargo, Tracking)',
            'School System (Grades, Attendance, Schedules)'
        ];
        $selectedDomain = $domains[array_rand($domains)];

        $prompt  = "You are an expert technical interviewer.\n";
        $prompt .= "Generate 1 coding interview question for {$params['language']} ({$params['difficulty']}).\n";
        $prompt .= "Context: {$params['prompt']}.\n";
        $prompt .= "Domain: {$selectedDomain}.\n\n";

        $prompt .= "STRICT REQUIREMENTS:\n";
        $prompt .= "1. **Real World Context**: Use the domain above. NO server logs or abstract math.\n";
        $prompt .= "2. **Concise Writing**: 'description' and 'problem_statement' must be SHORT (max 2 sentences each).\n";
        $prompt .= "3. **Test Cases**: Generate EXACTLY 3 test cases.\n";
        $prompt .= "4. **Solution**: You MUST provide the full, working solution code in the 'solution' field.\n";
        $prompt .= "5. **Hint**: The hint MUST be a numbered list (1., 2., 3.) explaining the steps.\n\n";

        $prompt .= "Output strictly valid JSON (no markdown). Use this structure:\n";
        $prompt .= "{\n";
        $prompt .= "  \"title\": \"Short Business Title\",\n";
        $prompt .= "  \"function_name\": \"camelCaseFunctionName\",\n";
        $prompt .= "  \"description\": \"Two hospital wings are merging their patient queues.\",\n";
        $prompt .= "  \"problem_statement\": \"Implement `mergeLists` to combine two sorted arrays into one.\",\n";
        $prompt .= "  \"constraints\": [\"Array length <= 100\", \"Sorted input\"],\n";
        // Enforce full code in the example so the AI mimics it
        $prompt .= "  \"solution\": \"class Solution { public int[] solve(int[] a, int[] b) { ...full code... } }\",\n";
        $prompt .= "  \"language\": \"{$params['language']}\",\n";
        $prompt .= "  \"difficulty\": \"{$params['difficulty']}\",\n";
        $prompt .= "  \"topic\": \"Arrays\",\n";
        // Enforce numbered list format
        $prompt .= "  \"hint\": \"1. Initialize two pointers.\\n2. Compare elements.\\n3. Push smaller element to result.\",\n";
        $prompt .= "  \"tests\": [\n";
        $prompt .= "    {\"input\": {\"a\": 1, \"b\": 2}, \"output\": 3},\n";
        $prompt .= "    {\"input\": {\"a\": 0, \"b\": 0}, \"output\": 0},\n";
        $prompt .= "    {\"input\": {\"a\": -1, \"b\": 1}, \"output\": 0}\n";
        $prompt .= "  ]\n";
        $prompt .= "}";

        return $prompt;
    }

    private function parseGeneratedQuestion(string $text)
    {
        Log::info('Raw AI Response:', ['text' => substr($text, 0, 500) . '...']);
        
        $cleanText = preg_replace('/^```json\s*/i', '', trim($text));
        $cleanText = preg_replace('/^```\s*/', '', $cleanText);
        $cleanText = preg_replace('/```$/', '', $cleanText);
        
        $decoded = json_decode($cleanText, true);

        if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
            $decoded = $decoded[0];
        }

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::error('JSON Parse Error', ['error' => json_last_error_msg()]);
            // Fallback structure
            return [
                'title' => 'Generation Failed',
                'function_name' => 'solve',
                'description' => 'AI generation failed.',
                'problemStatement' => 'Please try again.',
                'constraints' => [],
                'expectedApproach' => '1. Retry generation.',
                'tests' => [],
                'solution' => '// No solution generated',
                'topic' => 'General'
            ];
        }

        return [
            'title' => $decoded['title'] ?? 'Generated Question',
            'function_name' => $decoded['function_name'] ?? 'solve',
            'description' => $decoded['description'] ?? '',
            'problemStatement' => $decoded['problem_statement'] ?? ($decoded['description'] ?? ''),
            'constraints' => $decoded['constraints'] ?? [],
            'expectedApproach' => $decoded['hint'] ?? ($decoded['expectedApproach'] ?? '1. Analyze input.'),
            'tests' => $decoded['tests'] ?? [],
            
            // Check if solution exists, if not, put a placeholder so it doesn't crash
            'solution' => $decoded['solution'] ?? '// Solution missing from AI response',
            
            'topic' => $decoded['topic'] ?? 'General',
            'language' => $decoded['language'] ?? 'Unknown',
            'difficulty' => $decoded['difficulty'] ?? 'Beginner'
        ];
    }
}