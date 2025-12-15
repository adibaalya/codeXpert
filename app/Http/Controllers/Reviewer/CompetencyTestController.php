<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Question;
use App\Models\CompetencyTestResult;

class CompetencyTestController extends Controller
{
    public function chooseLanguage()
    {
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
            
            // Check if requirements are met
            $isSufficient = ($mcqCount >= 4 && $evaluationCount >= 2 && $codeSolutionCount >= 2);
            
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
                    'iconBg' => '#A855F7', // Violet
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

        return view('reviewer.competency.choose-language', compact('languageData'));
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

        // Retrieve 4 MCQ_Question questions
        $mcqQuestions = Question::where('language', $language)
            ->where('questionCategory', 'competencyTest')
            ->where('questionType', 'MCQ_Question')
            ->where('status', 'Approved')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // Retrieve 2 Question_Evaluation questions
        $evaluationQuestions = Question::where('language', $language)
            ->where('questionCategory', 'competencyTest')
            ->where('questionType', 'Question_Evaluation')
            ->where('status', 'Approved')
            ->inRandomOrder()
            ->limit(2)
            ->get();

        // Retrieve 2 Code_Solution questions for the code test phase
        $codeSolutionQuestions = Question::where('language', $language)
            ->where('questionCategory', 'competencyTest')
            ->where('questionType', 'Code_Solution')
            ->where('status', 'Approved')
            ->inRandomOrder()
            ->limit(2)
            ->get();

        // Check if we have enough questions
        if ($mcqQuestions->count() < 4 || $evaluationQuestions->count() < 2 || $codeSolutionQuestions->count() < 2) {
            return redirect()->back()->with('error', 'Not enough questions available for this language. Please contact administrator.');
        }

        // Merge MCQ and Evaluation questions for the MCQ test phase (total 6 questions)
        $allMcqPhaseQuestions = $mcqQuestions->merge($evaluationQuestions);

        // Store in session
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

        $questionId = $codeQuestions[$currentIndex];
        $question = Question::find($questionId);
        $language = session('test_language');

        return view('reviewer.competency.code-test', [
            'question' => $question,
            'currentIndex' => $currentIndex,
            'currentQuestion' => $currentIndex + 1,
            'totalQuestions' => count($codeQuestions),
            'language' => $language
        ]);
    }

