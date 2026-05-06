<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncludedItem;
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
            'image_url' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

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
            'service_id' => ['sometimes', 'exists:services,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

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