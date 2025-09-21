<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSelection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function getUsers(Request $request)
    {
        $query = User::with(['subscription', 'selections.dailyMenu.option1', 'selections.dailyMenu.option2']);
        $perPage = $request->input('per_page', 20);

        if ($request->has('is_active')) {
            $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            if ($isActive) {
                $query->whereHas('subscription');
            } else {
                $query->whereDoesntHave('subscription');
            }
        }
        
        $users = $query->paginate($perPage);
        return response()->json($users);
    }

    public function getOrderReport(string $date)
    {
        $deliveryReport = UserSelection::with(['user', 'dailyMenu', 'selectedOption'])
            ->whereHas('dailyMenu', function ($query) use ($date) {
                $query->where('menu_date', $date);
            })
            ->where('is_skipped', false)
            ->get()
            ->map(function ($selection) {
                if ($selection->user && $selection->selectedOption) {
                    return [
                        'user_name' => $selection->user->name,
                        'meal_type' => $selection->dailyMenu->meal_type,
                        'selected_meal' => $selection->selectedOption->name,
                    ];
                }
                return null;
            })
            ->filter()
            ->values();

        $kitchenReport = $deliveryReport->groupBy('selected_meal')
            ->map(function ($items, $mealName) {
                return ['meal_name' => $mealName, 'quantity' => $items->count()];
            })
            ->values();

        return response()->json([
            'date' => $date,
            'kitchen_report' => $kitchenReport,
            'delivery_report' => $deliveryReport,
        ]);
    }
    
    // getUserDetails function ko wapas add kiya gaya hai
    public function getUserDetails(string $userId)
    {
        $user = User::with(['subscription', 'selections.dailyMenu.option1', 'selections.dailyMenu.option2', 'selections.selectedOption'])
            ->find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        
        $user->selections = $user->selections->filter(function($selection) {
            return $selection->dailyMenu !== null;
        })->sortByDesc(function($selection) { // sort by desc to show latest first
            return $selection->dailyMenu->menu_date;
        })->values();

        return response()->json($user);
    }

    public function destroyUser(User $user)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Only admins can delete users.'], 403);
        }

        if (Auth::user()->id === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}