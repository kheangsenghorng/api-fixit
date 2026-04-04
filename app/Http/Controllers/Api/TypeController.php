<?php

namespace App\Http\Controllers\Api;

use App\Models\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTypeRequest;
use App\Http\Requests\UpdateTypeRequest;
use App\Http\Resources\TypeResource;
use App\Services\ImageUploadService;

class TypeController extends Controller
{
    public function index(Request $request)
    {
        $types = Type::with('category')
            ->search($request->search)
            ->category($request->category_id)
            ->status($request->status)
            ->sort($request->sort_by, $request->sort_order)
            ->paginate($request->get('per_page', 10));

        return TypeResource::collection($types);
    }


    /**
     * Get type statistics.
     */
    public function stats()
    {
        $stats = Type::selectRaw('
                COUNT(*) as total_types,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_types,
                SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) as inactive_types
            ')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Type statistics retrieved successfully.',
            'data' => [
                'total_types' => (int) $stats->total_types,
                'active_types' => (int) $stats->active_types,
                'inactive_types' => (int) $stats->inactive_types,
            ]
        ]);
    }

    public function active(Request $request)
    {
        $query = Type::with('category')
            ->where('status', 'active')
            ->whereHas('category', function ($q) {
                $q->where('status', 'active');
            });

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $types = $query->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'message' => 'Success Types retrieved successfully',
            'data' => TypeResource::collection($types),
        ]);
    }

    public function store(StoreTypeRequest $request)
    {
        $data = $request->validated();

        $category = \App\Models\Category::find($data['category_id']);

        if (!$category || $category->status !== 'active') {
            return response()->json([
                'message' => 'Cannot create type. Category is inactive.'
            ], 422);
        }

        if ($request->hasFile('icon')) {
            $data['icon'] = ImageUploadService::upload(
                $request->file('icon'),
                'types',
                600
            );
        }

        $type = Type::create($data);
        $type->load('category');

        return response()->json([
            'message' => 'Type created successfully',
            'data' => new TypeResource($type),
        ]);
    }

    public function show(Type $type)
    {
        $type->load('category');

        return new TypeResource($type);
    }

    public function update(UpdateTypeRequest $request, Type $type)
    {
        $data = $request->validated();

        $categoryId = $data['category_id'] ?? $type->category_id;
        $category = \App\Models\Category::find($categoryId);

        if (!$category || $category->status !== 'active') {
            return response()->json([
                'message' => 'Cannot update type. Category is inactive.'
            ], 422);
        }

        if ($request->hasFile('icon')) {
            ImageUploadService::delete($type->icon);

            $data['icon'] = ImageUploadService::upload(
                $request->file('icon'),
                'types',
                600
            );
        }

        $type->update($data);
        $type->refresh()->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Type updated successfully',
            'data' => new TypeResource($type),
        ]);
    }

    public function updateManyStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:types,id',
            'status' => 'required|in:active,inactive',
        ]);

        $types = Type::whereIn('id', $request->ids)->get();

        foreach ($types as $type) {
            $type->update([
                'status' => $request->status,
            ]);
        }

        return response()->json([
            'message' => 'Types status updated successfully',
        ]);
    }

    public function destroy(Type $type)
    {
        if ($type->icon) {
            ImageUploadService::delete($type->icon);
        }

        $type->delete();

        return response()->json([
            'message' => 'Type deleted successfully',
        ]);
    }

    public function destroyMany(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:types,id',
        ]);

        $types = Type::whereIn('id', $request->ids)->get();

        foreach ($types as $type) {
            if ($type->icon) {
                ImageUploadService::delete($type->icon);
            }

            $type->delete();
        }

        return response()->json([
            'message' => 'Types deleted successfully',
        ]);
    }
}