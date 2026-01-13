<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Question;

class GenerateWeeklyQuestions extends Command
{
    protected $signature = 'questions:generate-weekly';
    protected $description = 'Automatically generate questions using strict JSON parsing and varied domains';

    public function handle()
    {
        // 1. Prevent Timeout Error for long-running batch process
        set_time_limit(0); 

        $this->info('Starting weekly question generation...');
        
        $languages = ['Python','Java','C','JavaScript','PHP', 'C++']; // Standardized list
        $difficulties = ['beginner', 'intermediate', 'advanced'];
        
        // We will loop through languages and generate a few questions for each
        $totalGenerated = 0;
        $totalFailed = 0;
        
        foreach ($languages as $language) {
            $this->info("\nGenerating questions for {$language}...");
            
            // Generate 1 question per difficulty for each language
            foreach ($difficulties as $difficulty) {
                try {
                    $this->line("  - Generating {$difficulty} question...");
                    
                    $question = $this->generateQuestion($language, $difficulty);
                    
                    if ($question) {
                        $totalGenerated++;
                        $this->info("    ✓ Success: {$question->title}");
                    } else {
                        $totalFailed++;
                        $this->error("    ✗ Failed");
                    }
                    
                    // Sleep to respect API rate limits (Gemini Free Tier)
                    sleep(10);
                    
                } catch (\Exception $e) {
                    $totalFailed++;
                    $this->error("    ✗ Error: " . $e->getMessage());
                }
            }
        }

        
        
        $this->newLine();
        $this->info("Complete! Success: {$totalGenerated}, Failed: {$totalFailed}");
        return 0;
    }
    
