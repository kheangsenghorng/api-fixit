<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET all categories
    public function index()
    {
        return response()->json(Category::latest()->get());
    }

    // STORE category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_group' => 'required|in:service,mechanical',
            'status' => 'in:active,inactive',
            'icon' => 'nullable|string'
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    // SHOW single category
    public function show(Category $category)
    {
        return response()->json($category);
    }

    // UPDATE category
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_group' => 'sometimes|required|in:service,mechanical',
            'status' => 'in:active,inactive',
            'icon' => 'nullable|string'
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    // DELETE category
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}