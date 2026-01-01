<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Learner extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'learners';
    protected $primaryKey = 'learner_ID';

    protected $fillable = [
        'username',
        'email',
        'password',
        'google_ID',
        'github_ID',
        'registration_date',
        'totalPoint',
        'badge',
        'streak',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'registration_date' => 'date',
        ];
    }

    /**
     * Get the user proficiencies for the learner
     */
    public function userProficiencies()
    {
        return $this->hasMany(UserProficiency::class, 'learner_ID', 'learner_ID');
    }

    // ========================================
    // QUADRATIC CURVE LEVELING SYSTEM
    // Formula: Total XP Required = Constant × (Level)²
    // ========================================
    
    const LEVEL_CONSTANT = 10; // Controls the steepness of the curve
    
    /**
     * Calculate current level based on total XP
     * Uses quadratic formula: Level thresholds are 0, 40, 90, 160, 250, etc.
     * 
     * @return int Current level (minimum 1)
     */
    public function getCurrentLevel()
    {
        if ($this->totalPoint < 40) {
            return 1; // Level 1: 0-39 XP
        } elseif ($this->totalPoint < 90) {
            return 2; // Level 2: 40-89 XP
        } elseif ($this->totalPoint < 160) {
            return 3; // Level 3: 90-159 XP
        } elseif ($this->totalPoint < 250) {
            return 4; // Level 4: 160-249 XP
        } elseif ($this->totalPoint < 360) {
            return 5; // Level 5: 250-359 XP
        } else {
            // For higher levels, use the quadratic formula
            // Level = floor(sqrt(XP / 10)) + 1
            return floor(sqrt($this->totalPoint / self::LEVEL_CONSTANT)) + 1;
        }
    }
    
    /**
     * Get XP required for a specific level
     * Formula: XP = Constant × (Level²)
     * 
     * @param int $level Target level
     * @return int Total XP required to reach that level
     */
    public static function getXpRequirement($level)
    {
        if ($level <= 1) return 0; // Level 1 starts at 0 XP
        
        // Level 2: 10 × (2²) = 10 × 4 = 40 XP
        // Level 3: 10 × (3²) = 10 × 9 = 90 XP
        // Level 4: 10 × (4²) = 10 × 16 = 160 XP
        return self::LEVEL_CONSTANT * ($level * $level);
    }
    
    /**
     * Get XP needed to reach the next level
     * 
     * @return int XP needed for next level
     */
    public function getXpForNextLevel()
    {
        $currentLevel = $this->getCurrentLevel();
        $nextLevel = $currentLevel + 1;
        
        return self::getXpRequirement($nextLevel);
    }
    
    /**
     * Get progress towards next level
     * Returns array with progress information for UI display
     * 
     * @return array ['current_level', 'next_level', 'current_xp', 'xp_progress', 'xp_gap', 'percentage']
     */
    public function getLevelProgress()
    {
        $currentLevel = $this->getCurrentLevel();
        $nextLevel = $currentLevel + 1;
        
        // XP thresholds
        $currentLevelXp = self::getXpRequirement($currentLevel);
        $nextLevelXp = self::getXpRequirement($nextLevel);
        
        // Progress within current level
        $xpProgress = $this->totalPoint - $currentLevelXp;
        $xpGap = $nextLevelXp - $currentLevelXp;
        
        // Percentage for progress bar
        $percentage = $xpGap > 0 ? round(($xpProgress / $xpGap) * 100, 1) : 0;
        
        return [
            'current_level' => $currentLevel,
            'next_level' => $nextLevel,
            'current_xp' => $this->totalPoint,
            'xp_progress' => $xpProgress,
            'xp_gap' => $xpGap,
            'percentage' => $percentage,
            'xp_for_current_level' => $currentLevelXp,
            'xp_for_next_level' => $nextLevelXp,
        ];
    }

    /**
     * Get current streak for a learner (consecutive days with attempts)
     * 
     * @return int Number of consecutive days
     */
    public function getCurrentStreak()
    {
        // Get all distinct dates when learner attempted questions
        $activityDates = \Illuminate\Support\Facades\DB::table('attempts')
            ->where('learner_ID', $this->learner_ID)
            ->selectRaw('DATE(dateAttempted) as attempt_date')
            ->distinct()
            ->orderByDesc('attempt_date')
            ->pluck('attempt_date')
            ->toArray();

        if (empty($activityDates)) {
            return 0;
        }

        $today = \Carbon\Carbon::today();
        $yesterday = \Carbon\Carbon::yesterday();
        $latestActivityDate = \Carbon\Carbon::parse($activityDates[0]);
        
        // Determine if the streak is still active
        // Streak is active if last activity was today OR yesterday
        $isToday = $latestActivityDate->isSameDay($today);
        $isYesterday = $latestActivityDate->isSameDay($yesterday);
        
        if (!$isToday && !$isYesterday) {
            // Last activity was 2+ days ago - streak is broken
            return 0;
        }

        // Start counting the streak
        $streak = 0;
        
        // Determine starting point for counting
        // If last activity was yesterday, we start from yesterday (streak still active for today)
        // If last activity was today, we start from today
        $expectedDate = $isYesterday ? $yesterday : $today;

        // Count consecutive days backwards from the expected date
        foreach ($activityDates as $dateString) {
            $activityDate = \Carbon\Carbon::parse($dateString);
            
            // Check if this date matches our expected date
            if ($activityDate->isSameDay($expectedDate)) {
                $streak++;
                // Move expected date one day back
                $expectedDate = $expectedDate->copy()->subDay();
            } else {
                // Gap found - stop counting
                break;
            }
        }

        return $streak;
    }

    /**
     * Get the badges for the learner
     */
    public function badges()
    {
        return $this->morphMany(UserBadge::class, 'badgeable');
    }
}
