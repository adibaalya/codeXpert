<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Question;

class GenerateMonthlyCompetencyQuestions extends Command
{
    protected $signature = 'questions:generate-monthly-competency';

    protected $description = 'Generate monthly competency test questions: 2 MCQs, 2 Code Solutions, and 2 Question Evaluations per language';

    public function handle()
    {
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
    
    private function generateMCQQuestions($language, $topics)
    {
        $difficulties = ['beginner', 'intermediate'];
        $success = 0;
        $failed = 0;
        
        foreach ($difficulties as $index => $difficulty) {
            try {
                $topic = $topics[array_rand($topics)];
                $this->line("  - MCQ ($difficulty)...");
                
                $question = $this->generateMCQ($language, $difficulty, $topic);
                
                if ($question) {
                    $success++;
                    $this->info("    Success");
                } else {
                    $failed++;
                    $this->error("    Failed");
                }
                
                sleep(10); 
                
            } catch (\Exception $e) {
                $failed++;
                $this->error("    Error: " . $e->getMessage());
            }
        }
        
        return ['success' => $success, 'failed' => $failed];
    }
    
    private function generateCodeSolutionQuestions($language, $topics)
    {
        $difficulties = ['beginner', 'intermediate'];
        $success = 0;
        $failed = 0;
        
        foreach ($difficulties as $index => $difficulty) {
            try {
                $topic = $topics[array_rand($topics)];
                $this->line("  - Code Solution ($difficulty)...");
                
                $question = $this->generateCodeSolution($language, $difficulty, $topic);
                
                if ($question) {
                    $success++;
                    $this->info("    Success");
                } else {
                    $failed++;
                    $this->error("    Failed");
                }
                
                sleep(10);
                
            } catch (\Exception $e) {
                $failed++;
                $this->error("    Error: " . $e->getMessage());
            }
        }
        
        return ['success' => $success, 'failed' => $failed];
    }
    
    private function generateQuestionEvaluationQuestions($language, $topics)
    {
        $difficulties = ['beginner', 'intermediate'];
        $success = 0;
        $failed = 0;
        
        foreach ($difficulties as $index => $difficulty) {
            try {
                $topic = $topics[array_rand($topics)];
                $this->line("  - Question Evaluation ($difficulty)...");
                
                $question = $this->generateQuestionEvaluation($language, $difficulty, $topic);
                
                if ($question) {
                    $success++;
                    $this->info("    Success");
                } else {
                    $failed++;
                    $this->error("    Failed");
                }
                
                sleep(10);
                
            } catch (\Exception $e) {
                $failed++;
                $this->error("    Error: " . $e->getMessage());
            }
        }
        
        return ['success' => $success, 'failed' => $failed];
    }
    
    private function generateMCQ($language, $difficulty, $topic)
    {
        $prompt = $this->buildMCQPrompt($language, $difficulty, $topic);
        $generatedText = $this->callGeminiAPIWithRetry($prompt);
        
        if (!$generatedText) {
            return null;
        }
        
        $parsed = $this->parseMCQ($generatedText);
        return $this->saveMCQQuestion($parsed, $language, $difficulty, $topic);
    }
    
    private function generateCodeSolution($language, $difficulty, $topic)
    {
        $prompt = $this->buildCodeSolutionPrompt($language, $difficulty, $topic);
        $generatedText = $this->callGeminiAPIWithRetry($prompt);
        
        if (!$generatedText) {
            return null;
        }
        
        $parsed = $this->parseCodeSolution($generatedText);
        return $this->saveCodeSolutionQuestion($parsed, $language, $difficulty, $topic);
    }
    
    private function generateQuestionEvaluation($language, $difficulty, $topic)
    {
        $prompt = $this->buildQuestionEvaluationPrompt($language, $difficulty, $topic);
        $generatedText = $this->callGeminiAPIWithRetry($prompt);
        
        if (!$generatedText) {
            return null;
        }
        
        $parsed = $this->parseQuestionEvaluation($generatedText);
        return $this->saveQuestionEvaluationQuestion($parsed, $language, $difficulty, $topic);
    }
    
    private function callGeminiAPIWithRetry($prompt)
    {
        $geminiApiKey = env('GEMINI_API_KEY');
        
        if (!$geminiApiKey) {
            throw new \Exception('Gemini API key not configured');
        }

        $maxRetries = 3;
        $attempt = 0;
        $baseDelay = 5;

        while ($attempt < $maxRetries) {
            $attempt++;
            
            try {
                $response = Http::timeout(60)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 2048,
                    ]
                ]);

                if ($response->status() === 429) {
                    $this->warn("    Rate limited (429). Retrying in " . ($baseDelay * $attempt) . " seconds...");
                    sleep($baseDelay * $attempt);
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
                if ($attempt === $maxRetries) {
                    Log::error('Gemini API Exception', ['message' => $e->getMessage()]);
                    return null;
                }
                sleep($baseDelay * $attempt);
            }
        }
        
