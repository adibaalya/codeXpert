<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Hackathon extends Model
{
    protected $table = 'hackathons';
    protected $primaryKey = 'hackathon_ID';

    protected $fillable = [
        'hackathonName',
        'hackathonDetail',
        'totalPrize',
        'targetedParticipant',
        'hackathonLink',
        'hackathonCategory',
        'hackathonDate',
    ];

    protected $casts = [
        'hackathonDate' => 'date',
        'totalPrize' => 'float',
    ];

    /**
     * Check if hackathon is currently live (date is today or in the past but within a week)
     */
    public function isLive()
    {
        $today = Carbon::today();
        $hackathonDate = Carbon::parse($this->hackathonDate);
        
        // Consider "live" if it started today or up to 7 days ago
        return $hackathonDate->lte($today) && $hackathonDate->gte($today->copy()->subDays(7));
    }

    /**
     * Check if hackathon is upcoming (date is in the future)
     */
    public function isUpcoming()
    {
        return Carbon::parse($this->hackathonDate)->isFuture();
    }

    /**
     * Get days until hackathon starts or days since it started
     */
    public function getDaysUntilOrSince()
    {
        $today = Carbon::today();
        $hackathonDate = Carbon::parse($this->hackathonDate);
        
        return $today->diffInDays($hackathonDate, false);
    }

    /**
     * Get status: 'live' or 'upcoming'
     */
    public function getStatus()
    {
        return $this->isLive() ? 'live' : 'upcoming';
    }
}
