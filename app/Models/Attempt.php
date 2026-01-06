<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attempt extends Model
{
    protected $table = 'attempts';
    protected $primaryKey = 'attempt_id';

    protected $fillable = [
        'question_ID',
        'learner_ID',
        'submittedCode',
        'language',
        'plagiarismScore',
        'accuracyScore',
        'aiFeedback',
        'dateAttempted',
    ];

    protected $casts = [
        'plagiarismScore' => 'float',
        'accuracyScore' => 'float',
        'dateAttempted' => 'datetime',
        'aiFeedback' => 'array', // Automatically cast JSON to array
    ];

    /**
     * Get the question that this attempt belongs to
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_ID', 'question_ID');
    }

    /**
     * Get the learner that made this attempt
     */
    public function learner()
    {
        return $this->belongsTo(Learner::class, 'learner_ID', 'learner_ID');
    }

    /**
     * Check if the attempt passed (100% accuracy)
     * 
     * @return bool
     */
    public function isPassed()
    {
        return $this->accuracyScore >= 100;
    }

    /**
     * Check if the attempt has plagiarism risk
     * 
     * @param float $threshold Plagiarism score threshold (default: 70)
     * @return bool
     */
    public function hasPlagiarism($threshold = 70)
    {
        return $this->plagiarismScore >= $threshold;
    }

    /**
     * Get plagiarism risk level
     * 
     * @return string 'Low', 'Medium', 'High', or 'Critical'
     */
    public function getPlagiarismRiskLevel()
    {
        if ($this->plagiarismScore < 30) {
            return 'Low';
        } elseif ($this->plagiarismScore < 60) {
            return 'Medium';
        } elseif ($this->plagiarismScore < 80) {
            return 'High';
        } else {
            return 'Critical';
        }
    }

    /**
     * Get human-readable time since attempt
     * 
     * @return string
     */
    public function getTimeSinceAttempt()
    {
        return $this->dateAttempted->diffForHumans();
    }

    /**
     * Scope: Get only passed attempts
     */
    public function scopePassed($query)
    {
        return $query->where('accuracyScore', '>=', 100);
    }

    /**
     * Scope: Get only failed attempts
     */
    public function scopeFailed($query)
    {
        return $query->where('accuracyScore', '<', 100);
    }

    /**
     * Scope: Get attempts with plagiarism risk
     */
    public function scopeWithPlagiarism($query, $threshold = 70)
    {
        return $query->where('plagiarismScore', '>=', $threshold);
    }

    /**
     * Scope: Get attempts for a specific language
     */
    public function scopeForLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope: Get recent attempts (within last N days)
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('dateAttempted', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope: Get attempts for today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('dateAttempted', Carbon::today());
    }
}
