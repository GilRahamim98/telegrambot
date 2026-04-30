<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'calories',
        'protein',
        'carbs',
        'fats',
        'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
