<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\DailyMenuController;
use App\Http\Controllers\UserSelectionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReviewController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ▼▼▼ YEH ROUTES PUBLIC HAIN (KOI BHI ACCESS KAR SAKTA HAI) ▼▼▼
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/daily-menus', [DailyMenuController::class, 'index']);

// ▼▼▼ YEH NAYA SURAKSHIT GROUP HAI (SIRF LOGGED-IN USERS KE LIYE) ▼▼▼
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);

    // ✅ User ke meal selection ko save karne ka route
    Route::post('/user-selections', [UserSelectionController::class, 'store']);
    Route::get('/user-selections', [UserSelectionController::class, 'index']);
    
    // User ke subscription me meals ko update karne ka route
    Route::post('/update-meals', [UserSelectionController::class, 'updateSubscriptionMeals']);
    
    // ▼▼▼ YEH USER KE PAUSE/RESUME KE LIYE SAHI JAGAH HAI ▼▼▼
    Route::post('/subscriptions/toggle-pause', [SubscriptionController::class, 'toggleSubscriptionPause']);

    // ✅ User ke liye review submit karne ka route
    Route::post('/reviews', [ReviewController::class, 'store']);

    // ▼▼▼ ADMIN ROUTES (SIRF ADMIN KE LIYE) ▼▼▼
    Route::middleware('admin')->group(function () {
        // Admin ke liye sabhi users ko dekhne ka route
        Route::get('/admin/users', [AdminController::class, 'getUsers']);
        
        // ✅ getUserDetails function ko wapas add kiya gaya hai
        Route::get('/admin/users/{userId}', [AdminController::class, 'getUserDetails']);
        
        // Menu Items ke routes
        Route::get('/menu-items', [MenuItemController::class, 'index']);
        Route::post('/menu-items', [MenuItemController::class, 'store']);
        Route::put('/menu-items/{menuItem}', [MenuItemController::class, 'update']);
        Route::delete('/menu-items/{menuItem}', [MenuItemController::class, 'destroy']);
        
        // Daily Menu ke routes
        Route::get('/admin/daily-menus', [DailyMenuController::class, 'listAll']);
        Route::post('/daily-menus', [DailyMenuController::class, 'store']);
        Route::put('/daily-menus/{dailyMenu}', [DailyMenuController::class, 'update']);
        Route::delete('/daily-menus/{dailyMenu}', [DailyMenuController::class, 'destroy']);
        
        // Subscription ke routes
        Route::post('/subscriptions', [SubscriptionController::class, 'store']);
        
        // Reports ka route
        Route::get('/admin/reports/{date}', [AdminController::class, 'getOrderReport']);

        // ✅ User ko delete karne ka route
        Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser']);
        
        // ✅ Reviews dekhne ka route
        Route::get('/admin/reviews', [ReviewController::class, 'index']);

        
    });
});