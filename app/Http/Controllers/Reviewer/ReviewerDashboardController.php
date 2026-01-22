<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Question;
use App\Models\Reviewer;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\AchievementService;

class ReviewerDashboardController extends Controller
{
    public function index()
    {
        $reviewer = Auth::guard('reviewer')->user();
        
        // Get statistics
        $pendingReviews = $this->getPendingReviewsCount();
        $approvedToday = $this->getApprovedTodayCount();
        $totalReviewed = $this->getTotalReviewedCount($reviewer);
        $correctionsMade = $this->getCorrectionsMadeCount($reviewer);
        
        // Get weekly review data
        $weeklyData = $this->getWeeklyReviewData($reviewer);
        
        // Get pending questions (limit 3 for display)
        $pendingQuestions = $this->getPendingQuestions(3);
        
        // Get recent review activities
        $recentActivities = $this->getRecentActivities($reviewer, 4);
        
        return view('reviewer.dashboard', compact(
            'reviewer',
            'pendingReviews',
            'approvedToday',
            'totalReviewed',
            'correctionsMade',
            'weeklyData',
            'pendingQuestions',
            'recentActivities'
        ));
    }
    
    public function profile()
    {
        $reviewer = Auth::guard('reviewer')->user();
        
        // Get all competency test results that were passed, grouped by language with highest scores only
        $competencyResults = \App\Models\CompetencyTestResult::where('reviewer_ID', $reviewer->reviewer_ID)
            ->where('passed', true)
            ->orderBy('total_score', 'desc')
            ->orderBy('completed_at', 'desc')
            ->get()
            ->groupBy('language')
            ->map(function($languageResults) {
                // Return only the highest score for each language (first item after sorting by score desc)
                return $languageResults->first();
            })
            ->values(); // Reset array keys
        
        // Calculate statistics
        $stats = [
            'totalReviewed' => $this->getTotalReviewedCount($reviewer),
            'correctionsMade' => $this->getCorrectionsMadeCount($reviewer),
            'currentStreak' => \App\Models\ReviewerSession::getCurrentStreak($reviewer->reviewer_ID)
        ];
        
        return view('reviewer.profile', compact('reviewer', 'competencyResults', 'stats'));
    }

    /**
     * Generate and download certificate for competency test
     */
    public function downloadCertificate()
    {
        $reviewer = Auth::guard('reviewer')->user();
        
        // Get the latest passed competency test result
        $competencyResult = \App\Models\CompetencyTestResult::where('reviewer_ID', $reviewer->reviewer_ID)
            ->where('passed', true)
            ->latest()
            ->first();
        
        if (!$competencyResult) {
            return redirect()->back()->with('error', 'No competency test certificate available.');
        }
        
        // Generate certificate ID only if it doesn't exist
        if (empty($competencyResult->certificate_id)) {
            $certificate_id = $competencyResult->id . '-' . $competencyResult->reviewer_ID . '-' . strtoupper($competencyResult->language);
            
            // Store the certificate ID in the database
            $competencyResult->certificate_id = $certificate_id;
            $competencyResult->save();
        } else {
            // Use the existing certificate ID
            $certificate_id = $competencyResult->certificate_id;
        }
        
        // Prepare data for the certificate
        $data = [
            'name' => $reviewer->username,
            'language' => $competencyResult->language,
            'date' => $competencyResult->completed_at->format('d F Y'),
            'score' => $competencyResult->total_score,
            'plagiarism_score' => $competencyResult->plagiarism_score,
            'certificate_id' => $certificate_id
        ];
        
        // Generate PDF
        $pdf = Pdf::loadView('reviewer.certificate', $data);
        $pdf->setPaper('a4', 'landscape');
        
        // Download the PDF
        $filename = 'Certificate_' . str_replace(' ', '_', $reviewer->username) . '_' . $competencyResult->language . '.pdf';
        
        return $pdf->download($filename);
    }
    
