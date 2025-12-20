<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Question;
use App\Models\Learner;
use App\Services\CodeExecutionService;
use App\Services\AICodeFeedbackService;
use App\Services\AIPlagiarismDetectionService;

class CodeExecutionController extends Controller
{
    protected $codeExecutionService;
    protected $aiFeedbackService;
    protected $plagiarismService;
    
    public function __construct(
        CodeExecutionService $codeExecutionService, 
        AICodeFeedbackService $aiFeedbackService,
        AIPlagiarismDetectionService $plagiarismService
    )
    {
        $this->codeExecutionService = $codeExecutionService;
        $this->aiFeedbackService = $aiFeedbackService;
        $this->plagiarismService = $plagiarismService;
    }
    
    /**
     * Run code without saving (for testing) - SAME AS COMPETENCY TEST
     */
    public function runCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'language' => 'required|string',
            'question_id' => 'required|exists:questions,question_ID'
        ]);

        $code = $request->code;
        $language = $request->language;
        $question = Question::find($request->question_id);

        // Get ALL test cases
        $testCases = is_string($question->input) ? json_decode($question->input, true) : $question->input;
        $expectedOutputs = is_string($question->expected_output) ? json_decode($question->expected_output, true) : $question->expected_output;

        if (!$testCases || !is_array($testCases) || empty($testCases)) {
            return response()->json([
                'success' => false,
                'output' => 'Error: No test cases available for this question.'
            ], 200);
        }

        // Get function name from question (stored in grading_details)
        $gradingDetails = is_string($question->grading_details) ? json_decode($question->grading_details, true) : $question->grading_details;
        $functionName = $gradingDetails['function_name'] ?? null;

        // Run code against ALL test cases - JUST EXECUTE, DON'T VALIDATE
        $totalTests = count($testCases);
        $testResults = [];
        $hasError = false;
        $errorMessage = '';

        \Log::info('=== RUN CODE DEBUG (Learner) ===', [
            'total_test_cases' => $totalTests,
            'test_cases_raw' => $testCases
        ]);

        foreach ($testCases as $index => $testCase) {
            try {
                // EXTRACT THE ACTUAL INPUT from nested structure
                $actualInput = null;
                
                if (is_array($testCase)) {
                    // Nested structure - extract the 'input' field
                    $actualInput = $testCase['input'] ?? $testCase;
                } else {
                    // Direct input
                    $actualInput = $testCase;
                }
                
                \Log::info("Processing test case #" . ($index + 1), [
                    'index' => $index,
                    'raw_test_case' => $testCase,
                    'extracted_input' => $actualInput,
                    'input_length' => is_string($actualInput) ? strlen($actualInput) : 'not string'
                ]);
                
                // Execute code for this test case using Docker
                $result = $this->codeExecutionService->executeCode(
                    $code, 
                    $language, 
                    $actualInput,
                    $functionName
                );
                
                \Log::info("Execution result for test case #" . ($index + 1), [
                    'success' => $result['success'],
                    'output' => $result['output'],
                    'output_length' => strlen($result['output'])
                ]);
                
                if (!$result['success']) {
                    // Execution error - stop testing and show error
                    $hasError = true;
                    $errorMessage = $result['output'];
                    break;
                }

                $actualOutput = trim($result['output']);
                
                // Format input for display (clean and readable)
                $displayInput = $actualInput;
                if (is_array($displayInput)) {
                    $displayInput = json_encode($displayInput, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                } else {
                    // Remove markdown code blocks
                    $displayInput = preg_replace('/```[\w]*\n?/', '', $displayInput);
                    $displayInput = preg_replace('/```/', '', $displayInput);
                    $displayInput = trim($displayInput);
                }
                
                // Store test results - ONLY INPUT AND OUTPUT (no validation)
                $testResults[] = [
                    'test_number' => $index + 1,
                    'input' => $displayInput,
                    'output' => $actualOutput
                ];

                \Log::info("Added test result #" . ($index + 1), [
                    'test_number' => $index + 1,
                    'input_preview' => substr($displayInput, 0, 100),
                    'output_preview' => substr($actualOutput, 0, 100)
                ]);

            } catch (\Exception $e) {
                $hasError = true;
                $errorMessage = 'Error: ' . $e->getMessage();
                \Log::error("Exception in test case #" . ($index + 1), [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                break;
            }
        }

        \Log::info('=== FINAL TEST RESULTS (Learner) ===', [
            'total_results' => count($testResults),
            'results' => $testResults
        ]);

        // If there was an error, return it
        if ($hasError) {
            return response()->json([
                'success' => false,
                'output' => $errorMessage
            ], 200);
        }

        // Return results - SIMPLE FORMAT (no validation, no pass/fail)
        return response()->json([
            'success' => true,
            'totalTests' => $totalTests,
            'testResults' => $testResults,
            'message' => "Code executed successfully on {$totalTests} test case(s)"
        ]);
    }
    
    /**
     * Submit code and run against all test cases - SAME AS COMPETENCY TEST
     */
    public function submitCode(Request $request)
    {
        $request->validate([
            'solution' => 'required',
            'question_id' => 'required|exists:questions,question_ID',
            'language' => 'required|string'
        ]);

        $code = $request->solution;
        $language = $request->language;
        $question = Question::find($request->question_id);
        $learner = Auth::guard('learner')->user();

        // Get ALL test cases from the database
        $testCases = is_string($question->input) ? json_decode($question->input, true) : $question->input;
        $expectedOutputs = is_string($question->expected_output) ? json_decode($question->expected_output, true) : $question->expected_output;

        if (!$testCases || !is_array($testCases) || empty($testCases)) {
            return redirect()->back()->with('error', 'No test cases available for this question.');
        }

        // Get function name from question (stored in grading_details)
        $gradingDetails = is_string($question->grading_details) ? json_decode($question->grading_details, true) : $question->grading_details;
        $functionName = $gradingDetails['function_name'] ?? null;

        // Run code against ALL test cases using Docker
        $totalTests = count($testCases);
        $passedTests = 0;
        $testResults = [];

        foreach ($testCases as $index => $testCase) {
            // EXTRACT THE ACTUAL INPUT from nested structure (same as runCode)
            $actualInput = null;
            if (is_array($testCase)) {
                // Nested structure - extract the 'input' field
                $actualInput = $testCase['input'] ?? $testCase;
            } else {
                // Direct input
                $actualInput = $testCase;
            }
            
            // Get expected output for this test case
            $expectedOutput = isset($expectedOutputs[$index]) ? $expectedOutputs[$index] : '';
            
            // Extract expected output from nested structure if needed
            if (is_array($expectedOutput) && isset($expectedOutput['output'])) {
                $expectedOutput = $expectedOutput['output'];
            }
            
            // Use CodeExecutionService with Docker
            $result = $this->codeExecutionService->executeCode(
                $code,
                $language,
                $actualInput,
                $functionName
            );
            
            if ($result['success']) {
                $actualOutput = trim($result['output']);
                $expectedOutputString = is_array($expectedOutput) ? json_encode($expectedOutput) : (string)$expectedOutput;
                
                // Clean expected output - remove markdown backticks
                $expectedOutputString = str_replace('`', '', $expectedOutputString);
                $expectedOutputString = trim($expectedOutputString);
                
                $passed = $actualOutput === $expectedOutputString;
                
                if ($passed) {
                    $passedTests++;
                }

                // Format input for display (clean and readable)
                $displayInput = $actualInput;
                if (is_array($displayInput)) {
                    $displayInput = json_encode($displayInput, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                } else {
                    // Remove markdown code blocks
                    $displayInput = preg_replace('/```[\w]*\n?/', '', $displayInput);
                    $displayInput = preg_replace('/```/', '', $displayInput);
                    $displayInput = trim($displayInput);
                }

                // SHOW ALL TEST CASES
                $testResults[] = [
                    'test_number' => $index + 1,
                    'is_sample' => $index === 0, // First test case is marked as sample
                    'input' => $displayInput,
                    'expected' => $expectedOutputString,
                    'actual' => $actualOutput,
                    'passed' => $passed
                ];
            } else {
                // If execution failed, mark as failed
                $displayInput = is_array($actualInput) ? json_encode($actualInput, JSON_PRETTY_PRINT) : $actualInput;
                // Remove markdown code blocks
                if (is_string($displayInput)) {
                    $displayInput = preg_replace('/```[\w]*\n?/', '', $displayInput);
                    $displayInput = preg_replace('/```/', '', $displayInput);
                    $displayInput = trim($displayInput);
                }
                
                $testResults[] = [
                    'test_number' => $index + 1,
                    'is_sample' => $index === 0,
                    'input' => $displayInput,
                    'expected' => is_array($expectedOutput) ? json_encode($expectedOutput) : $expectedOutput,
                    'actual' => $result['output'], // Show error message
                    'passed' => false
                ];
            }
        }

        // Calculate score for this question
        $scorePercentage = ($passedTests / $totalTests) * 100;
        $questionScore = round($scorePercentage, 2);

        // Generate AI feedback for the submission
        $aiFeedback = $this->aiFeedbackService->generateFeedback(
            $code,
            $language,
            $question->title ?? 'Coding Challenge',
            $testResults
        );

        // ============================================================
        // AI PLAGIARISM DETECTION (Ghost File Trap Method)
        // ============================================================
        // Analyze code for AI authorship patterns by comparing against known AI solutions
        $plagiarismAnalysis = $this->plagiarismService->analyzeCode($code, $language, $question->question_ID);
        $plagiarismScore = $plagiarismAnalysis['ai_probability'];
        $riskLevel = $this->plagiarismService->getRiskLevel($plagiarismScore);
        
        \Log::info('Plagiarism analysis completed', [
            'learner_id' => $learner->learner_ID,
            'question_id' => $question->question_ID,
            'plagiarism_score' => $plagiarismScore,
            'risk_level' => $riskLevel,
            'confidence' => $plagiarismAnalysis['confidence'],
            'matched_solution' => $plagiarismAnalysis['matched_solution'] ?? null,
            'indicators' => $plagiarismAnalysis['indicators']
        ]);

        // ============================================================
        // CALCULATE XP USING LINEAR MULTIPLIER MODEL
        // ============================================================
        // Formula: XP = BaseXP × DifficultyMultiplier × (Score/MaxScore)
        
        $baseXP = 10; // Base XP for any question
        
        // Difficulty Multiplier based on question level
        $difficultyMultipliers = [
            'Easy' => 1.0,
            'Medium' => 1.5,
            'Hard' => 2.0
        ];
        
        $difficulty = $question->level ?? 'Easy';
        $difficultyMultiplier = $difficultyMultipliers[$difficulty] ?? 1.0;
        
        // Calculate earned XP
        $earnedXP = $baseXP * $difficultyMultiplier * ($questionScore / 100);
        $earnedXP = round($earnedXP, 2);
        
        // ============================================================
        // SAVE TO DATABASE (attempts table)
        // ============================================================
        try {
            \DB::beginTransaction();
            
            // Convert aiFeedback to JSON string if it's an array
            $aiFeedbackString = is_array($aiFeedback) ? json_encode($aiFeedback) : $aiFeedback;
            
            // Save attempt to database
            \DB::table('attempts')->insert([
                'question_ID' => $question->question_ID,
                'learner_ID' => $learner->learner_ID,
                'testResult_ID' => null, // This is for practice, not competency test
                'submittedCode' => $code,
                'plagiarismScore' => $plagiarismScore, // Store AI plagiarism detection score
                'accuracyScore' => $earnedXP, // Store earned XP as accuracy score
                'aiFeedback' => $aiFeedbackString, // Store as JSON string
                'dateAttempted' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update learner's total XP
            $learner->totalPoint += $earnedXP;
            
            // ============================================================
            // UPDATE STREAK
            // ============================================================
            // Check if learner has attempted today
            $lastAttempt = \DB::table('attempts')
                ->where('learner_ID', $learner->learner_ID)
                ->where('dateAttempted', '<', now()->startOfDay())
                ->orderBy('dateAttempted', 'desc')
                ->first();
            
            if ($lastAttempt) {
                $lastAttemptDate = \Carbon\Carbon::parse($lastAttempt->dateAttempted);
                $today = \Carbon\Carbon::now()->startOfDay();
                $yesterday = \Carbon\Carbon::now()->subDay()->startOfDay();
                
                // If last attempt was yesterday, increment streak
                if ($lastAttemptDate->isSameDay($yesterday)) {
                    $learner->streak += 1;
                }
                // If last attempt was more than 1 day ago, reset streak to 1
                elseif ($lastAttemptDate->isBefore($yesterday)) {
                    $learner->streak = 1;
                }
                // If last attempt was today, keep streak unchanged
            } else {
                // First attempt ever, set streak to 1
                $learner->streak = 1;
            }
            
            $learner->save();
            
            // ============================================================
            // UPDATE USER PROFICIENCY
            // ============================================================
            // Use updateOrCreate which handles composite keys better
            $proficiency = \App\Models\UserProficiency::updateOrCreate(
                [
                    'learner_ID' => $learner->learner_ID,
                    'language' => $language
                ],
                [
                    'XP' => \DB::raw("XP + $earnedXP"),
                    'level' => 'Beginner' // Will be updated below
                ]
            );
            
            // Fetch fresh data to get updated XP value
            $proficiency = \App\Models\UserProficiency::where('learner_ID', $learner->learner_ID)
                ->where('language', $language)
                ->first();
            
            // Update proficiency level based on XP
            $newLevel = 'Beginner';
            if ($proficiency->XP >= 70) {
                $newLevel = 'Advanced';
            } elseif ($proficiency->XP >= 30) {
                $newLevel = 'Intermediate';
            }
            
            // Only update level if it changed
            if ($proficiency->level !== $newLevel) {
                \DB::table('user_proficiencies')
                    ->where('learner_ID', $learner->learner_ID)
                    ->where('language', $language)
                    ->update(['level' => $newLevel]);
            }
            
            \DB::commit();
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error saving attempt: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to save your submission. Please try again.');
        }

        // Store submission data in session
        $submissionData = [
            'learner_id' => $learner->learner_ID,
            'question_id' => $question->question_ID,
            'question_title' => $question->title,
            'language' => $language,
            'solution' => $code,
            'score' => $questionScore,
            'passed_tests' => $passedTests,
            'total_tests' => $totalTests,
            'test_results' => $testResults,
            'ai_feedback' => is_string($aiFeedback) ? json_decode($aiFeedback, true) : $aiFeedback, // Handle as array
            'earned_xp' => $earnedXP, // Add earned XP to session data
            'new_total_xp' => $learner->totalPoint, // Add new total XP
            'current_streak' => $learner->streak, // Add current streak
            'plagiarism_analysis' => $plagiarismAnalysis, // Add plagiarism analysis
            'submitted_at' => now()
        ];

        // Store in session for result page
        session()->put('coding_submission', $submissionData);

        // Redirect to result page (using same view as competency test)
        return redirect()->route('learner.coding.result');
    }

    /**
     * Rate a question as good or bad
     */
    public function rateQuestion(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,question_ID',
            'rating' => 'required|in:good,bad'
        ]);

        $learner = Auth::guard('learner')->user();
        $questionId = $request->question_id;
        $rating = $request->rating;

        try {
            // Check if learner has already rated this question
            $existingRating = \App\Models\QuestionRating::where('learner_ID', $learner->learner_ID)
                ->where('question_ID', $questionId)
                ->first();

            $question = Question::find($questionId);

            if ($existingRating) {
                // Update existing rating
                $oldRating = $existingRating->rating;
                
                // Decrement old rating count
                if ($oldRating === 'good') {
                    $question->decrement('good_ratings');
                } else {
                    $question->decrement('bad_ratings');
                }

                // Increment new rating count
                if ($rating === 'good') {
                    $question->increment('good_ratings');
                } else {
                    $question->increment('bad_ratings');
                }

                // Update the rating
                $existingRating->rating = $rating;
                $existingRating->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Rating updated successfully',
                    'good_ratings' => $question->fresh()->good_ratings,
                    'bad_ratings' => $question->fresh()->bad_ratings
                ]);
            } else {
                // Create new rating
                \App\Models\QuestionRating::create([
                    'learner_ID' => $learner->learner_ID,
                    'question_ID' => $questionId,
                    'rating' => $rating
                ]);

                // Increment rating count
                if ($rating === 'good') {
                    $question->increment('good_ratings');
                } else {
                    $question->increment('bad_ratings');
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Rating submitted successfully',
                    'good_ratings' => $question->fresh()->good_ratings,
                    'bad_ratings' => $question->fresh()->bad_ratings
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error rating question: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit rating. Please try again.'
            ], 500);
        }
    }
}
