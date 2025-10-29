<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // ⭐ ADD THIS LINE (if missing)
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class UserProficiency extends Model
{
    use HasFactory;

    protected $table = 'user_proficiency';

    public $timestamps = false;

    // Important: Composite Primary Key (tell Eloquent NOT to look for 'id')
    public $incrementing = false; 
    protected $keyType = 'array';
    protected $primaryKey = ['user_id', 'language'];

    protected $fillable = [
        'user_id', 'language', 'xp_points', 'level'
    ];

    // Relationship back to User
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
