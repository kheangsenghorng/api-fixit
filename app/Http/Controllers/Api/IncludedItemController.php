<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncludedItem;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class IncludedItemController extends Controller
{
    public function index()
    {
        return response()->json(
            IncludedItem::latest()->get()
        );
    }


    
    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
    
        if ($request->hasFile('image')) {
            $data['image_url'] = ImageUploadService::upload(
                $request->file('image'),
                'included-items',
                1200
            );
        }
    
        unset($data['image']);
    
        $includedItem = IncludedItem::create($data);
    
        return response()->json($includedItem, 201);
    }


    public function show(IncludedItem $includedItem)
    {
        return response()->json($includedItem);
    }


    public function update(Request $request, IncludedItem $includedItem)
    {
        $data = $request->validate([
            'service_id' => ['sometimes', 'required', 'exists:services,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
    
        if ($request->hasFile('image')) {
            ImageUploadService::delete($includedItem->image_url);
    
            $data['image_url'] = ImageUploadService::upload(
                $request->file('image'),
                'included-items',
                1200
            );
        }
    
        unset($data['image']);
    
        $includedItem->update($data);
    
        return response()->json($includedItem);
    }
    public function destroy(IncludedItem $includedItem)
    {
        $includedItem->delete();

        return response()->json([
            'message' => 'Included item deleted successfully.',
        ]);
    }
}