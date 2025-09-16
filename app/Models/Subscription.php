<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id', // <-- Changed from plan_name
        'meals_per_day', // <-- Added
        'is_paused',
        'meals_remaining',
        // 'start_date' and 'end_date' are no longer needed in the fillable array
    ];

    protected $casts = [
        'is_paused' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}