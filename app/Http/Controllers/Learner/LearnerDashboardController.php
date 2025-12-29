<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Learner;
use App\Models\Question;
use App\Models\UserProficiency;
use App\Models\Hackathon;
use App\Services\AIPersonalizedChallengeService;
use Carbon\Carbon;

class LearnerDashboardController extends Controller
{
    protected $aiChallengeService;
    
    public function __construct(AIPersonalizedChallengeService $aiChallengeService)
    {
        $this->aiChallengeService = $aiChallengeService;
    }
    
    public function index()
    {
        $learner = Auth::guard('learner')->user();
        
        // Use Quadratic Curve leveling system (SAME AS LEARNER MODEL)
        $levelProgress = $learner->getLevelProgress();
        $currentLevel = $levelProgress['current_level'];
        $xpPoints = $levelProgress['current_xp'];
        $nextLevelXP = $levelProgress['xp_for_next_level'];
        $xpProgress = $levelProgress['percentage'];
        
        // Calculate actual streak based on consecutive days of attempts
        $currentStreak = $learner->getCurrentStreak();
        $achievements = $this->getAchievementsCount($learner);
        
        // Get weekly practice data
        $weeklyData = $this->getWeeklyPracticeData($learner);
        
        // Get AI-powered personalized challenge
        $todaysChallenge = $this->aiChallengeService->generatePersonalizedChallenge($learner);
        
        // Get recommended questions (limit 3 for display)
        $recommendedQuestions = $this->getRecommendedQuestions($learner, 3);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($learner, 4);
        
        // Get proficiency data with XP-based progress (NOT question count)
        $proficiencies = $learner->userProficiencies->map(function($prof) use ($learner) {
            // XP thresholds for language proficiency levels
            // Beginner: 0-29 XP, Intermediate: 30-69 XP, Advanced: 70+ XP
            $maxXP = 100; // Max XP to consider for progress bar (100%)
            
            // Current XP for this language
            $currentXP = $prof->XP;
            
            // Calculate percentage based on XP (0-100%)
            $percentage = min(round(($currentXP / $maxXP) * 100), 100);
            
            // Determine level based on XP
            if ($currentXP < 30) {
                $level = 'Beginner';
            } elseif ($currentXP < 70) {
                $level = 'Intermediate';
            } else {
                $level = 'Advanced';
            }
            
            // Count actual questions solved for display (optional info)
            $solvedProblems = DB::table('attempts')
                ->join('questions', 'attempts.question_ID', '=', 'questions.question_ID')
                ->where('attempts.learner_ID', $learner->learner_ID)
                ->where('questions.language', $prof->language)
                ->where('attempts.accuracyScore', '>', 0) // Any XP earned
                ->distinct('attempts.question_ID')
                ->count('attempts.question_ID');
            
            // Count total available questions for this language
            $totalProblems = DB::table('questions')
                ->where('language', $prof->language)
                ->where('status', 'Approved')
                ->where('questionCategory', 'learnerPractice')
                ->count();
            
            // If no questions available, show fallback
            if ($totalProblems == 0) {
                $totalProblems = 150;
            }
            
            return (object)[
                'language' => $prof->language,
                'level' => $level,
                'XP' => $currentXP,
                'maxXP' => $maxXP,
                'percentage' => $percentage,
                'solved' => $solvedProblems,
                'total' => $totalProblems
            ];
        });
        
        // Get hackathons (limit 2 for display)
        $hackathons = $this->getHackathons(2);
        
        // Get leaderboard data
        $leaderboardData = $this->getLeaderboardData($learner);
        
        return view('learner.dashboard', compact(
            'learner',
            'currentLevel',
            'xpPoints',
            'currentStreak',
            'achievements',
            'xpProgress',
            'nextLevelXP',
            'weeklyData',
            'todaysChallenge',
            'recommendedQuestions',
            'recentActivities',
            'proficiencies',
            'hackathons',
            'leaderboardData'
        ));
    }
    
    /**
     * Calculate learner level based on XP points
     * Level formula: Level = floor(sqrt(XP / 100))
     */
    private function calculateLevel($xp)
    {
        if ($xp <= 0) return 1;
        return floor(sqrt($xp / 100)) + 1;
    }
    
    /**
     * Get XP required for a specific level
     */
    private function getLevelXP($level)
    {
        if ($level <= 1) return 0;
        return pow($level - 1, 2) * 100;
    }
    
    /**
     * Get XP required for next level
     */
    private function getNextLevelXP($currentLevel)
    {
        return pow($currentLevel, 2) * 100;
    }
    