    private function generateQuestion($language, $difficulty)
    {
        try {
            // Use the new consistent prompt builder
            $prompt = $this->buildPrompt($language, $difficulty);
            
            $generatedText = $this->callGeminiAPIWithRetry($prompt, $difficulty);
            
            if (!$generatedText) return null;
            
            $parsedQuestion = $this->parseGeneratedQuestion($generatedText);
            
            // Validation: Must have tests
            if (empty($parsedQuestion['tests'])) {
                Log::warning('Generated question missing tests', ['data' => $parsedQuestion]);
                return null;
            }
            
            return $this->saveQuestion($parsedQuestion, $language, $difficulty);
        } catch (\Throwable $e) {
            Log::error('Question Generation Error', [
                'language' => $language,
                'difficulty' => $difficulty,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function callGeminiAPIWithRetry($prompt, $difficulty = 'intermediate')
    {
        // Use Config first, then Env fallback
        $geminiApiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        
        if (!$geminiApiKey) throw new \Exception('Gemini API key not configured');

        // Increased tokens for advanced questions - they need more space for complex solutions
        $maxTokens = match($difficulty) {
            'advanced' => 20480,      // Increased to 20K for complex DP/Graph solutions
            'intermediate' => 12288,  // Increased for moderate complexity
            default => 8192           // Standard for beginner
        };

        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            $attempt++;
            try {
                $response = Http::timeout(120)->withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'temperature' => 0.7, 
                        'maxOutputTokens' => $maxTokens,
                        'topP' => 0.95,
                        'topK' => 40
                    ]
                ]);

                if ($response->status() === 429) {
                    $this->warn("    Rate limit (429). Retrying in 15s...");
                    sleep(15);
                    continue;
                }
                
                if ($response->status() === 503) {
                    $this->warn("    Service overloaded (503). Retrying in 20s...");
                    sleep(20);
                    continue;
                }

                if ($response->failed()) {
                    $errorBody = $response->body();
                    Log::error('Gemini API Error', [
                        'status' => $response->status(),
                        'body' => $errorBody,
                        'difficulty' => $difficulty
                    ]);
                    
                    if ($attempt === $maxRetries) {
                        return null;
                    }
                    sleep(10);
                    continue;
                }

                $result = $response->json();
                
                // Log the raw response for debugging advanced questions
                if ($difficulty === 'advanced') {
                    Log::info('Advanced Question Generated', [
                        'has_candidates' => isset($result['candidates']),
                        'sample' => substr($result['candidates'][0]['content']['parts'][0]['text'] ?? '', 0, 200)
                    ]);
                }
                
                return $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            } catch (\Exception $e) {
                Log::error('Gemini API Call Exception', [
                    'attempt' => $attempt,
                    'difficulty' => $difficulty,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt === $maxRetries) return null;
                sleep(10);
            }
        }
        return null;
    }

    
    
    /**
     * ALIGNED: Matches QuestionGeneratorController.php with LeetCode-style formatting and difficulty calibration
     */
    private function buildPrompt($language, $difficulty)
    {
        $domains = ['FinTech', 'Healthcare', 'E-commerce', 'Logistics', 'Gaming', 'Social Media', 'EdTech', 'Real Estate'];
        $selectedDomain = $domains[array_rand($domains)];

        // Simplified guidelines with STRICT line limits for single-method solutions
        $difficultyGuidelines = [
            'beginner' => [
                'complexity' => 'Simple, straightforward logic with basic data structures',
                'topics' => 'Basic Arrays, String Manipulation, Simple Math, Basic Loops',
                'solution_length' => 'MAXIMUM 10 lines of code (excluding function signature and closing brace)',
                'line_limit' => 'STRICTLY under 10 lines',
                'examples' => 'Find max value, reverse string, count occurrences'
            ],
            'intermediate' => [
                'complexity' => 'Moderate complexity with common algorithms',
                'topics' => 'Hash Tables, Two Pointers, Sliding Window, Stack, Queue, Sorting',
                'solution_length' => 'MAXIMUM 20 lines of code (excluding function signature and closing brace)',
                'line_limit' => 'STRICTLY under 20 lines',
                'examples' => 'Two Sum, validate parentheses, merge intervals'
            ],
            'advanced' => [
                'complexity' => 'Complex algorithms requiring optimization',
                'topics' => 'Dynamic Programming, Graph Algorithms, Advanced Trees, Backtracking',
                'solution_length' => 'MAXIMUM 25 lines of code (excluding function signature and closing brace)',
                'line_limit' => 'STRICTLY under 25 lines',
                'examples' => 'LIS, shortest path, LRU cache, delivery optimization'
            ]
        ];

        $guide = $difficultyGuidelines[strtolower($difficulty)] ?? $difficultyGuidelines['intermediate'];

        // Simplified prompt for all difficulty levels with single-method enforcement
        $prompt = "Create a {$difficulty} {$language} coding challenge for {$selectedDomain} domain.\n\n";
        
        $prompt .= "==== CRITICAL: SINGLE METHOD REQUIREMENT ====\n\n";
        $prompt .= "⚠️ MANDATORY: The solution must implement EXACTLY ONE METHOD/FUNCTION\n";
        $prompt .= "⚠️ MANDATORY: {$guide['line_limit']}\n";
        $prompt .= "⚠️ DO NOT create helper methods, classes, or multiple functions\n";
        $prompt .= "⚠️ ALL logic must fit inside ONE single method\n\n";
        
        $prompt .= "Requirements:\n";
        $prompt .= "- Topics: {$guide['topics']}\n";
        $prompt .= "- Solution: ONE method only - {$guide['solution_length']}\n";
        $prompt .= "- Real-world {$selectedDomain} scenario (2-3 sentences max)\n";
        $prompt .= "- 2-3 test cases with brief explanations\n";
        $prompt .= "- 3-4 step approach (concise)\n\n";
        
        // Add single-method examples for each difficulty
        if ($difficulty === 'beginner') {
            $prompt .= "BEGINNER Example (Under 10 lines):\n";
            $prompt .= "function findMax(arr) {\n";
            $prompt .= "    let max = arr[0];\n";
            $prompt .= "    for (let i = 1; i < arr.length; i++) {\n";
            $prompt .= "        if (arr[i] > max) max = arr[i];\n";
            $prompt .= "    }\n";
            $prompt .= "    return max;\n";
            $prompt .= "}\n\n";
        } elseif ($difficulty === 'intermediate') {
            $prompt .= "INTERMEDIATE Example (Under 20 lines):\n";
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
            $prompt .= "function lengthOfLIS(nums) {\n";
            $prompt .= "    if (!nums.length) return 0;\n";
            $prompt .= "    const dp = new Array(nums.length).fill(1);\n";
            $prompt .= "    let maxLen = 1;\n";
            $prompt .= "    for (let i = 1; i < nums.length; i++) {\n";
            $prompt .= "        for (let j = 0; i < i; j++) {\n";
            $prompt .= "            if (nums[i] > nums[j]) {\n";
            $prompt .= "                dp[i] = Math.max(dp[i], dp[j] + 1);\n";
            $prompt .= "            }\n";
            $prompt .= "        }\n";
            $prompt .= "        maxLen = Math.max(maxLen, dp[i]);\n";
            $prompt .= "    }\n";
            $prompt .= "    return maxLen;\n";
            $prompt .= "}\n\n";
        }
        
        $prompt .= "JSON format (NO markdown):\n";
        $prompt .= "{\n";
        $prompt .= "  \"title\": \"Action Title\",\n";
        $prompt .= "  \"difficulty\": \"{$difficulty}\",\n";
        $prompt .= "  \"description\": \"Brief {$selectedDomain} scenario\",\n";
        $prompt .= "  \"problem_statement\": \"Given input X, return Y. Must be solvable with ONE method.\",\n";
        $prompt .= "  \"constraints\": [\"1<=n<=10^" . ($difficulty === 'beginner' ? '2' : ($difficulty === 'intermediate' ? '4' : '5')) . "\", \"ONE method only - {$guide['line_limit']}\"],\n";
        $prompt .= "  \"function_name\": \"solveProblem\",\n";
        $prompt .= "  \"return_type\": \"int|string|array|boolean\",\n";
        $prompt .= "  \"tests\": [{\"input\":\"...\", \"output\":\"...\", \"explanation\":\"brief\"}],\n";
        $prompt .= "  \"expected_approach\": \"1. Step one\\n2. Step two\\n3. Step three\",\n";
        $prompt .= "  \"solution\": \"ONE complete working method - {$guide['solution_length']}\",\n";
        $prompt .= "  \"topic\": \"" . ($difficulty === 'beginner' ? 'Arrays|Strings|Loops' : ($difficulty === 'intermediate' ? 'Hash Tables|Two Pointers|Stack' : 'Dynamic Programming|Graph|Backtracking')) . "\"\n";
        $prompt .= "}\n\n";
        $prompt .= "🚨 CRITICAL: ONE method only ({$guide['line_limit']}) - complete and working. Follow LeetCode format with {$selectedDomain} scenario.";
        
        return $prompt;
    }

    
    
    private function parseGeneratedQuestion(string $text)
    {
        // Step 1: Remove markdown code blocks
        $cleanText = preg_replace('/^```json\s*/i', '', trim($text));
        $cleanText = preg_replace('/^```\s*/', '', $cleanText);
        $cleanText = preg_replace('/```$/', '', $cleanText);
        
        // Step 2: Remove control characters that break JSON (except newlines and tabs we want to keep in strings)
        // This fixes the "Control character error, possibly incorrectly encoded" issue
        $cleanText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleanText);
        
        // Step 3: Fix common JSON issues in AI responses
        $cleanText = str_replace(["\r\n", "\r"], "\n", $cleanText); // Normalize line endings
        $cleanText = preg_replace('/,\s*([\]}])/', '$1', $cleanText); // Remove trailing commas
        
        // Step 4: Try to decode
        $decoded = json_decode($cleanText, true);

        // Step 5: If decoding fails, try more aggressive cleaning
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('First JSON decode attempt failed, trying aggressive cleaning', [
                'error' => json_last_error_msg(),
                'sample' => substr($cleanText, 0, 500)
            ]);
            
            // More aggressive: escape unescaped quotes in strings and fix newlines
            $cleanText = trim($cleanText);
            
            // Try decoding again
            $decoded = json_decode($cleanText, true);
        }

