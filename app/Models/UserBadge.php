<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    protected $guarded = [];

    public function badgeable()
    {
        return $this->morphTo();
    }
}
