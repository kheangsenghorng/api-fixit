<?php

namespace App\Http\Controllers\Api;

use App\Models\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTypeRequest;
use App\Http\Requests\UpdateTypeRequest;
use App\Http\Resources\TypeResource;


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
    public function active(Request $request)
    {
        $query = Type::with('category')
            ->where('status', 'active')
            ->whereHas('category', function ($q) {
                $q->where('status', 'active');
            });
    
        // filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
    
        // filter by search
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
        // Check if category is active
        $category = \App\Models\Category::find($data['category_id']);

        if (!$category || $category->status !== 'active') {
            return response()->json([
                'message' => 'Cannot create type. Category is inactive.'
            ], 422);
        }
    
        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('types', 'public');
        }
    
        $type = Type::create($data);
    
        return response()->json([
            'message' => 'Type created successfully',
            'data' => new TypeResource($type->load('category')), // ✅ load relation
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
    
        // Use new category if provided, otherwise keep old one
        $categoryId = $data['category_id'] ?? $type->category_id;
    
        $category = \App\Models\Category::find($categoryId);
    
        if (!$category || $category->status !== 'active') {
            return response()->json([
                'message' => 'Cannot update type. Category is inactive.'
            ], 422);
        }
    
        // Handle icon update
        if ($request->hasFile('icon')) {
            $type->deleteIcon();
            $data['icon'] = $request->file('icon')->store('types', 'public');
        }
    
        $type->update($data);
    
        return response()->json([
            'success' => true,
            'message' => 'Type updated successfully',
            'data' => new TypeResource($type->load('category')),
        ]);
    }
    public function updateManyStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:types,id',
            'status' => 'required|in:active,inactive'
        ]);

        Type::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Types status updated successfully'
        ]);
    }



    public function destroy(Type $type)
    {
        $type->deleteIcon();
        $type->delete();
    
        return response()->json([
            'message' => 'Type deleted successfully'
        ]);
    }

    public function destroyMany(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:types,id'
        ]);
    
        $types = Type::whereIn('id', $request->ids)->get();
    
        foreach ($types as $type) {
            $type->deleteIcon();
            $type->delete();
        }
    
        return response()->json([
            'message' => 'Types deleted successfully'
        ]);
    }
}