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
        
        $languages = ['Python', 'Java', 'JavaScript', 'PHP', 'C++']; // Standardized list
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
        // Use the new consistent prompt builder
        $prompt = $this->buildPrompt($language, $difficulty);
        
        $generatedText = $this->callGeminiAPIWithRetry($prompt);
        
        if (!$generatedText) return null;
        
        $parsedQuestion = $this->parseGeneratedQuestion($generatedText);
        
        // Validation: Must have tests
        if (empty($parsedQuestion['tests'])) {
            Log::warning('Generated question missing tests', ['data' => $parsedQuestion]);
            return null;
        }
        
        return $this->saveQuestion($parsedQuestion, $language, $difficulty);
    }

    private function callGeminiAPIWithRetry($prompt)
    {
        // Use Config first, then Env fallback
        $geminiApiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        
        if (!$geminiApiKey) throw new \Exception('Gemini API key not configured');

        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            $attempt++;
            try {
                $response = Http::timeout(60)->withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 8192]
                ]);

                if ($response->status() === 429) {
                    $this->warn("    Rate limit (429). Retrying in 10s...");
                    sleep(10);
                    continue;
                }

                if ($response->failed()) {
                    Log::error('Gemini API Error', ['body' => $response->body()]);
                    return null;
                }

                $result = $response->json();
                return $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            } catch (\Exception $e) {
                if ($attempt === $maxRetries) return null;
                sleep(10);
            }
        }
        return null;
    }

    
    
    /**
     * ALIGNED: Matches QuestionGeneratorController.php EXACTLY
     */
    private function buildPrompt($language, $difficulty)
    {
        // 1. Domains List
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
        $prompt .= "Generate 1 coding interview question for {$language} ({$difficulty}).\n";
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
        $prompt .= "  \"solution\": \"class Solution { public int[] solve(int[] a, int[] b) { ...full code... } }\",\n";
        $prompt .= "  \"language\": \"{$language}\",\n";
        $prompt .= "  \"difficulty\": \"{$difficulty}\",\n";
        $prompt .= "  \"topic\": \"Arrays\",\n";
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
        // 1. Clean Markdown
        $cleanText = preg_replace('/^```json\s*/i', '', trim($text));
        $cleanText = preg_replace('/^```\s*/', '', $cleanText);
        $cleanText = preg_replace('/```$/', '', $cleanText);
        
        // 2. Decode
        $decoded = json_decode($cleanText, true);

        // Handle array wrapper [ {} ]
        if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
            $decoded = $decoded[0];
        }

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::error('Weekly Generation JSON Error', ['error' => json_last_error_msg(), 'raw' => $cleanText]);
            return [];
        }

        // 3. Map Fields (Matching Controller Logic)
        return [
            'title' => $decoded['title'] ?? 'Weekly Challenge',
            'function_name' => $decoded['function_name'] ?? 'solve',
            'description' => $decoded['description'] ?? '',
            'problemStatement' => $decoded['problem_statement'] ?? ($decoded['description'] ?? ''),
            'constraints' => $decoded['constraints'] ?? [],
            'solution' => $decoded['solution'] ?? '// No solution provided',
            'topic' => $decoded['topic'] ?? 'General',
            // Map 'hint' to 'expectedApproach'
            'expectedApproach' => $decoded['hint'] ?? ($decoded['expectedApproach'] ?? '1. Analyze the input.'),
            'tests' => $decoded['tests'] ?? []
        ];
    }

    
    
    private function saveQuestion($parsed, $language, $difficulty)
    {
        $levelMap = ['beginner' => 'beginner', 'intermediate' => 'intermediate', 'advanced' => 'advanced'];
        $mappedLevel = $levelMap[strtolower($difficulty)] ?? 'intermediate';

        $constraintsText = "";
        if (is_array($parsed['constraints'])) {
            $constraintsText = implode("\n", array_map(fn($c) => "- $c", $parsed['constraints']));
        }

        $inputData = [];
        $expectedOutputData = [];

        // ALIGNED: Handle [object Object] fix by encoding input
        if (isset($parsed['tests']) && is_array($parsed['tests'])) {
            foreach ($parsed['tests'] as $index => $test) {
                
                $inputVal = $test['input'] ?? [];
                
                // If it's an array/object, leave it as is. 
                // The Model cast (protected $casts = ['input' => 'array']) handles encoding to JSON string for DB.
                // NOTE: In the Controller we encoded it for the Frontend Display. 
                // Here we are saving directly to DB, so Laravel's Eloquent casting handles array->json.
                
                $inputData[] = [
                    'test_case' => $index + 1,
                    'input' => $inputVal
                ];

                // Output is usually a simple value, but robustly handle it
                $expectedOutputData[] = [
                    'test_case' => $index + 1,
                    'output' => $test['output'] ?? ''
                ];
            }
        }

        $content = "# {$parsed['title']}\n\n## Description\n{$parsed['description']}\n\n## Constraints\n$constraintsText\n\n## Hint\n{$parsed['expectedApproach']}";

        // Assign to a System Reviewer or Null
        // Assuming Reviewer ID 1 exists, or you can make this nullable in DB
        $systemReviewerId = 1; 

        return Question::create([
            'title' => $parsed['title'],
            'function_name' => $parsed['function_name'], 
            'content' => $content,
            'description' => $parsed['description'],
            'problem_statement' => $parsed['problemStatement'],
            'constraints' => trim($constraintsText),
            'expected_output' => $expectedOutputData,
            'answersData' => $parsed['solution'],
            'status' => 'Pending',
            'language' => $language,
            'level' => $mappedLevel,
            'questionCategory' => 'learnerPractice',
            'questionType' => 'Code_Solution',
            'chapter' => $parsed['topic'],
            'hint' => $parsed['expectedApproach'],
            'input' => $inputData,
            'reviewer_ID' => $systemReviewerId // Ensure this ID exists in your reviewers table
        ]);
    }
}