    public function review()
    {
        $reviewer = Auth::guard('reviewer')->user();
        
        $qualifiedLanguages = \App\Models\CompetencyTestResult::where('reviewer_ID', $reviewer->reviewer_ID)
            ->where('passed', true)
            ->pluck('language')
            ->toArray();
            
        if (empty($qualifiedLanguages)) {
            $pendingQuestions = collect();
            $currentQuestion = null;
        } else {
            // Get pending questions that match reviewer's qualifications
            $pendingQuestions = Question::where('status', 'Pending')
                ->whereIn('language', $qualifiedLanguages)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($question) {
                    $secondsPending = Carbon::parse($question->created_at)->diffInSeconds(Carbon::now());
                    
                    return [
                        'id' => $question->question_ID,
                        'title' => $question->title ?? 'Untitled Question',
                        'description' => $question->description ?? 'No description available',
                        'problem_statement' => $question->problem_statement ?? 'No problem statement available',
                        'constraints' => $question->constraints ?? 'No constraints available',
                        'difficulty' => ucfirst($question->level ?? 'intermediate'),
                        'language' => $question->language,
                        'category' => $question->questionCategory ?? 'General',
                        'chapter' => $question->chapter ?? 'N/A',
                        'seconds_pending' => $secondsPending,
                        'time_ago' => $this->formatTimeFromSeconds($secondsPending),
                        'status' => $question->status,
                        'question_type' => $question->questionType
                    ];
                });
            
            // Get the first question's full details for display
            $firstQuestion = Question::where('status', 'Pending')
                ->whereIn('language', $qualifiedLanguages)
                ->orderBy('created_at', 'asc')
                ->first();
            
            $currentQuestion = $firstQuestion ? [
                'id' => $firstQuestion->question_ID,
                'title' => $firstQuestion->title ?? 'Untitled Question',
                'description' => $firstQuestion->description ?? 'No description available',
                'problem_statement' => $firstQuestion->problem_statement ?? 'No problem statement available',
                'constraints' => $firstQuestion->constraints ?? 'No constraints available',
                'difficulty' => ucfirst($firstQuestion->level ?? 'intermediate'),
                'language' => $firstQuestion->language,
                'category' => $firstQuestion->questionCategory ?? 'General',
                'chapter' => $firstQuestion->chapter ?? 'N/A',
                'hint' => $firstQuestion->hint,
                'input' => $firstQuestion->input ?? 'N/A',
                'expected_output' => $firstQuestion->expected_output ?? 'N/A',
                'solution' => $firstQuestion->answersData ?? $firstQuestion->expectedAnswer,
                'question_type' => $firstQuestion->questionType,
                'options' => $firstQuestion->options ?? [],
                'seconds_ago' => Carbon::parse($firstQuestion->created_at)->diffInSeconds(Carbon::now()),
                'time_ago' => $this->formatTimeFromSeconds(Carbon::parse($firstQuestion->created_at)->diffInSeconds(Carbon::now()))
            ] : null;
        }
        
