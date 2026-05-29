<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncludedItemResource;
use App\Models\IncludedItem;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class IncludedItemController extends Controller
{
    public function index(Request $request)
    {
        $query = IncludedItem::query();

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $includedItems = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Included items list',
            'data' => IncludedItemResource::collection($includedItems),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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

        return response()->json([
            'success' => true,
            'message' => 'Included item created successfully',
            'data' => new IncludedItemResource($includedItem),
        ], 201);
    }

    public function show(IncludedItem $includedItem)
    {
        return response()->json([
            'success' => true,
            'message' => 'Included item details',
            'data' => new IncludedItemResource($includedItem),
        ]);
    }

    public function showByServiceId($serviceId)
    {
        $includedItems = IncludedItem::where('service_id', $serviceId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Included items by service id',
            'data' => IncludedItemResource::collection($includedItems),
        ]);
    }

    public function update(Request $request, IncludedItem $includedItem)
    {
        $data = $request->validate([
            'service_id' => ['sometimes', 'required', 'exists:services,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        if ($request->hasFile('image')) {
            if ($includedItem->getRawOriginal('image_url')) {
                ImageUploadService::delete($includedItem->getRawOriginal('image_url'));
            }

            $data['image_url'] = ImageUploadService::upload(
                $request->file('image'),
                'included-items',
                1200
            );
        }

        unset($data['image']);

        $includedItem->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Included item updated successfully',
            'data' => new IncludedItemResource($includedItem->fresh()),
        ]);
    }

    public function destroy(IncludedItem $includedItem)
    {
        if ($includedItem->getRawOriginal('image_url')) {
            ImageUploadService::delete($includedItem->getRawOriginal('image_url'));
        }

        $includedItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Included item deleted successfully.',
        ]);
    }
}