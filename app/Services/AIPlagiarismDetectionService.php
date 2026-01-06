<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AIPlagiarismDetectionService
{
    /**
     * Detection method: 'codebert' or 'tfidf'
     */
    private $detectionMethod = 'tfidf'; // Switched to TF-IDF to prevent timeouts

    /**
     * Analyze code against known AI-generated solutions using CodeBERT Semantic Similarity
     * 
     * @param string $code The student's submitted code
     * @param string $language Programming language used
     * @param int $questionId The question ID to match ghost solutions
     * @return array Contains ai_probability (0-100), reason, and analysis details
     */
    public function analyzeCode(string $code, string $language, int $questionId = null): array
    {
        try {
            // Get ghost solutions directory for this question
            $ghostDir = $this->getGhostDirectory($language, $questionId);
            
            if (!File::isDirectory($ghostDir)) {
                Log::warning('No ghost solutions directory found for plagiarism detection', [
                    'language' => $language,
                    'question_id' => $questionId,
                    'ghost_dir' => $ghostDir
                ]);
                return $this->getFallbackResult();
            }
            
            // Check if there are any ghost files
            $ghostFiles = File::files($ghostDir);
            if (empty($ghostFiles)) {
                Log::warning('No ghost solution files found in directory', [
                    'ghost_dir' => $ghostDir
                ]);
                return $this->getFallbackResult();
            }
            
            // Create temporary file for student code
            $tempFile = $this->createTempCodeFile($code, $language);
            
            if (!$tempFile) {
                Log::error('Failed to create temporary file for student code');
                return $this->getFallbackResult();
            }
            
            // Run similarity check based on selected method
            if ($this->detectionMethod === 'codebert') {
                $result = $this->runCodeBERTSimilarityCheck($tempFile, $ghostDir);
                $methodName = 'CodeBERT Semantic Similarity';
            } else {
                $result = $this->runVectorSimilarityCheck($tempFile, $ghostDir);
                $methodName = 'TF-IDF Vector Similarity';
            }
            
            // Clean up temporary file
            @unlink($tempFile);
            
            if ($result === null) {
                return $this->getFallbackResult();
            }
            
            // Parse result
            $similarity = $result['similarity'];
            $matchedGhost = $result['matched_file'];
            
            // Convert similarity (0.0-1.0) to percentage (0-100)
            $similarityPercent = round($similarity * 100, 2);
            
            // INVERT THE SCORE: High similarity = Low originality
            // Originality Score = 100 - Similarity Score
            $originalityScore = 100 - $similarityPercent;
            
            // Determine indicators based on originality (inverted)
            $indicators = $this->getIndicators($similarityPercent, $matchedGhost, $methodName);
            
            // Determine confidence based on similarity
            $confidence = $similarityPercent >= 80 ? 'high' : ($similarityPercent >= 60 ? 'medium' : 'low');
            
            Log::info('Plagiarism detection completed', [
                'method' => $methodName,
                'similarity_to_ai' => $similarityPercent,
                'originality_score' => $originalityScore,
                'matched_ghost' => $matchedGhost,
                'confidence' => $confidence
            ]);
            
            return [
                'ai_probability' => (int)$originalityScore,  // Return originality, not similarity
                'similarity_to_ai' => (int)$similarityPercent, // Store actual similarity for display
                'reason' => $originalityScore >= 60 
                    ? "Code appears original with low similarity ({$similarityPercent}%) to known AI solutions" 
                    : "Code shows high similarity ({$similarityPercent}%) to known AI-generated solutions",
                'indicators' => $indicators,
                'confidence' => $confidence,
                'matched_solution' => $matchedGhost,
                'detection_method' => $methodName
            ];
            
        } catch (\Exception $e) {
            Log::error('Plagiarism detection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getFallbackResult();
        }
    }
    
    /**
     * Get the ghost solutions directory path
     */
    private function getGhostDirectory(string $language, ?int $questionId): string
    {
        $basePath = storage_path('app/ghost_solutions/' . strtolower($language));
        
        // If question-specific ghosts exist, use them
        if ($questionId) {
            $questionSpecificPath = $basePath . '/question_' . $questionId;
            if (File::isDirectory($questionSpecificPath)) {
                return $questionSpecificPath;
            }
        }
        
        // Otherwise, use generic ghost solutions for the language
        return $basePath;
    }
    
    /**
     * Create temporary file for student code
     */
    private function createTempCodeFile(string $code, string $language): ?string
    {
        $tempDir = storage_path('app/temp_plagiarism');
        
        // Create temp directory if it doesn't exist
        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
        
        // Get file extension based on language
        $extension = $this->getFileExtension($language);
        
        // Create unique temp file
        $tempFile = $tempDir . '/student_' . uniqid() . '.' . $extension;
        
        // Write code to file
        if (File::put($tempFile, $code) !== false) {
            return $tempFile;
        }
        
        return null;
    }
    
    /**
     * Get file extension for language
     */
    private function getFileExtension(string $language): string
    {
        $extensions = [
            'python' => 'py',
            'java' => 'java',
            'javascript' => 'js',
            'php' => 'php',
            'c++' => 'cpp',
            'cpp' => 'cpp',
            'c' => 'c',
            'c#' => 'cs',
            'csharp' => 'cs',
        ];
        
        return $extensions[strtolower($language)] ?? 'txt';
    }
    
    /**
     * Run Python CodeBERT similarity checker (Semantic Analysis)
     */
    private function runCodeBERTSimilarityCheck(string $studentFile, string $ghostDir): ?array
    {
        // Path to Python script
        $scriptPath = base_path('check_similarity_codebert.py');
        
        // Path to Python executable in virtual environment
        $pythonPath = base_path('venv_plagiarism/bin/python3');
        
        // Fallback to system Python if venv doesn't exist
        if (!file_exists($pythonPath)) {
            $pythonPath = 'python3';
        }
        
        // Build command
        $command = sprintf(
            '%s %s %s %s 2>&1',
            escapeshellarg($pythonPath),
            escapeshellarg($scriptPath),
            escapeshellarg($studentFile),
            escapeshellarg($ghostDir)
        );
        
        // Execute command
        $output = shell_exec($command);
        
        if ($output === null || trim($output) === '') {
            Log::error('CodeBERT similarity checker returned no output', [
                'command' => $command
            ]);
            return null;
        }
        
        $output = trim($output);
        
        // Parse output format: "0.8542|AI_SUSPECT_1_chatgpt.py"
        if (strpos($output, '|') !== false) {
            list($similarity, $matchedFile) = explode('|', $output, 2);
            
            return [
                'similarity' => (float)$similarity,
                'matched_file' => $matchedFile
            ];
        } else {
            // Error or unexpected format
            Log::warning('CodeBERT returned unexpected format', [
                'output' => $output
            ]);
            return [
                'similarity' => 0.0,
                'matched_file' => 'unknown'
            ];
        }
    }
    
    /**
     * Run Python vector similarity checker (TF-IDF Method)
     */
    private function runVectorSimilarityCheck(string $studentFile, string $ghostDir): ?array
    {
        // Path to Python script
        $scriptPath = base_path('check_similarity.py');
        
        // Path to Python executable in virtual environment
        $pythonPath = base_path('venv_plagiarism/bin/python3');
        
        // Fallback to system Python if venv doesn't exist
        if (!file_exists($pythonPath)) {
            $pythonPath = 'python3';
        }
        
        // Build command
        $command = sprintf(
            '%s %s %s %s 2>&1',
            escapeshellarg($pythonPath),
            escapeshellarg($scriptPath),
            escapeshellarg($studentFile),
            escapeshellarg($ghostDir)
        );
        
        // Execute command
        $output = shell_exec($command);
        
        if ($output === null || trim($output) === '') {
            Log::error('Python similarity checker returned no output', [
                'command' => $command
            ]);
            return null;
        }
        
        $output = trim($output);
        
        // Parse output format: "0.8542|AI_SUSPECT_1_chatgpt.py"
        if (strpos($output, '|') !== false) {
            list($similarity, $matchedFile) = explode('|', $output, 2);
            
            return [
                'similarity' => (float)$similarity,
                'matched_file' => $matchedFile
            ];
        } else {
            // Old format or error - just similarity score
            return [
                'similarity' => (float)$output,
                'matched_file' => 'unknown'
            ];
        }
    }
    
    /**
     * Get indicators based on similarity score
     */
    private function getIndicators(float $similarity, string $matchedGhost, string $method = 'Vector Similarity'): array
    {
        if ($similarity >= 80) {
            return [
                "Code is {$similarity}% similar to known AI solution '{$matchedGhost}'",
                "Extremely high semantic similarity detected using {$method}",
                "Code structure and patterns match AI-generated solutions",
                $method === 'CodeBERT Semantic Similarity' 
                    ? "Deep learning analysis confirms AI authorship patterns"
                    : "Rare keywords and function usage align with ChatGPT/Copilot outputs"
            ];
        } elseif ($similarity >= 60) {
            return [
                "Code shows {$similarity}% similarity to AI solution '{$matchedGhost}'",
                "High similarity in code patterns detected using {$method}",
                "Significant overlap in algorithmic approach with AI solutions",
                $method === 'CodeBERT Semantic Similarity'
                    ? "Semantic embeddings suggest AI-generated code"
                    : "Token distribution matches typical AI-generated code"
            ];
        } elseif ($similarity >= 40) {
            return [
                "Moderate similarity ({$similarity}%) to AI solution '{$matchedGhost}'",
                "Some common patterns detected with AI solutions",
                "Partial overlap in implementation approach"
            ];
        } else {
            return [
                "Code appears original ({$similarity}% similarity to AI solutions)",
                "Unique implementation style detected",
                "Implementation approach differs from known AI patterns"
            ];
        }
    }
    
    /**
     * Get fallback result when analysis is not available
     */
    private function getFallbackResult(): array
    {
        return [
            'ai_probability' => 100, // Give benefit of doubt - assume original when system can't check
            'similarity_to_ai' => 0, // No similarity detected (because we couldn't check)
            'reason' => 'Plagiarism detection service unavailable - no ghost solutions found. Code marked as original by default.',
            'indicators' => ['No AI reference solutions available for comparison', 'Student given benefit of doubt'],
            'confidence' => 'low',
            'matched_solution' => null,
            'detection_method' => $this->detectionMethod === 'codebert' ? 'CodeBERT Semantic Similarity' : 'TF-IDF Vector Similarity'
        ];
    }
    
    /**
     * Get risk level based on probability score
     */
    public function getRiskLevel(int $probability): string
    {
        if ($probability >= 80) {
            return 'high';
        } elseif ($probability >= 60) {
            return 'medium';
        } elseif ($probability >= 40) {
            return 'low';
        } else {
            return 'minimal';
        }
    }
    
    /**
     * Get color code for risk level
     */
    public function getRiskColor(string $riskLevel): string
    {
        $colors = [
            'high' => '#EF4444',      // Red
            'medium' => '#F59E0B',    // Orange
            'low' => '#EAB308',       // Yellow
            'minimal' => '#10B981'    // Green
        ];
        
        return $colors[$riskLevel] ?? '#6B7280';
    }
}
