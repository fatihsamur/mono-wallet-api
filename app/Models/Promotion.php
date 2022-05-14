<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    /**
     * The users that belong to the promotion.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}