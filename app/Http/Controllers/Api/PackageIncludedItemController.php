<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PackageIncludedItem;
use Illuminate\Http\Request;

class PackageIncludedItemController extends Controller
{
    public function index()
    {
        return response()->json(
            PackageIncludedItem::orderBy('sort_order')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:service_packages,id'],
            'included_item_id' => ['required', 'exists:included_items,id'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $item = PackageIncludedItem::create($data);

        return response()->json($item, 201);
    }

    public function show(PackageIncludedItem $packageIncludedItem)
    {
        return response()->json($packageIncludedItem);
    }

    public function update(Request $request, PackageIncludedItem $packageIncludedItem)
    {
        $data = $request->validate([
            'package_id' => ['sometimes', 'exists:service_packages,id'],
            'included_item_id' => ['sometimes', 'exists:included_items,id'],
            'sort_order' => ['sometimes', 'integer'],
        ]);

        $packageIncludedItem->update($data);

        return response()->json($packageIncludedItem);
    }

    public function destroy(PackageIncludedItem $packageIncludedItem)
    {
        $packageIncludedItem->delete();

        return response()->json([
            'message' => 'Package included item deleted successfully.',
        ]);
    }
}