        return null;
    }
    
    private function buildMCQPrompt($language, $difficulty, $topic)
    {
        $lengthInstruction = $difficulty === 'beginner' 
            ? "Keep the question very short (max 2 sentences)." 
            : "Keep the question concise (max 3 sentences).";

        $prompt = "You are an expert programming instructor.\n";
        $prompt .= "Generate an MCQ question.\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- Difficulty: {$difficulty}\n";
        $prompt .= "- Language: {$language}\n";
        $prompt .= "- Topic: {$topic}\n";
        $prompt .= "- {$lengthInstruction}\n";
        $prompt .= "- Provide 1 correct answer and 3 realistic distractors.\n";
        $prompt .= "- Do NOT include explanations.\n\n";
        
        $prompt .= "Output format:\n";
        $prompt .= "QUESTION:\n[Short question here]\n\n";
        $prompt .= "CHOICES:\n";
        $prompt .= "A) [Choice]\n";
        $prompt .= "B) [Choice]\n";
        $prompt .= "C) [Choice]\n";
        $prompt .= "D) [Choice]\n\n";
        $prompt .= "CORRECT_ANSWER:\n[A, B, C, or D]\n";
        
        return $prompt;
    }
    
    private function buildCodeSolutionPrompt($language, $difficulty, $topic)
    {
        $lengthInstruction = $difficulty === 'beginner' 
            ? "Keep the problem statement very simple and short (max 30 words)." 
            : "Keep the problem statement concise (max 60 words).";

        $prompt = "You are an expert programming instructor.\n";
        $prompt .= "Generate a coding question for a REVIEWER to assess.\n";
        $prompt .= "Difficulty: {$difficulty}\n";
        $prompt .= "Language: {$language}\n";
        $prompt .= "Topic: {$topic}\n";
        $prompt .= "{$lengthInstruction}\n\n";

        $prompt .= "Use the following structure EXACTLY:\n\n";
        $prompt .= "TITLE:\n[Short Title]\n\n";
        $prompt .= "DESCRIPTION:\n[1 sentence scenario]\n\n";
        $prompt .= "PROBLEM_STATEMENT:\n[Concise technical requirement]\n\n";
        $prompt .= "CONSTRAINTS:\n- Input parameters: [List]\n- Output: [Type]\n- Rules: [Brief rules]\n- Edge cases: [List 2]\n\n";
        $prompt .= "HINTS:\n1. [Hint]\n2. [Hint]\n\n";
        $prompt .= "INPUT:\nTest Case 1:\nInput: [Value]\nTest Case 2:\nInput: [Value]\nTest Case 3:\nInput: [Value]\nTest Case 4:\nInput: [Value]\n\n";
        $prompt .= "EXPECTED_OUTPUT:\nTest Case 1:\nOutput: [Value]\nTest Case 2:\nOutput: [Value]\nTest Case 3:\nOutput: [Value]\nTest Case 4:\nOutput: [Value]\n";

        return $prompt;
    }
    
    private function buildQuestionEvaluationPrompt($language, $difficulty, $topic)
    {
        $lengthInstruction = $difficulty === 'beginner' 
            ? "Make the flawed question very short and simple." 
            : "Make the flawed question concise.";

        $prompt = "Generate a multiple-choice question testing a reviewer's ability to spot errors in a coding question.\n";
        $prompt .= "Language: {$language}\n";
        $prompt .= "Topic: {$topic}\n";
        $prompt .= "Difficulty: {$difficulty}\n";
        $prompt .= "{$lengthInstruction}\n\n";
        
        $prompt .= "Output format:\n";
        $prompt .= "QUESTION_UNDER_REVIEW:\n[A short flawed programming question]\n\n";
        $prompt .= "EVALUATION_PROMPT:\nWhat is the main issue?\n\n";
        $prompt .= "CHOICES:\n";
        $prompt .= "A) [Choice]\n";
        $prompt .= "B) [Choice]\n";
        $prompt .= "C) [Choice]\n";
        $prompt .= "D) [Choice]\n\n";
        $prompt .= "CORRECT_ANSWER:\n[A, B, C, or D]\n";
        
        return $prompt;
    }
    
    private function parseMCQ($text)
    {
        $mcq = ['question' => '', 'choices' => [], 'correctAnswer' => ''];
        
        if (preg_match('/QUESTION:\s*(.+?)(?=CHOICES:|$)/s', $text, $matches)) {
            $mcq['question'] = trim($matches[1]);
        }
        
        if (preg_match('/CHOICES:\s*(.+?)(?=CORRECT_ANSWER:|$)/s', $text, $matches)) {
            $choicesText = trim($matches[1]);
            preg_match_all('/([A-D])\)\s*(.+?)(?=[A-D]\)|$)/s', $choicesText, $choiceMatches, PREG_SET_ORDER);
            foreach ($choiceMatches as $match) {
                $mcq['choices'][$match[1]] = trim($match[2]);
            }
        }
        
        if (preg_match('/CORRECT_ANSWER:\s*([A-D])/i', $text, $matches)) {
            $mcq['correctAnswer'] = strtoupper(trim($matches[1]));
        }
        
        return $mcq;
    }
    
    private function parseCodeSolution($text)
    {
        $question = [
            'title' => '',
            'description' => '',
            'problemStatement' => '',
            'constraints' => [],
            'hints' => '',
            'tests' => []
        ];

        if (preg_match('/TITLE:\s*(.+?)(?=DESCRIPTION:|$)/s', $text, $matches)) {
            $question['title'] = trim($matches[1]);
        }

        if (preg_match('/DESCRIPTION:\s*(.+?)(?=PROBLEM_STATEMENT:|$)/s', $text, $matches)) {
            $question['description'] = trim($matches[1]);
        }

        if (preg_match('/PROBLEM_STATEMENT:\s*(.+?)(?=CONSTRAINTS:|$)/s', $text, $matches)) {
            $question['problemStatement'] = trim($matches[1]);
        }

        if (preg_match('/CONSTRAINTS:\s*(.+?)(?=HINTS:|$)/s', $text, $matches)) {
            $constraintsText = trim($matches[1]);
            $lines = explode("\n", $constraintsText);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && $line !== '-' && !preg_match('/^(Input parameters|Output|Rules|Edge cases):$/i', $line)) {
                    $line = preg_replace('/^[-•]\s*/', '', $line);
                    if (!empty($line)) {
                        $question['constraints'][] = $line;
                    }
                }
            }
        }

        if (preg_match('/HINTS:\s*(.+?)(?=INPUT:|$)/s', $text, $matches)) {
            $question['hints'] = trim($matches[1]);
        }

        if (preg_match('/INPUT:\s*(.+?)(?=EXPECTED_OUTPUT:|$)/s', $text, $matches)) {
            $inputText = $matches[1];
            preg_match_all('/Test Case (\d+)[^:]*:\s*(?:Input:\s*)?(.+?)(?=Test Case \d+|$)/s', $inputText, $inputMatches, PREG_SET_ORDER);
            
            foreach ($inputMatches as $match) {
                $testCaseNum = (int)$match[1];
                $inputValue = trim($match[2]);
                $inputValue = preg_replace('/^Input:\s*/i', '', $inputValue);
                
                $question['tests'][$testCaseNum - 1] = [
                    'input' => trim($inputValue),
                    'output' => ''
                ];
            }
        }

        if (preg_match('/EXPECTED_OUTPUT:\s*(.+?)(?=IMPORTANT:|$)/s', $text, $matches)) {
            $outputText = $matches[1];
            preg_match_all('/Test Case (\d+)[^:]*:\s*(?:Output:\s*)?(.+?)(?=Test Case \d+|$)/s', $outputText, $outputMatches, PREG_SET_ORDER);
            
            foreach ($outputMatches as $match) {
                $testCaseNum = (int)$match[1];
                $outputValue = trim($match[2]);
                $outputValue = preg_replace('/^Output:\s*/i', '', $outputValue);
                
                if (isset($question['tests'][$testCaseNum - 1])) {
                    $question['tests'][$testCaseNum - 1]['output'] = trim($outputValue);
                }
            }
        }

        return $question;
    }
    
    private function parseQuestionEvaluation($text)
    {
        $evaluation = ['questionUnderReview' => '', 'evaluationPrompt' => '', 'choices' => [], 'correctAnswer' => ''];
        
        if (preg_match('/QUESTION_UNDER_REVIEW:\s*(.+?)(?=EVALUATION_PROMPT:|$)/s', $text, $matches)) {
            $evaluation['questionUnderReview'] = trim($matches[1]);
        }
        
        if (preg_match('/EVALUATION_PROMPT:\s*(.+?)(?=CHOICES:|$)/s', $text, $matches)) {
            $evaluation['evaluationPrompt'] = trim($matches[1]);
        }
        
        if (preg_match('/CHOICES:\s*(.+?)(?=CORRECT_ANSWER:|$)/s', $text, $matches)) {
            $choicesText = trim($matches[1]);
            preg_match_all('/([A-D])\)\s*(.+?)(?=[A-D]\)|$)/s', $choicesText, $choiceMatches, PREG_SET_ORDER);
            foreach ($choiceMatches as $match) {
                $evaluation['choices'][$match[1]] = trim($match[2]);
            }
        }
        
        if (preg_match('/CORRECT_ANSWER:\s*([A-D])/i', $text, $matches)) {
            $evaluation['correctAnswer'] = strtoupper(trim($matches[1]));
        }
        
        return $evaluation;
    }
    
    private function saveMCQQuestion($parsed, $language, $difficulty, $topic)
    {
        $levelMap = ['beginner' => 'beginner', 'intermediate' => 'intermediate'];
        $mappedLevel = $levelMap[$difficulty] ?? 'intermediate';

        return Question::create([
            'title' => 'MCQ: ' . substr($parsed['question'], 0, 100),
            'content' => $parsed['question'],
            'description' => $parsed['question'],
            'problem_statement' => null,
            'constraints' => null,
            'expected_output' => null,
            'answersData' => $parsed['correctAnswer'],
            'options' => $parsed['choices'],
            'status' => 'Approved',
            'reviewer_ID' => null,
            'language' => $language,
            'level' => $mappedLevel,
            'questionCategory' => 'competencyTest',
            'questionType' => 'MCQ_Question',
            'chapter' => $topic,
            'hint' => null,
            'input' => null,
        ]);
    }
    
    private function saveCodeSolutionQuestion($parsed, $language, $difficulty, $topic)
    {
        $levelMap = ['beginner' => 'beginner', 'intermediate' => 'intermediate'];
        $mappedLevel = $levelMap[$difficulty] ?? 'intermediate';

        $constraintsText = '';
        foreach ($parsed['constraints'] as $constraint) {
            $constraintsText .= "- {$constraint}\n";
        }

        $inputData = [];
        foreach ($parsed['tests'] as $index => $test) {
            $inputData[] = [
                'test_case' => $index + 1,
                'input' => $test['input']
            ];
        }

        $expectedOutputData = [];
        foreach ($parsed['tests'] as $index => $test) {
            $expectedOutputData[] = [
                'test_case' => $index + 1,
                'output' => $test['output']
            ];
        }

        $content = "# {$parsed['title']}\n\n";
        $content .= "## Description\n{$parsed['description']}\n\n";
        $content .= "## Problem Statement\n{$parsed['problemStatement']}\n\n";
        $content .= "## Constraints\n" . $constraintsText;

        return Question::create([
            'title' => $parsed['title'],
            'content' => $content,
            'description' => $parsed['description'],
            'problem_statement' => $parsed['problemStatement'],
            'constraints' => trim($constraintsText),
            'expected_output' => $expectedOutputData,
            'answersData' => 'TO_BE_EVALUATED_BY_REVIEWER',
            'status' => 'Approved',
            'reviewer_ID' => null,
            'language' => $language,
            'level' => $mappedLevel,
            'questionCategory' => 'competencyTest',
            'questionType' => 'Code_Solution',
            'chapter' => $topic,
            'hint' => $parsed['hints'],
            'input' => $inputData,
        ]);
    }
    
    private function saveQuestionEvaluationQuestion($parsed, $language, $difficulty, $topic)
    {
        $levelMap = ['beginner' => 'beginner', 'intermediate' => 'intermediate'];
        $mappedLevel = $levelMap[$difficulty] ?? 'intermediate';

        $fullQuestion = "Question Under Review:\n\n" . $parsed['questionUnderReview'] . "\n\n";
        $fullQuestion .= $parsed['evaluationPrompt'];

        return Question::create([
            'title' => 'Question Evaluation: ' . substr($parsed['evaluationPrompt'], 0, 80),
            'content' => $fullQuestion,
            'description' => $parsed['questionUnderReview'],
            'problem_statement' => $parsed['evaluationPrompt'],
            'constraints' => null,
            'expected_output' => null,
            'answersData' => $parsed['correctAnswer'],
            'options' => $parsed['choices'],
            'status' => 'Approved',
            'reviewer_ID' => null,
            'language' => $language,
            'level' => $mappedLevel,
            'questionCategory' => 'competencyTest',
            'questionType' => 'Question_Evaluation',
            'chapter' => $topic,
            'hint' => null,
            'input' => null,
        ]);
    }
}