    public function runCode(Request $request)
    {
        $request->validate([
            'solution' => 'required|string',
            'question_id' => 'required|exists:questions,question_ID'
        ]);

        $code = $request->solution;
        $language = strtolower(session('test_language'));
        $question = Question::find($request->question_id);

        // Get ONLY the sample test case (first test case) - this is visible to the user
        $testCases = is_string($question->input) ? json_decode($question->input, true) : $question->input;
        $expectedOutputs = is_string($question->expected_output) ? json_decode($question->expected_output, true) : $question->expected_output;

        if (!$testCases || !is_array($testCases) || empty($testCases)) {
            return response()->json([
                'success' => false,
                'output' => 'Error: No test cases available for this question.'
            ], 200);
        }

        // Only use the first test case (sample test case - visible to user)
        $sampleInput = is_array($testCases[0]) ? json_encode($testCases[0]) : (string)$testCases[0];
        $sampleExpectedOutput = isset($expectedOutputs[0]) ? 
            (is_array($expectedOutputs[0]) ? json_encode($expectedOutputs[0]) : (string)$expectedOutputs[0]) 
            : '';

        try {
            $result = $this->executeCodeInDocker($code, $language, $sampleInput);
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'output' => $result['output'],
                    'sampleInput' => $sampleInput,
                    'expectedOutput' => $sampleExpectedOutput
                ], 200);
            }

            // Show the output with sample input and expected output (visible to user)
            return response()->json([
                'success' => true,
                'output' => $result['output'],
                'sampleInput' => $sampleInput,
                'expectedOutput' => $sampleExpectedOutput
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'output' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function executeCodeInDocker($code, $language, $input = '')
    {
        try {
            // Ensure input is a string
            $inputString = is_array($input) ? json_encode($input) : (string)$input;
            
            // Map language names to script-friendly names
            $languageMap = [
                'python' => 'python',
                'java' => 'java',
                'javascript' => 'javascript',
                'c++' => 'cpp',
                'c' => 'c',
                'php' => 'php',
                'c#' => 'csharp'
            ];

            $mappedLanguage = $languageMap[$language] ?? $language;

            // Execute code using Docker with Process
            $process = new \Symfony\Component\Process\Process([
                'docker', 'run', '--rm',
                '--memory=128m',
                '--cpus=0.5',
                '--network=none',
                '--pids-limit=50',
                '-e', "USER_CODE=" . $code,
                '-e', "LANGUAGE=" . $mappedLanguage,
                '-e', "TEST_INPUT=" . $inputString,
                'code-sandbox'
            ]);

            // Set timeout (10 seconds)
            $process->setTimeout(10);
            
            // Run the process
            $process->run();

            $output = $process->getOutput();
            $error = $process->getErrorOutput();

            // If there's an error, return it
            if ($process->getExitCode() !== 0 && $error) {
                return [
                    'success' => false,
                    'output' => "Error:\n" . $error
                ];
            }

            return [
                'success' => true,
                'output' => $output ?: "Code executed successfully with no output."
            ];

        } catch (\Symfony\Component\Process\Exception\ProcessTimedOutException $e) {
            return [
                'success' => false,
                'output' => 'Error: Execution timed out (10 seconds limit). Please optimize your code.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function submitCode(Request $request)
    {
        $request->validate([
            'solution' => 'required',
            'question_id' => 'required|exists:questions,question_ID'
        ]);

        $code = $request->solution;
        $language = strtolower(session('test_language'));
        $question = Question::find($request->question_id);

        // Get ALL test cases from the database (sample + hidden test cases)
        $testCases = is_string($question->input) ? json_decode($question->input, true) : $question->input;
        $expectedOutputs = is_string($question->expected_output) ? json_decode($question->expected_output, true) : $question->expected_output;

        if (!$testCases || !is_array($testCases) || empty($testCases)) {
            return redirect()->back()->with('error', 'No test cases available for this question.');
        }

        // Run code against ALL test cases (sample + hidden)
        $totalTests = count($testCases);
        $passedTests = 0;
        $testResults = [];

        foreach ($testCases as $index => $input) {
            $expectedOutput = isset($expectedOutputs[$index]) ? 
                (is_array($expectedOutputs[$index]) ? json_encode($expectedOutputs[$index]) : (string)$expectedOutputs[$index]) 
                : '';
            
            // Convert input to string if needed
            $inputString = is_array($input) ? json_encode($input) : (string)$input;
            
            $result = $this->executeCodeInDocker($code, $language, $inputString);
            
            if ($result['success']) {
                $actualOutput = trim($result['output']);
                $expectedOutput = trim($expectedOutput);
                $passed = $actualOutput === $expectedOutput;
                
                if ($passed) {
                    $passedTests++;
                }

                // Store test results but mark hidden test cases
                $testResults[] = [
                    'test_number' => $index + 1,
                    'is_sample' => $index === 0, // First test case is the sample
                    'input' => $index === 0 ? $inputString : 'Hidden', // Hide input for hidden test cases
                    'expected' => $index === 0 ? $expectedOutput : 'Hidden', // Hide expected output for hidden test cases
                    'actual' => $index === 0 ? $actualOutput : ($passed ? 'Passed' : 'Failed'), // Hide actual output for hidden test cases
                    'passed' => $passed
                ];
            } else {
                // If execution failed, mark as failed
                $testResults[] = [
                    'test_number' => $index + 1,
                    'is_sample' => $index === 0,
                    'input' => $index === 0 ? $inputString : 'Hidden',
                    'expected' => $index === 0 ? $expectedOutput : 'Hidden',
                    'actual' => $index === 0 ? $result['output'] : 'Failed',
                    'passed' => false
                ];
            }
        }

        // Calculate score for this question (10 points max per question)
        $scorePercentage = ($passedTests / $totalTests) * 100;
        $questionScore = ($scorePercentage / 100) * 10; // Scale to 10 points

        // Store the solution and test results
        $codeSolutions = session('code_solutions', []);
        $codeSolutions[$request->question_id] = [
            'solution' => $code,
            'score' => $questionScore,
            'passed_tests' => $passedTests,
            'total_tests' => $totalTests,
            'test_results' => $testResults
        ];
        session()->put('code_solutions', $codeSolutions);

        // Store current submission feedback in session
        session()->put('submission_feedback', [
            'question_id' => $request->question_id,
            'question_title' => $question->title,
            'passed_tests' => $passedTests,
            'total_tests' => $totalTests,
            'test_results' => $testResults,
            'score' => $questionScore
        ]);

        // Increment the index
        $currentIndex = session('current_code_index', 0);
        $newIndex = $currentIndex + 1;
        session()->put('current_code_index', $newIndex);
        
        // Save session to ensure it persists
        session()->save();

        // Redirect to feedback page to show test results
        return redirect()->route('reviewer.competency.code.feedback');
    }

    public function showCodeFeedback()
    {
        $feedback = session('submission_feedback');
        
        if (!$feedback) {
            return redirect()->route('reviewer.competency.code');
        }

        $codeQuestions = session('code_questions');
        $currentIndex = session('current_code_index', 0);
        $isLastQuestion = $currentIndex >= count($codeQuestions);

        return view('reviewer.competency.code-feedback', [
            'feedback' => $feedback,
            'isLastQuestion' => $isLastQuestion,
            'currentQuestion' => $currentIndex,
            'totalQuestions' => count($codeQuestions)
        ]);
    }

    public function continueFromFeedback()
    {
        // Clear the feedback from session
        session()->forget('submission_feedback');

        $codeQuestions = session('code_questions');
        $currentIndex = session('current_code_index', 0);
        
        // Check if we've completed all code questions
        if ($currentIndex >= count($codeQuestions)) {
            return $this->submitTest();
        }

        return redirect()->route('reviewer.competency.code');
    }

    public function submitTest()
    {
        $reviewer = Auth::guard('reviewer')->user();
        $language = session('test_language');
        $mcqAnswers = session('mcq_answers', []);
        $codeSolutions = session('code_solutions', []);

        // Calculate score for MCQ phase (4 MCQ_Question + 2 Question_Evaluation = 6 questions x 10 points = 60 points max)
        $mcqScore = 0;
        foreach ($mcqAnswers as $questionId => $answer) {
            $question = Question::find($questionId);
            if ($question && $question->answersData === $answer) {
                $mcqScore += 10; // 10 points per question
            }
        }

        // Calculate code solution score from actual test results
        $codeScore = 0;
        foreach ($codeSolutions as $questionId => $solutionData) {
            if (is_array($solutionData) && isset($solutionData['score'])) {
                $codeScore += $solutionData['score'];
            }
        }

        // Total: 60 + 20 = 80 points max, scale to 100
        $totalScore = (($mcqScore + $codeScore) / 80) * 100;
        $plagiarismScore = 100; // Placeholder for plagiarism detection

        $levelAchieved = null;
        $passed = false;

        if ($totalScore >= 90) {
            $levelAchieved = 'all';
            $passed = true;
        } elseif ($totalScore >= 75) {
            $levelAchieved = 'intermediate';
            $passed = true;
        } elseif ($totalScore >= 50) {
            $levelAchieved = 'beginner';
            $passed = true;
        }

        // Save test result
        $result = CompetencyTestResult::create([
            'reviewer_ID' => $reviewer->reviewer_ID,
            'language' => $language,
            'mcq_score' => $mcqScore,
            'code_score' => $codeScore,
            'total_score' => round($totalScore),
            'plagiarism_score' => $plagiarismScore,
            'level_achieved' => $levelAchieved,
            'passed' => $passed,
            'mcq_answers' => $mcqAnswers,
            'code_solutions' => $codeSolutions,
            'completed_at' => now()
        ]);

        // Update reviewer qualification status
        if ($passed) {
            $reviewer->update(['isQualified' => true]);
        }

        // Clear session
        session()->forget(['test_language', 'test_started_at', 'mcq_questions', 'current_mcq_index', 
                          'mcq_answers', 'code_questions', 'current_code_index', 'code_solutions']);

        return redirect()->route('reviewer.competency.result', $result->id);
    }

    public function showResult($id)
    {
        $result = CompetencyTestResult::findOrFail($id);
        $reviewer = Auth::guard('reviewer')->user();

        // Ensure the result belongs to the current reviewer
        if ($result->reviewer_ID !== $reviewer->reviewer_ID) {
            abort(403);
        }

        return view('reviewer.competency.result', compact('result'));
    }
}
