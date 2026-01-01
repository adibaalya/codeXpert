<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Learner;
use App\Models\Attempt;
use App\Services\AchievementService;
use Illuminate\Support\Facades\DB;

class BackfillLearnerAchievements extends Command
{
    protected $signature = 'achievements:backfill-learners';
    protected $description = 'Retroactively count attempts and award badges for existing learners';

    public function handle()
    {
        $this->info('Starting backfill of learner achievements...');
        
        $learners = Learner::all();
        $achievementService = app(AchievementService::class);
        
        foreach ($learners as $learner) {
            $this->info("Processing learner: {$learner->username}");
            
            // Get all attempts for this learner
            $attempts = DB::table('attempts')
                ->where('learner_ID', $learner->learner_ID)
                ->orderBy('dateAttempted', 'asc')
                ->get();
            
            if ($attempts->isEmpty()) {
                $this->warn("  No attempts found, skipping...");
                continue;
            }
            
            // Count total attempts
            $totalAttempts = $attempts->count();
            
            // Count solved questions (accuracyScore = 100 or 10+ XP)
            $solvedQuestions = $attempts->where('accuracyScore', '>=', 10)->unique('question_ID')->count();
            
            // Calculate streak
            $currentStreak = $this->calculateStreak($attempts, $learner->learner_ID);
            
            // Update learner stats
            $learner->total_attempts = $totalAttempts;
            $learner->solved_questions = $solvedQuestions;
            $learner->current_streak = $currentStreak;
            
            // Set last activity date to the latest attempt
            $lastAttempt = $attempts->sortByDesc('dateAttempted')->first();
            $learner->last_activity_date = $lastAttempt->dateAttempted;
            
            $learner->save();
            
            $this->info("  ✓ Total attempts: {$totalAttempts}");
            $this->info("  ✓ Solved questions: {$solvedQuestions}");
            $this->info("  ✓ Current streak: {$currentStreak} days");
            
            // Check and award badges for each language the learner has practiced
            $languages = $attempts->pluck('language')->unique();
            
            foreach ($languages as $language) {
                $achievementService->checkLearnerBadges($learner, $language);
            }
            
            $badgeCount = $learner->badges()->count();
            $this->info("  🎖️  Badges earned: {$badgeCount}");
            $this->line('');
        }
        
        $this->info('✅ Backfill completed successfully!');
        return 0;
    }
    
    private function calculateStreak($attempts, $learnerId)
    {
        if ($attempts->isEmpty()) {
            return 0;
        }
        
        // Get unique dates of attempts
        $attemptDates = $attempts->pluck('dateAttempted')
            ->map(function($date) {
                return \Carbon\Carbon::parse($date)->startOfDay();
            })
            ->unique()
            ->sortDesc()
            ->values();
        
        if ($attemptDates->isEmpty()) {
            return 0;
        }
        
        $today = \Carbon\Carbon::now()->startOfDay();
        $yesterday = \Carbon\Carbon::now()->subDay()->startOfDay();
        
        // Check if the most recent attempt was today or yesterday
        $lastAttemptDate = $attemptDates->first();
        
        if (!$lastAttemptDate->isSameDay($today) && !$lastAttemptDate->isSameDay($yesterday)) {
            // Streak is broken if last attempt was not today or yesterday
            return 0;
        }
        
        // Count consecutive days
        $streak = 0;
        $expectedDate = $today;
        
        foreach ($attemptDates as $attemptDate) {
            if ($attemptDate->isSameDay($expectedDate) || $attemptDate->isSameDay($expectedDate->copy()->subDay())) {
                $streak++;
                $expectedDate = $attemptDate->copy()->subDay();
            } else {
                break;
            }
        }
        
        return $streak;
    }
}
