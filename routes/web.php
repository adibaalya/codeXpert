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
    
    // API endpoint to get starter code (LeetCode-style)
    Route::get('/coding/question/{questionId}/starter-code', [CodingQuestionController::class, 'getStarterCode'])->name('learner.coding.starter');
    
    // Code Execution Routes (for run and submit)
    Route::post('/coding/run', [\App\Http\Controllers\CodeExecutionController::class, 'runCode'])->name('learner.coding.run');
    Route::post('/coding/submit', [\App\Http\Controllers\CodeExecutionController::class, 'submitCode'])->name('learner.coding.submit');
    Route::post('/coding/rate', [\App\Http\Controllers\CodeExecutionController::class, 'rateQuestion'])->name('learner.coding.rate');
    Route::get('/coding/result', function() {
        $learner = auth()->guard('learner')->user();
        $submission = session('coding_submission');
        
        if (!$submission) {
            return redirect()->route('learner.practice')->with('error', 'No submission found.');
        }
        
        // Use the same result view as competency test, but adapt it for learners
        return view('learner.coding-result', compact('submission', 'learner'));
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
        
        // Assign ranks with proper tie handling
        $leaderboardData = [];
        $rank = 0; // Actual rank counter
        $previousXP = null;
        $position = 0; // Sequential position counter
        
        foreach ($allLearners as $topLearner) {
            $position++;
            
            // If XP is different from previous, increment rank by 1
            if ($previousXP === null || $topLearner->totalPoint != $previousXP) {
                $rank++; // Increment rank only when XP changes
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
            
            // Stop after collecting 15 entries (but include all ties at position 15)
            if (count($leaderboardData) >= 15) {
                $nextIndex = array_search($topLearner, $allLearners->toArray()) + 1;
                if ($nextIndex >= count($allLearners) || $allLearners[$nextIndex]->totalPoint != $previousXP) {
                    break;
                }
            }
        }
        
        // Calculate current user's rank with tie handling
        $currentUserRank = 0;
        $prevXP = null;
        foreach ($allLearners as $l) {
            if ($prevXP === null || $l->totalPoint != $prevXP) {
                $currentUserRank++;
            }
            if ($l->learner_ID === $learner->learner_ID) {
                break;
            }
            $prevXP = $l->totalPoint;
        }
        
        return view('learner.leaderboard', compact('learner', 'currentUserRank', 'currentUserXP', 'currentUserWeeklyXP', 'leaderboardData'));
    })->name('learner.leaderboard');
    
    // Hackathon
    Route::get('/hackathon', [LearnerDashboardController::class, 'hackathon'])->name('learner.hackathon');
    
    // Profile (placeholder routes - you'll need to implement these)
    Route::get('/profile', [LearnerDashboardController::class, 'profile'])->name('learner.profile');
    
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
    
    // Edit Profile
    Route::get('/profile/edit', function() {
        $reviewer = auth()->guard('reviewer')->user();
        return view('reviewer.edit-profile', compact('reviewer'));
    })->name('reviewer.profile.edit');
    
    Route::put('/profile/update', function(Illuminate\Http\Request $request) {
        $reviewer = auth()->guard('reviewer')->user();
        
        $request->validate([
            'username' => 'required|string|max:50|unique:reviewers,username,' . $reviewer->reviewer_ID . ',reviewer_ID',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        try {
            // Update username
            $reviewer->username = $request->username;
            
            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($reviewer->profile_photo && \Storage::disk('public')->exists($reviewer->profile_photo)) {
                    \Storage::disk('public')->delete($reviewer->profile_photo);
                }
                
                // Store new photo
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $reviewer->profile_photo = $path;
            }
            
            // Handle photo removal
            if ($request->remove_photo == '1' && $reviewer->profile_photo) {
                if (\Storage::disk('public')->exists($reviewer->profile_photo)) {
                    \Storage::disk('public')->delete($reviewer->profile_photo);
                }
                $reviewer->profile_photo = null;
            }
            
            $reviewer->save();
            
            return redirect()->route('reviewer.profile')->with('success', 'Profile updated successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update profile. Please try again.');
        }
    })->name('reviewer.profile.update');
    
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
