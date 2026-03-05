<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Automatically apply CategoryPolicy to all resource methods
        $this->authorizeResource(Category::class, 'category');
    }

    /**
     * GET all categories (search + filter + pagination)
     */
    public function index(Request $request)
    {
        $query = Category::query();

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by group
        if ($request->filled('group')) {
            $query->where('category_group', $request->group);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
  
        $categories = $query->latest()->paginate(10);

        return CategoryResource::collection($categories);
    }

    /**
     * STORE category
     */
    public function store(CategoryStoreRequest $request)
    {

        $data = $request->validated();

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('categories', 'public');
        }

         $category = Category::create($data);

        return new CategoryResource($category);
    }

    /**
     * SHOW single category
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * UPDATE category
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $data = $request->validated();
    
        if ($request->hasFile('icon')) {
    
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
    
            $data['icon'] = $request->file('icon')->store('categories', 'public');
        }
    
        $category->update($data);
    
        return new CategoryResource($category);
    }

    /**
     * UPDATE category
     */

    public function updateManyStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
            'status' => 'required|in:active,inactive'
        ]);

        Category::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Category status updated successfully'
        ]);
    }

    /**
     * DELETE category (soft delete)
     */
    public function destroy(Category $category)
    {
         // Delete icon file if exists
        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }


        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * DELETE category destroyMany
     */
    public function destroyMany(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id'
        ]);
    
        $categories = Category::whereIn('id', $request->ids)->get();
    
        foreach ($categories as $category) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
        }
    
        Category::whereIn('id', $request->ids)->delete();
    
        return response()->json([
            'message' => 'Categories deleted successfully'
        ]);
    }
    /**
     * RESTORE soft deleted category
     */
    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);

        $this->authorize('restore', $category);

        $category->restore();

        return new CategoryResource($category);
    }

    /**
     * FORCE DELETE permanently
     */
    public function forceDelete($id)
    {
        $category = Category::withTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $category);

        $category->forceDelete();

        return response()->json([
            'message' => 'Category permanently deleted'
        ]);
    }
}