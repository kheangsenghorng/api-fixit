<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskGroup;
use Illuminate\Http\Request;

class TaskGroupController extends Controller
{
    public function index()
    {
        return response()->json(
            TaskGroup::with(['taskItems'])->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $taskGroup = TaskGroup::create($data);

        return response()->json($taskGroup, 201);
    }

    public function show(TaskGroup $taskGroup)
    {
        return response()->json(
            $taskGroup->load(['taskItems'])
        );
    }

    public function update(Request $request, TaskGroup $taskGroup)
    {
        $data = $request->validate([
            'service_id' => ['sometimes', 'exists:services,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        $taskGroup->update($data);

        return response()->json($taskGroup);
    }

    public function destroy(TaskGroup $taskGroup)
    {
        $taskGroup->delete();

        return response()->json([
            'message' => 'Task group deleted successfully.',
        ]);
    }
}