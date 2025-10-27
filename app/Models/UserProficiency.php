<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProficiency extends Model
{
    use HasFactory;

    protected $table = 'user_proficiency';

    // Important: Composite Primary Key (tell Eloquent NOT to look for 'id')
    public $incrementing = false; 
    protected $keyType = 'array';
    protected $primaryKey = ['user_id', 'language'];

    protected $fillable = [
        'user_id', 'language', 'xp_points', 'level'
    ];

    // Relationship back to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