        // Step 6: Handle array wrapping
        if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
            $decoded = $decoded[0];
        }

        // Step 7: Final validation
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::error('AI JSON Error', [
                'error' => json_last_error_msg(),
                'sample' => substr($text, 0, 1000)
            ]);
            return [];
        }

        return [
            'title'             => $decoded['title'] ?? 'Business Challenge',
            'function_name'     => $decoded['function_name'] ?? 'solve',
            'return_type'       => $decoded['return_type'] ?? 'mixed',
            'description'       => $decoded['description'] ?? '',
            'problemStatement' => $decoded['problem_statement'] ?? ($decoded['problemStatement'] ?? ($decoded['description'] ?? '')),
            'constraints'       => $decoded['constraints'] ?? [],
            'solution'          => $decoded['solution'] ?? '// Logic required',
            'topic'             => $decoded['topic'] ?? 'General',
            'expected_approach' => $decoded['expected_approach'] ?? ($decoded['hint'] ?? ($decoded['expectedApproach'] ?? '1. Analyze input.')),
            'tests'             => $decoded['tests'] ?? [],
            'language'          => $decoded['language'] ?? 'Unknown',
            'difficulty'        => $decoded['difficulty'] ?? 'intermediate'
        ];
    }

    
    
    private function saveQuestion($parsed, $language, $difficulty)
    {
        if (empty($parsed)) return null;

        $levelMap = ['beginner' => 'beginner', 'intermediate' => 'intermediate', 'advanced'];
        $mappedLevel = $levelMap[strtolower($difficulty)] ?? 'intermediate';

        $constraintsText = is_array($parsed['constraints']) 
            ? implode("\n", array_map(fn($c) => "- $c", $parsed['constraints'])) 
            : "";

        $inputData = [];
        $expectedOutputData = [];

        if (isset($parsed['tests']) && is_array($parsed['tests'])) {
            foreach ($parsed['tests'] as $index => $test) {
                // FIX: Handle test input properly - keep as string if it's already a string
                $inputValue = $test['input'] ?? '';
                
                // If input is already a string, keep it as is
                // If it's an array/object, convert to string
                if (is_array($inputValue) || is_object($inputValue)) {
                    $inputValue = json_encode($inputValue);
                }
                
                $outputValue = $test['output'] ?? '';
                if (is_array($outputValue) || is_object($outputValue)) {
                    $outputValue = json_encode($outputValue);
                }
                
                $inputData[] = [
                    'test_case' => $index + 1, 
                    'input' => $inputValue
                ];
                
                $expectedOutputData[] = [
                    'test_case' => $index + 1, 
                    'output' => $outputValue
                ];
            }
        }

        // FIX: Use correct key names from parseGeneratedQuestion and ensure strings
        $problemStatement = $parsed['problemStatement'] ?? $parsed['description'];
        $returnType = $parsed['return_type'] ?? 'void';
        
        // FIX: Convert arrays to strings for content
        $expectedApproach = is_array($parsed['expected_approach']) 
            ? implode("\n", $parsed['expected_approach']) 
            : ($parsed['expected_approach'] ?? '1. Analyze input.');
            
        $description = is_array($parsed['description']) 
            ? implode(' ', $parsed['description']) 
            : ($parsed['description'] ?? '');

        // Include the Technical Requirements and Return Type in the Markdown content
        $content = "# {$parsed['title']}\n\n" .
                "**Return Type:** `{$returnType}`\n\n" .
                "## Description\n{$description}\n\n" .
                "## Technical Requirements\n{$problemStatement}\n\n" .
                "## Constraints\n$constraintsText\n\n" .
                "## Expected Approach\n{$expectedApproach}";

        try {
            return Question::create([
                'title'             => $parsed['title'],
                'function_name'     => $parsed['function_name'],
                'return_type'       => $returnType,
                'content'           => $content,
                'description'       => $description,
                'problem_statement' => $problemStatement,
                'constraints'       => trim($constraintsText),
                'expected_output'   => $expectedOutputData,
                'answersData'       => $parsed['solution'],
                'status'            => 'Pending',
                'language'          => $language,
                'level'             => $mappedLevel,
                'questionCategory'  => 'learnerPractice',
                'questionType'      => 'Code_Solution',
                'chapter'           => $parsed['topic'],
                'hint'              => $expectedApproach,
                'input'             => $inputData,
                'reviewer_ID'       => 1 
            ]);
        } catch (\Exception $e) {
            \Log::error('Save Question Error', [
                'error' => $e->getMessage(),
                'parsed' => $parsed
            ]);
            return null;
        }
    }
}