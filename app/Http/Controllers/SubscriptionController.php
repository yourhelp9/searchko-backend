<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Only admins can manage subscriptions.'], 403);
        }

        $planCredits = [
            1 => 7,  // Weekly 1 Time
            2 => 14, // Weekly 2 Times
            3 => 30, // Monthly 1 Time
            4 => 60, // Monthly 2 Times
            5 => 90, // Monthly 3 Times
        ];

        $planMealsPerDay = [
            1 => 1,
            2 => 2,
            3 => 1,
            4 => 2,
            5 => 3,
        ];

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|integer|in:1,2,3,4,5',
            'meals_time_preference' => 'required|array',
            'meals_time_preference.*' => 'in:Breakfast,Lunch,Dinner',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $meals_per_day = $planMealsPerDay[$request->input('plan_id')] ?? 0;
        if (count($request->input('meals_time_preference')) !== $meals_per_day) {
            return response()->json(['message' => "The number of selected meal types must be " . $meals_per_day . " for this plan."], 422);
        }

        $validated = $validator->validated();
        $userId = $validated['user_id'];
        $planId = $validated['plan_id'];
        $mealsPreference = implode(',', $validated['meals_time_preference']);

        $user = User::with('subscription')->find($userId);

        if ($user->subscription && $user->subscription->meals_remaining > 0) {
            return response()->json(['message' => 'User already has an active subscription. Please wait for credits to expire.'], 409);
        }

        $mealsRemaining = $planCredits[$planId];
        $mealsPerDay = $planMealsPerDay[$planId];
        
        $user->meals_time_preference = $mealsPreference;
        $user->is_subscription_paused = false;
        $user->save();

        $subscription = Subscription::updateOrCreate(
            ['user_id' => $userId],
            [
                'plan_id' => $planId,
                'meals_remaining' => $mealsRemaining,
                'meals_per_day' => $mealsPerDay,
                'is_paused' => false,
            ]
        );

        return response()->json([
            'message' => 'Subscription activated/updated successfully!',
            'subscription' => $subscription
        ], 201);
    }

    public function toggleSubscriptionPause()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized. Please log in.'], 401);
        }

        if (!$user->subscription || $user->subscription->meals_remaining <= 0) {
            return response()->json(['message' => 'No active plan found to pause or resume.'], 404);
        }
        
        $user->is_subscription_paused = !$user->is_subscription_paused;
        $user->save();
        
        $message = $user->is_subscription_paused ? 'Subscription paused successfully!' : 'Subscription resumed successfully!';

        return response()->json([
            'message' => $message,
            'is_paused' => $user->is_subscription_paused,
        ]);
    }
}