<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use Illuminate\Support\Facades\File;

class ImportQuestionsFromJson extends Command
{
    protected $signature = 'questions:import-json';
    protected $description = 'Import questions from database/data/questions.json';

    public function handle()
    {
        $path = database_path('data/questions.json');

        if (!File::exists($path)) {
            $this->error("File not found: $path");
            $this->info("Please create the file and paste your JSON array inside.");
            return 1;
        }

        $json = File::get($path);
        $questions = json_decode($json, true);

        if (!$questions) {
            $this->error("Invalid JSON format. Make sure it is a valid array [ ... ].");
            return 1;
        }

        $count = 0;
        foreach ($questions as $q) {
            
            // 1. Prepare Data for Code Solutions (Weekly/Monthly)
            $inputData = null;
            $expectedOutputData = null;
            $constraintsText = null;

            if ($q['questionType'] === 'Code_Solution' && isset($q['tests'])) {
                $inputData = [];
                $expectedOutputData = [];
                foreach ($q['tests'] as $index => $test) {
                    $inputData[] = ['test_case' => $index + 1, 'input' => $test['input']];
                    $expectedOutputData[] = ['test_case' => $index + 1, 'output' => $test['output']];
                }
                
                if (isset($q['constraints']) && is_array($q['constraints'])) {
                    $constraintsText = implode("\n", array_map(fn($c) => "- $c", $q['constraints']));
                }
            }

            // 2. Prepare Data for MCQ / Evaluation (Monthly)
            $options = isset($q['options']) ? $q['options'] : null;
            
            // 3. Create Record
            Question::create([
                'title' => $q['title'],
                'function_name' => $q['function_name'] ?? null,
                
                // Content Fallback: If content isn't provided (like in Code Solutions), build it from Desc + Prob Statement
                'content' => $q['content'] ?? ($q['description'] . "\n\n" . ($q['problem_statement'] ?? '')),
                
                'description' => $q['description'] ?? null,
                'problem_statement' => $q['problem_statement'] ?? null,
                'constraints' => $constraintsText,
                'expected_output' => $expectedOutputData, // Laravel casts to JSON
                'input' => $inputData, // Laravel casts to JSON
                
                // Map "solution" (Code) or "answersData" (MCQ/Eval) to the same DB column
                'answersData' => $q['answersData'] ?? ($q['solution'] ?? null),
                'options' => $options, // Laravel casts to JSON
                
                'status' => 'Approved',
                'language' => $q['language'],
                'level' => $q['difficulty'],
                'chapter' => $q['topic'],
                
                // ADDED: Map the hint field
                'hint' => $q['hint'] ?? null,
                
                // CRITICAL FIELDS
                'questionCategory' => $q['questionCategory'],
                'questionType' => $q['questionType'],
            ]);
            
            $count++;
        }

        $this->info("Successfully imported {$count} questions!");
        return 0;
    }
}