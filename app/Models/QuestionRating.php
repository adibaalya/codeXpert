<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionRating extends Model
{
    protected $fillable = [
        'learner_ID',
        'question_ID',
        'rating'
    ];

    public function learner()
    {
        return $this->belongsTo(Learner::class, 'learner_ID', 'learner_ID');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_ID', 'question_ID');
    }
}
