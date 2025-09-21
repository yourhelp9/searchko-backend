<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMenu extends Model
{
    use HasFactory;


    // `option_1_id` ko `menu_items` table se jodta hai
    public function option1()
    {
        return $this->belongsTo(MenuItem::class, 'option_1_id');
    }

    // `option_2_id` ko `menu_items` table se jodta hai
    public function option2()
    {
        return $this->belongsTo(MenuItem::class, 'option_2_id');
    }

    protected $fillable = [
        'menu_date',
        'meal_type',
        'option_1_id',
        'option_2_id',
    ];
}