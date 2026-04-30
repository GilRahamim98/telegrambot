<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    protected $fillable = [
        'user_id',
        'exercise',
        'weight',
        'reps',
        'sets',
        'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
