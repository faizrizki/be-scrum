<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $project->tasks()->get(),
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|in:TODO,IN_PROGRESS,REVIEW,DONE',
            'priority' => 'required|in:LOW,MEDIUM,HIGH',
            'progress' => 'required|integer|min:0|max:100',
            'deadline' => 'required|date',
            'assigneeId' => 'nullable|exists:users,id',
        ]);

        $task = $project->tasks()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
            'priority' => $data['priority'],
            'progress' => $data['progress'],
            'deadline' => $data['deadline'],
            'assignee_id' => $data['assigneeId'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $task,
        ], 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:TODO,IN_PROGRESS,REVIEW,DONE',
            'priority' => 'sometimes|required|in:LOW,MEDIUM,HIGH',
            'progress' => 'sometimes|integer|min:0|max:100',
            'deadline' => 'sometimes|required|date',
            'assigneeId' => 'nullable|exists:users,id',
        ]);

        if (array_key_exists('assigneeId', $data)) {
            $data['assignee_id'] = $data['assigneeId'];
            unset($data['assigneeId']);
        }

        $task->update($data);

        return response()->json([
            'success' => true,
            'data' => $task->fresh(),
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();
        return response()->json(null, 204);
    }
}
