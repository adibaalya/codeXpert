<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Learner extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'learners';
    protected $primaryKey = 'learner_ID';

    protected $fillable = [
        'username',
        'email',
        'password',
        'google_ID',
        'github_ID',
        'registration_date',
        'totalPoint',
        'badge',
        'streak',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'registration_date' => 'date',
        ];
    }

    /**
     * Get the user proficiencies for the learner
     */
    public function userProficiencies()
    {
        return $this->hasMany(UserProficiency::class, 'learner_ID', 'learner_ID');
    }
}