    /**
     * Count achievements earned by learner
     */
    private function getAchievementsCount($learner)
    {
        $count = 0;
        
        // Get current streak for achievements
        $currentStreak = $learner->getCurrentStreak();
        
        // Achievement: First Steps (Complete 1 question)
        if ($this->getQuestionsCompletedCount($learner) >= 1) $count++;
        
        // Achievement: Problem Solver (Complete 10 questions)
        if ($this->getQuestionsCompletedCount($learner) >= 10) $count++;
        
        // Achievement: Code Master (Complete 50 questions)
        if ($this->getQuestionsCompletedCount($learner) >= 50) $count++;
        
        // Achievement: Streak Keeper (7 day streak)
        if ($currentStreak >= 7) $count++;
        
        // Achievement: Dedicated Learner (30 day streak)
        if ($currentStreak >= 30) $count++;
        
        // Achievement: XP Hunter (Reach 1000 XP)
        if ($learner->totalPoint >= 1000) $count++;
        
        // Achievement: Level Up (Reach level 5)
        if ($this->calculateLevel($learner->totalPoint ?? 0) >= 5) $count++;
        
        // Achievement: Language Explorer (Master 3+ languages)
        if ($learner->userProficiencies->count() >= 3) $count++;
        
        return $count;
    }
    
    private function getQuestionsCompletedCount($learner)
    {
        // This would typically come from a learner_submissions or learner_progress table
        // For now, return a placeholder value
        return 0;
    }
    
    private function getWeeklyPracticeData($learner)
    {
        $data = [];
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();
            
            // Count the number of questions attempted by the learner on this day
            $count = DB::table('attempts')
                ->where('learner_ID', $learner->learner_ID)
                ->whereBetween('dateAttempted', [$startOfDay, $endOfDay])
                ->count();
            
            $data[] = [
                'day' => $days[($date->dayOfWeek + 6) % 7],
                'count' => $count
            ];
        }
        
