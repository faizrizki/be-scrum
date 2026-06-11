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

    public function show(Task $task): JsonResponse
    {
        $task->load(['assignee:id,name', 'project:id,name']);

        return response()->json([
            'success' => true,
            'data' => $task,
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        if (! $request->user()->canManageTasks()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya izin membuat task',
            ], 403);
        }

        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|in:TODO,IN_PROGRESS,REVIEW,DONE',
            'priority' => 'required|in:LOW,MEDIUM,HIGH',
            'storyPoints' => 'required|integer|min:1',
            'assigneeId' => 'nullable|integer|exists:users,id',
        ]);

        $task = $project->tasks()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
            'priority' => $data['priority'],
            'story_points' => $data['storyPoints'],
            'assignee_id' => $data['assigneeId'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $task,
        ], 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if (! $user->canUpdateTaskStatus()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya izin mengubah task',
            ], 403);
        }

        // Member hanya boleh update status. PM & Admin bisa update semua field.
        if (! $user->canManageTasks()) {
            $allowedKeys = ['status'];
            $sent = array_keys($request->all());
            $forbidden = array_diff($sent, $allowedKeys);

            if (! empty($forbidden)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member hanya boleh mengubah status task',
                    'forbidden_fields' => array_values($forbidden),
                ], 403);
            }
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:TODO,IN_PROGRESS,REVIEW,DONE',
            'priority' => 'sometimes|required|in:LOW,MEDIUM,HIGH',
            'storyPoints' => 'sometimes|required|integer|min:1',
            'assigneeId' => 'nullable|integer|exists:users,id',
        ]);

        if (array_key_exists('storyPoints', $data)) {
            $data['story_points'] = $data['storyPoints'];
            unset($data['storyPoints']);
        }
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

    public function destroy(Request $request, Task $task): JsonResponse
    {
        if (! $request->user()->canManageTasks()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya izin menghapus task',
            ], 403);
        }

        $task->delete();
        return response()->json(null, 204);
    }
}
