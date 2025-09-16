<?php

namespace App\Http\Controllers;

use App\Models\DailyMenu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DailyMenuController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Only admins can add daily menus.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'menu_date' => [
                'required',
                'date',
                'unique:daily_menus,menu_date,NULL,id,meal_type,' . $request->meal_type,
            ],
            'meal_type' => 'required|in:Breakfast,Lunch,Dinner',
            'option_1_id' => 'required|exists:menu_items,id',
            'option_2_id' => 'nullable|exists:menu_items,id|different:option_1_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $dailyMenu = DailyMenu::updateOrCreate(
            ['menu_date' => $request->menu_date, 'meal_type' => $request->meal_type],
            $validator->validated()
        );

        return response()->json([
            'message' => 'Daily menu saved successfully!',
            'daily_menu' => $dailyMenu
        ], 200);
    }

    public function index()
    {
        $dailyMenus = DailyMenu::with(['option1', 'option2'])
            ->whereBetween('menu_date', [
                now()->toDateString(),
                now()->addDays(6)->toDateString()
            ])
            ->orderBy('menu_date', 'asc')
            ->get();

        return response()->json($dailyMenus, 200);
    }

    public function listAll()
    {
        $dailyMenus = DailyMenu::with(['option1', 'option2'])
            ->where('menu_date', '>=', now()->toDateString())
            ->orderBy('menu_date', 'asc')
            ->get();

        return response()->json($dailyMenus);
    }
    
    public function update(Request $request, DailyMenu $dailyMenu)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Only admins can perform this action.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'menu_date' => [
                'required',
                'date',
                Rule::unique('daily_menus')->ignore($dailyMenu->id, 'id')->where(function ($query) use ($request) {
                    return $query->where('meal_type', $request->meal_type);
                }),
            ],
            'meal_type' => 'required|in:Breakfast,Lunch,Dinner',
            'option_1_id' => 'required|exists:menu_items,id',
            'option_2_id' => 'nullable|exists:menu_items,id|different:option_1_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        
        $dailyMenu->update($validator->validated());

        return response()->json([
            'message' => 'Daily menu updated successfully!',
            'daily_menu' => $dailyMenu
        ], 200);
    }

    public function destroy(DailyMenu $dailyMenu)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Only admins can perform this action.'], 403);
        }
        
        $dailyMenu->delete();
        return response()->json(['message' => 'Daily menu deleted successfully.'], 200);
    }
}