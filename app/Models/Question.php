<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $primaryKey = 'question_ID';

    // Allowed programming languages (SQL removed)
    const ALLOWED_LANGUAGES = ['Java', 'C', 'C++', 'Python', 'JavaScript', 'PHP', 'C#'];

    protected $fillable = [
        'title',
        'content',
        'description',
        'problem_statement',
        'constraints',
        'expected_output',
        'answersData',
        'status',
        'reviewer_ID',
        'language',
        'level',
        'questionCategory',
        'questionType',
        'options',
        'chapter',
        'hint',
        'input',
        'expectedAnswer',
        'grading_details',
        'good_ratings',
        'bad_ratings'
    ];

    protected $casts = [
        'options' => 'array',
        'input' => 'array',
        'expected_output' => 'array',
        'grading_details' => 'array',
    ];

    /**
     * Boot method to add model event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent SQL questions from being created
        static::creating(function ($question) {
            if (isset($question->language) && strtoupper($question->language) === 'SQL') {
                throw new \Exception('SQL questions are no longer supported. Please use one of the following languages: ' . implode(', ', self::ALLOWED_LANGUAGES));
            }

            // Validate language is in allowed list
            if (isset($question->language) && !in_array($question->language, self::ALLOWED_LANGUAGES)) {
                throw new \Exception('Invalid language. Allowed languages are: ' . implode(', ', self::ALLOWED_LANGUAGES));
            }
        });

        // Prevent SQL questions from being updated
        static::updating(function ($question) {
            if (isset($question->language) && strtoupper($question->language) === 'SQL') {
                throw new \Exception('Cannot update to SQL language. SQL questions are no longer supported.');
            }

            // Validate language is in allowed list
            if (isset($question->language) && !in_array($question->language, self::ALLOWED_LANGUAGES)) {
                throw new \Exception('Invalid language. Allowed languages are: ' . implode(', ', self::ALLOWED_LANGUAGES));
            }
        });
    }

    public function reviewer()
    {
        return $this->belongsTo(Reviewer::class, 'reviewer_ID', 'reviewer_ID');
    }

    public function ratings()
    {
        return $this->hasMany(QuestionRating::class, 'question_ID', 'question_ID');
    }
}
