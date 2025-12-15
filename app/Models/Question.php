<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $primaryKey = 'question_ID';

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
        'grading_details'
    ];

    protected $casts = [
        'options' => 'array',
        'input' => 'array',
        'expected_output' => 'array',
        'grading_details' => 'array',
    ];

    public function reviewer()
    {
        return $this->belongsTo(Reviewer::class, 'reviewer_ID', 'reviewer_ID');
    }
}