        return $data;
    }
    
    private function getRecommendedQuestions($learner, $limit = 3)
    {
        // Get approved questions that match learner's proficiencies
        $proficiencyLanguages = $learner->userProficiencies->pluck('language')->toArray();
        
        $questions = Question::where('status', 'Approved')
            ->where('questionCategory', 'learnerPractice')
            ->when(!empty($proficiencyLanguages), function($query) use ($proficiencyLanguages) {
                return $query->whereIn('language', $proficiencyLanguages);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($question) {
                return [
                    'id' => $question->question_ID,
                    'title' => $question->title ?? 'Untitled Question',
                    'difficulty' => ucfirst($question->level ?? 'intermediate'),
                    'language' => $question->language,
                    'chapter' => $question->chapter ?? 'Programming Challenge',
                    'points' => $this->calculatePoints($question->level ?? 'intermediate')
                ];
            });
        
        return $questions;
    }
    
    private function calculatePoints($difficulty)
    {
        $points = [
            'beginner' => 10,
            'easy' => 10,
            'intermediate' => 20,
            'medium' => 20,
            'advanced' => 30,
            'hard' => 30
        ];
        
        return $points[strtolower($difficulty)] ?? 15;
    }
    
    private function getRecentActivities($learner, $limit = 5)
    {
        // This would typically come from a learner_submissions or activity log table
        // For now, return placeholder data
        return collect([]);
    }
    
    /**
     * Get active and upcoming hackathons
     */
    private function getHackathons($limit = 2)
    {
        $today = Carbon::today();
        
        // Get hackathons that are either live or upcoming
        $hackathons = Hackathon::where('hackathonDate', '>=', $today->copy()->subDays(7))
            ->orderByRaw("CASE 
                WHEN hackathonDate <= ? AND hackathonDate >= ? THEN 0 
                ELSE 1 
            END", [$today, $today->copy()->subDays(7)])
            ->orderBy('hackathonDate', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($hackathon) use ($today) {
                $daysUntil = $hackathon->getDaysUntilOrSince();
                $isLive = $hackathon->isLive();
                
                return [
                    'id' => $hackathon->hackathon_ID,
                    'name' => $hackathon->hackathonName,
                    'description' => $hackathon->hackathonDetail,
                    'prize' => number_format($hackathon->totalPrize, 0),
                    'link' => $hackathon->hackathonLink,
                    'status' => $isLive ? 'live' : 'upcoming',
                    'days' => abs($daysUntil),
                    'daysLabel' => $isLive ? 'days left' : 'days',
                    'participants' => rand(100, 300), // Replace with actual participant count if available
                ];
            });
        
        return $hackathons;
    }
    
    /**
     * Get leaderboard data with top 5 learners and current user's position
     */
    private function getLeaderboardData($currentLearner)
    {
        // Get all learners sorted by total points (descending)
        $allLearners = Learner::orderBy('totalPoint', 'desc')
            ->orderBy('username', 'asc')
            ->get();
        
        // Calculate positions with ties
        $position = 1;
        $previousXP = null;
        $actualIndex = 0;
        
        $learnersWithPositions = $allLearners->map(function($learner) use (&$position, &$previousXP, &$actualIndex) {
            $actualIndex++;
            
            // If XP is different from previous, update position to current index
            if ($previousXP !== null && $learner->totalPoint < $previousXP) {
                $position = $actualIndex;
            }
            
            $previousXP = $learner->totalPoint;
            
            return [
                'learner' => $learner,
                'position' => $position,
                'xp' => $learner->totalPoint ?? 0
            ];
        });
        
        // Find current learner's position
        $currentUserPosition = $learnersWithPositions->first(function($item) use ($currentLearner) {
            return $item['learner']->learner_ID === $currentLearner->learner_ID;
        });
        
        $currentPosition = $currentUserPosition['position'] ?? 0;
        
        // Get top 5 positions (which might include more than 5 people if there are ties)
        $topPositions = $learnersWithPositions->filter(function($item) {
            return $item['position'] <= 5;
        });
        
        // Map to display format
        $topFive = $topPositions->map(function($item) use ($currentLearner) {
            $position = $item['position'];
            $learner = $item['learner'];
            $isCurrentUser = $learner->learner_ID === $currentLearner->learner_ID;
            
            $title = match($position) {
                1 => 'Champion',
                2 => 'Runner-up',
                3 => 'Third place',
                default => 'Top 5'
            };
            
            $rankClass = match($position) {
                1 => 'champion',
                2 => 'runner-up',
                3 => 'third-place',
                default => 'top-five'
            };
            
            return [
                'id' => $learner->learner_ID,
                'username' => $learner->username,
                'xp' => $learner->totalPoint ?? 0,
                'position' => $position,
                'title' => $title,
                'rank_class' => $rankClass,
                'is_current_user' => $isCurrentUser
            ];
        });
        
        // Check if current user is in top 5
        $isInTopFive = $currentPosition <= 5;
        
        // Get current user data if not in top 5
        $currentUserData = null;
        if (!$isInTopFive && $currentPosition > 0) {
            // Calculate rank change (mock data for now - you can implement actual tracking)
            $rankChange = rand(-10, 10);
            
            $currentUserData = [
                'id' => $currentLearner->learner_ID,
                'username' => $currentLearner->username,
                'xp' => $currentLearner->totalPoint ?? 0,
                'position' => $currentPosition,
                'rank_change' => $rankChange,
                'rank_change_label' => $rankChange > 0 ? '+' . $rankChange : $rankChange
            ];
        }
        
        return [
            'top_five' => $topFive,
            'current_user' => $currentUserData,
            'current_position' => $currentPosition,
            'total_learners' => $allLearners->count(),
            'is_in_top_five' => $isInTopFive
        ];
    }

    /**
     * Display the hackathon hub page
     */
    public function hackathon()
    {
        $learner = Auth::guard('learner')->user();
        $now = Carbon::now();
        $today = Carbon::today();
        
        // Get all active and upcoming hackathons (only future or current hackathons)
        $hackathons = Hackathon::where('hackathonDate', '>=', $today)
            ->orderBy('hackathonDate', 'asc')
            ->get()
            ->map(function($hackathon) use ($now, $today) {
                $hackathonDate = Carbon::parse($hackathon->hackathonDate);
                $isLive = $hackathonDate->isSameDay($today);
                
                return (object)[
                    'id' => $hackathon->hackathon_ID,
                    'name' => $hackathon->hackathonName,
                    'description' => $hackathon->hackathonDetail,
                    'prize_pool' => $hackathon->totalPrize,
                    'category' => $hackathon->hackathonCategory ?? 'General',
                    'location' => $hackathon->hackathonLocation ?? 'Virtual',
                    'registration_link' => $hackathon->hackathonLink,
                    'status' => $isLive ? 'live' : 'upcoming',
                    'hackathon_date' => $hackathonDate,
                    'days_remaining' => $today->diffInDays($hackathonDate, false),
                    'participants' => rand(50, 300), // Replace with actual count if available
                ];
            });
        
        // Get featured hackathon (nearest upcoming or live hackathon)
        $featuredHackathon = null;
        if ($hackathons->isNotEmpty()) {
            $featured = $hackathons->first();
            $hackathonDate = $featured->hackathon_date;
            
            // Calculate time remaining
            $diff = $now->diff($hackathonDate);
            $months = $diff->y * 12 + $diff->m;
            $days = $diff->d;
            $hours = $diff->h;
            
            $featuredHackathon = (object)[
                'id' => $featured->id,
                'name' => $featured->name,
                'description' => $featured->description,
                'prize_pool' => $featured->prize_pool,
                'category' => $featured->category,
                'location' => $featured->location,
                'registration_link' => $featured->registration_link,
                'status' => $featured->status,
                'participants' => $featured->participants,
                'days_remaining' => $featured->days_remaining,
                'countdown' => [
                    'months' => str_pad($months, 2, '0', STR_PAD_LEFT),
                    'days' => str_pad($days, 2, '0', STR_PAD_LEFT),
                    'hours' => str_pad($hours, 2, '0', STR_PAD_LEFT),
                ],
            ];
        }
        
        // Calculate statistics - "Active" means upcoming hackathons (future events)
        $activeCount = $hackathons->where('status', 'upcoming')->count();
        $totalPrizes = $hackathons->sum('prize_pool');
        $totalParticipants = $hackathons->sum('participants');
        
        return view('learner.hackathon', compact(
            'learner',
            'hackathons',
            'featuredHackathon',
            'activeCount',
            'totalPrizes',
            'totalParticipants'
        ));
    }
}
