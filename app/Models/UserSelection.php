<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'daily_menu_id',
        'selected_option_id',
        'is_skipped',
    ];

    /**
     * UserSelection ko us user se jodta hai jisne isko banaya.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * UserSelection ko daily menu entry se jodta hai.
     */
    public function dailyMenu()
    {
        return $this->belongsTo(DailyMenu::class);
    }

    /**
     * UserSelection ko chune hue menu item se jodta hai.
     */
    public function selectedOption()
    {
        return $this->belongsTo(MenuItem::class, 'selected_option_id');
    }
}