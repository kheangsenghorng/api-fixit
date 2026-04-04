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
        $this->authorizeResource(Category::class, 'category');
    }

    /**
     * GET all categories (search + filter + pagination)
     */
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('group')) {
            $query->where('category_group', $request->group);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $categories = $query
            ->latest('id')
            ->paginate($request->integer('per_page', 10));

        return CategoryResource::collection($categories);
    }

    /**
     * Get category statistics.
     */
    public function stats()
    {
        $stats = Category::selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive
            ")
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Category statistics retrieved successfully.',
            'data' => [
                'total_categories' => (int) $stats->total,
                'active_categories' => (int) $stats->active,
                'inactive_categories' => (int) $stats->inactive,
            ],
        ]);
    }

    /**
     * GET active categories
     */
    public function activeCategories(Request $request)
    {
        $categories = Category::where('status', 'active')
            ->latest('id')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Active categories retrieved successfully',
            'data' => CategoryResource::collection($categories)->resolve(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
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
        $category->refresh();

        return (new CategoryResource($category))->additional([
            'message' => 'Category created successfully',
        ]);
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
        $category->refresh();

        return (new CategoryResource($category))->additional([
            'message' => 'Category updated successfully',
        ]);
    }

    /**
     * BULK UPDATE status
     */
    public function updateManyStatus(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:categories,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $categories = Category::whereIn('id', $request->ids)->get();

        foreach ($categories as $category) {
            $category->update([
                'status' => $request->status,
            ]);
        }

        return response()->json([
            'message' => 'Category status updated successfully',
        ]);
    }

    /**
     * DELETE category (soft delete)
     */
    public function destroy(Category $category)
    {
        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }

    /**
     * BULK DELETE category
     */
    public function destroyMany(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:categories,id'],
        ]);

        $categories = Category::whereIn('id', $request->ids)->get();

        foreach ($categories as $category) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }

            $category->delete();
        }

        return response()->json([
            'message' => 'Categories deleted successfully',
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
        $category->refresh();

        return (new CategoryResource($category))->additional([
            'message' => 'Category restored successfully',
        ]);
    }

    /**
     * FORCE DELETE permanently
     */
    public function forceDelete($id)
    {
        $category = Category::withTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $category);

        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->forceDelete();

        return response()->json([
            'message' => 'Category permanently deleted',
        ]);
    }
}