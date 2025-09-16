<?php

namespace App\Http\Controllers;

use App\Models\UserSelection;
use App\Models\DailyMenu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserSelectionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $selections = UserSelection::with('dailyMenu', 'selectedOption')
            ->where('user_id', $user->id)
            ->get();

        return response()->json($selections, 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->subscription || $user->subscription->meals_remaining <= 0 || $user->is_subscription_paused) {
            return response()->json(['message' => 'You do not have an active plan or your subscription is paused.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'daily_menu_id' => 'required|exists:daily_menus,id',
            'selected_option_id' => 'nullable|exists:menu_items,id',
            'is_skipped' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        
        $dailyMenu = DailyMenu::find($validated['daily_menu_id']);
        $subscription = $user->subscription;

        $existingSelection = UserSelection::where('user_id', $user->id)
            ->where('daily_menu_id', $validated['daily_menu_id'])
            ->first();

        $menuDate = \Carbon\Carbon::parse($dailyMenu->menu_date)->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();
        $currentTime = now();
        $deadline = now()->setTime(17, 0, 0);

        if ($menuDate->isSameDay(now())) {
            return response()->json(['message' => 'Today\'s selection window is closed.'], 403);
        }
        
        if ($menuDate->isSameDay($tomorrow) && $currentTime->gt($deadline)) {
             return response()->json(['message' => 'Selection window for tomorrow is closed. Please select before 5 PM.'], 403);
        }

        if (!$existingSelection) {
            if (!($validated['is_skipped'] ?? false)) {
                $subscription->decrement('meals_remaining');
            }
        } else {
            if ($existingSelection->is_skipped && !($validated['is_skipped'] ?? false)) {
                $subscription->decrement('meals_remaining');
            } elseif (!($existingSelection->is_skipped) && ($validated['is_skipped'] ?? false)) {
                $subscription->increment('meals_remaining');
            }
        }
        $subscription->save();

        $userSelection = UserSelection::updateOrCreate(
            ['user_id' => $user->id, 'daily_menu_id' => $validated['daily_menu_id']],
            [
                'selected_option_id' => $validated['selected_option_id'] ?? null,
                'is_skipped' => $validated['is_skipped'] ?? false,
            ]
        );

        return response()->json([
            'message' => 'Meal selection updated successfully!',
            'selection' => $userSelection
        ], 200);
    }
}