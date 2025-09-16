<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
    
    public function user()
    {
        $user = Auth::user()
            ->load(['subscription', 'selections' => function($query) {
                // Yahaan par nested relationships ko sahi se load kiya gaya hai
                $query->with(['dailyMenu']);
            }]);

        return response()->json($user);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('profile_image') && str_contains($errors->first('profile_image'), 'size')) {
                return response()->json(['message' => 'Image size is too large. Max size is 10MB.'], 422);
            }
            return response()->json(['message' => 'Validation failed', 'errors' => $errors], 422);
        }

        $validatedData = $validator->validated();

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image_url) {
                File::delete(public_path('storage/' . $user->profile_image_url));
            }
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $validatedData['profile_image_url'] = $path;
        } elseif ($request->input('remove_profile_image') === 'true') {
             if ($user->profile_image_url) {
                File::delete(public_path('storage/' . $user->profile_image_url));
            }
            $validatedData['profile_image_url'] = null;
        }

        $user->update($validatedData);

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => $user->fresh(),
        ]);
    }
}