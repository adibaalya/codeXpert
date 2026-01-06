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
        $domains = [
            'FinTech (Transaction Ledgers)', 'Healthcare (Triage Logic)', 
            'E-commerce (Inventory Locks)', 'Logistics (Route Efficiency)'
        ];
        $selectedDomain = $domains[array_rand($domains)];

        $prompt  = "You are a Senior LeetCode Content Engineer. Generate 1 coding problem for {$language} ({$difficulty}).\n";
        $prompt .= "Domain: {$selectedDomain}.\n\n";

        $prompt .= "STRICT REQUIREMENTS:\n";
        $prompt .= "1. **Description**: High-level business context (max 3 sentences).\n";
        $prompt .= "2. **Problem Statement**: MANDATORY. Technical logic and transformation rules (max 4 sentences).\n";
        $prompt .= "3. **Return Type**: Explicitly state the data type returned (e.g., int[], boolean, String).\n";
        $prompt .= "4. **The 'Anti-Simple' Rule**: Logic must handle edge cases and be at least 10-15 lines. No one-line built-in function solutions.\n";
        $prompt .= "5. **Advanced Difficulty**: Must require algorithms like DP, BFS/DFS, or Tries.\n\n";

        $prompt .= "Output strictly valid JSON (no markdown). Use this exact keys:\n";
        $prompt .= "{\n";
        $prompt .= "  \"title\": \"Short Business Title\",\n";
        $prompt .= "  \"function_name\": \"methodName\",\n";
        $prompt .= "  \"return_type\": \"String\",\n";
        $prompt .= "  \"description\": \"The business scenario.\",\n";
        $prompt .= "  \"problem_statement\": \"Technical implementation details.\",\n";
        $prompt .= "  \"constraints\": [\"Complexity O(n)\", \"Input size <= 10^5\"],\n";
        $prompt .= "  \"solution\": \"class Solution { ...full code... }\",\n";
        $prompt .= "  \"topic\": \"Algorithm Category\",\n";
        $prompt .= "  \"expected_approach\": \"1. Step one.\\n2. Step two.\\n3. Step three.\",\n";
        $prompt .= "  \"tests\": [{\"input\": {}, \"output\": \"\"}]\n";
        $prompt .= "}";

        return $prompt;
    }

    
    
    private function parseGeneratedQuestion(string $text)
    {
        $cleanText = preg_replace('/^```json\s*/i', '', trim($text));
        $cleanText = preg_replace('/^```\s*/', '', $cleanText);
        $cleanText = preg_replace('/```$/', '', $cleanText);
        
        $decoded = json_decode($cleanText, true);

        if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
            $decoded = $decoded[0];
        }

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::error('AI JSON Error', ['error' => json_last_error_msg()]);
            return [];
        }

        return [
            'title'             => $decoded['title'] ?? 'Business Challenge',
            'function_name'     => $decoded['function_name'] ?? 'solve',
            'return_type'       => $decoded['return_type'] ?? 'mixed',
            'description'       => $decoded['description'] ?? '',
            // FIX: Check for both snake_case and camelCase
            'problemStatement' => $decoded['problem_statement'] ?? ($decoded['problemStatement'] ?? ($decoded['description'] ?? '')),
            'constraints'       => $decoded['constraints'] ?? [],
            'solution'          => $decoded['solution'] ?? '// Logic required',
            'topic'             => $decoded['topic'] ?? 'General',
            // FIX: Check for expected_approach, hint, or approach
            'expected_approach' => $decoded['expected_approach'] ?? ($decoded['hint'] ?? ($decoded['expectedApproach'] ?? '1. Analyze input.')),
            'tests'             => $decoded['tests'] ?? [],
            'language'          => $decoded['language'] ?? 'Unknown',
            'difficulty'        => $decoded['difficulty'] ?? 'intermediate'
        ];
    }

    
    
    private function saveQuestion($parsed, $language, $difficulty)
    {
        if (empty($parsed)) return null;

        $levelMap = ['beginner' => 'beginner', 'intermediate' => 'intermediate', 'advanced' => 'advanced'];
        $mappedLevel = $levelMap[strtolower($difficulty)] ?? 'intermediate';

        $constraintsText = is_array($parsed['constraints']) 
            ? implode("\n", array_map(fn($c) => "- $c", $parsed['constraints'])) 
            : "";

        $inputData = [];
        $expectedOutputData = [];

        if (isset($parsed['tests']) && is_array($parsed['tests'])) {
            foreach ($parsed['tests'] as $index => $test) {
                $inputData[] = ['test_case' => $index + 1, 'input' => $test['input'] ?? []];
                $expectedOutputData[] = ['test_case' => $index + 1, 'output' => $test['output'] ?? ''];
            }
        }

        // Include the Technical Requirements and Return Type in the Markdown content
        $content = "# {$parsed['title']}\n\n" .
                "**Return Type:** `{$parsed['return_type']}`\n\n" .
                "## Description\n{$parsed['description']}\n\n" .
                "## Technical Requirements\n{$parsed['problem_statement']}\n\n" .
                "## Constraints\n$constraintsText\n\n" .
                "## Expected Approach\n{$parsed['expected_approach']}";

        return Question::create([
            'title'             => $parsed['title'],
            'function_name'     => $parsed['function_name'], 
            'content'           => $content,
            'description'       => $parsed['description'],
            'problem_statement' => $parsed['problemStatement'],
            'constraints'       => trim($constraintsText),
            'expected_output'   => $expectedOutputData,
            'answersData'       => $parsed['solution'],
            'status'            => 'Pending',
            'language'          => $language,
            'level'             => $mappedLevel,
            'questionCategory'  => 'learnerPractice',
            'questionType'      => 'Code_Solution',
            'chapter'           => $parsed['topic'],
            'hint'              => $parsed['expected_approach'],
            'input'             => $inputData,
            'reviewer_ID'       => 1 
        ]);
    }
}