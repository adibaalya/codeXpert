<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Reviewer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'reviewers';
    protected $primaryKey = 'reviewer_ID';

    protected $fillable = [
        'username',
        'email',
        'password',
        'google_ID',
        'github_ID',
        'registrationDate',
        'isQualified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'registrationDate' => 'date',
            'isQualified' => 'boolean',
        ];
    }
}
