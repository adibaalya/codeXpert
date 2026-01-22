<?php

namespace App\Services;

use App\Models\Attempt;

class AchievementService
{
    // Helper to give badge only if they don't have it
    private function award($user, $badgeName)
    {
        if (!$user->badges()->where('badge_type', $badgeName)->exists()) {
            $user->badges()->create(['badge_type' => $badgeName, 'earned_date' => now()]);
        }
    }

    // --- REVIEWER LOGIC ---
    public function checkReviewerBadges($reviewer)
    {
        if ($reviewer->isQualified) {
            $this->award($reviewer, 'certified_reviewer');
        }

        if ($reviewer->total_reviews >= 1) {
            $this->award($reviewer, 'first_review');
        }

        // 3. Active Reviewer (10 reviews)
        if ($reviewer->total_reviews >= 10) {
            $this->award($reviewer, 'active_reviewer');
        }

        // 4. Quality Checker (25 reviews & No Errors Found)
        if ($reviewer->total_reviews >= 25 && $reviewer->clean_reviews_count >= 25) {
            $this->award($reviewer, 'quality_checker');
        }

        // 5. Error Spotter (5 Errors Flagged)
        if ($reviewer->errors_flagged_count >= 5) {
            $this->award($reviewer, 'error_spotter');
        }

        // 6. Question Creator (5 Generated)
        if ($reviewer->questions_generated_count >= 5) {
            $this->award($reviewer, 'question_creator');
        }

        // 7. Creative Author (20 Generated)
        if ($reviewer->questions_generated_count >= 20) {
            $this->award($reviewer, 'creative_author');
        }

        if ($reviewer->total_reviews >= 10) {
            $rate = $reviewer->clean_reviews_count / $reviewer->total_reviews;
            if ($rate >= 0.90) {
                $this->award($reviewer, 'trusted_reviewer');
            }
        }
    }

    // --- LEARNER LOGIC ---
    public function checkLearnerBadges($learner, $currentLanguage)
    {
        // 1. First Problem Solved
        if ($learner->solved_questions >= 1) {
            $this->award($learner, 'first_problem_solved');
        }

        // 2. Beginner Solver (10 Solved)
        if ($learner->solved_questions >= 10) {
            $this->award($learner, 'beginner_solver');
        }

        // 3. Active Learner (25 Solved)
        if ($learner->solved_questions >= 25) {
            $this->award($learner, 'active_learner');
        }

        // 4. Problem Solver (50 Solved)
        if ($learner->solved_questions >= 50) {
            $this->award($learner, 'problem_solver');
        }

        // 5. Consistent Learner (7 Day Streak)
        if ($learner->current_streak >= 7) {
            $this->award($learner, 'consistent_learner');
        }

        // 6. Accuracy Improver (80% correct across 20 questions)
        if ($learner->total_attempts >= 20) {
            $rate = $learner->solved_questions / $learner->total_attempts;
            if ($rate >= 0.80) {
                $this->award($learner, 'accuracy_improver');
            }
        }

        // 7. Language Confident (30 solved in SAME language)
        $langCount = \DB::table('attempts')
                        ->where('learner_ID', $learner->learner_ID)
                        ->where('language', $currentLanguage)
                        ->where('accuracyScore', 100)
                        ->distinct('question_ID')
                        ->count('question_ID');
        if ($langCount >= 30) {
            $this->award($learner, 'language_confident');
        }
    }
}
