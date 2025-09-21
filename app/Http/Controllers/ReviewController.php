<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    // User ke liye review submit karne ka method
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Validation rule ko update kiya gaya hai
            'meal_rating' => 'required|in:Quality or Taste Issues,Insufficient Portion Size,Menu,Meal Timings,Delicious,Good,Average,Bad',
            'custom_message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'meal_rating' => $request->meal_rating,
            'custom_message' => $request->custom_message,
        ]);

        return response()->json(['message' => 'Review submitted successfully!', 'review' => $review], 201);
    }

    // Admin ke liye sabhi reviews dekhne ka method (latest first)
    public function index()
    {
        $reviews = Review::with('user')->latest()->get();
        return response()->json($reviews);
    }
}