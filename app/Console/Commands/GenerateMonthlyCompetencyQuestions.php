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
        // Customizing focus based on language to make it authentic
        $focusArea = "Best Practices, Architecture, and Language Internals";
        if (in_array($language, ['C', 'C++'])) {
            $focusArea = "Memory Management, Pointers, and Compilation";
        } elseif ($language === 'JavaScript') {
            $focusArea = "Asynchronous operations, Scopes, and Event Loop";
        } elseif ($language === 'PHP') {
            $focusArea = "Security (XSS/CSRF/SQLi), Request Lifecycle, and Type Juggling";
        } elseif ($language === 'Java') {
            $focusArea = "JVM, Multithreading, and OOP Principles";
        }

        return "Act as a Senior Software Engineer. Generate a theoretical MCQ to test a Code Reviewer's expert knowledge in {$language}.\n" .
               "Context: A reviewer needs to know WHY code works, not just how to write it.\n" .
               "Topic: {$topic}\n" .
               "Difficulty: {$difficulty}\n" .
               "Focus On: {$focusArea}.\n" .
               "- The question MUST be conceptual (e.g., 'What is the risk of...?', 'Which design pattern...?').\n" .
               "- Avoid simple syntax questions like 'How to declare a variable?'.\n" .
               "- Output format:\n" .
               "QUESTION:\n[A challenging conceptual question]\n\n" .
               "CHOICES:\nA) [Choice]\nB) [Choice]\nC) [Choice]\nD) [Choice]\n\n" .
               "CORRECT_ANSWER:\n[A, B, C, or D]\n";
    }
    
    /**
     * UPDATED: Added HINTS and SOLUTION to prompt
     */
    private function buildCodeSolutionPrompt($language, $difficulty, $topic)
    {
        return "Act as a Technical Lead. Generate a 'Code Debugging Challenge' for {$language} ({$difficulty}) on {$topic}.\n" .
               "SCENARIO: A junior developer has submitted a function that runs but produces incorrect results (Logic Bug).\n" .
               "YOUR GOAL: Provide the buggy code and ask the reviewer to fix it.\n" .
               "1. The 'PROBLEM_STATEMENT' must clearly state what the function IS SUPPOSED to do, and show the BUGGY code block.\n" .
               "2. The 'SOLUTION' must be the corrected version of that function.\n" .
               "3. 'TEST_CASES_JSON' must contain inputs and expected outputs for the CORRECTED version.\n\n" .
               
               "STRICT REQUIREMENTS:\n" .
               "- The bug should be logical (e.g., < instead of <=, missing return, wrong variable usage), NOT just a missing semicolon.\n" .
               "- **MUST PROVIDE AT LEAST 3 DISTINCT TEST CASES** (including one edge case).\n\n" .

               "IMPORTANT FORMATTING:\n" .
               "1. Use proper indentation (4 spaces) for all code blocks.\n" .
               "2. Always use triple backticks with the language identifier (e.g. ```{$language}).\n\n" .
               
               "- Output format:\n" .
               "TITLE:\nDebug: [Function Name]\n\n" .
               "FUNCTION_NAME:\n[functionName]\n\n" .
               "PROBLEM_STATEMENT:\nThe following function is intended to [Goal], but it fails for edge cases. Identify and fix the logic bug.\n\n```{$language}\n[Insert Properly Indented Buggy Code]\n```\n\n" .
               "CONSTRAINTS:\n- Keep the function signature unchanged.\n- Fix the logic error.\n\n" .
               "HINTS:\n[Hint regarding the specific logic error]\n\n" .
               "TEST_CASES_JSON:\n" .
               "[\n" .
               "  { \"input\": { \"arg1\": ... }, \"output\": ... },\n" .
               "  { \"input\": { \"arg1\": ... }, \"output\": ... },\n" .
               "  { \"input\": { \"arg1\": ... }, \"output\": ... }\n" .
               "]\n\n" .
               "SOLUTION:\n```{$language}\n[The Fully Corrected Code with Proper Indentation]\n```\n";
    }
    
    private function buildQuestionEvaluationPrompt($language, $difficulty, $topic)
    {
        return "Act as a Technical Lead. Generate a 'Code Review' MCQ for {$language} on {$topic}.\n" .
               "Task: Create a code snippet that LOOKS correct but contains a HIDDEN DEFECT.\n" .
               "The defect should be one of: Security Vulnerability, Logic Error (Off-by-one), Memory Leak, or Performance Issue.\n" .
               "Constraint: The code snippet must be 3-8 lines long.\n\n" .
               
               // FORMATTING INSTRUCTIONS
               "IMPORTANT FORMATTING:\n" .
               "1. The code snippet MUST use proper indentation (4 spaces or tabs).\n" .
               "2. Wrap the code snippet in triple backticks with the language identifier (e.g. ```{$language} ... ```).\n" .
               "3. Ensure the code structure is clean and readable.\n\n" .
               
               "- Output format:\n" .
               "QUESTION_UNDER_REVIEW:\n```{$language}\n[Properly indented code snippet with hidden bug]\n```\n\n" .
               "EVALUATION_PROMPT:\n[Question: What is the primary reason to reject this code?]\n\n" .
               "CHOICES:\nA) [Plausible but incorrect reason]\nB) [Plausible but incorrect reason]\nC) [The Actual Defect]\nD) [Plausible but incorrect reason]\n\n" .
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
            'hint' => '',
            'tests' => [],
            'solution' => ''
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

        // IMPROVED: More flexible JSON parsing with better error handling
        $jsonString = '';
        
        // Method 1: Look for TEST_CASES_JSON followed by JSON array
        if (preg_match('/TEST_CASES_JSON[:\s]*\n*\s*(\[\s*\{.+?\}\s*\])/s', $text, $matches)) {
            $jsonString = $matches[1];
            \Log::info('JSON Method 1 matched', ['json' => substr($jsonString, 0, 200)]);
        }
        // Method 2: Look for any JSON array with "input" and "output" keys
        elseif (preg_match('/(\[\s*\{\s*["\']input["\'].+?\}\s*\])/s', $text, $matches)) {
            $jsonString = $matches[1];
            \Log::info('JSON Method 2 matched', ['json' => substr($jsonString, 0, 200)]);
        }
        // Method 3: Extract everything between TEST_CASES_JSON and SOLUTION/end
        elseif (preg_match('/TEST_CASES_JSON[:\s]*\n*(.+?)(?=SOLUTION|$)/s', $text, $matches)) {
            $extracted = trim($matches[1]);
            // Remove markdown code blocks if present
            $extracted = preg_replace('/```json\s*/i', '', $extracted);
            $extracted = preg_replace('/```\s*$/s', '', $extracted);
            // Try to find the array in the extracted text
            if (preg_match('/(\[\s*\{.+?\}\s*\])/s', $extracted, $arrayMatch)) {
                $jsonString = $arrayMatch[1];
                \Log::info('JSON Method 3 matched', ['json' => substr($jsonString, 0, 200)]);
            }
        }

        if (!empty($jsonString)) {
            // Clean up the JSON string
            $jsonString = preg_replace('/^```json\s*/i', '', trim($jsonString));
            $jsonString = preg_replace('/```\s*$/s', '', $jsonString);
            $jsonString = trim($jsonString);
            
            \Log::info('Attempting to decode JSON', ['json_preview' => substr($jsonString, 0, 300)]);
            
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                $question['tests'] = $decoded;
                \Log::info('JSON decoded successfully', ['test_count' => count($decoded)]);
            } else {
                \Log::error('JSON decode failed', [
                    'error' => json_last_error_msg(),
                    'json_preview' => substr($jsonString, 0, 500)
                ]);
            }
        } else {
            \Log::warning('No JSON string found in response', ['text_preview' => substr($text, 0, 1000)]);
        }

        // Parse Solution Code - more flexible pattern
        if (preg_match('/SOLUTION[:\s]*\n*\s*```[\w]*\n(.+?)```/s', $text, $matches)) {
            $question['solution'] = trim($matches[1]);
        } elseif (preg_match('/SOLUTION[:\s]*\n*\s*(.+?)(?=$)/s', $text, $matches)) {
            // Fallback: grab everything after SOLUTION to end of text
            $solution = trim($matches[1]);
            // Remove any leading markdown code blocks
            $solution = preg_replace('/^```[\w]*\s*/i', '', $solution);
            $solution = preg_replace('/```\s*$/s', '', $solution);
            $question['solution'] = trim($solution);
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