<?php

namespace App\Services;

use App\Models\Learner;
use App\Models\Question;
use App\Models\UserProficiency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
     * Select the optimal question based on learner's profile using AI-powered ranking
     * 
     * @param Learner $learner
     * @param array $proficiencyLevels
     * @param array $weakAreas
     * @return Question|null
     */
    private function selectOptimalQuestion(Learner $learner, array $proficiencyLevels, array $weakAreas)
    {
        $geminiApiKey = env('GEMINI_API_KEY');
        
        // Try AI-powered selection if API key is available
        if ($geminiApiKey) {
            try {
                $aiSelectedQuestion = $this->selectOptimalQuestionWithAI($learner, $proficiencyLevels, $weakAreas);
                
                if ($aiSelectedQuestion) {
                    Log::info('AI-powered question selection successful', [
                        'learner_id' => $learner->learner_ID,
                        'selected_question_id' => $aiSelectedQuestion->question_ID,
                        'question_title' => $aiSelectedQuestion->title
                    ]);
                    return $aiSelectedQuestion;
                }
            } catch (\Exception $e) {
                Log::warning('AI question selection failed, falling back to rule-based', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Fallback to rule-based selection
        return $this->selectOptimalQuestionRuleBased($learner, $proficiencyLevels, $weakAreas);
    }
    
    /**
     * AI-POWERED: Select optimal question using Gemini to rank candidates
     * 
     * @param Learner $learner
     * @param array $proficiencyLevels
     * @param array $weakAreas
     * @return Question|null
     */
    private function selectOptimalQuestionWithAI(Learner $learner, array $proficiencyLevels, array $weakAreas): ?Question
    {
        // STEP 1: Get candidate questions (rule-based pre-filtering for efficiency)
        $candidates = $this->getCandidateQuestions($learner, $proficiencyLevels, $weakAreas);
        
        if ($candidates->isEmpty()) {
            return null;
        }
        
        // If only one candidate, return it directly (no need for AI ranking)
        if ($candidates->count() === 1) {
            return $candidates->first();
        }
        
        // STEP 2: Ask AI to rank the candidates
        $rankedQuestionId = $this->rankQuestionsWithAI($candidates, $learner, $weakAreas, $proficiencyLevels);
        
        if (!$rankedQuestionId) {
            // If AI ranking fails, return first candidate
            return $candidates->first();
        }
        
        // STEP 3: Return the top-ranked question
        return Question::find($rankedQuestionId);
    }
    
    /**
     * Get candidate questions for AI ranking (rule-based pre-filtering)
     * 
     * @param Learner $learner
     * @param array $proficiencyLevels
     * @param array $weakAreas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getCandidateQuestions(Learner $learner, array $proficiencyLevels, array $weakAreas)
    {
        // Get recently attempted questions (last 7 days)
        $recentlyAttemptedIds = DB::table('attempts')
            ->where('learner_ID', $learner->learner_ID)
            ->where('dateAttempted', '>=', Carbon::now()->subDays(7))
            ->pluck('question_ID')
            ->toArray();
        
        $query = Question::where('status', 'Approved')
            ->where('questionCategory', 'learnerPractice')
            ->whereNotIn('question_ID', $recentlyAttemptedIds);
        
        // Priority 1: Questions addressing weak areas (if any)
        if (!empty($weakAreas['languages']) || !empty($weakAreas['strugglingTopics'])) {
            $weakCandidates = $query->clone()
                ->when(!empty($weakAreas['languages']), function($q) use ($weakAreas) {
                    return $q->whereIn('language', $weakAreas['languages']);
                })
                ->when(!empty($weakAreas['strugglingTopics']), function($q) use ($weakAreas) {
                    return $q->orWhereIn('chapter', $weakAreas['strugglingTopics']);
                })
                ->limit(10) // Get top 10 candidates
                ->get();
            
            if ($weakCandidates->isNotEmpty()) {
                return $weakCandidates;
            }
        }
        
        // Priority 2: Questions in learner's proficient languages
        if (!empty($proficiencyLevels)) {
            $proficientCandidates = $query->clone()
                ->whereIn('language', array_keys($proficiencyLevels))
                ->limit(10)
                ->get();
            
            if ($proficientCandidates->isNotEmpty()) {
                return $proficientCandidates;
            }
        }
        
        // Priority 3: Any available questions
        return $query->limit(10)->get();
    }
    
    /**
     * Use Gemini AI to rank candidate questions and return the best one
     * 
     * @param \Illuminate\Database\Eloquent\Collection $candidates
     * @param Learner $learner
     * @param array $weakAreas
     * @param array $proficiencyLevels
     * @return int|null Question ID of the best ranked question
     */
    private function rankQuestionsWithAI($candidates, Learner $learner, array $weakAreas, array $proficiencyLevels): ?int
    {
        $geminiApiKey = env('GEMINI_API_KEY');
        
        // Build learner profile summary
        $weakLanguagesText = !empty($weakAreas['languages']) 
            ? implode(', ', $weakAreas['languages']) 
            : 'None';
        
        $strugglingTopicsText = !empty($weakAreas['strugglingTopics']) 
            ? implode(', ', $weakAreas['strugglingTopics']) 
            : 'None';
        
        $proficiencyText = '';
        foreach ($proficiencyLevels as $lang => $profData) {
            $proficiencyText .= "- {$lang}: {$profData['level']} ({$profData['xp']} XP)\n";
        }
        
        if (empty($proficiencyText)) {
            $proficiencyText = "- No proficiency data (new learner)";
        }
        
        // Format candidate questions
        $candidatesText = '';
        foreach ($candidates as $index => $question) {
            $candidatesText .= ($index + 1) . ". ID: {$question->question_ID}\n";
            $candidatesText .= "   Title: {$question->title}\n";
            $candidatesText .= "   Language: {$question->language}\n";
            $candidatesText .= "   Topic: {$question->chapter}\n";
            $candidatesText .= "   Difficulty: {$question->level}\n\n";
        }
        
        // Build AI prompt for ranking
        $prompt = <<<PROMPT
You are an expert learning coach for CodeXpert. Your task is to select the BEST coding question for this learner from the candidates below.

**Learner Profile:**
- Weak Languages: {$weakLanguagesText}
- Struggling Topics: {$strugglingTopicsText}
- Current Proficiency:
{$proficiencyText}
- Average Accuracy (last 30 days): {$weakAreas['averageAccuracy']}%
- Total Attempts (last 30 days): {$weakAreas['totalAttempts']}

**Selection Criteria (Priority Order):**
1. **Maximum Learning Impact** - Choose questions that address their weakest areas
2. **Appropriate Difficulty** - Not too easy (boring) or too hard (frustrating)
3. **Skill Progression** - Help them level up in their weakest skills
4. **Engagement** - Keep them motivated and challenged

**Available Questions:**
{$candidatesText}

**Instructions:**
1. Analyze each question against the learner profile
2. Consider their weak areas and proficiency levels
3. Select the ONE question with the highest learning impact
4. Respond with ONLY the question ID number (e.g., "42")

Your response (question ID only):
PROMPT;
        
        // Call Gemini API
        $response = Http::timeout(20)
            ->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}", [
                'contents' => [[
                    'parts' => [['text' => $prompt]]
                ]],
                'generationConfig' => [
                    'temperature' => 0.3,  // Lower temperature for more deterministic selection
                    'maxOutputTokens' => 50,  // Only need the question ID
                    'topP' => 0.8,
                    'topK' => 20
                ]
            ]);
        
        if ($response->successful()) {
            $responseData = $response->json();
            $aiText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;
            
            if ($aiText) {
                // Extract question ID from response
                $aiText = trim($aiText);
                
                // Try to extract number from response
                if (preg_match('/\b(\d+)\b/', $aiText, $matches)) {
                    $selectedQuestionId = (int) $matches[1];
                    
                    // Verify the selected question exists in candidates
                    $selectedQuestion = $candidates->firstWhere('question_ID', $selectedQuestionId);
                    
                    if ($selectedQuestion) {
                        Log::info('AI successfully ranked and selected question', [
                            'selected_question_id' => $selectedQuestionId,
                            'ai_response' => $aiText,
                            'total_candidates' => $candidates->count()
                        ]);
                        return $selectedQuestionId;
                    } else {
                        Log::warning('AI selected invalid question ID', [
                            'selected_id' => $selectedQuestionId,
                            'ai_response' => $aiText
                        ]);
                    }
                }
            }
        }
        
        Log::warning('AI ranking failed to return valid question ID', [
            'response_status' => $response->status(),
            'candidates_count' => $candidates->count()
        ]);
        
        return null;
    }
    
    /**
     * RULE-BASED: Fallback selection when AI is unavailable
     * 
     * @param Learner $learner
     * @param array $proficiencyLevels
     * @param array $weakAreas
     * @return Question|null
     */
    private function selectOptimalQuestionRuleBased(Learner $learner, array $proficiencyLevels, array $weakAreas): ?Question
    {
        // Get questions attempted in the last 7 days to avoid immediate repetition
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
     * Generate personalized description based on learner's profile using Gemini AI
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
        
        // ===== AI-POWERED DESCRIPTION GENERATION =====
        $geminiApiKey = env('GEMINI_API_KEY');
        
        if ($geminiApiKey) {
            try {
                $aiDescription = $this->generateAIDescription(
                    $question, 
                    $weakAreas, 
                    $proficiencyLevels, 
                    $isWeakLanguage, 
                    $isWeakTopic
                );
                
                if ($aiDescription) {
                    Log::info('AI-generated personalized description', [
                        'question_id' => $question->question_ID,
                        'description_length' => strlen($aiDescription)
                    ]);
                    return $aiDescription;
                }
            } catch (\Exception $e) {
                Log::error('AI description generation failed, using fallback', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // ===== FALLBACK: RULE-BASED DESCRIPTION =====
        return $this->getRuleBasedDescription($question, $weakAreas, $proficiencyLevels, $isWeakLanguage, $isWeakTopic);
    }
    
    /**
     * Generate AI-powered personalized description using Gemini
     * 
     * @param Question $question
     * @param array $weakAreas
     * @param array $proficiencyLevels
     * @param bool $isWeakLanguage
     * @param bool $isWeakTopic
     * @return string|null
     */
    private function generateAIDescription(Question $question, array $weakAreas, array $proficiencyLevels, bool $isWeakLanguage, bool $isWeakTopic): ?string
    {
        $geminiApiKey = env('GEMINI_API_KEY');
        $language = $question->language;
        $topic = $question->chapter ?? 'programming concepts';
        $difficulty = $question->level ?? 'intermediate';
        
        // Build learner profile context
        $weakLanguagesText = !empty($weakAreas['languages']) 
            ? implode(', ', $weakAreas['languages']) 
            : 'None';
        
        $strugglingTopicsText = !empty($weakAreas['strugglingTopics']) 
            ? implode(', ', $weakAreas['strugglingTopics']) 
            : 'None';
        
        $proficiencyText = '';
        foreach ($proficiencyLevels as $lang => $profData) {
            $proficiencyText .= "- {$lang}: {$profData['level']} ({$profData['xp']} XP)\n";
        }
        
        if (empty($proficiencyText)) {
            $proficiencyText = "- No proficiency data yet (new learner)";
        }
        
        // Determine recommendation reason
        $recommendationReason = '';
        if ($isWeakLanguage && $isWeakTopic) {
            $recommendationReason = "This question addresses BOTH a weak language ({$language}) AND a struggling topic ({$topic}) for maximum learning impact.";
        } elseif ($isWeakLanguage) {
            $recommendationReason = "This question helps reinforce your weaker language ({$language}).";
        } elseif ($isWeakTopic) {
            $recommendationReason = "This question focuses on your struggling topic ({$topic}).";
        } else {
            $recommendationReason = "This question matches your current skill level and provides continuous practice.";
        }
        
        // Build AI prompt
        $prompt = <<<PROMPT
You are a personalized learning coach for CodeXpert, an online coding platform. Generate a motivational and specific 2-3 sentence message recommending a coding challenge to a learner.

**Learner Profile:**
- Weak Languages: {$weakLanguagesText}
- Struggling Topics: {$strugglingTopicsText}
- Current Proficiency Levels:
{$proficiencyText}
- Average Accuracy (last 30 days): {$weakAreas['averageAccuracy']}%

**Recommended Challenge:**
- Title: {$question->title}
- Language: {$language}
- Topic/Chapter: {$topic}
- Difficulty: {$difficulty}

**Why This Challenge:**
{$recommendationReason}

**Instructions:**
1. Write in a warm, encouraging, mentor-like tone
2. Be SPECIFIC - reference their actual weak areas or proficiency level
3. Explain WHY this challenge is perfect for them RIGHT NOW
4. Keep it concise (2-3 sentences maximum)
5. Make them feel motivated and excited to try it
6. Do NOT use generic phrases like "Great for learning!" - be personal and specific

Generate ONLY the recommendation message (no greetings, no signatures, no extra formatting).
PROMPT;
        
        // Call Gemini API
        $response = Http::timeout(15)
            ->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}", [
                'contents' => [[
                    'parts' => [['text' => $prompt]]
                ]],
                'generationConfig' => [
                    'temperature' => 0.9,  // Higher creativity for personalized messages
                    'maxOutputTokens' => 300,  // Short, concise messages
                    'topP' => 0.95,
                    'topK' => 40
                ]
            ]);
        
        if ($response->successful()) {
            $responseData = $response->json();
            $aiText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;
            
            if ($aiText) {
                // Clean up the response
                $aiText = trim($aiText);
                // Remove any quotes if AI added them
                $aiText = trim($aiText, '"\'');
                
                return $aiText;
            }
        }
        
        Log::warning('Gemini API call failed for recommendation description', [
            'status' => $response->status(),
            'question_id' => $question->question_ID
        ]);
        
        return null;
    }
    
    /**
     * Get rule-based description (fallback when AI is unavailable)
     * 
     * @param Question $question
     * @param array $weakAreas
     * @param array $proficiencyLevels
     * @param bool $isWeakLanguage
     * @param bool $isWeakTopic
     * @return string
     */
    private function getRuleBasedDescription(Question $question, array $weakAreas, array $proficiencyLevels, bool $isWeakLanguage, bool $isWeakTopic): string
    {
        $language = $question->language;
        $topic = $question->chapter ?? 'programming concepts';
        
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
