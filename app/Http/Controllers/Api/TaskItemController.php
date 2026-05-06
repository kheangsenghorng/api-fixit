<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskItem;
use Illuminate\Http\Request;

class TaskItemController extends Controller
{
    public function index()
    {
        return response()->json(
            TaskItem::with('taskGroup')->orderBy('sort_order')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'task_group_id' => ['required', 'exists:task_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $taskItem = TaskItem::create($data);

        return response()->json($taskItem, 201);
    }

    public function show(TaskItem $taskItem)
    {
        return response()->json(
            $taskItem->load('taskGroup')
        );
    }

    public function update(Request $request, TaskItem $taskItem)
    {
        $data = $request->validate([
            'task_group_id' => ['sometimes', 'exists:task_groups,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['sometimes', 'integer'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        $taskItem->update($data);

        return response()->json($taskItem);
    }

    public function destroy(TaskItem $taskItem)
    {
        $taskItem->delete();

        return response()->json([
            'message' => 'Task item deleted successfully.',
        ]);
    }
}