<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Question;

class GenerateWeeklyQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questions:generate-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate 5 questions for each programming language every week';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting weekly question generation...');
        
        // Define languages and their distribution
        $languages = ['Python', 'Java', 'JavaScript', 'PHP', 'C#'];
        
        // Define difficulty levels (5 questions per language)
        $difficulties = ['beginner', 'beginner', 'intermediate', 'intermediate', 'advanced'];
        
        // Define generic prompts for different programming concepts
        $prompts = [
            'Create a practical coding problem involving data manipulation and basic operations',
            'Design a question that tests fundamental programming logic and problem-solving skills',
            'Generate a real-world scenario that requires algorithmic thinking',
            'Create a challenge focused on data structure manipulation',
            'Design a problem that combines multiple programming concepts'
        ];
        
        $totalGenerated = 0;
        $totalFailed = 0;
        
        foreach ($languages as $language) {
            $this->info("\nGenerating questions for {$language}...");
            
            foreach ($difficulties as $index => $difficulty) {
                try {
                    $prompt = $prompts[$index];
                    
                    $this->line("  - Generating {$difficulty} level question...");
                    
                    $question = $this->generateQuestion($prompt, $language, $difficulty);
                    
                    if ($question) {
                        $totalGenerated++;
                        $this->info("    ✓ Successfully generated and saved!");
                    } else {
                        $totalFailed++;
                        $this->error("    ✗ Failed to generate question");
                    }
                    
                    // Small delay to avoid rate limiting
                    sleep(2);
                    
                } catch (\Exception $e) {
                    $totalFailed++;
                    $this->error("    ✗ Error: " . $e->getMessage());
                    Log::error('Weekly Question Generation Error', [
                        'language' => $language,
                        'difficulty' => $difficulty,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        $this->newLine();
        $this->info("===========================================");
        $this->info("Weekly Question Generation Complete!");
        $this->info("Successfully generated: {$totalGenerated} questions");
        if ($totalFailed > 0) {
            $this->warn("Failed: {$totalFailed} questions");
        }
        $this->info("===========================================");
        
        return 0;
    }
    
    private function generateQuestion($prompt, $language, $difficulty)
    {
        $geminiApiKey = env('GEMINI_API_KEY');
        
        if (!$geminiApiKey) {
            throw new \Exception('Gemini API key not configured');
        }
        
        // Build the prompt
        $systemPrompt = $this->buildPrompt($prompt, $language, $difficulty);
        
        // Call Gemini API
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
                'maxOutputTokens' => 2048,
            ]
        ]);
        
        if ($response->failed()) {
            Log::error('Gemini API Error in Weekly Generation', ['response' => $response->body()]);
            return null;
        }
        
        $result = $response->json();
        
        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return null;
        }
        
        $generatedText = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // Parse and save the question
        $parsedQuestion = $this->parseGeneratedQuestion($generatedText);
        
        return $this->saveQuestion($parsedQuestion, $language, $difficulty);
    }
    
    private function buildPrompt($userPrompt, $language, $difficulty)
    {
        $prompt  = "You are an expert programming instructor who creates real-world, single-file coding interview questions.\n\n";
        $prompt .= "Generate a simple real-world coding question suitable for a technical interview.\n\n";

        $prompt .= "Difficulty: {$difficulty}\n";
        $prompt .= "Language: {$language}\n";
        $prompt .= "User Request: {$userPrompt}\n\n";

        $prompt .= "Use the following structure EXACTLY:\n\n";

        $prompt .= "TITLE:\n";
        $prompt .= "[Generate a clear, concise, and descriptive title for this coding question. The title should be specific and indicate what the problem is about. Examples: 'Find Maximum Subarray Sum', 'Implement Binary Search Tree', 'Validate Parentheses']\n\n";

        $prompt .= "LANGUAGE:\n";
        $prompt .= "[Use: {$language}]\n\n";

        $prompt .= "TOPIC:\n";
        $prompt .= "[Generate ONE specific programming concept/category that best describes this question. Choose from concepts like: Array, String, Loop, Sorting, Searching, Recursion, Dynamic Programming, Hash Table, Stack, Queue, Linked List, Tree, Graph, Greedy Algorithm, Backtracking, Bit Manipulation, Math, Two Pointers, Sliding Window, Divide and Conquer, or other relevant programming concepts. Use Title Case (e.g., 'Hash Table', 'Dynamic Programming')]\n\n";

        $prompt .= "DIFFICULTY:\n{$difficulty}\n\n";

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
        $prompt .= "[Provide a complete, correct, working solution in {$language}. Must fit inside ONE FILE only. No external libraries unless built-in.]\n\n";

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
        }

        // Extract description
        if (preg_match('/DESCRIPTION:\s*(.+?)(?=PROBLEM_STATEMENT:|$)/s', $text, $matches)) {
            $question['description'] = trim($matches[1]);
        }

        // Extract problem statement
        if (preg_match('/PROBLEM_STATEMENT:\s*(.+?)(?=CONSTRAINTS:|$)/s', $text, $matches)) {
            $question['problemStatement'] = trim($matches[1]);
        }

        // Extract constraints
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

        // Extract hints
        if (preg_match('/HINTS:\s*(.+?)(?=INPUT:|$)/s', $text, $matches)) {
            $hintsText = trim($matches[1]);
            $question['expectedApproach'] = $hintsText;
        }

        // Extract input test cases - improved parsing
        if (preg_match('/INPUT:\s*(.+?)(?=EXPECTED_OUTPUT:|$)/s', $text, $matches)) {
            $inputText = $matches[1];
            preg_match_all('/Test Case (\d+)[^:]*:\s*(?:Input:\s*)?(.+?)(?=Test Case \d+|$)/s', $inputText, $inputMatches, PREG_SET_ORDER);
            
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
        }

        // Extract expected output - improved parsing
        if (preg_match('/EXPECTED_OUTPUT:\s*(.+?)(?=SOLUTION:|$)/s', $text, $matches)) {
            $outputText = $matches[1];
            preg_match_all('/Test Case (\d+)[^:]*:\s*(?:Output:\s*)?(.+?)(?=Test Case \d+|$)/s', $outputText, $outputMatches, PREG_SET_ORDER);
            
            foreach ($outputMatches as $match) {
                $testCaseNum = (int)$match[1];
                $outputValue = trim($match[2]);
                // Remove "Output:" label if it appears at the start
                $outputValue = preg_replace('/^Output:\s*/i', '', $outputValue);
                
                if (isset($question['tests'][$testCaseNum - 1])) {
                    $question['tests'][$testCaseNum - 1]['output'] = trim($outputValue);
                }
            }
        }

        // Extract solution
        if (preg_match('/SOLUTION:\s*```[\w]*\s*(.+?)```/s', $text, $matches)) {
            $question['solution'] = trim($matches[1]);
        } elseif (preg_match('/SOLUTION:\s*(.+?)(?=IMPORTANT:|$)/s', $text, $matches)) {
            $solutionText = trim($matches[1]);
            $solutionText = preg_replace('/```[\w]*\s*/', '', $solutionText);
            $solutionText = preg_replace('/```\s*$/', '', $solutionText);
            $question['solution'] = trim($solutionText);
        }

        // Fallback values
        if (empty($question['title'])) {
            $question['title'] = 'Generated Coding Question';
        }
        if (empty($question['description'])) {
            $question['description'] = 'Please solve the following coding problem.';
        }
        if (empty($question['problemStatement'])) {
            $question['problemStatement'] = $question['description'];
        }
        if (empty($question['constraints'])) {
            $question['constraints'] = ['Input size: 1 ≤ n ≤ 10^3'];
        }
        if (empty($question['expectedApproach'])) {
            $question['expectedApproach'] = 'Follow the requirements and hints provided.';
        }
        if (empty($question['tests'])) {
            $question['tests'][] = [
                'input' => 'Example input',
                'output' => 'Example output',
                'explanation' => ''
            ];
        }

        return $question;
    }
    
    private function saveQuestion($parsedQuestion, $language, $difficulty)
    {
        // Map difficulty to level
        $levelMap = [
            'easy' => 'beginner',
            'beginner' => 'beginner',
            'intermediate' => 'intermediate',
            'hard' => 'advanced',
            'advanced' => 'advanced'
        ];

        $level = strtolower($difficulty);
        $mappedLevel = $levelMap[$level] ?? 'intermediate';

        // Prepare constraints as a string
        $constraintsText = '';
        foreach ($parsedQuestion['constraints'] as $constraint) {
            $constraintsText .= "- {$constraint}\n";
        }

        // Prepare input test cases as array (proper structure for database)
        $inputData = [];
        foreach ($parsedQuestion['tests'] as $index => $test) {
            $inputData[] = [
                'test_case' => $index + 1,
                'input' => $test['input']
            ];
        }

        // Prepare expected output as array (proper structure for database)
        $expectedOutputData = [];
        foreach ($parsedQuestion['tests'] as $index => $test) {
            $expectedOutputData[] = [
                'test_case' => $index + 1,
                'output' => $test['output']
            ];
        }

        // Legacy content field for backward compatibility
        $content = "# {$parsedQuestion['title']}\n\n";
        $content .= "## Description\n{$parsedQuestion['description']}\n\n";
        $content .= "## Problem Statement\n{$parsedQuestion['problemStatement']}\n\n";
        $content .= "## Constraints\n" . $constraintsText;
        $content .= "\n## Hints\n{$parsedQuestion['expectedApproach']}";

        return Question::create([
            'title' => $parsedQuestion['title'],
            'content' => $content, // Legacy field
            'description' => $parsedQuestion['description'],
            'problem_statement' => $parsedQuestion['problemStatement'],
            'constraints' => trim($constraintsText),
            'expected_output' => $expectedOutputData, // Now stored as array
            'answersData' => $parsedQuestion['solution'],
            'status' => 'Pending',
            'reviewer_ID' => null,
            'language' => $language,
            'level' => $mappedLevel,
            'questionCategory' => 'learnerPractice',
            'questionType' => 'Code_Solution',
            'chapter' => $parsedQuestion['topic'],
            'hint' => $parsedQuestion['expectedApproach'],
            'input' => $inputData, // Stored as array with proper structure
        ]);
    }
}
