<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class QuestionsJsonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/questions.json');
        
        if (!File::exists($jsonPath)) {
            $this->command->error("questions.json file not found at: {$jsonPath}");
            return;
        }
        
        $jsonContent = File::get($jsonPath);
        $questionsData = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Error parsing JSON: " . json_last_error_msg());
            return;
        }
        
        // The JSON structure has nested arrays, get the first element
        if (isset($questionsData[0]) && is_array($questionsData[0])) {
            $questionsData = $questionsData[0];
        }
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($questionsData as $questionData) {
            try {
                // Check if question already exists (by title and language)
                $exists = Question::where('title', $questionData['title'])
                    ->where('language', $questionData['language'])
                    ->exists();
                
                if ($exists) {
                    $this->command->warn("Skipping duplicate: {$questionData['title']}");
                    $skipped++;
                    continue;
                }
                
                // Create the question
                Question::create($questionData);
                $this->command->info("Imported: {$questionData['title']}");
                $imported++;
                
            } catch (\Exception $e) {
                $this->command->error("Failed to import question: {$questionData['title']}");
                $this->command->error("Error: " . $e->getMessage());
                Log::error('Question import failed', [
                    'question' => $questionData['title'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $skipped++;
            }
        }
        
        $this->command->info("\n=== Import Complete ===");
        $this->command->info("Imported: {$imported}");
        $this->command->info("Skipped: {$skipped}");
    }
}
