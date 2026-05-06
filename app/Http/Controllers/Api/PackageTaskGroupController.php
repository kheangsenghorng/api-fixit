<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PackageTaskGroup;
use Illuminate\Http\Request;

class PackageTaskGroupController extends Controller
{
    public function index()
    {
        return response()->json(
            PackageTaskGroup::orderBy('sort_order')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:service_packages,id'],
            'task_group_id' => ['required', 'exists:task_groups,id'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $item = PackageTaskGroup::create($data);

        return response()->json($item, 201);
    }

    public function show(PackageTaskGroup $packageTaskGroup)
    {
        return response()->json($packageTaskGroup);
    }

    public function update(Request $request, PackageTaskGroup $packageTaskGroup)
    {
        $data = $request->validate([
            'package_id' => ['sometimes', 'exists:service_packages,id'],
            'task_group_id' => ['sometimes', 'exists:task_groups,id'],
            'sort_order' => ['sometimes', 'integer'],
        ]);

        $packageTaskGroup->update($data);

        return response()->json($packageTaskGroup);
    }

    public function destroy(PackageTaskGroup $packageTaskGroup)
    {
        $packageTaskGroup->delete();

        return response()->json([
            'message' => 'Package task group deleted successfully.',
        ]);
    }
}