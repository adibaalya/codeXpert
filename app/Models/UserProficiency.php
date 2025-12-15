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

    // Composite primary key
    protected $primaryKey = ['learner_ID', 'language'];
    public $incrementing = false;

    /**
     * Get the learner that owns this proficiency
     */
    public function learner()
    {
        return $this->belongsTo(Learner::class, 'learner_ID', 'learner_ID');
    }
}
