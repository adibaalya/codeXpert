<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Question;

class GenerateMonthlyCompetencyQuestions extends Command
{
    protected $signature = 'questions:generate-monthly-competency';
    protected $description = 'Generate monthly competency test questions with hints and solutions';

    public function handle()
    {
        // FIX: Prevent timeout
        set_time_limit(0);

        $this->info('Starting monthly competency test question generation...');
        
        $languages = ['Python', 'Java', 'JavaScript', 'PHP', 'C++', 'C', 'SQL'];
        $topics = ['Array', 'String', 'Loop', 'Sorting', 'Searching', 'Recursion', 'Hash Table', 'Stack', 'Queue'];
        
        $totalGenerated = 0;
        $totalFailed = 0;
        
        foreach ($languages as $language) {
            $this->info("\nGenerating competency questions for {$language}");
            
            $mcqResults = $this->generateMCQQuestions($language, $topics);
            $totalGenerated += $mcqResults['success'];
            $totalFailed += $mcqResults['failed'];
            
            $codeResults = $this->generateCodeSolutionQuestions($language, $topics);
            $totalGenerated += $codeResults['success'];
            $totalFailed += $codeResults['failed'];
            
            $evalResults = $this->generateQuestionEvaluationQuestions($language, $topics);
            $totalGenerated += $evalResults['success'];
            $totalFailed += $evalResults['failed'];
        }
        
        $this->newLine();
        $this->info("Generation Complete! Success: {$totalGenerated}, Failed: {$totalFailed}");
        return 0;
    }
    
    // ... [generateMCQQuestions and generateQuestionEvaluationQuestions remain unchanged] ...

    private function generateMCQQuestions($language, $topics)
    {
        $difficulties = ['beginner', 'intermediate'];
        $success = 0; $failed = 0;
        foreach ($difficulties as $index => $difficulty) {
            try {
                $topic = $topics[array_rand($topics)];
                $this->line("  - MCQ ($difficulty)...");
                $question = $this->generateMCQ($language, $difficulty, $topic);
                if ($question) { $success++; $this->info("    Success"); } 
                else { $failed++; $this->error("    Failed"); }
                sleep(8); 
            } catch (\Exception $e) { $failed++; $this->error("    Error: " . $e->getMessage()); }
        }
        return ['success' => $success, 'failed' => $failed];
    }
    
    private function generateQuestionEvaluationQuestions($language, $topics)
    {
        $difficulties = ['beginner', 'intermediate'];
        $success = 0; $failed = 0;
        foreach ($difficulties as $index => $difficulty) {
            try {
                $topic = $topics[array_rand($topics)];
                $this->line("  - Question Evaluation ($difficulty)...");
                $question = $this->generateQuestionEvaluation($language, $difficulty, $topic);
                if ($question) { $success++; $this->info("    Success"); } 
                else { $failed++; $this->error("    Failed"); }
                sleep(8);
            } catch (\Exception $e) { $failed++; $this->error("    Error: " . $e->getMessage()); }
        }
        return ['success' => $success, 'failed' => $failed];
    }

    private function generateCodeSolutionQuestions($language, $topics)
    {
        $difficulties = ['beginner', 'intermediate'];
        $success = 0; $failed = 0;
        
        foreach ($difficulties as $index => $difficulty) {
            try {
                $topic = $topics[array_rand($topics)];
                $this->line("  - Code Solution ($difficulty)...");
                $question = $this->generateCodeSolution($language, $difficulty, $topic);
                if ($question) { $success++; $this->info("    Success"); } 
                else { $failed++; $this->error("    Failed"); }
                sleep(10); 
            } catch (\Exception $e) { $failed++; $this->error("    Error: " . $e->getMessage()); }
        }
        return ['success' => $success, 'failed' => $failed];
    }

    // --- GENERATORS ---

    private function generateMCQ($language, $difficulty, $topic)
    {
        $prompt = $this->buildMCQPrompt($language, $difficulty, $topic);
        $generatedText = $this->callGeminiAPIWithRetry($prompt);
        if (!$generatedText) return null;
        $parsed = $this->parseMCQ($generatedText);
        if (empty($parsed['question']) || empty($parsed['choices'])) return null;
        return $this->saveMCQQuestion($parsed, $language, $difficulty, $topic);
    }
    
