<?php

namespace App\Services;

use App\Models\Learner;
use App\Models\Question;
use App\Models\UserProficiency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AIPersonalizedChallengeService
{
    /**
     * Generate a personalized challenge for the learner based on their proficiency and past attempts
     * Challenge is cached for the day and only regenerates if completed or new day starts
     * 
     * @param Learner $learner
     * @return array|null
     */
    public function generatePersonalizedChallenge(Learner $learner)
    {
        $cacheKey = $this->getCacheKey($learner->learner_ID);
        
        // Check if we have a cached challenge for today
        $cachedChallenge = Cache::get($cacheKey);
        
        if ($cachedChallenge) {
            // Validate that the cached question still exists and is approved
            if (isset($cachedChallenge['question_id']) && $cachedChallenge['question_id']) {
                $questionExists = Question::where('question_ID', $cachedChallenge['question_id'])
                    ->where('status', 'Approved')
                    ->exists();
                
                if (!$questionExists) {
                    // Question no longer exists or not approved, clear cache and generate new
                    Cache::forget($cacheKey);
                    $cachedChallenge = null;
                }
            }
            
            // If still valid, check if completed
            if ($cachedChallenge) {
                $isCompleted = $this->isChallengeCompleted($learner->learner_ID, $cachedChallenge['question_id']);
                
                if (!$isCompleted) {
                    // Return the cached challenge if not completed
                    return $cachedChallenge;
                }
                
                // If completed, clear the cache and generate a new one
                Cache::forget($cacheKey);
            }
        }
        
        // Generate a new challenge
        $challenge = $this->generateNewChallenge($learner);
        
        // Cache the challenge until end of day
        $expiresAt = Carbon::tomorrow()->startOfDay();
        Cache::put($cacheKey, $challenge, $expiresAt);
        
        return $challenge;
    }
    
    /**
     * Get cache key for learner's daily challenge
     * 
     * @param int $learnerId
     * @return string
     */
    private function getCacheKey(int $learnerId): string
    {
        $today = Carbon::today()->toDateString();
        return "daily_challenge_{$learnerId}_{$today}";
    }
    
    /**
     * Check if the challenge question has been completed today
     * 
     * @param int $learnerId
     * @param int|null $questionId
     * @return bool
     */
    private function isChallengeCompleted(int $learnerId, ?int $questionId): bool
    {
        if (!$questionId) {
            return false;
        }
        
        // Check if user has attempted this question today with good accuracy (70%+)
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        
        $completedAttempt = DB::table('attempts')
            ->where('learner_ID', $learnerId)
            ->where('question_ID', $questionId)
            ->whereBetween('dateAttempted', [$todayStart, $todayEnd])
            ->where('accuracyScore', '>=', 70)
            ->exists();
        
        return $completedAttempt;
    }
    
    /**
     * Generate a new challenge for the learner
     * 
     * @param Learner $learner
     * @return array
     */
    private function generateNewChallenge(Learner $learner): array
    {
        // Get learner's proficiencies
        $proficiencies = $learner->userProficiencies;
        
        if ($proficiencies->isEmpty()) {
            return $this->getDefaultChallenge();
        }
        
        // Analyze past attempts to identify weak areas
        $weakAreas = $this->analyzeWeakAreas($learner);
        
        // Get proficiency levels for each language
        $proficiencyLevels = $this->getProficiencyLevels($proficiencies);
        
        // Select the best question based on the analysis
        $question = $this->selectOptimalQuestion($learner, $proficiencyLevels, $weakAreas);
        
        if (!$question) {
            return $this->getDefaultChallenge();
        }
        
        return $this->formatChallengeResponse($question, $weakAreas, $proficiencyLevels);
    }
    
    /**
     * Analyze learner's past attempts to identify weak areas
     * 
     * @param Learner $learner
     * @return array
     */
    private function analyzeWeakAreas(Learner $learner)
    {
        // Get recent attempts (last 30 days)
        $recentAttempts = DB::table('attempts')
            ->join('questions', 'attempts.question_ID', '=', 'questions.question_ID')
            ->where('attempts.learner_ID', $learner->learner_ID)
            ->where('attempts.dateAttempted', '>=', Carbon::now()->subDays(30))
            ->select(
                'questions.language',
                'questions.level',
                'questions.chapter',
                'attempts.accuracyScore',
                'questions.question_ID'
            )
            ->get();
        
        $weakAreas = [
            'languages' => [],
            'difficulties' => [],
            'topics' => [],
            'totalAttempts' => $recentAttempts->count(),
            'averageAccuracy' => 0,
            'strugglingTopics' => []
        ];
        
        if ($recentAttempts->isEmpty()) {
            return $weakAreas;
        }
        
        // Calculate average accuracy
        $weakAreas['averageAccuracy'] = $recentAttempts->avg('accuracyScore');
        
        // Analyze performance by language
        $languagePerformance = $recentAttempts->groupBy('language')->map(function ($attempts) {
            return [
                'count' => $attempts->count(),
                'avgAccuracy' => $attempts->avg('accuracyScore'),
                'weakPerformance' => $attempts->avg('accuracyScore') < 70
            ];
        });
        
        $weakAreas['languages'] = $languagePerformance->filter(function ($perf) {
            return $perf['weakPerformance'];
        })->keys()->toArray();
        
        // Analyze performance by difficulty
        $difficultyPerformance = $recentAttempts->groupBy('level')->map(function ($attempts) {
            return [
                'count' => $attempts->count(),
                'avgAccuracy' => $attempts->avg('accuracyScore'),
                'weakPerformance' => $attempts->avg('accuracyScore') < 70
            ];
        });
        
        $weakAreas['difficulties'] = $difficultyPerformance->filter(function ($perf) {
            return $perf['weakPerformance'];
        })->keys()->toArray();
        
        // Analyze performance by topic/chapter
        $topicPerformance = $recentAttempts->groupBy('chapter')->map(function ($attempts) {
            return [
                'count' => $attempts->count(),
                'avgAccuracy' => $attempts->avg('accuracyScore'),
                'weakPerformance' => $attempts->avg('accuracyScore') < 70
            ];
        });
        
        $weakAreas['strugglingTopics'] = $topicPerformance->filter(function ($perf) {
            return $perf['weakPerformance'];
        })->keys()->toArray();
        
        return $weakAreas;
    }
    
    /**
     * Get proficiency levels for each language
     * 
     * @param \Illuminate\Database\Eloquent\Collection $proficiencies
     * @return array
     */
    private function getProficiencyLevels($proficiencies)
    {
        return $proficiencies->mapWithKeys(function ($proficiency) {
            $xp = $proficiency->XP ?? 0;
            
            // Determine proficiency level based on XP
            if ($xp < 100) {
                $level = 'Beginner';
            } elseif ($xp < 500) {
                $level = 'Intermediate';
            } else {
                $level = 'Advanced';
            }
            
            return [$proficiency->language => [
                'xp' => $xp,
                'level' => $level,
                'score' => min(($xp / 1000) * 100, 100) // Normalized score 0-100
            ]];
        })->toArray();
    }
    
    /**
     * Select the optimal question based on learner's profile
     * 
     * @param Learner $learner
     * @param array $proficiencyLevels
     * @param array $weakAreas
     * @return Question|null
     */
    private function selectOptimalQuestion(Learner $learner, array $proficiencyLevels, array $weakAreas)
    {
        // Get questions attempted in the last 7 days to avoid immediate repetition
        // But allow re-attempting older questions for practice
        $recentlyAttemptedIds = DB::table('attempts')
            ->where('learner_ID', $learner->learner_ID)
            ->where('dateAttempted', '>=', Carbon::now()->subDays(7))
            ->pluck('question_ID')
            ->toArray();
        
        // Build query for recommended questions
        $query = Question::where('status', 'Approved')
            ->where('questionCategory', 'learnerPractice');
        
        // Try to find questions not attempted recently
        $queryWithoutRecent = $query->clone()->whereNotIn('question_ID', $recentlyAttemptedIds);
        
        // Priority 1: Focus on weak languages if identified (not attempted recently)
        if (!empty($weakAreas['languages'])) {
            $question = $queryWithoutRecent->clone()
                ->whereIn('language', $weakAreas['languages'])
                ->when(!empty($weakAreas['strugglingTopics']), function($q) use ($weakAreas) {
                    return $q->whereIn('chapter', $weakAreas['strugglingTopics']);
                })
                ->inRandomOrder()
                ->first();
            
            if ($question) return $question;
        }
        
        // Priority 2: Focus on struggling topics in known languages (not attempted recently)
        if (!empty($weakAreas['strugglingTopics']) && !empty($proficiencyLevels)) {
            $question = $queryWithoutRecent->clone()
                ->whereIn('language', array_keys($proficiencyLevels))
                ->whereIn('chapter', $weakAreas['strugglingTopics'])
                ->inRandomOrder()
                ->first();
            
            if ($question) return $question;
        }
        
        // Priority 3: Match proficiency level and slightly challenge (not attempted recently)
        if (!empty($proficiencyLevels)) {
            foreach ($proficiencyLevels as $language => $profData) {
                $targetDifficulty = $this->getTargetDifficulty($profData['level']);
                
                $question = $queryWithoutRecent->clone()
                    ->where('language', $language)
                    ->where('level', $targetDifficulty)
                    ->inRandomOrder()
                    ->first();
                
                if ($question) return $question;
            }
        }
        
        // Priority 4: Any question in learner's proficient languages (not attempted recently)
        if (!empty($proficiencyLevels)) {
            $question = $queryWithoutRecent->clone()
                ->whereIn('language', array_keys($proficiencyLevels))
                ->inRandomOrder()
                ->first();
            
            if ($question) return $question;
        }
        
        // Priority 5: Fallback to any available question (not attempted recently)
        $question = $queryWithoutRecent->inRandomOrder()->first();
        if ($question) return $question;
        
        // Priority 6: If all questions were attempted recently, allow re-attempting
        // This ensures learners always have challenges available
        if (!empty($proficiencyLevels)) {
            return $query->clone()
                ->whereIn('language', array_keys($proficiencyLevels))
                ->inRandomOrder()
                ->first();
        }
        
        // Final fallback: Any available question
        return $query->inRandomOrder()->first();
    }
    
    /**
     * Get target difficulty level based on proficiency
     * 
     * @param string $proficiencyLevel
     * @return string
     */
    private function getTargetDifficulty(string $proficiencyLevel)
    {
        return match($proficiencyLevel) {
            'Beginner' => 'beginner',
            'Intermediate' => 'intermediate',
            'Advanced' => 'advanced',
            default => 'intermediate'
        };
    }
    
    /**
     * Format the challenge response with AI-like suggestions
     * 
     * @param Question $question
     * @param array $weakAreas
     * @param array $proficiencyLevels
     * @return array
     */
    private function formatChallengeResponse(Question $question, array $weakAreas, array $proficiencyLevels)
    {
        $language = $question->language;
        $difficulty = ucfirst($question->level ?? 'intermediate');
        $topic = $question->chapter ?? 'Programming Challenge';
        
        // Generate personalized description
        $description = $this->generatePersonalizedDescription($question, $weakAreas, $proficiencyLevels);
        
        // Estimate time based on difficulty
        $estimatedTime = match(strtolower($question->level ?? 'intermediate')) {
            'beginner', 'easy' => '~15 minutes',
            'intermediate', 'medium' => '~30 minutes',
            'advanced', 'hard' => '~45 minutes',
            default => '~30 minutes'
        };
        
        return [
            'question_id' => $question->question_ID,
            'title' => $question->title ?? 'Coding Challenge',
            'description' => $description,
            'language' => $language,
            'difficulty' => $difficulty,
            'topic' => $topic,
            'estimated_time' => $estimatedTime,
            'hint' => $question->hint,
            'tags' => [$language, $difficulty, $topic]
        ];
    }
    
    /**
     * Generate personalized description based on learner's profile
     * 
     * @param Question $question
     * @param array $weakAreas
     * @param array $proficiencyLevels
     * @return string
     */
    private function generatePersonalizedDescription(Question $question, array $weakAreas, array $proficiencyLevels)
    {
        $language = $question->language;
        $topic = $question->chapter ?? 'programming concepts';
        
        // Check if this addresses a weak area
        $isWeakLanguage = in_array($language, $weakAreas['languages'] ?? []);
        $isWeakTopic = in_array($topic, $weakAreas['strugglingTopics'] ?? []);
        
        if ($isWeakLanguage && $isWeakTopic) {
            return "Based on your recent attempts, we've noticed you could use more practice with {$topic} in {$language}. This challenge is designed to help you strengthen these skills and build confidence!";
        }
        
        if ($isWeakLanguage) {
            return "You've been making good progress, but let's reinforce your {$language} skills with this {$topic} challenge. Perfect for filling in knowledge gaps!";
        }
        
        if ($isWeakTopic) {
            return "We've identified {$topic} as an area for improvement. This {$language} challenge will help you master these concepts and advance to the next level!";
        }
        
        // Check proficiency level
        $proficiency = $proficiencyLevels[$language] ?? null;
        if ($proficiency) {
            $level = $proficiency['level'];
            return "Based on your {$level} level in {$language}, this {$topic} challenge is perfectly tailored to your current skills. Great for continuous improvement!";
        }
        
        // Default personalized message
        return "Based on your learning path and recent progress, we recommend this {$topic} challenge in {$language}. Perfect for strengthening your foundation and advancing your skills!";
    }
    
    /**
     * Get default challenge when no proficiencies exist
     * 
     * @return array
     */
    private function getDefaultChallenge()
    {
        $question = Question::where('status', 'Approved')
            ->where('questionCategory', 'learnerPractice')
            ->where('level', 'beginner')
            ->inRandomOrder()
            ->first();
        
        if (!$question) {
            return [
                'question_id' => null,
                'title' => 'Start Your Coding Journey',
                'description' => 'Welcome to CodeXpert! Set up your learning path by customizing your language preferences to get personalized challenge recommendations.',
                'language' => 'Python',
                'difficulty' => 'Beginner',
                'topic' => 'Getting Started',
                'estimated_time' => '~15 minutes',
                'hint' => 'Visit the customization page to select your preferred programming languages.',
                'tags' => ['Python', 'Beginner', 'Getting Started']
            ];
        }
        
        return [
            'question_id' => $question->question_ID,
            'title' => $question->title ?? 'Your First Challenge',
            'description' => 'Start your coding journey with this beginner-friendly challenge. Perfect for getting familiar with the platform and building your confidence!',
            'language' => $question->language,
            'difficulty' => 'Beginner',
            'topic' => $question->chapter ?? 'Introduction',
            'estimated_time' => '~15 minutes',
            'hint' => $question->hint,
            'tags' => [$question->language, 'Beginner', $question->chapter ?? 'Introduction']
        ];
    }
    
    /**
     * Manually clear the daily challenge cache for a learner
     * Useful when you want to force regenerate a challenge
     * 
     * @param int $learnerId
     * @return bool
     */
    public function clearDailyChallenge(int $learnerId): bool
    {
        $cacheKey = $this->getCacheKey($learnerId);
        return Cache::forget($cacheKey);
    }
    
    /**
     * Get the current cached challenge without regenerating
     * 
     * @param int $learnerId
     * @return array|null
     */
    public function getCurrentChallenge(int $learnerId): ?array
    {
        $cacheKey = $this->getCacheKey($learnerId);
        return Cache::get($cacheKey);
    }
}
