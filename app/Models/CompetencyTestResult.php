<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetencyTestResult extends Model
{
    protected $fillable = [
        'reviewer_ID',
        'language',
        'mcq_score',
        'code_score',
        'total_score',
        'plagiarism_score',
        'level_achieved',
        'passed',
        'mcq_answers',
        'code_solutions',
        'completed_at'
    ];

    protected $casts = [
        'mcq_answers' => 'array',
        'code_solutions' => 'array',
        'passed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(Reviewer::class, 'reviewer_ID', 'reviewer_ID');
    }

    public function calculateLevelAchieved()
    {
        if ($this->total_score >= 90) {
            return 'all';
        } elseif ($this->total_score >= 75) {
            return 'intermediate';
        } elseif ($this->total_score >= 50) {
            return 'beginner';
        }
        return null;
    }
}