    private function generateCodeSolution($language, $difficulty, $topic)
    {
        $prompt = $this->buildCodeSolutionPrompt($language, $difficulty, $topic);
        $generatedText = $this->callGeminiAPIWithRetry($prompt);
        if (!$generatedText) return null;
        
        $parsed = $this->parseCodeSolution($generatedText);
        
        if (empty($parsed['tests'])) {
            $this->warn("    No structured test cases found.");
            return null;
        }

        return $this->saveCodeSolutionQuestion($parsed, $language, $difficulty, $topic);
    }
    
    private function generateQuestionEvaluation($language, $difficulty, $topic)
    {
        $prompt = $this->buildQuestionEvaluationPrompt($language, $difficulty, $topic);
        $generatedText = $this->callGeminiAPIWithRetry($prompt);
        if (!$generatedText) return null;
        $parsed = $this->parseQuestionEvaluation($generatedText);
        if (empty($parsed['questionUnderReview'])) return null;
        return $this->saveQuestionEvaluationQuestion($parsed, $language, $difficulty, $topic);
    }
    
    // --- API HANDLER ---
    private function callGeminiAPIWithRetry($prompt)
    {
        $geminiApiKey = env('GEMINI_API_KEY');
        if (!$geminiApiKey) throw new \Exception('Gemini API key not configured');

        $maxRetries = 3; 
        $attempt = 0; 
        $baseDelay = 10; 

        while ($attempt < $maxRetries) {
            $attempt++;
            try {
                $response = Http::timeout(60)->withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 2048]
                ]);

                if ($response->status() === 429) {
                    $delay = $baseDelay * $attempt;
                    $this->warn("    Rate limited (429). Retrying in {$delay}s...");
                    sleep($delay);
                    continue;
                }

                if ($response->failed()) {
                    Log::error('Gemini API Error', ['response' => $response->body()]);
                    return null;
                }

