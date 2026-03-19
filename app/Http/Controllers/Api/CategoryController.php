<?php

namespace App\Http\Controllers\Api;

use App\Events\CategoryChanged;
use App\Events\CategoryCreated;
use App\Events\CategoryUpdated;
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
            ->orderByDesc('priority')
            ->latest('id')
            ->paginate(10);

        return CategoryResource::collection($categories);
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
            'data' => CategoryResource::collection($categories),
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
    
        broadcast(new CategoryCreated($category))->toOthers();
    
        return (new CategoryResource($category))
            ->additional([
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

        broadcast(new CategoryChanged('changed', $category))->toOthers();

        return (new CategoryResource($category))
            ->additional([
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
    
        Category::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);
    
        $categories = Category::whereIn('id', $request->ids)->get();
    
        foreach ($categories as $category) {
            broadcast(new CategoryUpdated(Category::find($category->id)))->toOthers();
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
        $categoryData = clone $category;

        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->delete();

        broadcast(new CategoryChanged('deleted', $categoryData))->toOthers();

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
        }

        Category::whereIn('id', $request->ids)->delete();

        foreach ($categories as $category) {
            broadcast(new CategoryChanged('deleted', $category))->toOthers();
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

        broadcast(new CategoryChanged('restored', $category))->toOthers();

        return (new CategoryResource($category))
            ->additional([
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

        $categoryData = clone $category;

        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->forceDelete();

        broadcast(new CategoryChanged('force_deleted', $categoryData))->toOthers();

        return response()->json([
            'message' => 'Category permanently deleted',
        ]);
    }
}