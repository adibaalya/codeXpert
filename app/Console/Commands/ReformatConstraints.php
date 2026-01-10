<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;

class ReformatConstraints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'constraints:reformat {--dry-run : Preview changes without applying them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reformat question constraints to use bold labels and dashes for better display';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 Running in DRY RUN mode - no changes will be saved');
            $this->newLine();
        }

        // Get all questions with constraints
        $questions = Question::whereNotNull('constraints')->get();
        
        if ($questions->isEmpty()) {
            $this->warn('No questions with constraints found in the database.');
            return 0;
        }

        $this->info("Found {$questions->count()} questions with constraints");
        $this->newLine();

        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($questions as $question) {
            $constraints = is_string($question->constraints) 
                ? json_decode($question->constraints, true) 
                : $question->constraints;

            if (!is_array($constraints)) {
                $this->warn("Skipping Question ID {$question->question_ID}: Invalid constraints format");
                $skippedCount++;
                continue;
            }

            // Check if already formatted
            if ($this->isAlreadyFormatted($constraints)) {
                $this->line("⏭️  Question ID {$question->question_ID}: Already formatted");
                $skippedCount++;
                continue;
            }

            // Reformat the constraints
            $formattedConstraints = $this->reformatConstraints($constraints);

            $this->info("✨ Question ID {$question->question_ID}:");
            $this->line("   Title: {$question->title}");
            $this->line("   Before: " . json_encode($constraints));
            $this->line("   After: " . json_encode($formattedConstraints));
            $this->newLine();

            if (!$isDryRun) {
                $question->constraints = json_encode($formattedConstraints);
                $question->save();
                $updatedCount++;
            }
        }

        $this->newLine();
        if ($isDryRun) {
            $this->info("✅ Dry run completed. {$questions->count()} questions checked, {$skippedCount} already formatted.");
            $this->info("💡 Run without --dry-run to apply changes.");
        } else {
            $this->info("✅ Reformatting completed!");
            $this->info("   Updated: {$updatedCount} questions");
            $this->info("   Skipped: {$skippedCount} questions");
        }

        return 0;
    }

    /**
     * Check if constraints are already formatted with HTML tags
     */
    private function isAlreadyFormatted(array $constraints): bool
    {
        foreach ($constraints as $constraint) {
            if (is_string($constraint) && strpos($constraint, '<strong>') !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reformat constraints array to use bold labels and dashes
     */
    private function reformatConstraints(array $constraints): array
    {
        $formatted = [];
        $sections = [
            'input' => 'Input parameters',
            'output' => 'Output',
            'rules' => 'Rules',
            'edge' => 'Edge cases',
            'constraints' => 'Constraints',
            'requirements' => 'Requirements',
            'note' => 'Note',
            'examples' => 'Examples'
        ];

        foreach ($constraints as $constraint) {
            if (!is_string($constraint)) {
                $formatted[] = $constraint;
                continue;
            }

            // Detect if this is a section header (contains a colon at the start)
            $formattedConstraint = $constraint;
            
            // Check for common section patterns
            foreach ($sections as $key => $label) {
                $patterns = [
                    "/^({$label}:)/i",
                    "/^({$label} -)/i",
                    "/^({$key}:)/i",
                ];
                
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $constraint, $matches)) {
                        // Replace the label with bold version
                        $formattedConstraint = preg_replace(
                            $pattern,
                            "<strong>{$label}:</strong>",
                            $constraint,
                            1
                        );
                        break 2;
                    }
                }
            }

            // If the constraint has multiple lines, ensure each sub-item starts with a dash
            if (strpos($formattedConstraint, "\n") !== false) {
                $lines = explode("\n", $formattedConstraint);
                $processedLines = [];
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    // If line doesn't start with dash or is a section header, keep as is
                    if (strpos($line, '<strong>') !== false || 
                        preg_match('/^[\-\•\*]/', $line) || 
                        empty($processedLines)) {
                        $processedLines[] = $line;
                    } else {
                        // Add dash if it's a sub-item
                        $processedLines[] = '- ' . $line;
                    }
                }
                
                $formattedConstraint = implode("\n", $processedLines);
            }

            $formatted[] = $formattedConstraint;
        }

        return $formatted;
    }
}
