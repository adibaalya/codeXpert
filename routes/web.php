<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Learner\CustomizationPathController;
use App\Http\Controllers\Learner\LearnerDashboardController;
use App\Http\Controllers\Learner\CodingQuestionController;
use App\Http\Controllers\Reviewer\CompetencyTestController;
use App\Http\Controllers\Reviewer\ReviewerDashboardController;
use App\Http\Controllers\Reviewer\QuestionGeneratorController;

// Social Authentication Routes
Route::get('/auth/{provider}', [SocialLoginController::class, 'redirectToProvider'])
    ->where('provider', 'google|github')
    ->name('auth.provider');
Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])
    ->where('provider', 'google|github')
    ->name('auth.provider.callback');

Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Learner Customization Path Routes (Protected by learner guard)
Route::middleware(['auth:learner'])->prefix('learner')->group(function () {
    // Dashboard
    Route::get('/dashboard', [LearnerDashboardController::class, 'index'])->name('learner.dashboard');
    
    // Practice
    Route::get('/practice', function() {
        $learner = auth()->guard('learner')->user();
        
        // Get all available languages from questions table where questionCategory is 'learnerPractice'
        $languages = \DB::table('questions')
            ->where('status', 'Approved')
            ->where('questionCategory', 'learnerPractice')
            ->distinct()
            ->pluck('language');
        
        return view('learner.practice', compact('learner', 'languages'));
    })->name('learner.practice');
    
    // API endpoint to get available topics based on language and level
    Route::get('/practice/topics', function(Illuminate\Http\Request $request) {
        $language = $request->input('language');
        $level = $request->input('level');
        
        $topics = \DB::table('questions')
            ->where('status', 'Approved')
            ->where('questionCategory', 'learnerPractice')
            ->where('language', $language)
            ->where('level', $level)
            ->whereNotNull('chapter')
            ->distinct()
            ->pluck('chapter');
        
        return response()->json(['topics' => $topics]);
    })->name('learner.practice.topics');
    
    // Coding Question Routes
    Route::get('/coding/question/{questionId}', [CodingQuestionController::class, 'show'])->name('learner.coding.show');
    Route::get('/coding/random', [CodingQuestionController::class, 'random'])->name('learner.coding.random');
    Route::get('/coding/suggested', [CodingQuestionController::class, 'suggested'])->name('learner.coding.suggested');
    
    // Code Execution Routes (for run and submit)
    Route::post('/coding/run', [\App\Http\Controllers\CodeExecutionController::class, 'runCode'])->name('learner.coding.run');
    Route::post('/coding/submit', [\App\Http\Controllers\CodeExecutionController::class, 'submitCode'])->name('learner.coding.submit');
    Route::post('/coding/rate', [\App\Http\Controllers\CodeExecutionController::class, 'rateQuestion'])->name('learner.coding.rate');
    Route::get('/coding/result', function() {
        $submission = session('coding_submission');
        
        if (!$submission) {
            return redirect()->route('learner.practice')->with('error', 'No submission found.');
        }
        
        // Use the same result view as competency test, but adapt it for learners
        return view('learner.coding-result', compact('submission'));
    })->name('learner.coding.result');
    
    // Progress (placeholder routes - you'll need to implement these)
    Route::get('/progress', function() {
        return view('learner.progress');
    })->name('learner.progress');
    
    // Leaderboard
    Route::get('/leaderboard', function() {
        $learner = auth()->guard('learner')->user();
        
        // Get current user's total XP from database
        $currentUserXP = $learner->totalPoint;
        
        // Calculate user's rank based on totalPoint (descending order)
        // Users with higher XP get a better rank
        $currentUserRank = \App\Models\Learner::where('totalPoint', '>', $currentUserXP)->count() + 1;
        
        // Calculate weekly XP (Monday to Sunday)
        $startOfWeek = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek = \Carbon\Carbon::now()->endOfWeek(\Carbon\Carbon::SUNDAY);
        
        // Sum XP from attempts this week (using accuracyScore as XP earned per attempt)
        $currentUserWeeklyXP = \DB::table('attempts')
            ->where('learner_ID', $learner->learner_ID)
            ->whereBetween('dateAttempted', [$startOfWeek, $endOfWeek])
            ->sum('accuracyScore');
        
        // Round the weekly XP to integer
        $currentUserWeeklyXP = (int) round($currentUserWeeklyXP);
        
        // Get all learners sorted by totalPoint (descending), then by username for tie-breaking
        $allLearners = \App\Models\Learner::orderBy('totalPoint', 'desc')
            ->orderBy('username', 'asc')
            ->get();
        
        // Assign ranks with tie handling
        $leaderboardData = [];
        $currentRank = 1;
        $previousXP = null;
        $sameRankCount = 0;
        
        foreach ($allLearners as $index => $topLearner) {
            // If XP is the same as previous, use the same rank
            if ($previousXP !== null && $topLearner->totalPoint == $previousXP) {
                $rank = $currentRank;
                $sameRankCount++;
            } else {
                // New XP value, calculate new rank
                $rank = $index + 1;
                $currentRank = $rank;
                $sameRankCount = 0;
            }
            
            $previousXP = $topLearner->totalPoint;
            
            // Calculate weekly XP for this learner
            $weeklyXP = \DB::table('attempts')
                ->where('learner_ID', $topLearner->learner_ID)
                ->whereBetween('dateAttempted', [$startOfWeek, $endOfWeek])
                ->sum('accuracyScore');
            
            $leaderboardData[] = [
                'rank' => $rank,
                'username' => $topLearner->username,
                'xp' => $topLearner->totalPoint,
                'weeklyXP' => (int) round($weeklyXP)
            ];
            
            // Stop after collecting 15 entries (but include all ties for rank 15)
            if (count($leaderboardData) >= 15 && 
                ($index + 1 >= count($allLearners) || $allLearners[$index + 1]->totalPoint != $previousXP)) {
                break;
            }
        }
        
        return view('learner.leaderboard', compact('learner', 'currentUserRank', 'currentUserXP', 'currentUserWeeklyXP', 'leaderboardData'));
    })->name('learner.leaderboard');
    
    // Hackathon
    Route::get('/hackathon', [LearnerDashboardController::class, 'hackathon'])->name('learner.hackathon');
    
    // Profile (placeholder routes - you'll need to implement these)
    Route::get('/profile', function() {
        $learner = auth()->guard('learner')->user();
        
        // Use Quadratic Curve leveling system
        $levelProgress = $learner->getLevelProgress();
        
        // Calculate global rank
        $globalRank = \App\Models\Learner::where('totalPoint', '>', $learner->totalPoint)->count() + 1;
        
        // Get member since date
        $memberSince = \Carbon\Carbon::parse($learner->created_at)->format('F Y');
        
        // Get proficiencies
        $proficiencies = \App\Models\UserProficiency::where('learner_ID', $learner->learner_ID)->get();
        
        // Calculate statistics
        $totalAttempts = \DB::table('attempts')->where('learner_ID', $learner->learner_ID)->count();
        $successfulAttempts = \DB::table('attempts')
            ->where('learner_ID', $learner->learner_ID)
            ->where('accuracyScore', '>=', 70)
            ->count();
        
        $challengesCompleted = $successfulAttempts;
        $successRate = $totalAttempts > 0 ? round(($successfulAttempts / $totalAttempts) * 100) : 0;
        
        // Calculate total coding time (estimate: 15 minutes per attempt)
        $totalMinutes = $totalAttempts * 15;
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $totalTimeCoding = "{$hours}h {$minutes}m";
        
        // Prepare learner object with additional data using Quadratic Curve
        $learner->currentLevel = $levelProgress['current_level'];
        $learner->xpPoints = $levelProgress['current_xp'];
        $learner->nextLevelXP = $levelProgress['xp_for_next_level'];
        $learner->xpProgress = $levelProgress['xp_progress'];
        $learner->xpGap = $levelProgress['xp_gap'];
        $learner->levelPercentage = $levelProgress['percentage'];
        $learner->globalRank = $globalRank;
        $learner->memberSince = $memberSince;
        $learner->challengesCompleted = $challengesCompleted;
        $learner->successRate = $successRate;
        $learner->totalTimeCoding = $totalTimeCoding;
        
        // Format proficiencies for display - XP-based progress
        $learner->proficiencies = $proficiencies->map(function($prof) use ($learner) {
            // XP thresholds for language proficiency levels
            $maxXP = 100; // Max XP to consider for progress bar (100%)
            
            // Current XP for this language
            $currentXP = $prof->XP;
            
            // Calculate percentage based on XP (0-100%)
            $percentage = min(round(($currentXP / $maxXP) * 100), 100);
            
            // Count actual questions solved for display
            $solvedProblems = \DB::table('attempts')
                ->join('questions', 'attempts.question_ID', '=', 'questions.question_ID')
                ->where('attempts.learner_ID', $learner->learner_ID)
                ->where('questions.language', $prof->language)
                ->where('attempts.accuracyScore', '>', 0) // Any XP earned
                ->distinct('attempts.question_ID')
                ->count('attempts.question_ID');
            
            // Count total available questions for this language
            $totalProblems = \DB::table('questions')
                ->where('language', $prof->language)
                ->where('status', 'Approved')
                ->where('questionCategory', 'learnerPractice')
                ->count();
            
            // If no questions available, show 0/150 as fallback
            if ($totalProblems == 0) {
                $totalProblems = 150;
            }
            
            // Determine level based on XP
            if ($prof->XP < 30) {
                $level = 'Beginner';
            } elseif ($prof->XP < 70) {
                $level = 'Intermediate';
            } else {
                $level = 'Advanced';
            }
            
            return [
                'language' => $prof->language,
                'level' => $level,
                'XP' => $currentXP,
                'maxXP' => $maxXP,
                'percentage' => $percentage,
                'solved' => $solvedProblems,
                'total' => $totalProblems,
                'xp' => $prof->XP
            ];
        });
        
        return view('learner.profile', compact('learner'));
    })->name('learner.profile');
    
    // Edit Profile
    Route::get('/profile/edit', function() {
        $learner = auth()->guard('learner')->user();
        return view('learner.edit-profile', compact('learner'));
    })->name('learner.profile.edit');
    
    Route::put('/profile/update', function(Illuminate\Http\Request $request) {
        $learner = auth()->guard('learner')->user();
        
        $request->validate([
            'username' => 'required|string|max:50|unique:learners,username,' . $learner->learner_ID . ',learner_ID',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        try {
            // Update username
            $learner->username = $request->username;
            
            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($learner->profile_photo && \Storage::disk('public')->exists($learner->profile_photo)) {
                    \Storage::disk('public')->delete($learner->profile_photo);
                }
                
                // Store new photo
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $learner->profile_photo = $path;
            }
            
            // Handle photo removal
            if ($request->remove_photo == '1' && $learner->profile_photo) {
                if (\Storage::disk('public')->exists($learner->profile_photo)) {
                    \Storage::disk('public')->delete($learner->profile_photo);
                }
                $learner->profile_photo = null;
            }
            
            $learner->save();
            
            return redirect()->route('learner.profile')->with('success', 'Profile updated successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update profile. Please try again.');
        }
    })->name('learner.profile.update');
    
    // Customization
    Route::get('/customization', [CustomizationPathController::class, 'show'])->name('learner.customization');
    Route::post('/customization', [CustomizationPathController::class, 'store'])->name('learner.customization.store');
    Route::post('/customization/complete', [CustomizationPathController::class, 'complete'])->name('learner.customization.complete');
    Route::delete('/customization', [CustomizationPathController::class, 'destroy'])->name('learner.customization.destroy');
    
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('learner.logout');
});

// Reviewer Competency Test Routes (Protected by reviewer guard)
Route::middleware(['auth:reviewer'])->prefix('reviewer')->group(function () {
    // Dashboard
    Route::get('/dashboard', [ReviewerDashboardController::class, 'index'])->name('reviewer.dashboard');
    
    // Review
    Route::get('/review', [ReviewerDashboardController::class, 'review'])->name('reviewer.review');
    Route::get('/review/question/{questionId}', [ReviewerDashboardController::class, 'getQuestionDetails'])->name('reviewer.review.question');
    Route::post('/review/grade', [ReviewerDashboardController::class, 'submitGrade'])->name('reviewer.review.grade');
    Route::post('/review/edit', [ReviewerDashboardController::class, 'editQuestion'])->name('reviewer.review.edit');
    
    // History
    Route::get('/history', [ReviewerDashboardController::class, 'history'])->name('reviewer.history');
    
    // Profile
    Route::get('/profile', [ReviewerDashboardController::class, 'profile'])->name('reviewer.profile');
    
    // Certificate Download
    Route::get('/certificate/download', [ReviewerDashboardController::class, 'downloadCertificate'])->name('reviewer.certificate.download');
    
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('reviewer.logout');
    
    // Question Generator
    Route::get('/generate', [QuestionGeneratorController::class, 'showGeneratePage'])->name('reviewer.generate');
    Route::post('/generate/question', [QuestionGeneratorController::class, 'generateQuestion'])->name('reviewer.generate.question');
    Route::post('/generate/save', [QuestionGeneratorController::class, 'saveQuestion'])->name('reviewer.generate.save');
    
    // Competency Test Routes
    Route::get('/competency/choose', [CompetencyTestController::class, 'chooseLanguage'])->name('reviewer.competency.choose');
    Route::post('/competency/start', [CompetencyTestController::class, 'startTest'])->name('reviewer.competency.start');
    
    // MCQ Test
    Route::get('/competency/mcq', [CompetencyTestController::class, 'showMCQ'])->name('reviewer.competency.mcq');
    Route::post('/competency/mcq', [CompetencyTestController::class, 'submitMCQ'])->name('reviewer.competency.mcq.submit');
    Route::post('/competency/mcq/previous', [CompetencyTestController::class, 'previousMCQ'])->name('reviewer.competency.mcq.previous');
    
    // Code Test
    Route::get('/competency/code', [CompetencyTestController::class, 'showCode'])->name('reviewer.competency.code');
    Route::post('/competency/code/run', [CompetencyTestController::class, 'runCode'])->name('reviewer.competency.code.run');
    Route::post('/competency/code', [CompetencyTestController::class, 'submitCode'])->name('reviewer.competency.code.submit');
    
    // Code Test Feedback
    Route::get('/competency/code/feedback', [CompetencyTestController::class, 'showCodeFeedback'])->name('reviewer.competency.code.feedback');
    Route::post('/competency/code/continue', [CompetencyTestController::class, 'continueFromFeedback'])->name('reviewer.competency.code.continue');
    
    // Submit & Results
    Route::get('/competency/submit', [CompetencyTestController::class, 'submitTest'])->name('reviewer.competency.submit');
    Route::get('/competency/result/{id}', [CompetencyTestController::class, 'showResult'])->name('reviewer.competency.result');
});
