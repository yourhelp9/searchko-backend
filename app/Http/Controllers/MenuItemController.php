<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // Auth facade ko add kiya gaya hai
use Illuminate\Support\Facades\Storage; // Storage facade ko add kiya gaya hai

class MenuItemController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Only admins can perform this action.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:menu_items',
            'description' => 'nullable|string',
            'meal_type' => ['required', Rule::in(['Breakfast', 'Lunch', 'Dinner'])],
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();
        $path = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('menu_images', 'public');
            $validatedData['image_url'] = $path;
        }
        
        $menuItem = MenuItem::create($validatedData);

        return response()->json([
            'message' => 'Menu item created successfully!',
            'menu_item' => $menuItem
        ], 201);
    }

    public function index()
    {
        $menuItems = MenuItem::all();
        return response()->json($menuItems);
    }
    
    public function destroy(MenuItem $menuItem)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Only admins can perform this action.'], 403);
        }
        
        // Agar image hai to use delete karein
        if ($menuItem->image_url) {
            Storage::disk('public')->delete($menuItem->image_url);
        }
        
        $menuItem->delete();
        return response()->json(['message' => 'Menu item deleted successfully.'], 200);
    }
    
    public function update(Request $request, MenuItem $menuItem)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Only admins can perform this action.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menu_items')->ignore($menuItem->id),
            ],
            'description' => 'nullable|string',
            'meal_type' => ['required', Rule::in(['Breakfast', 'Lunch', 'Dinner'])],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $validatedData = $validator->validated();
        
        if ($request->hasFile('image')) {
            if ($menuItem->image_url) {
                 Storage::disk('public')->delete($menuItem->image_url);
            }

            $path = $request->file('image')->store('menu_images', 'public');
            $validatedData['image_url'] = $path;
        }

        $menuItem->update($validatedData);

        return response()->json([
            'message' => 'Menu item updated successfully!',
            'menu_item' => $menuItem
        ], 200);
    }
}