<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ReviewerSession extends Model
{
    protected $primaryKey = 'session_id';
    
    protected $fillable = [
        'reviewer_ID',
        'login_at',
        'logout_at',
        'duration_minutes'
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    /**
     * Relationship to Reviewer
     */
    public function reviewer()
    {
        return $this->belongsTo(Reviewer::class, 'reviewer_ID', 'reviewer_ID');
    }

    /**
     * Calculate and set duration when ending session
     */
    public function endSession()
    {
        $this->logout_at = Carbon::now();
        $this->duration_minutes = $this->login_at->diffInMinutes($this->logout_at);
        $this->save();
    }

    /**
     * Get average session duration for a reviewer in minutes
     */
    public static function getAverageSessionDuration($reviewerId)
    {
        return self::where('reviewer_ID', $reviewerId)
            ->whereNotNull('logout_at')
            ->avg('duration_minutes');
    }

    /**
     * Get current streak for a reviewer (consecutive days with reviews)
     */
    public static function getCurrentStreak($reviewerId)
    {
        // Get all distinct dates when reviewer had activity (reviewed questions)
        $activityDates = \App\Models\Question::where('reviewer_ID', $reviewerId)
            ->whereIn('status', ['Approved', 'Rejected'])
            ->selectRaw('DATE(updated_at) as review_date')
            ->distinct()
            ->orderByDesc('review_date')
            ->pluck('review_date')
            ->toArray();

        if (empty($activityDates)) {
            return 0;
        }

        $today = Carbon::today();
        $latestActivityDate = Carbon::parse($activityDates[0]);
        
        // Check if the latest activity was today or yesterday
        // If it's more than 1 day ago, the streak is broken
        $daysSinceLastActivity = $today->diffInDays($latestActivityDate, false);
        
        if ($daysSinceLastActivity > 1) {
            // No activity today or yesterday - streak is broken
            return 0;
        }
        
        if ($daysSinceLastActivity < 0) {
            // This shouldn't happen (activity in the future), but handle it
            return 0;
        }

        // Start counting the streak
        $streak = 0;
        $expectedDate = $today;
        
        // If no activity today, start from yesterday
        if ($daysSinceLastActivity === 1) {
            $expectedDate = Carbon::yesterday();
        }

        // Count consecutive days backwards from the expected date
        foreach ($activityDates as $dateString) {
            $activityDate = Carbon::parse($dateString);
            
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
}
