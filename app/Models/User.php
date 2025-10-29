<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $primaryKey = 'user_id';

    protected $passwordColumn = 'password_hash';
    protected $fillable = [
        'username', // Changed from 'name' to 'username'
        'email',
        'password_hash', // Changed from 'password' to 'password_hash'
        'role',
        'badge',
        'streak',
        'github_id', // Add this
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function proficiency(): HasMany
    {
        return $this->hasMany(UserProficiency::class, 'user_id', 'user_id');
    }
    
    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'user_id', 'user_id');
    }
    
    public function competencies()
    {
        return $this->hasMany(ReviewerCompetency::class, 'user_id', 'user_id');
    }

    public function reviewsGiven()
    {
        // User is the 'approved_by' in the Question table
        return $this->hasMany(Question::class, 'approved_by', 'user_id');
    }

    
}