        return view('reviewer.review', compact('reviewer', 'pendingQuestions', 'currentQuestion', 'qualifiedLanguages'));
    }
    
    public function history()
    {
        $reviewer = Auth::guard('reviewer')->user();
        
        $questions = Question::where('status', 'Approved')
            ->where('questionCategory', 'learnerPractice')
            ->with(['reviewer' => function($query) {
                $query->select('reviewer_ID', 'username');
            }])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($question) {
                return (object)[
                    'id' => $question->question_ID,
                    'chapter' => $question->chapter ?? 'Programming Challenge',
                    'title' => $question->title ?? 'Untitled Question',
                    'description' => $question->description ?? 'No description available',
                    'content' => $question->content ?? $question->description ?? 'No content available',
                    'problem_statement' => $question->problem_statement ?? '',
                    'constraints' => $question->constraints ?? '',
                    'input_format' => $question->input ?? '',
                    'output_format' => $question->expected_output ?? '',
                    'hint' => $question->hint ?? '',
                    'difficulty' => $question->level ?? 'Intermediate',
                    'language' => $question->language ?? 'Python',
                    'topic' => $question->questionCategory ?? 'Algorithms',
                    'approved_at' => $question->updated_at,
                    'approver' => (object)[
                        'username' => $question->reviewer->username ?? 'Unknown'
                    ]
                ];
            });
        
        $totalCount = $questions->count();
        
        return view('reviewer.history', compact('reviewer', 'questions', 'totalCount'));
    }
    
    public function getQuestionDetails($questionId)
    {
        $reviewer = Auth::guard('reviewer')->user();
        
        // Get languages the reviewer is qualified for
        $qualifiedLanguages = \App\Models\CompetencyTestResult::where('reviewer_ID', $reviewer->reviewer_ID)
            ->where('passed', true)
            ->pluck('language')
            ->toArray();
        
        // Get the question details
        $question = Question::where('question_ID', $questionId)
            ->where('status', 'Pending')
            ->whereIn('language', $qualifiedLanguages)
            ->first();
        
        if (!$question) {
            return response()->json(['error' => 'Question not found or not accessible'], 404);
        }
        
        $secondsAgo = Carbon::parse($question->created_at)->diffInSeconds(Carbon::now());
        
        // Format test cases from input and expected_output columns
        $testCases = [];
        if ($question->input && is_array($question->input)) {
            // Get expected outputs - could be array, JSON string, or plain value
            $expectedOutputs = $question->expected_output;
            
            // If it's a string, try to decode it as JSON
            if (is_string($expectedOutputs)) {
                $decoded = json_decode($expectedOutputs, true);
                // If JSON decode succeeded, use the decoded value
                if (json_last_error() === JSON_ERROR_NONE) {
                    $expectedOutputs = $decoded;
                }
                // Otherwise keep it as a string (single value for all test cases)
            }
            
            // Create test cases array by pairing inputs with outputs
            foreach ($question->input as $index => $input) {
                $expectedOutput = 'N/A';
                
                if (is_array($expectedOutputs) && isset($expectedOutputs[$index])) {
                    // If expectedOutputs is an array, get the corresponding index
                    $expectedOutput = $expectedOutputs[$index];
                } elseif (!is_array($expectedOutputs) && $expectedOutputs !== null) {
                    // If it's a single value, use it for all test cases
                    $expectedOutput = $expectedOutputs;
                }
                
                $testCases[] = [
                    'input' => $input,
                    'expected_output' => $expectedOutput
                ];
            }
        }
        
        $questionData = [
            'id' => $question->question_ID,
            'title' => $question->title ?? 'Untitled Question',
            'description' => $question->description ?? 'No description available',
            'problem_statement' => $question->problem_statement ?? 'No problem statement available',
            'constraints' => $question->constraints ?? 'No constraints available',
            'difficulty' => ucfirst($question->level ?? 'intermediate'),
            'language' => $question->language,
            'category' => $question->questionCategory ?? 'General',
            'chapter' => $question->chapter ?? 'N/A',
            'hint' => $question->hint,
            'test_cases' => $testCases,
            'solution' => $question->answersData ?? $question->expectedAnswer,
            'question_type' => $question->questionType,
            'options' => $question->options ?? [],
            'seconds_ago' => $secondsAgo,
            'time_ago' => $this->formatTimeFromSeconds($secondsAgo)
        ];
        
        return response()->json($questionData);
    }

    /**
     * Submit grade for a question
     */
    public function submitGrade(Request $request)
    {
        $reviewer = Auth::guard('reviewer')->user();
        
        // Validate the request
        $validated = $request->validate([
            'question_id' => 'required|integer',
            'quality_score' => 'required|integer|min:0|max:100',
            'clarity_score' => 'required|integer|min:0|max:100',
            'difficulty_score' => 'required|integer|min:0|max:100',
            'testcases_score' => 'required|integer|min:0|max:100',
            'overall_grade' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string',
            'approved' => 'required|boolean'
        ]);
        
        // Get the question
        $question = Question::find($validated['question_id']);
        
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found'
            ], 404);
        }
        
        // Check if question is pending
        if ($question->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Question has already been reviewed'
            ], 400);
        }
        
        // Check if reviewer is qualified for this language
        $isQualified = \App\Models\CompetencyTestResult::where('reviewer_ID', $reviewer->reviewer_ID)
            ->where('language', $question->language)
            ->where('passed', true)
            ->exists();
        
        if (!$isQualified) {
            return response()->json([
                'success' => false,
                'message' => 'You are not qualified to review questions in this language'
            ], 403);
        }
        
        $question->status = $validated['overall_grade'] >= 70 ? 'Approved' : 'Rejected';
        $question->reviewer_ID = $reviewer->reviewer_ID;
        
        $question->grading_details = json_encode([
            'quality_score' => $validated['quality_score'],
            'clarity_score' => $validated['clarity_score'],
            'difficulty_score' => $validated['difficulty_score'],
            'testcases_score' => $validated['testcases_score'],
            'overall_grade' => $validated['overall_grade'],
            'feedback' => $validated['feedback'],
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->username
        ]);
        $question->save();
        
        // ============================================================
        // ACHIEVEMENT SYSTEM: Update Stats & Check Badges
        // ============================================================
        $reviewer->total_reviews++;
        
        // Track approved vs rejected reviews
        if ($validated['overall_grade'] >= 70) {
            $reviewer->clean_reviews_count++; // "No Errors" - Approved
        } else {
            $reviewer->errors_flagged_count++; // "Errors Found" - Rejected
        }
        
        $reviewer->save();
        
        // Check for earned badges
        $achievementService = app(AchievementService::class);
        $achievementService->checkReviewerBadges($reviewer);
        
        return response()->json([
            'success' => true,
            'message' => $validated['overall_grade'] >= 70 ? 'Question approved successfully!' : 'Question rejected (grade below 70%)',
            'status' => $question->status,
            'grade' => $validated['overall_grade']
        ]);
    }

    /**
     * Edit a question
     */
    public function editQuestion(Request $request)
    {
        $reviewer = Auth::guard('reviewer')->user();
        
        // Validate the request - support both inline editing and modal editing
        $validated = $request->validate([
            'question_id' => 'required|integer',
            'description' => 'nullable|string',
            'problem_statement' => 'nullable|string',
            'constraints' => 'nullable|string',
            'hint' => 'nullable|string',
            'content' => 'nullable|string',
            'solution' => 'nullable|string',
            'options' => 'nullable|array',
            'test_cases' => 'nullable|array'
        ]);
        
        // Get the question
        $question = Question::find($validated['question_id']);
        
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found'
            ], 404);
        }
        
        // Check if question is pending
        if ($question->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending questions can be edited'
            ], 400);
        }
        
        // Check if reviewer is qualified for this language
        $isQualified = \App\Models\CompetencyTestResult::where('reviewer_ID', $reviewer->reviewer_ID)
            ->where('language', $question->language)
            ->where('passed', true)
            ->exists();
        
        if (!$isQualified) {
            return response()->json([
                'success' => false,
                'message' => 'You are not qualified to edit questions in this language'
            ], 403);
        }
        
        // ============================================================
        // CORRECTION TRACKING: Detect if any fields were changed
        // ============================================================
        $hasChanges = false;
        
        if (isset($validated['description'])) {
            $cleanDescription = strip_tags(html_entity_decode($validated['description']));
            if ($cleanDescription !== $question->description) {
                $hasChanges = true;
                $question->description = $cleanDescription;
            }
        }
        
        // Check if problem_statement changed
        if (isset($validated['problem_statement'])) {
            $cleanStatement = strip_tags(html_entity_decode($validated['problem_statement']));
            if ($cleanStatement !== $question->problem_statement) {
                $hasChanges = true;
                $question->problem_statement = $cleanStatement;
            }
        }
        
        // Check if constraints changed
        if (isset($validated['constraints'])) {
            $cleanConstraints = strip_tags(html_entity_decode($validated['constraints']));
            if ($cleanConstraints !== $question->constraints) {
                $hasChanges = true;
                $question->constraints = $cleanConstraints;
            }
        }
        
        // Check if hint changed
        if (isset($validated['hint'])) {
            $hintText = strip_tags(html_entity_decode($validated['hint']));
            $hintText = preg_replace('/^💡\s*Hint:\s*/i', '', $hintText);
            $cleanHint = trim($hintText);
            if ($cleanHint !== $question->hint) {
                $hasChanges = true;
                $question->hint = $cleanHint;
            }
        }
        
        // Check if content changed
        if (isset($validated['content']) && $validated['content'] !== $question->content) {
            $hasChanges = true;
            $question->content = $validated['content'];
        }
        
        // Check if solution changed
        if (isset($validated['solution']) && $validated['solution'] !== $question->answersData) {
            $hasChanges = true;
            $question->answersData = $validated['solution'];
        }
        
        // Check if options changed (for MCQ)
        if ($question->questionType === 'MCQ_Single' && isset($validated['options'])) {
            if (json_encode($validated['options']) !== json_encode($question->options)) {
                $hasChanges = true;
                $question->options = $validated['options'];
            }
        }
        
        // Check if test cases changed
        if ($question->questionType !== 'MCQ_Single' && isset($validated['test_cases']) && is_array($validated['test_cases'])) {
            $inputs = [];
            $outputs = [];
            
            foreach ($validated['test_cases'] as $testCase) {
                if (isset($testCase['input'])) {
                    $inputs[] = $testCase['input'];
                }
                if (isset($testCase['expected_output'])) {
                    $outputs[] = $testCase['expected_output'];
                }
            }
            
            if (!empty($inputs) && json_encode($inputs) !== json_encode($question->input)) {
                $hasChanges = true;
                $question->input = $inputs;
            }
            if (!empty($outputs) && json_encode($outputs) !== json_encode($question->expected_output)) {
                $hasChanges = true;
                $question->expected_output = $outputs;
            }
        }
        
        // Mark question as edited if changes were detected
        if ($hasChanges) {
            $question->was_edited = true;
        }
        
        $question->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Question edited successfully!'
        ]);
    }

    /**
     * Remove the Hints section from question content
     */
    private function removeHintsSection($content)
    {
        if (!$content) {
            return $content;
        }
        
        // Remove ## Hints section (including the heading and content until next section or end)
        $content = preg_replace('/##\s*Hints?\s*\n.*?(?=\n##|\n\n##|$)/is', '', $content);
        
        // Clean up any extra blank lines that might be left
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        return trim($content);
    }

    /**
     * Calculate average time reviewer spends on the system
     */
    private function getAverageReviewTime($reviewerId)
    {
        $avgMinutes = \App\Models\ReviewerSession::getAverageSessionDuration($reviewerId);
        
        if (!$avgMinutes || $avgMinutes == 0) {
            return 'N/A';
        }
        
        // Format the output
        if ($avgMinutes < 60) {
            return round($avgMinutes) . ' min';
        } else {
            $hours = floor($avgMinutes / 60);
            $minutes = round($avgMinutes % 60);
            
            if ($minutes == 0) {
                return $hours . ' hr';
            }
            return $hours . ' hr ' . $minutes . ' min';
        }
    }
    
    private function getPendingReviewsCount()
    {
        $reviewer = Auth::guard('reviewer')->user();
        
        // Get languages the reviewer is qualified for
        $qualifiedLanguages = \App\Models\CompetencyTestResult::where('reviewer_ID', $reviewer->reviewer_ID)
            ->where('passed', true)
            ->pluck('language')
            ->toArray();
        
        if (empty($qualifiedLanguages)) {
            return 0;
        }
        
        return Question::where('status', 'Pending')
            ->whereIn('language', $qualifiedLanguages)
            ->count();
    }
    
    private function getApprovedTodayCount()
    {
        return Question::where('status', 'Approved')
            ->whereDate('updated_at', Carbon::today())
            ->count();
    }
    
    private function getTotalReviewedCount($reviewer)
    {
        // Count questions that were reviewed (approved or rejected) by this specific reviewer
        return Question::where('reviewer_ID', $reviewer->reviewer_ID)
            ->whereIn('status', ['Approved', 'Rejected'])
            ->count();
    }
    
    /**
     * Count how many questions were edited/corrected by the reviewer before approval
     */
    private function getCorrectionsMadeCount($reviewer)
    {
        return Question::where('reviewer_ID', $reviewer->reviewer_ID)
            ->where('was_edited', true)
            ->whereIn('status', ['Approved', 'Rejected'])
            ->count();
    }
    
    private function getAccuracyRate($reviewer)
    {
        // Placeholder calculation - you'll need to implement based on your review tracking
        $total = Question::whereIn('status', ['Approved', 'Rejected'])->count();
        $approved = Question::where('status', 'Approved')->count();
        
        if ($total == 0) return 0;
        
        return round(($approved / $total) * 100, 1);
    }
    
    private function getWeeklyReviewData($reviewer)
    {
        $data = [];
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            // Count only reviews made by this specific reviewer
            $count = Question::where('reviewer_ID', $reviewer->reviewer_ID)
                ->whereIn('status', ['Approved', 'Rejected'])
                ->whereDate('updated_at', $date)
                ->count();
            
            $data[] = [
                'day' => $days[($date->dayOfWeek + 6) % 7],
                'count' => $count
            ];
        }
        
        return $data;
    }
    
    private function getPendingQuestions($limit = 3)
    {
        $reviewer = Auth::guard('reviewer')->user();
        
        // Get languages the reviewer is qualified for
        $qualifiedLanguages = \App\Models\CompetencyTestResult::where('reviewer_ID', $reviewer->reviewer_ID)
            ->where('passed', true)
            ->pluck('language')
            ->toArray();
        
        if (empty($qualifiedLanguages)) {
            return collect();
        }
        
        $questions = Question::where('status', 'Pending')
            ->whereIn('language', $qualifiedLanguages)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($question) {
                // Calculate how many days pending (whole number)
                $daysPending = floor(Carbon::parse($question->created_at)->diffInDays(Carbon::now()));
                
                // Calculate seconds pending for timer functionality
                $secondsPending = Carbon::parse($question->created_at)->diffInSeconds(Carbon::now());
                
                // Determine priority based on days pending
                $priority = 'High Priority';
                if ($daysPending < 2) {
                    $priority = 'Low Priority';
                } elseif ($daysPending < 4) {
                    $priority = 'Medium Priority';
                }
                
                // Use the title field from database, fallback to "Untitled Question"
                $title = $question->title ?? 'Untitled Question';
                
                // Limit length if needed
                if (strlen($title) > 80) {
                    $title = substr($title, 0, 80) . '...';
                }
                
                return [
                    'id' => $question->question_ID,
                    'title' => $title,
                    'difficulty' => ucfirst($question->level ?? 'intermediate'),
                    'days_pending' => $daysPending,
                    'seconds_pending' => $secondsPending,
                    'priority' => $priority,
                    'language' => $question->language
                ];
            });
        
        return $questions;
    }
    
    private function getRecentActivities($reviewer, $limit = 5)
    {
        // Get recently updated questions by this specific reviewer
        $activities = collect();
        
        // Get approved/rejected questions by this reviewer
        $reviewedQuestions = Question::where('reviewer_ID', $reviewer->reviewer_ID)
            ->whereIn('status', ['Approved', 'Rejected'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($question) {
                $minutesAgo = Carbon::parse($question->updated_at)->diffInMinutes(Carbon::now());
                
                return [
                    'type' => 'review',
                    'status' => $question->status,
                    'title' => $question->title ?? 'Untitled Question',
                    'time_ago' => $this->formatTimeAgo($minutesAgo),
                    'timestamp' => $question->updated_at
                ];
            });
        
        // Get generated questions by this reviewer (pending status with this reviewer_ID)
        $generatedQuestions = Question::where('reviewer_ID', $reviewer->reviewer_ID)
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($question) {
                $minutesAgo = Carbon::parse($question->created_at)->diffInMinutes(Carbon::now());
                
                return [
                    'type' => 'generate',
                    'status' => 'Generated',
                    'title' => $question->title ?? 'Untitled Question', 
                    'time_ago' => $this->formatTimeAgo($minutesAgo),
                    'timestamp' => $question->created_at
                ];
            });
        
        // Combine and sort by timestamp
        $activities = $reviewedQuestions->concat($generatedQuestions)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();
        
        return $activities;
    }
    
    private function formatTimeAgo($minutes)
    {
        $seconds = $minutes * 60;
        
        if ($seconds < 60) {
            // Less than 1 minute - show seconds
            $sec = round($seconds);
            return $sec . ($sec == 1 ? ' second ago' : ' seconds ago');
        } elseif ($minutes < 60) {
            // Less than 1 hour - show minutes
            $min = round($minutes);
            return $min . ($min == 1 ? ' minute ago' : ' minutes ago');
        } elseif ($minutes < 1440) {
            // Less than 1 day - show hours
            $hours = floor($minutes / 60);
            return $hours . ($hours == 1 ? ' hour ago' : ' hours ago');
        } elseif ($minutes < 43200) {
            // Less than 30 days - show days
            $days = floor($minutes / 1440);
            return $days . ($days == 1 ? ' day ago' : ' days ago');
        } else {
            // More than 30 days - show months
            $months = floor($minutes / 43200);
            return $months . ($months == 1 ? ' month ago' : ' months ago');
        }
    }

    /**
     * Format time from seconds to appropriate unit
     */
    private function formatTimeFromSeconds($seconds)
    {
        if ($seconds < 60) {
            // Less than 1 minute - show seconds
            return round($seconds) . 's ago';
        } elseif ($seconds < 3600) {
            // Less than 1 hour - show minutes
            $minutes = round($seconds / 60);
            return $minutes . 'm ago';
        } elseif ($seconds < 86400) {
            // Less than 1 day - show hours
            $hours = round($seconds / 3600);
            return $hours . 'h ago';
        } else {
            // Show days
            $days = round($seconds / 86400);
            return $days . 'd ago';
        }
    }
}