                $result = $response->json();
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    return $result['candidates'][0]['content']['parts'][0]['text'];
                }
                return null;

            } catch (\Exception $e) {
                if ($attempt === $maxRetries) return null;
                sleep($baseDelay * $attempt);
            }
        }
        return null;
    }
    
    // --- PROMPT BUILDERS ---

    private function buildMCQPrompt($language, $difficulty, $topic)
    {
        $lengthInstruction = $difficulty === 'beginner' 
            ? "The question must be a single, direct sentence." 
            : "The question must be maximum 2 sentences.";

        return "Generate a focused MCQ question for {$language} ({$difficulty}) on {$topic}.\n" .
               "- {$lengthInstruction} Avoid scenarios.\n" .
               "- Output format:\n" .
               "QUESTION:\n[Question]\n\n" .
               "CHOICES:\nA) [Choice]\nB) [Choice]\nC) [Choice]\nD) [Choice]\n\n" .
               "CORRECT_ANSWER:\n[A, B, C, or D]\n";
    }
    
    /**
     * UPDATED: Added HINTS and SOLUTION to prompt
     */
    private function buildCodeSolutionPrompt($language, $difficulty, $topic)
    {
        return "Generate a {$difficulty} coding problem for {$language} on {$topic}.\n" .
               "STRICT REQUIREMENT: The problem MUST ask the user to implement a specific function name.\n" .
               "Example: 'Implement the `calculateSum(arr)` function...'\n" .
               "STRICT JSON REQUIREMENT: Provide test cases in a strict JSON array format.\n" .
               "Output format:\n" .
               "TITLE:\n[Title]\n\n" .
               "FUNCTION_NAME:\n[The exact name of the function, e.g. twoSum]\n\n" .
               "PROBLEM_STATEMENT:\n[Start with 'Implement the `functionName` function which...']\n\n" .
               "CONSTRAINTS:\n- [Constraint]\n\n" .
               "HINTS:\n[Brief hint on approach]\n\n" . // Added Hint
               "TEST_CASES_JSON:\n" .
               "[\n" .
               "  { \"input\": { \"arr\": [1,2], \"k\": 1 }, \"output\": 3 },\n" .
               "  { \"input\": { \"arr\": [5], \"k\": 0 }, \"output\": 5 }\n" .
               "]\n\n" .
               "SOLUTION:\n[Clean solution code in {$language}]\n"; // Added Solution
    }
    
    private function buildQuestionEvaluationPrompt($language, $difficulty, $topic)
    {
        return "Generate a code review MCQ for {$language} ({$difficulty}) on {$topic}.\n" .
               "Create a flawed code snippet and ask what is wrong.\n" .
               "Output format:\n" .
               "QUESTION_UNDER_REVIEW:\n[Code Snippet]\n\n" .
               "EVALUATION_PROMPT:\n[Question like 'What is the bug?']\n\n" .
               "CHOICES:\nA) [Choice]\nB) [Choice]\nC) [Choice]\nD) [Choice]\n\n" .
               "CORRECT_ANSWER:\n[A, B, C, or D]\n";
    }
    
    // --- PARSERS ---

    private function parseMCQ($text)
    {
        $mcq = ['question' => '', 'choices' => [], 'correctAnswer' => ''];
        
        if (preg_match('/QUESTION:\s*(.+?)(?=CHOICES:|$)/is', $text, $matches)) 
            $mcq['question'] = trim($matches[1]);
        
        if (preg_match('/CHOICES:\s*(.+?)(?=CORRECT_ANSWER:|$)/is', $text, $matches)) {
            preg_match_all('/([A-D])\)\s*(.+?)(?=[A-D]\)|$)/s', trim($matches[1]), $choiceMatches, PREG_SET_ORDER);
            foreach ($choiceMatches as $match) $mcq['choices'][$match[1]] = trim($match[2]);
        }
        
        if (preg_match('/CORRECT_ANSWER:\s*([A-D])/i', $text, $matches)) 
            $mcq['correctAnswer'] = strtoupper(trim($matches[1]));
        
        return $mcq;
    }
    
    /**
     * UPDATED: Added HINTS and SOLUTION parsing
     */
    private function parseCodeSolution($text)
    {
        $question = [
            'title' => 'Coding Challenge',
            'function_name' => 'solve',
            'description' => '',
            'problemStatement' => '',
            'constraints' => [],
            'hint' => '', // Changed from hints to hint for consistency with DB
            'tests' => [],
            'solution' => '' // Added solution
        ];

        if (preg_match('/TITLE:\s*(.+?)(\r\n|\n|$)/', $text, $matches)) 
            $question['title'] = substr(trim(str_replace(['*', '#', '`'], '', $matches[1])), 0, 190);

        if (preg_match('/FUNCTION_NAME:\s*(.+?)(\r\n|\n|$)/', $text, $matches)) 
            $question['function_name'] = trim($matches[1]);

        if (preg_match('/(?:PROBLEM_STATEMENT|DESCRIPTION)[:\s]*\s*(.+?)(?=(?:CONSTRAINTS|HINTS|TEST_CASES_JSON)|$)/is', $text, $matches)) {
            $question['problemStatement'] = trim($matches[1]);
            $question['description'] = $question['problemStatement'];
        }

        if (preg_match('/CONSTRAINTS[:\s]*\s*(.+?)(?=(?:HINTS|TEST_CASES_JSON)|$)/is', $text, $matches)) {
            $lines = explode("\n", trim($matches[1]));
            foreach ($lines as $line) {
                $line = trim(preg_replace('/^[-•*]\s*/', '', $line));
                if (!empty($line) && stripos($line, 'TEST_CASES') === false) $question['constraints'][] = $line;
            }
        }

        // Parse Hint
        if (preg_match('/HINTS[:\s]*\s*(.+?)(?=(?:TEST_CASES_JSON|SOLUTION)|$)/is', $text, $matches)) {
            $question['hint'] = trim($matches[1]);
        }

        // Parse JSON
        $jsonString = '';
        if (preg_match('/TEST_CASES_JSON[:\s]*(\[.+\])/s', $text, $matches)) {
            $jsonString = $matches[1];
        } elseif (preg_match('/(\[\s*\{\s*"input".+\])/s', $text, $matches)) {
            $jsonString = $matches[1];
        }

        if (!empty($jsonString)) {
            $jsonString = preg_replace('/^```json\s*/i', '', trim($jsonString));
            $jsonString = preg_replace('/```\s*$/', '', $jsonString);
            $decoded = json_decode($jsonString, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $question['tests'] = $decoded;
            }
        }

        // Parse Solution Code
        if (preg_match('/SOLUTION[:\s]*\s*(?:```[\w]*\s*)?(.+?)(?:```|$)/s', $text, $matches)) {
            $question['solution'] = trim($matches[1]);
        }

        return $question;
    }
    
    private function parseQuestionEvaluation($text)
    {
        $evaluation = ['questionUnderReview' => '', 'evaluationPrompt' => '', 'choices' => [], 'correctAnswer' => ''];
        
        if (preg_match('/QUESTION_UNDER_REVIEW:\s*(.+?)(?=(?:EVALUATION_PROMPT|CHOICES)|$)/is', $text, $matches)) 
            $evaluation['questionUnderReview'] = trim($matches[1]);
        
        if (preg_match('/EVALUATION_PROMPT:\s*(.+?)(?=(?:CHOICES|CORRECT_ANSWER)|$)/is', $text, $matches)) {
            $evaluation['evaluationPrompt'] = trim($matches[1]);
        } else {
            $evaluation['evaluationPrompt'] = "Identify the main issue in the code above.";
        }
        
        if (preg_match('/CHOICES:\s*(.+?)(?=CORRECT_ANSWER:|$)/is', $text, $matches)) {
            preg_match_all('/([A-D])\)\s*(.+?)(?=[A-D]\)|$)/s', trim($matches[1]), $choiceMatches, PREG_SET_ORDER);
            foreach ($choiceMatches as $match) $evaluation['choices'][$match[1]] = trim($match[2]);
        }
        
        if (preg_match('/CORRECT_ANSWER:\s*([A-D])/i', $text, $matches)) 
            $evaluation['correctAnswer'] = strtoupper(trim($matches[1]));
        
        return $evaluation;
    }
    
    // --- SAVERS ---

    private function saveMCQQuestion($parsed, $language, $difficulty, $topic)
    {
        return Question::create([
            'title' => 'MCQ: ' . substr($parsed['question'], 0, 100),
            'content' => $parsed['question'],
            'description' => $parsed['question'],
            'answersData' => $parsed['correctAnswer'],
            'options' => $parsed['choices'],
            'status' => 'Approved',
            'language' => $language,
            'level' => $difficulty,
            'questionCategory' => 'competencyTest',
            'questionType' => 'MCQ_Question',
            'chapter' => $topic,
        ]);
    }
    
    /**
     * UPDATED: Saves hints and solution
     */
    private function saveCodeSolutionQuestion($parsed, $language, $difficulty, $topic)
    {
        $constraintsText = implode("\n", array_map(fn($c) => "- $c", $parsed['constraints']));
        
        $inputData = [];
        $expectedOutputData = [];

        foreach ($parsed['tests'] as $index => $test) {
            $inputData[] = ['test_case' => $index + 1, 'input' => $test['input']];
            $expectedOutputData[] = ['test_case' => $index + 1, 'output' => $test['output']];
        }

        $content = "# {$parsed['title']}\n\n## Description\n{$parsed['description']}\n\n## Constraints\n$constraintsText";

        return Question::create([
            'title' => $parsed['title'],
            'content' => $content,
            'description' => $parsed['description'],
            'problem_statement' => $parsed['problemStatement'],
            'constraints' => trim($constraintsText),
            'expected_output' => $expectedOutputData,
            
            // UPDATED: Now saves the actual solution code
            'answersData' => $parsed['solution'] ?? 'Solution generation failed',
            
            // UPDATED: Now saves the hint
            'hint' => $parsed['hint'] ?? null,
            
            'status' => 'Approved',
            'language' => $language,
            'level' => $difficulty,
            'questionCategory' => 'competencyTest',
            'questionType' => 'Code_Solution',
            'chapter' => $topic,
            'input' => $inputData,
        ]);
    }
    
    private function saveQuestionEvaluationQuestion($parsed, $language, $difficulty, $topic)
    {
        $fullQuestion = "Question Under Review:\n\n" . $parsed['questionUnderReview'] . "\n\n" . $parsed['evaluationPrompt'];

        return Question::create([
            'title' => 'Question Evaluation: ' . substr($parsed['evaluationPrompt'], 0, 80),
            'content' => $fullQuestion,
            'description' => $parsed['questionUnderReview'],
            'problem_statement' => $parsed['evaluationPrompt'],
            'answersData' => $parsed['correctAnswer'],
            'options' => $parsed['choices'],
            'status' => 'Approved',
            'language' => $language,
            'level' => $difficulty,
            'questionCategory' => 'competencyTest',
            'questionType' => 'Question_Evaluation',
            'chapter' => $topic,
        ]);
    }
}