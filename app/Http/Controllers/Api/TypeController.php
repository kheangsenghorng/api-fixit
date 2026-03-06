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
            ->where('status', 'active');
    
        // filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
    
        $types = $query->latest()->get();
    
        return TypeResource::collection($types);
    }
    
    public function store(StoreTypeRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('types', 'public');
        }

        $type = Type::create($data);

        return new TypeResource($type);
    }


    public function show(Type $type)
    {
        $type->load('category');
        return new TypeResource($type);
    }


    public function update(UpdateTypeRequest $request, Type $type)
    {
        $data = $request->validated();
    
        if ($request->hasFile('icon')) {
            $type->deleteIcon();
            $data['icon'] = $request->file('icon')->store('types', 'public');
        }
    
        $type->update($data);
    
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