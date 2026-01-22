<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Question;
use App\Models\CompetencyTestResult;
use App\Services\CodeExecutionService;
use App\Services\AICodeFeedbackService;
use App\Services\AIPlagiarismDetectionService;
use App\Services\OutputNormalizer;

class CompetencyTestController extends Controller
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

    public function chooseLanguage()
    {
        $reviewer = Auth::guard('reviewer')->user();
        // Get distinct languages from approved competency test questions
        $languages = Question::where('questionCategory', 'competencyTest')
            ->where('status', 'Approved')
            ->select('language')
            ->distinct()
            ->pluck('language')
            ->filter() // Remove null values
            ->values();

        // Define desired order: Python, Java, JavaScript, PHP, C++, C, SQL
        // This makes SQL appear below C++ in a 3-column grid
        $desiredOrder = ['Python', 'Java', 'JavaScript', 'PHP', 'C++', 'C', 'SQL'];
        
        // Sort languages based on desired order
        $sortedLanguages = collect($desiredOrder)
            ->filter(function($lang) use ($languages) {
                return $languages->contains($lang);
            })
            ->values();
        
        // Add any languages not in the desired order at the end
        $remainingLanguages = $languages->diff($desiredOrder);
        $sortedLanguages = $sortedLanguages->concat($remainingLanguages);

        // Map languages to their icons, colors, and descriptions
        $languageData = $sortedLanguages->map(function($language) {
            // Check if language has sufficient questions
            $mcqCount = Question::where('language', $language)
                ->where('questionCategory', 'competencyTest')
                ->where('questionType', 'MCQ_Question')
                ->where('status', 'Approved')
                ->count();
            
            $evaluationCount = Question::where('language', $language)
                ->where('questionCategory', 'competencyTest')
                ->where('questionType', 'Question_Evaluation')
                ->where('status', 'Approved')
                ->count();
            
            $codeSolutionCount = Question::where('language', $language)
                ->where('questionCategory', 'competencyTest')
                ->where('questionType', 'Code_Solution')
                ->where('status', 'Approved')
                ->count();
            
            
            $isSufficient = ($mcqCount >= 3 && $evaluationCount >= 3 && $codeSolutionCount >= 1);
            
            $iconMap = [
                'Python' => [
                    'icon' => '</>', 
                    'iconBg' => '#4C6EF5', // Blue
                    'cardBg' => '#ffffff',
                    'hoverBg' => 'rgba(76, 110, 245, 0.50)',
                    'description' => 'Test your Python fundamentals'
                ],
                'Java' => [
                    'icon' => '</>', 
                    'iconBg' => '#F97316', // Orange
                    'cardBg' => '#ffffff',
                    'hoverBg' => 'rgba(249, 116, 22, 0.5)',
                    'description' => 'Evaluate your Java knowledge'
                ],
                'JavaScript' => [
                    'icon' => '</>', 
                    'iconBg' => '#F59E0B', // Violet
                    'cardBg' => '#ffffff',
                    'hoverBg' => 'rgba(169, 85, 247, 0.5)',
                    'description' => 'Assess your JavaScript skills'
                ],
                'C++' => [
                    'icon' => '</>', 
                    'iconBg' => '#EC4899', // Pink
                    'cardBg' => '#ffffff',
                    'hoverBg' => 'rgba(236, 72, 154, 0.5)',
                    'description' => 'Verify your C++ expertise'
                ],
                'SQL' => [
                    'icon' => '</>', 
                    'iconBg' => '#10B981', // Green
                    'cardBg' => '#ffffff',
                    'hoverBg' => 'rgba(16, 185, 129, 0.5)',
                    'description' => 'Master database queries'
                ],
                'PHP' => [
                    'icon' => '</>', 
                    'iconBg' => '#6366F1', // Indigo
                    'cardBg' => '#ffffff',
                    'hoverBg' => 'rgba(99, 101, 241, 0.5)',
                    'description' => 'Check your server-side scripting'
                ],
                'C#' => [
                    'icon' => '</>', 
                    'iconBg' => '#8B5CF6', // Purple
                    'cardBg' => '#ffffff',
                    'hoverBg' => 'rgba(138, 92, 246, 0.5)',
                    'description' => 'Test your C# proficiency'
                ],
                'C' => [
                    'icon' => '</>', 
                    'iconBg' => '#EF4444', // Red
                    'cardBg' => '#ffffff',
                    'hoverBg' => 'rgba(239, 68, 68, 0.5)',
                    'description' => 'Assess your Ruby expertise'
                ],
            ];

            $default = [
                'icon' => '</>', 
                'iconBg' => 'rgb(182, 99, 120)', // Gray
                'cardBg' => '#ffffff',
                'hoverBg' => 'rgba(182, 99, 120, 0.5)',
                'description' => 'Test your ' . $language . ' knowledge'
            ];

            $langData = $iconMap[$language] ?? $default;

            return [
                'name' => $language,
                'icon' => $langData['icon'],
                'iconBg' => $langData['iconBg'],
                'cardBg' => $langData['cardBg'],
                'hoverBg' => $langData['hoverBg'],
                'description' => $langData['description'],
                'isSufficient' => $isSufficient,
                'questionCounts' => [
                    'mcq' => $mcqCount,
                    'evaluation' => $evaluationCount,
                    'codeSolution' => $codeSolutionCount
                ]
            ];
        });

        return view('reviewer.competency.choose-language', compact('languageData', 'reviewer'));
    }

    public function startTest(Request $request)
    {
        // Get available languages from database
        $availableLanguages = Question::where('questionCategory', 'competencyTest')
            ->where('status', 'Approved')
            ->distinct()
            ->pluck('language')
            ->filter()
            ->toArray();

        $request->validate([
            'language' => 'required|in:' . implode(',', $availableLanguages)
        ]);

        $language = $request->language;

        // Retrieve 3 MCQ_Question questions (changed from 4)
        $mcqQuestions = Question::where('language', $language)
            ->where('questionCategory', 'competencyTest')
            ->where('questionType', 'MCQ_Question')
            ->where('status', 'Approved')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        // Retrieve 3 Question_Evaluation questions (changed from 2)
        $evaluationQuestions = Question::where('language', $language)
            ->where('questionCategory', 'competencyTest')
            ->where('questionType', 'Question_Evaluation')
            ->where('status', 'Approved')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        // Retrieve 1 Code_Solution question for the code test phase (changed from 2)
        $codeSolutionQuestions = Question::where('language', $language)
            ->where('questionCategory', 'competencyTest')
            ->where('questionType', 'Code_Solution')
            ->where('status', 'Approved')
            ->inRandomOrder()
            ->limit(1)
            ->get();

        // Check if we have enough questions (updated requirements)
        if ($mcqQuestions->count() < 3 || $evaluationQuestions->count() < 3 || $codeSolutionQuestions->count() < 1) {
            return redirect()->back()->with('error', 'Not enough questions available for this language. Please contact administrator.');
        }

        // Merge MCQ and Evaluation questions for the MCQ test phase (total 6 questions)
        $allMcqPhaseQuestions = $mcqQuestions->merge($evaluationQuestions);

        
        session([
            'test_language' => $language,
            'test_started_at' => now()->timestamp, // Store as Unix timestamp
            'test_timer_seconds' => 45 * 60, // 45 minutes in seconds
            'mcq_questions' => $allMcqPhaseQuestions->pluck('question_ID')->toArray(),
            'code_questions' => $codeSolutionQuestions->pluck('question_ID')->toArray(),
            'current_mcq_index' => 0,
            'current_code_index' => 0,
            'mcq_answers' => [],
            'code_solutions' => []
        ]);

        return redirect()->route('reviewer.competency.mcq');
    }

    public function showMCQ()
    {
        $mcqQuestions = session('mcq_questions');
        $currentIndex = session('current_mcq_index', 0);

        // Check if test was properly started
        if (!$mcqQuestions || !session('test_language') || !session('test_started_at')) {
            return redirect()->route('reviewer.competency.choose')
                ->with('error', 'Please start the test first by selecting a language.');
        }

        // Additional check to ensure mcqQuestions is an array and has items
        if (!is_array($mcqQuestions) || empty($mcqQuestions)) {
            return redirect()->route('reviewer.competency.choose')
                ->with('error', 'Test session expired. Please start the test again.');
        }

        // Check if we've completed all MCQ questions
        if ($currentIndex >= count($mcqQuestions)) {
            \Log::info('MCQ Completed - Redirecting to code section', [
                'current_index' => $currentIndex,
                'total_questions' => count($mcqQuestions)
            ]);
            return redirect()->route('reviewer.competency.code');
        }

        // Calculate remaining time
        $testStartedAt = session('test_started_at');
        
        // Handle if Carbon instance is returned instead of timestamp
        if ($testStartedAt instanceof \Carbon\Carbon) {
            $testStartedAt = $testStartedAt->timestamp;
        }
        
        $totalSeconds = 45 * 60; // 45 minutes
        $elapsedSeconds = time() - intval($testStartedAt); // Ensure it's an integer
        $remainingSeconds = max(0, $totalSeconds - $elapsedSeconds);

        // If time is up, redirect to submit
        if ($remainingSeconds <= 0) {
            return $this->submitTest();
        }

        $questionId = $mcqQuestions[$currentIndex];
        $question = Question::find($questionId);
        
        // Check if question exists
        if (!$question) {
            return redirect()->route('reviewer.competency.choose')
                ->with('error', 'Question not found. Please restart the test.');
        }

        $language = session('test_language');

        \Log::info('Showing MCQ Question', [
            'current_index' => $currentIndex,
            'question_number' => $currentIndex + 1,
            'total_questions' => count($mcqQuestions),
            'question_id' => $questionId,
            'remaining_seconds' => $remainingSeconds,
            'test_started_at' => $testStartedAt,
            'elapsed_seconds' => $elapsedSeconds
        ]);

        return view('reviewer.competency.mcq-test', [
            'question' => $question,
            'currentQuestion' => $currentIndex + 1,
            'totalQuestions' => count($mcqQuestions),
            'language' => $language,
            'remainingSeconds' => $remainingSeconds
        ]);
    }

    public function submitMCQ(Request $request)
    {
        $request->validate([
            'answer' => 'required',
            'question_id' => 'required|exists:questions,question_ID'
        ]);

        // Get current answers and add the new one
        $mcqAnswers = session('mcq_answers', []);
        $mcqAnswers[$request->question_id] = $request->answer;
        session()->put('mcq_answers', $mcqAnswers);

        // Increment the index
        $currentIndex = session('current_mcq_index', 0);
        \Log::info('MCQ Submit - Before Increment', ['current_index' => $currentIndex]);
        $newIndex = $currentIndex + 1;
        session()->put('current_mcq_index', $newIndex);
        session()->save();
        \Log::info('MCQ Submit - After Increment', ['new_index' => $newIndex]);

        $mcqQuestions = session('mcq_questions');
        
        // Check if we've completed all MCQ questions
        if ($newIndex >= count($mcqQuestions)) {
            return redirect()->route('reviewer.competency.code');
        }

        return redirect()->route('reviewer.competency.mcq');
    }

    public function previousMCQ()
    {
        $currentIndex = session('current_mcq_index', 0);
        
        // Only allow going back if we're not at the first question
        if ($currentIndex > 0) {
            $newIndex = $currentIndex - 1;
            session()->put('current_mcq_index', $newIndex);
            session()->save();
            \Log::info('MCQ Previous - Going Back', ['new_index' => $newIndex]);
        }
        
        return redirect()->route('reviewer.competency.mcq');
    }

    public function showCode()
    {
        $codeQuestions = session('code_questions');
        $currentIndex = session('current_code_index', 0);

        if (!$codeQuestions || $currentIndex >= count($codeQuestions)) {
            return $this->submitTest();
        }

        // Calculate remaining time (same logic as MCQ)
        $testStartedAt = session('test_started_at');
        
        // Handle if Carbon instance is returned instead of timestamp
        if ($testStartedAt instanceof \Carbon\Carbon) {
            $testStartedAt = $testStartedAt->timestamp;
        }
        
        $totalSeconds = 45 * 60; // 45 minutes
        $elapsedSeconds = time() - intval($testStartedAt);
        $remainingSeconds = max(0, $totalSeconds - $elapsedSeconds);

        // If time is up, redirect to submit
        if ($remainingSeconds <= 0) {
            return $this->submitTest();
        }

        $questionId = $codeQuestions[$currentIndex];
        $question = Question::find($questionId);
        
        // Check if question exists
        if (!$question) {
            \Log::error('Question not found in showCode', [
                'question_id' => $questionId,
                'current_index' => $currentIndex
            ]);
            return redirect()->route('reviewer.competency.choose')
                ->with('error', 'Question not found. Please restart the test.');
        }
        
        $language = session('test_language');

        return view('reviewer.competency.code-test', [
            'question' => $question,
            'currentIndex' => $currentIndex,
            'currentQuestion' => $currentIndex + 1,
            'totalQuestions' => count($codeQuestions),
            'language' => $language,
            'remainingSeconds' => $remainingSeconds
        ]);
    }

    public function runCode(Request $request)
    {
        $request->validate([
            'solution' => 'required|string',
            'question_id' => 'required|exists:questions,question_ID'
        ]);

        $code = $request->solution;
        $language = session('test_language');
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
        $gradingDetails = is_string($question->grading_details) 
            ? json_decode($question->grading_details, true) 
            : $question->grading_details;
        $functionName = $gradingDetails['function_name'] ?? null;

        // Run code against ALL test cases - NOW WITH VALIDATION
        $totalTests = count($testCases);
        $testResults = [];
        $passedTests = 0;
        $hasError = false;
        $errorMessage = '';

        \Log::info('=== RUN CODE DEBUG ===', [
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
                
                // Get expected output for this test case
                $expectedOutput = isset($expectedOutputs[$index]) ? $expectedOutputs[$index] : '';
                
                // Extract expected output from nested structure if needed
                if (is_array($expectedOutput) && isset($expectedOutput['output'])) {
                    $expectedOutput = $expectedOutput['output'];
                }
                
                \Log::info("Processing test case #" . ($index + 1), [
                    'index' => $index,
                    'raw_test_case' => $testCase,
                    'extracted_input' => $actualInput,
                    'expected_output' => $expectedOutput
                ]);
                
                // Execute code for this test case
                $result = $this->codeExecutionService->executeCode(
                    $code, 
                    $language, 
                    $actualInput,
                    $functionName
                );
                
                \Log::info("Execution result for test case #" . ($index + 1), [
                    'success' => $result['success'],
                    'output' => $result['output']
                ]);
                
                if (!$result['success']) {
                    // Execution error - stop testing and show error
                    $hasError = true;
                    $errorMessage = $result['output'];
                    break;
                }

                $actualOutput = trim($result['output']);
                $expectedOutputString = is_array($expectedOutput) ? json_encode($expectedOutput) : (string)$expectedOutput;
                
                // Clean expected output - remove markdown backticks
                $expectedOutputString = str_replace('`', '', $expectedOutputString);
                $expectedOutputString = trim($expectedOutputString);
                
                // Use smart comparison with normalization (handles spaces, quotes, newlines)
                $comparisonResult = OutputNormalizer::smartCompare($actualOutput, $expectedOutputString);
                $passed = $comparisonResult['match'];
                
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
                
                // Store test results WITH validation
                $testResults[] = [
                    'test_number' => $index + 1,
                    'input' => $displayInput,
                    'output' => $actualOutput,
                    'expected' => $expectedOutputString,
                    'passed' => $passed,
                    'is_sample' => $index === 0,  // First test case is the sample
                    'comparison_message' => $comparisonResult['message'] ?? null
                ];

                \Log::info("Added test result #" . ($index + 1), [
                    'test_number' => $index + 1,
                    'passed' => $passed
                ]);

            } catch (\Exception $e) {
                $hasError = true;
                $errorMessage = 'Error: ' . $e->getMessage();
                \Log::error("Exception in test case #" . ($index + 1), [
                    'error' => $e->getMessage()
                ]);
                break;
            }
        }

        // If there was an error, return it
        if ($hasError) {
            // Clear cached results on error
            session()->forget('last_run_result_' . $question->question_ID);
            
            return response()->json([
                'success' => false,
                'output' => $errorMessage
            ], 200);
        }

        // Cache the run results in session for quick submit
        session()->put('last_run_result_' . $question->question_ID, [
            'code' => $code,
            'passed_tests' => $passedTests,
            'total_tests' => $totalTests,
            'test_results' => $testResults,
            'all_passed' => $passedTests === $totalTests,
            'timestamp' => now()->timestamp
        ]);

        \Log::info('=== FINAL TEST RESULTS (Cached) ===', [
            'total_results' => count($testResults),
            'passed_tests' => $passedTests,
            'all_passed' => $passedTests === $totalTests
        ]);

        // Return results with pass/fail status
        return response()->json([
            'success' => true,
            'totalTests' => $totalTests,
            'passedTests' => $passedTests,
            'testResults' => $testResults,
            'allPassed' => $passedTests === $totalTests,
            'message' => $passedTests === $totalTests 
                ? "✓ All test cases passed!" 
                : "✗ {$passedTests}/{$totalTests} test cases passed"
        ]);
    }

    public function submitCode(Request $request)
    {
        \Log::info('=== SUBMIT CODE CALLED ===', [
            'question_id' => $request->question_id,
            'has_solution' => !empty($request->solution),
            'solution_length' => $request->solution ? strlen($request->solution) : 0
        ]);

        $request->validate([
            'solution' => 'required',
            'question_id' => 'required|exists:questions,question_ID'
        ]);

        $code = $request->solution;
        $language = session('test_language');
        $question = Question::find($request->question_id);

        \Log::info('Question and language loaded', [
            'language' => $language,
            'question_title' => $question->title ?? 'N/A'
        ]);

        // Check if user has run the code first
        $cachedResult = session('last_run_result_' . $question->question_ID);
        
        // Normalize both codes for comparison (remove all whitespace differences)
        $submittedCodeNormalized = preg_replace('/\s+/', '', trim($code));
        $cachedCodeNormalized = $cachedResult ? preg_replace('/\s+/', '', trim($cachedResult['code'])) : '';
        
        \Log::info('Checking cached run result', [
            'has_cached_result' => !empty($cachedResult),
            'codes_match' => $submittedCodeNormalized === $cachedCodeNormalized,
            'submitted_length' => strlen($code),
            'cached_length' => $cachedResult ? strlen($cachedResult['code']) : 0,
            'normalized_submitted_length' => strlen($submittedCodeNormalized),
            'normalized_cached_length' => strlen($cachedCodeNormalized)
        ]);
        
        if (!$cachedResult) {
            \Log::warning('No cached result found - redirecting back');
            return redirect()->back()->with('error', 'Please run your code first before submitting. This ensures your code works correctly.');
        }
        
        if ($submittedCodeNormalized !== $cachedCodeNormalized) {
            \Log::warning('Code mismatch after running - redirecting back', [
                'first_50_chars_submitted' => substr($submittedCodeNormalized, 0, 50),
                'first_50_chars_cached' => substr($cachedCodeNormalized, 0, 50)
            ]);
            return redirect()->back()->with('error', 'The code has been modified after running. Please run your code again before submitting.');
        }

        // Use cached test results from "Run Code"
        $passedTests = $cachedResult['passed_tests'];
        $totalTests = $cachedResult['total_tests'];
        $testResults = $cachedResult['test_results'];

        \Log::info('Using cached test results', [
            'passed_tests' => $passedTests,
            'total_tests' => $totalTests
        ]);

        // Calculate score for this question (10 points max per question)
        $scorePercentage = ($passedTests / $totalTests) * 100;
        $questionScore = ($scorePercentage / 100) * 10; // Scale to 10 points

        \Log::info('Running plagiarism detection...');

        // ============================================================
        // AI PLAGIARISM DETECTION (Vector Similarity Method)
        // ============================================================
        try {
            // Analyze code for AI authorship using TF-IDF Vector Similarity
            $plagiarismAnalysis = $this->plagiarismService->analyzeCode($code, $language, $question->question_ID);
            $plagiarismScore = $plagiarismAnalysis['ai_probability'];
            $riskLevel = $this->plagiarismService->getRiskLevel($plagiarismScore);
            
            \Log::info('Plagiarism analysis completed (Competency Test)', [
                'reviewer_id' => Auth::guard('reviewer')->user()->reviewer_ID,
                'question_id' => $question->question_ID,
                'similarity_score' => $plagiarismScore,
                'risk_level' => $riskLevel,
                'confidence' => $plagiarismAnalysis['confidence'],
                'matched_solution' => $plagiarismAnalysis['matched_solution'] ?? null,
                'method' => 'TF-IDF Vector Similarity'
            ]);
        } catch (\Exception $e) {
            \Log::error('Plagiarism detection failed, using fallback', [
                'error' => $e->getMessage()
            ]);
            // Fallback values if plagiarism detection fails
            $plagiarismAnalysis = [
                'ai_probability' => 0,
                'reason' => 'Plagiarism detection unavailable',
                'indicators' => ['Detection service error'],
                'confidence' => 'low',
                'matched_solution' => null
            ];
            $plagiarismScore = 0;
            $riskLevel = 'minimal';
        }

        // Skip AI feedback for now (too slow for instant submission)
        $aiFeedback = null;

        // Clear the cached run result
        session()->forget('last_run_result_' . $question->question_ID);

        // Store the solution and test results WITH plagiarism data
        $codeSolutions = session('code_solutions', []);
        $codeSolutions[$request->question_id] = [
            'solution' => $code,
            'score' => $questionScore,
            'passed_tests' => $passedTests,
            'total_tests' => $totalTests,
            'test_results' => $testResults,
            'ai_feedback' => $aiFeedback,
            'plagiarism_score' => $plagiarismScore,
            'plagiarism_analysis' => $plagiarismAnalysis
        ];
        session()->put('code_solutions', $codeSolutions);

        \Log::info('Stored code solution in session', [
            'question_id' => $request->question_id,
            'total_solutions_stored' => count($codeSolutions)
        ]);

        // Increment index since we're going directly to results
        $currentIndex = session('current_code_index', 0);
        $newIndex = $currentIndex + 1;
        session()->put('current_code_index', $newIndex);
        
        // Save session to ensure it persists
        session()->save();

        \Log::info('Session saved, checking if test is complete', [
            'current_index' => $newIndex,
            'total_questions' => count(session('code_questions', []))
        ]);

        // Check if we've completed all code questions
        $codeQuestions = session('code_questions', []);
        if ($newIndex >= count($codeQuestions)) {
            // All questions completed - go to final results
            return $this->submitTest();
        }

        // More questions remaining - go to next question
        return redirect()->route('reviewer.competency.code');
    }

    public function submitTest()
    {
        $reviewer = Auth::guard('reviewer')->user();
        $language = session('test_language');
        $mcqAnswers = session('mcq_answers', []);
        $codeSolutions = session('code_solutions', []);

        // Calculate score for MCQ phase (3 MCQ_Question + 3 Question_Evaluation = 6 questions x 10 points = 60 points max)
        $mcqScore = 0;
        foreach ($mcqAnswers as $questionId => $answer) {
            $question = Question::find($questionId);
            if ($question && $question->answersData === $answer) {
                $mcqScore += 10; // 10 points per question
            }
        }

        // Calculate code solution score from actual test results
        $codeScore = 0;
        $totalPlagiarismScore = 0;
        $plagiarismCount = 0;
        
        foreach ($codeSolutions as $questionId => $solutionData) {
            if (is_array($solutionData) && isset($solutionData['score'])) {
                $codeScore += $solutionData['score'];
            }
            
            // Accumulate plagiarism scores
            if (is_array($solutionData) && isset($solutionData['plagiarism_score'])) {
                $totalPlagiarismScore += $solutionData['plagiarism_score'];
                $plagiarismCount++;
            }
        }

        // Calculate average plagiarism score (100 = no plagiarism, 0 = high plagiarism)
        $avgPlagiarismScore = $plagiarismCount > 0 ? round($totalPlagiarismScore / $plagiarismCount, 2) : 100;

        $totalScore = (($mcqScore + $codeScore) / 70) * 100;
        $levelAchieved = null;
        $passed = false;
        $plagiarismPassed = $avgPlagiarismScore >= 60;
        if ($totalScore >= 90 && $plagiarismPassed) {
            $levelAchieved = 'all';
            $passed = true;
        } elseif ($totalScore >= 75 && $plagiarismPassed) {
            $levelAchieved = 'intermediate';
            $passed = true;
        } elseif ($totalScore >= 50 && $plagiarismPassed) {
            $levelAchieved = 'beginner';
            $passed = true;
        }

        // Log the final results
        \Log::info('Competency Test Completed', [
            'reviewer_id' => $reviewer->reviewer_ID,
            'language' => $language,
            'total_score' => round($totalScore),
            'plagiarism_score' => $avgPlagiarismScore,
            'plagiarism_passed' => $plagiarismPassed,
            'passed' => $passed,
            'level_achieved' => $levelAchieved
        ]);

        // Save test result
        $result = CompetencyTestResult::create([
            'reviewer_ID' => $reviewer->reviewer_ID,
            'language' => $language,
            'mcq_score' => $mcqScore,
            'code_score' => $codeScore,
            'total_score' => round($totalScore),
            'plagiarism_score' => $avgPlagiarismScore,
            'level_achieved' => $levelAchieved,
            'passed' => $passed,
            'mcq_answers' => $mcqAnswers,
            'code_solutions' => $codeSolutions,
            'completed_at' => now()
        ]);

        // Update reviewer qualification status ONLY if passed
        if ($passed) {
            $reviewer->update(['isQualified' => true]);
        }

        // Clear session
        session()->forget(['test_language', 'test_started_at', 'mcq_questions', 'current_mcq_index', 
                          'mcq_answers', 'code_questions', 'current_code_index', 'code_solutions', 'submission_feedback']);

        return redirect()->route('reviewer.competency.result', $result->id);
    }

    public function showResult($id)
    {
        $reviewer = Auth::guard('reviewer')->user();
        $result = CompetencyTestResult::findOrFail($id);
        $reviewer = Auth::guard('reviewer')->user();

        // Ensure the result belongs to the current reviewer
        if ($result->reviewer_ID !== $reviewer->reviewer_ID) {
            abort(403);
        }

        return view('reviewer.competency.result', compact('result', 'reviewer'));
    }
}
