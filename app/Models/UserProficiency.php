<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProficiency extends Model
{
    protected $table = 'user_proficiencies';
    
    protected $fillable = [
        'learner_ID',
        'language',
        'XP',
        'level',
    ];

    // Don't define composite primary key - it causes issues with Eloquent
    // Instead, we'll use the combination as unique identifiers
    public $incrementing = false;
    protected $primaryKey = null; // No single primary key

    /**
     * Get the learner that owns this proficiency
     */
    public function learner()
    {
        return $this->belongsTo(Learner::class, 'learner_ID', 'learner_ID');
    }
}
